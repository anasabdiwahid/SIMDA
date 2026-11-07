<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isSo = Lang::locale() === 'so';
$debtors = $debtors ?? [];
$methods = ['EVC Plus' => 'Hormuud', 'Zaad' => 'Telesom', 'eDahab' => 'Somtel', 'Cash' => $isSo ? 'Lacag caddaan' : 'In person'];
$initials = static fn(string $n): string => mb_strtoupper(mb_substr(trim($n), 0, 1));
?>

<div class="mf-head">
    <div class="mf-head-title">
        <span class="mf-head-icon mf-head-icon-green"><?= Icons::svg('wallet', 22) ?></span>
        <div>
            <h1><?= $isSo ? 'Qabo Lacag-celin' : 'Collect Repayment' ?></h1>
            <p><?= $isSo
                ? 'Raadi macmiilka, dooro heshiiska, geli lacagta uu keenay. Rasiidka waxaa loo dirayaa email-kiisa.'
                : 'Find the client, pick the contract and enter what they paid. The receipt is emailed to them.' ?></p>
        </div>
    </div>
    <a href="?route=repayments" class="btn btn-outline btn-sm"><?= Icons::svg('activity', 14) ?> <?= $isSo ? 'Taariikhda' : 'History' ?></a>
</div>

<form method="POST" action="?route=repayment_submit" id="collectForm" class="mf-layout" novalidate>
    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

    <div class="mf-main">

        <!-- 1. CLIENT -->
        <section class="card mf-step">
            <header class="mf-step-head">
                <span class="mf-step-num">1</span>
                <div>
                    <h2><?= $isSo ? 'Macmiilka' : 'Client' ?></h2>
                    <p><?= $isSo ? 'Ku raadi magaca macmiilka lacagta keenay.' : 'Search for the client who is paying.' ?></p>
                </div>
                <span class="mf-step-meta"><?= count($debtors) ?> <?= $isSo ? 'deyn leh' : 'owing' ?></span>
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
                <span class="rc-owed num-tabular" id="clientOwedDisplay"></span>
                <button type="button" class="btn btn-outline btn-sm" id="changeClientBtn"><?= $isSo ? 'Beddel' : 'Change' ?></button>
            </div>

            <div class="mf-search" id="clientSearchWrapper">
                <span class="mf-search-icon"><?= Icons::svg('search', 17) ?></span>
                <input type="search" id="clientSearchInput" class="form-control" autocomplete="off"
                       placeholder="<?= $isSo ? 'Ku raadi magaca, telefoonka ama koodhka...' : 'Search by name, phone or client code...' ?>"
                       aria-controls="clientDropdownList">
                <div class="mf-dropdown" id="clientDropdownList" role="listbox" hidden>
                    <?php if (!$debtors): ?>
                        <div class="mf-dropdown-empty"><?= $isSo ? 'Ma jiro macmiil deyn lagu leeyahay.' : 'No client currently owes money.' ?></div>
                    <?php endif; ?>
                    <?php foreach ($debtors as $d): ?>
                        <button type="button" class="mf-option client-option-item" role="option"
                                data-id="<?= (int) $d['id'] ?>"
                                data-name="<?= Security::e($d['name']) ?>"
                                data-code="<?= Security::e($d['code']) ?>"
                                data-phone="<?= Security::e($d['phone']) ?>"
                                data-email="<?= Security::e($d['email']) ?>"
                                data-idnum="<?= Security::e($d['idnum']) ?>">
                            <span class="mf-avatar mf-avatar-sm"><?= Security::e($initials($d['name'])) ?></span>
                            <span class="mf-option-text">
                                <strong><?= Security::e($d['name']) ?></strong>
                                <small><span class="num-tabular"><?= Security::e($d['code']) ?></span> · <?= Security::e($d['phone']) ?></small>
                            </span>
                            <span class="rc-owed num-tabular">$<?= number_format($d['owed'], 2) ?></span>
                        </button>
                    <?php endforeach; ?>
                    <div class="mf-dropdown-empty" id="clientNoMatch" hidden><?= $isSo ? 'Wax la mid ah lama helin.' : 'No matching client.' ?></div>
                </div>
            </div>
        </section>

        <!-- 2. CONTRACT -->
        <section class="card mf-step" id="contractStep">
            <header class="mf-step-head">
                <span class="mf-step-num">2</span>
                <div>
                    <h2><?= $isSo ? 'Heshiiska' : 'Contract' ?></h2>
                    <p><?= $isSo ? 'Dooro daynta uu bixinayo.' : 'Choose which loan the payment is for.' ?></p>
                </div>
            </header>
            <div class="rc-contracts" id="contractList" role="radiogroup">
                <div class="rc-placeholder"><?= $isSo ? 'Marka hore dooro macmiilka.' : 'Choose a client first.' ?></div>
            </div>
        </section>

        <!-- 3. PAYMENT -->
        <section class="card mf-step">
            <header class="mf-step-head">
                <span class="mf-step-num">3</span>
                <div>
                    <h2><?= $isSo ? 'Lacagta la keenay' : 'Payment' ?></h2>
                    <p><?= $isSo ? 'Inta uu bixiyay iyo habka uu ku bixiyay.' : 'How much was paid, and how.' ?></p>
                </div>
            </header>

            <div class="form-group">
                <label for="amountInput"><?= $isSo ? 'Lacagta *' : 'Amount paid *' ?></label>
                <div class="mf-input mf-input-prefix mf-input-lg"><span>$</span>
                    <input type="number" name="amount" id="amountInput" class="form-control num-tabular" min="0.01" step="0.01" placeholder="0.00" required>
                </div>
                <div class="mf-quick" id="amountQuick" hidden>
                    <button type="button" class="mf-quick-btn" id="quickNext"></button>
                    <button type="button" class="mf-quick-btn" id="quickFull"></button>
                </div>
            </div>

            <div class="mf-label"><?= $isSo ? 'Habka bixinta' : 'Payment method' ?></div>
            <div class="mf-providers rc-methods" role="radiogroup">
                <?php $first = true; foreach ($methods as $m => $sub): ?>
                    <label class="mf-provider">
                        <input type="radio" name="payment_method" value="<?= Security::e($m) ?>" <?= $first ? 'checked' : '' ?>>
                        <span><strong><?= Security::e($m) ?></strong><small><?= Security::e($sub) ?></small></span>
                    </label>
                <?php $first = false; endforeach; ?>
            </div>

            <div class="mf-fields-3 rc-fields">
                <div class="form-group">
                    <label for="txRef"><?= $isSo ? 'Lambarka xawaaladda' : 'Transaction ref' ?></label>
                    <input type="text" name="transaction_reference" id="txRef" class="form-control num-tabular" maxlength="80" placeholder="<?= $isSo ? 'Ikhtiyaari' : 'Optional' ?>">
                </div>
                <div class="form-group">
                    <label for="payDate"><?= $isSo ? 'Taariikhda' : 'Date' ?></label>
                    <input type="date" name="payment_date" id="payDate" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label for="notesInput"><?= $isSo ? 'Qoraal' : 'Note' ?></label>
                    <input type="text" name="notes" id="notesInput" class="form-control" maxlength="255" placeholder="<?= $isSo ? 'Ikhtiyaari' : 'Optional' ?>">
                </div>
            </div>
        </section>
    </div>

    <!-- SUMMARY -->
    <aside class="mf-aside">
        <div class="mf-summary">
            <div class="mf-summary-top">
                <span><?= $isSo ? 'Rasiidka' : 'Receipt' ?></span>
                <span class="mf-pill"><?= date('d/m/Y') ?></span>
            </div>

            <div class="mf-summary-item">
                <span class="mf-summary-thumb"><?= Icons::svg('user', 20) ?></span>
                <div>
                    <strong id="sumClient"><?= $isSo ? 'Macmiil lama dooran' : 'No client selected' ?></strong>
                    <small id="sumContract">—</small>
                </div>
            </div>

            <div class="mf-summary-amount">
                <small><?= $isSo ? 'Lacagta la qabanayo' : 'Amount received' ?></small>
                <div class="num-tabular" id="sumAmount">$0.00</div>
                <small id="sumMethod">EVC Plus</small>
            </div>

            <dl class="mf-summary-rows">
                <div><dt><?= $isSo ? 'Haraaga hadda' : 'Balance now' ?></dt><dd class="num-tabular" id="sumBefore">$0.00</dd></div>
                <div><dt><?= $isSo ? 'La bixiyay' : 'Paid now' ?></dt><dd class="num-tabular mf-gold" id="sumPaid">−$0.00</dd></div>
                <div class="mf-total"><dt><?= $isSo ? 'Haraaga ka dib' : 'Balance after' ?></dt><dd class="num-tabular" id="sumAfter">$0.00</dd></div>
            </dl>

            <p class="rc-mail" id="sumMail"></p>
            <p class="mf-error" id="formError" role="alert" hidden></p>

            <button type="submit" id="btnSubmitCollect" class="mf-submit">
                <?= Icons::svg('check-circle', 18) ?>
                <span><?= $isSo ? 'Xaqiiji & Dir Rasiidka' : 'Confirm & Send Receipt' ?></span>
            </button>
            <p class="mf-summary-note"><?= $isSo ? 'Rasiidka wuxuu ku kaydsamayaa bogga macmiilka.' : 'The receipt is also saved on the client\'s profile.' ?></p>
        </div>
    </aside>
</form>

<script>
(function () {
    const isSo = <?= json_encode($isSo) ?>;
    const debtors = <?= json_encode(array_column($debtors, null, 'id'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const preselectedId = <?= (int) ($selectedClientId ?? 0) ?>;
    const $ = (id) => document.getElementById(id);
    const money = (n) => '$' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const fmtDate = (s) => { if (!s) return '—'; const p = s.split('-'); return p[2] + '/' + p[1] + '/' + p[0]; };
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const options = Array.from(document.querySelectorAll('.client-option-item'));
    const search = $('clientSearchInput');
    const list = $('clientDropdownList');
    const amount = $('amountInput');
    let client = null;
    let contract = null;

    // ---------- Client search ----------
    function filterClients() {
        const q = search.value.toLowerCase().trim();
        let shown = 0;
        options.forEach(function (o) {
            const hay = [o.dataset.name, o.dataset.code, o.dataset.phone, o.dataset.email, o.dataset.idnum].join(' ').toLowerCase();
            o.hidden = !(!q || hay.includes(q));
            if (!o.hidden) shown++;
        });
        $('clientNoMatch').hidden = shown > 0 || options.length === 0;
        list.hidden = false;
    }

    function selectClient(id) {
        client = debtors[id];
        if (!client) return;
        $('clientAvatar').textContent = client.name.trim().charAt(0).toUpperCase();
        $('clientNameDisplay').textContent = client.name;
        $('clientCodeDisplay').textContent = client.code;
        $('clientPhoneDisplay').textContent = client.phone;
        $('clientEmailDisplay').textContent = client.email;
        $('clientEmailWrap').hidden = !client.email;
        $('clientOwedDisplay').textContent = (isSo ? 'Deyn: ' : 'Owes ') + money(client.owed);
        $('selectedClientBox').hidden = false;
        $('clientSearchWrapper').hidden = true;
        list.hidden = true;
        $('formError').hidden = true;
        renderContracts();
    }

    function clearClient() {
        client = null;
        contract = null;
        $('selectedClientBox').hidden = true;
        $('clientSearchWrapper').hidden = false;
        search.value = '';
        renderContracts();
        filterClients();
        search.focus();
    }

    // ---------- Contracts ----------
    function renderContracts() {
        const box = $('contractList');
        contract = null;
        if (!client) {
            box.innerHTML = '<div class="rc-placeholder">' + (isSo ? 'Marka hore dooro macmiilka.' : 'Choose a client first.') + '</div>';
            update();
            return;
        }
        box.innerHTML = client.contracts.map(function (c, i) {
            const kind = c.type === 'murabaha' ? (isSo ? 'Murabaxo' : 'Murabaha') : (isSo ? 'Dayn Cadaan' : 'Cash Loan');
            const pct = c.term > 0 ? Math.round(c.paid / c.term * 100) : 0;
            const late = c.status === 'overdue' ? '<span class="rc-late">' + (isSo ? 'Dib u dhac' : 'Overdue') + '</span>' : '';
            const next = c.next_amount !== null
                ? (isSo ? 'Qaybta xigta: ' : 'Next due: ') + '<b>' + money(c.next_amount) + '</b> · ' + fmtDate(c.next_date)
                : '';
            return '<label class="rc-contract">'
                + '<input type="radio" name="financing_id" value="' + c.id + '"' + (i === 0 ? ' checked' : '') + '>'
                + '<span class="rc-contract-body">'
                + '<span class="rc-contract-top"><b class="num-tabular">' + esc(c.number) + '</b><span class="adm-role dash-tone-' + (c.type === 'murabaha' ? 'gold' : 'green') + '">' + kind + '</span>' + late + '</span>'
                + '<span class="rc-contract-bal"><small>' + (isSo ? 'Haraaga' : 'Balance') + '</small><b class="num-tabular">' + money(c.balance) + '</b></span>'
                + '<span class="rc-progress"><span style="width:' + pct + '%"></span></span>'
                + '<span class="rc-contract-meta">' + c.paid + '/' + c.term + (isSo ? ' qaybood la bixiyay' : ' installments paid') + (next ? ' · ' + next : '') + '</span>'
                + '</span></label>';
        }).join('');
        box.querySelectorAll('input[name="financing_id"]').forEach(function (r) {
            r.addEventListener('change', pickContract);
        });
        pickContract();
    }

    function pickContract() {
        const r = document.querySelector('input[name="financing_id"]:checked');
        contract = r && client ? client.contracts.find((c) => c.id == r.value) : null;
        if (contract) {
            const next = contract.next_amount !== null ? Math.min(contract.next_amount, contract.balance) : contract.balance;
            $('quickNext').textContent = (isSo ? 'Qaybta: ' : 'Installment: ') + money(next);
            $('quickNext').dataset.amount = next.toFixed(2);
            $('quickFull').textContent = (isSo ? 'Dhammaan: ' : 'Full balance: ') + money(contract.balance);
            $('quickFull').dataset.amount = contract.balance.toFixed(2);
            $('amountQuick').hidden = false;
            if (!amount.value) amount.value = next.toFixed(2);
        } else {
            $('amountQuick').hidden = true;
        }
        update();
    }

    // ---------- Summary ----------
    function update() {
        const a = Math.max(0, parseFloat(amount.value) || 0);
        const method = (document.querySelector('input[name="payment_method"]:checked') || {}).value || '';
        const before = contract ? contract.balance : 0;
        $('sumClient').textContent = client ? client.name : (isSo ? 'Macmiil lama dooran' : 'No client selected');
        $('sumContract').textContent = contract ? contract.number : '—';
        $('sumAmount').textContent = money(a);
        $('sumMethod').textContent = method;
        $('sumBefore').textContent = money(before);
        $('sumPaid').textContent = '−' + money(Math.min(a, before));
        $('sumAfter').textContent = money(Math.max(0, before - a));
        const mail = $('sumMail');
        if (client && client.email) {
            mail.textContent = (isSo ? 'Rasiidka waxaa loo dirayaa: ' : 'Receipt will be emailed to: ') + client.email;
            mail.className = 'rc-mail is-ok';
        } else if (client) {
            mail.textContent = isSo ? 'Macmiilku email ma laha; rasiidka waa la daabici karaa.' : 'Client has no email; you can print the receipt.';
            mail.className = 'rc-mail is-warn';
        } else {
            mail.textContent = '';
            mail.className = 'rc-mail';
        }
        document.querySelectorAll('#amountQuick .mf-quick-btn').forEach((b) => b.classList.toggle('is-active', a > 0 && parseFloat(b.dataset.amount) === a));
    }

    search.addEventListener('focus', filterClients);
    search.addEventListener('input', filterClients);
    search.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') list.hidden = true;
        if (e.key === 'Enter') {
            e.preventDefault();
            const first = options.find((o) => !o.hidden);
            if (first) selectClient(first.dataset.id);
        }
    });
    options.forEach((o) => o.addEventListener('click', () => selectClient(o.dataset.id)));
    $('changeClientBtn').addEventListener('click', clearClient);
    document.addEventListener('click', function (e) {
        if (!$('clientSearchWrapper').contains(e.target)) list.hidden = true;
    });
    document.querySelectorAll('#amountQuick .mf-quick-btn').forEach(function (b) {
        b.addEventListener('click', function () { amount.value = b.dataset.amount; update(); });
    });
    amount.addEventListener('input', update);
    document.querySelectorAll('input[name="payment_method"]').forEach((r) => r.addEventListener('change', update));

    // ---------- Submit checks ----------
    $('collectForm').addEventListener('submit', function (e) {
        let msg = '';
        const a = parseFloat(amount.value) || 0;
        if (!client) {
            msg = isSo ? 'Fadlan dooro macmiilka.' : 'Please choose a client.';
            search.focus();
        } else if (!contract) {
            msg = isSo ? 'Fadlan dooro heshiiska.' : 'Please choose a contract.';
        } else if (a <= 0) {
            msg = isSo ? 'Fadlan geli lacagta la keenay.' : 'Please enter the amount paid.';
            amount.focus();
        }
        if (msg) {
            e.preventDefault();
            e.stopImmediatePropagation(); // keep the global double-submit guard from locking the button
            $('formError').textContent = msg;
            $('formError').hidden = false;
        }
    });

    // ---------- Init ----------
    update();
    if (preselectedId > 0 && debtors[preselectedId]) selectClient(preselectedId);
})();
</script>
