<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$isSo = Lang::locale() === 'so';
$filter = $filter ?? 'all';
$s = $summary ?? [];

$heads = [
    'unpaid' => [$isSo ? 'Murabaxo Socota' : 'Active Murabaha', $isSo ? 'Dadka murabaxada qaatay oo lacag-bixintoodu weli socoto.' : 'People who took Murabaha and are still paying.'],
    'settled' => [$isSo ? 'Murabaxada La Bixiyay' : 'Settled Murabaha', $isSo ? 'Heshiisyada si buuxda loo bixiyay.' : 'Contracts that are fully paid.'],
    'all' => [$isSo ? 'Dhammaan Murabaxada' : 'All Murabaha', $isSo ? 'Dhammaan heshiisyada murabaxada ee la bixiyay.' : 'Every Murabaha contract issued.'],
];
[$headTitle, $headText] = $heads[$filter] ?? $heads['all'];

$kpis = [
    ['label' => $isSo ? 'Qiimaha Hantida' : 'Asset Cost', 'value' => $s['sum_cost'] ?? 0, 'sub' => (int) ($s['count_all'] ?? 0) . ($isSo ? ' heshiis' : ' contracts'), 'icon' => 'shopping-bag', 'tone' => 'info'],
    ['label' => $isSo ? "Faa'iidada" : 'Profit', 'value' => $s['sum_markup'] ?? 0, 'sub' => $isSo ? "Faa'iido go'an" : 'Agreed markup', 'icon' => 'layers', 'tone' => 'gold'],
    ['label' => $isSo ? 'La Soo Celiyay' : 'Repaid', 'value' => $s['sum_paid'] ?? 0, 'sub' => (int) ($s['count_settled'] ?? 0) . ($isSo ? ' la wada bixiyay' : ' fully settled'), 'icon' => 'check-circle', 'tone' => 'green'],
    ['label' => $isSo ? 'Weli Dhiman' : 'Outstanding', 'value' => $s['sum_outstanding'] ?? 0, 'sub' => (int) ($s['count_unpaid'] ?? 0) . ($isSo ? ' socda' : ' in progress'), 'icon' => 'clock', 'tone' => 'danger'],
];

$tabs = [
    ['key' => 'unpaid', 'label' => $isSo ? 'Socota' : 'Active', 'count' => $s['count_unpaid'] ?? 0],
    ['key' => 'settled', 'label' => $isSo ? 'La Bixiyay' : 'Settled', 'count' => $s['count_settled'] ?? 0],
    ['key' => 'all', 'label' => $isSo ? 'Dhammaan' : 'All', 'count' => $s['count_all'] ?? 0],
];

$labels = [
    'client' => $isSo ? 'Macmiilka' : 'Client',
    'contract' => $isSo ? 'Heshiiska' : 'Contract',
    'total' => $isSo ? 'Wadarta' : 'Total Due',
    'paid' => $isSo ? 'La Bixiyay' : 'Repaid',
    'left' => $isSo ? 'Dhiman' : 'Outstanding',
    'status' => $isSo ? 'Xaaladda' : 'Status',
];
?>
<div class="adm">
    <header class="adm-head adm-page-head">
        <div>
            <h1><?= Security::e($headTitle) ?></h1>
            <p><?= Security::e($headText) ?></p>
        </div>
        <div class="adm-head-actions">
            <a href="?route=repayment_record" class="btn btn-sm btn-outline"><?= Icons::svg('clock', 14) ?> <span><?= $isSo ? 'Qabo Bixin' : 'Record Repayment' ?></span></a>
            <a href="?route=murabaha_new" class="btn btn-sm btn-gold"><?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Murabaxo Cusub' : 'New Murabaha' ?></span></a>
        </div>
    </header>

    <section class="dash-kpis" aria-label="<?= $isSo ? 'Tirooyinka muhiimka ah' : 'Key figures' ?>">
        <?php foreach ($kpis as $kpi): ?>
            <div class="kpi-card dash-kpi dash-tone-<?= Security::e($kpi['tone']) ?>">
                <div class="kpi-header">
                    <span class="kpi-label"><?= Security::e($kpi['label']) ?></span>
                    <span class="kpi-icon"><?= Icons::svg($kpi['icon'], 17) ?></span>
                </div>
                <div class="kpi-value num-tabular"><?= Security::formatMoney($kpi['value']) ?></div>
                <div class="kpi-sub"><?= Security::e($kpi['sub']) ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <nav class="adm-tabs" aria-label="<?= $labels['status'] ?>">
        <?php foreach ($tabs as $tab): ?>
            <a href="?route=murabaha&filter=<?= Security::e($tab['key']) ?>" class="adm-tab <?= $filter === $tab['key'] ? 'is-active' : '' ?>" <?= $filter === $tab['key'] ? 'aria-current="page"' : '' ?>>
                <span><?= Security::e($tab['label']) ?></span>
                <span class="adm-tab-count num-tabular"><?= (int) $tab['count'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if (empty($contracts)): ?>
        <div class="adm-table-card">
            <div class="dash-empty">
                <span class="dash-empty-icon"><?= Icons::svg('shopping-bag', 20) ?></span>
                <strong><?= $isSo ? 'Heshiis qaybtan kuma jiro' : 'No contracts in this view' ?></strong>
                <p><?= $isSo ? 'Marka murabaxo la bixiyo halkan ayay ka muuqan doontaa.' : 'Contracts appear here once a Murabaha is disbursed.' ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="adm-toolbar">
            <div class="adm-search">
                <?= Icons::svg('search', 15) ?>
                <input type="search" class="form-control" data-filter-for="murabahaRows" placeholder="<?= $isSo ? 'Raadi macmiil, telefoon, heshiis...' : 'Search client, phone, contract...' ?>" aria-label="<?= $isSo ? 'Raadi' : 'Search' ?>" autocomplete="off">
            </div>
        </div>

        <div class="adm-table-card">
            <div class="table-responsive adm-scroll">
                <table class="simda-table fin-table">
                    <thead>
                        <tr>
                            <th><?= Security::e($labels['client']) ?></th>
                            <th><?= Security::e($labels['contract']) ?></th>
                            <th><?= Security::e($labels['total']) ?></th>
                            <th><?= Security::e($labels['paid']) ?></th>
                            <th><?= Security::e($labels['left']) ?></th>
                            <th><?= Security::e($labels['status']) ?></th>
                            <th class="fin-end"><span class="dash-sr"><?= $isSo ? 'Hawlaha' : 'Actions' ?></span></th>
                        </tr>
                    </thead>
                    <tbody id="murabahaRows" data-filter-list>
                        <?php foreach ($contracts as $c): ?>
                            <?php
                            $paidPct = (float) $c['total_due'] > 0 ? min(100, ((float) $c['total_paid'] / (float) $c['total_due']) * 100) : 0;
                            $statusClass = match ($c['status']) {
                                'completed' => 'is-on',
                                'overdue' => 'is-danger',
                                default => 'is-info',
                            };
                            ?>
                            <tr data-filter-item>
                                <td data-label="<?= Security::e($labels['client']) ?>">
                                    <div class="adm-person">
                                        <span class="adm-avatar dash-tone-gold"><?= Security::e(Security::initials((string) $c['client_name'])) ?></span>
                                        <div class="adm-person-text">
                                            <span class="adm-primary"><?= Security::e($c['client_name']) ?></span>
                                            <span class="adm-secondary num-tabular" dir="ltr"><?= Security::e($c['client_phone'] ?? '') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="<?= Security::e($labels['contract']) ?>">
                                    <a href="?route=financing_view&id=<?= (int) $c['id'] ?>" class="fin-link num-tabular"><?= Security::e($c['financing_number']) ?></a>
                                    <span class="fin-sub"><?= Security::e($c['branch_name']) ?></span>
                                </td>
                                <td class="num-tabular" data-label="<?= Security::e($labels['total']) ?>">
                                    <strong><?= Security::formatMoney($c['total_due']) ?></strong>
                                    <span class="fin-sub"><?= Security::formatMoney($c['approved_amount']) ?> + <?= Security::formatMoney($c['total_profit']) ?></span>
                                </td>
                                <td class="num-tabular" data-label="<?= Security::e($labels['paid']) ?>">
                                    <?= Security::formatMoney($c['total_paid']) ?>
                                    <span class="dash-progress fin-progress dash-tone-green"><span style="width:<?= number_format($paidPct, 1, '.', '') ?>%"></span></span>
                                </td>
                                <td class="num-tabular" data-label="<?= Security::e($labels['left']) ?>">
                                    <strong><?= Security::formatMoney($c['outstanding_balance']) ?></strong>
                                </td>
                                <td data-label="<?= Security::e($labels['status']) ?>">
                                    <span class="adm-status <?= $statusClass ?>"><?= $c['status'] === 'completed' ? ($isSo ? 'La Wada Bixiyay' : 'Completed') : Security::e(Lang::get('status_' . $c['status'])) ?></span>
                                    <span class="fin-sub num-tabular"><?= Security::e($c['maturity_date']) ?></span>
                                </td>
                                <td class="fin-end">
                                    <div class="adm-actions">
                                        <a href="?route=financing_view&id=<?= (int) $c['id'] ?>" class="adm-icon-btn" title="<?= $isSo ? 'Fiiri Faahfaahinta' : 'View Details' ?>" aria-label="<?= $isSo ? 'Fiiri Faahfaahinta' : 'View Details' ?>"><?= Icons::svg('eye', 15) ?></a>
                                        <a href="?route=statement_pdf&id=<?= (int) $c['id'] ?>" class="adm-icon-btn" title="PDF Statement" aria-label="PDF Statement"><?= Icons::svg('download', 15) ?></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="dash-empty" data-filter-empty="murabahaRows" hidden>
                <span class="dash-empty-icon"><?= Icons::svg('search', 20) ?></span>
                <strong><?= $isSo ? 'Wax lama helin' : 'No matches' ?></strong>
            </div>
        </div>
    <?php endif; ?>
</div>
