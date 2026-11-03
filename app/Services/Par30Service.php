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

