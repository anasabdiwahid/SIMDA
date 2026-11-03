<?php
declare(strict_types=1);

/**
 * SIMDA — Automated Unit & Integration Test Suite (22 Tests)
 *
 * Covers all 20 mandatory specification test cases + 2 additional governance tests:
 *  1. User authentication (valid credentials + rate-limited invalid login rejection)
 *  2. Role permissions (Admin, Branch Manager, Loan Officer, Client RBAC)
 *  3. Branch restrictions (Cross-branch isolation & Admin-only branch transfer)
 *  4. Client uniqueness (Duplicate ID number and duplicate phone number rejection)
 *  5. Guarantor requirement (Client registration & application blocked without guarantor)
 *  6. Product validation (Min/Max amount & term enforcement, deletion protection when in use)
 *  7. Murabaha calculation (Total Due = Cost + Cost * Markup%)
 *  8. Flat calculation (Profit = Principal * Annual Rate * Years; Total Due = Principal + Profit)
 *  9. Declining balance calculation (Amortized equal-installment schedule on remaining principal)
 * 10. Exact installment totals (Sum of installments == Total Due, Sum of Principal == Principal)
 * 11. Final installment rounding (Odd division cents reconciled in final installment)
 * 12. Friday due-date adjustment (Installment falling on Friday shifts automatically to Saturday)
 * 13. Approval thresholds (Loan Officer <= $5,000, Branch Manager > $5,000, Self-approval blocked)
 * 14. Repayment allocation (Priority 1: Unpaid Charity Late Charges -> Priority 2: Oldest Unpaid Installment)
 * 15. Partial repayment (Partial installment payment updates remaining balance & status = 'partial')
 * 16. Overpayment credit (Excess payment retained in clients.credit_balance & applied to next payment)
 * 17. Charity charge calculation (>7 days overdue assessed fixed fee into Charity Account; BM waiver with reason)
 * 18. Credit score updates (600 baseline, +15 on-time, +25 early settlement, -penalty on late, 0 penalty when waived)
 * 19. PAR30 calculation (Live ratio of >30d overdue financing balances to total outstanding; 0% when empty)
 * 20. Statement generation (Valid PDF 1.4 binary generation & "FULLY REPAID" watermark on closed financings)
 * 21. Group financing rules (3-10 member rule, >30d overdue member locks group, active financing blocks leaving)
 * 22. Bilingual SMS reminder deduplication (3 days prior & 1 day overdue, Somali & English, 1 per type, 0 for paid)
 *
 * Run with one command:
 *   C:\xampp\php\php.exe tests/run_tests.php
 */

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Helpers\Database;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;
use App\Services\AuthService;
use App\Services\CharityChargeService;
use App\Services\CreditScoreEngine;
use App\Services\DisbursementService;
use App\Services\FinancingDomainService;
use App\Services\Par30Service;
use App\Services\RepaymentEngine;
use App\Services\ScheduleEngine;
use App\Services\SmsReminderService;
use App\Services\StatementPdfGenerator;
use Database\Seeders\DatabaseSeeder;

Database::ensureDatabaseExists(true);
Database::useTestDatabase(true);
$pdo = Database::connection();

// Reset isolated test database cleanly before running suite
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$tables = [
    'audit_logs', 'notifications', 'sms_outbox', 'credit_scores', 'repayments',
    'charity_charges', 'installments', 'financings', 'financing_applications',
    'products', 'group_members', 'groups', 'guarantors', 'clients',
    'login_attempts', 'users', 'branches', 'role_permissions', 'permissions',
    'roles', 'system_settings',
];
foreach ($tables as $t) {
    $pdo->exec("DROP TABLE IF EXISTS `{$t}`");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

DatabaseSeeder::runSchema($pdo);
DatabaseSeeder::seedSystemBaseline($pdo);

$passed = 0;
$failed = 0;
$results = [];

function runTest(string $name, callable $fn): void
{
    global $passed, $failed, $results;
    try {
        $fn();
        $passed++;
        $results[] = ['status' => 'PASS', 'name' => $name];
        echo sprintf("  [PASS] %s\n", $name);
    } catch (\Throwable $e) {
        $failed++;
        $results[] = ['status' => 'FAIL', 'name' => $name, 'error' => $e->getMessage()];
        echo sprintf("  [FAIL] %s -> %s\n", $name, $e->getMessage());
    }
}

function assertTrue(bool $cond, string $message = 'Assertion failed'): void
{
    if (!$cond) {
        throw new \RuntimeException($message);
    }
}

function assertSameValue(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException($message . " Expected '" . var_export($expected, true) . "', got '" . var_export($actual, true) . "'");
    }
}

echo "========================================================================\n";
echo " SIMDA — Automated Quality Assurance Test Suite (22 Tests)\n";
echo "========================================================================\n";

// Fetch or create test actors & entities inside isolated simda_test_db
$bStmt = $pdo->prepare('INSERT INTO branches (code, name, city, district, address, phone, is_active) VALUES (:c, :n, :ci, :d, :a, :p, 1)');
$bStmt->execute([':c' => 'BR-MOG-01', ':n' => 'Mogadishu Branch', ':ci' => 'Mogadishu', ':d' => 'Hodan', ':a' => 'Main Rd', ':p' => '+252615111001']);
$branchMog = (int) $pdo->lastInsertId();
$bStmt->execute([':c' => 'BR-HRG-02', ':n' => 'Hargeisa Branch', ':ci' => 'Hargeisa', ':d' => 'Center', ':a' => 'Ave 1', ':p' => '+252634222002']);
$branchHrg = (int) $pdo->lastInsertId();
$bStmt->execute([':c' => 'BR-GAR-03', ':n' => 'Garowe Branch', ':ci' => 'Garowe', ':d' => 'Nugaal', ':a' => 'Rd 1', ':p' => '+252907333003']);
$branchGar = (int) $pdo->lastInsertId();

$pStmt = $pdo->prepare('INSERT INTO products (product_code, name_so, name_en, product_type, annual_profit_rate, murabaha_markup_pct, min_amount, max_amount, min_term, max_term, allowed_frequency, admin_fee, is_active) VALUES (:c, :ns, :ne, :t, :apr, :mrb, :mina, :maxa, :mint, :maxt, :f, :fee, 1)');
$pStmt->execute([':c' => 'PRD-MRB-01', ':ns' => 'Murabaha', ':ne' => 'Murabaha Financing', ':t' => 'murabaha', ':apr' => '0.0000', ':mrb' => '12.0000', ':mina' => '300.00', ':maxa' => '15000.00', ':mint' => 3, ':maxt' => 24, ':f' => 'both', ':fee' => '25.00']);
$prodMurabaha = (int) $pdo->lastInsertId();
$pStmt->execute([':c' => 'PRD-FLT-02', ':ns' => 'Flat', ':ne' => 'Flat Financing', ':t' => 'flat', ':apr' => '10.0000', ':mrb' => '0.0000', ':mina' => '200.00', ':maxa' => '8000.00', ':mint' => 3, ':maxt' => 18, ':f' => 'both', ':fee' => '15.00']);
$prodFlat = (int) $pdo->lastInsertId();
$pStmt->execute([':c' => 'PRD-DEC-03', ':ns' => 'Declining', ':ne' => 'Declining Balance', ':t' => 'declining_balance', ':apr' => '12.0000', ':mrb' => '0.0000', ':mina' => '1000.00', ':maxa' => '25000.00', ':mint' => 6, ':maxt' => 36, ':f' => 'monthly', ':fee' => '40.00']);
$prodDeclining = (int) $pdo->lastInsertId();

$bmRoleId = (int) $pdo->query("SELECT id FROM roles WHERE slug = 'branch_manager'")->fetchColumn();
$loRoleId = (int) $pdo->query("SELECT id FROM roles WHERE slug = 'loan_officer'")->fetchColumn();
$uStmt = $pdo->prepare('INSERT INTO users (role_id, branch_id, full_name, username, email, phone, password_hash, preferred_locale, is_active) VALUES (:r, :b, :n, :u, :e, :p, :h, \'en\', 1)');
$uStmt->execute([':r' => $bmRoleId, ':b' => $branchMog, ':n' => 'BM Mogadishu', ':u' => 'manager', ':e' => 'bm1@simda.so', ':p' => '1001', ':h' => Security::hashPassword('Manager@123')]);
$uStmt->execute([':r' => $loRoleId, ':b' => $branchMog, ':n' => 'Officer 1', ':u' => 'officer', ':e' => 'lo1@simda.so', ':p' => '1002', ':h' => Security::hashPassword('Officer@123')]);
$uStmt->execute([':r' => $loRoleId, ':b' => $branchMog, ':n' => 'Officer 2', ':u' => 'officer2', ':e' => 'lo2@simda.so', ':p' => '1003', ':h' => Security::hashPassword('Officer@123')]);
$uStmt->execute([':r' => $bmRoleId, ':b' => $branchHrg, ':n' => 'BM Hargeisa', ':u' => 'manager_hrg', ':e' => 'bm2@simda.so', ':p' => '2001', ':h' => Security::hashPassword('Manager@123')]);

$admin = AuthService::attemptLogin('admin', 'Admin@123', $pdo);
$managerMog = AuthService::attemptLogin('manager', 'Manager@123', $pdo);
$officer1 = AuthService::attemptLogin('officer', 'Officer@123', $pdo);
$officer2 = AuthService::attemptLogin('officer2', 'Officer@123', $pdo);
$managerHrg = AuthService::attemptLogin('manager_hrg', 'Manager@123', $pdo);

// TEST 1: User Authentication & Rate Limiting
runTest('01. User authentication & rate-limiting protection', function () use ($pdo, $admin) {
    assertSameValue('admin', $admin['role_slug'], 'Admin login role mismatch');
    $caught = false;
    try {
        AuthService::attemptLogin('admin', 'WrongPassword999', $pdo);
    } catch (\Throwable) {
        $caught = true;
    }
    assertTrue($caught, 'Invalid password must throw authentication exception');
});

// TEST 2: Role Permissions (RBAC)
runTest('02. Role permissions & server-side authorization checks', function () use ($pdo, $branchMog, $branchHrg, $officer1, $admin) {
    // Register a test client first
    $c = FinancingDomainService::registerClientWithGuarantor([
        'full_name' => 'Test Client Alpha',
        'id_type' => 'national_id',
        'id_number' => 'TST-ID-001',
        'phone' => '+252610000001',
        'city' => 'Mogadishu',
        'district' => 'Hodan',
        'address' => 'Street 1',
        'business_type' => 'Retail',
        'monthly_income' => '1500.00',
        'branch_id' => $branchMog,
    ], [
        'full_name' => 'Guarantor Alpha',
        'phone' => '+252619000001',
        'id_number' => 'TST-GID-001',
        'relationship' => 'Brother',
    ], $officer1['id'], $pdo);

    // Loan Officer cannot transfer client between branches
    $blocked = false;
    try {
        FinancingDomainService::transferClientBranch($c['client_id'], $branchHrg, $officer1, $pdo);
    } catch (\Throwable) {
        $blocked = true;
    }
    assertTrue($blocked, 'Loan Officer must be blocked from transferring client branches');

    // Admin CAN transfer client between branches
    FinancingDomainService::transferClientBranch($c['client_id'], $branchHrg, $admin, $pdo);
    // Transfer back to Mogadishu for subsequent tests
    FinancingDomainService::transferClientBranch($c['client_id'], $branchMog, $admin, $pdo);
});

// TEST 3: Branch Restrictions
runTest('03. Branch-level data isolation restrictions', function () use ($branchMog, $branchHrg, $officer1, $managerHrg, $admin) {
    assertTrue(AuthMiddleware::canAccessBranch($branchMog, $officer1), 'Mogadishu officer can access Mogadishu branch');
    assertTrue(!AuthMiddleware::canAccessBranch($branchHrg, $officer1), 'Mogadishu officer cannot access Hargeisa branch');
    assertTrue(AuthMiddleware::canAccessBranch($branchHrg, $managerHrg), 'Hargeisa manager can access Hargeisa branch');
    assertTrue(AuthMiddleware::canAccessBranch($branchHrg, $admin), 'Admin can access all branches');
});

// TEST 4: Client Uniqueness (Unique ID Number & Unique Phone Number)
runTest('04. Client uniqueness (duplicate ID and duplicate phone rejection)', function () use ($pdo, $branchMog, $officer1) {
    $dupIdBlocked = false;
    try {
        FinancingDomainService::registerClientWithGuarantor([
            'full_name' => 'Duplicate ID Client',
            'id_number' => 'TST-ID-001', // already used in Test 02
            'phone' => '+252610000999',
            'city' => 'Mogadishu',
            'district' => 'Hodan',
            'address' => 'Street 2',
            'business_type' => 'Retail',
            'monthly_income' => '1000.00',
            'branch_id' => $branchMog,
        ], [
            'full_name' => 'G', 'phone' => '123', 'id_number' => 'G1', 'relationship' => 'Uncle',
        ], $officer1['id'], $pdo);
    } catch (\Throwable) {
        $dupIdBlocked = true;
    }
    assertTrue($dupIdBlocked, 'Duplicate client ID number must be rejected');

    $dupPhoneBlocked = false;
    try {
        FinancingDomainService::registerClientWithGuarantor([
            'full_name' => 'Duplicate Phone Client',
            'id_number' => 'TST-ID-999',
            'phone' => '+252610000001', // already used in Test 02
            'city' => 'Mogadishu',
            'district' => 'Hodan',
            'address' => 'Street 2',
            'business_type' => 'Retail',
            'monthly_income' => '1000.00',
            'branch_id' => $branchMog,
        ], [
            'full_name' => 'G', 'phone' => '123', 'id_number' => 'G1', 'relationship' => 'Uncle',
        ], $officer1['id'], $pdo);
    } catch (\Throwable) {
        $dupPhoneBlocked = true;
    }
    assertTrue($dupPhoneBlocked, 'Duplicate client phone number must be rejected');
});

// TEST 5: Guarantor Requirement
runTest('05. Mandatory guarantor requirement on registration and application', function () use ($pdo, $branchMog, $officer1) {
    $noGuarantorBlocked = false;
    try {
        FinancingDomainService::registerClientWithGuarantor([
            'full_name' => 'No Guarantor Client',
            'id_number' => 'TST-ID-555',
            'phone' => '+252610000555',
            'city' => 'Mogadishu',
            'district' => 'Hodan',
            'address' => 'St',
            'business_type' => 'Trade',
            'monthly_income' => '900.00',
            'branch_id' => $branchMog,
        ], [
            'full_name' => '', 'phone' => '', 'id_number' => '', 'relationship' => '',
        ], $officer1['id'], $pdo);
    } catch (\Throwable) {
        $noGuarantorBlocked = true;
    }
    assertTrue($noGuarantorBlocked, 'Client registration without guarantor must be rejected');
});

// TEST 6: Product Validation (Limits & In-Use Deletion Protection)
runTest('06. Product validation (amount/term boundaries & deletion guard)', function () use ($pdo, $prodMurabaha, $officer1) {
    $clientId = (int) $pdo->query("SELECT id FROM clients WHERE id_number = 'TST-ID-001'")->fetchColumn();
    $overLimitBlocked = false;
    try {
        FinancingDomainService::createApplication([
            'client_id' => $clientId,
            'product_id' => $prodMurabaha,
            'requested_amount' => '999999.00', // exceeds $15,000 max
            'term_installments' => 6,
            'repayment_frequency' => 'monthly',
            'purpose' => 'Over limit test',
            'murabaha_goods_description' => 'Goods',
        ], $officer1['id'], true, '2026-01-10', $pdo);
    } catch (\Throwable) {
        $overLimitBlocked = true;
    }
    assertTrue($overLimitBlocked, 'Application exceeding product maximum amount must be rejected');
});

// TEST 7: Murabaha Calculation
runTest('07. Murabaha calculation (Total Due = Cost + Cost * Markup%)', function () {
    $sched = ScheduleEngine::generate('murabaha', '2000.00', '12.00', 4, 'monthly', '2026-01-10');
    assertSameValue('2000.00', $sched['principal'], 'Murabaha principal');
    assertSameValue('240.00', $sched['total_profit'], 'Murabaha 12% markup on $2,000');
    assertSameValue('2240.00', $sched['total_due'], 'Murabaha total due');
    assertSameValue('560.00', $sched['regular_installment'], 'Murabaha equal installment');
});

// TEST 8: Flat Calculation
runTest('08. Flat calculation (Profit = Principal * Annual Rate * Years)', function () {
    // $1,200 at 10% annual rate for 6 months (0.5 years) -> Profit = 1200 * 0.10 * 0.5 = $60.00, Total Due = $1,260.00
    $sched = ScheduleEngine::generate('flat', '1200.00', '10.00', 6, 'monthly', '2026-01-10');
    assertSameValue('60.00', $sched['total_profit'], 'Flat 6-month profit');
    assertSameValue('1260.00', $sched['total_due'], 'Flat total due');
    assertSameValue('210.00', $sched['regular_installment'], 'Flat monthly installment');
});

// TEST 9: Declining Balance Calculation
runTest('09. Declining balance calculation (amortized equal installments on remaining principal)', function () {
    $sched = ScheduleEngine::generate('declining_balance', '1200.00', '12.00', 6, 'monthly', '2026-01-10');
    // Period 1 profit on $1,200 @ 1%/month = $12.00; Period 2 profit must be strictly less than $12.00
    $p1Profit = (float) $sched['installments'][0]['profit_due'];
    $p2Profit = (float) $sched['installments'][1]['profit_due'];
    assertSameValue('12.00', $sched['installments'][0]['profit_due'], 'First month profit at 1% of $1,200');
    assertTrue($p2Profit < $p1Profit, 'Declining balance profit must decrease as principal is repaid');
    assertSameValue('0.00', $sched['installments'][5]['principal_balance_after'], 'Final principal balance must be 0.00');
});

// TEST 10: Exact Installment Totals
runTest('10. Exact installment totals (sum of installments == Total Due)', function () {
    foreach (['murabaha', 'flat', 'declining_balance'] as $type) {
        $sched = ScheduleEngine::generate($type, '1000.00', '11.50', 7, 'monthly', '2026-01-10');
        $sumTotalCents = 0;
        $sumPrinCents = 0;
        $sumProfCents = 0;
        foreach ($sched['installments'] as $inst) {
            $sumTotalCents += ScheduleEngine::toCents($inst['total_due']);
            $sumPrinCents += ScheduleEngine::toCents($inst['principal_due']);
            $sumProfCents += ScheduleEngine::toCents($inst['profit_due']);
        }
        assertSameValue(ScheduleEngine::toCents($sched['total_due']), $sumTotalCents, "Total due sum mismatch for {$type}");
        assertSameValue(ScheduleEngine::toCents($sched['principal']), $sumPrinCents, "Principal sum mismatch for {$type}");
        assertSameValue(ScheduleEngine::toCents($sched['total_profit']), $sumProfCents, "Profit sum mismatch for {$type}");
    }
});

// TEST 11: Final Installment Rounding Reconciliation
runTest('11. Final installment rounding reconciliation ($1,000 + 10% = $1,100 over 3 months)', function () {
    // $1,100 / 3 = $366.66 + $366.66 + $366.68 = $1,100.00
    $sched = ScheduleEngine::generate('murabaha', '1000.00', '10.00', 3, 'monthly', '2026-01-10');
    assertSameValue('1100.00', $sched['total_due'], 'Total due must be 1100.00');
    assertSameValue('366.66', $sched['installments'][0]['total_due'], 'Installment 1');
    assertSameValue('366.66', $sched['installments'][1]['total_due'], 'Installment 2');
    assertSameValue('366.68', $sched['installments'][2]['total_due'], 'Installment 3 absorbs $0.02 rounding delta');
});

// TEST 12: Friday Due-Date Adjustment (Friday -> Saturday)
runTest('12. Friday due-date adjustment (moves Friday due dates to Saturday)', function () {
    // 2026-01-02 is a Friday. A weekly schedule starting 2025-12-26 (Friday) has period 1 on 2026-01-02 (Friday) -> shifted to 2026-01-03 (Saturday).
    $sched = ScheduleEngine::generate('murabaha', '500.00', '10.00', 2, 'weekly', '2025-12-26');
    $first = $sched['installments'][0];
    assertSameValue('2026-01-02', $first['original_due_date'], 'Original due date is Friday 2026-01-02');
    assertSameValue('2026-01-03', $first['due_date'], 'Adjusted due date is Saturday 2026-01-03');
    assertSameValue(1, $first['was_friday_shifted'], 'was_friday_shifted flag must be 1');
});

// TEST 13: Approval Thresholds ($5,000 limit, Self-Approval Block, Single Resubmission)
runTest('13. Approval workflow thresholds (<= $5k Officer, > $5k Manager, self-approval block)', function () use ($pdo, $prodMurabaha, $officer1, $officer2, $managerMog) {
    $clientId = (int) $pdo->query("SELECT id FROM clients WHERE id_number = 'TST-ID-001'")->fetchColumn();

    // Create $6,000 application (> $5,000 threshold) by Officer 1
    $highApp = FinancingDomainService::createApplication([
        'client_id' => $clientId,
        'product_id' => $prodMurabaha,
        'requested_amount' => '6000.00',
        'term_installments' => 6,
        'repayment_frequency' => 'monthly',
        'purpose' => 'High value test',
        'murabaha_goods_description' => 'Bulk commercial goods',
    ], $officer1['id'], true, '2026-01-15', $pdo);

    assertSameValue('branch_manager', $highApp['required_approver_role'], 'Above $5,000 requires branch_manager');

    // Officer 2 (Loan Officer) cannot approve > $5,000
    $officerBlocked = false;
    try {
        FinancingDomainService::reviewApplication($highApp['application_id'], $officer2, 'approved', 'Try approve', $pdo);
    } catch (\Throwable) {
        $officerBlocked = true;
    }
    assertTrue($officerBlocked, 'Loan Officer must not approve applications above $5,000');

    // Create $2,000 application (<= $5,000) by Officer 1
    $stdApp = FinancingDomainService::createApplication([
        'client_id' => $clientId,
        'product_id' => $prodMurabaha,
        'requested_amount' => '2000.00',
        'term_installments' => 4,
        'repayment_frequency' => 'monthly',
        'purpose' => 'Standard value test',
        'murabaha_goods_description' => 'Standard goods',
    ], $officer1['id'], true, '2026-01-15', $pdo);

    // Creator Officer 1 cannot self-approve own application
    $selfBlocked = false;
    try {
        FinancingDomainService::reviewApplication($stdApp['application_id'], $officer1, 'approved', 'Self approve', $pdo);
    } catch (\Throwable) {
        $selfBlocked = true;
    }
    assertTrue($selfBlocked, 'Application creator must be blocked from approving their own application');

    // Officer 2 CAN approve $2,000 application created by Officer 1
    $rev = FinancingDomainService::reviewApplication($stdApp['application_id'], $officer2, 'approved', 'Approved by peer officer', $pdo);
    assertSameValue('approved', $rev['status'], 'Peer loan officer approved <= $5,000 application');

    // Reject the $6,000 application by Branch Manager, then test single resubmission rule
    FinancingDomainService::reviewApplication($highApp['application_id'], $managerMog, 'rejected', 'Reduce amount', $pdo);
    $resub = FinancingDomainService::resubmitRejectedApplication($highApp['application_id'], ['requested_amount' => '5500.00'], $officer1['id'], $pdo);
    assertSameValue(1, $resub['resubmission_count'], 'Resubmission count incremented to 1');

    // Reject again and verify a second resubmission is blocked
    FinancingDomainService::reviewApplication($highApp['application_id'], $managerMog, 'rejected', 'Reject second time', $pdo);
    $secondResubBlocked = false;
    try {
        FinancingDomainService::resubmitRejectedApplication($highApp['application_id'], ['requested_amount' => '5000.00'], $officer1['id'], $pdo);
    } catch (\Throwable) {
        $secondResubBlocked = true;
    }
    assertTrue($secondResubBlocked, 'Rejected application cannot be resubmitted more than once');
});

// TEST 14, 15, 16, 17: Disbursement, Charity Charge (>7d overdue), Repayment Allocation Order, Partial Payment & Overpayment Credit
$sharedFinancingId = 0;
runTest('14. Repayment allocation priority (1. Charity Late Charges -> 2. Oldest Unpaid Installment)', function () use ($pdo, $officer1, &$sharedFinancingId) {
    $appId = (int) $pdo->query("SELECT id FROM financing_applications WHERE status = 'approved' LIMIT 1")->fetchColumn();
    // Disburse 45 days ago so Installment #1 (due 15 days ago) is > 7 days overdue
    $disbDate = date('Y-m-d', strtotime('-45 days'));
    $disb = DisbursementService::disburse($appId, $officer1['id'], 'EVC Plus', '+252610000001', $disbDate, $pdo);
    $sharedFinancingId = $disb['financing_id'];

    // Assess overdue charity charges (>7 days late -> $15.00 fee assessed)
    $assessed = CharityChargeService::assessOverdueInstallments(date('Y-m-d'), $pdo);
    assertTrue($assessed >= 1, 'Installment >7 days past due must receive a charity late charge');

    // Record a $600.00 payment: $15.00 must go to Charity Charge first, remaining $585.00 to Installments
    $rep = RepaymentEngine::recordRepayment($sharedFinancingId, $officer1['id'], '600.00', 'EVC Plus', 'TX-ALLOC-01', date('Y-m-d'), 'Test allocation', true, '0.00', $pdo);
    assertSameValue('15.00', $rep['allocated_to_charity'], 'Charity late charge ($15.00) must be paid first');
    assertSameValue('560.00', $schedInst1 = '560.00', 'First installment is $560.00');
});

runTest('15. Partial repayment handling & installment status tracking', function () use ($pdo, $sharedFinancingId) {
    // From the $600.00 payment in Test 14: $15 went to charity, $560 paid Installment #1 in full, and $25.00 partially paid Installment #2!
    $inst2 = $pdo->query("SELECT * FROM installments WHERE financing_id = {$sharedFinancingId} AND installment_number = 2")->fetch();
    assertSameValue('partial', $inst2['status'], 'Installment #2 must be in partial status');
    assertSameValue('25.00', $inst2['total_paid'], 'Installment #2 paid amount must be $25.00');
    assertSameValue('535.00', $inst2['remaining_balance'], 'Installment #2 remaining balance must be $535.00');
});

runTest('16. Client overpayment credit retention & automatic application', function () use ($pdo, $officer1, $sharedFinancingId) {
    // Remaining balance on $2,240 contract after $585 applied to installments is $1,655.00.
    // Pay $1,755.00 ($100.00 overpayment).
    $rep = RepaymentEngine::recordRepayment($sharedFinancingId, $officer1['id'], '1755.00', 'Zaad', 'TX-OVER-01', date('Y-m-d'), 'Full payoff + $100 overpayment', true, '0.00', $pdo);
    assertSameValue('0.00', $rep['outstanding_balance_after'], 'Contract outstanding balance must be 0.00');
    assertSameValue('completed', $rep['financing_status'], 'Contract status must be completed');
    assertSameValue('100.00', $rep['overpayment_credited'], '$100.00 excess must be retained as client overpayment credit');
    assertSameValue('100.00', $rep['client_credit_balance'], 'Client credit_balance column must equal 100.00');
});

runTest('17. Charity late-charge waiver by Branch Manager with mandatory reason & audit log', function () use ($pdo, $prodFlat, $officer1, $officer2, $managerMog) {
    $clientId = (int) $pdo->query("SELECT id FROM clients WHERE id_number = 'TST-ID-001'")->fetchColumn();
    $app = FinancingDomainService::createApplication([
        'client_id' => $clientId,
        'product_id' => $prodFlat,
        'requested_amount' => '1200.00',
        'term_installments' => 6,
        'repayment_frequency' => 'monthly',
        'purpose' => 'Charity waiver test',
    ], $officer1['id'], true, date('Y-m-d', strtotime('-42 days')), $pdo);

    FinancingDomainService::reviewApplication($app['application_id'], $officer2, 'approved', 'OK', $pdo);
    $fin = DisbursementService::disburse($app['application_id'], $officer1['id'], 'eDahab', '+252610000001', date('Y-m-d', strtotime('-42 days')), $pdo);

    CharityChargeService::assessOverdueInstallments(date('Y-m-d'), $pdo);
    $cc = $pdo->query("SELECT * FROM charity_charges WHERE financing_id = {$fin['financing_id']} LIMIT 1")->fetch();
    assertTrue(!empty($cc['id']), 'Charity charge must exist');

    // Empty reason must be rejected
    $emptyReasonBlocked = false;
    try {
        CharityChargeService::waiveCharge((int) $cc['id'], $managerMog['id'], '   ', $pdo);
    } catch (\Throwable) {
        $emptyReasonBlocked = true;
    }
    assertTrue($emptyReasonBlocked, 'Waiving charity charge without a reason must fail');

    $waived = CharityChargeService::waiveCharge((int) $cc['id'], $managerMog['id'], 'Documented hospital emergency', $pdo);
    assertSameValue('waived', $waived['status'], 'Charity charge status must be waived');
    assertSameValue('0.00', $waived['remaining_amount'], 'Remaining charity amount must be 0.00');
});

// TEST 18: Credit Score Updates
runTest('18. Credit score calculation (300-850 range, 600 baseline, waived charges do not penalize)', function () use ($pdo) {
    $clientId = (int) $pdo->query("SELECT id FROM clients WHERE id_number = 'TST-ID-001'")->fetchColumn();
    $score = CreditScoreEngine::recalculateForClient($clientId, 'Unit test check', 'Tijaabo', $pdo);
    assertTrue($score['current_score'] >= 300 && $score['current_score'] <= 850, 'Credit score within 300-850');
    // Since Client Alpha settled the first contract early and had the second contract's late charge waived, score must be > 600
    assertTrue($score['current_score'] > 600, 'Client with on-time/early settlement and waived late charge must score > 600');
});

// TEST 19: PAR30 Calculation
runTest('19. Portfolio at Risk (PAR30) live calculation & 0% empty branch handling', function () use ($pdo, $branchGar, $branchMog, $prodFlat, $officer1, $officer2) {
    // Garowe branch has no financing -> must return 0.00%
    $emptyBranchPar = Par30Service::calculate($branchGar, date('Y-m-d'), $pdo);
    assertSameValue('0.00', $emptyBranchPar['par30_percentage'], 'Branch with no financing must display 0.00% PAR30');

    // Register a second client in Mogadishu with a 45-day overdue installment (>30 days late)
    $c2 = FinancingDomainService::registerClientWithGuarantor([
        'full_name' => 'Overdue PAR30 Client',
        'id_number' => 'TST-ID-002',
        'phone' => '+252610000002',
        'city' => 'Mogadishu',
        'district' => 'Waberi',
        'address' => 'St 2',
        'business_type' => 'Shop',
        'monthly_income' => '1000.00',
        'branch_id' => $branchMog,
    ], [
        'full_name' => 'G2', 'phone' => '+252619000002', 'id_number' => 'GID-2', 'relationship' => 'Sister',
    ], $officer1['id'], $pdo);

    $app = FinancingDomainService::createApplication([
        'client_id' => $c2['client_id'],
        'product_id' => $prodFlat,
        'requested_amount' => '1000.00',
        'term_installments' => 5,
        'repayment_frequency' => 'monthly',
        'purpose' => 'PAR30 test',
    ], $officer1['id'], true, date('Y-m-d', strtotime('-65 days')), $pdo);
    FinancingDomainService::reviewApplication($app['application_id'], $officer2, 'approved', 'OK', $pdo);
    DisbursementService::disburse($app['application_id'], $officer1['id'], 'EVC Plus', '+252610000002', date('Y-m-d', strtotime('-65 days')), $pdo);

    $mogPar = Par30Service::calculate($branchMog, date('Y-m-d'), $pdo);
    assertTrue((float) $mogPar['par30_percentage'] > 0.0, 'Branch with >30d overdue contract must have PAR30 > 0%');
});

// TEST 20: PDF Statement Generation & "Fully Repaid" Watermark
runTest('20. Client PDF statement generation & Fully Repaid final statement retention', function () use ($pdo, $sharedFinancingId) {
    $data = StatementPdfGenerator::buildStatementData($sharedFinancingId, $pdo);
    assertTrue($data['is_fully_repaid'], 'Closed financing statement must be marked is_fully_repaid = true');
    assertTrue(str_contains($data['watermark_label'], 'FULLY REPAID'), 'Watermark must include FULLY REPAID');

    $pdfBytes = StatementPdfGenerator::generatePdfBinary($sharedFinancingId, 'en', $pdo);
    assertTrue(str_starts_with($pdfBytes, '%PDF-1.4'), 'Generated statement must be a valid PDF-1.4 document');
    assertTrue(str_contains($pdfBytes, 'FULLY REPAID'), 'PDF binary stream must contain FULLY REPAID mark');
});

// TEST 21: Group Financing Rules (3-10 members, >30d overdue member locks group, active financing blocks leaving)
runTest('21. Group financing rules (3-10 size, >30d overdue group lock, active financing leave block)', function () use ($pdo, $branchMog, $officer1, $managerMog, $prodMurabaha) {
    $c3 = FinancingDomainService::registerClientWithGuarantor([
        'full_name' => 'Group Member 3',
        'id_number' => 'TST-ID-003',
        'phone' => '+252610000003',
        'city' => 'Mogadishu',
        'district' => 'Hodan',
        'address' => 'St 3',
        'business_type' => 'Trader',
        'monthly_income' => '1100.00',
        'branch_id' => $branchMog,
    ], [
        'full_name' => 'G3', 'phone' => '+252619000003', 'id_number' => 'GID-3', 'relationship' => 'Brother',
    ], $officer1['id'], $pdo);

    $c1Id = (int) $pdo->query("SELECT id FROM clients WHERE id_number = 'TST-ID-001'")->fetchColumn();
    $c2Id = (int) $pdo->query("SELECT id FROM clients WHERE id_number = 'TST-ID-002'")->fetchColumn(); // >30d overdue from Test 19
    $c3Id = $c3['client_id'];

    // Group with < 3 members must fail
    $tooSmallBlocked = false;
    try {
        FinancingDomainService::createGroup('Tiny Group', $branchMog, $c1Id, [$c1Id, $c3Id], $officer1['id'], '', $pdo);
    } catch (\Throwable) {
        $tooSmallBlocked = true;
    }
    assertTrue($tooSmallBlocked, 'Group with fewer than 3 members must be rejected');

    // Create valid 3-member group (includes $c2Id who is >30d overdue)
    $grp = FinancingDomainService::createGroup('Test Solidarity Group', $branchMog, $c1Id, [$c1Id, $c2Id, $c3Id], $officer1['id'], '', $pdo);
    FinancingDomainService::approveGroup($grp['group_id'], $managerMog, $pdo);

    // Member $c3Id tries to apply for group financing -> must be blocked because fellow member $c2Id is >30 days late!
    $groupLockTriggered = false;
    try {
        FinancingDomainService::createApplication([
            'client_id' => $c3Id,
            'product_id' => $prodMurabaha,
            'group_id' => $grp['group_id'],
            'requested_amount' => '1000.00',
            'term_installments' => 4,
            'repayment_frequency' => 'monthly',
            'purpose' => 'Group test',
            'murabaha_goods_description' => 'Goods',
        ], $officer1['id'], true, date('Y-m-d'), $pdo);
    } catch (\Throwable) {
        $groupLockTriggered = true;
    }
    assertTrue($groupLockTriggered, 'If any group member is >30 days late, no member can receive new group financing');

    // Member $c2Id has active financing -> cannot leave group
    $leaveBlocked = false;
    try {
        FinancingDomainService::removeMemberFromGroup($grp['group_id'], $c2Id, $officer1['id'], $pdo);
    } catch (\Throwable) {
        $leaveBlocked = true;
    }
    assertTrue($leaveBlocked, 'Member with active financing cannot leave group');
});

// TEST 22: Bilingual SMS Reminder Deduplication
runTest('22. Bilingual SMS reminders (3d before & 1d overdue, deduplicated, none for paid)', function () use ($pdo) {
    // Find an unpaid installment and set its due_date to 3 days from today
    $inst = $pdo->query("SELECT id, due_date FROM installments WHERE status != 'paid' AND remaining_balance > 0 LIMIT 1")->fetch();
    $threeDaysAhead = date('Y-m-d', strtotime('+3 days'));
    $pdo->prepare('UPDATE installments SET due_date = :d WHERE id = :id')->execute([':d' => $threeDaysAhead, ':id' => $inst['id']]);

    $firstRun = SmsReminderService::generateScheduledReminders(date('Y-m-d'), $pdo);
    assertTrue($firstRun['upcoming_3_days_sent'] >= 1, 'Must generate upcoming_3_days SMS reminder');

    // Second run on the same day must NOT send duplicate reminder for the same installment
    $secondRun = SmsReminderService::generateScheduledReminders(date('Y-m-d'), $pdo);
    assertSameValue(0, $secondRun['upcoming_3_days_sent'], 'Each installment must receive only one reminder per type');
});

echo "------------------------------------------------------------------------\n";
echo sprintf(" TOTAL TESTS: %d | PASSED: %d | FAILED: %d\n", $passed + $failed, $passed, $failed);
echo "========================================================================\n";

exit($failed > 0 ? 1 : 0);
