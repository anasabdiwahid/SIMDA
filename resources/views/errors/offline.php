<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), 1);

$i18n = [
    'so' => [
        'page_title' => 'Offline — Qad La\'aan | SIMDA Microfinance',
        'badge' => 'Xaaladda: Qad La\'aan',
        'title' => 'Qadka Internet-ka ayaa Kaa Go\'an!',
        'sub' => 'Computer-kaaga hadda wax xiriir internet ah ma laha. Fadlan hubi Wi-Fi-gaaga, router-ka ama fiilada qadka si aad u sii wadato.',
        'btn_retry' => 'Dib u Tijaabi Qadka ↻',
        'btn_retrying' => 'Waa la hubinayaa...',
        'btn_home' => 'Ku Noqo Bogga Hore →',
        'btn_dashboard' => 'Gal Dashboard-ka →',
        'online_banner' => 'Qadkii Internet-ka wuu soo noqday! Bogga ayaa dib loo furayaa...',
        'offline_still' => 'Wali ma jiro wax xiriir ah. Fadlan hubi qadkaaga oo mar kale tijaabi.',
        'help_lead' => 'Haddii ciladdu sii socoto, fadlan la xariir taageerada SIMDA.',
    ],
    'en' => [
        'page_title' => 'Offline — No Internet Connection | SIMDA Microfinance',
        'badge' => 'Status: Offline',
        'title' => 'You Are Currently Offline!',
        'sub' => 'No internet connection detected on this device. Please check your Wi-Fi, router, or network cable to continue.',
        'btn_retry' => 'Check Connection ↻',
        'btn_retrying' => 'Checking...',
        'btn_home' => 'Back to Home →',
        'btn_dashboard' => 'Open Dashboard →',
        'online_banner' => 'Internet connection restored! Reloading page...',
        'offline_still' => 'Still offline. Please check your network and try again.',
        'help_lead' => 'If the problem persists, please contact SIMDA technical support.',
    ],
    'ar' => [
        'page_title' => 'غير متصل بالإنترنت | SIMDA Microfinance',
        'badge' => 'الحالة: غير متصل',
        'title' => 'أنت غير متصل بالإنترنت حالياً!',
        'sub' => 'لم يتم العثور على اتصال بالإنترنت في جهازك. يرجى التحقق من شبكة Wi-Fi أو كابل الشبكة للمتابعة.',
        'btn_retry' => 'إعادة المحاولة ↻',
        'btn_retrying' => 'جاري الفحص...',
        'btn_home' => 'العودة للرئيسية ←',
        'btn_dashboard' => 'لوحة التحكم ←',
        'online_banner' => 'تمت استعادة الاتصال بالإنترنت! جاري إعادة التحميل...',
        'offline_still' => 'لا يوجد اتصال حتى الآن. يرجى التحقق وإعادة المحاولة.',
        'help_lead' => 'إذا استمرت المشكلة، يرجى التواصل مع الدعم الفني لـ SIMDA.',
    ],
];

$t = $i18n[$locale] ?? $i18n['en'];
$isLoggedIn = AuthMiddleware::check();
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" dir="<?= $locale === 'ar' ? 'rtl' : 'ltr' ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e($t['page_title']) ?></title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="stylesheet" href="assets/css/simda.css?v=<?= Security::e($assetVer) ?>">
    <script>
        (function() {
            var theme = localStorage.getItem('simda_theme_pref') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <style>
        :root {
            --stage-bg: radial-gradient(circle at 25% 20%, #1F5B4C 0%, #123B32 52%, #0A221C 100%);
            --card-bg: #FFFFFF;
            --card-text: #162621;
            --card-sub: #5E6E67;
            --badge-bg: rgba(201, 75, 75, 0.12);
            --badge-border: rgba(201, 75, 75, 0.35);
            --badge-text: #C94B4B;
            --badge-dot: #E05252;
            --gif-border: rgba(18, 59, 50, 0.12);
            --gif-shadow: 0 16px 36px rgba(18, 59, 50, 0.14);
            --btn-alt-bg: #FFFFFF;
            --btn-alt-border: #26332E;
            --btn-alt-text: #162621;
        }

        [data-theme="dark"] {
            --stage-bg: radial-gradient(circle at 25% 20%, #142822 0%, #0D1A16 55%, #050B09 100%);
            --card-bg: #14221D;
            --card-text: #FFFFFF;
            --card-sub: #A2B0A9;
            --badge-bg: rgba(201, 75, 75, 0.2);
            --badge-border: rgba(201, 75, 75, 0.45);
            --badge-text: #FFA3A3;
            --badge-dot: #FF6B6B;
            --gif-border: rgba(214, 182, 108, 0.25);
            --gif-shadow: 0 16px 36px rgba(0, 0, 0, 0.45);
            --btn-alt-bg: #1A2C24;
            --btn-alt-border: rgba(255, 255, 255, 0.25);
            --btn-alt-text: #FFFFFF;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: var(--stage-bg);
            font-family: 'Plus Jakarta Sans', var(--font-body), sans-serif;
            color: var(--card-text);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 18px;
            box-sizing: border-box;
        }

        /* Top Header */
        .simda-top-header {
            width: 100%;
            max-width: 860px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            padding: 0 6px;
            box-sizing: border-box;
        }

        .simda-brand-link {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            text-decoration: none;
            gap: 2px;
            transition: opacity 0.2s ease;
        }

        .simda-brand-link:hover {
            opacity: 0.92;
        }

        .simda-brand-link img {
            height: 48px;
            width: auto;
            display: block;
        }

        .simda-brand-tagline {
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            line-height: 1.25;
            text-transform: uppercase;
        }

        .simda-brand-tagline span:first-child {
            color: #FFFFFF;
        }

        .simda-brand-tagline span:last-child {
            color: #D6B66C;
        }

        .simda-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Sliding Pill Theme Toggle Switch */
        .simda-theme-pill-toggle {
            position: relative;
            width: 66px;
            height: 34px;
            background: rgba(18, 42, 34, 0.72);
            border: 1.5px solid rgba(214, 182, 108, 0.32);
            border-radius: 999px;
            padding: 2px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            outline: none;
            transition: background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
            user-select: none;
        }

        [data-theme="light"] .simda-theme-pill-toggle {
            background: #E5ECE8;
            border-color: rgba(18, 59, 50, 0.22);
        }

        [data-theme="dark"] .simda-theme-pill-toggle {
            background: rgba(12, 26, 21, 0.85);
            border-color: rgba(214, 182, 108, 0.4);
        }

        .simda-track-icon {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8A9C93;
            z-index: 1;
            pointer-events: none;
            transition: opacity 0.22s ease, color 0.22s ease;
            flex-shrink: 0;
        }

        .simda-pill-thumb {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 27px;
            height: 27px;
            border-radius: 50%;
            background: #D8B76E;
            color: #12332B;
            box-shadow: 0 0 14px rgba(216, 183, 110, 0.55), 0 2px 6px rgba(0, 0, 0, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.25s ease;
        }

        [data-theme="light"] .simda-pill-thumb {
            transform: translateX(0);
        }
        [data-theme="light"] .simda-thumb-sun {
            display: flex;
        }
        [data-theme="light"] .simda-thumb-moon {
            display: none;
        }
        [data-theme="light"] .simda-track-sun {
            opacity: 0;
        }
        [data-theme="light"] .simda-track-moon {
            opacity: 1;
            color: #586961;
        }

        [data-theme="dark"] .simda-pill-thumb {
            transform: translateX(33px);
        }
        [data-theme="dark"] .simda-thumb-sun {
            display: none;
        }
        [data-theme="dark"] .simda-thumb-moon {
            display: flex;
        }
        [data-theme="dark"] .simda-track-sun {
            opacity: 1;
            color: #8E9F97;
        }
        [data-theme="dark"] .simda-track-moon {
            opacity: 0;
        }

        .simda-thumb-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        /* Offline Main Card */
        .simda-offline-card {
            width: 100%;
            max-width: 860px;
            background: var(--card-bg);
            border-radius: 28px;
            box-shadow: 0 32px 70px rgba(0, 0, 0, 0.42);
            padding: 48px 44px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(214, 182, 108, 0.2);
            animation: simdaPopUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes simdaPopUp {
            from { transform: translateY(18px) scale(0.97); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        /* Caveman Animation Wrapper */
        .simda-gif-container {
            position: relative;
            margin-bottom: 24px;
            border-radius: 20px;
            padding: 8px;
            background: linear-gradient(135deg, rgba(18, 59, 50, 0.08), rgba(214, 182, 108, 0.16));
            border: 2px solid var(--gif-border);
            box-shadow: var(--gif-shadow);
            max-width: 380px;
            width: 100%;
            overflow: hidden;
        }

        .simda-err-gif {
            width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: cover;
            border-radius: 14px;
            display: block;
        }

        /* Pulsing Offline Badge */
        .simda-offline-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--badge-bg);
            border: 1.5px solid var(--badge-border);
            color: var(--badge-text);
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 999px;
            margin-bottom: 14px;
        }

        .simda-offline-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--badge-dot);
            box-shadow: 0 0 10px var(--badge-dot);
            animation: simdaPulse 1.5s ease-in-out infinite;
        }

        @keyframes simdaPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }

        .simda-offline-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--card-text);
            margin: 0 0 10px;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .simda-offline-sub {
            font-size: 0.95rem;
            line-height: 1.65;
            color: var(--card-sub);
            max-width: 580px;
            margin: 0 0 30px;
        }

        /* Connection feedback message */
        .simda-conn-alert {
            display: none;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 22px;
            max-width: 480px;
            width: 100%;
            box-sizing: border-box;
            animation: simdaPopIn 0.2s ease-out;
        }

        .simda-conn-alert.is-online {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.14);
            border: 1px solid #10B981;
            color: #059669;
        }

        .simda-conn-alert.is-error {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(201, 75, 75, 0.14);
            border: 1px solid #C94B4B;
            color: #C94B4B;
        }

        /* Buttons */
        .simda-err-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 28px;
            width: 100%;
        }

        .simda-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #123B32;
            color: #FFFFFF;
            border: none;
            border-radius: 12px;
            padding: 13px 26px;
            font-size: 0.92rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(18, 59, 50, 0.28);
            transition: background-color 0.18s ease, transform 0.18s ease;
        }

        .simda-btn-primary:hover {
            background: #0D2B24;
            transform: translateY(-2px);
        }

        [data-theme="dark"] .simda-btn-primary {
            background: linear-gradient(135deg, #1A4D41 0%, #123B32 100%);
            border: 1px solid rgba(214, 182, 108, 0.35);
        }

        [data-theme="dark"] .simda-btn-primary:hover {
            background: #205C4E;
        }

        .simda-btn-alt {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--btn-alt-bg);
            color: var(--btn-alt-text);
            border: 1.5px solid var(--btn-alt-border);
            border-radius: 12px;
            padding: 12px 24px;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .simda-btn-alt:hover {
            border-color: #D6B66C;
            transform: translateY(-2px);
        }

        .simda-spinner-icon {
            display: inline-block;
            animation: simdaSpin 0.75s linear infinite;
        }

        @keyframes simdaSpin {
            to { transform: rotate(360deg); }
        }

        .simda-err-footer-note {
            font-size: 0.82rem;
            color: var(--card-sub);
            border-top: 1px solid rgba(18, 59, 50, 0.08);
            padding-top: 20px;
            width: 100%;
        }

        [data-theme="dark"] .simda-err-footer-note {
            border-top-color: rgba(255, 255, 255, 0.08);
        }

        @media (max-width: 680px) {
            .simda-offline-card {
                padding: 34px 22px;
            }
            .simda-offline-title {
                font-size: 1.7rem;
            }
            .simda-top-header {
                flex-direction: column;
                gap: 14px;
                align-items: center;
            }
        }
    </style>
</head>
<body>

    <!-- Header Actions (Brand Logo, Theme Pill Toggle, Language Selector) -->
    <header class="simda-top-header">
        <a href="?route=home" class="simda-brand-link" title="SIMDA">
            <img src="assets/images/simda-logo-white.png" alt="SIMDA Logo">
            <span class="simda-brand-tagline">
                <span>SOMALI ISLAMIC MICROFINANCE</span>
                <span>DEVELOPMENT ASSOCIATION</span>
            </span>
        </a>

        <div class="simda-header-actions">
            <!-- Sliding Pill Theme Toggle Switch -->
            <button type="button" class="simda-theme-pill-toggle" id="themePillToggle" onclick="toggleSimdaTheme()" title="Toggle Dark / Light Mode" aria-label="Toggle Dark / Light Mode">
                <span class="simda-track-icon simda-track-sun" aria-hidden="true">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3.5" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                </span>
                <span class="simda-track-icon simda-track-moon" aria-hidden="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </span>
                <span class="simda-pill-thumb">
                    <span class="simda-thumb-icon simda-thumb-sun" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3.5" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                    </span>
                    <span class="simda-thumb-icon simda-thumb-moon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </span>
                </span>
            </button>

            <!-- Language Dropdown -->
            <?php
            $langList = [
                'so' => ['label' => 'Af-Soomaali', 'code' => 'SO', 'flag' => 'assets/images/flags/flag-so.jpg'],
                'en' => ['label' => 'English (US)', 'code' => 'EN', 'flag' => 'assets/images/flags/flag-us.jpg'],
                'ar' => ['label' => 'العربية', 'code' => 'AR', 'flag' => 'assets/images/flags/flag-ar.jpg'],
            ];
            $activeLang = $langList[$locale] ?? $langList['en'];
            ?>
            <div class="simda-lang-dropdown">
                <button type="button" class="simda-lang-trigger" aria-haspopup="true" aria-expanded="false" onclick="event.stopPropagation(); const p = this.closest('.simda-lang-dropdown'); const open = p.classList.toggle('is-open'); this.setAttribute('aria-expanded', open);" title="Change Language / Dooro Luqadda">
                    <img src="<?= Security::e($activeLang['flag']) ?>" alt="<?= Security::e($activeLang['label']) ?>" class="simda-lang-flag-circle">
                    <span class="simda-lang-code"><?= Security::e($activeLang['code']) ?></span>
                    <svg class="simda-lang-caret" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 1 5 5 9 1"></polyline>
                    </svg>
                </button>
                <div class="simda-lang-menu" role="menu" style="top:calc(100% + 8px);bottom:auto;">
                    <?php foreach ($langList as $code => $info): ?>
                        <a href="?route=offline&lang=<?= $code ?>" class="simda-lang-item <?= $locale === $code ? 'is-active' : '' ?>" role="menuitem">
                            <img src="<?= Security::e($info['flag']) ?>" alt="<?= Security::e($info['label']) ?>" class="simda-lang-flag-circle">
                            <span class="simda-lang-name"><?= Security::e($info['label']) ?></span>
                            <?php if ($locale === $code): ?>
                                <svg class="check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Offline Card -->
    <main class="simda-offline-card">
        <!-- Caveman Looking GIF from Pinterest -->
        <div class="simda-gif-container">
            <img src="assets/images/illustrations/caveman-looking.gif" alt="Looking for Connection — Offline Animation" class="simda-err-gif">
        </div>

        <!-- Pulsing Offline Badge -->
        <div class="simda-offline-badge">
            <span class="simda-offline-dot"></span>
            <span><?= Security::e($t['badge']) ?></span>
        </div>

        <h1 class="simda-offline-title"><?= Security::e($t['title']) ?></h1>
        <p class="simda-offline-sub"><?= Security::e($t['sub']) ?></p>

        <!-- Live Connection Feedback Toast -->
        <div id="connAlert" class="simda-conn-alert"></div>

        <!-- Action Buttons -->
        <div class="simda-err-actions">
            <button type="button" class="simda-btn-primary" id="btnCheckConn" onclick="checkNetworkConnection()">
                <span id="btnRetryText"><?= Security::e($t['btn_retry']) ?></span>
            </button>

            <?php if ($isLoggedIn): ?>
                <a href="?route=dashboard" class="simda-btn-alt">
                    <?= Security::e($t['btn_dashboard']) ?>
                </a>
            <?php else: ?>
                <a href="?route=home" class="simda-btn-alt">
                    <?= Security::e($t['btn_home']) ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="simda-err-footer-note">
            <?= Security::e($t['help_lead']) ?>
        </div>
    </main>

    <script>
        function toggleSimdaTheme() {
            var current = document.documentElement.getAttribute('data-theme') || 'light';
            var next = (current === 'dark') ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('simda_theme_pref', next);
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            document.querySelectorAll('.simda-lang-dropdown.is-open').forEach(function(d) {
                if (!d.contains(e.target)) {
                    d.classList.remove('is-open');
                }
            });
        });

        // Test network connection directly
        function checkNetworkConnection() {
            var btn = document.getElementById('btnCheckConn');
            var btnText = document.getElementById('btnRetryText');
            var alertBox = document.getElementById('connAlert');

            btn.disabled = true;
            btnText.innerHTML = '<span class="simda-spinner-icon">↻</span> <?= Security::e($t['btn_retrying']) ?>';
            alertBox.className = 'simda-conn-alert';
            alertBox.style.display = 'none';

            // Ping local static asset or public endpoint
            fetch('assets/images/simda-icon.png?_ping=' + Date.now(), { method: 'HEAD', cache: 'no-store' })
                .then(function(res) {
                    btn.disabled = false;
                    btnText.textContent = '<?= Security::e($t['btn_retry']) ?>';
                    alertBox.className = 'simda-conn-alert is-online';
                    alertBox.textContent = '<?= Security::e($t['online_banner']) ?>';
                    alertBox.style.display = 'flex';
                    setTimeout(function() {
                        var returnUrl = sessionStorage.getItem('simda_last_url') || '?route=home';
                        window.location.href = returnUrl;
                    }, 1200);
                })
                .catch(function(err) {
                    btn.disabled = false;
                    btnText.textContent = '<?= Security::e($t['btn_retry']) ?>';
                    alertBox.className = 'simda-conn-alert is-error';
                    alertBox.textContent = '<?= Security::e($t['offline_still']) ?>';
                    alertBox.style.display = 'flex';
                });
        }

        // Auto-detect when connection is restored
        window.addEventListener('online', function() {
            var alertBox = document.getElementById('connAlert');
            if (alertBox) {
                alertBox.className = 'simda-conn-alert is-online';
                alertBox.textContent = '<?= Security::e($t['online_banner']) ?>';
                alertBox.style.display = 'flex';
            }
            setTimeout(function() {
                var returnUrl = sessionStorage.getItem('simda_last_url') || '?route=home';
                window.location.href = returnUrl;
            }, 1000);
        });
    </script>
</body>
</html>

