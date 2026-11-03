<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use App\Helpers\Database;
use PDO;
use RuntimeException;

/**
 * Module 7 — Repayment Collection & Allocation Engine
 *
 * Strict Payment Allocation Order:
 * 1. Outstanding Charity Late Charges (oldest first).
 * 2. Oldest Unpaid Installment (ordered by installment_number ASC).
 *    Within an installment, profit due is satisfied first, then principal due (standard microfinance accounting).
 *
 * Supported Capabilities:
 * - Full payments & Partial payments.
 * - Consumption of existing Client Overpayment Credit (`apply_client_credit = true`).
 * - Retention of any excess payment beyond total financing obligations as Client Overpayment Credit (`clients.credit_balance`),
 *   or automatic application to the next installment(s).
 * - Early Repayment with optional Murabaha *Ibra* (voluntary Sharia-compliant profit rebate on unearned future installments).
 * - Full ACID database transaction safety.
 */
final class RepaymentEngine
{
    public const METHODS = ['EVC Plus', 'Zaad', 'eDahab', 'Cash'];

    public static function recordRepayment(
        int $financingId,
        int $recordedByUserId,
        string|float|int $cashAmount,
        string $paymentMethod,
        string $transactionReference,
        ?string $paymentDate = null,
        string $notes = '',
        bool $useClientCredit = true,
        string|float|int $ibraDiscountAmount = '0.00',
        ?PDO $pdo = null
    ): array {
        if (!in_array($paymentMethod, self::METHODS, true)) {
            throw new RuntimeException('Unsupported payment method.');
        }

        $cashCents = ScheduleEngine::toCents($cashAmount);
        $ibraCents = ScheduleEngine::toCents($ibraDiscountAmount);
        if ($cashCents < 0 || $ibraCents < 0) {
            throw new RuntimeException('Repayment and discount amounts cannot be negative.');
        }
        if ($cashCents === 0 && !$useClientCredit && $ibraCents === 0) {
            throw new RuntimeException('Repayment amount must be greater than zero.');
        }

        $payDateStr = $paymentDate ? substr($paymentDate, 0, 10) : date('Y-m-d');
        $payDateTimeStr = $payDateStr . ' ' . date('H:i:s');

        $db = $pdo ?? Database::connection();
        $ownTransaction = !$db->inTransaction();
        if ($ownTransaction) {
            $db->beginTransaction();
        }

        try {
            // Lock financing and client rows
            $finStmt = $db->prepare('SELECT * FROM financings WHERE id = :id FOR UPDATE');
            $finStmt->execute([':id' => $financingId]);
            $financing = $finStmt->fetch();

            if (!$financing) {
                throw new RuntimeException('Financing contract not found.');
            }
            if ($financing['status'] === 'completed') {
                throw new RuntimeException('This financing is already fully repaid.');
            }

            $clientId = (int) $financing['client_id'];
            $branchId = (int) $financing['branch_id'];

            $cliStmt = $db->prepare('SELECT * FROM clients WHERE id = :cid FOR UPDATE');
            $cliStmt->execute([':cid' => $clientId]);
            $client = $cliStmt->fetch();

            $existingCreditCents = ScheduleEngine::toCents($client['credit_balance'] ?? '0.00');
            $creditAppliedCents = 0;

            // Optional Murabaha Ibra discount on future unpaid profit
            $actualIbraAppliedCents = 0;
            if ($ibraCents > 0 && $financing['product_type'] === 'murabaha') {
                $actualIbraAppliedCents = self::applyMurabahaIbra($db, $financingId, $ibraCents);
            }

            $availablePoolCents = $cashCents;
            if ($useClientCredit && $existingCreditCents > 0) {
                $availablePoolCents += $existingCreditCents;
            }

            $allocatedCharityCents = 0;
            $allocatedPrincipalCents = 0;
            $allocatedProfitCents = 0;
            $breakdown = [
                'charity_charges_paid' => [],
                'installments_paid' => [],
            ];

            // STEP 1: Allocate to Outstanding Charity Late Charges first
            $ccStmt = $db->prepare("
                SELECT * FROM charity_charges
                WHERE financing_id = :fid AND status IN ('unpaid', 'partial') AND remaining_amount > 0
                ORDER BY id ASC
                FOR UPDATE
            ");
            $ccStmt->execute([':fid' => $financingId]);
            $charityCharges = $ccStmt->fetchAll();

            $updCc = $db->prepare('
                UPDATE charity_charges
                SET amount_paid = :paid,
                    remaining_amount = :rem,
                    status = :status,
                    paid_at = :paid_at
                WHERE id = :id
            ');

            foreach ($charityCharges as $cc) {
                if ($availablePoolCents <= 0) {
                    break;
                }
                $ccRemCents = ScheduleEngine::toCents($cc['remaining_amount']);
                $payCc = min($availablePoolCents, $ccRemCents);
                if ($payCc <= 0) {
                    continue;
                }

                $newCcPaidCents = ScheduleEngine::toCents($cc['amount_paid']) + $payCc;
                $newCcRemCents = $ccRemCents - $payCc;
                $newCcStatus = ($newCcRemCents === 0) ? 'paid' : 'partial';

                $updCc->execute([
                    ':paid' => ScheduleEngine::fromCents($newCcPaidCents),
                    ':rem' => ScheduleEngine::fromCents($newCcRemCents),
                    ':status' => $newCcStatus,
                    ':paid_at' => ($newCcRemCents === 0) ? $payDateTimeStr : null,
                    ':id' => $cc['id'],
                ]);

                $availablePoolCents -= $payCc;
                $allocatedCharityCents += $payCc;
                $breakdown['charity_charges_paid'][] = [
                    'charge_id' => (int) $cc['id'],
                    'installment_id' => (int) $cc['installment_id'],
                    'amount_applied' => ScheduleEngine::fromCents($payCc),
                    'status_after' => $newCcStatus,
                ];
            }

            // STEP 2: Allocate to Oldest Unpaid Installment(s) in chronological order
            $instStmt = $db->prepare("
                SELECT * FROM installments
                WHERE financing_id = :fid AND status != 'paid' AND remaining_balance > 0
                ORDER BY installment_number ASC
                FOR UPDATE
            ");
            $instStmt->execute([':fid' => $financingId]);
            $installments = $instStmt->fetchAll();

            $updInst = $db->prepare('
                UPDATE installments
                SET principal_paid = :prin_paid,
                    profit_paid = :prof_paid,
                    total_paid = :tot_paid,
                    remaining_balance = :rem_bal,
                    status = :status,
                    paid_at = :paid_at
                WHERE id = :id
            ');

            foreach ($installments as $inst) {
                if ($availablePoolCents <= 0) {
                    break;
                }
                $instRemCents = ScheduleEngine::toCents($inst['remaining_balance']);
                if ($instRemCents <= 0) {
                    continue;
                }

                $payInst = min($availablePoolCents, $instRemCents);

                $profDueCents = ScheduleEngine::toCents($inst['profit_due']);
                $profPaidCents = ScheduleEngine::toCents($inst['profit_paid']);
                $profUnpaidCents = max(0, $profDueCents - $profPaidCents);

                $prinDueCents = ScheduleEngine::toCents($inst['principal_due']);
                $prinPaidCents = ScheduleEngine::toCents($inst['principal_paid']);
                $prinUnpaidCents = max(0, $prinDueCents - $prinPaidCents);

                $payProf = min($payInst, $profUnpaidCents);
                $payPrin = min($payInst - $payProf, $prinUnpaidCents);

                $newProfPaidCents = $profPaidCents + $payProf;
                $newPrinPaidCents = $prinPaidCents + $payPrin;
                $newTotPaidCents = $newProfPaidCents + $newPrinPaidCents;
                $newRemCents = $instRemCents - $payInst;

                $newStatus = 'partial';
                if ($newRemCents === 0) {
                    $newStatus = 'paid';
                } elseif ($inst['due_date'] < $payDateStr) {
                    $newStatus = 'overdue';
                }

                $updInst->execute([
                    ':prin_paid' => ScheduleEngine::fromCents($newPrinPaidCents),
                    ':prof_paid' => ScheduleEngine::fromCents($newProfPaidCents),
                    ':tot_paid' => ScheduleEngine::fromCents($newTotPaidCents),
                    ':rem_bal' => ScheduleEngine::fromCents($newRemCents),
                    ':status' => $newStatus,
                    ':paid_at' => ($newRemCents === 0) ? $payDateTimeStr : null,
                    ':id' => $inst['id'],
                ]);

                $availablePoolCents -= $payInst;
                $allocatedProfitCents += $payProf;
                $allocatedPrincipalCents += $payPrin;

                $breakdown['installments_paid'][] = [
                    'installment_id' => (int) $inst['id'],
                    'installment_number' => (int) $inst['installment_number'],
                    'amount_applied' => ScheduleEngine::fromCents($payInst),
                    'principal_part' => ScheduleEngine::fromCents($payPrin),
                    'profit_part' => ScheduleEngine::fromCents($payProf),
                    'remaining_after' => ScheduleEngine::fromCents($newRemCents),
                    'status_after' => $newStatus,
                ];
            }

            // Compute how much existing client credit was consumed vs new overpayment credit retained
            $totalConsumedCents = $allocatedCharityCents + $allocatedPrincipalCents + $allocatedProfitCents;
            if ($useClientCredit && $existingCreditCents > 0) {
                $creditAppliedCents = min($existingCreditCents, $totalConsumedCents);
            }

            $overpaymentCreditedCents = max(0, $cashCents + $creditAppliedCents - $totalConsumedCents);
            $newClientCreditCents = ($existingCreditCents - $creditAppliedCents) + $overpaymentCreditedCents;

            // Update client credit balance
            $db->prepare('UPDATE clients SET credit_balance = :cb WHERE id = :cid')
                ->execute([
                    ':cb' => ScheduleEngine::fromCents($newClientCreditCents),
                    ':cid' => $clientId,
                ]);

            // Recompute financing aggregates directly from installments for 100% mathematical accuracy
            $aggStmt = $db->prepare('
                SELECT
                    COALESCE(SUM(principal_due), 0) AS sum_prin_due,
                    COALESCE(SUM(profit_due), 0) AS sum_prof_due,
                    COALESCE(SUM(total_due), 0) AS sum_tot_due,
                    COALESCE(SUM(principal_paid), 0) AS sum_prin_paid,
                    COALESCE(SUM(profit_paid), 0) AS sum_prof_paid,
                    COALESCE(SUM(total_paid), 0) AS sum_tot_paid,
                    COALESCE(SUM(remaining_balance), 0) AS sum_rem_bal,
                    SUM(CASE WHEN remaining_balance > 0 AND due_date < :pay_date THEN 1 ELSE 0 END) AS overdue_count
                FROM installments
                WHERE financing_id = :fid
            ');
            $aggStmt->execute([':pay_date' => $payDateStr, ':fid' => $financingId]);
            $agg = $aggStmt->fetch();

            $outBalCents = ScheduleEngine::toCents($agg['sum_rem_bal']);
            $outPrinCents = max(0, ScheduleEngine::toCents($agg['sum_prin_due']) - ScheduleEngine::toCents($agg['sum_prin_paid']));

            $finStatus = 'active';
            $closedAt = null;
            $isEarlySettlement = 0;

            if ($outBalCents === 0) {
                $finStatus = 'completed';
                $closedAt = $payDateTimeStr;
                if ($payDateStr < $financing['maturity_date']) {
                    $isEarlySettlement = 1;
                }
            } elseif ((int) $agg['overdue_count'] > 0) {
                $finStatus = 'overdue';
            }

            $totalIbraCents = ScheduleEngine::toCents($financing['ibra_discount']) + $actualIbraAppliedCents;

            $updFin = $db->prepare('
                UPDATE financings
                SET total_profit = :tot_prof,
                    ibra_discount = :ibra,
                    total_due = :tot_due,
                    principal_paid = :prin_paid,
                    profit_paid = :prof_paid,
                    total_paid = :tot_paid,
                    outstanding_principal = :out_prin,
                    outstanding_balance = :out_bal,
                    status = :status,
                    closed_at = :closed_at
                WHERE id = :id
            ');
            $updFin->execute([
                ':tot_prof' => $agg['sum_prof_due'],
                ':ibra' => ScheduleEngine::fromCents($totalIbraCents),
                ':tot_due' => $agg['sum_tot_due'],
                ':prin_paid' => $agg['sum_prin_paid'],
                ':prof_paid' => $agg['sum_prof_paid'],
                ':tot_paid' => $agg['sum_tot_paid'],
                ':out_prin' => ScheduleEngine::fromCents($outPrinCents),
                ':out_bal' => ScheduleEngine::fromCents($outBalCents),
                ':status' => $finStatus,
                ':closed_at' => $closedAt,
                ':id' => $financingId,
            ]);

            $receiptNumber = 'RCP-' . date('Ymd', strtotime($payDateStr)) . '-' . strtoupper(bin2hex(random_bytes(3)));

            $insRep = $db->prepare('
                INSERT INTO repayments (
                    receipt_number, financing_id, client_id, branch_id, recorded_by,
                    amount, credit_applied, allocated_to_charity, allocated_to_principal,
                    allocated_to_profit, overpayment_credited, ibra_discount_applied,
                    payment_method, transaction_reference, payment_date, is_early_settlement,
                    allocation_breakdown, notes
                ) VALUES (
                    :rcp, :fid, :cid, :bid, :rec_by,
                    :amt, :cred_app, :alloc_char, :alloc_prin,
                    :alloc_prof, :overpay, :ibra,
                    :method, :tx_ref, :pay_date, :early,
                    :breakdown, :notes
                )
            ');
            $insRep->execute([
                ':rcp' => $receiptNumber,
                ':fid' => $financingId,
                ':cid' => $clientId,
                ':bid' => $branchId,
                ':rec_by' => $recordedByUserId,
                ':amt' => ScheduleEngine::fromCents($cashCents),
                ':cred_app' => ScheduleEngine::fromCents($creditAppliedCents),
                ':alloc_char' => ScheduleEngine::fromCents($allocatedCharityCents),
                ':alloc_prin' => ScheduleEngine::fromCents($allocatedPrincipalCents),
                ':alloc_prof' => ScheduleEngine::fromCents($allocatedProfitCents),
                ':overpay' => ScheduleEngine::fromCents($overpaymentCreditedCents),
                ':ibra' => ScheduleEngine::fromCents($actualIbraAppliedCents),
                ':method' => $paymentMethod,
                ':tx_ref' => $transactionReference,
                ':pay_date' => $payDateStr,
                ':early' => $isEarlySettlement,
                ':breakdown' => json_encode($breakdown, JSON_UNESCAPED_UNICODE),
                ':notes' => $notes,
            ]);

            $repaymentId = (int) $db->lastInsertId();

            // Recalculate credit score inside transaction
            $reasonEn = $isEarlySettlement
                ? "Full early settlement of financing {$financing['financing_number']} ({$receiptNumber})"
                : "Repayment {$receiptNumber} recorded (\$" . ScheduleEngine::fromCents($cashCents) . ')';
            $reasonSo = $isEarlySettlement
                ? "Bixinta hore oo dhammaystiran ee maalgelinta {$financing['financing_number']} ({$receiptNumber})"
                : "Lacag bixinta {$receiptNumber} waa la diiwaangeliyay (\$" . ScheduleEngine::fromCents($cashCents) . ')';

            $scoreResult = CreditScoreEngine::recalculateForClient($clientId, $reasonEn, $reasonSo, $db, $payDateStr);

            if ($ownTransaction) {
                $db->commit();
            }

            AuditLogger::log(
                'repayment.recorded',
                'repayments',
                $repaymentId,
                "Recorded repayment {$receiptNumber} of \$" . ScheduleEngine::fromCents($cashCents) . " for financing {$financing['financing_number']}.",
                [
                    'receipt_number' => $receiptNumber,
                    'financing_id' => $financingId,
                    'cash_amount' => ScheduleEngine::fromCents($cashCents),
                    'credit_applied' => ScheduleEngine::fromCents($creditAppliedCents),
                    'allocated_to_charity' => ScheduleEngine::fromCents($allocatedCharityCents),
                    'allocated_to_principal' => ScheduleEngine::fromCents($allocatedPrincipalCents),
                    'allocated_to_profit' => ScheduleEngine::fromCents($allocatedProfitCents),
                    'overpayment_credited' => ScheduleEngine::fromCents($overpaymentCreditedCents),
                    'ibra_discount_applied' => ScheduleEngine::fromCents($actualIbraAppliedCents),
                    'financing_status_after' => $finStatus,
                ],
                $recordedByUserId,
                $branchId
            );

            return [
                'repayment_id' => $repaymentId,
                'receipt_number' => $receiptNumber,
                'financing_id' => $financingId,
                'cash_amount' => ScheduleEngine::fromCents($cashCents),
                'credit_applied' => ScheduleEngine::fromCents($creditAppliedCents),
                'allocated_to_charity' => ScheduleEngine::fromCents($allocatedCharityCents),
                'allocated_to_principal' => ScheduleEngine::fromCents($allocatedPrincipalCents),
                'allocated_to_profit' => ScheduleEngine::fromCents($allocatedProfitCents),
                'overpayment_credited' => ScheduleEngine::fromCents($overpaymentCreditedCents),
                'client_credit_balance' => ScheduleEngine::fromCents($newClientCreditCents),
                'ibra_discount_applied' => ScheduleEngine::fromCents($actualIbraAppliedCents),
                'outstanding_balance_after' => ScheduleEngine::fromCents($outBalCents),
                'financing_status' => $finStatus,
                'is_early_settlement' => (bool) $isEarlySettlement,
                'credit_score' => $scoreResult,
                'breakdown' => $breakdown,
            ];
        } catch (\Throwable $e) {
            if ($ownTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Apply Murabaha Ibra discount (reduces unpaid profit on future installments).
     */
    private static function applyMurabahaIbra(PDO $db, int $financingId, int $requestedIbraCents): int
    {
        $stmt = $db->prepare("
            SELECT * FROM installments
            WHERE financing_id = :fid AND status != 'paid' AND remaining_balance > 0
            ORDER BY installment_number DESC
            FOR UPDATE
        ");
        $stmt->execute([':fid' => $financingId]);
        $rows = $stmt->fetchAll();

        $remainingIbra = $requestedIbraCents;
        $appliedTotal = 0;

        $upd = $db->prepare('
            UPDATE installments
            SET profit_due = :prof_due,
                total_due = :tot_due,
                remaining_balance = :rem_bal,
                status = :status
            WHERE id = :id
        ');

        foreach ($rows as $inst) {
            if ($remainingIbra <= 0) {
                break;
            }
            $profDue = ScheduleEngine::toCents($inst['profit_due']);
            $profPaid = ScheduleEngine::toCents($inst['profit_paid']);
            $unpaidProf = max(0, $profDue - $profPaid);
            if ($unpaidProf <= 0) {
                continue;
            }

            $cut = min($remainingIbra, $unpaidProf);
            $newProfDue = $profDue - $cut;
            $newTotDue = ScheduleEngine::toCents($inst['principal_due']) + $newProfDue;
            $newRemBal = max(0, ScheduleEngine::toCents($inst['remaining_balance']) - $cut);
            $newStatus = ($newRemBal === 0) ? 'paid' : $inst['status'];

            $upd->execute([
                ':prof_due' => ScheduleEngine::fromCents($newProfDue),
                ':tot_due' => ScheduleEngine::fromCents($newTotDue),
                ':rem_bal' => ScheduleEngine::fromCents($newRemBal),
                ':status' => $newStatus,
                ':id' => $inst['id'],
            ]);

            $remainingIbra -= $cut;
            $appliedTotal += $cut;
        }

        return $appliedTotal;
    }
}

