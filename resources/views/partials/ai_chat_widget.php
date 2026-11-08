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
        <span class="aic-fab-ring" aria-hidden="true"></span>
        <svg class="aic-ico-ai" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3l1.9 4.6L18.5 9.5l-4.6 1.9L12 16l-1.9-4.6L5.5 9.5l4.6-1.9z"/>
            <path d="M19 15l.8 1.9 1.9.8-1.9.8L19 20.4l-.8-1.9-1.9-.8 1.9-.8z"/>
            <path d="M5 16.5l.6 1.4 1.4.6-1.4.6-.6 1.4-.6-1.4-1.4-.6 1.4-.6z"/>
        </svg>
        <svg class="aic-ico-close" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
        <span class="aic-fab-tag" aria-hidden="true">AI</span>
    </button>
    <div class="aic-hint" id="aicHint" role="status">
        <?= Security::e($aicT('Su\'aal ma qabtaa? Weydii AI-ga 👋', 'Have a question? Ask our AI 👋', 'لديك سؤال؟ اسأل مساعدنا الذكي 👋')) ?>
    </div>

    <section class="aic-panel" id="aicPanel" role="dialog" aria-modal="false" aria-labelledby="aicTitle" hidden>
        <header class="aic-head">
            <span class="aic-avatar" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 4.6L18.5 9.5l-4.6 1.9L12 16l-1.9-4.6L5.5 9.5l4.6-1.9z"/><path d="M19 15l.8 1.9 1.9.8-1.9.8L19 20.4l-.8-1.9-1.9-.8 1.9-.8z"/></svg>
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
       position: fixed; bottom: 22px; inset-inline-end: 22px; z-index: 1200; font-family: inherit; }
[data-theme="dark"] .aic { --aic-bg: #13231D; --aic-soft: #1A2E27; --aic-ink: #EDF1EE; --aic-muted: #9AA7A0; --aic-line: #2A4239; }

.aic-fab { position: relative; width: 62px; height: 62px; border-radius: 50%; border: 0; cursor: pointer; color: var(--aic-gold);
           background: radial-gradient(circle at 30% 25%, #2B6E5A, var(--aic-green) 70%);
           box-shadow: 0 12px 28px rgba(18, 59, 50, 0.38), inset 0 0 0 2px rgba(214, 182, 108, 0.45);
           display: inline-flex; align-items: center; justify-content: center; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.aic-fab:hover { transform: translateY(-3px) scale(1.04); box-shadow: 0 16px 34px rgba(18, 59, 50, 0.45), inset 0 0 0 2px rgba(214, 182, 108, 0.7); }
.aic-fab:focus-visible { outline: 3px solid var(--aic-gold); outline-offset: 3px; }
.aic-fab-ring { position: absolute; inset: 0; border-radius: 50%; border: 2px solid var(--aic-gold); opacity: 0; animation: aicPulse 2.6s ease-out infinite; pointer-events: none; }
@keyframes aicPulse { 0% { transform: scale(1); opacity: 0.55; } 80%, 100% { transform: scale(1.55); opacity: 0; } }
.aic-ico-ai { animation: aicTwinkle 3.2s ease-in-out infinite; }
@keyframes aicTwinkle { 0%, 100% { transform: rotate(0) scale(1); } 50% { transform: rotate(12deg) scale(1.08); } }
.aic-fab-tag { position: absolute; top: -3px; inset-inline-end: -3px; min-width: 24px; height: 20px; padding: 0 5px; border-radius: 999px;
               background: var(--aic-gold); color: var(--aic-green); font-size: 0.66rem; font-weight: 900; letter-spacing: 0.04em;
               display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
.aic-ico-close { display: none; }
.aic.is-open .aic-ico-ai, .aic.is-open .aic-fab-tag, .aic.is-open .aic-fab-ring { display: none; }
.aic.is-open .aic-ico-close { display: block; }

.aic-hint { position: absolute; bottom: 14px; inset-inline-end: 74px; white-space: nowrap; padding: 9px 14px; border-radius: 14px;
            background: var(--aic-bg); color: var(--aic-ink); font-size: 0.85rem; font-weight: 600; box-shadow: 0 10px 26px rgba(18, 59, 50, 0.18);
            border: 1px solid var(--aic-line); opacity: 0; transform: translateY(6px); pointer-events: none; transition: opacity 0.3s ease, transform 0.3s ease; }
.aic-hint.is-shown { opacity: 1; transform: none; pointer-events: auto; cursor: pointer; }
.aic.is-open .aic-hint { display: none; }

.aic-panel { position: absolute; bottom: 76px; inset-inline-end: 0; width: 370px; height: min(560px, calc(100vh - 120px));
             display: flex; flex-direction: column; overflow: hidden; border-radius: 22px; background: var(--aic-bg); color: var(--aic-ink);
             border: 1px solid var(--aic-line); box-shadow: 0 24px 60px rgba(6, 26, 21, 0.28); transform-origin: bottom right; animation: aicIn 0.22s ease-out; }
[dir="rtl"] .aic-panel { transform-origin: bottom left; }
.aic-panel[hidden] { display: none; }
@keyframes aicIn { from { opacity: 0; transform: translateY(10px) scale(0.96); } to { opacity: 1; transform: none; } }

.aic-head { display: flex; align-items: center; gap: 10px; padding: 14px 12px 14px 16px; color: #fff;
            background: linear-gradient(135deg, var(--aic-green), var(--aic-green-2)); }
.aic-avatar { position: relative; flex-shrink: 0; width: 38px; height: 38px; border-radius: 50%; background: rgba(214, 182, 108, 0.18); color: var(--aic-gold);
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
    .aic { bottom: 16px; inset-inline-end: 16px; }
    .aic-fab { width: 56px; height: 56px; }
    .aic-hint { inset-inline-end: 66px; bottom: 10px; font-size: 0.8rem; }
    .aic.is-open { inset: 0; }
    .aic.is-open .aic-fab { display: none; }
    .aic-panel { position: fixed; inset: auto 0 0 0; width: 100%; height: 88vh; height: 88dvh; border-radius: 22px 22px 0 0; animation: aicSheet 0.25s ease-out; }
    .aic-form { padding-bottom: calc(12px + env(safe-area-inset-bottom)); }
}
@keyframes aicSheet { from { transform: translateY(100%); } to { transform: none; } }
@media (prefers-reduced-motion: reduce) { .aic-fab-ring, .aic-ico-ai, .aic-panel { animation: none; } }
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
