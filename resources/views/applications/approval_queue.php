<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Services\FinancingDomainService;

$csrf = Security::csrfToken();
$currentUserId = (int) ($user['id'] ?? 0);
$isSo = Lang::locale() === 'so';

$totalAmount = 0.0;
$levelCounts = ['loan_officer' => 0, 'branch_manager' => 0, 'manager' => 0];
foreach ($queue as $q) {
    $totalAmount += (float) $q['requested_amount'];
    $levelCounts[$q['approval_level']]++;
}
$kpis = [
    ['label' => $isSo ? 'Lacagta La Codsaday' : 'Amount Requested', 'value' => Security::formatMoney($totalAmount), 'sub' => count($queue) . ($isSo ? ' codsi sugaya' : ' waiting'), 'icon' => 'clock', 'tone' => 'gold'],
    ['label' => 'Header Office', 'value' => (string) $levelCounts['loan_officer'], 'sub' => $isSo ? '$5,000 iyo ka yar' : '$5,000 or less', 'icon' => 'shield', 'tone' => 'green'],
    ['label' => 'Branch Manager', 'value' => (string) $levelCounts['branch_manager'], 'sub' => '$5,000 – $50,000', 'icon' => 'user-check', 'tone' => 'info'],
    ['label' => 'General Manager', 'value' => (string) $levelCounts['manager'], 'sub' => $isSo ? 'Ka badan $50,000' : 'Above $50,000', 'icon' => 'briefcase', 'tone' => 'muted'],
];
?>
<div class="adm">
    <header class="adm-head">
        <h1><?= $isSo ? 'Codsiyada Sugaya' : 'Pending Applications' ?></h1>
        <p><?= $isSo ? 'Dadka codsi soo gudbiyay oo aan weli la aqbalin. Qofkii codsiga sameeyay ma ogolaan karo codsigiisa.' : 'People who submitted an application that is not yet approved. Whoever created an application cannot approve it.' ?></p>
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

    <?php if (empty($queue)): ?>
        <div class="adm-table-card">
            <div class="dash-empty">
                <span class="dash-empty-icon"><?= Icons::svg('check-circle', 20) ?></span>
                <strong><?= $isSo ? 'Ma jiro codsi sugaya' : 'No pending applications' ?></strong>
                <p><?= $isSo ? 'Dhammaan codsiyadii la soo gudbiyay waa la eegay.' : 'All submitted applications have been reviewed.' ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="adm-toolbar">
            <select class="form-control adm-select" data-filter-for="queueList" data-filter-attr="level" aria-label="<?= $isSo ? 'Heerka' : 'Level' ?>">
                <option value=""><?= $isSo ? 'Dhammaan Heerarka' : 'All Levels' ?></option>
                <?php foreach (array_keys($levelCounts) as $level): ?>
                    <?php if ($levelCounts[$level] > 0): ?>
                        <option value="<?= Security::e($level) ?>"><?= Security::e(FinancingDomainService::approverLabel($level)) ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <div class="adm-search">
                <?= Icons::svg('search', 15) ?>
                <input type="search" class="form-control" data-filter-for="queueList" placeholder="<?= $isSo ? 'Raadi macmiil, codsi, laan...' : 'Search client, application, branch...' ?>" aria-label="<?= $isSo ? 'Raadi' : 'Search' ?>" autocomplete="off">
            </div>
        </div>

        <div class="apq-list" id="queueList" data-filter-list>
            <?php foreach ($queue as $q): ?>
                <?php
                $isCreator = (int) $q['created_by'] === $currentUserId;
                $isMurabaha = $q['product_type'] === 'murabaha';
                $termText = $isSo
                    ? (int) $q['term_installments'] . ' qaybood oo ' . ($q['repayment_frequency'] === 'weekly' ? 'toddobaadle' : 'bille') . ' ah'
                    : (int) $q['term_installments'] . ' ' . $q['repayment_frequency'] . ' installments';
                ?>
                <article class="apq-card" data-filter-item data-level="<?= Security::e($q['approval_level']) ?>">
                    <div class="apq-top">
                        <div class="adm-person">
                            <span class="adm-avatar dash-tone-gold"><?= Security::e(Security::initials((string) $q['client_name'])) ?></span>
                            <div class="adm-person-text">
                                <span class="adm-primary"><?= Security::e($q['client_name']) ?></span>
                                <span class="adm-secondary num-tabular"><?= Security::e($q['client_code']) ?> · <span dir="ltr"><?= Security::e($q['client_phone']) ?></span></span>
                            </div>
                        </div>
                        <div class="apq-amount">
                            <strong class="num-tabular"><?= Security::formatMoney($q['requested_amount']) ?></strong>
                            <span class="adm-secondary num-tabular"><?= $isSo ? 'Wadarta' : 'Total' ?> <?= Security::formatMoney($q['projected_total_due']) ?></span>
                        </div>
                    </div>

                    <div class="apq-chips">
                        <span class="adm-role dash-tone-<?= $isMurabaha ? 'gold' : 'green' ?>"><?= $isMurabaha ? ($isSo ? 'Murabaxo' : 'Murabaha') : ($isSo ? 'Dayn Cadaan' : 'Cash Loan') ?></span>
                        <span class="adm-role dash-tone-info"><?= $isSo ? 'Heerka' : 'Level' ?>: <?= Security::e(FinancingDomainService::approverLabel($q['approval_level'])) ?></span>
                        <span class="adm-role dash-tone-muted num-tabular"><?= $isSo ? 'Dhibcaha' : 'Score' ?> <?= (int) ($q['current_score'] ?? 600) ?></span>
                    </div>

                    <dl class="apq-facts">
                        <div><dt><?= $isSo ? 'Codsiga' : 'Application' ?></dt><dd class="num-tabular"><?= Security::e($q['application_number']) ?></dd></div>
                        <div><dt><?= $isSo ? 'Laanta' : 'Branch' ?></dt><dd><?= Security::e($q['branch_name']) ?></dd></div>
                        <div><dt><?= $isSo ? 'Muddada' : 'Term' ?></dt><dd><?= Security::e($termText) ?></dd></div>
                        <div><dt><?= $isSo ? 'Waxaa sameeyay' : 'Created by' ?></dt><dd><?= Security::e($q['creator_name']) ?></dd></div>
                        <div><dt><?= $isSo ? 'Waqtiga' : 'Submitted' ?></dt><dd class="num-tabular"><?= !empty($q['submitted_at']) ? Security::e(date('d/m/Y H:i', strtotime((string) $q['submitted_at']))) : '—' ?></dd></div>
                        <div><dt><?= $isSo ? 'Ganacsiga' : 'Business' ?></dt><dd><?= Security::e($q['business_type']) ?></dd></div>
                        <div class="apq-wide"><dt><?= $isSo ? 'Ujeeddada' : 'Purpose' ?></dt><dd><?= Security::e($q['purpose']) ?><?= !empty($q['murabaha_goods_description']) ? ' · ' . Security::e($q['murabaha_goods_description']) : '' ?></dd></div>
                    </dl>

                    <div class="apq-foot">
                        <?php if ($isCreator): ?>
                            <span class="adm-status is-warn"><?= $isSo ? 'Adigaa sameeyay: qof kale ayaa ogolaanaya' : 'Created by you: someone else must approve it' ?></span>
                        <?php else: ?>
                            <form method="POST" action="?route=application_review" class="apq-decide" data-confirm="<?= $isSo ? "Ma hubtaa go'aankan?" : 'Confirm this decision?' ?>">
                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                <input type="hidden" name="application_id" value="<?= (int) $q['id'] ?>">
                                <input type="text" name="comments" class="form-control" required placeholder="<?= $isSo ? 'Faallo ama shuruudo...' : 'Comments or conditions...' ?>" aria-label="<?= $isSo ? 'Faallo' : 'Comments' ?>">
                                <button type="submit" name="decision" value="approved" class="btn btn-sm btn-primary"><?= Icons::svg('check-circle', 14) ?> <span><?= $isSo ? 'Aqbal' : 'Approve' ?></span></button>
                                <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-outline-danger"><?= Icons::svg('x-circle', 14) ?> <span><?= $isSo ? 'Diid' : 'Reject' ?></span></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="adm-table-card" data-filter-empty="queueList" hidden>
            <div class="dash-empty">
                <span class="dash-empty-icon"><?= Icons::svg('search', 20) ?></span>
                <strong><?= $isSo ? 'Codsi lama helin' : 'No matching applications' ?></strong>
            </div>
        </div>
    <?php endif; ?>
</div>
