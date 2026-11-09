<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isSo = Lang::locale() === 'so';
$broadcastClients = $broadcastClients ?? [];
$bOld = $_SESSION['broadcast_old'] ?? [];
unset($_SESSION['broadcast_old']);
$withEmail = count(array_filter($broadcastClients, static fn ($c) => trim((string) ($c['email'] ?? '')) !== ''));
$withPhone = count(array_filter($broadcastClients, static fn ($c) => trim((string) ($c['phone'] ?? '')) !== ''));
?>

<?php if (!empty($broadcastClients)): ?>
<style>
    .bc-grid { display:grid; grid-template-columns:minmax(0,1.1fr) minmax(0,1fr); gap:20px; }
    @media (max-width: 900px) { .bc-grid { grid-template-columns:1fr; } }
    .bc-list { border:1px solid var(--border-color, #e3e8e5); border-radius:12px; max-height:340px; overflow-y:auto; }
    .bc-row { display:flex; align-items:center; gap:10px; padding:9px 12px; border-bottom:1px solid var(--border-color, #eef1ef); cursor:pointer; }
    .bc-row:last-child { border-bottom:0; }
    .bc-row:hover { background:var(--bg-hover, rgba(18,59,50,0.04)); }
    .bc-row input { width:17px; height:17px; flex-shrink:0; accent-color:#123B32; }
    .bc-name { font-weight:600; font-size:0.88rem; }
    .bc-meta { font-size:0.74rem; color:var(--text-muted); overflow-wrap:anywhere; }
    .bc-missing { color:#B4532A; }
    .bc-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:14px; }
    .bc-btn-wa { background:#1F9D55; border-color:#1F9D55; color:#fff; }
    .bc-btn-wa:hover { background:#188246; }
    .bc-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; margin-bottom:10px; }
</style>

<div class="card animate-in" id="broadcast" style="margin-bottom:20px;">
    <h2 style="font-size:1.2rem;margin-top:6px;"><?= $isSo ? 'U dir fariin macaamiisha' : 'Send a message to clients' ?></h2>
    <p style="font-size:0.84rem;color:var(--text-secondary);margin-bottom:16px;">
        <?= $isSo
            ? 'Qor fariinta (tusaale wax cusub oo shirkaddu kordhisay), dooro macaamiisha, kadibna dir Gmail ama WhatsApp.'
            : 'Write the message (for example a new service), pick the clients, then send it by Gmail or WhatsApp.' ?>
    </p>

    <form method="POST" action="?route=sms_broadcast" id="bcForm">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <input type="hidden" name="channel" id="bcChannel" value="">
        <div class="bc-grid">
            <div>
                <div class="form-group">
                    <label for="bcSubject"><?= $isSo ? 'Cinwaanka (Gmail)' : 'Subject (Gmail)' ?></label>
                    <input type="text" id="bcSubject" name="subject" class="form-control" maxlength="150"
                           placeholder="<?= $isSo ? 'Tusaale: Adeeg cusub oo SIMDA' : 'e.g. New service at SIMDA' ?>"
                           value="<?= Security::e((string) ($bOld['subject'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label for="bcMessage"><?= $isSo ? 'Fariinta *' : 'Message *' ?></label>
                    <textarea id="bcMessage" name="message" class="form-control" rows="9" maxlength="3000" required
                              placeholder="<?= $isSo ? 'Qor fariinta aad u direyso macaamiisha…' : 'Write the message for your clients…' ?>"><?= Security::e((string) ($bOld['message'] ?? '')) ?></textarea>
                    <div style="font-size:0.74rem;color:var(--text-muted);text-align:right;margin-top:4px;"><span id="bcCount">0</span> / 3000</div>
                </div>
                <p style="font-size:0.78rem;color:var(--text-muted);">
                    <?= $isSo
                        ? 'Fariin kasta waxay ku bilaabanaysaa magaca macmiilka (Salaamu Calaykum, …).'
                        : 'Each message starts with the client\'s name (Hello, …).' ?>
                </p>
            </div>

            <div>
                <div class="bc-toolbar">
                    <label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:0.88rem;cursor:pointer;">
                        <input type="checkbox" id="bcAll" checked style="width:17px;height:17px;accent-color:#123B32;">
                        <span><?= $isSo ? 'Dooro dhammaan' : 'Select all' ?></span>
                    </label>
                    <span style="font-size:0.8rem;color:var(--text-secondary);">
                        <strong id="bcSelected"><?= count($broadcastClients) ?></strong> / <?= count($broadcastClients) ?> <?= $isSo ? 'la doortay' : 'selected' ?>
                    </span>
                </div>
                <input type="search" id="bcSearch" class="form-control" style="margin-bottom:10px;"
                       placeholder="<?= $isSo ? 'Raadi magac, lambar ama email…' : 'Search name, phone or email…' ?>">
                <div class="bc-list" id="bcList">
                    <?php foreach ($broadcastClients as $c):
                        $em = trim((string) ($c['email'] ?? ''));
                        $ph = trim((string) ($c['phone'] ?? ''));
                    ?>
                        <label class="bc-row" data-search="<?= Security::e(mb_strtolower($c['full_name'] . ' ' . $c['client_code'] . ' ' . $ph . ' ' . \App\Services\WhatsAppService::displayPhone($ph) . ' ' . $em)) ?>"
                               data-email="<?= $em !== '' ? '1' : '0' ?>" data-phone="<?= $ph !== '' ? '1' : '0' ?>">
                            <input type="checkbox" name="client_ids[]" value="<?= (int) $c['id'] ?>" class="bc-check" checked>
                            <span style="min-width:0;">
                                <span class="bc-name"><?= Security::e($c['full_name']) ?></span>
                                <?php if (($c['status'] ?? 'active') !== 'active'): ?>
                                    <span class="badge badge-warning" style="margin-left:6px;"><?= Security::e($c['status']) ?></span>
                                <?php endif; ?>
                                <span class="bc-meta" style="display:block;">
                                    <span class="num-tabular"><?= $ph !== '' ? Security::e(\App\Services\WhatsAppService::displayPhone($ph)) : '<span class="bc-missing">' . ($isSo ? 'lambar ma laha' : 'no phone') . '</span>' ?></span>
                                    · <?= $em !== '' ? Security::e($em) : '<span class="bc-missing">' . ($isSo ? 'email ma laha' : 'no email') . '</span>' ?>
                                </span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                    <div id="bcEmpty" style="display:none;padding:14px;text-align:center;color:var(--text-muted);font-size:0.84rem;">
                        <?= $isSo ? 'Macmiil lama helin.' : 'No clients found.' ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="bc-actions">
            <button type="submit" class="btn btn-primary" data-channel="email">
                <?= Icons::svg('mail', 16) ?>
                <span><?= $isSo ? 'U dir Gmail' : 'Send by Gmail' ?> (<span class="bc-n-email">0</span>)</span>
            </button>
            <button type="submit" class="btn bc-btn-wa" data-channel="whatsapp" <?= empty($whatsappReady) ? 'title="' . Security::e($isSo ? 'WhatsApp gateway-ga lama daarin' : 'WhatsApp gateway is not running') . '"' : '' ?>>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.9s.7-2.1 1-2.4c.3-.3.6-.3.8-.3h.6c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .5l-.3.5-.4.5c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 .9c.3.1.5.2.5.3.1.1.1.7-.1 1.3z"/></svg>
                <span><?= $isSo ? 'U dir WhatsApp' : 'Send by WhatsApp' ?> (<span class="bc-n-phone">0</span>)</span>
            </button>
        </div>
        <?php if (empty($whatsappReady)): ?>
            <p style="font-size:0.78rem;color:#B4532A;margin-top:8px;">
                <?= $isSo
                    ? 'WhatsApp lama dejin: .env-ka ku qor WHATSAPP_ENABLED=true kadibna daar whatsapp-gateway/start-whatsapp.bat.'
                    : 'WhatsApp is not set up: set WHATSAPP_ENABLED=true in .env and run whatsapp-gateway/start-whatsapp.bat.' ?>
            </p>
        <?php endif; ?>
    </form>
</div>

<script>
(function () {
    var form = document.getElementById('bcForm');
    if (!form) return;
    var isSo = <?= $isSo ? 'true' : 'false' ?>;
    var checks = Array.prototype.slice.call(form.querySelectorAll('.bc-check'));
    var all = document.getElementById('bcAll');
    var search = document.getElementById('bcSearch');
    var msg = document.getElementById('bcMessage');
    var channel = document.getElementById('bcChannel');

    function visible(cb) { return cb.closest('.bc-row').style.display !== 'none'; }
    function refresh() {
        var sel = checks.filter(function (c) { return c.checked; });
        document.getElementById('bcSelected').textContent = sel.length;
        form.querySelector('.bc-n-email').textContent = sel.filter(function (c) { return c.closest('.bc-row').dataset.email === '1'; }).length;
        form.querySelector('.bc-n-phone').textContent = sel.filter(function (c) { return c.closest('.bc-row').dataset.phone === '1'; }).length;
        var shown = checks.filter(visible);
        all.checked = shown.length > 0 && shown.every(function (c) { return c.checked; });
        all.indeterminate = !all.checked && shown.some(function (c) { return c.checked; });
        document.getElementById('bcCount').textContent = msg.value.length;
    }
    all.addEventListener('change', function () {
        checks.filter(visible).forEach(function (c) { c.checked = all.checked; });
        refresh();
    });
    checks.forEach(function (c) { c.addEventListener('change', refresh); });
    msg.addEventListener('input', refresh);
    search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase();
        var any = false;
        checks.forEach(function (c) {
            var row = c.closest('.bc-row');
            var hit = q === '' || row.dataset.search.indexOf(q) !== -1;
            row.style.display = hit ? '' : 'none';
            if (hit) any = true;
        });
        document.getElementById('bcEmpty').style.display = any ? 'none' : '';
        refresh();
    });
    form.querySelectorAll('button[data-channel]').forEach(function (btn) {
        btn.addEventListener('click', function () { channel.value = btn.dataset.channel; });
    });
    form.addEventListener('submit', function (e) {
        var isEmail = channel.value === 'email';
        var n = parseInt(form.querySelector(isEmail ? '.bc-n-email' : '.bc-n-phone').textContent, 10) || 0;
        if (msg.value.trim() === '') { e.preventDefault(); msg.focus(); return; }
        if (n === 0) {
            e.preventDefault();
            alert(isSo ? 'Dooro ugu yaraan hal macmiil oo leh ' + (isEmail ? 'email.' : 'lambar.') : 'Select at least one client with ' + (isEmail ? 'an email.' : 'a phone number.'));
            return;
        }
        var label = isEmail ? 'Gmail' : 'WhatsApp';
        if (!confirm(isSo ? 'Ma u diraysaa fariintan ' + n + ' macmiil (' + label + ')?' : 'Send this message to ' + n + ' client(s) by ' + label + '?')) {
            e.preventDefault();
            return;
        }
        form.querySelectorAll('button[data-channel]').forEach(function (b) { b.disabled = true; });
    });
    refresh();
})();
</script>
<?php endif; ?>

<div class="card animate-in">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;margin-bottom:16px;">
        <div>
            <h2 style="font-size:1.2rem;margin-top:6px;"><?= Security::e(Lang::get('menu_sms_outbox')) ?></h2>
            <p style="font-size:0.84rem;color:var(--text-secondary);">
                Dispatches 3 days before due date (`upcoming_3_days`) and 1 day after a missed due date (`overdue_1_day`). Deduplicated per installment and reminder type; paid installments never receive reminders.
            </p>
        </div>
        <form method="POST" action="?route=sms_run_dispatcher">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
            <button type="submit" class="btn btn-primary btn-sm">
                <?= Icons::svg('refresh', 14) ?>
                <span><?= \App\Helpers\Lang::t('Hadda Dir Xasuusinta', 'Run Reminder Dispatcher Now') ?></span>
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th><?= \App\Helpers\Lang::t('Macmiilka & Telefoonka', 'Client & Phone') ?></th>
                    <th><?= \App\Helpers\Lang::t('Heshiiska', 'Contract') ?></th>
                    <th><?= \App\Helpers\Lang::t('Nooca', 'Type') ?></th>
                    <th><?= \App\Helpers\Lang::t('Taariikhda & Lacagta', 'Due Date & Amount') ?></th>
                    <th><?= \App\Helpers\Lang::t('Fariinta (English & Soomaali)', 'Message (English & Somali)') ?></th>
                    <th><?= \App\Helpers\Lang::t('Waqtiga La Diray', 'Sent At') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);"><?= \App\Helpers\Lang::t('Weli ma jiraan xasuusin SMS ah.', 'No SMS reminders in outbox yet.') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $m): ?>
                        <tr>
                            <td>
                                <strong><?= Security::e($m['client_name']) ?></strong>
                                <div class="num-tabular" style="font-size:0.76rem;color:var(--text-muted);"><?= Security::e($m['phone']) ?></div>
                            </td>
                            <td class="num-tabular"><?= Security::e($m['financing_number']) ?></td>
                            <td>
                                <span class="badge badge-<?= $m['reminder_type'] === 'overdue_1_day' ? 'danger' : 'info' ?>">
                                    <?= Security::e($m['reminder_type']) ?>
                                </span>
                            </td>
                            <td class="num-tabular">
                                <div><?= Security::e($m['due_date']) ?></div>
                                <strong><?= Security::formatMoney($m['amount_due']) ?></strong>
                            </td>
                            <td style="max-width:440px;font-size:0.82rem;">
                                <div style="margin-bottom:4px;"><strong>EN:</strong> <?= Security::e($m['message_en']) ?></div>
                                <div style="color:var(--text-secondary);"><strong>SO:</strong> <?= Security::e($m['message_so']) ?></div>
                            </td>
                            <td class="num-tabular" style="font-size:0.76rem;"><?= Security::e($m['sent_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
