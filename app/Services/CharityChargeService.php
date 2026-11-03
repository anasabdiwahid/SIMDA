<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use App\Helpers\Database;
use App\Helpers\Env;
use PDO;
use RuntimeException;

/**
 * Module 8 — Charity Late Charges Service
 *
 * Rules:
 * - An installment unpaid 7 days after its due date (i.e. > 7 days late, day 8+)
 *   receives the administrator-configured fixed late charge.
 * - Late charges are directed to a separate Charity account and NEVER counted as institutional income.
 * - Branch Managers (and Admin) can waive charges with a mandatory reason.
 * - Every assessment and waiver is recorded in the audit log.
 * - Waived charges do not penalize the client's credit score.
 */
final class CharityChargeService
{
    public static function configuredLateFee(?PDO $pdo = null): string
    {
        $db = $pdo ?? Database::connection();
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'charity_late_fee_amount' LIMIT 1");
        $stmt->execute();
        $val = $stmt->fetchColumn();
        if ($val !== false && is_numeric($val)) {
            return number_format((float) $val, 2, '.', '');
        }
        return number_format((float) Env::get('DEFAULT_CHARITY_LATE_FEE', '15.00'), 2, '.', '');
    }

    /**
     * Scan all active/overdue financings and assess fixed charity late charges
     * on installments that are > 7 days past due and not yet charged.
     * Also marks any unpaid installment past due_date as 'overdue'.
     */
    public static function assessOverdueInstallments(?string $referenceDate = null, ?PDO $pdo = null): int
    {
        $db = $pdo ?? Database::connection();
        $refDate = $referenceDate ?? date('Y-m-d');
        $graceDays = (int) Env::get('LATE_CHARGE_GRACE_DAYS', 7);
        $lateFee = self::configuredLateFee($db);

        // 1. Mark pending/partial installments past due_date as overdue
        $markStmt = $db->prepare("
            UPDATE installments i
            INNER JOIN financings f ON f.id = i.financing_id
            SET i.status = 'overdue', f.status = 'overdue'
            WHERE i.status IN ('pending', 'partial')
              AND i.remaining_balance > 0
              AND i.due_date < :ref_date
              AND f.status IN ('active', 'overdue')
        ");
        $markStmt->execute([':ref_date' => $refDate]);

        // 2. Find installments more than $graceDays late without an existing charity_charges row
        $findStmt = $db->prepare("
            SELECT i.id AS installment_id,
                   i.financing_id,
                   i.due_date,
                   DATEDIFF(:ref_date, i.due_date) AS days_late,
                   f.client_id,
                   f.branch_id
            FROM installments i
            INNER JOIN financings f ON f.id = i.financing_id
            LEFT JOIN charity_charges cc ON cc.installment_id = i.id
            WHERE i.remaining_balance > 0
              AND i.status != 'paid'
              AND DATEDIFF(:ref_date_2, i.due_date) > :grace_days
              AND cc.id IS NULL
              AND f.status IN ('active', 'overdue')
        ");
        $findStmt->execute([
            ':ref_date' => $refDate,
            ':ref_date_2' => $refDate,
            ':grace_days' => $graceDays,
        ]);

        $rows = $findStmt->fetchAll();
        $assessedCount = 0;

        $insertStmt = $db->prepare("
            INSERT INTO charity_charges (
                financing_id, installment_id, client_id, branch_id,
                amount, amount_paid, remaining_amount, days_overdue_when_assessed, status, assessed_at
            ) VALUES (
                :financing_id, :installment_id, :client_id, :branch_id,
                :amount, 0.00, :remaining_amount, :days_overdue, 'unpaid', NOW()
            )
        ");

        foreach ($rows as $row) {
            $insertStmt->execute([
                ':financing_id' => $row['financing_id'],
                ':installment_id' => $row['installment_id'],
                ':client_id' => $row['client_id'],
                ':branch_id' => $row['branch_id'],
                ':amount' => $lateFee,
                ':remaining_amount' => $lateFee,
                ':days_overdue' => (int) $row['days_late'],
            ]);
            $chargeId = (int) $db->lastInsertId();
            $assessedCount++;

            AuditLogger::log(
                'charity_charge.assessed',
                'charity_charges',
                $chargeId,
                "Assessed \${$lateFee} charity late charge on installment #{$row['installment_id']} ({$row['days_late']} days overdue).",
                [
                    'financing_id' => (int) $row['financing_id'],
                    'installment_id' => (int) $row['installment_id'],
                    'amount' => $lateFee,
                    'days_late' => (int) $row['days_late'],
                ],
                null,
                (int) $row['branch_id']
            );

            // Recalculate client credit score upon missed due date penalty
            CreditScoreEngine::recalculateForClient(
                (int) $row['client_id'],
                'Missed installment due date by more than 7 days',
                'Wakhtigii bixinta قیbta oo dhaafay in ka badan 7 maalmood',
                $db
            );
        }

        return $assessedCount;
    }

    /**
     * Waive an unpaid/partial charity late charge (Branch Manager or Admin).
     */
    public static function waiveCharge(int $chargeId, int $waivedByUserId, string $reason, ?PDO $pdo = null): array
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('A valid waiver reason is required to waive a charity late charge.');
        }

        $db = $pdo ?? Database::connection();
        $stmt = $db->prepare('SELECT * FROM charity_charges WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $chargeId]);
        $charge = $stmt->fetch();

        if (!$charge) {
            throw new RuntimeException('Charity charge record not found.');
        }
        if ($charge['status'] === 'paid') {
            throw new RuntimeException('Cannot waive a charity charge that has already been paid.');
        }
        if ($charge['status'] === 'waived') {
            throw new RuntimeException('This charity charge has already been waived.');
        }

        $upd = $db->prepare("
            UPDATE charity_charges
            SET status = 'waived',
                remaining_amount = 0.00,
                waived_by = :waived_by,
                waived_at = NOW(),
                waiver_reason = :reason
            WHERE id = :id
        ");
        $upd->execute([
            ':waived_by' => $waivedByUserId,
            ':reason' => $reason,
            ':id' => $chargeId,
        ]);

        AuditLogger::log(
            'charity_charge.waived',
            'charity_charges',
            $chargeId,
            "Waived charity late charge #{$chargeId} (\${$charge['remaining_amount']}). Reason: {$reason}",
            [
                'charge_id' => $chargeId,
                'financing_id' => (int) $charge['financing_id'],
                'client_id' => (int) $charge['client_id'],
                'waived_amount' => $charge['remaining_amount'],
                'reason' => $reason,
            ],
            $waivedByUserId,
            (int) $charge['branch_id']
        );

        // Recalculate credit score because waived charges do not penalize the client
        CreditScoreEngine::recalculateForClient(
            (int) $charge['client_id'],
            'Charity late charge waived by management (' . $reason . ')',
            'Khidmadda sadaqada ee dib u dhaca waa la cafiyay (' . $reason . ')',
            $db
        );

        $stmt->execute([':id' => $chargeId]);
        return $stmt->fetch();
    }
}

