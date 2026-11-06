<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

http_response_code(404);

$locale = Lang::locale();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), 1);

$i18n = [
    'so' => [
        'page_title' => '404 — Boggan Lama Helin | SIMDA Microfinance',
        'badge' => 'Khalad 404',
        'title' => 'Boggii Aad Raadinaysay Lama Helin!',
        'sub' => 'Waxay u muuqataa in bogga aad codsatay uusan jirin, magiciisa la beddelay, ama aad soo gashay ciwaan aan nidaamka ka tirsanayn.',
        'btn_home' => 'Ku Noqo Bogga Hore →',
        'btn_back' => '← Dib u Noqo',
        'btn_dashboard' => 'Gal Dashboard-ka →',
        'btn_contact' => 'Xafiiska SIMDA la xariir',
        'help_lead' => 'Ma u baahan tahay caawinaad?',
    ],
    'en' => [
        'page_title' => '404 — Page Not Found | SIMDA Microfinance',
        'badge' => 'Error 404',
        'title' => 'Oops! Page Not Found',
        'sub' => 'The page you are looking for might have been removed, had its address changed, or is temporarily unavailable.',
        'btn_home' => 'Back to Home →',
        'btn_back' => '← Go Back',
        'btn_dashboard' => 'Open Dashboard →',
        'btn_contact' => 'Contact SIMDA Office',
        'help_lead' => 'Need help finding something?',
    ],
    'ar' => [
        'page_title' => '404 — الصفحة غير موجودة | SIMDA Microfinance',
        'badge' => 'خطأ 404',
        'title' => 'عذراً! الصفحة غير موجودة',
        'sub' => 'الصفحة التي تحاول الوصول إليها قد تكون حُذفت، أو تم تغيير رابطها، أو أنها غير متاحة حالياً.',
        'btn_home' => 'العودة للرئيسية ←',
        'btn_back' => 'رجوع →',
        'btn_dashboard' => 'لوحة التحكم ←',
        'btn_contact' => 'تواصل مع إدارة SIMDA',
        'help_lead' => 'هل تحتاج إلى مساعدة؟',
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
            --badge-bg: rgba(214, 182, 108, 0.16);
            --badge-border: #D6B66C;
            --badge-text: #967425;
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
            --badge-bg: rgba(214, 182, 108, 0.18);
            --badge-border: #D6B66C;
            --badge-text: #D6B66C;
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

        /* Top Action Bar (Header Controls) */
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

        /* 404 Main Card */
        .simda-err-card {
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

        /* 404 Floating Badge */
        .simda-err-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--badge-bg);
            border: 1.5px solid var(--badge-border);
            color: var(--badge-text);
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 999px;
            margin-bottom: 14px;
        }

        .simda-err-badge-digit {
            font-size: 1.1rem;
            font-weight: 900;
            color: #D6B66C;
        }

        .simda-err-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--card-text);
            margin: 0 0 10px;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .simda-err-sub {
            font-size: 0.95rem;
            line-height: 1.65;
            color: var(--card-sub);
            max-width: 580px;
            margin: 0 0 32px;
        }

        /* Button Action Row */
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

        .simda-err-footer-note a {
            color: #2872A1;
            font-weight: 700;
            text-decoration: none;
        }

        [data-theme="dark"] .simda-err-footer-note a {
            color: #D6B66C;
        }

        @media (max-width: 680px) {
            .simda-err-card {
                padding: 34px 22px;
            }
            .simda-err-title {
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
                        <a href="?route=404&lang=<?= $code ?>" class="simda-lang-item <?= $locale === $code ? 'is-active' : '' ?>" role="menuitem">
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

    <!-- Main 404 Error Content Box -->
    <main class="simda-err-card">
        <!-- Caveman Animation -->
        <div class="simda-gif-container">
            <img src="assets/images/illustrations/caveman-looking.gif" alt="Looking Around — 404 Animation" class="simda-err-gif">
        </div>

        <!-- 404 Error Badge -->
        <div class="simda-err-badge">
            <span class="simda-err-badge-digit">404</span>
            <span><?= Security::e($t['badge']) ?></span>
        </div>

        <h1 class="simda-err-title"><?= Security::e($t['title']) ?></h1>
        <p class="simda-err-sub"><?= Security::e($t['sub']) ?></p>

        <!-- Navigation Buttons -->
        <div class="simda-err-actions">
            <?php if ($isLoggedIn): ?>
                <a href="?route=dashboard" class="simda-btn-primary">
                    <?= Security::e($t['btn_dashboard']) ?>
                </a>
            <?php else: ?>
                <a href="?route=home" class="simda-btn-primary">
                    <?= Security::e($t['btn_home']) ?>
                </a>
            <?php endif; ?>

            <button type="button" class="simda-btn-alt" onclick="history.length > 1 ? history.back() : window.location.href='?route=home';">
                <?= Security::e($t['btn_back']) ?>
            </button>
        </div>

        <div class="simda-err-footer-note">
            <?= Security::e($t['help_lead']) ?> <a href="?route=contact"><?= Security::e($t['btn_contact']) ?></a> · <a href="?route=faq">FAQ</a>
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
    </script>
</body>
</html>
