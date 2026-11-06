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
        'portal' => 'SIMDA MANAGEMENT PORTAL',
        'portal_desc' => 'Centralized, ethical, and 100% Zero-Riba Sharia-compliant financial management system.',
        'badge_sharia' => '100% Sharia Compliant • Zero Riba',
        'trust_p1_title' => 'Zero-Riba Guaranteed',
        'trust_p1_sub' => 'Audited Murabaha financing and transparent ethical terms.',
        'trust_p2_title' => 'Instant Mobile Disbursements',
        'trust_p2_sub' => 'Seamless automated processing with EVC Plus & Zaad.',
        'trust_p3_title' => 'Bank-Grade Security',
        'trust_p3_sub' => '256-bit encryption and complete immutable audit trails.',
        'stat_clients' => 'Active Clients',
        'stat_sharia' => 'Islamic Audit',
        'signin' => 'Sign in',
        'signin_sub' => 'Enter your staff or management credentials to access the portal',
        'identifier' => 'User Name or Email',
        'password' => 'Password',
        'remember' => 'Remember me',
        'show' => 'SHOW',
        'hide' => 'HIDE',
        'btn_signin' => 'Sign In to Portal →',
        'or' => 'Or',
        'back_home' => 'Back to Public Website',
        'need_help' => 'Need assistance?',
        'contact_office' => 'Contact SIMDA Head Office',
    ],
    'so' => [
        'welcome' => 'SOO DHAWOOW',
        'portal' => 'BORTAALKA MAAMULKA SIMDA',
        'portal_desc' => 'Nidaam dhexe, xalaal ah, oo 100% waafaqsan Shareecada Islaamka iyo 0% Ribo.',
        'badge_sharia' => '100% Xalaal & Shareeco • 0% Ribo',
        'trust_p1_title' => '0% Ribo oo La Hubiyay',
        'trust_p1_sub' => 'Maalgelin Muraabaxo oo hufan oo caddaalad ku dhisan.',
        'trust_p2_title' => 'Lacag Bixin Degdeg ah',
        'trust_p2_sub' => 'Isku-xirka tooska ah ee Hormuud EVC Plus iyo Telesom Zaad.',
        'trust_p3_title' => 'Amni & Hanti-Dhowr Sare',
        'trust_p3_sub' => 'Xog dhowran, xafidan, oo diiwaan buuxa leh mar kasta.',
        'stat_clients' => 'Macaamiil',
        'stat_sharia' => 'Audit Xalaal ah',
        'signin' => 'Gal Nidaamka',
        'signin_sub' => 'Geli magacaaga ama email-kaaga si aad u gasho bortaalka',
        'identifier' => 'Magaca ama Email-ka',
        'password' => 'Furaha Sirta ah',
        'remember' => 'I xasuuso',
        'show' => 'MUUG',
        'hide' => 'QARI',
        'btn_signin' => 'Gal Bortaalka →',
        'or' => 'Ama',
        'back_home' => 'Ku laabo Bogga Guud',
        'need_help' => 'Ma u baahan tahay caawimaad?',
        'contact_office' => 'La xiriir Xafiiska Guud ee SIMDA',
    ],
    'ar' => [
        'welcome' => 'مرحباً بكم',
        'portal' => 'بوابة إدارة سيمدا للتمويل الأصغر',
        'portal_desc' => 'منظومة مالية إسلامية مركزية وأخلاقية متوافقة 100% مع الشريعة وبدون ربا (0%).',
        'badge_sharia' => 'متوافق 100% مع الشريعة • بدون ربا',
        'trust_p1_title' => 'تمويل إسلامي بدون ربا (0%)',
        'trust_p1_sub' => 'عقود مرابحة شرعية معتمدة وتدقيق أخلاقي كامل.',
        'trust_p2_title' => 'صرف فوري عبر المحافظ',
        'trust_p2_sub' => 'ربط آلي مباشر مع EVC Plus و Telesom Zaad.',
        'trust_p3_title' => 'أمان مصرفي عالي المستوى',
        'trust_p3_sub' => 'تشفير 256-bit وسجل تدقيق غير قابل للتعديل.',
        'stat_clients' => 'عميل نشط',
        'stat_sharia' => 'تدقيق شرعي',
        'signin' => 'تسجيل الدخول',
        'signin_sub' => 'أدخل بيانات الموظف أو الإدارة للوصول إلى لوحة التحكم',
        'identifier' => 'اسم المستخدم أو البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'remember' => 'تذكرني',
        'show' => 'إظهار',
        'hide' => 'إخفاء',
        'btn_signin' => 'الدخول إلى البوابة ←',
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
    <title><?= Security::e(Lang::get('nav_login')) ?> — SIMDA Microfinance</title>
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
            padding: 32px 20px;
            background: radial-gradient(circle at 20% 15%, #184D3E 0%, #0F352B 48%, #071C17 100%);
            font-family: 'Plus Jakarta Sans', var(--font-body);
            position: relative;
            overflow: hidden;
        }

        /* Ambient subtle light glows behind card */
        .simda-login-stage::before {
            content: "";
            position: absolute;
            top: -100px;
            left: -100px;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(214, 182, 108, 0.14) 0%, transparent 68%);
            pointer-events: none;
        }

        .simda-login-stage::after {
            content: "";
            position: absolute;
            bottom: -120px;
            right: -100px;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(38, 126, 98, 0.22) 0%, transparent 70%);
            pointer-events: none;
        }

        .simda-login-card {
            position: relative;
            width: 100%;
            max-width: 980px;
            min-height: 580px;
            background: #FFFFFF;
            border-radius: 24px;
            box-shadow: 0 30px 80px -15px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(214, 182, 108, 0.22);
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            overflow: hidden;
            z-index: 10;
        }

        /* LEFT SIDE: Deep Emerald Luxury Branding & Trust */
        .simda-login-left {
            position: relative;
            padding: 44px 42px 38px;
            background: linear-gradient(155deg, #0A2E24 0%, #124336 45%, #082119 100%);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid rgba(214, 182, 108, 0.16);
            overflow: hidden;
        }

        [dir="rtl"] .simda-login-left {
            border-right: none;
            border-left: 1px solid rgba(214, 182, 108, 0.16);
        }

        /* Subtle Geometric Background Watermark */
        .simda-login-left::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 18% 18%, rgba(214, 182, 108, 0.12) 0%, transparent 48%),
                        radial-gradient(circle at 82% 82%, rgba(52, 163, 131, 0.18) 0%, transparent 52%);
            pointer-events: none;
        }

        .simda-login-top-brand {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        [dir="rtl"] .simda-login-top-brand {
            align-items: flex-start;
        }

        .simda-login-logo-link {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            text-decoration: none;
            gap: 2px;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .simda-login-logo-link:hover {
            transform: scale(1.02);
            opacity: 0.95;
        }

        .simda-login-logo-img {
            height: 44px;
            width: auto;
            display: block;
            filter: brightness(0) invert(1) !important;
        }

        .simda-login-logo-tagline {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 1px;
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .simda-login-logo-tagline span:first-child {
            color: #FFFFFF;
        }

        .simda-login-logo-tagline span:last-child {
            color: #D6B66C;
        }

        .simda-login-sharia-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(214, 182, 108, 0.12);
            border: 1px solid rgba(214, 182, 108, 0.35);
            padding: 4px 12px;
            border-radius: 999px;
            color: #E6CE93;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        /* Hero Text & Features */
        .simda-login-mid-hero {
            position: relative;
            z-index: 3;
            margin: 24px 0 20px;
        }

        .simda-login-welcome-title {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #FFFFFF;
            line-height: 1.1;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        .simda-login-welcome-sub {
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #D6B66C;
            margin: 0 0 10px;
        }

        .simda-login-welcome-desc {
            font-size: 0.82rem;
            line-height: 1.6;
            color: rgba(244, 245, 240, 0.82);
            margin: 0 0 20px;
            max-width: 440px;
        }

        /* 3 Luxury Feature Items */
        .simda-login-features {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .simda-login-feature-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .simda-login-feature-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(214, 182, 108, 0.3);
        }

        .simda-feature-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: rgba(214, 182, 108, 0.16);
            border: 1px solid rgba(214, 182, 108, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #D6B66C;
            flex-shrink: 0;
        }

        .simda-feature-text {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .simda-feature-text strong {
            font-size: 0.84rem;
            color: #FFFFFF;
            font-weight: 700;
        }

        .simda-feature-text span {
            font-size: 0.72rem;
            color: rgba(244, 245, 240, 0.72);
            line-height: 1.35;
        }

        /* Bottom Stats Ribbon */
        .simda-login-stats-ribbon {
            position: relative;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            margin-top: 10px;
        }

        .simda-stat-col {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .simda-stat-col strong {
            font-size: 1.15rem;
            font-weight: 800;
            color: #D6B66C;
            letter-spacing: -0.01em;
        }

        .simda-stat-col span {
            font-size: 0.68rem;
            font-weight: 700;
            color: rgba(244, 245, 240, 0.72);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .simda-stat-divider {
            width: 1px;
            height: 28px;
            background: rgba(255, 255, 255, 0.14);
        }

        /* RIGHT SIDE: Clean Modern Form Panel */
        .simda-login-right {
            position: relative;
            z-index: 3;
            padding: 48px 46px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #FFFFFF;
        }

        .simda-signin-title {
            font-size: 1.95rem;
            font-weight: 800;
            color: #123B32;
            margin: 0 0 6px;
            letter-spacing: -0.02em;
        }

        .simda-signin-sub {
            font-size: 0.82rem;
            color: #61736B;
            margin: 0 0 24px;
            line-height: 1.5;
        }

        .simda-input-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #F4F6F4;
            border: 1.5px solid transparent;
            border-radius: 12px;
            padding: 13px 16px;
            margin-bottom: 15px;
            transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
        }

        .simda-input-pill:focus-within {
            background: #FFFFFF;
            border-color: #123B32;
            box-shadow: 0 0 0 3px rgba(18, 59, 50, 0.12);
        }

        .simda-input-icon {
            color: #123B32;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .simda-input-pill input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 0.9rem;
            color: #162621;
            font-family: inherit;
            outline: none;
            min-width: 0;
            font-weight: 500;
        }

        .simda-input-pill input::placeholder {
            color: #8C9993;
        }

        .simda-pw-toggle {
            background: none;
            border: none;
            color: #123B32;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            transition: background 0.15s ease;
        }

        .simda-pw-toggle:hover {
            background: rgba(18, 59, 50, 0.08);
        }

        .simda-login-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 4px 0 22px;
            font-size: 0.8rem;
            color: #4A5751;
        }

        .simda-btn-signin-main {
            width: 100%;
            background: linear-gradient(135deg, #123B32 0%, #0A2620 100%);
            color: #FFFFFF;
            border: 1px solid rgba(214, 182, 108, 0.3);
            border-radius: 12px;
            padding: 14px 22px;
            font-size: 0.94rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(18, 59, 50, 0.28);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .simda-btn-signin-main:hover {
            background: linear-gradient(135deg, #184D41 0%, #0D332B 100%);
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(18, 59, 50, 0.35);
        }

        .simda-btn-signin-main:active {
            transform: translateY(0);
        }

        .simda-or-divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 18px 0;
            color: #8C9993;
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .simda-or-divider::before,
        .simda-or-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #E5E9E6;
        }

        .simda-btn-signin-alt {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #FFFFFF;
            color: #123B32;
            border: 1.5px solid #D5DBD8;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }

        .simda-btn-signin-alt:hover {
            background: #F4F6F4;
            border-color: #123B32;
        }

        .simda-login-bottom-note {
            margin-top: 24px;
            text-align: center;
            font-size: 0.8rem;
            color: #61736B;
        }

        .simda-login-bottom-note a {
            color: #123B32;
            font-weight: 800;
            text-decoration: underline;
        }

        @media (max-width: 860px) {
            .simda-login-card {
                grid-template-columns: 1fr;
                max-width: 480px;
                min-height: auto;
            }
            .simda-login-left {
                padding: 34px 28px 30px;
                border-right: none;
                border-bottom: 1px solid rgba(214, 182, 108, 0.2);
            }
            .simda-login-welcome-title {
                font-size: 1.8rem;
            }
            .simda-login-features {
                display: none;
            }
            .simda-login-right {
                padding: 34px 28px 38px;
            }
        }
    </style>
</head>
<body>

<div class="simda-login-stage">
    <div class="simda-login-card animate-in">
        <!-- Left Circular & Deep Emerald Brand Experience -->
        <div class="simda-login-left">
            <div class="simda-login-top-brand">
                <a href="?route=home" class="simda-login-logo-link" title="SIMDA Microfinance">
                    <img src="assets/images/simda-logo.png" alt="SIMDA Logo" class="simda-login-logo-img">
                    <span class="simda-login-logo-tagline">
                        <span>SOMALI ISLAMIC MICROFINANCE</span>
                        <span>DEVELOPMENT ASSOCIATION</span>
                    </span>
                </a>
                <div class="simda-login-sharia-badge">
                    <?= Icons::svg('check-circle', 14) ?>
                    <span><?= Security::e($lt['badge_sharia']) ?></span>
                </div>
            </div>

            <div class="simda-login-mid-hero">
                <h1 class="simda-login-welcome-title"><?= Security::e($lt['welcome']) ?></h1>
                <div class="simda-login-welcome-sub"><?= Security::e($lt['portal']) ?></div>
                <p class="simda-login-welcome-desc">
                    <?= Security::e($lt['portal_desc']) ?>
                </p>

                <!-- 3 Feature Pillars -->
                <div class="simda-login-features">
                    <div class="simda-login-feature-item">
                        <div class="simda-feature-icon-box">
                            <?= Icons::svg('shield', 18) ?>
                        </div>
                        <div class="simda-feature-text">
                            <strong><?= Security::e($lt['trust_p1_title']) ?></strong>
                            <span><?= Security::e($lt['trust_p1_sub']) ?></span>
                        </div>
                    </div>

                    <div class="simda-login-feature-item">
                        <div class="simda-feature-icon-box">
                            <?= Icons::svg('send', 18) ?>
                        </div>
                        <div class="simda-feature-text">
                            <strong><?= Security::e($lt['trust_p2_title']) ?></strong>
                            <span><?= Security::e($lt['trust_p2_sub']) ?></span>
                        </div>
                    </div>

                    <div class="simda-login-feature-item">
                        <div class="simda-feature-icon-box">
                            <?= Icons::svg('lock', 18) ?>
                        </div>
                        <div class="simda-feature-text">
                            <strong><?= Security::e($lt['trust_p3_title']) ?></strong>
                            <span><?= Security::e($lt['trust_p3_sub']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Stats Ribbon -->
            <div class="simda-login-stats-ribbon">
                <div class="simda-stat-col">
                    <strong>15K+</strong>
                    <span><?= Security::e($lt['stat_clients']) ?></span>
                </div>
                <div class="simda-stat-divider"></div>
                <div class="simda-stat-col">
                    <strong>0%</strong>
                    <span>Ribo (Riba)</span>
                </div>
                <div class="simda-stat-divider"></div>
                <div class="simda-stat-col">
                    <strong>100%</strong>
                    <span><?= Security::e($lt['stat_sharia']) ?></span>
                </div>
            </div>
        </div>

        <!-- Right Modern Clean Sign-In Form Panel -->
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
                    <label style="display:inline-flex;align-items:center;gap:7px;cursor:pointer;font-weight:600;">
                        <input type="checkbox" name="remember" style="accent-color:#123B32;width:15px;height:15px;">
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
                    <?= Icons::svg('home', 16) ?>
                    <span><?= Security::e($lt['back_home']) ?></span>
                </a>
            </form>

            <div class="simda-login-bottom-note">
                <?= Security::e($lt['need_help']) ?> <a href="?route=contact"><?= Security::e($lt['contact_office']) ?></a>
            </div>
        </div>
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
                    btn.textContent = '<?= Security::e($lt['hide']) ?>';
                } else {
                    pw.type = 'password';
                    btn.textContent = '<?= Security::e($lt['show']) ?>';
                }
            });
        }
    })();
</script>
<script src="assets/js/simda.js?v=<?= Security::e($assetVer) ?>"></script>
</body>
</html>
