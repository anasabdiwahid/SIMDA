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

    /**
     * Charity account report (brief, Module 8): what was assessed, collected, waived and is still owed,
     * what was given out, and the balance left (collected − given out). Never counted as income.
     * $branchId null = whole institution.
     *
     * @return array{assessed: string, collected: string, waived: string, outstanding: string, used: string, balance: string}
     */
    public static function accountSummary(?int $branchId = null, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $where = $branchId !== null ? ' WHERE branch_id = :bid ' : '';
        $params = $branchId !== null ? [':bid' => $branchId] : [];

        $st = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) AS assessed,
                   COALESCE(SUM(amount_paid), 0) AS collected,
                   COALESCE(SUM(CASE WHEN status = 'waived' THEN remaining_amount ELSE 0 END), 0) AS waived,
                   COALESCE(SUM(CASE WHEN status <> 'waived' THEN remaining_amount ELSE 0 END), 0) AS outstanding
            FROM charity_charges {$where}
        ");
        $st->execute($params);
        $c = $st->fetch() ?: [];

        $pu = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM charity_payouts {$where}");
        $pu->execute($params);
        $usedCents = ScheduleEngine::toCents((string) $pu->fetchColumn());
        $collectedCents = ScheduleEngine::toCents((string) ($c['collected'] ?? '0'));

        return [
            'assessed' => ScheduleEngine::fromCents(ScheduleEngine::toCents((string) ($c['assessed'] ?? '0'))),
            'collected' => ScheduleEngine::fromCents($collectedCents),
            'waived' => ScheduleEngine::fromCents(ScheduleEngine::toCents((string) ($c['waived'] ?? '0'))),
            'outstanding' => ScheduleEngine::fromCents(ScheduleEngine::toCents((string) ($c['outstanding'] ?? '0'))),
            'used' => ScheduleEngine::fromCents($usedCents),
            'balance' => ScheduleEngine::fromCents($collectedCents - $usedCents),
        ];
    }

    /**
     * Records money given out from the charity account. The amount must be positive and no more than
     * the balance of that branch's account (or the whole institution's when $branchId is null).
     * Logged in the audit log.
     *
     * @throws RuntimeException on invalid input or insufficient balance
     */
    public static function recordPayout(?int $branchId, string $amount, string $recipient, string $purpose, string $paidOn, int $userId, ?PDO $pdo = null): int
    {
        $db = $pdo ?? Database::connection();
        $recipient = trim($recipient);
        $purpose = trim($purpose);
        $cents = ScheduleEngine::toCents($amount);
        if ($cents <= 0) {
            throw new RuntimeException('The amount must be greater than zero.');
        }
        if ($recipient === '' || $purpose === '') {
            throw new RuntimeException('Recipient and purpose are required.');
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $paidOn);
        if ($date === false || $date->format('Y-m-d') !== $paidOn || $paidOn > date('Y-m-d')) {
            throw new RuntimeException('Enter a valid payout date that is not in the future.');
        }
        $balanceCents = ScheduleEngine::toCents(self::accountSummary($branchId, $db)['balance']);
        if ($cents > $balanceCents) {
            throw new RuntimeException('The charity account balance ($' . ScheduleEngine::fromCents($balanceCents) . ') is too low for this payout.');
        }

        $db->prepare('
            INSERT INTO charity_payouts (branch_id, amount, recipient, purpose, paid_on, recorded_by)
            VALUES (:bid, :amt, :rec, :pur, :d, :uid)
        ')->execute([
            ':bid' => $branchId,
            ':amt' => ScheduleEngine::fromCents($cents),
            ':rec' => mb_substr($recipient, 0, 150),
            ':pur' => mb_substr($purpose, 0, 255),
            ':d' => $paidOn,
            ':uid' => $userId,
        ]);
        $id = (int) $db->lastInsertId();
        AuditLogger::log('charity.payout', 'charity_payouts', $id, 'Charity payout of $' . ScheduleEngine::fromCents($cents) . " to {$recipient}: {$purpose}", [], $userId, $branchId);
        return $id;
    }

    /** Payouts from the charity account, newest first. */
    public static function payouts(?int $branchId = null, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $where = $branchId !== null ? ' WHERE cp.branch_id = :bid ' : '';
        $st = $db->prepare("
            SELECT cp.*, u.full_name AS recorded_by_name, b.name AS branch_name
            FROM charity_payouts cp
            LEFT JOIN users u ON u.id = cp.recorded_by
            LEFT JOIN branches b ON b.id = cp.branch_id
            {$where}
            ORDER BY cp.paid_on DESC, cp.id DESC
        ");
        $st->execute($branchId !== null ? [':bid' => $branchId] : []);
        return $st->fetchAll();
    }
}

