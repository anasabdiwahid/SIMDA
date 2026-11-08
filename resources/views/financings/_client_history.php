<?php
declare(strict_types=1);

/**
 * Client financing history alert for New Cash Loan / New Murabaha.
 * Expects $history (client_id => list of past financings) and $isSo.
 * The form calls window.renderClientHistory(clientId) when a client is picked, or with null to clear.
 */

use App\Helpers\Icons;
?>
<div class="ch-box" id="clientHistory" role="status" aria-live="polite" hidden></div>

<script>
(function () {
    const isSo = <?= json_encode((bool) ($isSo ?? false)) ?>;
    const history = <?= json_encode($history ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const icons = {
        ok: <?= json_encode(Icons::svg('check-circle', 18)) ?>,
        info: <?= json_encode(Icons::svg('info', 18)) ?>,
        warn: <?= json_encode(Icons::svg('alert-triangle', 18)) ?>
    };
    const money = (n) => '$' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    window.renderClientHistory = function (clientId) {
        const box = document.getElementById('clientHistory');
        if (!box) return;
        if (!clientId) { box.hidden = true; box.innerHTML = ''; return; }

        const items = history[clientId] || [];
        if (!items.length) {
            box.className = 'ch-box is-new';
            box.innerHTML = '<div class="ch-head">' + icons.ok + '<div><strong>'
                + (isSo ? 'Macmiil cusub' : 'New client') + '</strong><span>'
                + (isSo ? 'Macmiilkan weli dayn ama Murabaxo hore ma qaadan.' : 'This client has no previous loan or Murabaha.')
                + '</span></div></div>';
            box.hidden = false;
            return;
        }

        const loans = items.filter((i) => i.type === 'loan').length;
        const murabaha = items.length - loans;
        const late = items.filter((i) => i.late).length;
        const open = items.filter((i) => i.state === 'processing').length;

        const parts = [];
        if (loans) parts.push(loans + (isSo ? ' dayn' : (loans > 1 ? ' cash loans' : ' cash loan')));
        if (murabaha) parts.push(murabaha + (isSo ? ' Murabaxo' : ' Murabaha'));
        const took = isSo ? 'Macmiilkan hore ayuu u qaatay ' + parts.join(' iyo ') + '.' : 'This client has taken ' + parts.join(' and ') + ' before.';

        let title, sub;
        if (late) {
            title = isSo ? 'Digniin: waqtigii loogu talagalay kuma bixin' : 'Warning: did not pay on time';
            sub = took + ' ' + (isSo ? late + ' ka mid ah waqtigooda laguma bixin.' : late + ' of them were not paid on time.');
        } else if (open) {
            title = isSo ? 'Wuxuu leeyahay dayn socota' : 'Has financing in progress';
            sub = took + ' ' + (isSo ? open + ' weli waa la bixinayaa.' : open + ' still being repaid.');
        } else {
            title = isSo ? 'Wuxuu hore u dhammeystay daymihiisa' : 'Previous financing completed';
            sub = took + ' ' + (isSo ? 'Dhammaan waqtigooda ayuu ku bixiyay.' : 'All were paid on time.');
        }

        box.className = 'ch-box ' + (late ? 'is-late' : (open ? 'is-open' : 'is-good'));
        box.innerHTML = '<div class="ch-head">' + (late ? icons.warn : (open ? icons.info : icons.ok))
            + '<div><strong>' + esc(title) + '</strong><span>' + esc(sub) + '</span></div></div>'
            + '<ul class="ch-list">' + items.map(function (i) {
                const kind = i.type === 'murabaha' ? (isSo ? 'Murabaxo' : 'Murabaha') : (isSo ? 'Dayn' : 'Cash Loan');
                const state = i.state === 'complete'
                    ? '<span class="ch-pill is-done">' + (isSo ? 'Dhammaaday' : 'Complete') + '</span>'
                    : '<span class="ch-pill is-proc">' + (isSo ? 'Socda (Processing)' : 'Processing') + '</span>';
                const lateTag = i.late ? '<span class="ch-pill is-late">' + (isSo ? 'Waqtigii kuma bixin' : 'Not paid on time') + '</span>' : '';
                const money2 = i.state === 'complete'
                    ? money(i.amount)
                    : money(i.balance) + ' ' + (isSo ? 'haray' : 'left');
                return '<li class="' + (i.late ? 'is-late' : '') + '">'
                    + '<span class="ch-num num-tabular">' + esc(i.number) + '</span>'
                    + '<span class="ch-kind">' + kind + (i.date ? ' · ' + esc(i.date) : '') + '</span>'
                    + '<span class="ch-amt num-tabular">' + money2 + '</span>'
                    + '<span class="ch-tags">' + state + lateTag + '</span>'
                    + '</li>';
            }).join('') + '</ul>';
        box.hidden = false;
    };
})();
</script>
