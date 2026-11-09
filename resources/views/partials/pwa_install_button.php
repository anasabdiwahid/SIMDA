<?php
/**
 * Floating "DOWNLOAD" button that installs SIMDA as an app (PWA).
 * Sits just above the SIMDA AI robot, pops in for 7 seconds, hides for 7 seconds, and repeats.
 * Uses the browser's install prompt when it offers one; otherwise shows short how-to steps
 * (iPhone Safari, or a browser that only installs from its own menu). Hidden once installed.
 */
declare(strict_types=1);

use App\Helpers\Lang;

$pwaLocale = Lang::locale();
$pwaT = [
    'so' => [
        'btn' => 'DOWNLOAD',
        'aria' => 'Soo deji app-ka SIMDA',
        'title' => 'Soo deji app-ka SIMDA',
        'ios' => 'Taabo batoonka Share <b>⬆</b> ee hoose, kadibna dooro <b>"Add to Home Screen"</b>.',
        'other' => 'Fur menu-ga browser-ka <b>⋮</b>, kadibna dooro <b>"Install app"</b> ama <b>"Add to Home screen"</b>.',
        'ok' => 'Waan fahmay',
    ],
    'en' => [
        'btn' => 'DOWNLOAD',
        'aria' => 'Download the SIMDA app',
        'title' => 'Download the SIMDA app',
        'ios' => 'Tap the Share button <b>⬆</b> below, then choose <b>"Add to Home Screen"</b>.',
        'other' => 'Open the browser menu <b>⋮</b>, then choose <b>"Install app"</b> or <b>"Add to Home screen"</b>.',
        'ok' => 'Got it',
    ],
    'ar' => [
        'btn' => 'تحميل',
        'aria' => 'تحميل تطبيق سيمدا',
        'title' => 'تحميل تطبيق سيمدا',
        'ios' => 'اضغط زر المشاركة <b>⬆</b> بالأسفل، ثم اختر <b>"إضافة إلى الشاشة الرئيسية"</b>.',
        'other' => 'افتح قائمة المتصفح <b>⋮</b>، ثم اختر <b>"تثبيت التطبيق"</b>.',
        'ok' => 'حسناً',
    ],
][$pwaLocale] ?? null;
$pwaT = $pwaT ?? [
    'btn' => 'DOWNLOAD', 'aria' => 'Download the SIMDA app', 'title' => 'Download the SIMDA app',
    'ios' => 'Tap Share, then "Add to Home Screen".', 'other' => 'Open the browser menu, then "Install app".', 'ok' => 'OK',
];
?>
<div class="pwa-dl" id="pwaDl" hidden>
    <button type="button" class="pwa-dl-btn" id="pwaDlBtn" aria-label="<?= htmlspecialchars($pwaT['aria'], ENT_QUOTES, 'UTF-8') ?>">
        <span class="pwa-dl-text"><?= htmlspecialchars($pwaT['btn'], ENT_QUOTES, 'UTF-8') ?></span>
        <svg class="pwa-dl-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="12" y1="3" x2="12" y2="15"/><polyline points="6 10 12 16 18 10"/><line x1="5" y1="21" x2="19" y2="21"/>
        </svg>
        <svg class="pwa-dl-hand" width="34" height="38" viewBox="0 0 34 38" aria-hidden="true">
            <g class="pwa-dl-clicks" stroke="#1D2B26" stroke-width="2" stroke-linecap="round">
                <line x1="6" y1="6" x2="2" y2="2"/><line x1="12" y1="3" x2="12" y2="0"/><line x1="3" y1="12" x2="0" y2="12"/>
            </g>
            <path d="M11 9c0-1.7 2.6-1.7 2.6 0v9.5l1-.1v-3c0-1.6 2.5-1.6 2.5 0v3.2l1-.1v-2.2c0-1.6 2.5-1.6 2.5 0v2.6l1-.1v-1.2c0-1.6 2.4-1.6 2.4 0v7.6c0 4.6-3 8.3-7.6 8.3h-1.6c-2.6 0-4.3-1.1-5.7-3.2l-4.3-6.4c-.9-1.4 1-2.8 2.2-1.6l2.6 2.7z"
                  fill="#FFFFFF" stroke="#1D2B26" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>
    </button>
</div>

<div class="pwa-help" id="pwaHelp" role="dialog" aria-modal="false" aria-labelledby="pwaHelpTitle" hidden>
    <strong id="pwaHelpTitle"><?= htmlspecialchars($pwaT['title'], ENT_QUOTES, 'UTF-8') ?></strong>
    <p id="pwaHelpText" data-ios="<?= htmlspecialchars($pwaT['ios'], ENT_QUOTES, 'UTF-8') ?>" data-other="<?= htmlspecialchars($pwaT['other'], ENT_QUOTES, 'UTF-8') ?>"></p>
    <button type="button" id="pwaHelpOk"><?= htmlspecialchars($pwaT['ok'], ENT_QUOTES, 'UTF-8') ?></button>
</div>

<style>
/* Sits right above the SIMDA AI robot (robot: bottom 30px, 90px tall). */
.pwa-dl { position: fixed; bottom: 146px; inset-inline-end: 14px; z-index: 9999; pointer-events: none; }
.pwa-dl[hidden] { display: none; }
.pwa-dl-btn {
    position: relative; display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 20px 13px; border: 0; border-radius: 14px; cursor: pointer;
    font-family: inherit; font-weight: 900; font-size: 1.02rem; line-height: 1; letter-spacing: 0.06em; color: #FFFFFF;
    background: linear-gradient(180deg, #F2493B 0%, #E0322A 100%);
    box-shadow: 0 5px 0 #A9201A, 0 14px 26px rgba(224, 50, 42, 0.38);
    text-shadow: 0 2px 0 rgba(120, 18, 12, 0.35);
    pointer-events: auto; -webkit-tap-highlight-color: transparent;
    opacity: 0; transform: translateY(40px) scale(0.6); visibility: hidden;
}
.pwa-dl-btn:focus-visible { outline: 3px solid #D6B66C; outline-offset: 4px; }
.pwa-dl-btn:active { transform: translateY(4px) !important; box-shadow: 0 1px 0 #A9201A, 0 6px 14px rgba(224, 50, 42, 0.35); }
.pwa-dl-arrow { flex-shrink: 0; }
.pwa-dl-hand { position: absolute; inset-inline-end: -14px; bottom: -24px; pointer-events: none; }
[dir="rtl"] .pwa-dl-hand { transform: scaleX(-1); }

/* Coming in: rises from below and bounces into place. Leaving: dips, then drops away. */
.pwa-dl.is-in .pwa-dl-btn { visibility: visible; animation: pwaIn 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
.pwa-dl.is-out .pwa-dl-btn { visibility: visible; animation: pwaOut 0.5s ease-in forwards; }
@keyframes pwaIn {
    0%   { opacity: 0; transform: translateY(40px) scale(0.6); }
    60%  { opacity: 1; transform: translateY(-8px) scale(1.06); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes pwaOut {
    0%   { opacity: 1; transform: translateY(0) scale(1); }
    30%  { opacity: 1; transform: translateY(-6px) scale(1.04); }
    100% { opacity: 0; transform: translateY(40px) scale(0.6); }
}
/* While shown: the arrow nudges down and the hand taps the button. */
.pwa-dl.is-in .pwa-dl-arrow { animation: pwaArrow 1.1s ease-in-out 0.7s infinite; }
.pwa-dl.is-in .pwa-dl-hand { animation: pwaTap 1.1s ease-in-out 0.7s infinite; }
.pwa-dl.is-in .pwa-dl-clicks { animation: pwaClicks 1.1s ease-in-out 0.7s infinite; }
@keyframes pwaArrow { 0%, 100% { transform: translateY(-2px); } 50% { transform: translateY(3px); } }
@keyframes pwaTap { 0%, 100% { translate: 0 0; } 45% { translate: -4px -5px; } 60% { translate: 0 0; } }
@keyframes pwaClicks { 0%, 40% { opacity: 0; } 55%, 75% { opacity: 1; } 100% { opacity: 0; } }

.pwa-help {
    position: fixed; bottom: 206px; inset-inline-end: 14px; z-index: 10001; width: min(300px, calc(100vw - 28px));
    padding: 16px 16px 14px; border-radius: 16px; background: #FFFFFF; color: #1D2B26;
    border: 1px solid #ECE7DD; box-shadow: 0 18px 40px rgba(18, 59, 50, 0.22); font-size: 0.88rem; line-height: 1.5;
    animation: pwaIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pwa-help[hidden] { display: none; }
.pwa-help strong { display: block; margin-bottom: 6px; color: #123B32; font-size: 0.95rem; }
.pwa-help p { margin: 0 0 12px; }
.pwa-help button { border: 0; border-radius: 10px; padding: 8px 14px; background: #123B32; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 0.85rem; cursor: pointer; }
[data-theme="dark"] .pwa-help { background: #13231D; color: #EDF1EE; border-color: #2A4239; }
[data-theme="dark"] .pwa-help strong { color: #D6B66C; }

@media (max-width: 680px) {
    .pwa-dl { bottom: 130px; inset-inline-end: 10px; } /* phone robot: bottom 24px, 72px tall */
    .pwa-dl-btn { padding: 10px 15px 11px; font-size: 0.9rem; gap: 8px; border-radius: 12px; }
    .pwa-help { bottom: 184px; inset-inline-end: 10px; }
}
/* Chat open: get out of the way. */
.aic.is-open ~ .pwa-dl, body:has(.aic.is-open) .pwa-dl { display: none; }
@media (prefers-reduced-motion: reduce) {
    .pwa-dl.is-in .pwa-dl-btn, .pwa-dl.is-out .pwa-dl-btn { animation-duration: 0.01s; }
    .pwa-dl.is-in .pwa-dl-arrow, .pwa-dl.is-in .pwa-dl-hand, .pwa-dl.is-in .pwa-dl-clicks { animation: none; }
}
</style>

<script>
(function () {
    'use strict';
    var wrap = document.getElementById('pwaDl');
    var btn = document.getElementById('pwaDlBtn');
    var help = document.getElementById('pwaHelp');
    var helpText = document.getElementById('pwaHelpText');
    if (!wrap || !btn) return;

    var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
    if (standalone) return; // already running as the installed app

    var SHOW_MS = 7000, HIDE_MS = 7000;
    var deferred = null;
    var timer = null;
    var held = false; // mouse over / keyboard focus keeps it on screen

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferred = e;
    });
    window.addEventListener('appinstalled', function () { stop(); wrap.remove(); if (help) help.hidden = true; });

    function show() {
        wrap.classList.remove('is-out');
        void wrap.offsetWidth; // restart the animation
        wrap.classList.add('is-in');
        timer = setTimeout(hide, SHOW_MS);
    }
    function hide() {
        if (held || (help && !help.hidden)) { timer = setTimeout(hide, 1000); return; }
        wrap.classList.remove('is-in');
        wrap.classList.add('is-out');
        timer = setTimeout(show, HIDE_MS);
    }
    function stop() { clearTimeout(timer); }

    btn.addEventListener('mouseenter', function () { held = true; });
    btn.addEventListener('mouseleave', function () { held = false; });
    btn.addEventListener('focus', function () { held = true; });
    btn.addEventListener('blur', function () { held = false; });

    btn.addEventListener('click', function () {
        if (deferred) {
            var p = deferred;
            deferred = null; // a prompt can only be used once
            p.prompt();
            p.userChoice.then(function (choice) {
                if (choice && choice.outcome === 'accepted') { stop(); wrap.remove(); }
            }).catch(function () {});
            return;
        }
        if (!help) return;
        var ua = navigator.userAgent || '';
        var ios = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        helpText.innerHTML = ios ? helpText.dataset.ios : helpText.dataset.other;
        help.hidden = false;
        document.getElementById('pwaHelpOk').focus();
    });
    if (help) {
        document.getElementById('pwaHelpOk').addEventListener('click', function () { help.hidden = true; btn.focus(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') help.hidden = true; });
    }

    wrap.hidden = false;
    timer = setTimeout(show, 1500);
})();
</script>
