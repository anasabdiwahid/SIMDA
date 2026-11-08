<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$locale = Lang::locale();
$isSo = ($locale === 'so');
$clients = $clients ?? [];
$months = [1, 2, 3, 4, 6, 9, 12, 18, 24, 36];
$quickAmounts = [300, 500, 1000, 2000, 3000, 5000];
$providers = ['EVC Plus' => 'Hormuud', 'Zaad' => 'Telesom', 'eDahab' => 'Somtel'];
$initials = static fn(string $n): string => mb_strtoupper(mb_substr(trim($n), 0, 1));
?>

<div class="mf-head">
    <div class="mf-head-title">
        <span class="mf-head-icon mf-head-icon-green"><?= Icons::svg('credit-card', 22) ?></span>
        <div>
            <h1><?= $isSo ? 'Dayn Lacag Ah (Qard Xasan)' : 'New Cash Loan (Qard Hassan)' ?></h1>
            <p><?= $isSo
                ? 'Dayn 0% ribo ah: macmiilku wuxuu celinayaa oo keliya lacagta la siiyay.'
                : 'Zero-riba cash financing: the client repays only the amount received.' ?></p>
        </div>
    </div>
    <a href="?route=loans" class="btn btn-outline btn-sm"><?= Icons::svg('arrow-left', 14) ?> <?= $isSo ? 'Ku noqo' : 'Back' ?></a>
</div>

<form method="POST" action="?route=loan_submit" id="loanNewForm" class="mf-layout" novalidate>
    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
    <input type="hidden" name="client_id" id="selectedClientId" value="<?= (int) ($selectedClientId ?? 0) ?>">

    <div class="mf-main">

        <!-- 1. CLIENT -->
        <section class="card mf-step">
            <header class="mf-step-head">
                <span class="mf-step-num">1</span>
                <div>
                    <h2><?= $isSo ? 'Macmiilka' : 'Borrower' ?></h2>
                    <p><?= $isSo ? 'Dooro macmiilka diiwaangashan ee daynta qaadanaya.' : 'Choose the registered client taking the loan.' ?></p>
                </div>
                <span class="mf-step-meta"><?= count($clients) ?> <?= $isSo ? 'macmiil' : 'clients' ?></span>
            </header>

            <div class="mf-client-selected" id="selectedClientBox" hidden>
                <span class="mf-avatar" id="clientAvatar">C</span>
                <div class="mf-client-info">
                    <strong id="clientNameDisplay"></strong>
                    <div class="mf-client-meta">
                        <span id="clientCodeDisplay" class="num-tabular"></span>
                        <span><?= Icons::svg('phone', 12) ?> <span id="clientPhoneDisplay"></span></span>
                        <span id="clientEmailWrap"><?= Icons::svg('send', 12) ?> <span id="clientEmailDisplay"></span></span>
                    </div>
                </div>
                <span class="mf-score" id="clientScoreDisplay">600</span>
                <button type="button" class="btn btn-outline btn-sm" id="changeClientBtn"><?= $isSo ? 'Beddel' : 'Change' ?></button>
            </div>

            <?php require __DIR__ . '/_client_history.php'; ?>

            <div class="mf-search" id="clientSearchWrapper">
                <span class="mf-search-icon"><?= Icons::svg('search', 17) ?></span>
                <input type="search" id="clientSearchInput" class="form-control" autocomplete="off"
                       placeholder="<?= $isSo ? 'Ku raadi magaca, telefoonka, email-ka ama ID-ga...' : 'Search by name, phone, email or ID...' ?>"
                       aria-controls="clientDropdownList">
                <div class="mf-dropdown" id="clientDropdownList" role="listbox" hidden>
                    <?php if (!$clients): ?>
                        <div class="mf-dropdown-empty">
                            <?= $isSo ? 'Weli macmiil ma jiro.' : 'No clients yet.' ?>
                            <a href="?route=client_register"><?= $isSo ? 'Diiwaangeli macmiil' : 'Register a client' ?></a>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($clients as $c): ?>
                        <button type="button" class="mf-option client-option-item" role="option"
                                data-id="<?= (int) $c['id'] ?>"
                                data-name="<?= Security::e($c['full_name']) ?>"
                                data-code="<?= Security::e($c['client_code']) ?>"
                                data-phone="<?= Security::e($c['phone']) ?>"
                                data-email="<?= Security::e($c['email'] ?? '') ?>"
                                data-idnum="<?= Security::e($c['id_number'] ?? '') ?>"
                                data-score="<?= (int) ($c['current_score'] ?? 600) ?>">
                            <span class="mf-avatar mf-avatar-sm"><?= Security::e($initials((string) $c['full_name'])) ?></span>
                            <span class="mf-option-text">
                                <strong><?= Security::e($c['full_name']) ?></strong>
                                <small><span class="num-tabular"><?= Security::e($c['client_code']) ?></span> · <?= Security::e($c['phone']) ?></small>
                            </span>
                            <span class="mf-score"><?= (int) ($c['current_score'] ?? 600) ?></span>
                        </button>
                    <?php endforeach; ?>
                    <div class="mf-dropdown-empty" id="clientNoMatch" hidden><?= $isSo ? 'Wax la mid ah lama helin.' : 'No matching client.' ?></div>
                </div>
            </div>
        </section>

        <!-- 2. AMOUNT & TERM -->
        <section class="card mf-step">
            <header class="mf-step-head">
                <span class="mf-step-num">2</span>
                <div>
                    <h2><?= $isSo ? 'Lacagta iyo muddada' : 'Amount and term' ?></h2>
                    <p><?= $isSo ? 'Inta la siinayo iyo waqtiga uu ku celinayo.' : 'How much to lend and how long to repay.' ?></p>
                </div>
            </header>

            <div class="form-group">
                <label for="loanAmountInput"><?= $isSo ? 'Lacagta daynta *' : 'Loan amount *' ?></label>
                <div class="mf-input mf-input-prefix mf-input-lg"><span>$</span>
                    <input type="number" name="requested_amount" id="loanAmountInput" class="form-control num-tabular" min="100" max="50000" step="50" value="1000" required>
                </div>
                <div class="mf-quick">
                    <?php foreach ($quickAmounts as $amt): ?>
                        <button type="button" class="mf-quick-btn" data-amount="<?= $amt ?>">$<?= number_format($amt) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mf-label"><?= $isSo ? 'Muddada (bilood)' : 'Term (months)' ?></div>
            <div class="mf-chips" role="radiogroup">
                <?php foreach ($months as $m): ?>
                    <label class="mf-chip">
                        <input type="radio" name="term_installments" value="<?= $m ?>" <?= $m === 6 ? 'checked' : '' ?>>
                        <span class="num-tabular"><?= $m ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="mf-label"><?= $isSo ? 'Habka qaybaha' : 'Installments' ?></div>
            <div class="mf-segment" role="radiogroup">
                <label><input type="radio" name="repayment_frequency" value="monthly" checked><span><?= $isSo ? 'Bille' : 'Monthly' ?></span></label>
                <label><input type="radio" name="repayment_frequency" value="weekly"><span><?= $isSo ? 'Toddobaadle' : 'Weekly' ?></span></label>
            </div>
        </section>

        <!-- 3. PURPOSE & PAYOUT -->
        <section class="card mf-step">
            <header class="mf-step-head">
                <span class="mf-step-num">3</span>
                <div>
                    <h2><?= $isSo ? 'Sababta iyo bixinta' : 'Purpose and payout' ?></h2>
                    <p><?= $isSo ? 'Sababta daynta iyo lambarka lacagta loo dirayo.' : 'Why the client needs the loan, and where to send it.' ?></p>
                </div>
            </header>

            <div class="form-group">
                <label for="loanPurposeInput"><?= $isSo ? 'Sababta daynta *' : 'Purpose *' ?></label>
                <textarea name="purpose" id="loanPurposeInput" class="form-control" rows="3" required
                          placeholder="<?= $isSo ? 'Tusaale: Balaarinta dukaanka, iibsashada badeecad cusub...' : 'e.g. Restocking a retail shop, buying new inventory...' ?>"></textarea>
            </div>

            <div class="mf-label"><?= $isSo ? 'Adeegga Mobile Money' : 'Mobile money provider' ?></div>
            <div class="mf-providers" role="radiogroup">
                <?php $first = true; foreach ($providers as $p => $co): ?>
                    <label class="mf-provider">
                        <input type="radio" name="provider" value="<?= Security::e($p) ?>" <?= $first ? 'checked' : '' ?>>
                        <span><strong><?= Security::e($p) ?></strong><small><?= Security::e($co) ?></small></span>
                    </label>
                <?php $first = false; endforeach; ?>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label for="loanDisbursePhone"><?= $isSo ? 'Lambarka lacagta lagu dirayo *' : 'Recipient phone number *' ?></label>
                <div class="mf-input mf-input-prefix"><span class="mf-input-icon"><?= Icons::svg('phone', 15) ?></span>
                    <input type="tel" name="phone" id="loanDisbursePhone" class="form-control num-tabular" required placeholder="+252 61 0000000">
                </div>
            </div>
        </section>
    </div>

    <!-- SUMMARY -->
    <aside class="mf-aside">
        <div class="mf-summary">
            <div class="mf-summary-top">
                <span><?= $isSo ? 'Soo koobid' : 'Summary' ?></span>
                <span class="mf-pill">0% <?= $isSo ? 'Ribo' : 'Riba' ?></span>
            </div>

            <div class="mf-summary-item">
                <span class="mf-summary-thumb"><?= Icons::svg('user', 20) ?></span>
                <div>
                    <strong id="sumClientName"><?= $isSo ? 'Macmiil lama dooran' : 'No client selected' ?></strong>
                    <small id="sumPayout">EVC Plus</small>
                </div>
            </div>

            <div class="mf-summary-amount">
                <small id="sumPerLabel"><?= $isSo ? 'Qaybta bishii' : 'Monthly installment' ?></small>
                <div class="num-tabular" id="summaryMonthly">$0.00</div>
                <small id="summaryTermLabel"></small>
            </div>

            <dl class="mf-summary-rows">
                <div><dt><?= $isSo ? 'Lacagta la siinayo' : 'Amount given' ?></dt><dd class="num-tabular" id="summaryPrincipal">$0.00</dd></div>
                <div><dt><?= $isSo ? 'Faa\'iido / ribo' : 'Profit / riba' ?></dt><dd class="num-tabular mf-gold">$0.00</dd></div>
                <div class="mf-total"><dt><?= $isSo ? 'Wadarta la celinayo' : 'Total to repay' ?></dt><dd class="num-tabular" id="summaryTotalDue">$0.00</dd></div>
            </dl>

            <p class="mf-error" id="formError" role="alert" hidden></p>

            <button type="submit" id="btnSubmitLoan" class="mf-submit">
                <?= Icons::svg('send', 18) ?>
                <span><?= !empty($isRequest) ? ($isSo ? 'Dir Codsiga' : 'Send Request') : ($isSo ? 'Bixi Daynta' : 'Disburse Loan') ?></span>
            </button>
            <p class="mf-summary-note"><?= !empty($isRequest) ? ($isSo ? 'Codsigan waxaa loo dirayaa Maamulaha Laanta. Marka uu ansixiyo ama diido, macmiilka email ayaa loo dirayaa.' : 'This request goes to the Branch Manager. The client is emailed when it is approved or rejected.') : ($isSo ? 'Macmiilka waxaa loo dirayaa rasiidka iyo jadwalka bixinta email ahaan.' : 'The client is emailed the receipt and repayment schedule.') ?></p>
        </div>

        <div class="mf-notice">
            <?= Icons::svg('shield', 16) ?>
            <span><?= $isSo
                ? 'Macmiil kasta waa inuu leeyahay damiin la xaqiijiyay ka hor intaan dayn la siin.'
                : 'Every client must have a verified guarantor before a loan is issued.' ?></span>
        </div>
    </aside>
</form>

<script>
(function () {
    const isSo = <?= json_encode($isSo) ?>;
    const preselectedId = <?= (int) ($selectedClientId ?? 0) ?>;
    const $ = (id) => document.getElementById(id);
    const money = (n) => '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // ---------- Client picker ----------
    const options = Array.from(document.querySelectorAll('.client-option-item'));
    const search = $('clientSearchInput');
    const list = $('clientDropdownList');
    const phone = $('loanDisbursePhone');
    let selectedClient = null;
    let autoPhone = '';

    function filterClients() {
        const q = search.value.toLowerCase().trim();
        let shown = 0;
        options.forEach(function (o) {
            const hay = [o.dataset.name, o.dataset.code, o.dataset.phone, o.dataset.email, o.dataset.idnum].join(' ').toLowerCase();
            const match = !q || hay.includes(q);
            o.hidden = !match;
            if (match) shown++;
        });
        if ($('clientNoMatch')) $('clientNoMatch').hidden = shown > 0 || options.length === 0;
        list.hidden = false;
    }

    function selectClient(o) {
        selectedClient = o.dataset.name;
        $('selectedClientId').value = o.dataset.id;
        $('clientAvatar').textContent = o.dataset.name.trim().charAt(0).toUpperCase();
        $('clientNameDisplay').textContent = o.dataset.name;
        $('clientCodeDisplay').textContent = o.dataset.code;
        $('clientPhoneDisplay').textContent = o.dataset.phone;
        $('clientEmailDisplay').textContent = o.dataset.email;
        $('clientEmailWrap').hidden = !o.dataset.email;
        $('clientScoreDisplay').textContent = o.dataset.score;
        $('selectedClientBox').hidden = false;
        $('clientSearchWrapper').hidden = true;
        list.hidden = true;
        // Fill the payout number with the client's phone unless staff typed another one.
        if (phone.value.trim() === '' || phone.value === autoPhone) {
            phone.value = o.dataset.phone;
            autoPhone = phone.value;
        }
        $('formError').hidden = true;
        calculate();
        if (window.renderClientHistory) window.renderClientHistory(o.dataset.id);
    }

    function clearClient() {
        selectedClient = null;
        $('selectedClientId').value = '';
        $('selectedClientBox').hidden = true;
        $('clientSearchWrapper').hidden = false;
        search.value = '';
        filterClients();
        search.focus();
        calculate();
        if (window.renderClientHistory) window.renderClientHistory(null);
    }

    search.addEventListener('focus', filterClients);
    search.addEventListener('input', filterClients);
    search.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') list.hidden = true;
        if (e.key === 'Enter') {
            e.preventDefault();
            const first = options.find((o) => !o.hidden);
            if (first) selectClient(first);
        }
    });
    options.forEach((o) => o.addEventListener('click', () => selectClient(o)));
    $('changeClientBtn').addEventListener('click', clearClient);
    document.addEventListener('click', function (e) {
        if (!$('clientSearchWrapper').contains(e.target)) list.hidden = true;
    });

    // ---------- Amount & schedule ----------
    const amount = $('loanAmountInput');
    const quick = Array.from(document.querySelectorAll('.mf-quick-btn'));

    function calculate() {
        const a = Math.max(0, parseFloat(amount.value) || 0);
        const term = parseInt((document.querySelector('input[name="term_installments"]:checked') || {}).value || 6, 10);
        const weekly = (document.querySelector('input[name="repayment_frequency"]:checked') || {}).value === 'weekly';
        const provider = (document.querySelector('input[name="provider"]:checked') || {}).value || '';

        $('summaryPrincipal').textContent = money(a);
        $('summaryTotalDue').textContent = money(a);
        $('summaryMonthly').textContent = money(term > 0 ? a / term : a);
        $('sumPerLabel').textContent = weekly ? (isSo ? 'Qaybta toddobaadkii' : 'Weekly installment') : (isSo ? 'Qaybta bishii' : 'Monthly installment');
        $('summaryTermLabel').textContent = isSo
            ? (term + ' qaybood' + (weekly ? ' oo toddobaadle ah' : ' oo bille ah'))
            : (term + (weekly ? ' weekly' : ' monthly') + ' payments');
        $('sumClientName').textContent = selectedClient || (isSo ? 'Macmiil lama dooran' : 'No client selected');
        $('sumPayout').textContent = provider + (phone.value.trim() ? ' · ' + phone.value.trim() : '');
        quick.forEach((b) => b.classList.toggle('is-active', parseFloat(b.dataset.amount) === a));
    }

    quick.forEach((b) => b.addEventListener('click', function () { amount.value = b.dataset.amount; calculate(); }));
    amount.addEventListener('input', calculate);
    phone.addEventListener('input', calculate);
    document.querySelectorAll('input[name="term_installments"], input[name="repayment_frequency"], input[name="provider"]').forEach((r) => r.addEventListener('change', calculate));

    // ---------- Submit checks ----------
    $('loanNewForm').addEventListener('submit', function (e) {
        let msg = '';
        const a = parseFloat(amount.value) || 0;
        if (!(parseInt($('selectedClientId').value, 10) > 0)) {
            msg = isSo ? 'Fadlan dooro macmiilka.' : 'Please choose a client.';
            $('clientSearchWrapper').hidden = false;
            search.focus();
        } else if (a < 100 || a > 50000) {
            msg = isSo ? 'Lacagta daynta waa inay u dhexeysaa $100 iyo $50,000.' : 'Loan amount must be between $100 and $50,000.';
            amount.focus();
        } else if ($('loanPurposeInput').value.trim() === '') {
            msg = isSo ? 'Fadlan qor sababta daynta.' : 'Please enter the purpose.';
            $('loanPurposeInput').focus();
        } else if (phone.value.trim() === '') {
            msg = isSo ? 'Fadlan geli lambarka lacagta lagu dirayo.' : 'Please enter the recipient phone number.';
            phone.focus();
        }
        if (msg) {
            e.preventDefault();
            e.stopImmediatePropagation(); // keep the global double-submit guard from locking the button
            $('formError').textContent = msg;
            $('formError').hidden = false;
        }
    });

    // ---------- Init ----------
    calculate();
    if (preselectedId > 0) {
        const o = options.find((x) => x.dataset.id == preselectedId);
        if (o) selectClient(o);
    }
})();
</script>
