<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Services\MurabahaGoodsService;

$csrf = Security::csrfToken();
$locale = Lang::locale();
$isSo = ($locale === 'so');
$clients = $clients ?? [];

// Murabaha goods catalog, managed by staff on the Murabaha Products page.
$catalog = [];
foreach (($goods ?? []) as $g) {
    $catalog[] = [
        'id' => 'g' . (int) $g['id'],
        'name' => (string) $g['name'],
        'cost' => (float) $g['price'],
        'markup_pct' => (float) $g['markup_pct'],
        'selling_price' => MurabahaGoodsService::sellingPrice($g),
        'desc' => (string) ($g['description'] ?? ''),
        'image' => (string) ($g['image_path'] ?? ''),
    ];
}
$catalog[] = [
    'id' => 'custom',
    'name' => $isSo ? 'Shey kale' : 'Other item',
    'cost' => 1000.00,
    'markup_pct' => 5.0,
    'selling_price' => 1050.00,
    'desc' => '',
    'image' => '',
];
$months = [3, 6, 9, 12, 18, 24, 36, 48];
$initials = static fn(string $n): string => mb_strtoupper(mb_substr(trim($n), 0, 1));
?>

<div class="mf-head">
    <div class="mf-head-title">
        <span class="mf-head-icon"><?= Icons::svg('shopping-bag', 22) ?></span>
        <div>
            <h1><?= $isSo ? 'Heshiis Murabaxo Cusub' : 'New Murabaha Financing' ?></h1>
            <p><?= $isSo
                ? 'SIMDA ayaa iibsanaysa sheyga, macmiilkana ugu iibinaysa qiimaha asalka iyo faa\'iido go\'an oo hore loo heshiiyay.'
                : 'SIMDA buys the item and sells it to the client at cost plus a fixed markup agreed upfront.' ?></p>
        </div>
    </div>
    <a href="?route=murabaha" class="btn btn-outline btn-sm"><?= Icons::svg('arrow-left', 14) ?> <?= $isSo ? 'Ku noqo' : 'Back' ?></a>
</div>

<form method="POST" action="?route=murabaha_submit" id="murabahaNewForm" class="mf-layout" novalidate>
    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
    <input type="hidden" name="client_id" id="selectedClientId" value="<?= (int) ($selectedClientId ?? 0) ?>">
    <input type="hidden" name="markup_pct" id="markupPctInput" value="5.0">
    <input type="hidden" name="selling_price" id="sellingPriceHidden" value="0">
    <input type="hidden" name="goods_name" id="goodsNameHidden" value="">

    <div class="mf-main">

        <!-- 1. CLIENT -->
        <section class="card mf-step">
            <header class="mf-step-head">
                <span class="mf-step-num">1</span>
                <div>
                    <h2><?= $isSo ? 'Macmiilka' : 'Client' ?></h2>
                    <p><?= $isSo ? 'Dooro macmiilka iibsanaya sheyga.' : 'Choose the client buying the item.' ?></p>
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

        <!-- 2. ITEM -->
        <section class="card mf-step">
            <header class="mf-step-head">
                <span class="mf-step-num">2</span>
                <div>
                    <h2><?= $isSo ? 'Sheyga' : 'Item' ?></h2>
                    <p><?= $isSo ? 'Dooro sheyga; qiimaha si toos ah ayuu u buuxsamayaa.' : 'Pick the item; the price fills in automatically.' ?></p>
                </div>
                <?php if (\App\Middleware\AuthMiddleware::hasPermission('products.manage')): ?>
                    <a href="?route=murabaha_goods" class="mf-step-link"><?= $isSo ? 'Maamul alaabta' : 'Manage products' ?></a>
                <?php endif; ?>
            </header>

            <div class="mf-goods" role="radiogroup" aria-label="<?= $isSo ? 'Sheyga' : 'Item' ?>">
                <?php foreach ($catalog as $i => $item): ?>
                    <label class="mf-good">
                        <input type="radio" name="catalog_item" value="<?= Security::e($item['id']) ?>" <?= $i === 0 ? 'checked' : '' ?>
                               data-name="<?= Security::e($item['name']) ?>"
                               data-cost="<?= (float) $item['cost'] ?>"
                               data-markup="<?= (float) $item['markup_pct'] ?>"
                               data-desc="<?= Security::e($item['desc']) ?>"
                               data-image="<?= Security::e($item['image']) ?>">
                        <span class="mf-good-thumb">
                            <?php if ($item['image'] !== ''): ?>
                                <img src="<?= Security::e($item['image']) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <?= Icons::svg($item['id'] === 'custom' ? 'plus' : 'shopping-bag', 22) ?>
                            <?php endif; ?>
                        </span>
                        <span class="mf-good-name"><?= Security::e($item['name']) ?></span>
                        <span class="mf-good-price num-tabular">
                            <?= $item['id'] === 'custom' ? ($isSo ? 'Qiime gaar ah' : 'Custom price') : '$' . number_format($item['selling_price'], 2) ?>
                        </span>
                        <span class="mf-good-check"><?= Icons::svg('check', 13) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="form-group mf-custom" id="customItemNameGroup" hidden>
                <label for="customItemName"><?= $isSo ? 'Magaca sheyga *' : 'Item name *' ?></label>
                <input type="text" name="custom_item_name" id="customItemName" class="form-control" placeholder="<?= $isSo ? 'Tusaale: Mashiinka alxanka' : 'e.g. Welding machine' ?>">
            </div>

            <div class="mf-fields-3">
                <div class="form-group">
                    <label for="murabahaCostInput"><?= $isSo ? 'Qiimaha asalka' : 'Cost price' ?></label>
                    <div class="mf-input mf-input-prefix"><span>$</span>
                        <input type="number" name="cost_price" id="murabahaCostInput" class="form-control num-tabular" step="0.01" min="50" max="1000000" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="murabahaQtyInput"><?= $isSo ? 'Tirada' : 'Quantity' ?></label>
                    <input type="number" name="quantity" id="murabahaQtyInput" class="form-control num-tabular" min="1" max="100" value="1" required>
                </div>
                <div class="form-group">
                    <label for="displayMarkupPct"><?= $isSo ? 'Faa\'iidada' : 'Markup' ?></label>
                    <div class="mf-input mf-input-suffix">
                        <input type="number" id="displayMarkupPct" class="form-control num-tabular" step="0.5" min="0" max="30" required>
                        <span>%</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. TERMS -->
        <section class="card mf-step">
            <header class="mf-step-head">
                <span class="mf-step-num">3</span>
                <div>
                    <h2><?= $isSo ? 'Muddada bixinta' : 'Repayment' ?></h2>
                    <p><?= $isSo ? 'Inta bil ee uu ku bixinayo iyo habka qaybaha.' : 'How long the client has to pay, and how often.' ?></p>
                </div>
            </header>

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

        <!-- 4. DETAILS -->
        <section class="card mf-step">
            <header class="mf-step-head">
                <span class="mf-step-num">4</span>
                <div>
                    <h2><?= $isSo ? 'Faahfaahin' : 'Details' ?></h2>
                    <p><?= $isSo ? 'Sifada sheyga iyo ujeeddada ganacsiga.' : 'Item specifics and the business purpose.' ?></p>
                </div>
            </header>
            <div class="form-group">
                <label for="murabahaGoodsDescInput"><?= $isSo ? 'Faahfaahinta sheyga *' : 'Item description *' ?></label>
                <textarea name="murabaha_goods_description" id="murabahaGoodsDescInput" class="form-control" rows="3" required
                          placeholder="<?= $isSo ? 'Nooca, lambarka taxanaha, iibiyaha, shuruudaha keenista...' : 'Model, serial number, supplier, delivery terms...' ?>"></textarea>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label for="murabahaPurposeInput"><?= $isSo ? 'Ujeeddada *' : 'Purpose *' ?></label>
                <input type="text" name="purpose" id="murabahaPurposeInput" class="form-control" required
                       value="<?= $isSo ? 'Maalgelinta qalab ganacsi oo Murabaxo ah' : 'Murabaha business asset financing' ?>">
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
                <span class="mf-summary-thumb" id="sumThumb"><?= Icons::svg('shopping-bag', 20) ?></span>
                <div>
                    <strong id="sumItemName">—</strong>
                    <small id="sumClientName"><?= $isSo ? 'Macmiil lama dooran' : 'No client selected' ?></small>
                </div>
            </div>

            <div class="mf-summary-amount">
                <small id="sumPerLabel"><?= $isSo ? 'Qaybta bishii' : 'Monthly installment' ?></small>
                <div class="num-tabular" id="sideMonthlyInstallment">$0.00</div>
                <small id="sideTermLabel"></small>
            </div>

            <dl class="mf-summary-rows">
                <div><dt><?= $isSo ? 'Qiimaha asalka' : 'Cost' ?></dt><dd class="num-tabular" id="sideCostDisplay">$0.00</dd></div>
                <div><dt><?= $isSo ? 'Faa\'iidada' : 'Markup' ?></dt><dd class="num-tabular mf-gold" id="sideProfitDisplay">+$0.00</dd></div>
                <div class="mf-total"><dt><?= $isSo ? 'Wadarta iibka' : 'Total selling price' ?></dt><dd class="num-tabular" id="sideTotalSellingDisplay">$0.00</dd></div>
            </dl>

            <p class="mf-error" id="formError" role="alert" hidden></p>

            <button type="submit" id="btnSubmitMurabaha" class="mf-submit">
                <?= Icons::svg('check-circle', 18) ?>
                <span><?= $isSo ? 'Xaqiiji Heshiiska' : 'Confirm Contract' ?></span>
            </button>
            <p class="mf-summary-note"><?= $isSo ? 'Macmiilka waxaa loo dirayaa rasiidka iyo jadwalka bixinta email ahaan.' : 'The client is emailed the receipt and repayment schedule.' ?></p>
        </div>

        <div class="mf-notice">
            <?= Icons::svg('shield', 16) ?>
            <span><?= $isSo
                ? 'Faa\'iidada waa go\'an tahay; kuma kordhayso xitaa haddii bixintu dib u dhacdo.'
                : 'The markup is fixed upfront and never grows, even if payments are late.' ?></span>
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
    let selectedClient = null;

    function openList() { list.hidden = false; }
    function closeList() { list.hidden = true; }

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
        openList();
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
        closeList();
        $('formError').hidden = true;
        updateSummary();
    }

    function clearClient() {
        selectedClient = null;
        $('selectedClientId').value = '';
        $('selectedClientBox').hidden = true;
        $('clientSearchWrapper').hidden = false;
        search.value = '';
        filterClients();
        search.focus();
        updateSummary();
    }

    search.addEventListener('focus', filterClients);
    search.addEventListener('input', filterClients);
    search.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeList();
        if (e.key === 'Enter') {
            e.preventDefault();
            const first = options.find((o) => !o.hidden);
            if (first) selectClient(first);
        }
    });
    options.forEach((o) => o.addEventListener('click', () => selectClient(o)));
    $('changeClientBtn').addEventListener('click', clearClient);
    document.addEventListener('click', function (e) {
        if (!$('clientSearchWrapper').contains(e.target)) closeList();
    });

    // ---------- Item & pricing ----------
    const cost = $('murabahaCostInput');
    const qty = $('murabahaQtyInput');
    const markup = $('displayMarkupPct');
    const desc = $('murabahaGoodsDescInput');
    const customName = $('customItemName');
    let lastAutoDesc = '';

    function currentItem() { return document.querySelector('input[name="catalog_item"]:checked'); }

    function onItemChange() {
        const it = currentItem();
        if (!it) return;
        const isCustom = it.value === 'custom';
        $('customItemNameGroup').hidden = !isCustom;
        cost.value = parseFloat(it.dataset.cost).toFixed(2);
        markup.value = parseFloat(it.dataset.markup).toFixed(1);
        // Replace the description only if staff have not typed their own.
        if (desc.value.trim() === '' || desc.value === lastAutoDesc) {
            desc.value = it.dataset.desc || '';
            lastAutoDesc = desc.value;
        }
        const thumb = $('sumThumb');
        thumb.innerHTML = '';
        if (it.dataset.image) {
            const img = document.createElement('img');
            img.src = it.dataset.image;
            img.alt = '';
            thumb.appendChild(img);
        } else {
            thumb.innerHTML = it.closest('.mf-good').querySelector('.mf-good-thumb').innerHTML;
        }
        if (isCustom) customName.focus();
        calculate();
    }

    function goodsName() {
        const it = currentItem();
        if (!it) return '';
        return it.value === 'custom' ? customName.value.trim() : it.dataset.name;
    }

    function calculate() {
        const c = Math.max(0, parseFloat(cost.value) || 0);
        const q = Math.max(1, parseInt(qty.value, 10) || 1);
        const m = Math.max(0, parseFloat(markup.value) || 0);
        const term = parseInt((document.querySelector('input[name="term_installments"]:checked') || {}).value || 6, 10);
        const weekly = (document.querySelector('input[name="repayment_frequency"]:checked') || {}).value === 'weekly';

        const base = c * q;
        const profit = base * m / 100;
        const total = base + profit;
        const per = term > 0 ? total / term : total;

        $('markupPctInput').value = m.toFixed(1);
        $('sellingPriceHidden').value = total.toFixed(2);
        $('sideCostDisplay').textContent = money(base);
        $('sideProfitDisplay').textContent = '+' + money(profit) + ' (' + m + '%)';
        $('sideTotalSellingDisplay').textContent = money(total);
        $('sideMonthlyInstallment').textContent = money(per);
        $('sumPerLabel').textContent = weekly ? (isSo ? 'Qaybta toddobaadkii' : 'Weekly installment') : (isSo ? 'Qaybta bishii' : 'Monthly installment');
        $('sideTermLabel').textContent = isSo
            ? (term + ' qaybood' + (weekly ? ' oo toddobaadle ah' : ' oo bille ah'))
            : (term + (weekly ? ' weekly' : ' monthly') + ' payments');
        updateSummary();
    }

    function updateSummary() {
        $('goodsNameHidden').value = goodsName();
        $('sumItemName').textContent = goodsName() || (isSo ? 'Sheyga lama dooran' : 'No item chosen');
        $('sumClientName').textContent = selectedClient || (isSo ? 'Macmiil lama dooran' : 'No client selected');
    }

    document.querySelectorAll('input[name="catalog_item"]').forEach((r) => r.addEventListener('change', onItemChange));
    document.querySelectorAll('input[name="term_installments"], input[name="repayment_frequency"]').forEach((r) => r.addEventListener('change', calculate));
    [cost, qty, markup].forEach((el) => el.addEventListener('input', calculate));
    customName.addEventListener('input', updateSummary);

    // ---------- Submit checks ----------
    $('murabahaNewForm').addEventListener('submit', function (e) {
        let msg = '';
        if (!(parseInt($('selectedClientId').value, 10) > 0)) {
            msg = isSo ? 'Fadlan dooro macmiilka.' : 'Please choose a client.';
            $('clientSearchWrapper').hidden = false;
            search.focus();
        } else if (currentItem() && currentItem().value === 'custom' && customName.value.trim() === '') {
            msg = isSo ? 'Fadlan qor magaca sheyga.' : 'Please enter the item name.';
            customName.focus();
        } else if ((parseFloat(cost.value) || 0) < 50) {
            msg = isSo ? 'Qiimaha sheyga ugu yaraan waa $50.' : 'Cost price must be at least $50.';
            cost.focus();
        } else if (desc.value.trim() === '') {
            msg = isSo ? 'Fadlan qor faahfaahinta sheyga.' : 'Please describe the item.';
            desc.focus();
        }
        if (msg) {
            e.preventDefault();
            e.stopImmediatePropagation(); // keep the global double-submit guard from locking the button
            $('formError').textContent = msg;
            $('formError').hidden = false;
        }
    });

    // ---------- Init ----------
    onItemChange();
    if (preselectedId > 0) {
        const o = options.find((x) => x.dataset.id == preselectedId);
        if (o) selectClient(o);
    }
})();
</script>
