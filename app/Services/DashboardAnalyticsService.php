<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;

/**
 * Live Database Dashboard & Analytics Service
 *
 * Strictly queries real database records. Never fabricates or generates random numbers.
 * Respects role-level, branch-level data isolation and dynamic date range filters.
 */
final class DashboardAnalyticsService
{
    public static function getOverviewMetrics(?array $user = null, ?PDO $pdo = null, array $dateFilter = []): array
    {
        $db = $pdo ?? Database::connection();
        $role = $user['role_slug'] ?? 'admin';
        // Same rule as PortalController::scopeSql: admin and General Manager see the whole company,
        // a client sees only their own records, everyone else only their branch. Staff with no branch
        // get an impossible branch id so they see nothing rather than the whole company.
        $companyWide = in_array($role, ['admin', 'manager'], true);
        $clientId = $role === 'client' ? (int) ($user['client_id'] ?? -1) : null;
        $branchId = (!$companyWide && $clientId === null) ? (int) ($user['branch_id'] ?? -1) : null;
        if ($branchId === 0) {
            $branchId = -1;
        }

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

        // Parse Date Filter (All, Year, Month, Specific Day)
        $periodType = $dateFilter['period'] ?? 'all';
        $filterYear = !empty($dateFilter['year']) ? (int) $dateFilter['year'] : (int) date('Y');
        $filterMonth = !empty($dateFilter['month']) ? trim((string) $dateFilter['month']) : date('Y-m');
        $filterDay = !empty($dateFilter['day']) ? trim((string) $dateFilter['day']) : date('Y-m-d');

        $dateFinDisbursed = '';
        $dateRepCollected = '';
        $dateParams = [];

        if ($periodType === 'year') {
            $dateFinDisbursed = ' AND YEAR(f.disbursed_at) = :d_year ';
            $dateRepCollected = ' AND YEAR(r.payment_date) = :d_year ';
            $dateParams[':d_year'] = $filterYear;
        } elseif ($periodType === 'month') {
            $dateFinDisbursed = ' AND DATE_FORMAT(f.disbursed_at, "%Y-%m") = :d_month ';
            $dateRepCollected = ' AND DATE_FORMAT(r.payment_date, "%Y-%m") = :d_month ';
            $dateParams[':d_month'] = $filterMonth;
        } elseif ($periodType === 'day') {
            $dateFinDisbursed = ' AND DATE(f.disbursed_at) = :d_day ';
            $dateRepCollected = ' AND DATE(r.payment_date) = :d_day ';
            $dateParams[':d_day'] = $filterDay;
        }

        $allDisbursedParams = array_merge($params, $dateParams);
        $allRepParams = array_merge($params, $dateParams);

        // 1. Total Registered Clients
        $st = $db->prepare("SELECT COUNT(*) FROM clients c WHERE c.deleted_at IS NULL {$whereClient}");
        $st->execute($params);
        $totalClients = (int) $st->fetchColumn();

        // 2. Total Disbursed (Inta Lacag la bixiyay)
        $st = $db->prepare("
            SELECT
                COALESCE(SUM(f.net_disbursed_amount), 0.00) AS total_disbursed,
                COUNT(*) AS disbursed_contracts_count
            FROM financings f
            WHERE 1=1 {$whereFin} {$dateFinDisbursed}
        ");
        $st->execute($allDisbursedParams);
        $disbursedRow = $st->fetch();

        // 3. Total Repayments Collected (Inta lasoo celiyay)
        $st = $db->prepare("
            SELECT
                COALESCE(SUM(r.allocated_to_principal + r.allocated_to_profit), 0.00) AS total_collected,
                COALESCE(SUM(r.amount), 0.00) AS gross_receipts,
                COUNT(*) AS receipts_count
            FROM repayments r
            WHERE 1=1 {$whereRep} {$dateRepCollected}
        ");
        $st->execute($allRepParams);
        $repRow = $st->fetch();

        // 4. Total Outstanding Balance (Inta maqan)
        $st = $db->prepare("
            SELECT
                COALESCE(SUM(f.outstanding_balance), 0.00) AS total_outstanding,
                COUNT(CASE WHEN f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 THEN 1 END) AS active_count
            FROM financings f
            WHERE f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 {$whereFin}
        ");
        $st->execute($params);
        $outRow = $st->fetch();

        // 5. Due Soon (Inta waqtigooda dhawyahay — within next 30 days)
        $st = $db->prepare("
            SELECT
                COUNT(DISTINCT f.id) AS due_soon_count,
                COALESCE(SUM(i.remaining_balance), 0.00) AS due_soon_balance
            FROM financings f
            INNER JOIN installments i ON i.financing_id = f.id
            WHERE f.status IN ('active', 'overdue')
              AND f.outstanding_balance > 0
              AND i.status IN ('pending', 'partial')
              AND i.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              {$whereFin}
        ");
        $st->execute($params);
        $dueSoonRow = $st->fetch();

        // 6. Overdue / Past Maturity & Unsettled (Inta deadline-koodu dhamaaday oo aan weli la soo celin)
        $st = $db->prepare("
            SELECT
                COUNT(DISTINCT f.id) AS overdue_count,
                COALESCE(SUM(f.outstanding_balance), 0.00) AS overdue_balance
            FROM financings f
            WHERE f.outstanding_balance > 0
              AND (
                  f.status = 'overdue'
                  OR f.maturity_date < CURDATE()
                  OR EXISTS (
                      SELECT 1 FROM installments i_od
                      WHERE i_od.financing_id = f.id
                        AND i_od.status IN ('pending', 'partial', 'overdue')
                        AND i_od.due_date < CURDATE()
                  )
              )
              {$whereFin}
        ");
        $st->execute($params);
        $overdueRow = $st->fetch();

        // 7. Murabaxo Breakdown (Murabaha Asset Financing)
        $st = $db->prepare("
            SELECT
                COUNT(*) AS total_count,
                COUNT(CASE WHEN f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 THEN 1 END) AS active_count,
                COUNT(CASE WHEN f.status = 'completed' THEN 1 END) AS settled_count,
                COALESCE(SUM(f.net_disbursed_amount), 0.00) AS disbursed_sum,
                COALESCE(SUM(f.total_profit), 0.00) AS markup_sum,
                COALESCE(SUM(f.total_paid), 0.00) AS paid_sum,
                COALESCE(SUM(CASE WHEN f.status IN ('active', 'overdue') THEN f.outstanding_balance ELSE 0 END), 0.00) AS outstanding_sum
            FROM financings f
            WHERE f.product_type = 'murabaha' {$whereFin}
        ");
        $st->execute($params);
        $murabahaRow = $st->fetch();

        // 8. Deyn Cad Breakdown (Qard Hassan / Zero-Riba Cash Debt)
        $st = $db->prepare("
            SELECT
                COUNT(*) AS total_count,
                COUNT(CASE WHEN f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 THEN 1 END) AS active_count,
                COUNT(CASE WHEN f.status = 'completed' THEN 1 END) AS settled_count,
                COALESCE(SUM(f.net_disbursed_amount), 0.00) AS disbursed_sum,
                COALESCE(SUM(f.total_profit), 0.00) AS profit_sum,
                COALESCE(SUM(f.total_paid), 0.00) AS paid_sum,
                COALESCE(SUM(CASE WHEN f.status IN ('active', 'overdue') THEN f.outstanding_balance ELSE 0 END), 0.00) AS outstanding_sum
            FROM financings f
            WHERE f.product_type IN ('flat', 'declining_balance') {$whereFin}
        ");
        $st->execute($params);
        $loansRow = $st->fetch();

        // 9. Pending Applications
        $st = $db->prepare("
            SELECT COUNT(*) FROM financing_applications fa
            WHERE fa.status = 'submitted' {$whereApp}
        ");
        $st->execute($params);
        $pendingApplications = (int) $st->fetchColumn();

        // 10. Segregated Charity Account Balance
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

        // 11. PAR30 Calculation
        // PAR30 is per branch: company-wide roles get the whole portfolio, everyone else (clients too) their branch.
        $par30Branch = $companyWide ? null : (((int) ($user['branch_id'] ?? 0)) ?: -1);
        $par30 = Par30Service::calculate($par30Branch, null, $db);

        // 12. Monthly Repayment Performance Chart
        $monthlyRepayments = self::getMonthlyRepaymentSeries($db, $whereRep, $params);

        // 13. Financing by Product Type Chart
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

        return [
            'total_clients' => $totalClients,
            'total_disbursed_amount' => number_format((float) ($disbursedRow['total_disbursed'] ?? 0), 2, '.', ''),
            'disbursed_contracts_count' => (int) ($disbursedRow['disbursed_contracts_count'] ?? 0),
            'total_repayments_collected' => number_format((float) ($repRow['total_collected'] ?? 0), 2, '.', ''),
            'gross_receipts' => number_format((float) ($repRow['gross_receipts'] ?? 0), 2, '.', ''),
            'receipts_count' => (int) ($repRow['receipts_count'] ?? 0),
            'total_outstanding_balance' => number_format((float) ($outRow['total_outstanding'] ?? 0), 2, '.', ''),
            'active_financing_count' => (int) ($outRow['active_count'] ?? 0),
            'due_soon_count' => (int) ($dueSoonRow['due_soon_count'] ?? 0),
            'due_soon_balance' => number_format((float) ($dueSoonRow['due_soon_balance'] ?? 0), 2, '.', ''),
            'overdue_unsettled_count' => (int) ($overdueRow['overdue_count'] ?? 0),
            'overdue_unsettled_balance' => number_format((float) ($overdueRow['overdue_balance'] ?? 0), 2, '.', ''),
            'pending_applications_count' => $pendingApplications,
            'par30_percentage' => $par30['par30_percentage'],
            'par30_risky_balance' => $par30['risky_outstanding_balance'],
            'charity_account_balance' => number_format((float) ($charityRow['charity_collected'] ?? 0), 2, '.', ''),
            'charity_pending_balance' => number_format((float) ($charityRow['charity_pending'] ?? 0), 2, '.', ''),
            'charity_waived_total' => number_format((float) ($charityRow['charity_waived'] ?? 0), 2, '.', ''),
            'date_filter' => [
                'period' => $periodType,
                'year' => $filterYear,
                'month' => $filterMonth,
                'day' => $filterDay,
            ],
            'murabaha' => [
                'total_count' => (int) ($murabahaRow['total_count'] ?? 0),
                'active_count' => (int) ($murabahaRow['active_count'] ?? 0),
                'settled_count' => (int) ($murabahaRow['settled_count'] ?? 0),
                'disbursed' => number_format((float) ($murabahaRow['disbursed_sum'] ?? 0), 2, '.', ''),
                'markup' => number_format((float) ($murabahaRow['markup_sum'] ?? 0), 2, '.', ''),
                'collected' => number_format((float) ($murabahaRow['paid_sum'] ?? 0), 2, '.', ''),
                'outstanding' => number_format((float) ($murabahaRow['outstanding_sum'] ?? 0), 2, '.', ''),
            ],
            'loans' => [
                'total_count' => (int) ($loansRow['total_count'] ?? 0),
                'active_count' => (int) ($loansRow['active_count'] ?? 0),
                'settled_count' => (int) ($loansRow['settled_count'] ?? 0),
                'disbursed' => number_format((float) ($loansRow['disbursed_sum'] ?? 0), 2, '.', ''),
                'profit' => number_format((float) ($loansRow['profit_sum'] ?? 0), 2, '.', ''),
                'collected' => number_format((float) ($loansRow['paid_sum'] ?? 0), 2, '.', ''),
                'outstanding' => number_format((float) ($loansRow['outstanding_sum'] ?? 0), 2, '.', ''),
            ],
            'charts' => [
                'monthly_repayments' => $monthlyRepayments,
                'by_product_type' => $byProductType,
                'murabaha_vs_loans' => [
                    'categories' => ['Murabaxo (Murabaha)', 'Deyn Cad (Qard Hassan)'],
                    'disbursed' => [(float) ($murabahaRow['disbursed_sum'] ?? 0), (float) ($loansRow['disbursed_sum'] ?? 0)],
                    'collected' => [(float) ($murabahaRow['paid_sum'] ?? 0), (float) ($loansRow['paid_sum'] ?? 0)],
                    'outstanding' => [(float) ($murabahaRow['outstanding_sum'] ?? 0), (float) ($loansRow['outstanding_sum'] ?? 0)],
                ],
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
            ORDER BY ym DESC
            LIMIT 12
        ");
        $stmt->execute($params);
        // Latest 12 months, displayed oldest to newest.
        return array_reverse($stmt->fetchAll());
    }
}
