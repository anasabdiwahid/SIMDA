<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;

/**
 * Module 12 — Portfolio at Risk (PAR30) Calculation Engine
 *
 * Formula:
 *   PAR30 = (Outstanding Balance of Financings with an Installment More Than 30 Days Late / Total Outstanding Balance) * 100
 *
 * Rules:
 * - Calculated strictly from live database records.
 * - Never manually entered.
 * - A branch or institution with no active/overdue financing ($0.00 total outstanding balance) returns 0.00%.
 */
final class Par30Service
{
    public static function calculate(?int $branchId = null, ?string $referenceDate = null, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $refDate = $referenceDate ?? date('Y-m-d');

        $branchFilter = '';
        $params = [];
        if ($branchId !== null) {
            $branchFilter = ' AND f.branch_id = :branch_id ';
            $params[':branch_id'] = $branchId;
        }

        // 1. Total Outstanding Balance across active/overdue financings
        $totStmt = $db->prepare("
            SELECT COALESCE(SUM(f.outstanding_balance), 0.00) AS total_outstanding,
                   COUNT(f.id) AS active_financings_count
            FROM financings f
            WHERE f.status IN ('active', 'overdue')
              AND f.outstanding_balance > 0
              {$branchFilter}
        ");
        $totStmt->execute($params);
        $totRow = $totStmt->fetch();
        $totalOutstandingCents = ScheduleEngine::toCents($totRow['total_outstanding'] ?? '0.00');
        $activeCount = (int) ($totRow['active_financings_count'] ?? 0);

        if ($totalOutstandingCents <= 0) {
            return [
                'reference_date' => $refDate,
                'branch_id' => $branchId,
                'total_outstanding_balance' => '0.00',
                'risky_outstanding_balance' => '0.00',
                'par30_percentage' => '0.00',
                'active_financings_count' => $activeCount,
                'risky_financings_count' => 0,
                'risky_financings' => [],
            ];
        }

        // 2. Financings that have at least one unpaid installment more than 30 days late (DATEDIFF > 30)
        $riskParams = $params;
        $riskParams[':ref_date'] = $refDate;
        $riskParams[':ref_date_2'] = $refDate;

        $riskStmt = $db->prepare("
            SELECT f.id,
                   f.financing_number,
                   f.client_id,
                   f.branch_id,
                   f.approved_amount,
                   f.outstanding_balance,
                   c.full_name AS client_name,
                   c.phone AS client_phone,
                   b.name AS branch_name,
                   MAX(DATEDIFF(:ref_date, i.due_date)) AS max_days_overdue
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            INNER JOIN branches b ON b.id = f.branch_id
            INNER JOIN installments i ON i.financing_id = f.id
            WHERE f.status IN ('active', 'overdue')
              AND f.outstanding_balance > 0
              AND i.remaining_balance > 0
              AND i.status != 'paid'
              AND DATEDIFF(:ref_date_2, i.due_date) > 30
              {$branchFilter}
            GROUP BY f.id
            ORDER BY max_days_overdue DESC
        ");
        $riskStmt->execute($riskParams);
        $riskyRows = $riskStmt->fetchAll();

        $riskyOutstandingCents = 0;
        foreach ($riskyRows as $r) {
            $riskyOutstandingCents += ScheduleEngine::toCents($r['outstanding_balance']);
        }

        $totalDec = ScheduleEngine::fromCents($totalOutstandingCents);
        $riskyDec = ScheduleEngine::fromCents($riskyOutstandingCents);
        $ratio = bcdiv(bcmul($riskyDec, '100', 6), $totalDec, 4);
        $par30Pct = number_format((float) $ratio, 2, '.', '');

        return [
            'reference_date' => $refDate,
            'branch_id' => $branchId,
            'total_outstanding_balance' => $totalDec,
            'risky_outstanding_balance' => $riskyDec,
            'par30_percentage' => $par30Pct,
            'active_financings_count' => $activeCount,
            'risky_financings_count' => count($riskyRows),
            'risky_financings' => $riskyRows,
        ];
    }

    /**
     * PAR30 at the end of each of the last $months months (the current month up to today), for the trend
     * chart. Rebuilt from live records, never stored or typed in:
     *   outstanding at date D = total due − (profit + principal repaid on or before D), for financings
     *                           disbursed by D and not closed before D;
     *   at risk at date D     = that outstanding, for financings with an installment due more than
     *                           30 days before D and not fully paid by D.
     * A month with nothing outstanding is 0.00, not an error.
     *
     * @return list<array{month: string, label: string, par30: float, outstanding: string, at_risk: string}>
     */
    public static function monthlyTrend(int $months = 12, ?int $branchId = null, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $branchFilter = $branchId !== null ? ' AND f.branch_id = :bid ' : '';

        $stmt = $db->prepare("
            SELECT
                COALESCE(SUM(owed.amount), 0) AS outstanding,
                COALESCE(SUM(CASE WHEN owed.at_risk = 1 THEN owed.amount ELSE 0 END), 0) AS at_risk
            FROM (
                SELECT
                    GREATEST(f.total_due - f.ibra_discount - COALESCE((
                        SELECT SUM(r.allocated_to_profit + r.allocated_to_principal)
                        FROM repayments r
                        WHERE r.financing_id = f.id AND r.payment_date <= :d1
                    ), 0), 0) AS amount,
                    EXISTS (
                        SELECT 1 FROM installments i
                        WHERE i.financing_id = f.id
                          AND DATEDIFF(:d2, i.due_date) > 30
                          AND (i.paid_at IS NULL OR DATE(i.paid_at) > :d3)
                    ) AS at_risk
                FROM financings f
                WHERE f.disbursed_at IS NOT NULL
                  AND DATE(f.disbursed_at) <= :d4
                  AND (f.closed_at IS NULL OR DATE(f.closed_at) > :d5)
                  {$branchFilter}
            ) owed
        ");

        $out = [];
        $first = new \DateTimeImmutable('first day of this month');
        for ($k = $months - 1; $k >= 0; $k--) {
            $start = $first->modify("-{$k} months");
            $end = $k === 0 ? new \DateTimeImmutable('today') : $start->modify('last day of this month');
            $d = $end->format('Y-m-d');
            $params = [':d1' => $d, ':d2' => $d, ':d3' => $d, ':d4' => $d, ':d5' => $d];
            if ($branchId !== null) {
                $params[':bid'] = $branchId;
            }
            $stmt->execute($params);
            $row = $stmt->fetch() ?: ['outstanding' => 0, 'at_risk' => 0];
            $outCents = ScheduleEngine::toCents((string) $row['outstanding']);
            $riskCents = ScheduleEngine::toCents((string) $row['at_risk']);
            $out[] = [
                'month' => $start->format('Y-m'),
                'label' => $start->format('M Y'),
                'par30' => $outCents > 0 ? round($riskCents * 100 / $outCents, 2) : 0.0,
                'outstanding' => ScheduleEngine::fromCents($outCents),
                'at_risk' => ScheduleEngine::fromCents($riskCents),
            ];
        }
        return $out;
    }

    /**
     * Branch-by-branch PAR30 breakdown.
     */
    public static function branchBreakdown(?string $referenceDate = null, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $branches = $db->query('SELECT id, code, name, city FROM branches ORDER BY id ASC')->fetchAll();
        $result = [];
        foreach ($branches as $b) {
            $metrics = self::calculate((int) $b['id'], $referenceDate, $db);
            $result[] = array_merge($b, $metrics);
        }
        return $result;
    }
}

