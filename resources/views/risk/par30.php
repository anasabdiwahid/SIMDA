<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;

$isSo = Lang::locale() === 'so';
$trend = $trend ?? [];
?>

<div class="card animate-in" style="margin-bottom:24px;">
    <h2 style="font-size:1.25rem;margin:6px 0;"><?= Security::e(Lang::get('menu_par30')) ?></h2>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label"><?= $isSo ? 'Wadarta Lacagta Maqan' : 'Total Portfolio Outstanding' ?></div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($par30['total_outstanding_balance']) ?></div>
            <div class="kpi-sub"><?= (int) $par30['active_financings_count'] ?> <?= $isSo ? 'heshiis socda / dib u dhacay' : 'active/overdue contracts' ?></div>
        </div>
        <div class="kpi-card kpi-danger">
            <div class="kpi-label"><?= $isSo ? 'Lacagta Khatarta ku Jirta (&gt;30 maalmood)' : 'Risky Outstanding Balance (&gt;30d)' ?></div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($par30['risky_outstanding_balance']) ?></div>
            <div class="kpi-sub"><?= (int) $par30['risky_financings_count'] ?> <?= $isSo ? 'heshiis oo ka dib dhacay 30 maalmood' : 'contract(s) &gt;30 days late' ?></div>
        </div>
        <div class="kpi-card kpi-danger">
            <div class="kpi-label"><?= $isSo ? 'PAR30 Guud' : 'Overall PAR30 Ratio' ?></div>
            <div class="kpi-value num-tabular"><?= Security::e($par30['par30_percentage']) ?>%</div>
            <div class="kpi-sub"><?= $isSo ? 'Xog toos ah, maanta' : 'Calculated live as of' ?> <?= Security::e($par30['reference_date']) ?></div>
        </div>
    </div>

    <h3 style="font-size:1.05rem;margin:18px 0 10px;"><?= $isSo ? '1. Isbeddelka PAR30 bilihii la soo dhaafay' : '1. PAR30 trend over the last 12 months' ?></h3>
    <div style="position:relative;height:260px;margin-bottom:24px;">
        <canvas id="par30TrendChart" aria-label="<?= $isSo ? 'Shaxda isbeddelka PAR30' : 'PAR30 trend chart' ?>" role="img"></canvas>
    </div>

    <h3 style="font-size:1.05rem;margin:18px 0 10px;"><?= $isSo ? '2. PAR30 laan kasta' : '2. Branch-level PAR30 breakdown' ?></h3>
    <div class="table-responsive" style="margin-bottom:24px;">
        <table class="simda-table">
            <thead>
                <tr>
                    <th><?= $isSo ? 'Koodhka Laanta' : 'Branch Code' ?></th>
                    <th><?= $isSo ? 'Laanta' : 'Branch Name' ?></th>
                    <th><?= $isSo ? 'Heshiisyada Socda' : 'Active Contracts' ?></th>
                    <th><?= $isSo ? 'Lacagta Maqan' : 'Total Outstanding Balance' ?></th>
                    <th><?= $isSo ? 'Khatarta (&gt;30 maalmood)' : 'Risky Balance (&gt;30d)' ?></th>
                    <th><?= $isSo ? 'PAR30 Laanta (%)' : 'Branch PAR30 (%)' ?></th>
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

    <h3 style="font-size:1.05rem;margin-bottom:10px;"><?= $isSo ? '3. Maalgelinta leh qaybo ka dib dhacay in ka badan 30 maalmood' : '3. Financings with installments more than 30 days late' ?></h3>
    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th><?= $isSo ? 'Heshiiska #' : 'Contract #' ?></th>
                    <th><?= $isSo ? 'Macmiilka' : 'Client' ?></th>
                    <th><?= $isSo ? 'Telefoonka' : 'Phone' ?></th>
                    <th><?= $isSo ? 'Laanta' : 'Branch' ?></th>
                    <th><?= $isSo ? 'Maalmaha Ugu Badan ee Dib u Dhaca' : 'Max Days Overdue' ?></th>
                    <th><?= $isSo ? 'Lacagta Khatarta ku Jirta' : 'At-Risk Outstanding Balance' ?></th>
                    <th><?= $isSo ? 'Bayaanka' : 'Statement' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($par30['risky_financings'])): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);"><?= $isSo ? 'Ma jirto maalgelin ka dib dhacday in ka badan 30 maalmood (PAR30 = 0.00%).' : 'Zero financings are currently more than 30 days overdue (PAR30 = 0.00%).' ?></td></tr>
                <?php else: ?>
                    <?php foreach ($par30['risky_financings'] as $rf): ?>
                        <tr>
                            <td class="num-tabular"><a href="?route=financing_view&id=<?= (int) $rf['id'] ?>" style="font-weight:700;color:var(--color-danger);"><?= Security::e($rf['financing_number']) ?></a></td>
                            <td><?= Security::e($rf['client_name']) ?></td>
                            <td class="num-tabular"><?= Security::e($rf['client_phone']) ?></td>
                            <td><?= Security::e($rf['branch_name']) ?></td>
                            <td class="num-tabular"><span class="badge badge-danger"><?= (int) $rf['max_days_overdue'] ?> <?= $isSo ? 'maalmood' : 'days late' ?></span></td>
                            <td class="num-tabular"><strong><?= Security::formatMoney($rf['outstanding_balance']) ?></strong></td>
                            <td><a href="?route=financing_view&id=<?= (int) $rf['id'] ?>" class="btn btn-sm btn-outline"><?= $isSo ? 'Eeg' : 'Inspect' ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    var canvas = document.getElementById('par30TrendChart');
    if (!canvas || typeof Chart === 'undefined') return;
    var trend = <?= json_encode(array_map(static fn ($m) => ['label' => $m['label'], 'par30' => $m['par30']], $trend), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    var css = getComputedStyle(document.documentElement);
    var danger = (css.getPropertyValue('--color-danger') || '#C94B4B').trim();
    var muted = (css.getPropertyValue('--text-muted') || '#7A8580').trim();
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: trend.map(function (m) { return m.label; }),
            datasets: [{
                label: 'PAR30 (%)',
                data: trend.map(function (m) { return m.par30; }),
                borderColor: danger,
                backgroundColor: danger,
                tension: 0.25,
                pointRadius: 3,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return c.parsed.y.toFixed(2) + '%'; } } } },
            scales: {
                y: { beginAtZero: true, suggestedMax: 10, ticks: { color: muted, callback: function (v) { return v + '%'; } } },
                x: { ticks: { color: muted } }
            }
        }
    });
})();
</script>
