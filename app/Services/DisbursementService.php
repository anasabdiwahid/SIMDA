<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use App\Helpers\Database;
use App\Helpers\Env;
use DateTimeImmutable;
use PDO;
use RuntimeException;

/**
 * Module 6 — Simulated Mobile Money Disbursement Service
 *
 * Rules:
 * - Supported mock providers: EVC Plus, Zaad, eDahab.
 * - Net Disbursed = Approved Amount - Administrative Fee.
 * - Approved but undisbursed applications expire after 30 days.
 * - The repayment schedule becomes active from the disbursement date.
 */
final class DisbursementService
{
    public const PROVIDERS = ['EVC Plus', 'Zaad', 'eDahab'];

    public static function expireStaleApprovals(?string $referenceDate = null, ?PDO $pdo = null): int
    {
        $db = $pdo ?? Database::connection();
        $ref = $referenceDate ?? date('Y-m-d H:i:s');
        $stmt = $db->prepare("
            UPDATE financing_applications
            SET status = 'expired'
            WHERE status = 'approved'
              AND expires_at IS NOT NULL
              AND expires_at < :ref
        ");
        $stmt->execute([':ref' => $ref]);
        return $stmt->rowCount();
    }

    public static function disburse(
        int $applicationId,
        int $disbursedByUserId,
        string $provider,
        string $disbursementPhone,
        ?string $disbursementDate = null,
        ?PDO $pdo = null
    ): array {
        if (!in_array($provider, self::PROVIDERS, true)) {
            throw new RuntimeException('Unsupported mobile money provider. Choose EVC Plus, Zaad, or eDahab.');
        }

        $db = $pdo ?? Database::connection();
        $ownTransaction = !$db->inTransaction();
        if ($ownTransaction) {
            $db->beginTransaction();
        }

        try {
            $appStmt = $db->prepare('
                SELECT fa.*, p.product_type, c.credit_balance
                FROM financing_applications fa
                INNER JOIN products p ON p.id = fa.product_id
                INNER JOIN clients c ON c.id = fa.client_id
                WHERE fa.id = :id
                FOR UPDATE
            ');
            $appStmt->execute([':id' => $applicationId]);
            $app = $appStmt->fetch();

            if (!$app) {
                throw new RuntimeException('Financing application not found.');
            }
            if ($app['status'] !== 'approved') {
                throw new RuntimeException('Only approved applications can be disbursed. Current status: ' . $app['status']);
            }

            // Paid out today -> the real time (minute / hour schedules count from it); a past/future date -> 10:00.
            $disbDateTimeStr = ($disbursementDate && substr($disbursementDate, 0, 10) !== date('Y-m-d'))
                ? substr($disbursementDate, 0, 10) . ' 10:00:00'
                : date('Y-m-d H:i:s');
            $disbDateOnly = substr($disbDateTimeStr, 0, 10);

            // Check 30-day expiry rule
            $expiryDays = (int) Env::get('DISBURSEMENT_EXPIRY_DAYS', 30);
            if (!empty($app['reviewed_at'])) {
                $approvedAt = new DateTimeImmutable(substr((string) $app['reviewed_at'], 0, 10));
                $currentAt = new DateTimeImmutable($disbDateOnly);
                $daysSinceApproval = (int) $approvedAt->diff($currentAt)->format('%r%a');
                if ($daysSinceApproval > $expiryDays) {
                    $db->prepare("UPDATE financing_applications SET status = 'expired' WHERE id = :id")
                        ->execute([':id' => $applicationId]);
                    if ($ownTransaction) {
                        $db->commit();
                    }
                    throw new RuntimeException("Application expired (> {$expiryDays} days since approval without disbursement).");
                }
            }

            $approvedCents = ScheduleEngine::toCents($app['requested_amount']);
            $adminFeeCents = ScheduleEngine::toCents($app['agreed_admin_fee']);
            $netDisbursedCents = $approvedCents - $adminFeeCents;
            if ($netDisbursedCents <= 0) {
                throw new RuntimeException('Net disbursed amount must be positive.');
            }

            // Generate active schedule starting from the disbursement date
            $schedule = ScheduleEngine::generate(
                (string) $app['product_type'],
                ScheduleEngine::fromCents($approvedCents),
                (string) $app['agreed_rate'],
                (int) $app['term_installments'],
                (string) $app['repayment_frequency'],
                $disbDateTimeStr,
                max(1, (int) ($app['repayment_interval'] ?? 1))
            );

            $financingNumber = 'FIN-' . date('Ymd', strtotime($disbDateOnly)) . '-' . str_pad((string) $applicationId, 4, '0', STR_PAD_LEFT);
            $prefix = match ($provider) {
                'EVC Plus' => 'EVC',
                'Zaad' => 'ZAD',
                'eDahab' => 'EDH',
                default => 'MM',
            };
            $txRef = $prefix . '-' . strtoupper(bin2hex(random_bytes(4))) . '-' . $applicationId;

            $insFin = $db->prepare('
                INSERT INTO financings (
                    financing_number, application_id, client_id, branch_id, product_id, group_id,
                    disbursed_by, product_type, approved_amount, admin_fee, net_disbursed_amount,
                    total_profit, ibra_discount, total_due, principal_paid, profit_paid, total_paid,
                    outstanding_principal, outstanding_balance, term_installments, repayment_frequency, repayment_interval,
                    agreed_rate, disbursement_provider, disbursement_reference, disbursement_phone,
                    disbursed_at, maturity_date, status, calculation_inputs
                ) VALUES (
                    :fin_no, :app_id, :client_id, :branch_id, :product_id, :group_id,
                    :disb_by, :prod_type, :approved_amt, :admin_fee, :net_disb,
                    :tot_profit, 0.00, :tot_due, 0.00, 0.00, 0.00,
                    :out_prin, :out_bal, :term, :freq, :interval,
                    :rate, :provider, :tx_ref, :phone,
                    :disb_at, :maturity, \'active\', :calc_inputs
                )
            ');

            $insFin->execute([
                ':fin_no' => $financingNumber,
                ':app_id' => $applicationId,
                ':client_id' => $app['client_id'],
                ':branch_id' => $app['branch_id'],
                ':product_id' => $app['product_id'],
                ':group_id' => $app['group_id'],
                ':disb_by' => $disbursedByUserId,
                ':prod_type' => $app['product_type'],
                ':approved_amt' => $schedule['principal'],
                ':admin_fee' => ScheduleEngine::fromCents($adminFeeCents),
                ':net_disb' => ScheduleEngine::fromCents($netDisbursedCents),
                ':tot_profit' => $schedule['total_profit'],
                ':tot_due' => $schedule['total_due'],
                ':out_prin' => $schedule['principal'],
                ':out_bal' => $schedule['total_due'],
                ':term' => $schedule['term_installments'],
                ':freq' => $schedule['frequency'],
                ':interval' => $schedule['interval'],
                ':rate' => $schedule['rate'],
                ':provider' => $provider,
                ':tx_ref' => $txRef,
                ':phone' => $disbursementPhone,
                ':disb_at' => $disbDateTimeStr,
                ':maturity' => $schedule['maturity_date'],
                ':calc_inputs' => json_encode($schedule['calculation_inputs'], JSON_UNESCAPED_UNICODE),
            ]);

            $financingId = (int) $db->lastInsertId();

            $insInst = $db->prepare('
                INSERT INTO installments (
                    financing_id, installment_number, original_due_date, due_date, due_at, was_friday_shifted,
                    principal_due, profit_due, total_due, principal_paid, profit_paid, total_paid,
                    remaining_balance, principal_balance_after, status
                ) VALUES (
                    :fin_id, :inst_no, :orig_due, :due, :due_at, :shifted,
                    :prin_due, :prof_due, :tot_due, 0.00, 0.00, 0.00,
                    :rem_bal, :prin_after, \'pending\'
                )
            ');

            foreach ($schedule['installments'] as $row) {
                $insInst->execute([
                    ':fin_id' => $financingId,
                    ':inst_no' => $row['installment_number'],
                    ':orig_due' => $row['original_due_date'],
                    ':due' => $row['due_date'],
                    ':due_at' => $row['due_at'],
                    ':shifted' => $row['was_friday_shifted'],
                    ':prin_due' => $row['principal_due'],
                    ':prof_due' => $row['profit_due'],
                    ':tot_due' => $row['total_due'],
                    ':rem_bal' => $row['remaining_balance'],
                    ':prin_after' => $row['principal_balance_after'],
                ]);
            }

            $db->prepare("UPDATE financing_applications SET status = 'disbursed' WHERE id = :id")
                ->execute([':id' => $applicationId]);

            if ($ownTransaction) {
                $db->commit();
            }

            AuditLogger::log(
                'financing.disbursed',
                'financings',
                $financingId,
                "Disbursed financing {$financingNumber} via {$provider} (Ref: {$txRef}). Net disbursed: \$" . ScheduleEngine::fromCents($netDisbursedCents),
                [
                    'application_id' => $applicationId,
                    'financing_id' => $financingId,
                    'approved_amount' => $schedule['principal'],
                    'admin_fee' => ScheduleEngine::fromCents($adminFeeCents),
                    'net_disbursed' => ScheduleEngine::fromCents($netDisbursedCents),
                    'provider' => $provider,
                    'reference' => $txRef,
                ],
                $disbursedByUserId,
                (int) $app['branch_id']
            );

            return [
                'financing_id' => $financingId,
                'financing_number' => $financingNumber,
                'disbursement_reference' => $txRef,
                'provider' => $provider,
                'approved_amount' => $schedule['principal'],
                'admin_fee' => ScheduleEngine::fromCents($adminFeeCents),
                'net_disbursed_amount' => ScheduleEngine::fromCents($netDisbursedCents),
                'total_due' => $schedule['total_due'],
                'disbursed_at' => $disbDateTimeStr,
            ];
        } catch (\Throwable $e) {
            if ($ownTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}

