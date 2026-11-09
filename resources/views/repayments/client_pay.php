<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

/**
 * Client portal: Send Payment (simulated mobile money). The amount can never be more than what the
 * client owes on the chosen financing; the browser checks it for a quick message and the server
 * (ClientPaymentService::pay) enforces it.
 */
$csrf = Security::csrfToken();
$financings = $financings ?? [];
$selectedId = (int) ($selectedId ?? 0);
if ($selectedId === 0 && !empty($financings)) {
    $selectedId = (int) $financings[0]['id'];
}
$phone = (string) ($user['phone'] ?? '');
?>

<div class="card animate-in" style="margin-bottom:22px;max-width:760px;">
    <h2 style="font-size:1.2rem;margin:6px 0;"><?= Lang::t('Dir Lacag', 'Send Payment') ?></h2>
    <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:16px;">
        <?= Lang::t(
            'Ku bixi daynta EVC Plus, Zaad ama eDahab. Waxaad diri kartaa ilaa inta lagugu leeyahay oo keliya.',
            'Pay your financing with EVC Plus, Zaad or eDahab. You can send up to what you owe, no more.'
        ) ?>
    </p>

    <?php if (empty($financings)): ?>
        <div style="padding:20px;background:var(--bg-elevated);border-radius:10px;color:var(--text-muted);">
            <?= Lang::t('Ma jirto dayn lagugu leeyahay hadda. Mahadsanid!', 'You have nothing to pay right now. Thank you!') ?>
        </div>
    <?php else: ?>
    <form method="POST" action="?route=client_pay_submit" id="clientPayForm" novalidate>
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

        <div class="form-group" style="margin-bottom:14px;">
            <label for="cpFinancing"><?= Lang::t('Daynta aad bixinayso *', 'Financing you are paying *') ?></label>
            <select name="financing_id" id="cpFinancing" class="form-control" required>
                <?php foreach ($financings as $f): ?>
                    <option value="<?= (int) $f['id'] ?>"
                            data-owed="<?= Security::e($f['owed']) ?>"
                            data-next="<?= Security::e((string) ($f['next_due_amount'] ?? '')) ?>"
                            data-next-date="<?= Security::e((string) ($f['next_due_date'] ?? '')) ?>"
                            <?= (int) $f['id'] === $selectedId ? 'selected' : '' ?>>
                        <?= Security::e($f['financing_number']) ?> — <?= Lang::t('lagugu leeyahay', 'you owe') ?> $<?= Security::e($f['owed']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="kpi-grid" style="margin-bottom:14px;">
            <div class="kpi-card">
                <div class="kpi-label"><?= Lang::t('Wadarta lagugu leeyahay', 'Total you owe') ?></div>
                <div class="kpi-value num-tabular" id="cpOwedText">—</div>
                <div class="kpi-sub"><?= Lang::t('Lacagta ugu badan ee aad diri karto', 'The most you can send') ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><?= Lang::t('Qaybta xigta', 'Next installment') ?></div>
                <div class="kpi-value num-tabular" id="cpNextText">—</div>
                <div class="kpi-sub" id="cpNextDate">&nbsp;</div>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:14px;">
            <label><?= Lang::t('Habka lacag-bixinta *', 'Payment method *') ?></label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php foreach (\App\Services\ClientPaymentService::PROVIDERS as $i => $p): ?>
                    <label class="btn btn-outline btn-sm" style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="radio" name="provider" value="<?= Security::e($p) ?>" <?= $i === 0 ? 'checked' : '' ?> required>
                        <?= Security::e($p) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="cpPhone"><?= Lang::t('Lambarka aad ka diraysid *', 'Phone you are paying from *') ?></label>
                <input type="tel" name="phone" id="cpPhone" class="form-control num-tabular" value="<?= Security::e($phone) ?>" required autocomplete="tel">
            </div>
            <div class="form-group">
                <label for="cpAmount"><?= Lang::t('Lacagta ($ USD) *', 'Amount ($ USD) *') ?></label>
                <input type="number" name="amount" id="cpAmount" class="form-control num-tabular" step="0.01" min="0.01" inputmode="decimal" required>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;">
                    <button type="button" class="btn btn-outline btn-xs" id="cpFillNext"><?= Lang::t('Qaybta xigta', 'Next installment') ?></button>
                    <button type="button" class="btn btn-outline btn-xs" id="cpFillAll"><?= Lang::t('Dhammaan', 'Pay all') ?></button>
                </div>
            </div>
        </div>

        <div id="cpError" role="alert" style="display:none;margin:4px 0 12px;padding:10px 12px;border-radius:8px;background:rgba(201,75,75,0.1);color:var(--color-danger);font-weight:600;font-size:0.88rem;"></div>

        <button type="submit" class="btn btn-primary" id="cpSubmit" style="display:inline-flex;align-items:center;gap:6px;">
            <?= Icons::svg('send', 15) ?> <span><?= Lang::t('Dir Lacagta', 'Send Payment') ?></span>
        </button>
    </form>
    <?php endif; ?>
</div>

<?php if (!empty($financings)): ?>
<script>
(function () {
    var form = document.getElementById('clientPayForm');
    var sel = document.getElementById('cpFinancing');
    var amount = document.getElementById('cpAmount');
    var err = document.getElementById('cpError');
    var submit = document.getElementById('cpSubmit');
    var isSo = <?= json_encode(Lang::locale() === 'so') ?>;

    function cents(v) { var n = Number(v); return isFinite(n) ? Math.round(n * 100) : NaN; }
    function money(c) { return '$' + (c / 100).toFixed(2); }
    function current() { return sel.options[sel.selectedIndex]; }

    function refresh() {
        var o = current();
        document.getElementById('cpOwedText').textContent = money(cents(o.dataset.owed));
        var next = o.dataset.next ? Math.min(cents(o.dataset.next), cents(o.dataset.owed)) : NaN;
        document.getElementById('cpNextText').textContent = isNaN(next) ? '—' : money(next);
        document.getElementById('cpNextDate').textContent = o.dataset.nextDate ? ((isSo ? 'Taariikhda: ' : 'Due: ') + o.dataset.nextDate) : '';
        amount.max = (cents(o.dataset.owed) / 100).toFixed(2);
        check();
    }

    // Same rule as the server: more than $0 and no more than what is owed.
    function check() {
        var owed = cents(current().dataset.owed);
        var v = amount.value.trim();
        var msg = '';
        if (v !== '') {
            var c = cents(v);
            if (isNaN(c) || c <= 0) {
                msg = isSo ? 'Lacagtu waa inay ka badan tahay $0.' : 'The amount must be more than $0.';
            } else if (c > owed) {
                msg = isSo
                    ? 'Lacagtaas lama oggola. Waxaa lagugu leeyahay ' + money(owed) + ' oo keliya. Kaliya dir ' + money(owed) + ' ama ka yar.'
                    : 'That amount is not allowed. You owe only ' + money(owed) + '. Send ' + money(owed) + ' or less.';
            }
        }
        err.textContent = msg;
        err.style.display = msg ? 'block' : 'none';
        submit.disabled = msg !== '';
        return msg === '' && v !== '';
    }

    sel.addEventListener('change', refresh);
    amount.addEventListener('input', check);
    document.getElementById('cpFillAll').addEventListener('click', function () {
        amount.value = (cents(current().dataset.owed) / 100).toFixed(2);
        check();
    });
    document.getElementById('cpFillNext').addEventListener('click', function () {
        var o = current();
        var next = o.dataset.next ? Math.min(cents(o.dataset.next), cents(o.dataset.owed)) : cents(o.dataset.owed);
        amount.value = (next / 100).toFixed(2);
        check();
    });
    form.addEventListener('submit', function (e) {
        if (!check()) {
            e.preventDefault();
            if (amount.value.trim() === '') {
                err.textContent = isSo ? 'Geli lacagta aad diraysid.' : 'Enter the amount you are sending.';
                err.style.display = 'block';
            }
            return;
        }
        var provider = form.querySelector('input[name="provider"]:checked');
        var text = isSo
            ? 'Ma diraysaa ' + money(cents(amount.value)) + ' adigoo isticmaalaya ' + (provider ? provider.value : '') + '?'
            : 'Send ' + money(cents(amount.value)) + ' with ' + (provider ? provider.value : '') + '?';
        if (!window.confirm(text)) {
            e.preventDefault();
        }
    });
    refresh();
})();
</script>
<?php endif; ?>
