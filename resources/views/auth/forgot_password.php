<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$locale = Lang::locale();
$csrf = Security::csrfToken();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), 1);

$i18n = [
    'en' => [
        'page_title' => 'Password Recovery — SIMDA Microfinance',
        'welcome_title' => 'RECOVER ACCESS',
        'welcome_sub' => 'SIMDA SECURITY & AUTHENTICATION',
        'welcome_desc' => 'Centralized, ethical, and 100% Zero-Riba Sharia-compliant financial management platform.',
        'step1_title' => 'Reset Password',
        'step1_sub' => 'Enter your registered email address to receive a 6-digit OTP code.',
        'email_label' => 'Registered Email Address',
        'email_placeholder' => 'name@example.com',
        'btn_send_otp' => 'Send Verification Code →',
        'btn_sending' => 'Sending Code...',
        'step2_title' => 'Enter OTP Code',
        'step2_sub' => 'We sent a 6-digit code to',
        'resend_code' => 'Resend Code',
        'btn_verify_otp' => 'Verify Code →',
        'btn_verifying' => 'Verifying...',
        'step3_title' => 'Set New Password',
        'step3_sub' => 'Create a strong new password with at least 8 characters.',
        'new_pw_placeholder' => 'Enter New Password',
        'confirm_pw_placeholder' => 'Confirm New Password',
        'btn_save_pw' => 'Save New Password →',
        'btn_saving' => 'Updating...',
        'success_title' => 'Password Updated!',
        'success_desc' => 'Your password has been reset successfully. You can now log into your portal.',
        'btn_go_login' => 'Sign In to Portal →',
        'back_to_login' => 'Back to Sign In',
        'need_help' => 'Need assistance?',
        'contact_office' => 'Contact SIMDA Head Office',
    ],
    'so' => [
        'page_title' => 'Soo Celinta Furaha — SIMDA Microfinance',
        'welcome_title' => 'SOO CELINTA FURAHA',
        'welcome_sub' => 'AMNIGA & XAQIIJINTA SIMDA',
        'welcome_desc' => 'Nidaam dhexe, xalaal ah, oo 100% waafaqsan Shareecada Islaamka iyo 0% Ribo.',
        'step1_title' => 'Soo Celinta Furaha',
        'step1_sub' => 'Geli ciwaankaaga email-ka si aan kuugu soo dirno koodhka xaqiijinta (OTP).',
        'email_label' => 'Ciwaanka Email-kaaga',
        'email_placeholder' => 'magacaaga@email.com',
        'btn_send_otp' => 'Dir Koodhka Xaqiijinta →',
        'btn_sending' => 'Koodhka waa la dirayaa...',
        'step2_title' => 'Geli Koodhka OTP-ga',
        'step2_sub' => 'Waxaan koodhka 6-da lambar ah u dirnay',
        'resend_code' => 'Dib u dir Koodhka',
        'btn_verify_otp' => 'Xaqiiji Koodhka →',
        'btn_verifying' => 'Waa la hubinayaa...',
        'step3_title' => 'Sameyso Fure Cusub',
        'step3_sub' => 'Fadlan sameyso fure cusub oo adag oo ugu yaraan 8 xaraf ah.',
        'new_pw_placeholder' => 'Geli Furaha Cusub',
        'confirm_pw_placeholder' => 'Xaqiiji Furaha Cusub',
        'btn_save_pw' => 'Keydi Furaha Cusub →',
        'btn_saving' => 'Waa la keydinayaa...',
        'success_title' => 'Furaha Waa La Beddelay!',
        'success_desc' => 'Furahaaga sirta ah si guul leh ayaa loo cusbooneysiiyay. Hadda waad geli kartaa bortaalkaaga.',
        'btn_go_login' => 'Gal Bortaalka Hadda →',
        'back_to_login' => 'Ku noqo Gelitaanka',
        'need_help' => 'Ma u baahan tahay caawimaad?',
        'contact_office' => 'La xiriir Xafiiska Guud ee SIMDA',
    ],
    'ar' => [
        'page_title' => 'استعادة كلمة المرور — سيمدا',
        'welcome_title' => 'استعادة كلمة المرور',
        'welcome_sub' => 'أمان وحماية بوابة سيمدا',
        'welcome_desc' => 'منظومة مالية إسلامية مركزية وأخلاقية متوافقة 100% مع الشريعة وبدون ربا (0%).',
        'step1_title' => 'استعادة كلمة المرور',
        'step1_sub' => 'أدخل بريدك الإلكتروني المسجل لاستلام رمز التحقق (OTP).',
        'email_label' => 'البريد الإلكتروني المسجل',
        'email_placeholder' => 'name@example.com',
        'btn_send_otp' => 'إرسال رمز التحقق ←',
        'btn_sending' => 'جاري الإرسال...',
        'step2_title' => 'أدخل رمز التحقق (OTP)',
        'step2_sub' => 'تم إرسال رمز التحقق إلى',
        'resend_code' => 'إعادة إرسال الرمز',
        'btn_verify_otp' => 'تأكيد الرمز ←',
        'btn_verifying' => 'جاري التحقق...',
        'step3_title' => 'تعيين كلمة مرور جديدة',
        'step3_sub' => 'يرجى إدخال كلمة مرور جديدة لا تقل عن 8 خانات.',
        'new_pw_placeholder' => 'أدخل كلمة المرور الجديدة',
        'confirm_pw_placeholder' => 'تأكيد كلمة المرور الجديدة',
        'btn_save_pw' => 'حفظ كلمة المرور ←',
        'btn_saving' => 'جاري الحفظ...',
        'success_title' => 'تم تغيير كلمة المرور بنجاح!',
        'success_desc' => 'تم تحديث كلمة المرور الخاصة بك بنجاح. يمكنك الآن الدخول إلى البوابة.',
        'btn_go_login' => 'تسجيل الدخول الآن ←',
        'back_to_login' => 'العودة لتسجيل الدخول',
        'need_help' => 'هل تحتاج إلى مساعدة؟',
        'contact_office' => 'اتصل بالمقر الرئيسي لسيمدا',
    ],
];
$t = $i18n[$locale] ?? $i18n['en'];
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" dir="<?= $locale === 'ar' ? 'rtl' : 'ltr' ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e($t['page_title']) ?></title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="manifest" href="manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            --card-sub: #5A6660;
            --input-bg: #F2F4F3;
            --input-border: transparent;
            --input-text: #162621;
            --border-divider: #E2E7E4;
            --alt-btn-bg: #FFFFFF;
            --alt-btn-border: #26332E;
            --alt-btn-text: #162621;
            --otp-box-bg: #F2F4F3;
            --otp-box-border: transparent;
            --loader-card-bg: #FFFFFF;
            --loader-overlay: rgba(255, 255, 255, 0.92);
        }

        [data-theme="dark"] {
            --stage-bg: radial-gradient(circle at 25% 20%, #142822 0%, #0D1A16 55%, #050B09 100%);
            --card-bg: #14221D;
            --card-text: #FFFFFF;
            --card-sub: #A2B0A9;
            --input-bg: #1C3029;
            --input-border: rgba(214, 182, 108, 0.25);
            --input-text: #FFFFFF;
            --border-divider: rgba(255, 255, 255, 0.12);
            --alt-btn-bg: #1A2C24;
            --alt-btn-border: rgba(255, 255, 255, 0.25);
            --alt-btn-text: #FFFFFF;
            --otp-box-bg: #1C3029;
            --otp-box-border: rgba(214, 182, 108, 0.3);
            --loader-card-bg: #14221D;
            --loader-overlay: rgba(13, 26, 22, 0.94);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--stage-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 18px;
            color: var(--card-text);
            -webkit-font-smoothing: antialiased;
        }

        .simda-fp-stage {
            width: 100%;
            max-width: 940px;
            min-height: 550px;
            margin: 0 auto;
        }

        .simda-fp-card {
            position: relative;
            width: 100%;
            background: var(--card-bg);
            border-radius: 26px;
            box-shadow: 0 32px 70px rgba(0, 0, 0, 0.42);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            overflow: hidden;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* Left Circular Welcome Panel & 3D Spheres */
        .simda-fp-left {
            position: relative;
            padding: 42px 42px 38px 48px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            color: #FFFFFF;
            z-index: 2;
            user-select: none;
            overflow: hidden;
        }

        .simda-fp-circle-main {
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

        .simda-fp-sphere-mid {
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

        .simda-fp-sphere-bl {
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

        .simda-fp-sphere-br {
            position: absolute;
            bottom: -46px;
            right: -36px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 26%, #28725F 0%, #133D33 60%, #071F19 100%);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.32);
            pointer-events: none;
            z-index: 1;
        }

        .simda-fp-welcome-content {
            position: relative;
            z-index: 2;
            max-width: 380px;
            margin-bottom: auto;
        }

        .simda-fp-logo-pill {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
            margin-bottom: 24px;
            text-decoration: none;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .simda-fp-logo-pill:hover {
            transform: scale(1.02);
            opacity: 0.95;
        }

        .simda-fp-logo-pill img {
            height: 64px;
            width: auto;
            display: block;
            filter: brightness(0) invert(1) !important;
            margin-bottom: 2px;
        }

        .simda-fp-logo-tagline {
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

        .simda-fp-logo-tagline span:first-child {
            color: #FFFFFF !important;
        }

        .simda-fp-logo-tagline span:last-child {
            color: #D6B66C !important;
        }

        .simda-fp-welcome-title {
            font-size: 2.35rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #FFFFFF;
            line-height: 1.08;
            margin: 0 0 8px;
            text-transform: uppercase;
        }

        .simda-fp-welcome-sub {
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #D6B66C;
            margin: 0 0 16px;
        }

        .simda-fp-welcome-desc {
            font-size: 0.82rem;
            line-height: 1.65;
            color: rgba(244, 245, 240, 0.84);
            margin: 0;
        }

        /* Right Form Side */
        .simda-fp-right {
            position: relative;
            z-index: 3;
            padding: 104px 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            background: transparent;
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

        /* Top Action Bar (Theme Toggle, Lang, Close) */
        .simda-fp-top-bar {
            position: absolute;
            top: 18px;
            right: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10;
        }

        #fpScreen1, #fpScreen2, #fpScreen3, #fpScreen4 {
            margin-top: 8px;
        }

        .simda-top-icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--input-bg);
            border: 1px solid var(--border-divider);
            color: var(--card-text);
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

        .simda-fp-title {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--card-text);
            letter-spacing: -0.02em;
            margin: 0 0 6px;
        }

        .simda-fp-sub {
            font-size: 0.84rem;
            color: var(--card-sub);
            line-height: 1.55;
            margin: 0 0 24px;
        }

        .simda-fp-input-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 18px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .simda-fp-input-pill:focus-within {
            border-color: #123B32;
            box-shadow: 0 0 0 3px rgba(18, 59, 50, 0.14);
        }

        [data-theme="dark"] .simda-fp-input-pill:focus-within {
            border-color: #D6B66C;
            box-shadow: 0 0 0 3px rgba(214, 182, 108, 0.2);
        }

        .simda-fp-input-icon {
            color: #7A8982;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .simda-fp-input {
            width: 100%;
            border: none;
            outline: none;
            background: transparent;
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--input-text);
            font-family: inherit;
        }

        .simda-fp-input::placeholder {
            color: #8C9993;
            font-weight: 500;
        }

        .simda-pw-toggle {
            background: none;
            border: none;
            color: #6C7A74;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            cursor: pointer;
            padding: 2px 4px;
        }

        .simda-pw-toggle:hover {
            color: #123B32;
        }

        [data-theme="dark"] .simda-pw-toggle:hover {
            color: #D6B66C;
        }

        /* SIMDA Brand Green Button */
        .simda-btn-primary {
            width: 100%;
            background: #123B32;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 13px 20px;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(18, 59, 50, 0.28);
            transition: background-color 0.18s ease, transform 0.18s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .simda-btn-primary:hover {
            background: #0D2B24;
            transform: translateY(-1px);
        }

        [data-theme="dark"] .simda-btn-primary {
            background: linear-gradient(135deg, #1A4D41 0%, #123B32 100%);
            border: 1px solid rgba(214, 182, 108, 0.35);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.5);
        }

        [data-theme="dark"] .simda-btn-primary:hover {
            background: #205C4E;
        }

        .simda-btn-alt {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--alt-btn-bg);
            color: var(--alt-btn-text);
            border: 1.5px solid var(--alt-btn-border);
            border-radius: 10px;
            padding: 11px 20px;
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.18s ease;
            margin-top: 14px;
        }

        .simda-btn-alt:hover {
            background: var(--input-bg);
        }

        /* 6 OTP Boxes */
        .simda-otp-grid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin: 20px 0 22px;
        }

        .simda-otp-box {
            width: 48px;
            height: 56px;
            border-radius: 12px;
            background: var(--otp-box-bg);
            border: 2px solid var(--otp-box-border);
            font-size: 1.45rem;
            font-weight: 800;
            text-align: center;
            color: var(--card-text);
            outline: none;
            transition: all 0.18s ease;
            font-family: monospace;
        }

        .simda-otp-box:focus {
            border-color: #123B32;
            background: var(--card-bg);
            box-shadow: 0 0 0 3px rgba(18, 59, 50, 0.18);
        }

        [data-theme="dark"] .simda-otp-box:focus {
            border-color: #D6B66C;
            box-shadow: 0 0 0 3px rgba(214, 182, 108, 0.25);
        }

        .simda-otp-box.is-filled {
            border-color: #123B32;
            background: var(--card-bg);
        }

        [data-theme="dark"] .simda-otp-box.is-filled {
            border-color: #D6B66C;
        }

        .simda-resend-row {
            margin-bottom: 22px;
            font-size: 0.82rem;
            text-align: center;
        }

        .simda-resend-link {
            color: #2872A1;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .simda-resend-link:hover {
            text-decoration: underline;
        }

        /* Alert Box */
        .simda-fp-alert {
            background: #FDF2F2;
            border: 1px solid #F5C6C6;
            color: #C94B4B;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            text-align: left;
        }

        [data-theme="dark"] .simda-fp-alert {
            background: rgba(201, 75, 75, 0.15);
            border-color: rgba(201, 75, 75, 0.35);
        }

        /* CENTER LOADING OVERLAY */
        .simda-center-loader {
            position: absolute;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--loader-overlay);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 26px;
            padding: 24px;
        }

        .simda-loader-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 38px;
            background: var(--loader-card-bg);
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18);
            border: 1.5px solid var(--border-divider);
            text-align: center;
            max-width: 320px;
            width: 100%;
        }

        .simda-loader-ring {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            background: linear-gradient(135deg, rgba(18, 59, 50, 0.08), rgba(214, 182, 108, 0.15));
            border: 2.5px dashed rgba(18, 59, 50, 0.35);
            animation: simdaRingSpin 2.2s linear infinite;
        }

        [data-theme="dark"] .simda-loader-ring {
            border-color: #D6B66C;
        }

        @keyframes simdaRingSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes simdaSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .simda-loader-spin-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            animation: simdaSpin 0.9s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            transform-origin: center center;
            filter: drop-shadow(0 4px 10px rgba(18, 59, 50, 0.25));
        }

        .simda-loader-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: var(--card-text);
            margin-bottom: 4px;
        }

        .simda-loader-sub {
            font-size: 0.78rem;
            color: var(--card-sub);
            font-weight: 600;
        }

        .simda-fp-bottom-note {
            margin-top: 22px;
            text-align: center;
            font-size: 0.78rem;
            color: var(--card-sub);
        }

        .simda-fp-bottom-note a {
            color: #123B32;
            font-weight: 700;
        }

        [data-theme="dark"] .simda-fp-bottom-note a {
            color: #D6B66C;
        }

        @media (max-width: 820px) {
            .simda-fp-card {
                grid-template-columns: 1fr;
                max-width: 460px;
            }
            .simda-fp-left {
                padding: 36px 32px 28px;
                background: linear-gradient(135deg, #194D40 0%, #123B32 100%);
            }
            .simda-fp-circle-main,
            .simda-fp-sphere-mid,
            .simda-fp-sphere-bl {
                display: none;
            }
            .simda-fp-top-bar {
                top: 14px;
                right: 18px;
            }
            .simda-fp-right {
                padding: 92px 24px 34px;
            }
        }
    </style>
</head>
<body>

<div class="simda-fp-stage">
    <div class="simda-fp-card animate-in">
        <!-- Left Circular Welcome Panel with 3D Spheres & SIMDA Logo -->
        <div class="simda-fp-left">
            <div class="simda-fp-circle-main"></div>
            <div class="simda-fp-sphere-mid"></div>
            <div class="simda-fp-sphere-bl"></div>

            <div class="simda-fp-welcome-content">
                <a href="?route=home" class="simda-fp-logo-pill" title="SIMDA — Somali Islamic Microfinance Development Association">
                    <img src="assets/images/simda-logo.png" alt="SIMDA Logo">
                    <span class="simda-fp-logo-tagline">
                        <span>SOMALI ISLAMIC MICROFINANCE</span>
                        <span>DEVELOPMENT ASSOCIATION</span>
                    </span>
                </a>
                <h1 class="simda-fp-welcome-title"><?= Security::e($t['welcome_title']) ?></h1>
                <div class="simda-fp-welcome-sub"><?= Security::e($t['welcome_sub']) ?></div>
                <p class="simda-fp-welcome-desc">
                    <?= Security::e(Lang::get('tagline')) ?> — <?= Security::e($t['welcome_desc']) ?>
                </p>
            </div>
        </div>

        <!-- Right Interactive Form Panel -->
        <div class="simda-fp-right">
            <!-- Top Controls (Dark/Light Mode, Language Selector, Close) -->
            <div class="simda-fp-top-bar">
                <!-- Sliding Pill Theme Toggle Switch -->
                <button type="button" class="simda-theme-pill-toggle" id="themePillToggle" onclick="toggleTheme()" title="Toggle Dark / Light Mode" aria-label="Toggle Dark / Light Mode">
                    <span class="simda-track-icon simda-track-sun" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
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
                                <line x1="1" y1="12" x2="3" y2="12"></line>
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

                <!-- Language Selector -->
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
                            <a href="?route=forgot_password&lang=<?= $code ?>" class="simda-lang-item <?= $locale === $code ? 'is-active' : '' ?>" role="menuitem">
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

                <!-- Close Button -->
                <a href="?route=login" class="simda-top-icon-btn" title="<?= Security::e($t['back_to_login']) ?>">✕</a>
            </div>

            <!-- Centered Loading Overlay (Active during network operations) -->
            <div id="fpCenterLoader" class="simda-center-loader" style="display:none;">
                <div class="simda-loader-card">
                    <div class="simda-loader-ring">
                        <img src="assets/images/simda-icon.png" alt="S" class="simda-loader-spin-logo">
                    </div>
                    <div class="simda-loader-title" id="loaderTitleText"><?= Security::e($t['btn_verifying']) ?></div>
                    <div class="simda-loader-sub"><?= ($locale === 'so') ? 'Fadlan sug wax yar...' : 'Please wait a moment...' ?></div>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- STEP 1: EMAIL ENTRY ONLY (No phone number)                       -->
            <!-- =============================================================== -->
            <div id="fpScreen1">
                <h2 class="simda-fp-title"><?= Security::e($t['step1_title']) ?></h2>
                <p class="simda-fp-sub"><?= Security::e($t['step1_sub']) ?></p>

                <!-- Error banner -->
                <div id="fpAlertError1" class="simda-fp-alert" style="display:none;">
                    <?= Icons::svg('alert-triangle', 16) ?>
                    <span id="fpAlertText1"></span>
                </div>

                <form onsubmit="handleSendOtp(event)">
                    <div class="simda-fp-input-pill" id="emailPill">
                        <span class="simda-fp-input-icon"><?= Icons::svg('mail', 18) ?></span>
                        <input type="email" id="inputEmail" class="simda-fp-input" placeholder="<?= Security::e($t['email_placeholder']) ?>" required autofocus autocomplete="email">
                    </div>

                    <button type="submit" class="simda-btn-primary" id="btnSendOtp">
                        <span id="btnSendOtpText"><?= Security::e($t['btn_send_otp']) ?></span>
                    </button>
                </form>

                <div class="simda-or-divider"><?= ($locale === 'so') ? 'Ama' : 'Or' ?></div>

                <a href="?route=login" class="simda-btn-alt">
                    <span>← <?= Security::e($t['back_to_login']) ?></span>
                </a>
            </div>

            <!-- =============================================================== -->
            <!-- STEP 2: ENTER OTP CODE (6 DIGITS)                               -->
            <!-- =============================================================== -->
            <div id="fpScreen2" style="display:none;">
                <h2 class="simda-fp-title"><?= Security::e($t['step2_title']) ?></h2>
                <p class="simda-fp-sub">
                    <?= Security::e($t['step2_sub']) ?>: <strong id="targetEmailLabel" style="color:var(--card-text);"></strong>
                </p>

                <!-- Error banner -->
                <div id="fpAlertError2" class="simda-fp-alert" style="display:none;">
                    <?= Icons::svg('alert-triangle', 16) ?>
                    <span id="fpAlertText2"></span>
                </div>

                <div class="simda-otp-grid">
                    <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox1" autofocus>
                    <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox2">
                    <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox3">
                    <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox4">
                    <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox5">
                    <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox6">
                </div>

                <div class="simda-resend-row">
                    <a href="javascript:void(0)" class="simda-resend-link" id="linkResend" onclick="handleResendOtp()"><?= Security::e($t['resend_code']) ?></a>
                    <span id="resendCountdown" style="color:var(--card-sub);display:none;margin-left:4px;">(60s)</span>
                </div>

                <button type="button" class="simda-btn-primary" id="btnVerifyOtp" onclick="handleVerifyOtp()">
                    <span id="btnVerifyOtpText"><?= Security::e($t['btn_verify_otp']) ?></span>
                </button>

                <a href="javascript:void(0)" onclick="goToScreen(1)" class="simda-btn-alt">
                    <span>← Beddel Email-ka</span>
                </a>
            </div>

            <!-- =============================================================== -->
            <!-- STEP 3: SET NEW PASSWORD                                        -->
            <!-- =============================================================== -->
            <div id="fpScreen3" style="display:none;">
                <h2 class="simda-fp-title"><?= Security::e($t['step3_title']) ?></h2>
                <p class="simda-fp-sub"><?= Security::e($t['step3_sub']) ?></p>

                <!-- Error banner -->
                <div id="fpAlertError3" class="simda-fp-alert" style="display:none;">
                    <?= Icons::svg('alert-triangle', 16) ?>
                    <span id="fpAlertText3"></span>
                </div>

                <form onsubmit="handleSavePassword(event)">
                    <div class="simda-fp-input-pill">
                        <span class="simda-fp-input-icon"><?= Icons::svg('lock', 18) ?></span>
                        <input type="password" id="inputNewPassword" class="simda-fp-input" placeholder="<?= Security::e($t['new_pw_placeholder']) ?>" required minlength="8" autocomplete="new-password">
                        <button type="button" class="simda-pw-toggle" onclick="togglePasswordVisibility('inputNewPassword', this)">SHOW</button>
                    </div>

                    <div class="simda-fp-input-pill">
                        <span class="simda-fp-input-icon"><?= Icons::svg('lock', 18) ?></span>
                        <input type="password" id="inputConfirmPassword" class="simda-fp-input" placeholder="<?= Security::e($t['confirm_pw_placeholder']) ?>" required minlength="8" autocomplete="new-password">
                        <button type="button" class="simda-pw-toggle" onclick="togglePasswordVisibility('inputConfirmPassword', this)">SHOW</button>
                    </div>

                    <button type="submit" class="simda-btn-primary" id="btnSavePw">
                        <span id="btnSavePwText"><?= Security::e($t['btn_save_pw']) ?></span>
                    </button>
                </form>
            </div>

            <!-- =============================================================== -->
            <!-- STEP 4: SUCCESS BANNER                                          -->
            <!-- =============================================================== -->
            <div id="fpScreen4" style="display:none;text-align:center;">
                <div style="width:72px;height:72px;border-radius:50%;background:rgba(18,59,50,0.1);display:inline-flex;align-items:center;justify-content:center;margin-bottom:18px;color:#10B981;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h2 class="simda-fp-title"><?= Security::e($t['success_title']) ?></h2>
                <p class="simda-fp-sub" style="margin-bottom:26px;"><?= Security::e($t['success_desc']) ?></p>

                <a href="?route=login" class="simda-btn-primary" style="text-decoration:none;">
                    <?= Security::e($t['btn_go_login']) ?>
                </a>
            </div>

            <div class="simda-fp-bottom-note">
                <?= Security::e($t['need_help']) ?> <a href="?route=contact"><?= Security::e($t['contact_office']) ?></a>
            </div>
        </div>

        <!-- Decorative Bottom-Right 3D Sphere -->
        <div class="simda-fp-sphere-br"></div>
    </div>
</div>

<script>
    var resetToken = '';
    var activeEmail = '';

    // Theme Toggle Functionality (Sliding Pill Switch)
    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-theme') || 'light';
        var next = (current === 'dark') ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('simda_theme_pref', next);
    }

    // Screen Transition
    function goToScreen(num) {
        document.getElementById('fpScreen1').style.display = (num === 1) ? 'block' : 'none';
        document.getElementById('fpScreen2').style.display = (num === 2) ? 'block' : 'none';
        document.getElementById('fpScreen3').style.display = (num === 3) ? 'block' : 'none';
        document.getElementById('fpScreen4').style.display = (num === 4) ? 'block' : 'none';

        if (num === 2) {
            setTimeout(function() {
                var b1 = document.getElementById('otpBox1');
                if (b1) b1.focus();
            }, 100);
        } else if (num === 3) {
            setTimeout(function() {
                var p1 = document.getElementById('inputNewPassword');
                if (p1) p1.focus();
            }, 100);
        }
    }

    // Step 1: Send OTP
    function handleSendOtp(e) {
        if (e) e.preventDefault();
        var emailInput = document.getElementById('inputEmail');
        var val = (emailInput.value || '').trim();
        var alertBox = document.getElementById('fpAlertError1');
        var alertText = document.getElementById('fpAlertText1');
        var loader = document.getElementById('fpCenterLoader');
        var loaderTitle = document.getElementById('loaderTitleText');

        if (!val) {
            emailInput.focus();
            return;
        }

        alertBox.style.display = 'none';
        if (loader) {
            loaderTitle.textContent = '<?= Security::e($t['btn_sending']) ?>';
            loader.style.display = 'flex';
        }

        var fd = new FormData();
        fd.append('_csrf', '<?= Security::e($csrf) ?>');
        fd.append('type', 'email');
        fd.append('target', val);

        var fetchP = fetch('?route=forgot_password_send_otp', {
            method: 'POST',
            body: fd
        }).then(function(r) { return r.json(); });

        var delayP = new Promise(function(res) { setTimeout(res, 650); });

        Promise.all([fetchP, delayP])
        .then(function(results) {
            var data = results[0];
            if (loader) loader.style.display = 'none';

            if (data && data.success) {
                resetToken = data.token;
                activeEmail = val;
                document.getElementById('targetEmailLabel').textContent = data.masked_target || val;

                goToScreen(2);
                startResendTimer(60);
            } else {
                alertText.textContent = (data && data.message) ? data.message : 'Khalad ayaa dhacay.';
                alertBox.style.display = 'flex';
                emailInput.focus();
            }
        })
        .catch(function(err) {
            if (loader) loader.style.display = 'none';
            alertText.textContent = 'Cilad xagga server-ka ah. Fadlan isku day mar kale.';
            alertBox.style.display = 'flex';
        });
    }

    function handleResendOtp() {
        var link = document.getElementById('linkResend');
        if (link.dataset.disabled === '1') return;
        document.getElementById('inputEmail').value = activeEmail;
        handleSendOtp();
    }

    function startResendTimer(sec) {
        var link = document.getElementById('linkResend');
        var timerSpan = document.getElementById('resendCountdown');
        link.dataset.disabled = '1';
        link.style.opacity = '0.5';
        link.style.cursor = 'not-allowed';
        timerSpan.style.display = 'inline';

        var left = sec;
        timerSpan.textContent = '(' + left + 's)';

        var iv = setInterval(function() {
            left--;
            if (left <= 0) {
                clearInterval(iv);
                link.dataset.disabled = '0';
                link.style.opacity = '1';
                link.style.cursor = 'pointer';
                timerSpan.style.display = 'none';
            } else {
                timerSpan.textContent = '(' + left + 's)';
            }
        }, 1000);
    }

    // 6 OTP Box Navigation & Paste Support
    var otpInputs = [
        document.getElementById('otpBox1'),
        document.getElementById('otpBox2'),
        document.getElementById('otpBox3'),
        document.getElementById('otpBox4'),
        document.getElementById('otpBox5'),
        document.getElementById('otpBox6')
    ];

    otpInputs.forEach(function(box, index) {
        box.addEventListener('input', function(e) {
            var val = (box.value || '').trim();
            if (val.length > 0) {
                box.value = val.slice(-1);
                box.classList.add('is-filled');
                if (index < 5) {
                    otpInputs[index + 1].focus();
                } else {
                    box.blur();
                    handleVerifyOtp();
                }
            } else {
                box.classList.remove('is-filled');
            }
        });

        box.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !box.value && index > 0) {
                otpInputs[index - 1].focus();
                otpInputs[index - 1].value = '';
                otpInputs[index - 1].classList.remove('is-filled');
            }
        });

        box.addEventListener('paste', function(e) {
            e.preventDefault();
            var pasted = (e.clipboardData || window.clipboardData).getData('text');
            var digits = (pasted || '').replace(/[^0-9]/g, '').slice(0, 6);
            if (digits.length > 0) {
                for (var i = 0; i < digits.length; i++) {
                    otpInputs[i].value = digits[i];
                    otpInputs[i].classList.add('is-filled');
                }
                if (digits.length < 6) {
                    otpInputs[digits.length].focus();
                } else {
                    otpInputs[5].focus();
                    handleVerifyOtp();
                }
            }
        });
    });

    function getEnteredOtp() {
        return otpInputs.map(function(b) { return (b.value || '').trim(); }).join('');
    }

    // Step 2: Verify OTP
    function handleVerifyOtp() {
        var otp = getEnteredOtp();
        var alertBox = document.getElementById('fpAlertError2');
        var alertText = document.getElementById('fpAlertText2');
        var loader = document.getElementById('fpCenterLoader');
        var loaderTitle = document.getElementById('loaderTitleText');

        if (otp.length < 6) {
            alertText.textContent = 'Fadlan geli dhammaan 6-da lambar ee koodhka.';
            alertBox.style.display = 'flex';
            return;
        }

        alertBox.style.display = 'none';
        if (loader) {
            loaderTitle.textContent = '<?= Security::e($t['btn_verifying']) ?>';
            loader.style.display = 'flex';
        }

        var fd = new FormData();
        fd.append('_csrf', '<?= Security::e($csrf) ?>');
        fd.append('token', resetToken);
        fd.append('otp', otp);

        var fetchP = fetch('?route=forgot_password_verify_otp', {
            method: 'POST',
            body: fd
        }).then(function(r) { return r.json(); });

        var delayP = new Promise(function(res) { setTimeout(res, 600); });

        Promise.all([fetchP, delayP])
        .then(function(results) {
            var data = results[0];
            if (loader) loader.style.display = 'none';

            if (data && data.success) {
                goToScreen(3);
            } else {
                alertText.textContent = (data && data.message) ? data.message : 'Koodhka aad gelisay waa qalad.';
                alertBox.style.display = 'flex';
            }
        })
        .catch(function(err) {
            if (loader) loader.style.display = 'none';
            alertText.textContent = 'Cilad ayaa dhacday. Fadlan mar kale isku day.';
            alertBox.style.display = 'flex';
        });
    }

    // Step 3: Save Password
    function handleSavePassword(e) {
        if (e) e.preventDefault();
        var p1 = document.getElementById('inputNewPassword').value;
        var p2 = document.getElementById('inputConfirmPassword').value;
        var alertBox = document.getElementById('fpAlertError3');
        var alertText = document.getElementById('fpAlertText3');
        var loader = document.getElementById('fpCenterLoader');
        var loaderTitle = document.getElementById('loaderTitleText');

        if (p1.length < 8) {
            alertText.textContent = 'Furaha cusub waa inuu ka koobnaadaa ugu yaraan 8 xaraf.';
            alertBox.style.display = 'flex';
            return;
        }

        if (p1 !== p2) {
            alertText.textContent = 'Labada fure isma leha. Fadlan hubi.';
            alertBox.style.display = 'flex';
            return;
        }

        alertBox.style.display = 'none';
        if (loader) {
            loaderTitle.textContent = '<?= Security::e($t['btn_saving']) ?>';
            loader.style.display = 'flex';
        }

        var fd = new FormData();
        fd.append('_csrf', '<?= Security::e($csrf) ?>');
        fd.append('password', p1);
        fd.append('confirm_password', p2);

        var fetchP = fetch('?route=forgot_password_reset', {
            method: 'POST',
            body: fd
        }).then(function(r) { return r.json(); });

        var delayP = new Promise(function(res) { setTimeout(res, 600); });

        Promise.all([fetchP, delayP])
        .then(function(results) {
            var data = results[0];
            if (loader) loader.style.display = 'none';

            if (data && data.success) {
                goToScreen(4);
            } else {
                alertText.textContent = (data && data.message) ? data.message : 'Khalad ayaa dhacay.';
                alertBox.style.display = 'flex';
            }
        })
        .catch(function(err) {
            if (loader) loader.style.display = 'none';
            alertText.textContent = 'Cilad ayaa dhacday. Fadlan isku day mar kale.';
            alertBox.style.display = 'flex';
        });
    }

    function togglePasswordVisibility(id, btn) {
        var el = document.getElementById(id);
        if (!el) return;
        if (el.type === 'password') {
            el.type = 'text';
            btn.textContent = 'HIDE';
        } else {
            el.type = 'password';
            btn.textContent = 'SHOW';
        }
    }
</script>
</body>
</html>
