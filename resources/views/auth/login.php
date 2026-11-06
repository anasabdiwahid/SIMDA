<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$locale = Lang::locale();
$csrf = Security::csrfToken();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), (int) @filemtime(BASE_PATH . '/public/assets/js/simda.js'), 1);
$preVerifiedEmail = (string) ($_SESSION['login_verified_identifier'] ?? '');
unset($_SESSION['login_verified_identifier']);
$flashError = (string) ($_SESSION['flash_error'] ?? '');
unset($_SESSION['flash_error']);

$loginI18n = [
    'en' => [
        'welcome' => 'WELCOME',
        'portal' => 'SIMDA MICROFINANCE PORTAL',
        'portal_desc' => 'Centralized, ethical, and 100% Zero-Riba Sharia-compliant financial management platform.',
        'signin' => 'Sign in',
        'signin_sub' => 'Enter your SIMDA staff or client credentials to access the portal',
        'step1_sub' => 'Enter your user name or registered email to verify your account',
        'btn_continue' => 'Continue →',
        'btn_verifying' => 'Verifying...',
        'step2_title' => 'Enter Password',
        'step2_sub' => 'Please enter your password to access your account',
        'btn_change_email' => 'Change',
        'err_not_found' => 'Please note this email is not recognized. Try again.',
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
        'forgot_password' => 'Forgot Password?',
    ],
    'so' => [
        'welcome' => 'SOO DHAWOOW',
        'portal' => 'BORTAALKA SIMDA MICROFINANCE',
        'portal_desc' => 'Nidaam dhexe, xalaal ah, oo 100% waafaqsan Shareecada Islaamka iyo 0% Ribo.',
        'signin' => 'Gal Nidaamka',
        'signin_sub' => 'Gali magacaaga ama email-kaaga si aad u gasho maamulka',
        'step1_sub' => 'Geli email-kaaga ama magacaaga si loo xaqiijiyo akoonkaaga',
        'btn_continue' => 'Sii Wad →',
        'btn_verifying' => 'Waa la hubinayaa...',
        'step2_title' => 'Geli Furaha Sirta ah',
        'step2_sub' => 'Fadlan geli furahaaga sirta ah si aad u gasho',
        'btn_change_email' => 'Beddel',
        'err_not_found' => 'Fadlan Email-kan lama aqoonsano. Isku day mar kale.',
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
        'forgot_password' => 'Ma illowday Furaha?',
    ],
    'ar' => [
        'welcome' => 'مرحباً بكم',
        'portal' => 'بوابة سيمدا للتمويل الأصغر',
        'portal_desc' => 'منظومة مالية إسلامية مركزية وأخلاقية متوافقة 100% مع الشريعة وبدون ربا (0%).',
        'signin' => 'تسجيل الدخول',
        'signin_sub' => 'أدخل بيانات الموظف أو العميل للوصول إلى لوحة التحكم',
        'step1_sub' => 'أدخل بريدك الإلكتروني أو اسم المستخدم للتحقق من حسابك',
        'btn_continue' => 'متابعة ←',
        'btn_verifying' => 'جارٍ التحقق...',
        'step2_title' => 'أدخل كلمة المرور',
        'step2_sub' => 'يرجى إدخال كلمة المرور الخاصة بك للوصول إلى حسابك',
        'btn_change_email' => 'تغيير',
        'err_not_found' => 'عذراً، هذا البريد الإلكتروني غير مسجل. يرجى المحاولة مرة أخرى.',
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
        'forgot_password' => 'هل نسيت كلمة المرور؟',
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
    <script>
        (function() {
            var theme = localStorage.getItem('simda_theme_pref') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <style>
        :root {
            --login-stage-bg: radial-gradient(circle at 25% 20%, #1F5B4C 0%, #123B32 52%, #0A221C 100%);
            --login-card-bg: #FFFFFF;
            --login-card-text: #162621;
            --login-card-sub: #6B7872;
            --login-input-bg: #F2F4F3;
            --login-input-text: #162621;
            --login-input-border: transparent;
            --login-alt-btn-bg: #FFFFFF;
            --login-alt-btn-border: #26332E;
            --login-alt-btn-text: #162621;
            --login-loader-bg: #FFFFFF;
            --login-loader-overlay: rgba(255, 255, 255, 0.92);
        }

        [data-theme="dark"] {
            --login-stage-bg: radial-gradient(circle at 25% 20%, #142822 0%, #0D1A16 55%, #050B09 100%);
            --login-card-bg: #14221D;
            --login-card-text: #FFFFFF;
            --login-card-sub: #A2B0A9;
            --login-input-bg: #1C3029;
            --login-input-text: #FFFFFF;
            --login-input-border: rgba(214, 182, 108, 0.25);
            --login-alt-btn-bg: #1A2C24;
            --login-alt-btn-border: rgba(255, 255, 255, 0.25);
            --login-alt-btn-text: #FFFFFF;
            --login-loader-bg: #14221D;
            --login-loader-overlay: rgba(13, 26, 22, 0.94);
        }

        /* Sliding Pill Theme Toggle Switch (Matches media_1793974722315_3b73df2d.png) */
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
            -webkit-tap-highlight-color: transparent;
        }

        .simda-theme-pill-toggle:focus-visible {
            box-shadow: 0 0 0 3px rgba(214, 182, 108, 0.4);
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

        /* LIGHT MODE: Thumb is on left (Sun active) */
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

        /* DARK MODE: Thumb is on right (Moon active) */
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

        .simda-login-top-bar {
            position: absolute;
            top: 24px;
            right: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10;
        }

        .simda-top-icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--login-input-bg);
            border: 1px solid rgba(0, 0, 0, 0.08);
            color: var(--login-card-text);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            transition: all 0.18s ease;
        }

        .simda-top-icon-btn:hover {
            border-color: #D6B66C;
            transform: translateY(-1px);
        }

        [data-theme="dark"] .simda-top-icon-btn {
            border-color: rgba(214, 182, 108, 0.25);
            background: #1C3029;
            color: #FFFFFF;
        }

        [data-theme="dark"] .simda-signin-title {
            color: var(--login-card-text);
        }

        [data-theme="dark"] .simda-signin-sub {
            color: var(--login-card-sub);
        }

        [data-theme="dark"] .simda-input-pill {
            background: var(--login-input-bg);
            border-color: var(--login-input-border);
        }

        [data-theme="dark"] .simda-input-pill:focus-within {
            background: #1C3029;
            border-color: #D6B66C;
            box-shadow: 0 0 0 3px rgba(214, 182, 108, 0.2);
        }

        [data-theme="dark"] .simda-input-pill input {
            color: var(--login-input-text);
        }

        [data-theme="dark"] .simda-input-icon {
            color: #A2B0A9;
        }

        [data-theme="dark"] .simda-account-chip {
            background: #1C3029;
            border-color: rgba(214, 182, 108, 0.25);
        }

        [data-theme="dark"] .simda-chip-email {
            color: #FFFFFF;
        }

        [data-theme="dark"] .simda-chip-change-btn {
            color: #D6B66C;
        }

        [data-theme="dark"] .simda-pw-toggle {
            color: #D6B66C;
        }

        [data-theme="dark"] .simda-btn-signin-main {
            background: linear-gradient(135deg, #1A4D41 0%, #123B32 100%);
            border: 1px solid rgba(214, 182, 108, 0.35);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.4);
        }

        [data-theme="dark"] .simda-btn-signin-main:hover {
            background: #205C4E;
        }

        [data-theme="dark"] .simda-or-divider {
            color: var(--login-card-sub);
        }

        [data-theme="dark"] .simda-or-divider::before,
        [data-theme="dark"] .simda-or-divider::after {
            background: rgba(255, 255, 255, 0.12);
        }

        [data-theme="dark"] .simda-btn-signin-alt {
            background: var(--login-alt-btn-bg);
            color: var(--login-alt-btn-text);
            border-color: var(--login-alt-btn-border);
        }

        [data-theme="dark"] .simda-btn-signin-alt:hover {
            background: #1E342B;
            border-color: #D6B66C;
        }

        [data-theme="dark"] .simda-login-bottom-note {
            color: var(--login-card-sub);
        }

        [data-theme="dark"] .simda-login-bottom-note a {
            color: #D6B66C;
        }

        [data-theme="dark"] .simda-forgot-link {
            color: #D6B66C !important;
        }

        [data-theme="dark"] .simda-center-loader {
            background: var(--login-loader-overlay);
        }

        [data-theme="dark"] .simda-loader-card {
            background: var(--login-loader-bg);
            border-color: rgba(214, 182, 108, 0.25);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
        }

        [data-theme="dark"] .simda-loader-ring {
            border-color: #D6B66C;
            background: linear-gradient(135deg, rgba(214, 182, 108, 0.12), rgba(18, 59, 50, 0.25));
        }

        [data-theme="dark"] .simda-loader-title {
            color: #FFFFFF;
        }

        [data-theme="dark"] .simda-loader-sub {
            color: #A2B0A9;
        }

        .simda-login-stage {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 18px;
            background: var(--login-stage-bg);
            font-family: 'Plus Jakarta Sans', var(--font-body);
        }

        .simda-login-card {
            position: relative;
            width: 100%;
            max-width: 940px;
            min-height: 550px;
            background: var(--login-card-bg);
            border-radius: 26px;
            box-shadow: 0 32px 70px rgba(0, 0, 0, 0.42);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            overflow: hidden;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* Left Circular Welcome Panel & 3D Spheres */
        .simda-login-left {
            position: relative;
            padding: 42px 42px 38px 48px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
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
            max-width: 370px;
            margin-top: 6px;
            margin-bottom: 20px;
        }

        .simda-login-logo-pill {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            background: transparent !important;
            padding: 0 !important;
            margin-bottom: 28px;
            box-shadow: none !important;
            text-decoration: none;
            gap: 2px;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .simda-login-logo-pill:hover {
            transform: scale(1.02);
            opacity: 0.95;
        }

        .simda-login-logo-pill img {
            height: 64px;
            width: auto;
            display: block;
            filter: brightness(0) invert(1) !important;
            margin-bottom: 2px;
        }

        .simda-login-logo-tagline {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 1px;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.055em;
            line-height: 1.25;
            white-space: nowrap;
            text-transform: uppercase;
        }

        .simda-login-logo-tagline span:first-child {
            color: #FFFFFF !important;
        }

        .simda-login-logo-tagline span:last-child {
            color: #D6B66C !important;
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

        .simda-account-chip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #F2F5F3;
            border: 1px solid #D5DDD8;
            border-radius: 12px;
            padding: 9px 14px;
            margin-bottom: 18px;
            gap: 10px;
        }

        .simda-account-chip-info {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            overflow: hidden;
        }

        .simda-chip-avatar {
            color: #10B981;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .simda-chip-email {
            font-size: 0.86rem;
            font-weight: 700;
            color: #123B32;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .simda-chip-change-btn {
            background: none;
            border: none;
            color: #2872A1;
            font-size: 0.78rem;
            font-weight: 800;
            cursor: pointer;
            text-decoration: underline;
            padding: 2px 6px;
            flex-shrink: 0;
        }

        .simda-chip-change-btn:hover {
            color: #123B32;
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

        @keyframes simdaSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .simda-spin-icon {
            display: inline-block;
            vertical-align: middle;
            animation: simdaSpin 0.75s linear infinite;
            transform-origin: center center;
        }

        .simda-btn-spinner-s {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            vertical-align: middle;
        }

        .simda-btn-spinner-s img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        /* CENTER LOADING MODAL OVERLAY */
        .simda-center-loader {
            position: absolute;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 24px;
            animation: simdaFadeIn 0.2s ease-out;
        }

        @keyframes simdaFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .simda-loader-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 38px;
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(18, 59, 50, 0.16), 0 2px 10px rgba(0, 0, 0, 0.04);
            border: 1.5px solid rgba(18, 59, 50, 0.08);
            text-align: center;
            max-width: 320px;
            width: 100%;
            animation: simdaPopIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes simdaPopIn {
            0% { transform: scale(0.85); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .simda-loader-ring {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 16px;
            background: linear-gradient(135deg, rgba(18, 59, 50, 0.06), rgba(214, 182, 108, 0.14));
            border: 2.5px dashed rgba(18, 59, 50, 0.28);
            animation: simdaRingSpin 2.2s linear infinite;
        }

        @keyframes simdaRingSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .simda-loader-spin-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            animation: simdaSpin 0.9s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            transform-origin: center center;
            filter: drop-shadow(0 4px 10px rgba(18, 59, 50, 0.24));
        }

        .simda-loader-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: #123B32;
            margin-bottom: 4px;
            letter-spacing: 0.02em;
        }

        .simda-loader-sub {
            font-size: 0.78rem;
            color: #5A6B63;
            font-weight: 600;
            line-height: 1.4;
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
                <a href="?route=home" class="simda-login-logo-pill" title="SIMDA — Somali Islamic Microfinance Development Association">
                    <img src="assets/images/simda-logo.png" alt="SIMDA Logo">
                    <span class="simda-login-logo-tagline">
                        <span>SOMALI ISLAMIC MICROFINANCE</span>
                        <span>DEVELOPMENT ASSOCIATION</span>
                    </span>
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
            <!-- Top Controls (Dark/Light Mode & Language Selector) -->
            <div class="simda-login-top-bar">
                <!-- Sliding Pill Theme Toggle Switch -->
                <button type="button" class="simda-theme-pill-toggle" id="loginThemePillToggle" onclick="toggleSimdaTheme()" title="Toggle Dark / Light Mode" aria-label="Toggle Dark / Light Mode">
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
                    <div class="simda-lang-menu" role="menu" style="top:calc(100% + 8px);bottom:auto;">
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

            <!-- Center Loading Modal (Visible in the dead center during email verification) -->
            <div id="loginCenterLoader" class="simda-center-loader" style="display:none;">
                <div class="simda-loader-card">
                    <div class="simda-loader-ring">
                        <img src="assets/images/simda-icon.png" alt="S" class="simda-loader-spin-logo">
                    </div>
                    <div class="simda-loader-title"><?= Security::e($lt['btn_verifying']) ?></div>
                    <div class="simda-loader-sub"><?= ($locale === 'so') ? 'Fadlan sug wax yar, akoonka ayaa la hubinayaa...' : (($locale === 'ar') ? 'يرجى الانتظار، جاري التحقق...' : 'Please wait, verifying account...') ?></div>
                </div>
            </div>

            <!-- STEP 1: EMAIL ENTRY ONLY (Password is hidden until email is verified) -->
            <div id="loginStep1" style="<?= !empty($preVerifiedEmail) ? 'display:none;' : '' ?>">
                <h2 class="simda-signin-title"><?= Security::e($lt['signin']) ?></h2>
                <p class="simda-signin-sub"><?= Security::e($lt['step1_sub']) ?></p>

                <div id="step1AlertError" class="alert alert-danger" style="display:<?= (!empty($flashError) && empty($preVerifiedEmail)) ? 'flex' : 'none' ?>;padding:10px 14px;font-size:0.8rem;margin-bottom:14px;align-items:center;gap:8px;">
                    <?= Icons::svg('alert-triangle', 16) ?>
                    <span id="step1AlertText"><?= Security::e(empty($preVerifiedEmail) ? $flashError : '') ?></span>
                </div>

                <form id="formCheckEmail" onsubmit="handleCheckEmail(event)">
                    <div class="simda-input-pill" id="identifierPill">
                        <span class="simda-input-icon" id="identifierIconWrap">
                            <span id="identifierUserIcon"><?= Icons::svg('user', 18) ?></span>
                            <img id="identifierSpinIcon" src="assets/images/simda-icon.png" alt="S" class="simda-spin-icon" style="display:none;width:18px;height:18px;object-fit:contain;">
                        </span>
                        <input type="text" id="identifierInput" name="identifier" required autofocus placeholder="<?= Security::e($lt['identifier']) ?>" value="<?= Security::e($preVerifiedEmail) ?>" autocomplete="username">
                    </div>

                    <div class="simda-login-meta-row" style="justify-content:flex-end;">
                        <a href="?route=forgot_password" class="simda-forgot-link" style="color:#2872A1;font-weight:700;text-decoration:none;font-size:0.78rem;">
                            <?= Security::e($lt['forgot_password']) ?>
                        </a>
                    </div>

                    <button type="submit" id="btnContinue" class="simda-btn-signin-main" style="display:inline-flex;align-items:center;justify-content:center;gap:8px;">
                        <span id="btnContinueSpinner" class="simda-btn-spinner-s" style="display:none;">
                            <img src="assets/images/simda-icon.png" alt="S" class="simda-spin-icon">
                        </span>
                        <span id="btnContinueText"><?= Security::e($lt['btn_continue']) ?></span>
                    </button>
                </form>
            </div>

            <!-- STEP 2: PASSWORD ENTRY (Only revealed when email is verified in database) -->
            <div id="loginStep2" style="<?= empty($preVerifiedEmail) ? 'display:none;' : '' ?>">
                <h2 class="simda-signin-title"><?= Security::e($lt['step2_title']) ?></h2>
                <p class="simda-signin-sub"><?= Security::e($lt['step2_sub']) ?></p>

                <!-- Verified Account Pill -->
                <div class="simda-account-chip">
                    <div class="simda-account-chip-info">
                        <span class="simda-chip-avatar"><?= Icons::svg('check-circle', 16) ?></span>
                        <span id="verifiedUserEmail" class="simda-chip-email"><?= Security::e($preVerifiedEmail) ?></span>
                    </div>
                    <button type="button" class="simda-chip-change-btn" onclick="handleChangeEmail()"><?= Security::e($lt['btn_change_email']) ?></button>
                </div>

                <div id="step2AlertError" class="alert alert-danger" style="display:<?= (!empty($flashError) && !empty($preVerifiedEmail)) ? 'flex' : 'none' ?>;padding:10px 14px;font-size:0.8rem;margin-bottom:14px;align-items:center;gap:8px;">
                    <?= Icons::svg('alert-triangle', 16) ?>
                    <span id="step2AlertText"><?= Security::e(!empty($preVerifiedEmail) ? $flashError : '') ?></span>
                </div>

                <form method="POST" action="?route=login_submit" id="formLoginSubmit">
                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                    <input type="hidden" name="identifier" id="finalIdentifier" value="<?= Security::e($preVerifiedEmail) ?>">

                    <div class="simda-input-pill" id="passwordPill" style="<?= (!empty($flashError) && !empty($preVerifiedEmail)) ? 'border-color:#C94B4B;' : '' ?>">
                        <span class="simda-input-icon"><?= Icons::svg('lock', 18) ?></span>
                        <input type="password" id="password" name="password" required autofocus placeholder="<?= Security::e($lt['password']) ?>" autocomplete="current-password">
                        <button type="button" class="simda-pw-toggle" id="togglePwBtn"><?= Security::e($lt['show']) ?></button>
                    </div>

                    <div class="simda-login-meta-row">
                        <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="checkbox" name="remember" style="accent-color:#123B32;">
                            <span><?= Security::e($lt['remember']) ?></span>
                        </label>
                        <a href="?route=forgot_password" class="simda-forgot-link" style="color:#2872A1;font-weight:700;text-decoration:none;font-size:0.78rem;">
                            <?= Security::e($lt['forgot_password']) ?>
                        </a>
                    </div>

                    <button type="submit" class="simda-btn-signin-main"><?= Security::e($lt['btn_signin']) ?></button>
                </form>
            </div>

            <div class="simda-or-divider"><?= Security::e($lt['or']) ?></div>

            <a href="?route=home" class="simda-btn-signin-alt">
                <span><?= Security::e($lt['back_home']) ?></span>
            </a>

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
                    btn.textContent = '<?= Security::e($lt['hide']) ?>';
                } else {
                    pw.type = 'password';
                    btn.textContent = '<?= Security::e($lt['show']) ?>';
                }
            });
        }
    })();

    function handleCheckEmail(e) {
        if (e) e.preventDefault();
        var emailInput = document.getElementById('identifierInput');
        var btn = document.getElementById('btnContinue');
        var btnText = document.getElementById('btnContinueText');
        var btnSpinner = document.getElementById('btnContinueSpinner');
        var userIcon = document.getElementById('identifierUserIcon');
        var spinIcon = document.getElementById('identifierSpinIcon');
        var alertErr = document.getElementById('step1AlertError');
        var alertText = document.getElementById('step1AlertText');
        var pill = document.getElementById('identifierPill');
        var centerLoader = document.getElementById('loginCenterLoader');

        var val = (emailInput.value || '').trim();
        if (!val) {
            emailInput.focus();
            return;
        }

        btn.disabled = true;
        btn.style.opacity = '0.85';
        if (centerLoader) centerLoader.style.display = 'flex';
        if (btnSpinner) btnSpinner.style.display = 'inline-flex';
        if (userIcon) userIcon.style.display = 'none';
        if (spinIcon) spinIcon.style.display = 'inline-block';
        btnText.textContent = '<?= Security::e($lt['btn_verifying']) ?>';
        alertErr.style.display = 'none';
        pill.style.borderColor = '#123B32';

        var fd = new FormData();
        fd.append('_csrf', '<?= Security::e($csrf) ?>');
        fd.append('identifier', val);

        var fetchPromise = fetch('?route=login_check_email', {
            method: 'POST',
            body: fd
        }).then(function(res) { return res.json(); });

        // Ensure user sees the rotating 'S' icon smoothly in the center (~700ms)
        var minDelay = new Promise(function(resolve) { setTimeout(resolve, 700); });

        Promise.all([fetchPromise, minDelay])
        .then(function(results) {
            var data = results[0];
            if (centerLoader) centerLoader.style.display = 'none';
            btn.disabled = false;
            btn.style.opacity = '1';
            if (btnSpinner) btnSpinner.style.display = 'none';
            if (userIcon) userIcon.style.display = 'inline-block';
            if (spinIcon) spinIcon.style.display = 'none';
            btnText.textContent = '<?= Security::e($lt['btn_continue']) ?>';

            if (data && data.success) {
                // Email found in database! Reveal Step 2 (Password box)
                document.getElementById('finalIdentifier').value = data.identifier || val;
                document.getElementById('verifiedUserEmail').textContent = data.identifier || val;
                document.getElementById('loginStep1').style.display = 'none';
                document.getElementById('loginStep2').style.display = 'block';
                setTimeout(function() {
                    var pw = document.getElementById('password');
                    if (pw) {
                        pw.value = '';
                        pw.focus();
                    }
                }, 100);
            } else {
                // Email NOT in database or locked/blocked
                alertText.textContent = (data && data.message) ? data.message : '<?= Security::e($lt['err_not_found']) ?>';
                alertErr.style.display = 'flex';
                pill.style.borderColor = '#C94B4B';
                emailInput.focus();
            }
        })
        .catch(function(err) {
            if (centerLoader) centerLoader.style.display = 'none';
            btn.disabled = false;
            btn.style.opacity = '1';
            if (btnSpinner) btnSpinner.style.display = 'none';
            if (userIcon) userIcon.style.display = 'inline-block';
            if (spinIcon) spinIcon.style.display = 'none';
            btnText.textContent = '<?= Security::e($lt['btn_continue']) ?>';
            alertText.textContent = '<?= Security::e($lt['err_not_found']) ?>';
            alertErr.style.display = 'flex';
            pill.style.borderColor = '#C94B4B';
        });
    }

    function handleChangeEmail() {
        document.getElementById('loginStep2').style.display = 'none';
        document.getElementById('loginStep1').style.display = 'block';
        var emailInput = document.getElementById('identifierInput');
        if (emailInput) {
            emailInput.focus();
            emailInput.select();
        }
    }

    function toggleSimdaTheme() {
        var current = document.documentElement.getAttribute('data-theme') || 'light';
        var next = (current === 'dark') ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('simda_theme_pref', next);
    }
</script>
<script src="assets/js/simda.js?v=<?= Security::e($assetVer) ?>"></script>
</body>
</html>
