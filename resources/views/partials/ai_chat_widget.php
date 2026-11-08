<?php
/**
 * Floating AI assistant for the public website (home, about, contact, FAQ).
 * A fixed round button (bottom corner) opens a chat panel that talks to ?route=faq_chat.
 * Not included in the dashboard layout, so it disappears after sign-in.
 */

use App\Helpers\Lang;
use App\Helpers\Security;

$aicLocale = Lang::locale();
$aicT = static fn(string $so, string $en, string $ar): string => $aicLocale === 'so' ? $so : ($aicLocale === 'ar' ? $ar : $en);
$aicChips = $aicLocale === 'so'
    ? ['0% Ribo ma dhabbaa?', 'Sideen u codsadaa?', 'Waa maxay Murabaxo?', 'Xafiiskiina xagee ku yaal?']
    : ($aicLocale === 'ar'
        ? ['هل التمويل خالٍ من الربا؟', 'كيف أتقدم بطلب؟', 'ما هي المرابحة؟', 'أين يقع مكتبكم؟']
        : ['Is it 0% Riba?', 'How can I apply?', 'What is Murabaha?', 'Where is your office?']);
?>
<!-- ===================== SIMDA AI ASSISTANT (floating) ===================== -->
<div class="aic" id="aicRoot" data-lang="<?= Security::e($aicLocale) ?>">
    <button type="button" class="aic-fab" id="aicFab" aria-expanded="false" aria-controls="aicPanel"
            aria-label="<?= Security::e($aicT('Kaaliyaha AI ee SIMDA', 'SIMDA AI assistant', 'مساعد سيمدا الذكي')) ?>">
        <!-- SIMDA AI robot: floats, blinks, and waves "Hi" every few seconds -->
        <svg class="aic-bot" viewBox="0 0 120 150" aria-hidden="true" focusable="false">
            <defs>
                <linearGradient id="aicRShell" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#FFFFFF"/><stop offset="0.55" stop-color="#EDEFF4"/><stop offset="1" stop-color="#C7CCD8"/>
                </linearGradient>
                <linearGradient id="aicRShellSide" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0" stop-color="#FFFFFF" stop-opacity="0"/><stop offset="0.75" stop-color="#FFFFFF" stop-opacity="0"/><stop offset="1" stop-color="#9AA3B5" stop-opacity="0.45"/>
                </linearGradient>
                <linearGradient id="aicRVisor" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#272D3B"/><stop offset="1" stop-color="#0A0D14"/>
                </linearGradient>
                <linearGradient id="aicRMetal" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#FFFFFF"/><stop offset="0.5" stop-color="#C4CAD6"/><stop offset="1" stop-color="#7E8796"/>
                </linearGradient>
            </defs>
            <!-- antennas -->
            <g class="aicR-ant" stroke="#AEB5C2" stroke-width="2.2" stroke-linecap="round">
                <line x1="36" y1="26" x2="31" y2="9"/><line x1="60" y1="20" x2="60" y2="5"/><line x1="84" y1="26" x2="89" y2="9"/>
            </g>
            <g class="aicR-tips">
                <circle class="aicR-glow" cx="31" cy="8" r="7"/><circle class="aicR-glow" cx="60" cy="4" r="7"/><circle class="aicR-glow" cx="89" cy="8" r="7"/>
                <circle cx="31" cy="8" r="4.2" fill="url(#aicRMetal)"/><circle cx="60" cy="4" r="4.2" fill="url(#aicRMetal)"/><circle cx="89" cy="8" r="4.2" fill="url(#aicRMetal)"/>
            </g>
            <!-- ear pods -->
            <rect x="13" y="37" width="11" height="22" rx="4" fill="url(#aicRMetal)"/>
            <rect x="96" y="37" width="11" height="22" rx="4" fill="url(#aicRMetal)"/>
            <!-- head + visor + face -->
            <rect x="20" y="18" width="80" height="54" rx="27" fill="url(#aicRShell)"/>
            <rect x="20" y="18" width="80" height="54" rx="27" fill="url(#aicRShellSide)"/>
            <path d="M34 25 Q60 15 86 25" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none" opacity="0.9"/>
            <rect x="28" y="30" width="64" height="31" rx="15.5" fill="url(#aicRVisor)"/>
            <path d="M34 35 Q45 31 58 32" stroke="#FFFFFF" stroke-width="1.6" stroke-linecap="round" fill="none" opacity="0.18"/>
            <g class="aicR-face">
                <g class="aicR-eyes">
                    <circle cx="46" cy="44.5" r="6.4" fill="none" stroke="#8FE3FF" stroke-width="3"/>
                    <circle cx="74" cy="44.5" r="6.4" fill="none" stroke="#8FE3FF" stroke-width="3"/>
                </g>
                <path d="M53 52 Q60 57.5 67 52" fill="none" stroke="#8FE3FF" stroke-width="2.6" stroke-linecap="round"/>
            </g>
            <!-- neck -->
            <rect x="48" y="70" width="24" height="8" rx="3" fill="#2B303C"/>
            <!-- left arm (resting, gentle sway) -->
            <g class="aicR-armL">
                <circle cx="31" cy="87" r="5.5" fill="#2B303C"/>
                <rect x="25.5" y="85" width="11" height="28" rx="5.5" fill="url(#aicRShell)" transform="rotate(14 31 87)"/>
                <circle cx="23.5" cy="116" r="5.2" fill="url(#aicRMetal)"/>
            </g>
            <!-- body -->
            <path d="M34 83 Q34 76 42 76 H78 Q86 76 86 83 V108 Q86 139 60 141 Q34 139 34 108 Z" fill="url(#aicRShell)"/>
            <path d="M34 83 Q34 76 42 76 H78 Q86 76 86 83 V108 Q86 139 60 141 Q34 139 34 108 Z" fill="url(#aicRShellSide)"/>
            <path d="M36.5 121 Q60 133 83.5 121 Q79 139.5 60 141 Q41 139.5 36.5 121 Z" fill="#1D2230"/>
            <ellipse cx="45" cy="92" rx="4.5" ry="9" fill="#FFFFFF" opacity="0.7"/>
            <!-- chest badge -->
            <circle cx="60" cy="102" r="17" fill="#12161F" stroke="#D6B66C" stroke-width="1.6"/>
            <text x="60" y="100" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="8" font-weight="800" fill="#FFFFFF" letter-spacing="0.4">SIMDA</text>
            <text x="60" y="111" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10.5" font-weight="900" fill="#D6B66C">AI</text>
            <!-- right arm (waves) -->
            <g class="aicR-armR">
                <circle cx="89" cy="87" r="5.5" fill="#2B303C"/>
                <rect x="83.5" y="85" width="11" height="28" rx="5.5" fill="url(#aicRShell)" transform="rotate(-14 89 87)"/>
                <circle cx="96.5" cy="116" r="5.2" fill="url(#aicRMetal)"/>
            </g>
        </svg>
        <span class="aic-shadow" aria-hidden="true"></span>
        <span class="aic-x" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </span>
    </button>
    <div class="aic-hi" aria-hidden="true"><?= Security::e($aicLocale === 'ar' ? 'مرحباً! 👋' : 'Hi! 👋') ?></div>
    <div class="aic-hint" id="aicHint" role="status">
        <?= Security::e($aicT('Su\'aal ma qabtaa? Weydii AI-ga 👋', 'Have a question? Ask our AI 👋', 'لديك سؤال؟ اسأل مساعدنا الذكي 👋')) ?>
    </div>

    <section class="aic-panel" id="aicPanel" role="dialog" aria-modal="false" aria-labelledby="aicTitle" hidden>
        <header class="aic-head">
            <span class="aic-avatar" aria-hidden="true">
                <svg viewBox="12 0 96 76" width="30" height="24">
                    <g stroke="#AEB5C2" stroke-width="3" stroke-linecap="round"><line x1="36" y1="26" x2="31" y2="9"/><line x1="60" y1="20" x2="60" y2="5"/><line x1="84" y1="26" x2="89" y2="9"/></g>
                    <circle cx="31" cy="8" r="5" fill="#E7EAF0"/><circle cx="60" cy="4" r="5" fill="#E7EAF0"/><circle cx="89" cy="8" r="5" fill="#E7EAF0"/>
                    <rect x="13" y="37" width="11" height="22" rx="4" fill="#C4CAD6"/><rect x="96" y="37" width="11" height="22" rx="4" fill="#C4CAD6"/>
                    <rect x="20" y="18" width="80" height="54" rx="27" fill="#F4F6FA"/>
                    <rect x="28" y="30" width="64" height="31" rx="15.5" fill="#12161F"/>
                    <circle cx="46" cy="44.5" r="6.4" fill="none" stroke="#8FE3FF" stroke-width="3.4"/><circle cx="74" cy="44.5" r="6.4" fill="none" stroke="#8FE3FF" stroke-width="3.4"/>
                    <path d="M53 52 Q60 57.5 67 52" fill="none" stroke="#8FE3FF" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <i class="aic-online"></i>
            </span>
            <div class="aic-head-text">
                <strong id="aicTitle"><?= Security::e($aicT('Kaaliyaha AI ee SIMDA', 'SIMDA AI Assistant', 'مساعد سيمدا الذكي')) ?></strong>
                <span><?= Security::e($aicT('Khadka ku jira · SO · EN · AR', 'Online · SO · EN · AR', 'متصل الآن · SO · EN · AR')) ?></span>
            </div>
            <button type="button" class="aic-icon-btn" id="aicReset" title="<?= Security::e($aicT('Bilow wadahadal cusub', 'New chat', 'محادثة جديدة')) ?>" aria-label="<?= Security::e($aicT('Bilow wadahadal cusub', 'New chat', 'محادثة جديدة')) ?>">
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
            </button>
            <button type="button" class="aic-icon-btn" id="aicClose" aria-label="<?= Security::e($aicT('Xir', 'Close', 'إغلاق')) ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </header>

        <div class="aic-stream" id="aicStream" aria-live="polite"></div>

        <div class="aic-chips" id="aicChips">
            <?php foreach ($aicChips as $chip): ?>
                <button type="button" class="aic-chip"><?= Security::e($chip) ?></button>
            <?php endforeach; ?>
        </div>

        <form class="aic-form" id="aicForm" autocomplete="off">
            <input type="text" id="aicInput" class="aic-input" maxlength="500"
                   placeholder="<?= Security::e($aicT('Qor su\'aashaada…', 'Type your question…', 'اكتب سؤالك…')) ?>"
                   aria-label="<?= Security::e($aicT('Su\'aashaada', 'Your question', 'سؤالك')) ?>">
            <button type="submit" class="aic-send" id="aicSend" aria-label="<?= Security::e($aicT('Dir', 'Send', 'إرسال')) ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4z"/></svg>
            </button>
        </form>
    </section>
</div>

<style>
.aic { --aic-green: #123B32; --aic-green-2: #1E5A4A; --aic-gold: #D6B66C; --aic-bg: #FFFFFF; --aic-soft: #F5F2EB; --aic-ink: #1D2B26; --aic-muted: #6A756F; --aic-line: #ECE7DD;
       position: fixed; bottom: 30px; inset-inline-end: 2px; z-index: 1200; font-family: inherit; }
[data-theme="dark"] .aic { --aic-bg: #13231D; --aic-soft: #1A2E27; --aic-ink: #EDF1EE; --aic-muted: #9AA7A0; --aic-line: #2A4239; }

/* ---- Robot button ---- */
.aic-fab { position: relative; display: block; width: 72px; height: 90px; padding: 0; border: 0; background: transparent; cursor: pointer; border-radius: 24px; -webkit-tap-highlight-color: transparent; }
.aic-fab:focus-visible { outline: 3px solid var(--aic-gold); outline-offset: 2px; }
.aic-bot { position: relative; z-index: 1; display: block; width: 100%; height: 100%; overflow: visible;
           filter: drop-shadow(0 10px 14px rgba(18, 40, 60, 0.28)); animation: aicFloat 3.2s ease-in-out infinite; transition: transform 0.25s ease; }
.aic-fab:hover .aic-bot { transform: scale(1.06); }
@keyframes aicFloat { 0%, 100% { translate: 0 0; } 50% { translate: 0 -7px; } }
.aic-shadow { position: absolute; left: 50%; bottom: -3px; width: 42px; height: 7px; margin-left: -21px; border-radius: 50%;
              background: radial-gradient(ellipse, rgba(18, 40, 60, 0.32), transparent 70%); animation: aicShadow 3.2s ease-in-out infinite; }
@keyframes aicShadow { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(0.78); opacity: 0.6; } }

/* blink */
.aicR-eyes { transform-box: fill-box; transform-origin: center; animation: aicBlink 4.6s infinite; }
@keyframes aicBlink { 0%, 92%, 100% { transform: scaleY(1); } 95% { transform: scaleY(0.1); } }
/* antenna tips glow */
.aicR-glow { fill: #8FE3FF; opacity: 0; animation: aicGlow 2.4s ease-in-out infinite; }
.aicR-glow:nth-of-type(2) { animation-delay: 0.4s; } .aicR-glow:nth-of-type(3) { animation-delay: 0.8s; }
@keyframes aicGlow { 0%, 100% { opacity: 0; } 50% { opacity: 0.55; } }
/* left arm sways a little */
.aicR-armL { transform-box: view-box; transform-origin: 31px 87px; animation: aicSway 3.2s ease-in-out infinite; }
@keyframes aicSway { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(6deg); } }
/* right arm: rests, then lifts and waves "Hi" (8s loop) */
.aicR-armR { transform-box: view-box; transform-origin: 89px 87px; animation: aicWave 8s ease-in-out infinite 1.2s; }
@keyframes aicWave {
    0%, 48% { transform: rotate(0deg); }
    55% { transform: rotate(-135deg); }
    60% { transform: rotate(-108deg); }
    65% { transform: rotate(-142deg); }
    70% { transform: rotate(-108deg); }
    75% { transform: rotate(-142deg); }
    80% { transform: rotate(-125deg); }
    88%, 100% { transform: rotate(0deg); }
}
.aic-fab:hover .aicR-armR { animation: aicWaveNow 0.9s ease-in-out infinite; }
@keyframes aicWaveNow { 0%, 100% { transform: rotate(-108deg); } 50% { transform: rotate(-142deg); } }

/* "Hi!" speech bubble, in step with the wave */
.aic-hi { position: absolute; bottom: 80px; inset-inline-end: 50px; z-index: 2; padding: 7px 13px; border-radius: 16px 16px 4px 16px;
          background: var(--aic-bg); color: var(--aic-green); font-weight: 800; font-size: 0.85rem; white-space: nowrap;
          border: 1px solid var(--aic-line); box-shadow: 0 8px 20px rgba(18, 59, 50, 0.18); pointer-events: none;
          opacity: 0; transform-origin: bottom right; animation: aicHi 8s ease-in-out infinite 1.2s; }
[dir="rtl"] .aic-hi { border-radius: 16px 16px 16px 4px; transform-origin: bottom left; }
[data-theme="dark"] .aic-hi { color: var(--aic-gold); }
@keyframes aicHi { 0%, 50% { opacity: 0; transform: scale(0.6); } 55%, 82% { opacity: 1; transform: scale(1); } 88%, 100% { opacity: 0; transform: scale(0.6); } }
.aic-fab:hover ~ .aic-hi { animation: none; opacity: 1; transform: none; }

/* open state: robot stays, small close badge appears */
.aic-x { position: absolute; top: 2px; inset-inline-end: 0; z-index: 2; width: 22px; height: 22px; border-radius: 50%;
         background: var(--aic-green); color: #fff; display: none; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(0,0,0,0.25); }
.aic.is-open .aic-x { display: inline-flex; }
.aic.is-open .aic-hi { display: none; }

.aic-hint { position: absolute; bottom: 16px; inset-inline-end: 78px; white-space: nowrap; padding: 9px 14px; border-radius: 14px;
            background: var(--aic-bg); color: var(--aic-ink); font-size: 0.85rem; font-weight: 600; box-shadow: 0 10px 26px rgba(18, 59, 50, 0.18);
            border: 1px solid var(--aic-line); opacity: 0; transform: translateY(6px); pointer-events: none; transition: opacity 0.3s ease, transform 0.3s ease; }
.aic-hint.is-shown { opacity: 1; transform: none; pointer-events: auto; cursor: pointer; }
.aic.is-open .aic-hint { display: none; }

.aic-panel { position: absolute; bottom: 100px; inset-inline-end: 0; width: 370px; height: min(560px, calc(100vh - 150px));
             display: flex; flex-direction: column; overflow: hidden; border-radius: 22px; background: var(--aic-bg); color: var(--aic-ink);
             border: 1px solid var(--aic-line); box-shadow: 0 24px 60px rgba(6, 26, 21, 0.28); transform-origin: bottom right; animation: aicIn 0.22s ease-out; }
[dir="rtl"] .aic-panel { transform-origin: bottom left; }
.aic-panel[hidden] { display: none; }
@keyframes aicIn { from { opacity: 0; transform: translateY(10px) scale(0.96); } to { opacity: 1; transform: none; } }

.aic-head { display: flex; align-items: center; gap: 10px; padding: 14px 12px 14px 16px; color: #fff;
            background: linear-gradient(135deg, var(--aic-green), var(--aic-green-2)); }
.aic-avatar { position: relative; flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; background: rgba(255, 255, 255, 0.12); color: var(--aic-gold);
              display: inline-flex; align-items: center; justify-content: center; box-shadow: inset 0 0 0 1px rgba(214, 182, 108, 0.5); }
.aic-online { position: absolute; bottom: 0; inset-inline-end: 0; width: 10px; height: 10px; border-radius: 50%; background: #3DDC84; box-shadow: 0 0 0 2px var(--aic-green); }
.aic-head-text { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.25; }
.aic-head-text strong { font-size: 0.98rem; }
.aic-head-text span { font-size: 0.74rem; opacity: 0.8; }
.aic-icon-btn { width: 34px; height: 34px; border-radius: 50%; border: 0; cursor: pointer; background: rgba(255,255,255,0.1); color: #fff;
                display: inline-flex; align-items: center; justify-content: center; transition: background 0.15s ease; }
.aic-icon-btn:hover { background: rgba(255,255,255,0.22); }

.aic-stream { flex: 1; overflow-y: auto; padding: 16px 14px 8px; display: flex; flex-direction: column; gap: 10px; background: var(--aic-soft); overscroll-behavior: contain; }
.aic-msg { max-width: 86%; display: flex; flex-direction: column; gap: 6px; }
.aic-msg.bot { align-self: flex-start; }
.aic-msg.user { align-self: flex-end; align-items: flex-end; }
.aic-bubble { padding: 10px 13px; border-radius: 16px; font-size: 0.88rem; line-height: 1.55; white-space: pre-line; word-wrap: break-word; }
.aic-msg.bot .aic-bubble { background: var(--aic-bg); color: var(--aic-ink); border: 1px solid var(--aic-line); border-end-start-radius: 4px; }
.aic-msg.user .aic-bubble { background: var(--aic-green); color: #fff; border-end-end-radius: 4px; }
.aic-action { align-self: flex-start; display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 999px; background: #25D366; color: #fff !important;
              font-size: 0.8rem; font-weight: 700; text-decoration: none; }
.aic-action:hover { filter: brightness(0.95); }
.aic-typing { align-self: flex-start; display: inline-flex; gap: 4px; padding: 12px 14px; border-radius: 16px; background: var(--aic-bg); border: 1px solid var(--aic-line); }
.aic-typing i { width: 7px; height: 7px; border-radius: 50%; background: var(--aic-muted); animation: aicDot 1.2s infinite ease-in-out; }
.aic-typing i:nth-child(2) { animation-delay: 0.15s; } .aic-typing i:nth-child(3) { animation-delay: 0.3s; }
@keyframes aicDot { 0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; } 40% { transform: scale(1); opacity: 1; } }

.aic-chips { display: flex; gap: 6px; overflow-x: auto; padding: 8px 12px; background: var(--aic-soft); border-top: 1px solid var(--aic-line); scrollbar-width: none; }
.aic-chips::-webkit-scrollbar { display: none; }
.aic-chip { flex-shrink: 0; padding: 6px 11px; border-radius: 999px; border: 1px solid var(--aic-line); background: var(--aic-bg); color: var(--aic-ink);
            font-size: 0.78rem; font-weight: 600; cursor: pointer; white-space: nowrap; }
.aic-chip:hover { border-color: var(--aic-gold); color: var(--aic-green); }
[data-theme="dark"] .aic-chip:hover { color: var(--aic-gold); }

.aic-form { display: flex; gap: 8px; padding: 10px 12px 12px; background: var(--aic-bg); border-top: 1px solid var(--aic-line); }
.aic-input { flex: 1; min-width: 0; height: 44px; padding: 0 14px; border-radius: 999px; border: 1px solid var(--aic-line); background: var(--aic-soft);
             color: var(--aic-ink); font: inherit; font-size: 16px; outline: none; }
.aic-input:focus { border-color: var(--aic-gold); box-shadow: 0 0 0 3px rgba(214, 182, 108, 0.2); }
.aic-send { width: 44px; height: 44px; flex-shrink: 0; border-radius: 50%; border: 0; cursor: pointer; background: var(--aic-green); color: var(--aic-gold);
            display: inline-flex; align-items: center; justify-content: center; }
.aic-send:disabled { opacity: 0.55; cursor: default; }
[dir="rtl"] .aic-send svg { transform: scaleX(-1); }

@media (max-width: 680px) {
    .aic { bottom: 24px; inset-inline-end: 2px; }
    .aic-fab { width: 58px; height: 72px; }
    .aic-hi { bottom: 64px; inset-inline-end: 40px; font-size: 0.78rem; padding: 5px 10px; }
    .aic-hint { inset-inline-end: 62px; bottom: 12px; font-size: 0.78rem; }
    .aic.is-open { inset: 0; }
    .aic.is-open .aic-fab { display: none; }
    .aic-panel { position: fixed; inset: auto 0 0 0; width: 100%; height: 88vh; height: 88dvh; border-radius: 22px 22px 0 0; animation: aicSheet 0.25s ease-out; }
    .aic-form { padding-bottom: calc(12px + env(safe-area-inset-bottom)); }
}
@keyframes aicSheet { from { transform: translateY(100%); } to { transform: none; } }
@media (prefers-reduced-motion: reduce) { .aic-bot, .aic-shadow, .aicR-eyes, .aicR-glow, .aicR-armL, .aicR-armR, .aic-panel { animation: none; } .aic-hi { animation: none; opacity: 0; } }
@media print { .aic { display: none; } }
</style>

<script>
(function () {
    var root = document.getElementById('aicRoot');
    if (!root) return;
    var fab = document.getElementById('aicFab'), panel = document.getElementById('aicPanel'), stream = document.getElementById('aicStream');
    var form = document.getElementById('aicForm'), input = document.getElementById('aicInput'), send = document.getElementById('aicSend');
    var chips = document.getElementById('aicChips'), hint = document.getElementById('aicHint');
    var lang = root.getAttribute('data-lang') || 'so';
    var KEY = 'simda_aic_history_v1';
    var welcome = <?= json_encode($aicT(
        "Asc! 👋 Waxaan ahay Kaaliyaha AI ee SIMDA. I weydii wax kasta oo ku saabsan maalgelinta xalaasha ah (0% Ribo), Murabaxada, shuruudaha iyo sida loo codsado. Af-Soomaali, English ama Carabi waan kuugu jawaabayaa.",
        "Hello! 👋 I'm the SIMDA AI Assistant. Ask me anything about our 0% Riba Islamic financing, Murabaha, requirements or how to apply — in English, Somali or Arabic.",
        "السلام عليكم! 👋 أنا مساعد سيمدا الذكي. اسألني عن التمويل الإسلامي بدون ربا، والمرابحة، والشروط، وطريقة التقديم — بالعربية أو الصومالية أو الإنجليزية."
    ), JSON_UNESCAPED_UNICODE) ?>;
    var errorMsg = <?= json_encode($aicT(
        'Waan ka xumahay, xiriirku wuu go\'ay. Fadlan mar kale isku day.',
        'Sorry, the connection failed. Please try again.',
        'عذراً، تعذر الاتصال. يرجى المحاولة مرة أخرى.'
    ), JSON_UNESCAPED_UNICODE) ?>;
    var history = [], busy = false;

    function save() { try { sessionStorage.setItem(KEY, JSON.stringify(history.slice(-40))); } catch (e) {} }
    function load() { try { return JSON.parse(sessionStorage.getItem(KEY) || '[]') || []; } catch (e) { return []; } }
    function scroll() { stream.scrollTop = stream.scrollHeight; }

    function render(m) {
        var wrap = document.createElement('div');
        wrap.className = 'aic-msg ' + m.who;
        var b = document.createElement('div');
        b.className = 'aic-bubble';
        b.dir = 'auto';
        b.textContent = m.text;
        wrap.appendChild(b);
        if (m.action && m.action.url && /^https:\/\//.test(m.action.url)) {
            var a = document.createElement('a');
            a.className = 'aic-action';
            a.href = m.action.url; a.target = '_blank'; a.rel = 'noopener noreferrer';
            a.textContent = m.action.label || 'WhatsApp';
            wrap.appendChild(a);
        }
        stream.appendChild(wrap);
    }
    function add(who, text, action) {
        var m = { who: who, text: text, action: action || null };
        history.push(m); save(); render(m); scroll();
    }
    function setChips(list) {
        if (!list || !list.length) return;
        chips.innerHTML = '';
        list.slice(0, 4).forEach(function (t) {
            var c = document.createElement('button');
            c.type = 'button'; c.className = 'aic-chip'; c.textContent = t;
            chips.appendChild(c);
        });
    }
    function reset() {
        history = []; save(); stream.innerHTML = '';
        add('bot', welcome);
    }

    function open() {
        panel.hidden = false;
        root.classList.add('is-open');
        fab.setAttribute('aria-expanded', 'true');
        hint.classList.remove('is-shown');
        try { sessionStorage.setItem('simda_aic_seen', '1'); } catch (e) {}
        if (!stream.children.length) {
            history = load();
            if (history.length) { history.forEach(render); } else { add('bot', welcome); }
        }
        scroll();
        if (window.matchMedia('(min-width: 681px)').matches) input.focus();
    }
    function close() {
        panel.hidden = true;
        root.classList.remove('is-open');
        fab.setAttribute('aria-expanded', 'false');
        fab.focus({ preventScroll: true });
    }

    function ask(text) {
        text = (text || '').trim();
        if (!text || busy) return;
        busy = true; send.disabled = true;
        add('user', text);
        input.value = '';
        var typing = document.createElement('div');
        typing.className = 'aic-typing';
        typing.innerHTML = '<i></i><i></i><i></i>';
        stream.appendChild(typing); scroll();
        fetch('?route=faq_chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=UTF-8' },
            body: JSON.stringify({ message: text, lang: lang })
        })
        .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function (d) {
            typing.remove();
            add('bot', (d && d.reply) ? d.reply : errorMsg, d ? d.action_btn : null);
            if (d && d.suggestions) setChips(d.suggestions);
        })
        .catch(function () { typing.remove(); add('bot', errorMsg); })
        .then(function () { busy = false; send.disabled = false; });
    }

    fab.addEventListener('click', function () { panel.hidden ? open() : close(); });
    hint.addEventListener('click', open);
    document.getElementById('aicClose').addEventListener('click', close);
    document.getElementById('aicReset').addEventListener('click', reset);
    form.addEventListener('submit', function (e) { e.preventDefault(); ask(input.value); });
    chips.addEventListener('click', function (e) {
        var c = e.target.closest('.aic-chip');
        if (c) ask(c.textContent);
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) close(); });

    // A small "Ask our AI" bubble once per visit, a few seconds after the page loads.
    var seen = false;
    try { seen = sessionStorage.getItem('simda_aic_seen') === '1'; } catch (e) {}
    if (!seen) {
        setTimeout(function () { if (panel.hidden) hint.classList.add('is-shown'); }, 2500);
        setTimeout(function () { hint.classList.remove('is-shown'); }, 11000);
    }
})();
</script>
