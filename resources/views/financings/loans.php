<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$isSo = Lang::locale() === 'so';
$filter = $filter ?? 'active';
$s = $summary ?? [];

$heads = [
    'active' => [$isSo ? 'Dayn Cadaan Socota' : 'Active Cash Loans', $isSo ? 'Dadka daynta cadaanta qaatay oo weli bixinaya.' : 'People who took a cash loan and are still paying.'],
    'settled_ontime' => [$isSo ? 'Waqtigii Ku Bixiyay' : 'Settled On Time', $isSo ? 'Daymaha si buuxda loo bixiyay iyadoo aan dib u dhac jirin.' : 'Loans fully paid with no delay.'],
    'settled_late' => [$isSo ? 'Dib U Dhac Ku Bixiyay' : 'Settled Late', $isSo ? 'Daymaha la bixiyay laakiin dib u dhac ku yimid.' : 'Loans fully paid, but with a delay.'],
    'all' => [$isSo ? 'Dhammaan Daymaha' : 'All Cash Loans', $isSo ? 'Dhammaan daymaha cadaanta ah ee la bixiyay.' : 'Every cash loan issued.'],
];
[$headTitle, $headText] = $heads[$filter] ?? $heads['active'];

$settledCount = (int) ($s['count_settled_ontime'] ?? 0) + (int) ($s['count_settled_late'] ?? 0);
$kpis = [
    ['label' => $isSo ? 'Daynta La Bixiyay' : 'Loans Disbursed', 'value' => $s['sum_principal'] ?? 0, 'sub' => (int) ($s['count_all'] ?? 0) . ($isSo ? ' heshiis' : ' contracts'), 'icon' => 'credit-card', 'tone' => 'info'],
    ['label' => $isSo ? 'La Soo Celiyay' : 'Repaid', 'value' => $s['sum_paid'] ?? 0, 'sub' => $settledCount . ($isSo ? ' la wada bixiyay' : ' fully settled'), 'icon' => 'check-circle', 'tone' => 'green'],
    ['label' => $isSo ? 'Weli Dhiman' : 'Outstanding', 'value' => $s['sum_outstanding'] ?? 0, 'sub' => (int) ($s['count_active'] ?? 0) . ($isSo ? ' socda' : ' in progress'), 'icon' => 'clock', 'tone' => 'danger'],
];

$tabs = [
    ['key' => 'active', 'label' => $isSo ? 'Socota' : 'Active', 'count' => $s['count_active'] ?? 0],
    ['key' => 'settled_ontime', 'label' => $isSo ? 'Waqtigii Ku Bixiyay' : 'On Time', 'count' => $s['count_settled_ontime'] ?? 0],
    ['key' => 'settled_late', 'label' => $isSo ? 'Dib U Dhac' : 'Late', 'count' => $s['count_settled_late'] ?? 0],
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
            <a href="?route=applications" class="btn btn-sm btn-gold"><?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Codsi Cusub' : 'New Application' ?></span></a>
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
            <a href="?route=loans&filter=<?= Security::e($tab['key']) ?>" class="adm-tab <?= $filter === $tab['key'] ? 'is-active' : '' ?>" <?= $filter === $tab['key'] ? 'aria-current="page"' : '' ?>>
                <span><?= Security::e($tab['label']) ?></span>
                <span class="adm-tab-count num-tabular"><?= (int) $tab['count'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if (empty($loans)): ?>
        <div class="adm-table-card">
            <div class="dash-empty">
                <span class="dash-empty-icon"><?= Icons::svg('credit-card', 20) ?></span>
                <strong><?= $isSo ? 'Dayn qaybtan kuma jirto' : 'No loans in this view' ?></strong>
                <p><?= $isSo ? 'Marka dayn la bixiyo halkan ayay ka muuqan doontaa.' : 'Loans appear here once they are disbursed.' ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="adm-toolbar">
            <div class="adm-search">
                <?= Icons::svg('search', 15) ?>
                <input type="search" class="form-control" data-filter-for="loanRows" placeholder="<?= $isSo ? 'Raadi macmiil, telefoon, heshiis...' : 'Search client, phone, contract...' ?>" aria-label="<?= $isSo ? 'Raadi' : 'Search' ?>" autocomplete="off">
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
                    <tbody id="loanRows" data-filter-list>
                        <?php foreach ($loans as $ln): ?>
                            <?php
                            $isSettled = $ln['status'] === 'completed';
                            $hasDelay = ($ln['closed_at'] && $ln['closed_at'] > $ln['maturity_date']) || ((int) $ln['charity_charges_count'] > 0) || ((int) $ln['late_installments_count'] > 0);
                            $paidPct = (float) $ln['total_due'] > 0 ? min(100, ((float) $ln['total_paid'] / (float) $ln['total_due']) * 100) : 0;
                            if ($isSettled && !$hasDelay) {
                                [$statusClass, $statusText] = ['is-on', $isSo ? 'Waqtigii ku bixiyay' : 'Paid on time'];
                            } elseif ($isSettled) {
                                [$statusClass, $statusText] = ['is-warn', $isSo ? 'Dib u dhac ku bixiyay' : 'Settled late'];
                            } elseif ($ln['status'] === 'overdue' || strtotime((string) $ln['maturity_date']) < time()) {
                                [$statusClass, $statusText] = ['is-danger', $isSo ? 'Dib u dhacay' : 'Overdue'];
                            } else {
                                [$statusClass, $statusText] = ['is-info', $isSo ? 'Socota' : 'In progress'];
                            }
                            ?>
                            <tr data-filter-item>
                                <td data-label="<?= Security::e($labels['client']) ?>">
                                    <div class="adm-person">
                                        <span class="adm-avatar dash-tone-green"><?= Security::e(Security::initials((string) $ln['client_name'])) ?></span>
                                        <div class="adm-person-text">
                                            <span class="adm-primary"><?= Security::e($ln['client_name']) ?></span>
                                            <span class="adm-secondary num-tabular" dir="ltr"><?= Security::e($ln['client_phone'] ?? '') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="<?= Security::e($labels['contract']) ?>">
                                    <a href="?route=financing_view&id=<?= (int) $ln['id'] ?>" class="fin-link num-tabular"><?= Security::e($ln['financing_number']) ?></a>
                                    <span class="fin-sub"><?= Security::e($ln['branch_name']) ?></span>
                                </td>
                                <td class="num-tabular" data-label="<?= Security::e($labels['total']) ?>">
                                    <strong><?= Security::formatMoney($ln['total_due']) ?></strong>
                                    <span class="fin-sub"><?= $isSo ? 'Asalka' : 'Principal' ?> <?= Security::formatMoney($ln['approved_amount']) ?></span>
                                </td>
                                <td class="num-tabular" data-label="<?= Security::e($labels['paid']) ?>">
                                    <?= Security::formatMoney($ln['total_paid']) ?>
                                    <span class="dash-progress fin-progress dash-tone-green"><span style="width:<?= number_format($paidPct, 1, '.', '') ?>%"></span></span>
                                </td>
                                <td class="num-tabular" data-label="<?= Security::e($labels['left']) ?>">
                                    <strong><?= Security::formatMoney($ln['outstanding_balance']) ?></strong>
                                </td>
                                <td data-label="<?= Security::e($labels['status']) ?>">
                                    <span class="adm-status <?= $statusClass ?>"><?= Security::e($statusText) ?></span>
                                    <span class="fin-sub num-tabular"><?= Security::e($ln['maturity_date']) ?></span>
                                </td>
                                <td class="fin-end">
                                    <div class="adm-actions">
                                        <a href="?route=financing_view&id=<?= (int) $ln['id'] ?>" class="adm-icon-btn" title="<?= $isSo ? 'Fiiri Faahfaahinta' : 'View Details' ?>" aria-label="<?= $isSo ? 'Fiiri Faahfaahinta' : 'View Details' ?>"><?= Icons::svg('eye', 15) ?></a>
                                        <a href="?route=statement_pdf&id=<?= (int) $ln['id'] ?>" class="adm-icon-btn" title="PDF Statement" aria-label="PDF Statement"><?= Icons::svg('download', 15) ?></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="dash-empty" data-filter-empty="loanRows" hidden>
                <span class="dash-empty-icon"><?= Icons::svg('search', 20) ?></span>
                <strong><?= $isSo ? 'Wax lama helin' : 'No matches' ?></strong>
            </div>
        </div>
    <?php endif; ?>
</div>
