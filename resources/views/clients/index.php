<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isSo = Lang::locale() === 'so';
$role = $user['role_slug'] ?? '';
$isAdmin = $role === 'admin';

$total = count($clients);
$withFinancing = 0;
$scoreSum = 0;
$lowScore = 0;
foreach ($clients as $c) {
    $score = (int) ($c['current_score'] ?? 600);
    $scoreSum += $score;
    if ((int) ($c['active_financings'] ?? 0) > 0) {
        $withFinancing++;
    }
    if (in_array($c['score_category'] ?? 'new', ['caution', 'high_risk'], true)) {
        $lowScore++;
    }
}
$kpis = [
    ['label' => $isSo ? 'Macaamiisha' : 'Clients', 'value' => $total, 'sub' => $isSo ? 'La diiwaangeliyay' : 'Registered', 'icon' => 'users', 'tone' => 'info'],
    ['label' => $isSo ? 'Maalgelin Socota' : 'Active Financing', 'value' => $withFinancing, 'sub' => $isSo ? 'Macmiil lacag bixinaya' : 'Clients still paying', 'icon' => 'credit-card', 'tone' => 'green'],
    ['label' => $isSo ? 'Celceliska Dhibcaha' : 'Average Score', 'value' => ($total > 0 ? (int) round($scoreSum / $total) : 600) . '/850', 'sub' => $isSo ? 'Dhibcaha credit-ka (300–850)' : 'Credit score (300–850)', 'icon' => 'bar-chart', 'tone' => 'gold'],
    ['label' => $isSo ? 'Khatar / Taxaddar' : 'At Risk', 'value' => $lowScore, 'sub' => $isSo ? 'Waqtiga kuma bixiyaan' : 'Paid late before', 'icon' => 'alert-triangle', 'tone' => $lowScore > 0 ? 'danger' : 'muted'],
];

$branchNames = [];
foreach ($clients as $c) {
    $branchNames[(string) $c['branch_name']] = true;
}
ksort($branchNames);

$activeFinancingsByClient = [];
foreach (($activeFinancings ?? []) as $af) {
    $activeFinancingsByClient[(int)$af['client_id']][] = [
        'id' => (int) $af['id'],
        'number' => $af['financing_number'],
        'product' => $af['product_type'],
        'balance' => (float) $af['outstanding_balance'],
        'status' => $af['status'],
        'credit_balance' => (float) ($af['credit_balance'] ?? 0),
    ];
}

$labels = [
    'client' => $isSo ? 'Macmiilka' : 'Client',
    'contact' => $isSo ? 'Xiriirka & Aqoonsiga' : 'Contact & ID',
    'branch' => $isSo ? 'Laanta' : 'Branch',
    'business' => $isSo ? 'Ganacsiga' : 'Business',
    'score' => $isSo ? 'Dhibcaha Credit' : 'Credit Score',
    'actions' => $isSo ? 'Ficilada / Lacag-Qabasho' : 'Actions / Repayment',
    'move' => $isSo ? 'U wareeji laan' : 'Move to branch',
];
?>
<style>
.client-clickable-row {
    cursor: pointer;
    transition: background 0.15s ease;
}
.client-clickable-row:hover td {
    background: rgba(35,132,91,0.04) !important;
}
.repay-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(10, 26, 22, 0.75);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: simdaFadeIn 0.2s ease;
}
.repay-modal-box {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    width: 100%;
    max-width: 580px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.35);
    overflow: hidden;
    animation: simdaScaleUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes simdaFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes simdaScaleUp {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>
<div class="adm">
    <header class="adm-head adm-page-head">
        <div>
            <h1><?= Security::e(Lang::get('menu_all_clients')) ?></h1>
            <p><?= $isSo ? 'Macaamiisha la diiwaangeliyay, ganacsigooda iyo dhibcahooda credit-ka.' : 'Registered clients, their business and credit score.' ?></p>
        </div>
        <?php if (in_array($role, ['admin', 'loan_officer'], true)): ?>
            <div class="adm-head-actions">
                <a href="?route=client_register" class="btn btn-sm btn-gold"><?= Icons::svg('user-plus', 14) ?> <span><?= $isSo ? 'Macmiil Cusub' : 'New Client' ?></span></a>
            </div>
        <?php endif; ?>
    </header>

    <section class="dash-kpis" aria-label="<?= $isSo ? 'Tirooyinka muhiimka ah' : 'Key figures' ?>">
        <?php foreach ($kpis as $kpi): ?>
            <div class="kpi-card dash-kpi dash-tone-<?= Security::e($kpi['tone']) ?>">
                <div class="kpi-header">
                    <span class="kpi-label"><?= Security::e($kpi['label']) ?></span>
                    <span class="kpi-icon"><?= Icons::svg($kpi['icon'], 17) ?></span>
                </div>
                <div class="kpi-value num-tabular"><?= (int) $kpi['value'] ?></div>
                <div class="kpi-sub"><?= Security::e($kpi['sub']) ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <?php if (empty($clients)): ?>
        <div class="adm-table-card">
            <div class="dash-empty">
                <span class="dash-empty-icon"><?= Icons::svg('users', 20) ?></span>
                <strong><?= Security::e(Lang::get('empty_state_title')) ?></strong>
                <p><?= Security::e(Lang::get('empty_state_desc')) ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="adm-toolbar">
            <?php if (count($branchNames) > 1): ?>
                <select class="form-control adm-select" data-filter-for="clientRows" data-filter-attr="branch" aria-label="<?= Security::e($labels['branch']) ?>">
                    <option value=""><?= $isSo ? 'Dhammaan Laamaha' : 'All Branches' ?></option>
                    <?php foreach (array_keys($branchNames) as $branchName): ?>
                        <option value="<?= Security::e($branchName) ?>"><?= Security::e($branchName) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <div class="adm-search">
                <?= Icons::svg('search', 15) ?>
                <input type="search" class="form-control" data-filter-for="clientRows" placeholder="<?= $isSo ? 'Raadi magac, telefoon, email, aqoonsi...' : 'Search name, phone, email, ID...' ?>" aria-label="<?= $isSo ? 'Raadi' : 'Search' ?>" autocomplete="off">
            </div>
        </div>

        <div class="adm-table-card">
            <div class="table-responsive adm-scroll">
                <table class="simda-table fin-table">
                    <thead>
                        <tr>
                            <th><?= Security::e($labels['client']) ?></th>
                            <th><?= Security::e($labels['contact']) ?></th>
                            <th><?= Security::e($labels['branch']) ?></th>
                            <th><?= Security::e($labels['business']) ?></th>
                            <th><?= Security::e($labels['score']) ?></th>
                            <th style="text-align:right;padding-right:16px;"><?= Security::e($labels['actions']) ?></th>
                            <?php if ($isAdmin): ?>
                                <th class="fin-end"><?= Security::e($labels['move']) ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="clientRows" data-filter-list>
                        <?php foreach ($clients as $c): ?>
                            <?php
                            $score = (int) ($c['current_score'] ?? 600);
                            $scoreCat = (string) ($c['score_category'] ?? 'new');
                            $scoreTone = \App\Services\CreditScoreEngine::tone($scoreCat);
                            $activeCount = (int) ($c['active_financings'] ?? 0);
                            $cFinancings = $activeFinancingsByClient[(int)$c['id']] ?? [];
                            $cJson = htmlspecialchars(json_encode([
                                'id' => (int) $c['id'],
                                'name' => $c['full_name'],
                                'code' => $c['client_code'],
                                'phone' => $c['phone'],
                                'email' => $c['email'] ?? '',
                                'financings' => $cFinancings,
                            ]), ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr data-filter-item
                                class="client-clickable-row"
                                data-branch="<?= Security::e($c['branch_name']) ?>"
                                data-search="<?= Security::e(strtolower($c['full_name'] . ' ' . $c['client_code'] . ' ' . $c['phone'] . ' ' . ($c['email'] ?? '') . ' ' . $c['id_number'] . ' ' . $c['business_type'] . ' ' . $c['branch_name'])) ?>">
                                
                                <td data-label="<?= Security::e($labels['client']) ?>" onclick="handleClientRowClick(<?= $cJson ?>)">
                                    <div class="adm-person">
                                        <span class="adm-avatar dash-tone-green"><?= Security::e(Security::initials((string) $c['full_name'])) ?></span>
                                        <div class="adm-person-text">
                                            <span class="adm-primary" style="font-weight:800;"><?= Security::e($c['full_name']) ?></span>
                                            <span class="adm-secondary num-tabular" style="font-weight:700;color:var(--simda-green);"><?= Security::e($c['client_code']) ?></span>
                                        </div>
                                    </div>
                                </td>

                                <td data-label="<?= Security::e($labels['contact']) ?>" onclick="handleClientRowClick(<?= $cJson ?>)">
                                    <span class="num-tabular" dir="ltr" style="font-weight:700;"><?= Security::e($c['phone']) ?></span>
                                    <?php if (!empty($c['email'])): ?>
                                        <span class="fin-sub" style="display:flex;align-items:center;gap:4px;color:var(--text-main);font-weight:600;margin-top:2px;">
                                            <span style="color:var(--simda-green);"><?= Icons::svg('mail', 11) ?></span>
                                            <?= Security::e($c['email']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="fin-sub num-tabular"><?= Security::e($c['id_number']) ?> &bull; <?= Security::e(str_replace('_', ' ', (string) $c['id_type'])) ?></span>
                                </td>

                                <td data-label="<?= Security::e($labels['branch']) ?>" onclick="handleClientRowClick(<?= $cJson ?>)">
                                    <div style="font-weight:700;display:flex;align-items:center;gap:4px;">
                                        <?= Icons::svg('building', 12) ?> <?= Security::e($c['branch_name']) ?>
                                    </div>
                                    <span class="fin-sub"><?= Security::e($c['district'] . ', ' . $c['city']) ?></span>
                                </td>

                                <td data-label="<?= Security::e($labels['business']) ?>" onclick="handleClientRowClick(<?= $cJson ?>)">
                                    <?= Security::e($c['business_type']) ?>
                                    <span class="fin-sub num-tabular"><?= Security::formatMoney($c['monthly_income']) ?><?= $isSo ? ' bishii' : '/mo' ?></span>
                                </td>

                                <td class="num-tabular" data-label="<?= Security::e($labels['score']) ?>" onclick="handleClientRowClick(<?= $cJson ?>)">
                                    <span class="adm-role dash-tone-<?= $scoreTone ?>"><?= $score ?>/850 &bull; <?= Security::e(\App\Services\CreditScoreEngine::label($scoreCat, $isSo)) ?></span>
                                    <span class="fin-sub">
                                        <?= (int) $c['guarantors_count'] ?> <?= $isSo ? 'damiin' : 'guarantor(s)' ?>
                                        <?php if ($activeCount > 0): ?>
                                            &bull; <strong style="color:var(--simda-green);"><?= $activeCount ?> <?= $isSo ? 'socda' : 'active' ?></strong>
                                        <?php endif; ?>
                                    </span>
                                </td>

                                <!-- ACTIONS: RECORD REPAYMENT BUTTON -->
                                <td style="text-align:right;white-space:nowrap;padding-right:16px;">
                                    <a href="?route=client_view&id=<?= (int) $c['id'] ?>" class="btn btn-xs btn-outline" style="font-size:0.75rem;padding:4px 10px;margin-right:4px;" title="<?= $isSo ? 'Eeg xogta oo dhan / Sii dayn' : 'View profile / Give financing' ?>">
                                        <?= Icons::svg('eye', 13) ?> <?= $isSo ? 'Xogta' : 'Profile' ?>
                                    </a>
                                    <?php if ($activeCount > 0): ?>
                                        <button type="button"
                                            class="btn btn-xs btn-primary"
                                            style="font-weight:800;display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:var(--simda-green);border-color:var(--simda-green);color:#FFFFFF;box-shadow:0 2px 6px rgba(35,132,91,0.25);"
                                            onclick="openQuickRepayModal(<?= $cJson ?>)"
                                            title="<?= $isSo ? 'Qabo lacagta uu soo celiyay macmiilkan' : 'Record repayment from this client' ?>">
                                            <?= Icons::svg('dollar-sign', 13) ?> <?= $isSo ? 'Soo Celi Lacag' : 'Collect Repayment' ?>
                                        </button>
                                    <?php else: ?>
                                        <button type="button"
                                            class="btn btn-xs btn-outline"
                                            style="font-size:0.75rem;padding:4px 10px;opacity:0.65;"
                                            onclick="openQuickRepayModal(<?= $cJson ?>)"
                                            title="<?= $isSo ? 'Macmiilkan ma laha deyn socota' : 'No active loans' ?>">
                                            <?= $isSo ? 'Deyn Ma Jirto' : 'No Active Loan' ?>
                                        </button>
                                    <?php endif; ?>
                                </td>

                                <?php if ($isAdmin): ?>
                                    <td class="fin-end" data-label="<?= Security::e($labels['move']) ?>">
                                        <form method="POST" action="?route=client_transfer" class="fin-transfer" data-confirm="<?= $isSo ? 'Ma u wareejinaysaa macmiilkan laanta la doortay?' : 'Transfer this client to the selected branch?' ?>">
                                            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                            <input type="hidden" name="client_id" value="<?= (int) $c['id'] ?>">
                                            <select name="new_branch_id" class="form-control" aria-label="<?= $isSo ? 'Laanta cusub' : 'New branch' ?>" style="font-size:0.78rem;padding:3px 6px;">
                                                <?php foreach ($branches as $b): ?>
                                                    <option value="<?= (int) $b['id'] ?>" <?= (int) $b['id'] === (int) $c['branch_id'] ? 'selected' : '' ?>><?= Security::e($b['code']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="adm-icon-btn" title="<?= $isSo ? 'Wareeji' : 'Transfer' ?>" aria-label="<?= $isSo ? 'Wareeji' : 'Transfer' ?> <?= Security::e($c['full_name']) ?>"><?= Icons::svg('check', 14) ?></button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="dash-empty" data-filter-empty="clientRows" hidden>
                <span class="dash-empty-icon"><?= Icons::svg('search', 20) ?></span>
                <strong><?= $isSo ? 'Macmiil lama helin' : 'No matching clients' ?></strong>
                <p><?= $isSo ? 'Hubi magaca, telefoonka, ama email-ka aad qortay.' : 'Check the name, phone number or email entered.' ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- QUICK REPAYMENT MODAL DIALOG                                              -->
<!-- ========================================================================= -->
<div id="quickRepayModalOverlay" class="repay-modal-overlay" onclick="handleRepayModalBackdropClick(event)">
    <div class="repay-modal-box" role="dialog" aria-modal="true" aria-labelledby="repayModalTitle">
        <!-- Header -->
        <div style="background:var(--bg-surface);padding:18px 22px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="fid-contact-dot" style="background:var(--simda-green);color:#FFFFFF;width:32px;height:32px;">
                    <?= Icons::svg('dollar-sign', 16) ?>
                </span>
                <div>
                    <h3 id="repayModalTitle" style="font-size:1.08rem;font-weight:900;margin:0;color:var(--text-main);">
                        <?= $isSo ? 'Soo Celinta Lacagta Macmiilka (Record Repayment)' : 'Record Client Repayment' ?>
                    </h3>
                    <div id="repayModalClientSubtitle" style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
                        <!-- Injected by JS: e.g. Faadumo Maxamed Cali (CL-2026-0001) -->
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-xs btn-outline" onclick="closeQuickRepayModal()" style="padding:4px 8px;font-size:1rem;line-height:1;border-radius:8px;">✕</button>
        </div>

        <!-- Form -->
        <form method="POST" action="?route=repayment_submit" id="quickRepayForm" style="padding:22px;">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

            <!-- Active Financing Select -->
            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:800;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);">
                    <?= $isSo ? 'Heshiiska Lacagta Loo Soo Celiyay *' : 'Select Financing Contract to Repay *' ?>
                </label>
                <select name="financing_id" id="repayModalFinancingSelect" class="form-control" required style="font-weight:700;" onchange="handleFinancingChange()">
                    <!-- Options injected dynamically by JS -->
                </select>
                <div id="repayModalBalanceNotice" style="font-size:0.8rem;color:var(--simda-green);font-weight:700;margin-top:5px;display:none;"></div>
                <div id="repayModalNoDebtNotice" style="display:none;background:rgba(214,182,108,0.15);border:1px solid rgba(214,182,108,0.3);padding:10px 14px;border-radius:8px;font-size:0.82rem;color:var(--text-main);margin-top:6px;">
                    <?= $isSo ? '💡 Macmiilkani hadda kuma laha nidaamka wax deyn ama murabaxo socota oo baaqi ah.' : 'Notice: This client has no active loan or financing contract.' ?>
                </div>
            </div>

            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <!-- Amount -->
                <div class="form-group">
                    <label style="font-weight:800;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);">
                        <?= $isSo ? 'Cadadka La Soo Celiyay ($ USD) *' : 'Amount Repaid ($ USD) *' ?>
                    </label>
                    <input type="number" name="amount" id="repayModalAmountInput" class="form-control num-tabular" step="0.01" min="1" required placeholder="0.00" style="font-size:1.15rem;font-weight:900;color:var(--simda-green);">
                </div>

                <!-- Payment Method -->
                <div class="form-group">
                    <label style="font-weight:800;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);">
                        <?= $isSo ? 'Qaabka Bixinta *' : 'Payment Method *' ?>
                    </label>
                    <select name="payment_method" id="repayModalPaymentMethod" class="form-control" required style="font-weight:700;">
                        <option value="EVC Plus">EVC Plus (Hormuud)</option>
                        <option value="Zaad">Zaad (Telesom)</option>
                        <option value="eDahab">eDahab (Somtel)</option>
                        <option value="Cash"><?= $isSo ? 'Lacag Cadaan ah (Cash)' : 'Cash' ?></option>
                    </select>
                </div>

                <!-- Transaction Reference -->
                <div class="form-group">
                    <label style="font-weight:800;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);">
                        <?= $isSo ? 'Lambarka Tixraaca (Ref #) *' : 'Transaction Ref # *' ?>
                    </label>
                    <input type="text" name="transaction_reference" id="repayModalTxRef" class="form-control num-tabular" required style="font-family:monospace;font-weight:700;">
                </div>

                <!-- Payment Date -->
                <div class="form-group">
                    <label style="font-weight:800;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);">
                        <?= $isSo ? 'Taariikhda Bixinta *' : 'Payment Date *' ?>
                    </label>
                    <input type="date" name="payment_date" id="repayModalDate" class="form-control num-tabular" required value="<?= date('Y-m-d') ?>" style="font-weight:700;">
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:700;font-size:0.82rem;margin-bottom:4px;display:block;color:var(--text-muted);">
                    <?= $isSo ? 'Faahfaahin / Qoraal (Ikhtiyaari)' : 'Notes / Remarks (Optional)' ?>
                </label>
                <input type="text" name="notes" id="repayModalNotes" class="form-control" placeholder="<?= $isSo ? 'Tusaale: Bixinta bishan' : 'e.g. Monthly installment' ?>">
            </div>

            <!-- Client Email Notification Notice -->
            <div id="repayModalEmailNotice" style="background:rgba(35,132,91,0.08);border:1px solid rgba(35,132,91,0.25);border-radius:10px;padding:12px 14px;font-size:0.8rem;color:var(--text-main);line-height:1.45;margin-bottom:18px;">
                <!-- Injected by JS: e.g. Rasiidka waxaa toos loogu diri doonaa: client@gmail.com -->
            </div>

            <!-- Actions Footer -->
            <div style="display:flex;justify-content:flex-end;gap:10px;align-items:center;padding-top:14px;border-top:1px solid var(--border-color);">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeQuickRepayModal()" style="font-weight:700;padding:8px 18px;">
                    <?= $isSo ? 'Ka noqo' : 'Cancel' ?>
                </button>
                <button type="submit" id="repayModalSubmitBtn" class="btn btn-primary btn-sm" style="font-weight:800;padding:9px 24px;display:inline-flex;align-items:center;gap:6px;">
                    <?= Icons::svg('check-circle', 14) ?> <?= $isSo ? 'Xaqiiji & Keydi Lacag-Bixinta' : 'Confirm & Save Repayment' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function handleClientRowClick(client) {
    openQuickRepayModal(client);
}

function openQuickRepayModal(client) {
    const overlay = document.getElementById('quickRepayModalOverlay');
    if (!overlay) return;

    // Set subtitle
    const sub = document.getElementById('repayModalClientSubtitle');
    if (sub) {
        sub.innerHTML = '<strong>' + escapeHtml(client.name) + '</strong> (' + escapeHtml(client.code) + ') &bull; ' + escapeHtml(client.phone);
    }

    // Populate financings select
    const select = document.getElementById('repayModalFinancingSelect');
    const balanceNotice = document.getElementById('repayModalBalanceNotice');
    const noDebtNotice = document.getElementById('repayModalNoDebtNotice');
    const submitBtn = document.getElementById('repayModalSubmitBtn');
    const amountInput = document.getElementById('repayModalAmountInput');

    if (select) {
        select.innerHTML = '';
        if (client.financings && client.financings.length > 0) {
            select.style.display = 'block';
            if (noDebtNotice) noDebtNotice.style.display = 'none';
            if (submitBtn) submitBtn.disabled = false;
            if (amountInput) amountInput.disabled = false;

            client.financings.forEach(function (f, idx) {
                const opt = document.createElement('option');
                opt.value = f.id;
                opt.dataset.balance = f.balance;
                opt.dataset.number = f.number;
                opt.dataset.product = f.product;
                const prodText = f.product === 'murabaha' ? 'Murabaxo' : 'Dayn';
                opt.textContent = f.number + ' (' + prodText + ') — Baaqiga: $' + parseFloat(f.balance).toFixed(2) + ' USD';
                select.appendChild(opt);
            });
            handleFinancingChange();
        } else {
            select.style.display = 'none';
            if (balanceNotice) balanceNotice.style.display = 'none';
            if (noDebtNotice) noDebtNotice.style.display = 'block';
            if (submitBtn) submitBtn.disabled = true;
            if (amountInput) amountInput.disabled = true;
        }
    }

    // Auto-generate Transaction Ref
    const txRef = document.getElementById('repayModalTxRef');
    if (txRef) {
        const randHex = Math.random().toString(16).substr(2, 6).toUpperCase();
        txRef.value = 'MM-' + randHex;
    }

    // Email notice
    const emailNotice = document.getElementById('repayModalEmailNotice');
    if (emailNotice) {
        if (client.email) {
            emailNotice.innerHTML = '<?= Icons::svg('mail', 13) ?> <?= $isSo ? '<strong>Rasiidka toos ayaa loogu diri doonaa:</strong> ' : '<strong>Receipt will be emailed to:</strong> ' ?> <u>' + escapeHtml(client.email) + '</u> <?= $isSo ? 'iyo diiwaanka nidaamka SIMDA.' : 'and logged in SIMDA records.' ?>';
        } else {
            emailNotice.innerHTML = '<?= Icons::svg('info', 13) ?> <?= $isSo ? 'Macmiilkan kuma qorna email. Rasiidka waxaa lagu keydin doonaa nidaamka waana la daabacan karaa.' : 'No email registered for client. Receipt will be saved in system and can be printed.' ?>';
        }
    }

    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function handleFinancingChange() {
    const select = document.getElementById('repayModalFinancingSelect');
    const balanceNotice = document.getElementById('repayModalBalanceNotice');
    const amountInput = document.getElementById('repayModalAmountInput');
    if (!select || select.options.length === 0) return;

    const opt = select.options[select.selectedIndex];
    if (opt && opt.dataset.balance) {
        const bal = parseFloat(opt.dataset.balance);
        if (balanceNotice) {
            balanceNotice.style.display = 'block';
            balanceNotice.textContent = '<?= $isSo ? 'Baaqiga Harsan:' : 'Outstanding Balance:' ?> $' + bal.toFixed(2) + ' USD';
        }
    }
}

function closeQuickRepayModal() {
    const overlay = document.getElementById('quickRepayModalOverlay');
    if (overlay) {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function handleRepayModalBackdropClick(event) {
    if (event.target === document.getElementById('quickRepayModalOverlay')) {
        closeQuickRepayModal();
    }
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeQuickRepayModal();
    }
});

function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}
</script>
