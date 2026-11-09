<?php
/**
 * Repayment plan picker shared by the Cash Loan and Murabaha forms.
 *
 * Posts:  payment_mode         installments | single
 *         repayment_frequency  minutely | hourly | daily | weekly | monthly   (the unit)
 *         repayment_interval   units between payments (single: units until the one payment)
 *         term_installments    number of payments (ignored for single = 1)
 *
 * window.simdaPlan.read() gives the current plan to the page's summary script;
 * window.simdaPlan.onChange(fn) runs fn whenever it changes.
 *
 * Expects $isSo and optional $planCounts (quick picks for the number of payments).
 */
declare(strict_types=1);

$planCounts = $planCounts ?? [1, 3, 6, 12, 24];
$units = [
    'minutely' => $isSo ? 'Daqiiqo' : 'Minutes',
    'hourly' => $isSo ? 'Saac' : 'Hours',
    'daily' => $isSo ? 'Maalin' : 'Days',
    'weekly' => $isSo ? 'Toddobaad' : 'Weeks',
    'monthly' => $isSo ? 'Bil' : 'Months',
];
$presets = [
    // [label, mode, interval, unit, count]
    [$isSo ? '30 daqiiqo' : '30 minutes', 'single', 30, 'minutely', 1],
    [$isSo ? '1 saac' : '1 hour', 'single', 1, 'hourly', 1],
    [$isSo ? '1 maalin' : '1 day', 'single', 1, 'daily', 1],
    [$isSo ? '7 maalmood' : '7 days', 'installments', 1, 'daily', 7],
    [$isSo ? '4 toddobaad' : '4 weeks', 'installments', 1, 'weekly', 4],
    [$isSo ? '6 bilood' : '6 months', 'installments', 1, 'monthly', 6],
    [$isSo ? '12 bilood' : '12 months', 'installments', 1, 'monthly', 12],
];
?>
<style>
.rp-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.rp-row .form-control { height: 44px; font-size: 0.95rem; border-radius: 10px; }
.rp-num { width: 110px; }
.rp-unit { width: 170px; }
.rp-hint { margin-top: 8px; font-size: 0.82rem; color: var(--text-secondary); }
.rp-hint strong { color: var(--text-primary); }
.rp-presets { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.rp-block[hidden] { display: none; }
#rpEveryLabel, #rpCountBlock > .mf-label { margin-top: 16px; }
.rp-presets { margin-top: 14px; }
</style>

<div class="mf-label"><?= $isSo ? 'Qaabka bixinta' : 'How it is repaid' ?></div>
<div class="mf-segment" role="radiogroup">
    <label><input type="radio" name="payment_mode" value="installments" checked><span><?= $isSo ? 'Qeyb-qeyb' : 'Installments' ?></span></label>
    <label><input type="radio" name="payment_mode" value="single"><span><?= $isSo ? 'Hal mar' : 'One payment' ?></span></label>
</div>

<div class="mf-label" id="rpEveryLabel"><?= $isSo ? 'Lacag-bixin kasta' : 'One payment every' ?></div>
<div class="rp-row">
    <input type="number" name="repayment_interval" id="rpInterval" class="form-control num-tabular rp-num" min="1" max="1000" step="1" value="1" required
           aria-label="<?= $isSo ? 'Tirada waqtiga' : 'Amount of time' ?>">
    <select name="repayment_frequency" id="rpUnit" class="form-control rp-unit" aria-label="<?= $isSo ? 'Nooca waqtiga' : 'Time unit' ?>">
        <?php foreach ($units as $value => $label): ?>
            <option value="<?= $value ?>" <?= $value === 'monthly' ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="rp-block" id="rpCountBlock">
    <div class="mf-label"><?= $isSo ? 'Tirada qaybaha' : 'Number of payments' ?></div>
    <div class="rp-row">
        <input type="number" name="term_installments" id="rpCount" class="form-control num-tabular rp-num" min="1" max="360" step="1" value="6" required
               aria-label="<?= $isSo ? 'Tirada qaybaha' : 'Number of payments' ?>">
        <div class="mf-quick" style="margin:0;">
            <?php foreach ($planCounts as $n): ?>
                <button type="button" class="mf-quick-btn rp-count-btn" data-count="<?= (int) $n ?>"><?= (int) $n ?></button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="rp-presets" aria-label="<?= $isSo ? 'Doorasho degdeg ah' : 'Quick picks' ?>">
    <?php foreach ($presets as [$label, $mode, $interval, $unit, $count]): ?>
        <button type="button" class="mf-quick-btn rp-preset" data-mode="<?= $mode ?>" data-interval="<?= $interval ?>" data-unit="<?= $unit ?>" data-count="<?= $count ?>"><?= $label ?></button>
    <?php endforeach; ?>
</div>
<div class="rp-hint" id="rpHint" aria-live="polite"></div>

<script>
(function () {
    'use strict';
    var isSo = <?= $isSo ? 'true' : 'false' ?>;
    var names = isSo
        ? { minutely: ['daqiiqo', 'daqiiqo'], hourly: ['saac', 'saacadood'], daily: ['maalin', 'maalmood'], weekly: ['toddobaad', 'toddobaad'], monthly: ['bil', 'bilood'] }
        : { minutely: ['minute', 'minutes'], hourly: ['hour', 'hours'], daily: ['day', 'days'], weekly: ['week', 'weeks'], monthly: ['month', 'months'] };
    var interval = document.getElementById('rpInterval');
    var unit = document.getElementById('rpUnit');
    var count = document.getElementById('rpCount');
    var countBlock = document.getElementById('rpCountBlock');
    var everyLabel = document.getElementById('rpEveryLabel');
    var hint = document.getElementById('rpHint');
    var listeners = [];
    // Count from SIMDA's clock (Africa/Mogadishu), not the computer's, so the preview matches the saved schedule.
    var serverStart = new Date(<?= (int) date('Y') ?>, <?= (int) date('n') - 1 ?>, <?= (int) date('j') ?>, <?= (int) date('G') ?>, <?= (int) date('i') ?>, <?= (int) date('s') ?>);
    var loadedAt = Date.now();
    function serverNow() { return new Date(serverStart.getTime() + (Date.now() - loadedAt)); }

    function span(n, u) { return n + ' ' + names[u][n === 1 ? 0 : 1]; }
    function addTime(d, n, u) {
        var x = new Date(d.getTime());
        if (u === 'minutely') x.setMinutes(x.getMinutes() + n);
        else if (u === 'hourly') x.setHours(x.getHours() + n);
        else if (u === 'daily') x.setDate(x.getDate() + n);
        else if (u === 'weekly') x.setDate(x.getDate() + 7 * n);
        else x.setMonth(x.getMonth() + n);
        return x;
    }
    function fmt(d, u) {
        var p = function (v) { return String(v).padStart(2, '0'); };
        var day = d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
        return (u === 'minutely' || u === 'hourly') ? day + ' ' + p(d.getHours()) + ':' + p(d.getMinutes()) : day;
    }

    function read() {
        var single = (document.querySelector('input[name="payment_mode"]:checked') || {}).value === 'single';
        var n = Math.max(1, Math.min(1000, parseInt(interval.value, 10) || 1));
        var u = unit.value;
        var c = single ? 1 : Math.max(1, Math.min(360, parseInt(count.value, 10) || 1));
        var last = addTime(serverNow(), n * c, u);
        var desc = single
            ? (isSo ? 'Hal mar · ' + span(n, u) + ' kadib' : 'One payment · after ' + span(n, u))
            : (isSo ? c + ' qaybood · ' + (n === 1 ? names[u][0] : span(n, u)) + ' kasta' : c + ' payments · every ' + (n === 1 ? names[u][0] : span(n, u)));
        return {
            single: single, interval: n, unit: u, count: c, description: desc, lastDue: fmt(last, u),
            perLabel: single ? (isSo ? 'Lacagta hal mar ah' : 'Single payment') : (isSo ? 'Qayb kasta' : 'Each installment')
        };
    }

    function refresh() {
        var p = read();
        countBlock.hidden = p.single;
        everyLabel.textContent = p.single
            ? (isSo ? 'Lacagta waxaa la bixinayaa kadib' : 'Repaid in one payment after')
            : (isSo ? 'Lacag-bixin kasta' : 'One payment every');
        hint.innerHTML = '<strong>' + p.description + '</strong> · ' + (isSo ? 'Bixinta ugu dambeysa: ' : 'Last payment: ') + p.lastDue
            + (p.unit === 'minutely' || p.unit === 'hourly' ? '' : (isSo ? ' (Jimcaha → Sabti)' : ' (Friday → Saturday)'));
        document.querySelectorAll('.rp-count-btn').forEach(function (b) { b.classList.toggle('is-active', !p.single && +b.dataset.count === p.count); });
        document.querySelectorAll('.rp-preset').forEach(function (b) {
            b.classList.toggle('is-active', b.dataset.mode === (p.single ? 'single' : 'installments') && +b.dataset.interval === p.interval && b.dataset.unit === p.unit && (p.single || +b.dataset.count === p.count));
        });
        listeners.forEach(function (fn) { fn(p); });
    }

    document.querySelectorAll('input[name="payment_mode"]').forEach(function (r) { r.addEventListener('change', refresh); });
    [interval, unit, count].forEach(function (el) { el.addEventListener('input', refresh); el.addEventListener('change', refresh); });
    document.querySelectorAll('.rp-count-btn').forEach(function (b) { b.addEventListener('click', function () { count.value = b.dataset.count; refresh(); }); });
    document.querySelectorAll('.rp-preset').forEach(function (b) {
        b.addEventListener('click', function () {
            var r = document.querySelector('input[name="payment_mode"][value="' + b.dataset.mode + '"]');
            if (r) r.checked = true;
            interval.value = b.dataset.interval;
            unit.value = b.dataset.unit;
            count.value = b.dataset.count;
            refresh();
        });
    });

    window.simdaPlan = { read: read, onChange: function (fn) { listeners.push(fn); } };
    document.addEventListener('DOMContentLoaded', refresh);
    refresh();
})();
</script>
