<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$currentUserId = (int) ($user['id'] ?? 0);
$isSo = Lang::locale() === 'so';
$queue = $queue ?? [];

// Employees no longer approve, so the lowest level is decided by the Branch Manager.
$levelOf = static fn(string $lvl): string => $lvl === 'manager' ? 'manager' : 'branch_manager';
$levelLabel = static fn(string $lvl): string => $lvl === 'manager' ? 'General Manager' : 'Branch Manager';

$totalAmount = 0.0;
$levelCounts = ['branch_manager' => 0, 'manager' => 0];
$oldest = null;
foreach ($queue as $q) {
    $totalAmount += (float) $q['requested_amount'];
    $levelCounts[$levelOf((string) $q['approval_level'])]++;
    if (!empty($q['submitted_at']) && ($oldest === null || strtotime((string) $q['submitted_at']) < $oldest)) {
        $oldest = strtotime((string) $q['submitted_at']);
    }
}

$ago = static function (?string $when) use ($isSo): string {
    if (!$when) {
        return '—';
    }
    $mins = max(0, (int) floor((time() - strtotime($when)) / 60));
    if ($mins < 1) {
        return $isSo ? 'hadda' : 'just now';
    }
    if ($mins < 60) {
        return $isSo ? "{$mins} daqiiqo ka hor" : "{$mins} min ago";
    }
    $h = intdiv($mins, 60);
    if ($h < 24) {
        return $isSo ? "{$h} saac ka hor" : "{$h} h ago";
    }
    $d = intdiv($h, 24);
    return $isSo ? "{$d} maalmood ka hor" : "{$d} days ago";
};
$dash = static fn($v): string => ($v === null || trim((string) $v) === '') ? '—' : (string) $v;

$kpis = [
    ['label' => $isSo ? 'Codsiyada sugaya' : 'Waiting', 'value' => (string) count($queue), 'sub' => $oldest ? (($isSo ? 'Kan ugu da\'da weyn: ' : 'Oldest: ') . $ago(date('Y-m-d H:i:s', $oldest))) : ($isSo ? 'Ma jiro' : 'None'), 'icon' => 'clock', 'tone' => 'gold'],
    ['label' => $isSo ? 'Lacagta la codsaday' : 'Amount requested', 'value' => Security::formatMoney($totalAmount), 'sub' => $isSo ? 'Wadarta codsiyada' : 'All waiting requests', 'icon' => 'dollar-sign', 'tone' => 'green'],
    ['label' => 'Branch Manager', 'value' => (string) $levelCounts['branch_manager'], 'sub' => $isSo ? 'Ilaa $50,000' : 'Up to $50,000', 'icon' => 'user-check', 'tone' => 'info'],
    ['label' => 'General Manager', 'value' => (string) $levelCounts['manager'], 'sub' => $isSo ? 'Ka badan $50,000' : 'Above $50,000', 'icon' => 'briefcase', 'tone' => 'muted'],
];
?>
<div class="adm">
    <header class="adm-head">
        <h1><?= $isSo ? 'Codsiyada' : 'Requests' ?></h1>
        <p><?= $isSo
            ? 'Codsiyada dayn iyo Murabaxo ee shaqaaluhu soo gudbiyeen. Eeg xogta, kadib aqbal ama diid; macmiilka email ayaa loo dirayaa.'
            : 'Loan and Murabaha requests sent by staff. Review the details, then approve or reject; the client is emailed either way.' ?></p>
    </header>

    <section class="dash-kpis" aria-label="<?= $isSo ? 'Tirooyinka muhiimka ah' : 'Key figures' ?>">
        <?php foreach ($kpis as $kpi): ?>
            <div class="kpi-card dash-kpi dash-tone-<?= Security::e($kpi['tone']) ?>">
                <div class="kpi-header">
                    <span class="kpi-label"><?= Security::e($kpi['label']) ?></span>
                    <span class="kpi-icon"><?= Icons::svg($kpi['icon'], 17) ?></span>
                </div>
                <div class="kpi-value num-tabular"><?= Security::e($kpi['value']) ?></div>
                <div class="kpi-sub"><?= Security::e($kpi['sub']) ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <?php if (!$queue): ?>
        <div class="adm-table-card">
            <div class="dash-empty">
                <span class="dash-empty-icon"><?= Icons::svg('check-circle', 20) ?></span>
                <strong><?= $isSo ? 'Ma jiro codsi sugaya' : 'No requests waiting' ?></strong>
                <p><?= $isSo ? 'Dhammaan codsiyadii la soo gudbiyay waa la eegay.' : 'Every submitted request has been decided.' ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="adm-toolbar">
            <?php if ($levelCounts['branch_manager'] > 0 && $levelCounts['manager'] > 0): ?>
                <select class="form-control adm-select" data-filter-for="queueList" data-filter-attr="level" aria-label="<?= $isSo ? 'Heerka' : 'Level' ?>">
                    <option value=""><?= $isSo ? 'Dhammaan Heerarka' : 'All levels' ?></option>
                    <option value="branch_manager">Branch Manager</option>
                    <option value="manager">General Manager</option>
                </select>
            <?php endif; ?>
            <div class="adm-search">
                <?= Icons::svg('search', 15) ?>
                <input type="search" class="form-control" data-filter-for="queueList" placeholder="<?= $isSo ? 'Raadi macmiil, codsi, shaqaale...' : 'Search client, request, staff...' ?>" aria-label="<?= $isSo ? 'Raadi' : 'Search' ?>" autocomplete="off">
            </div>
        </div>

        <div class="rq-list" id="queueList" data-filter-list>
            <?php foreach ($queue as $q): ?>
                <?php
                $isCreator = ((int) $q['created_by'] === $currentUserId) && (($user['role_slug'] ?? '') !== 'admin');
                $isMurabaha = $q['product_type'] === 'murabaha';
                $level = $levelOf((string) $q['approval_level']);
                $term = (int) $q['term_installments'];
                $weekly = $q['repayment_frequency'] === 'weekly';
                $perInstallment = $term > 0 ? (float) $q['projected_total_due'] / $term : (float) $q['projected_total_due'];
                $termText = $isSo
                    ? $term . ' qaybood oo ' . ($weekly ? 'toddobaadle' : 'bille') . ' ah'
                    : $term . ' ' . ($weekly ? 'weekly' : 'monthly') . ' installments';
                // Murabaha goods are saved as "Item name — description"; show the name on its own.
                $goods = trim((string) ($q['murabaha_goods_description'] ?? ''));
                $goodsParts = $goods !== '' ? explode(' — ', $goods, 2) : [];
                $itemName = $goodsParts[0] ?? '';
                $itemDesc = $goodsParts[1] ?? '';
                $hist = $q['history'] ?? [];
                $histLate = count(array_filter($hist, static fn($h) => $h['late']));
                $histOpen = count(array_filter($hist, static fn($h) => $h['state'] === 'processing'));
                $city = trim(($q['client_city'] ?? '') . ', ' . ($q['client_district'] ?? ''), ', ');
                ?>
                <article class="rq-card <?= $histLate ? 'is-risky' : '' ?>" data-filter-item data-level="<?= Security::e($level) ?>">
                    <header class="rq-head">
                        <div class="rq-who">
                            <span class="rq-avatar"><?= Security::e(Security::initials((string) $q['client_name'])) ?></span>
                            <div>
                                <h3><?= Security::e($q['client_name']) ?></h3>
                                <span class="num-tabular"><?= Security::e($q['client_code']) ?> · <span dir="ltr"><?= Security::e($q['client_phone']) ?></span></span>
                            </div>
                        </div>
                        <div class="rq-money">
                            <strong class="num-tabular"><?= Security::formatMoney($q['requested_amount']) ?></strong>
                            <span class="num-tabular">
                                <?= $isSo ? 'Wadarta' : 'Total' ?> <?= Security::formatMoney($q['projected_total_due']) ?>
                                · <?= Security::formatMoney($perInstallment) ?>/<?= $weekly ? ($isSo ? 'toddobaad' : 'wk') : ($isSo ? 'bishii' : 'mo') ?>
                            </span>
                        </div>
                    </header>

                    <div class="rq-tags">
                        <span class="rq-tag is-<?= $isMurabaha ? 'gold' : 'green' ?>"><?= Icons::svg($isMurabaha ? 'shopping-bag' : 'credit-card', 13) ?> <?= $isMurabaha ? ($isSo ? 'Murabaxo' : 'Murabaha') : ($isSo ? 'Dayn Cadaan' : 'Cash Loan') ?></span>
                        <span class="rq-tag is-info"><?= Icons::svg('user-check', 13) ?> <?= Security::e($levelLabel($level)) ?></span>
                        <span class="rq-tag"><?= Icons::svg('bar-chart', 13) ?> <?= $isSo ? 'Dhibcaha' : 'Score' ?> <?= (int) ($q['current_score'] ?? 600) ?></span>
                        <span class="rq-tag is-wait"><?= Icons::svg('clock', 13) ?> <?= Security::e($ago($q['submitted_at'] ?? null)) ?></span>
                    </div>

                    <div class="rq-grid">
                        <section class="rq-panel">
                            <h4><?= Icons::svg('file-text', 14) ?> <?= $isSo ? 'Codsiga' : 'Request' ?></h4>
                            <?php if ($itemName !== ''): ?>
                                <div class="rq-item">
                                    <strong><?= Security::e($itemName) ?></strong>
                                    <?php if ($itemDesc !== ''): ?><span><?= Security::e($itemDesc) ?></span><?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <dl class="rq-dl">
                                <div><dt><?= $isSo ? 'Lambarka' : 'Number' ?></dt><dd class="num-tabular"><?= Security::e($q['application_number']) ?></dd></div>
                                <div><dt><?= $isSo ? 'Muddada' : 'Term' ?></dt><dd><?= Security::e($termText) ?></dd></div>
                                <div><dt><?= $isSo ? 'Ujeeddada' : 'Purpose' ?></dt><dd><?= Security::e($dash($q['purpose'])) ?></dd></div>
                                <div><dt><?= $isSo ? 'Laanta' : 'Branch' ?></dt><dd><?= Security::e($q['branch_name']) ?></dd></div>
                                <div><dt><?= $isSo ? 'Waxaa diray' : 'Sent by' ?></dt><dd><?= Security::e($q['creator_name']) ?> <small class="num-tabular"><?= !empty($q['submitted_at']) ? Security::e(date('d/m/Y H:i', strtotime((string) $q['submitted_at']))) : '' ?></small></dd></div>
                                <?php if (!empty($q['from_form'])): ?>
                                    <div><dt><?= $isSo ? 'Lacagta loo dirayo' : 'Pay out to' ?></dt><dd><?= Security::e($dash($q['payout_provider'])) ?> · <span dir="ltr" class="num-tabular"><?= Security::e($dash($q['payout_phone'])) ?></span></dd></div>
                                <?php endif; ?>
                            </dl>
                        </section>

                        <section class="rq-panel">
                            <h4><?= Icons::svg('user', 14) ?> <?= $isSo ? 'Macmiilka' : 'Client' ?>
                                <a href="?route=client_view&id=<?= (int) $q['client_row_id'] ?>" class="rq-link"><?= $isSo ? 'Fur bogga' : 'Open profile' ?> <?= Icons::svg('arrow-right', 12) ?></a>
                            </h4>
                            <dl class="rq-dl">
                                <div><dt>Email</dt><dd><?= Security::e($dash($q['client_email'] ?? '')) ?></dd></div>
                                <div><dt><?= $isSo ? 'Aqoonsiga' : 'ID number' ?></dt><dd class="num-tabular"><?= Security::e($dash($q['client_id_number'] ?? '')) ?></dd></div>
                                <div><dt><?= $isSo ? 'Magaalada' : 'City' ?></dt><dd><?= Security::e($dash($city)) ?></dd></div>
                                <div><dt><?= $isSo ? 'Ganacsiga' : 'Business' ?></dt><dd><?= Security::e($dash($q['business_type'] ?? '')) ?></dd></div>
                                <div><dt><?= $isSo ? 'Dakhliga bishii' : 'Monthly income' ?></dt><dd class="num-tabular"><?= (float) ($q['monthly_income'] ?? 0) > 0 ? Security::formatMoney($q['monthly_income']) : '—' ?></dd></div>
                                <div><dt><?= $isSo ? 'Damiinka' : 'Guarantor' ?></dt><dd><?= Security::e($dash($q['guarantor_name'] ?? '')) ?><?php if (!empty($q['guarantor_phone'])): ?> <small dir="ltr" class="num-tabular"><?= Security::e($q['guarantor_phone']) ?></small><?php endif; ?></dd></div>
                            </dl>
                        </section>
                    </div>

                    <div class="rq-history <?= $histLate ? 'is-late' : ($hist ? 'is-known' : 'is-new') ?>">
                        <?= Icons::svg($histLate ? 'alert-triangle' : ($hist ? 'info' : 'check-circle'), 15) ?>
                        <span>
                            <?php if (!$hist): ?>
                                <?= $isSo ? 'Macmiil cusub: dayn ama Murabaxo hore ma qaadan.' : 'New client: no previous loan or Murabaha.' ?>
                            <?php else: ?>
                                <?= $isSo ? count($hist) . ' maalgelin hore · ' . $histOpen . ' weli socota' : count($hist) . ' previous financing · ' . $histOpen . ' still being repaid' ?>
                                <?php if ($histLate): ?> · <strong><?= $isSo ? $histLate . ' waqtigii laguma bixin' : $histLate . ' not paid on time' ?></strong><?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <footer class="rq-foot">
                        <?php if ($isCreator): ?>
                            <span class="adm-status is-warn"><?= $isSo ? 'Adigaa sameeyay: qof kale ayaa go\'aaminaya' : 'You created this: someone else must decide' ?></span>
                        <?php else: ?>
                            <form method="POST" action="?route=application_review" class="apq-decide rq-decide" data-confirm="<?= $isSo ? "Ma hubtaa go'aankan?" : 'Confirm this decision?' ?>">
                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                <input type="hidden" name="application_id" value="<?= (int) $q['id'] ?>">
                                <input type="text" name="comments" class="form-control" maxlength="500" placeholder="<?= $isSo ? 'Faallo: waa qasab haddii aad diiddo (macmiilka ayaa loo dirayaa)' : 'Note: required to reject (emailed to the client)' ?>" aria-label="<?= $isSo ? 'Faallo' : 'Note' ?>">
                                <button type="submit" name="decision" value="rejected" class="btn rq-reject"><?= Icons::svg('x-circle', 15) ?> <span><?= $isSo ? 'Diid' : 'Reject' ?></span></button>
                                <button type="submit" name="decision" value="approved" class="btn rq-approve"><?= Icons::svg('check-circle', 15) ?> <span><?= !empty($q['from_form']) ? ($isSo ? 'Aqbal & Bixi' : 'Approve & Pay Out') : ($isSo ? 'Aqbal' : 'Approve') ?></span></button>
                            </form>
                        <?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="adm-table-card" data-filter-empty="queueList" hidden>
            <div class="dash-empty">
                <span class="dash-empty-icon"><?= Icons::svg('search', 20) ?></span>
                <strong><?= $isSo ? 'Codsi lama helin' : 'No matching requests' ?></strong>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// A rejection reason is emailed to the client, so it is required; approving needs no note.
document.querySelectorAll('.apq-decide').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        const btn = e.submitter;
        const note = form.querySelector('input[name="comments"]');
        if (btn && btn.value === 'rejected' && note && note.value.trim() === '') {
            e.preventDefault();
            e.stopImmediatePropagation();
            note.classList.add('is-invalid');
            note.placeholder = <?= json_encode($isSo ? 'Fadlan qor sababta diidmada' : 'Please write the reason for rejecting') ?>;
            note.focus();
        }
    });
});
</script>
