<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;
?>

<div class="card animate-in" style="margin-bottom:24px;">
    <h2 style="font-size:1.25rem;margin:6px 0;"><?= Security::e(Lang::get('menu_par30')) ?></h2>
    <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:18px;">
        Formula: <code>PAR30 = (Outstanding Balance of Financings with an Installment &gt; 30 Days Late ÷ Total Outstanding Balance) × 100</code>. Branches with no active financing display <code>0.00%</code>.
    </p>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Total Portfolio Outstanding</div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($par30['total_outstanding_balance']) ?></div>
            <div class="kpi-sub"><?= (int) $par30['active_financings_count'] ?> active/overdue contracts</div>
        </div>
        <div class="kpi-card kpi-danger">
            <div class="kpi-label">Risky Outstanding Balance (&gt;30d)</div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($par30['risky_outstanding_balance']) ?></div>
            <div class="kpi-sub"><?= (int) $par30['risky_financings_count'] ?> contract(s) &gt;30 days late</div>
        </div>
        <div class="kpi-card kpi-danger">
            <div class="kpi-label">Overall PAR30 Ratio</div>
            <div class="kpi-value num-tabular"><?= Security::e($par30['par30_percentage']) ?>%</div>
            <div class="kpi-sub">Calculated live as of <?= Security::e($par30['reference_date']) ?></div>
        </div>
    </div>

    <h3 style="font-size:1.05rem;margin:18px 0 10px;">1. Branch-Level PAR30 Breakdown</h3>
    <div class="table-responsive" style="margin-bottom:24px;">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Branch Code</th>
                    <th>Branch Name</th>
                    <th>Active Contracts</th>
                    <th>Total Outstanding Balance</th>
                    <th>Risky Balance (&gt;30d)</th>
                    <th>Branch PAR30 (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($branchBreakdown as $b): ?>
                    <tr>
                        <td class="num-tabular"><strong><?= Security::e($b['code']) ?></strong></td>
                        <td><?= Security::e($b['name']) ?> (<?= Security::e($b['city']) ?>)</td>
                        <td class="num-tabular"><?= (int) $b['active_financings_count'] ?></td>
                        <td class="num-tabular"><?= Security::formatMoney($b['total_outstanding_balance']) ?></td>
                        <td class="num-tabular"><?= Security::formatMoney($b['risky_outstanding_balance']) ?></td>
                        <td class="num-tabular">
                            <span class="badge badge-<?= (float) $b['par30_percentage'] > 5.0 ? 'danger' : 'success' ?>">
                                <?= Security::e($b['par30_percentage']) ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h3 style="font-size:1.05rem;margin-bottom:10px;">2. Financings with Installments More Than 30 Days Late</h3>
    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Contract #</th>
                    <th>Client</th>
                    <th>Phone</th>
                    <th>Branch</th>
                    <th>Max Days Overdue</th>
                    <th>At-Risk Outstanding Balance</th>
                    <th>Statement</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($par30['risky_financings'])): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Zero financings are currently more than 30 days overdue (PAR30 = 0.00%).</td></tr>
                <?php else: ?>
                    <?php foreach ($par30['risky_financings'] as $rf): ?>
                        <tr>
                            <td class="num-tabular"><a href="?route=financing_view&id=<?= (int) $rf['id'] ?>" style="font-weight:700;color:var(--color-danger);"><?= Security::e($rf['financing_number']) ?></a></td>
                            <td><?= Security::e($rf['client_name']) ?></td>
                            <td class="num-tabular"><?= Security::e($rf['client_phone']) ?></td>
                            <td><?= Security::e($rf['branch_name']) ?></td>
                            <td class="num-tabular"><span class="badge badge-danger"><?= (int) $rf['max_days_overdue'] ?> days late</span></td>
                            <td class="num-tabular"><strong><?= Security::formatMoney($rf['outstanding_balance']) ?></strong></td>
                            <td><a href="?route=financing_view&id=<?= (int) $rf['id'] ?>" class="btn btn-sm btn-outline">Inspect</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

