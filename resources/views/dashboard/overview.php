<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$user = AuthMiddleware::user() ?? [];
$m = $metrics;
$isSo = $locale === 'so';
?>

<!-- 1. WELCOME & QUICK ACTIONS STRIP -->
<div class="card animate-in" style="margin-bottom:20px;border-left:4px solid var(--simda-gold);background:var(--bg-surface);">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <h1 style="font-size:1.35rem;font-weight:800;margin:0;color:var(--text-main);">
                    <?= $isSo ? 'As-Salaamu Calaykum' : 'Welcome back' ?>, <?= Security::e($user['full_name'] ?? 'Admin') ?>
                </h1>
                <span class="badge badge-gold" style="font-size:0.72rem;font-weight:700;">
                    <?= Security::e($user['role_name_en'] ?? 'System Administrator') ?>
                </span>
            </div>
            <p style="font-size:0.84rem;color:var(--text-muted);margin:0;">
                <?= $isSo ? 'Kormeerka guud ee hawlgallada Maalgelinta Islaamiga ah, Xisaabaadka, iyo Khatarta Portfolio-ga ee SIMDA.' : 'Institutional overview of SIMDA Islamic microfinance operations, Dual-Approval queue, and portfolio performance.' ?>
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="?route=client_register" class="btn btn-sm btn-gold" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('user-plus', 15) ?>
                <span><?= $isSo ? 'Diiwaangeli Macmiil' : 'Register Client' ?></span>
            </a>
            <a href="?route=products" class="btn btn-sm btn-outline" style="font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('file-text', 15) ?>
                <span><?= $isSo ? 'Codsi Maalgelin' : 'New Application' ?></span>
            </a>
            <a href="?route=repayment_record" class="btn btn-sm btn-outline" style="font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('clock', 15) ?>
                <span><?= $isSo ? 'Qabo Lacag Bixin' : 'Record Repayment' ?></span>
            </a>
        </div>
    </div>
</div>

<!-- 2. CORE ISLAMIC MICROFINANCE KPI CARDS -->
<div class="kpi-grid animate-in delay-1" style="margin-bottom:22px;">
    <!-- CARD 1: ACTIVE FINANCING PORTFOLIO -->
    <div class="kpi-card kpi-gold">
        <div class="kpi-header">
            <span class="kpi-label"><?= $isSo ? 'Maalgelinta Firfircoon (Portfolio)' : 'Active Financing Portfolio' ?></span>
            <span class="kpi-icon" style="background:rgba(214,182,108,0.18);color:var(--simda-green);"><?= Icons::svg('briefcase', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= Security::formatMoney($m['total_outstanding_balance']) ?></div>
        <div class="kpi-sub">
            <?= (int) $m['active_financing_count'] ?> <?= $isSo ? 'heshiis' : 'active contracts' ?> •
            <span class="num-tabular"><?= Security::formatMoney($m['total_disbursed_amount']) ?></span> <?= $isSo ? 'la bixiyay' : 'disbursed' ?>
        </div>
    </div>

    <!-- CARD 2: TOTAL REPAYMENTS COLLECTED -->
    <div class="kpi-card kpi-success">
        <div class="kpi-header">
            <span class="kpi-label"><?= $isSo ? 'Lacagaha Soo Xarooday' : 'Repayments Collected' ?></span>
            <span class="kpi-icon" style="background:rgba(35,132,91,0.15);color:#1FAF62;"><?= Icons::svg('check-circle', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= Security::formatMoney($m['total_repayments_collected']) ?></div>
        <div class="kpi-sub">
            <?= $isSo ? 'Wadarta guud (Gross)' : 'Gross Receipts' ?>: <span class="num-tabular"><?= Security::formatMoney($m['gross_receipts']) ?></span>
        </div>
    </div>

    <!-- CARD 3: CLIENTS & APPROVAL QUEUE -->
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label"><?= $isSo ? 'Macaamiisha Diiwaangashan' : 'Registered Clients' ?></span>
            <span class="kpi-icon"><?= Icons::svg('users', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= (int) $m['total_clients'] ?></div>
        <div class="kpi-sub">
            <a href="?route=approval_queue" style="color:var(--simda-green);font-weight:700;text-decoration:underline;">
                <?= (int) $m['pending_applications_count'] ?> <?= $isSo ? 'codsi sugaya ansixin' : 'applications in queue' ?> →
            </a>
        </div>
    </div>

    <!-- CARD 4: PAR30 RISK & CHARITY FUND -->
    <div class="kpi-card <?= ((float) $m['par30_percentage'] > 5) ? 'kpi-danger' : 'kpi-gold' ?>">
        <div class="kpi-header">
            <span class="kpi-label"><?= $isSo ? 'Khatarta PAR30 & Sanduuqa' : 'PAR30 Risk & Charity' ?></span>
            <span class="kpi-icon"><?= Icons::svg('alert-triangle', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= Security::e($m['par30_percentage']) ?>%</div>
        <div class="kpi-sub">
            <?= $isSo ? 'Khatar' : 'Risky' ?>: <span class="num-tabular"><?= Security::formatMoney($m['par30_risky_balance']) ?></span> •
            <?= $isSo ? 'Sanduuqa' : 'Charity' ?>: <span class="num-tabular"><?= Security::formatMoney($m['charity_account_balance']) ?></span>
        </div>
    </div>
</div>

<!-- 3. REAL LIVE CHARTS ROW (POWERED DIRECTLY BY DATABASE SQL) -->
<div class="dashboard-split-grid animate-in delay-2" style="margin-bottom:22px;">
    <!-- CHART 1: MONTHLY REPAYMENTS (PRINCIPAL VS PROFIT) -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;margin:0;font-weight:700;"><?= Security::e(Lang::get('chart_monthly_title')) ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= Security::e(Lang::get('chart_monthly_sub')) ?></div>
            </div>
            <span class="badge badge-info" style="font-weight:700;">Live SQL</span>
        </div>
        <div style="height:250px;position:relative;">
            <canvas id="chartMonthlyRepayments"></canvas>
        </div>
    </div>

    <!-- CHART 2: ISLAMIC PRODUCT MIX -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;margin:0;font-weight:700;"><?= $isSo ? 'Noocyada Maalgelinta Islaamiga ah' : 'Islamic Product Mix' ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= $isSo ? 'Kala soocidda qandaraasyada (Murabaha, Salam, Istisna)' : 'Distribution across Sharia-compliant contract types' ?></div>
            </div>
            <span class="badge badge-gold" style="font-weight:700;">0% Riba</span>
        </div>
        <div style="height:250px;position:relative;">
            <canvas id="chartProductMix"></canvas>
        </div>
    </div>
</div>

<!-- 4. RECENT ACTIVE FINANCINGS & RECENT REPAYMENTS TABLES -->
<div class="dashboard-split-grid animate-in delay-3">
    <!-- RECENT FINANCINGS -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;margin:0;font-weight:700;"><?= Security::e(Lang::get('menu_active_financing')) ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= $isSo ? 'Heshiisyada maalgelinta ee ugu dambeeyay' : 'Latest active microfinance contracts' ?></div>
            </div>
            <a href="?route=financings" class="btn btn-sm btn-outline"><?= Security::e(Lang::get('btn_view_all')) ?> <?= Icons::svg('arrow-right', 14) ?></a>
        </div>
        <?php if (empty($recentFinancings)): ?>
            <div style="padding:32px 20px;text-align:center;color:var(--text-muted);">
                <div style="width:48px;height:48px;border-radius:50%;background:rgba(214,182,108,0.12);color:var(--simda-gold);display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px;">
                    <?= Icons::svg('file-text', 22) ?>
                </div>
                <div style="font-weight:700;font-size:0.92rem;color:var(--text-main);margin-bottom:4px;">
                    <?= $isSo ? 'Weli ma jiraan heshiisyo firfircoon' : 'No active financings yet' ?>
                </div>
                <div style="font-size:0.8rem;max-width:340px;margin:0 auto 14px auto;">
                    <?= $isSo ? 'Database-ka hadda waa nadiif. Waxaad bilaabi kartaa adoo diiwaangelinaya macmiil cusub ama codsi maalgelin.' : 'The database is clean. Register a client or process an application to start.' ?>
                </div>
                <a href="?route=client_register" class="btn btn-sm btn-gold">
                    <?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Diiwaangeli Macmiil' : 'Register Client' ?></span>
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="simda-table">
                    <thead>
                        <tr>
                            <th><?= Security::e(Lang::get('th_contract')) ?></th>
                            <th><?= Security::e(Lang::get('th_client')) ?></th>
                            <th><?= Security::e(Lang::get('th_type')) ?></th>
                            <th><?= Security::e(Lang::get('th_total_due')) ?></th>
                            <th><?= Security::e(Lang::get('th_outstanding')) ?></th>
                            <th><?= Security::e(Lang::get('th_status')) ?></th>
                            <th><?= Security::e(Lang::get('th_pdf')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentFinancings as $f): ?>
                            <tr>
                                <td class="num-tabular"><a href="?route=financing_view&id=<?= (int) $f['id'] ?>" style="font-weight:700;color:var(--simda-green);"><?= Security::e($f['financing_number']) ?></a></td>
                                <td><?= Security::e($f['client_name']) ?></td>
                                <td><span class="badge badge-gold"><?= Security::e($f['product_type']) ?></span></td>
                                <td class="num-tabular"><?= Security::formatMoney($f['total_due']) ?></td>
                                <td class="num-tabular"><strong><?= Security::formatMoney($f['outstanding_balance']) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= $f['status'] === 'completed' ? 'success' : ($f['status'] === 'overdue' ? 'danger' : 'info') ?>">
                                        <?= Security::e(Lang::get('status_' . $f['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?route=statement_pdf&id=<?= (int) $f['id'] ?>" class="btn btn-sm btn-outline"><?= Icons::svg('download', 14) ?> PDF</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- RECENT REPAYMENTS -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;margin:0;font-weight:700;"><?= Security::e(Lang::get('menu_repayment_history')) ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= $isSo ? 'Lacagihii ugu dambeeyay ee soo xarooday' : 'Latest collected microfinance repayments' ?></div>
            </div>
            <a href="?route=repayments" class="btn btn-sm btn-outline"><?= Security::e(Lang::get('btn_view_all')) ?> <?= Icons::svg('arrow-right', 14) ?></a>
        </div>
        <?php if (empty($recentRepayments)): ?>
            <div style="padding:32px 20px;text-align:center;color:var(--text-muted);">
                <div style="width:48px;height:48px;border-radius:50%;background:rgba(35,132,91,0.12);color:#23845B;display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px;">
                    <?= Icons::svg('check-circle', 22) ?>
                </div>
                <div style="font-weight:700;font-size:0.92rem;color:var(--text-main);margin-bottom:4px;">
                    <?= $isSo ? 'Weli ma jiraan lacago soo xarooday' : 'No repayments collected yet' ?>
                </div>
                <div style="font-size:0.8rem;max-width:340px;margin:0 auto 14px auto;">
                    <?= $isSo ? 'Marka heshiisyada lacag laga bixiyo, rasiidhadu halkan ayay toos uga soo muuqanayaan.' : 'Repayment receipts will be logged here in real time.' ?>
                </div>
                <a href="?route=repayment_record" class="btn btn-sm btn-outline">
                    <?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Qabo Lacag Bixin' : 'Record Repayment' ?></span>
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="simda-table">
                    <thead>
                        <tr>
                            <th><?= Security::e(Lang::get('th_receipt')) ?></th>
                            <th><?= Security::e(Lang::get('th_client')) ?></th>
                            <th><?= Security::e(Lang::get('th_method')) ?></th>
                            <th><?= Security::e(Lang::get('th_paid')) ?></th>
                            <th><?= Security::e(Lang::get('th_principal_profit')) ?></th>
                            <th><?= Security::e(Lang::get('th_date')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRepayments as $r): ?>
                            <tr>
                                <td class="num-tabular"><strong><?= Security::e($r['receipt_number']) ?></strong></td>
                                <td><?= Security::e($r['client_name']) ?></td>
                                <td><span class="badge badge-info"><?= Security::e($r['payment_method']) ?></span></td>
                                <td class="num-tabular"><strong><?= Security::formatMoney($r['amount']) ?></strong></td>
                                <td class="num-tabular" style="font-size:0.76rem;">
                                    P: <?= Security::formatMoney($r['allocated_to_principal']) ?> |
                                    M: <?= Security::formatMoney($r['allocated_to_profit']) ?>
                                </td>
                                <td class="num-tabular"><?= Security::e($r['payment_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CLIENT-SIDE CHART SCRIPTS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;

    const monthlyData = <?= json_encode($m['charts']['monthly_repayments'] ?? []) ?>;
    const byProduct = <?= json_encode($m['charts']['by_product_type'] ?? []) ?>;

    const ctx1 = document.getElementById('chartMonthlyRepayments');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: monthlyData.length ? monthlyData.map(r => r.ym) : ['No Data Yet'],
                datasets: [
                    {
                        label: '<?= $isSo ? 'Asalka (Principal) $' : 'Principal Collected ($)' ?>',
                        data: monthlyData.length ? monthlyData.map(r => parseFloat(r.principal_collected)) : [0],
                        backgroundColor: '#123B32',
                        borderRadius: 6
                    },
                    {
                        label: '<?= $isSo ? 'Faa\'iidada (Markup) $' : 'Markup / Profit ($)' ?>',
                        data: monthlyData.length ? monthlyData.map(r => parseFloat(r.profit_collected)) : [0],
                        backgroundColor: '#D6B66C',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const ctx2 = document.getElementById('chartProductMix');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: byProduct.length ? byProduct.map(p => p.product_type.toUpperCase()) : ['Murabaha (Active)'],
                datasets: [{
                    data: byProduct.length ? byProduct.map(p => parseFloat(p.total_principal)) : [100],
                    backgroundColor: ['#123B32', '#D6B66C', '#23845B']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
