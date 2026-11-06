<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$locale = Lang::locale();
$csrf = Security::csrfToken();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), (int) @filemtime(BASE_PATH . '/public/assets/js/simda.js'), 1);

$loginI18n = [
    'en' => [
        'welcome' => 'WELCOME',
        'portal' => 'SIMDA MICROFINANCE PORTAL',
        'portal_desc' => 'Centralized, ethical, and 100% Zero-Riba Sharia-compliant financial management platform.',
        'signin' => 'Sign in',
        'signin_sub' => 'Enter your SIMDA staff or client credentials to access the portal',
        'identifier' => 'User Name or Email',
        'password' => 'Password',
        'remember' => 'Remember me',
        'show' => 'SHOW',
        'hide' => 'HIDE',
        'btn_signin' => 'Sign in',
        'or' => 'Or',
        'back_home' => 'Back to Public Website',
        'need_help' => 'Need assistance?',
        'contact_office' => 'Contact SIMDA Head Office',
    ],
    'so' => [
        'welcome' => 'SOO DHAWOOW',
        'portal' => 'BORTAALKA SIMDA MICROFINANCE',
        'portal_desc' => 'Nidaam dhexe, xalaal ah, oo 100% waafaqsan Shareecada Islaamka iyo 0% Ribo.',
        'signin' => 'Gal Nidaamka',
        'signin_sub' => 'Gali magacaaga ama email-kaaga si aad u gasho maamulka',
        'identifier' => 'Magaca ama Email-ka',
        'password' => 'Furaha Sirta ah',
        'remember' => 'I xasuuso',
        'show' => 'MUUG',
        'hide' => 'QARI',
        'btn_signin' => 'Gal Nidaamka',
        'or' => 'Ama',
        'back_home' => 'Ku laabo Bogga Guud',
        'need_help' => 'Ma u baahan tahay caawimaad?',
        'contact_office' => 'La xiriir Xafiiska Guud ee SIMDA',
    ],
    'ar' => [
        'welcome' => 'مرحباً بكم',
        'portal' => 'بوابة سيمدا للتمويل الأصغر',
        'portal_desc' => 'منظومة مالية إسلامية مركزية وأخلاقية متوافقة 100% مع الشريعة وبدون ربا (0%).',
        'signin' => 'تسجيل الدخول',
        'signin_sub' => 'أدخل بيانات الموظف أو العميل للوصول إلى لوحة التحكم',
        'identifier' => 'اسم المستخدم أو البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'remember' => 'تذكرني',
        'show' => 'إظهار',
        'hide' => 'إخفاء',
        'btn_signin' => 'تسجيل الدخول',
        'or' => 'أو',
        'back_home' => 'العودة للموقع العام',
        'need_help' => 'هل تحتاج إلى مساعدة؟',
        'contact_office' => 'اتصل بالمقر الرئيسي لسيمدا',
    ],
];
$lt = $loginI18n[$locale] ?? $loginI18n['en'];
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" dir="<?= $locale === 'ar' ? 'rtl' : 'ltr' ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e(Lang::get('nav_login')) ?> — SIMDA</title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="assets/css/simda.css?v=<?= Security::e($assetVer) ?>">
    <style>
        .simda-login-stage {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 18px;
            background: radial-gradient(circle at 25% 20%, #1F5B4C 0%, #123B32 52%, #0A221C 100%);
            font-family: 'Plus Jakarta Sans', var(--font-body);
        }

        .simda-login-card {
            position: relative;
            width: 100%;
            max-width: 940px;
            min-height: 550px;
            background: #FFFFFF;
            border-radius: 26px;
            box-shadow: 0 32px 70px rgba(0, 0, 0, 0.38);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            overflow: hidden;
        }

        /* Left Circular Welcome Panel & 3D Spheres */
        .simda-login-left {
            position: relative;
            padding: 56px 44px 56px 54px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #FFFFFF;
            z-index: 2;
            user-select: none;
        }

        /* Giant curved circle backdrop forming the right circular arc */
        .simda-login-circle-main {
            position: absolute;
            top: -120px;
            left: -130px;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle at 28% 28%, #28725F 0%, #144338 58%, #0C2A23 100%);
            box-shadow: 12px 12px 38px rgba(12, 42, 35, 0.25);
            z-index: -1;
        }

        /* Center-bottom 3D sphere overlapping the main circle edge */
        .simda-login-sphere-mid {
            position: absolute;
            bottom: 34px;
            right: 26px;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 28%, #348A73 0%, #16493C 62%, #0A241E 100%);
            box-shadow: 0 20px 38px rgba(7, 25, 20, 0.42);
            z-index: 1;
        }

        /* Bottom-left 3D sphere */
        .simda-login-sphere-bl {
            position: absolute;
            bottom: -58px;
            left: -48px;
            width: 215px;
            height: 215px;
            border-radius: 50%;
            background: radial-gradient(circle at 32% 28%, #2E7E69 0%, #133F34 65%, #09201A 100%);
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.35);
            z-index: 1;
        }

        /* Bottom-right decorative 3D sphere on white card corner */
        .simda-login-sphere-br {
            position: absolute;
            bottom: -56px;
            right: -46px;
            width: 148px;
            height: 148px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, #2E7E69 0%, #123B32 70%, #0A221C 100%);
            box-shadow: -6px -6px 24px rgba(18, 59, 50, 0.18);
            z-index: 1;
            pointer-events: none;
        }

        .simda-login-welcome-content {
            position: relative;
            z-index: 3;
            max-width: 340px;
            margin-bottom: 60px;
        }

        .simda-login-logo-pill {
            display: inline-flex;
            align-items: center;
            background: transparent !important;
            padding: 0 !important;
            margin-bottom: 26px;
            box-shadow: none !important;
            text-decoration: none;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .simda-login-logo-pill:hover {
            transform: scale(1.04);
            opacity: 0.92;
        }

        .simda-login-logo-pill img {
            height: 52px;
            width: auto;
            display: block;
            filter: brightness(0) invert(1) !important;
        }

        .simda-login-welcome-title {
            font-size: 2.55rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #FFFFFF;
            line-height: 1.08;
            margin: 0 0 8px;
            text-transform: uppercase;
        }

        .simda-login-welcome-sub {
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #D6B66C;
            margin: 0 0 16px;
        }

        .simda-login-welcome-desc {
            font-size: 0.82rem;
            line-height: 1.65;
            color: rgba(244, 245, 240, 0.84);
            margin: 0;
        }

        /* Right White Sign-In Form Side */
        .simda-login-right {
            position: relative;
            z-index: 3;
            padding: 48px 52px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: transparent;
        }

        .simda-signin-title {
            font-size: 2rem;
            font-weight: 800;
            color: #162621;
            margin: 0 0 6px;
            letter-spacing: -0.02em;
        }

        .simda-signin-sub {
            font-size: 0.8rem;
            color: #6B7872;
            margin: 0 0 24px;
        }

        .simda-input-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #F2F4F3;
            border: 1.5px solid transparent;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 14px;
            transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
        }

        .simda-input-pill:focus-within {
            background: #FFFFFF;
            border-color: #123B32;
            box-shadow: 0 0 0 3px rgba(18, 59, 50, 0.1);
        }

        .simda-input-icon {
            color: #26332E;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .simda-input-pill input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 0.88rem;
            color: #162621;
            font-family: inherit;
            outline: none;
            min-width: 0;
        }

        .simda-input-pill input::placeholder {
            color: #87938C;
        }

        .simda-pw-toggle {
            background: none;
            border: none;
            color: #123B32;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            cursor: pointer;
            padding: 2px 4px;
        }

        .simda-login-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 2px 0 20px;
            font-size: 0.78rem;
            color: #4A5751;
        }

        .simda-btn-signin-main {
            width: 100%;
            background: #123B32;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 13px 20px;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(18, 59, 50, 0.24);
            transition: background-color 0.18s ease, transform 0.18s ease;
        }

        .simda-btn-signin-main:hover {
            background: #0D2B24;
            transform: translateY(-1px);
        }

        .simda-or-divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 16px 0;
            color: #87938C;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .simda-or-divider::before,
        .simda-or-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #E2E7E4;
        }

        .simda-btn-signin-alt {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #FFFFFF;
            color: #162621;
            border: 1.5px solid #26332E;
            border-radius: 10px;
            padding: 11px 20px;
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.18s ease;
        }

        .simda-btn-signin-alt:hover {
            background: #F6F7F3;
        }

        .simda-login-bottom-note {
            margin-top: 22px;
            text-align: center;
            font-size: 0.78rem;
            color: #5A6660;
        }

        .simda-login-bottom-note a {
            color: #123B32;
            font-weight: 700;
        }

        @media (max-width: 820px) {
            .simda-login-card {
                grid-template-columns: 1fr;
                max-width: 460px;
            }
            .simda-login-left {
                padding: 36px 32px 28px;
                background: linear-gradient(135deg, #194D40 0%, #123B32 100%);
            }
            .simda-login-circle-main,
            .simda-login-sphere-mid,
            .simda-login-sphere-bl {
                display: none;
            }
            .simda-login-welcome-content {
                margin-bottom: 0;
            }
            .simda-login-right {
                padding: 34px 30px 40px;
            }
        }
    </style>
</head>
<body>

<div class="simda-login-stage">
    <div class="simda-login-card animate-in">
        <!-- Left Circular Welcome Panel with 3D Spheres -->
        <div class="simda-login-left">
            <div class="simda-login-circle-main"></div>
            <div class="simda-login-sphere-mid"></div>
            <div class="simda-login-sphere-bl"></div>

            <div class="simda-login-welcome-content">
                <a href="?route=home" class="simda-login-logo-pill">
                    <img src="assets/images/simda-logo.png" alt="SIMDA Logo">
                </a>
                <h1 class="simda-login-welcome-title"><?= Security::e($lt['welcome']) ?></h1>
                <div class="simda-login-welcome-sub"><?= Security::e($lt['portal']) ?></div>
                <p class="simda-login-welcome-desc">
                    <?= Security::e(Lang::get('tagline')) ?> — <?= Security::e($lt['portal_desc']) ?>
                </p>
            </div>
        </div>

        <!-- Right White Sign-In Form Panel -->
        <div class="simda-login-right">
            <h2 class="simda-signin-title"><?= Security::e($lt['signin']) ?></h2>
            <p class="simda-signin-sub"><?= Security::e($lt['signin_sub']) ?></p>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger" style="padding:10px 14px;font-size:0.8rem;margin-bottom:14px;">
                    <?= Icons::svg('alert-triangle', 16) ?>
                    <span><?= Security::e($_SESSION['flash_error']) ?></span>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_warning'])): ?>
                <div class="alert alert-warning" style="padding:10px 14px;font-size:0.8rem;margin-bottom:14px;">
                    <?= Icons::svg('info', 16) ?>
                    <span><?= Security::e($_SESSION['flash_warning']) ?></span>
                </div>
                <?php unset($_SESSION['flash_warning']); ?>
            <?php endif; ?>

            <form method="POST" action="?route=login_submit">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

                <div class="simda-input-pill">
                    <span class="simda-input-icon"><?= Icons::svg('user', 18) ?></span>
                    <input type="text" id="identifier" name="identifier" required autofocus placeholder="<?= Security::e($lt['identifier']) ?>">
                </div>

                <div class="simda-input-pill">
                    <span class="simda-input-icon"><?= Icons::svg('lock', 18) ?></span>
                    <input type="password" id="password" name="password" required placeholder="<?= Security::e($lt['password']) ?>">
                    <button type="button" class="simda-pw-toggle" id="togglePwBtn"><?= Security::e($lt['show']) ?></button>
                </div>

                <div class="simda-login-meta-row">
                    <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" name="remember" style="accent-color:#123B32;">
                        <span><?= Security::e($lt['remember']) ?></span>
                    </label>
                    <?php
                    $loginLanguagesList = [
                        'so' => ['label' => 'Af-Soomaali', 'code' => 'SO', 'flag' => 'assets/images/flags/flag-so.jpg'],
                        'en' => ['label' => 'English (US)', 'code' => 'EN', 'flag' => 'assets/images/flags/flag-us.jpg'],
                        'ar' => ['label' => 'العربية', 'code' => 'AR', 'flag' => 'assets/images/flags/flag-ar.jpg'],
                    ];
                    $loginActiveLang = $loginLanguagesList[$locale] ?? $loginLanguagesList['en'];
                    ?>
                    <div class="simda-lang-dropdown">
                        <button type="button" class="simda-lang-trigger" aria-haspopup="true" aria-expanded="false" onclick="event.stopPropagation(); const p = this.closest('.simda-lang-dropdown'); const open = p.classList.toggle('is-open'); this.setAttribute('aria-expanded', open);" title="Change Language / Dooro Luqadda">
                            <img src="<?= Security::e($loginActiveLang['flag']) ?>" alt="<?= Security::e($loginActiveLang['label']) ?>" class="simda-lang-flag-circle">
                            <span class="simda-lang-code"><?= Security::e($loginActiveLang['code']) ?></span>
                            <svg class="simda-lang-caret" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="1 1 5 5 9 1"></polyline>
                            </svg>
                        </button>
                        <div class="simda-lang-menu" role="menu" style="bottom:calc(100% + 8px);top:auto;">
                            <?php foreach ($loginLanguagesList as $code => $info): ?>
                                <a href="?route=login&lang=<?= $code ?>" class="simda-lang-item <?= $locale === $code ? 'is-active' : '' ?>" role="menuitem">
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

                <button type="submit" class="simda-btn-signin-main"><?= Security::e($lt['btn_signin']) ?></button>

                <div class="simda-or-divider"><?= Security::e($lt['or']) ?></div>

                <a href="?route=home" class="simda-btn-signin-alt">
                    <span><?= Security::e($lt['back_home']) ?></span>
                </a>
            </form>

            <div class="simda-login-bottom-note">
                <?= Security::e($lt['need_help']) ?> <a href="?route=contact"><?= Security::e($lt['contact_office']) ?></a>
            </div>
        </div>

        <!-- Bottom-right decorative 3D sphere -->
        <div class="simda-login-sphere-br"></div>
    </div>
</div>

<script>
    (function () {
        var btn = document.getElementById('togglePwBtn');
        var pw = document.getElementById('password');
        if (btn && pw) {
            btn.addEventListener('click', function () {
                if (pw.type === 'password') {
                    pw.type = 'text';
                    btn.textContent = 'HIDE';
                } else {
                    pw.type = 'password';
                    btn.textContent = 'SHOW';
                }
            });
        }
    })();
</script>
<script src="assets/js/simda.js?v=<?= Security::e($assetVer) ?>"></script>
</body>
</html>
