<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$locale = Lang::locale();
$m = $metrics;
?>

<!-- 8 FINANCIAL SUMMARY KPI CARDS (100% REAL DATABASE QUERIES) -->
<div class="kpi-grid animate-in">
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label"><?= Security::e(Lang::get('kpi_total_clients')) ?></span>
            <span class="kpi-icon"><?= Icons::svg('users', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= (int) $m['total_clients'] ?></div>
        <div class="kpi-sub"><?= Security::e(Lang::get('kpi_sub_verified_clients')) ?></div>
    </div>

    <div class="kpi-card kpi-gold">
        <div class="kpi-header">
            <span class="kpi-label"><?= Security::e(Lang::get('kpi_active_financing')) ?></span>
            <span class="kpi-icon"><?= Icons::svg('file-text', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= (int) $m['active_financing_count'] ?></div>
        <div class="kpi-sub"><?= Security::e(Lang::get('kpi_sub_disbursed')) ?>: <span class="num-tabular"><?= Security::formatMoney($m['total_disbursed_amount']) ?></span></div>
    </div>

    <div class="kpi-card kpi-gold">
        <div class="kpi-header">
            <span class="kpi-label"><?= Security::e(Lang::get('kpi_total_outstanding')) ?></span>
            <span class="kpi-icon"><?= Icons::svg('briefcase', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= Security::formatMoney($m['total_outstanding_balance']) ?></div>
        <div class="kpi-sub"><?= Security::e(Lang::get('kpi_sub_balance')) ?></div>
    </div>

    <div class="kpi-card kpi-success">
        <div class="kpi-header">
            <span class="kpi-label"><?= Security::e(Lang::get('kpi_total_repayments')) ?></span>
            <span class="kpi-icon"><?= Icons::svg('check-circle', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= Security::formatMoney($m['total_repayments_collected']) ?></div>
        <div class="kpi-sub"><?= Security::e(Lang::get('kpi_sub_gross_receipts')) ?>: <span class="num-tabular"><?= Security::formatMoney($m['gross_receipts']) ?></span></div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label"><?= Security::e(Lang::get('kpi_pending_apps')) ?></span>
            <span class="kpi-icon"><?= Icons::svg('clock', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= (int) $m['pending_applications_count'] ?></div>
        <div class="kpi-sub"><a href="?route=approval_queue" style="text-decoration:underline;"><?= Security::e(Lang::get('menu_approval_queue')) ?></a></div>
    </div>

    <div class="kpi-card kpi-danger">
        <div class="kpi-header">
            <span class="kpi-label"><?= Security::e(Lang::get('kpi_overdue_financing')) ?></span>
            <span class="kpi-icon"><?= Icons::svg('alert-triangle', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= (int) $m['overdue_financing_count'] ?></div>
        <div class="kpi-sub"><?= Security::e(Lang::get('kpi_sub_overdue')) ?></div>
    </div>

    <div class="kpi-card kpi-danger">
        <div class="kpi-header">
            <span class="kpi-label"><?= Security::e(Lang::get('kpi_par30')) ?></span>
            <span class="kpi-icon"><?= Icons::svg('bar-chart', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= Security::e($m['par30_percentage']) ?>%</div>
        <div class="kpi-sub"><?= Security::e(Lang::get('kpi_sub_par30_risky')) ?>: <span class="num-tabular"><?= Security::formatMoney($m['par30_risky_balance']) ?></span></div>
    </div>

    <div class="kpi-card kpi-gold">
        <div class="kpi-header">
            <span class="kpi-label"><?= Security::e(Lang::get('kpi_charity_balance')) ?></span>
            <span class="kpi-icon"><?= Icons::svg('heart-handshake', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular"><?= Security::formatMoney($m['charity_account_balance']) ?></div>
        <div class="kpi-sub"><?= Security::e(Lang::get('kpi_sub_charity_pending')) ?>: <span class="num-tabular"><?= Security::formatMoney($m['charity_pending_balance']) ?></span></div>
    </div>
</div>

<!-- CHARTS ROW (POWERED BY LIVE DATABASE DATA) -->
<div class="dashboard-split-grid animate-in delay-1">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;"><?= Security::e(Lang::get('chart_monthly_title')) ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= Security::e(Lang::get('chart_monthly_sub')) ?></div>
            </div>
            <span class="badge badge-info">Live SQL</span>
        </div>
        <div style="height:250px;position:relative;">
            <canvas id="chartMonthlyRepayments"></canvas>
        </div>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;"><?= Security::e(Lang::get('chart_branch_title')) ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= Security::e(Lang::get('chart_branch_sub')) ?></div>
            </div>
            <span class="badge badge-gold">Module 12</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;height:250px;align-items:center;">
            <div style="height:220px;position:relative;"><canvas id="chartPar30Branches"></canvas></div>
            <div style="height:220px;position:relative;"><canvas id="chartProductMix"></canvas></div>
        </div>
    </div>
</div>

<!-- RECENT ACTIVE FINANCINGS & RECENT REPAYMENTS -->
<div class="dashboard-split-grid animate-in delay-2">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 style="font-size:1rem;"><?= Security::e(Lang::get('menu_active_financing')) ?></h3>
            <a href="?route=financings" class="btn btn-sm btn-outline"><?= Security::e(Lang::get('btn_view_all')) ?> <?= Icons::svg('arrow-right', 14) ?></a>
        </div>
        <?php if (empty($recentFinancings)): ?>
            <div style="padding:24px;text-align:center;color:var(--text-muted);">
                <strong><?= Security::e(Lang::get('empty_state_title')) ?></strong>
                <div><?= Security::e(Lang::get('empty_state_desc')) ?></div>
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

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 style="font-size:1rem;"><?= Security::e(Lang::get('menu_repayment_history')) ?></h3>
            <a href="?route=repayments" class="btn btn-sm btn-outline"><?= Security::e(Lang::get('btn_view_all')) ?> <?= Icons::svg('arrow-right', 14) ?></a>
        </div>
        <?php if (empty($recentRepayments)): ?>
            <div style="padding:24px;text-align:center;color:var(--text-muted);">
                <strong><?= Security::e(Lang::get('empty_state_title')) ?></strong>
                <div><?= Security::e(Lang::get('empty_state_desc')) ?></div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;

    const monthlyData = <?= json_encode($m['charts']['monthly_repayments']) ?>;
    const byProduct = <?= json_encode($m['charts']['by_product_type']) ?>;
    const branchPar = <?= json_encode($m['charts']['branch_par30']) ?>;

    const ctx1 = document.getElementById('chartMonthlyRepayments');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: monthlyData.length ? monthlyData.map(r => r.ym) : ['No Data Yet'],
                datasets: [
                    {
                        label: 'Principal Collected ($)',
                        data: monthlyData.length ? monthlyData.map(r => parseFloat(r.principal_collected)) : [0],
                        backgroundColor: '#123B32',
                        borderRadius: 6
                    },
                    {
                        label: 'Profit/Markup Collected ($)',
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

    const ctx2 = document.getElementById('chartPar30Branches');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: branchPar.map(b => b.code || 'Branch'),
                datasets: [{
                    label: 'PAR30 (%)',
                    data: branchPar.map(b => parseFloat(b.par30_percentage || 0)),
                    backgroundColor: '#C94B4B',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
    }

    const ctx3 = document.getElementById('chartProductMix');
    if (ctx3) {
        new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: byProduct.length ? byProduct.map(p => p.product_type.toUpperCase()) : ['None'],
                datasets: [{
                    data: byProduct.length ? byProduct.map(p => parseFloat(p.total_principal)) : [0],
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
