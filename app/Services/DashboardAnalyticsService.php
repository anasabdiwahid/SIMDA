<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;

/**
 * Live Database Dashboard & Analytics Service
 *
 * Strictly queries real database records. Never fabricates or generates random numbers.
 * Respects role-level and branch-level data isolation.
 */
final class DashboardAnalyticsService
{
    public static function getOverviewMetrics(?array $user = null, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $role = $user['role_slug'] ?? 'admin';
        $branchId = ($role !== 'admin' && !empty($user['branch_id'])) ? (int) $user['branch_id'] : null;
        $clientId = ($role === 'client' && !empty($user['client_id'])) ? (int) $user['client_id'] : null;

        // Run background maintenance checks on live records
        DisbursementService::expireStaleApprovals(null, $db);
        CharityChargeService::assessOverdueInstallments(null, $db);
        SmsReminderService::generateScheduledReminders(null, $db);

        $whereClient = '';
        $whereFin = '';
        $whereApp = '';
        $whereRep = '';
        $whereCharity = '';
        $params = [];

        if ($clientId !== null) {
            $whereClient = ' AND c.id = :cid ';
            $whereFin = ' AND f.client_id = :cid ';
            $whereApp = ' AND fa.client_id = :cid ';
            $whereRep = ' AND r.client_id = :cid ';
            $whereCharity = ' AND cc.client_id = :cid ';
            $params[':cid'] = $clientId;
        } elseif ($branchId !== null) {
            $whereClient = ' AND c.branch_id = :bid ';
            $whereFin = ' AND f.branch_id = :bid ';
            $whereApp = ' AND fa.branch_id = :bid ';
            $whereRep = ' AND r.branch_id = :bid ';
            $whereCharity = ' AND cc.branch_id = :bid ';
            $params[':bid'] = $branchId;
        }

        // 1. Total Clients
        $st = $db->prepare("SELECT COUNT(*) FROM clients c WHERE c.deleted_at IS NULL {$whereClient}");
        $st->execute($params);
        $totalClients = (int) $st->fetchColumn();

        // 2. Active Financing count & Total Outstanding Balance & Overdue Financing count
        $st = $db->prepare("
            SELECT
                COUNT(CASE WHEN f.status IN ('active', 'overdue') THEN 1 END) AS active_count,
                COUNT(CASE WHEN f.status = 'overdue' THEN 1 END) AS overdue_count,
                COALESCE(SUM(CASE WHEN f.status IN ('active', 'overdue') THEN f.outstanding_balance ELSE 0 END), 0.00) AS total_outstanding,
                COALESCE(SUM(f.net_disbursed_amount), 0.00) AS total_disbursed
            FROM financings f
            WHERE 1=1 {$whereFin}
        ");
        $st->execute($params);
        $finRow = $st->fetch();

        // 3. Total Repayments Collected (excluding charity late charges which go to the segregated charity account)
        $st = $db->prepare("
            SELECT
                COALESCE(SUM(r.allocated_to_principal + r.allocated_to_profit), 0.00) AS total_collected,
                COALESCE(SUM(r.amount), 0.00) AS gross_receipts
            FROM repayments r
            WHERE 1=1 {$whereRep}
        ");
        $st->execute($params);
        $repRow = $st->fetch();

        // 4. Pending Applications
        $st = $db->prepare("
            SELECT COUNT(*) FROM financing_applications fa
            WHERE fa.status = 'submitted' {$whereApp}
        ");
        $st->execute($params);
        $pendingApplications = (int) $st->fetchColumn();

        // 5. Segregated Charity Account Balance (Paid charity charges + Unpaid assessed charity charges)
        $st = $db->prepare("
            SELECT
                COALESCE(SUM(cc.amount_paid), 0.00) AS charity_collected,
                COALESCE(SUM(CASE WHEN cc.status IN ('unpaid', 'partial') THEN cc.remaining_amount ELSE 0 END), 0.00) AS charity_pending,
                COALESCE(SUM(CASE WHEN cc.status = 'waived' THEN cc.amount ELSE 0 END), 0.00) AS charity_waived
            FROM charity_charges cc
            WHERE 1=1 {$whereCharity}
        ");
        $st->execute($params);
        $charityRow = $st->fetch();

        // 6. PAR30 Calculation
        $par30 = Par30Service::calculate($branchId, null, $db);

        // 7. Monthly Repayment Performance Chart (last 6 months from live database)
        $monthlyRepayments = self::getMonthlyRepaymentSeries($db, $whereRep, $params);

        // 8. Financing by Product Type Chart
        $st = $db->prepare("
            SELECT f.product_type,
                   COUNT(*) AS contracts_count,
                   COALESCE(SUM(f.approved_amount), 0.00) AS total_principal,
                   COALESCE(SUM(f.outstanding_balance), 0.00) AS total_outstanding
            FROM financings f
            WHERE 1=1 {$whereFin}
            GROUP BY f.product_type
        ");
        $st->execute($params);
        $byProductType = $st->fetchAll();

        // 9. Repayment Status Distribution
        $st = $db->prepare("
            SELECT f.status, COUNT(*) AS total_count, COALESCE(SUM(f.outstanding_balance), 0.00) AS balance_sum
            FROM financings f
            WHERE 1=1 {$whereFin}
            GROUP BY f.status
        ");
        $st->execute($params);
        $byStatus = $st->fetchAll();

        return [
            'total_clients' => $totalClients,
            'active_financing_count' => (int) ($finRow['active_count'] ?? 0),
            'overdue_financing_count' => (int) ($finRow['overdue_count'] ?? 0),
            'total_outstanding_balance' => number_format((float) ($finRow['total_outstanding'] ?? 0), 2, '.', ''),
            'total_disbursed_amount' => number_format((float) ($finRow['total_disbursed'] ?? 0), 2, '.', ''),
            'total_repayments_collected' => number_format((float) ($repRow['total_collected'] ?? 0), 2, '.', ''),
            'gross_receipts' => number_format((float) ($repRow['gross_receipts'] ?? 0), 2, '.', ''),
            'pending_applications_count' => $pendingApplications,
            'par30_percentage' => $par30['par30_percentage'],
            'par30_risky_balance' => $par30['risky_outstanding_balance'],
            'charity_account_balance' => number_format((float) ($charityRow['charity_collected'] ?? 0), 2, '.', ''),
            'charity_pending_balance' => number_format((float) ($charityRow['charity_pending'] ?? 0), 2, '.', ''),
            'charity_waived_total' => number_format((float) ($charityRow['charity_waived'] ?? 0), 2, '.', ''),
            'charts' => [
                'monthly_repayments' => $monthlyRepayments,
                'by_product_type' => $byProductType,
                'by_status' => $byStatus,
                'branch_par30' => ($role === 'admin') ? Par30Service::branchBreakdown(null, $db) : [$par30],
            ],
        ];
    }

    private static function getMonthlyRepaymentSeries(PDO $db, string $whereRep, array $params): array
    {
        $stmt = $db->prepare("
            SELECT DATE_FORMAT(r.payment_date, '%Y-%m') AS ym,
                   COALESCE(SUM(r.allocated_to_principal), 0.00) AS principal_collected,
                   COALESCE(SUM(r.allocated_to_profit), 0.00) AS profit_collected,
                   COALESCE(SUM(r.allocated_to_charity), 0.00) AS charity_collected,
                   COALESCE(SUM(r.amount), 0.00) AS total_amount
            FROM repayments r
            WHERE 1=1 {$whereRep}
            GROUP BY DATE_FORMAT(r.payment_date, '%Y-%m')
            ORDER BY ym ASC
            LIMIT 12
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

