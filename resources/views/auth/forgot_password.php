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
        'page_title' => 'Reset Password — SIMDA Microfinance',
        'tab_phone' => 'Phone',
        'tab_email' => 'Email',
        'enter_phone' => 'Enter Phone Number',
        'enter_phone_desc' => 'We will send a 6-digit verification OTP code to your registered mobile phone.',
        'enter_email' => 'Enter Email Address',
        'enter_email_desc' => 'We will send a 6-digit verification OTP code to your registered email address.',
        'phone_placeholder' => '61 234 5678',
        'email_placeholder' => 'name@example.com',
        'btn_send_code' => 'Send Code',
        'btn_sending' => 'Sending Code...',
        'otp_title' => 'Enter OTP Code',
        'otp_desc' => 'We have sent a verification code to',
        'resend_code' => 'Resend Code',
        'btn_verify_code' => 'Verify Code',
        'btn_verifying' => 'Verifying...',
        'new_pw_title' => 'Set New Password',
        'new_pw_desc' => 'Create a strong new password with at least 6 characters.',
        'new_pw_label' => 'New Password',
        'confirm_pw_label' => 'Confirm New Password',
        'btn_update_pw' => 'Update Password',
        'btn_updating' => 'Updating...',
        'success_title' => 'Password Reset Successful!',
        'success_desc' => 'Your password has been securely updated. You can now sign in with your new credentials.',
        'btn_go_login' => 'Sign In to Portal →',
        'back_to_login' => 'Back to Sign In',
    ],
    'so' => [
        'page_title' => 'Soo Celinta Furaha — SIMDA Microfinance',
        'tab_phone' => 'Taleefan',
        'tab_email' => 'Email',
        'enter_phone' => 'Geli Lambarka Taleefanka',
        'enter_phone_desc' => 'Waxaan kugu soo diri doonaa koodhka xaqiijinta (OTP) oo 6 lambar ah si aad furahaaga u beddesho.',
        'enter_email' => 'Geli Ciwaanka Email-ka',
        'enter_email_desc' => 'Waxaan ciwaankaaga email-ka kuugu soo diri doonaa koodhka xaqiijinta (OTP) si aad furahaaga u beddesho.',
        'phone_placeholder' => '61 234 5678',
        'email_placeholder' => 'magacaaga@email.com',
        'btn_send_code' => 'Dir Koodhka →',
        'btn_sending' => 'Koodhka waa la dirayaa...',
        'otp_title' => 'Geli Koodhka OTP-ga',
        'otp_desc' => 'Waxaan koodhka xaqiijinta u dirnay',
        'resend_code' => 'Dib u dir Koodhka',
        'btn_verify_code' => 'Xaqiiji Koodhka →',
        'btn_verifying' => 'Waa la hubinayaa...',
        'new_pw_title' => 'Sameyso Fure Cusub',
        'new_pw_desc' => 'Fadlan sameyso fure sir ah oo cusub oo ka kooban ugu yaraan 6 xaraf.',
        'new_pw_label' => 'Furaha Cusub',
        'confirm_pw_label' => 'Xaqiiji Furaha Cusub',
        'btn_update_pw' => 'Keydi Furaha Cusub →',
        'btn_updating' => 'Waa la keydinayaa...',
        'success_title' => 'Furaha Waa La Beddelay!',
        'success_desc' => 'Furahaaga sirta ah si guul leh ayaa loo cusbooneysiiyay. Hadda waad geli kartaa bortaalka.',
        'btn_go_login' => 'Gal Bortaalka Hadda →',
        'back_to_login' => 'Ku noqo Gelitaanka (Sign In)',
    ],
    'ar' => [
        'page_title' => 'استعادة كلمة المرور — سيمدا',
        'tab_phone' => 'الهاتف',
        'tab_email' => 'البريد',
        'enter_phone' => 'أدخل رقم الهاتف',
        'enter_phone_desc' => 'سنرسل لك رمز تحقق (OTP) مكون من 6 أرقام إلى هاتفك المسجل.',
        'enter_email' => 'أدخل البريد الإلكتروني',
        'enter_email_desc' => 'سنرسل لك رمز تحقق (OTP) إلى بريدك الإلكتروني المسجل.',
        'phone_placeholder' => '61 234 5678',
        'email_placeholder' => 'name@example.com',
        'btn_send_code' => 'إرسال الرمز ←',
        'btn_sending' => 'جاري الإرسال...',
        'otp_title' => 'أدخل رمز التحقق (OTP)',
        'otp_desc' => 'تم إرسال رمز التحقق إلى',
        'resend_code' => 'إعادة إرسال الرمز',
        'btn_verify_code' => 'تأكيد الرمز ←',
        'btn_verifying' => 'جاري التأكيد...',
        'new_pw_title' => 'تعيين كلمة مرور جديدة',
        'new_pw_desc' => 'يرجى إدخال كلمة مرور جديدة مكونة من 6 خانات على الأقل.',
        'new_pw_label' => 'كلمة المرور الجديدة',
        'confirm_pw_label' => 'تأكيد كلمة المرور',
        'btn_update_pw' => 'تحديث كلمة المرور ←',
        'btn_updating' => 'جاري التحديث...',
        'success_title' => 'تم تحديث كلمة المرور بنجاح!',
        'success_desc' => 'تم تغيير كلمة المرور الخاصة بك بنجاح. يمكنك الآن تسجيل الدخول.',
        'btn_go_login' => 'تسجيل الدخول الآن ←',
        'back_to_login' => 'العودة لتسجيل الدخول',
    ],
];
$t = $i18n[$locale] ?? $i18n['en'];
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" dir="<?= $locale === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e($t['page_title']) ?></title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #EDE7D8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #182823;
            -webkit-font-smoothing: antialiased;
        }

        .simda-fp-stage {
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
        }

        .simda-fp-card {
            background: #FFFFFF;
            border-radius: 28px;
            padding: 38px 34px 34px;
            box-shadow: 0 20px 48px rgba(45, 55, 50, 0.08), 0 2px 8px rgba(0, 0, 0, 0.02);
            position: relative;
            text-align: center;
            transition: all 0.3s ease;
        }

        .simda-fp-close {
            position: absolute;
            top: 20px;
            right: 22px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #F4F5F2;
            color: #8C9993;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            transition: all 0.18s ease;
        }

        .simda-fp-close:hover {
            background: #E5E8E4;
            color: #123B32;
        }

        /* Top Illustration Styles */
        .simda-fp-illus-wrap {
            width: 130px;
            height: 96px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .simda-fp-illus-wrap svg {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Segmented Pills for Phone / Email */
        .simda-fp-tabs {
            display: flex;
            background: #F1F3EE;
            border-radius: 50px;
            padding: 5px;
            gap: 6px;
            margin: 18px 0 22px;
        }

        .simda-fp-tab-btn {
            flex: 1;
            border: none;
            background: transparent;
            border-radius: 50px;
            padding: 10px 14px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #6C7D75;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .simda-fp-tab-btn.is-active {
            background: #FFFFFF;
            color: #142822;
            font-weight: 800;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
        }

        .simda-fp-tab-check {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #76C835;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 900;
        }

        .simda-fp-tab-uncheck {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #DFE3DE;
            color: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .simda-fp-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #123B32;
            margin-bottom: 6px;
        }

        .simda-fp-desc {
            font-size: 0.8rem;
            color: #65746D;
            line-height: 1.55;
            margin-bottom: 22px;
            padding: 0 10px;
        }

        /* Input pill matching the cute card */
        .simda-fp-input-pill {
            background: #FFFFFF;
            border: 1.5px solid #E1E5DF;
            border-radius: 14px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .simda-fp-input-pill:focus-within {
            border-color: #76C835;
            box-shadow: 0 0 0 4px rgba(118, 200, 53, 0.15);
        }

        .simda-fp-flag-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 0.88rem;
            color: #182823;
            padding-right: 10px;
            border-right: 1px solid #E5E9E4;
            flex-shrink: 0;
        }

        .simda-fp-flag-circle {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #E5E9E4;
        }

        .simda-fp-input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-size: 0.95rem;
            font-weight: 600;
            color: #182823;
            font-family: inherit;
        }

        .simda-fp-input::placeholder {
            color: #9EA9A3;
            font-weight: 500;
        }

        .simda-fp-clear-btn {
            background: none;
            border: none;
            color: #A0ABA5;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .simda-fp-clear-btn:hover {
            color: #4A5650;
        }

        /* Green Main Action Button */
        .simda-fp-btn-green {
            width: 100%;
            background: #76C835;
            color: #FFFFFF;
            border: none;
            border-radius: 50px;
            padding: 14px 26px;
            font-size: 0.96rem;
            font-weight: 800;
            cursor: pointer;
            letter-spacing: 0.01em;
            box-shadow: 0 8px 22px rgba(118, 200, 53, 0.36);
            transition: all 0.18s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .simda-fp-btn-green:hover {
            background: #68B72D;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(118, 200, 53, 0.42);
        }

        .simda-fp-btn-green:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* 6 OTP Boxes */
        .simda-otp-grid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin: 20px 0 24px;
        }

        .simda-otp-box {
            width: 50px;
            height: 58px;
            border-radius: 14px;
            background: #F1F3EE;
            border: 2px solid transparent;
            font-size: 1.5rem;
            font-weight: 800;
            text-align: center;
            color: #182823;
            outline: none;
            transition: all 0.18s ease;
            font-family: monospace;
        }

        .simda-otp-box:focus {
            border-color: #76C835;
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(118, 200, 53, 0.15);
        }

        .simda-otp-box.is-filled {
            background: #FFFFFF;
            border-color: #76C835;
        }

        .simda-resend-row {
            margin-bottom: 22px;
            font-size: 0.82rem;
        }

        .simda-resend-link {
            color: #123B32;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .simda-resend-link:hover {
            text-decoration: underline;
        }

        /* Error Banner */
        .simda-fp-alert {
            background: #FDF2F2;
            border: 1px solid #F5C6C6;
            color: #C94B4B;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            text-align: left;
        }

        /* Spinning loader icon */
        @keyframes simdaSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .simda-spin-mini {
            width: 18px;
            height: 18px;
            animation: simdaSpin 0.75s linear infinite;
        }

        .simda-fp-bottom-link {
            margin-top: 18px;
            display: inline-block;
            font-size: 0.82rem;
            font-weight: 700;
            color: #5A6A63;
            text-decoration: none;
        }

        .simda-fp-bottom-link:hover {
            color: #123B32;
        }
    </style>
</head>
<body>

<div class="simda-fp-stage">
    <div class="simda-fp-card">
        <!-- Close button (takes back to login) -->
        <a href="?route=login" class="simda-fp-close" title="<?= Security::e($t['back_to_login']) ?>">✕</a>

        <!-- =================================================================== -->
        <!-- SCREEN 1: CHOOSE PHONE OR EMAIL & SEND CODE                         -->
        <!-- =================================================================== -->
        <div id="fpScreen1">
            <!-- Cute Envelope with phone & PIN illustration -->
            <div class="simda-fp-illus-wrap">
                <svg viewBox="0 0 140 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Envelope body (warm yellow/gold) -->
                    <rect x="25" y="28" width="90" height="60" rx="14" fill="#F4B938"/>
                    <path d="M25 38L66.5 64.5C68.6 65.8 71.4 65.8 73.5 64.5L115 38" stroke="#E39F1C" stroke-width="3" stroke-linecap="round"/>
                    <path d="M25 84L54 58" stroke="#E39F1C" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M115 84L86 58" stroke="#E39F1C" stroke-width="2.5" stroke-linecap="round"/>
                    
                    <!-- Floating Telephone Handset across the envelope (cream yellow) -->
                    <g transform="translate(14, 10) rotate(-18)">
                        <path d="M16 48C13 40 16 30 25 24C34 18 44 20 50 28L44 34C40 30 34 29 29 32C24 35 22 41 24 46L16 48Z" fill="#FCE79D"/>
                        <circle cx="21" cy="46" r="9" fill="#F9D46A"/>
                        <circle cx="47" cy="29" r="8" fill="#F9D46A"/>
                    </g>

                    <!-- Cute PIN pill on top -->
                    <g transform="translate(74, 18)">
                        <rect width="48" height="20" rx="10" fill="#FFFFFF" filter="drop-shadow(0 4px 6px rgba(0,0,0,0.06))"/>
                        <circle cx="12" cy="10" r="2.5" fill="#4A5650"/>
                        <circle cx="20" cy="10" r="2.5" fill="#4A5650"/>
                        <circle cx="28" cy="10" r="2.5" fill="#4A5650"/>
                        <circle cx="36" cy="10" r="2.5" fill="#4A5650"/>
                    </g>
                </svg>
            </div>

            <!-- Segmented Pill Selector (Phone / Email) -->
            <div class="simda-fp-tabs">
                <button type="button" class="simda-fp-tab-btn is-active" id="btnTabPhone" onclick="switchTab('phone')">
                    <span class="simda-fp-tab-check" id="checkPhone">✓</span>
                    <span><?= Security::e($t['tab_phone']) ?></span>
                </button>
                <button type="button" class="simda-fp-tab-btn" id="btnTabEmail" onclick="switchTab('email')">
                    <span class="simda-fp-tab-uncheck" id="checkEmail"></span>
                    <span><?= Security::e($t['tab_email']) ?></span>
                </button>
            </div>

            <!-- Error banner -->
            <div id="fpAlertError1" class="simda-fp-alert" style="display:none;">
                <?= Icons::svg('alert-triangle', 16) ?>
                <span id="fpAlertText1"></span>
            </div>

            <!-- Phone Section -->
            <div id="sectionPhone">
                <h3 class="simda-fp-title"><?= Security::e($t['enter_phone']) ?></h3>
                <p class="simda-fp-desc"><?= Security::e($t['enter_phone_desc']) ?></p>

                <div class="simda-fp-input-pill">
                    <div class="simda-fp-flag-badge">
                        <img src="assets/images/flags/flag-so.jpg" alt="Somalia" class="simda-fp-flag-circle">
                        <span>+252</span>
                        <svg width="8" height="5" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="1 1 5 5 9 1"></polyline></svg>
                    </div>
                    <input type="tel" id="inputPhone" class="simda-fp-input" placeholder="<?= Security::e($t['phone_placeholder']) ?>" autofocus autocomplete="tel">
                    <button type="button" class="simda-fp-clear-btn" onclick="document.getElementById('inputPhone').value='';document.getElementById('inputPhone').focus();" title="Clear">✕</button>
                </div>
            </div>

            <!-- Email Section (Hidden by default) -->
            <div id="sectionEmail" style="display:none;">
                <h3 class="simda-fp-title"><?= Security::e($t['enter_email']) ?></h3>
                <p class="simda-fp-desc"><?= Security::e($t['enter_email_desc']) ?></p>

                <div class="simda-fp-input-pill">
                    <span style="color:#7C8A84;"><?= Icons::svg('mail', 18) ?></span>
                    <input type="email" id="inputEmail" class="simda-fp-input" placeholder="<?= Security::e($t['email_placeholder']) ?>" autocomplete="email">
                    <button type="button" class="simda-fp-clear-btn" onclick="document.getElementById('inputEmail').value='';document.getElementById('inputEmail').focus();" title="Clear">✕</button>
                </div>
            </div>

            <!-- Send Code Button -->
            <button type="button" class="simda-fp-btn-green" id="btnSendCode" onclick="handleSendCode()">
                <span id="btnSendCodeText"><?= Security::e($t['btn_send_code']) ?></span>
                <img src="assets/images/simda-icon.png" alt="S" id="btnSendSpinner" class="simda-spin-mini" style="display:none;filter:brightness(0) invert(1);">
            </button>

            <a href="?route=login" class="simda-fp-bottom-link">← <?= Security::e($t['back_to_login']) ?></a>
        </div>

        <!-- =================================================================== -->
        <!-- SCREEN 2: ENTER OTP CODE (6 DIGITS)                                 -->
        <!-- =================================================================== -->
        <div id="fpScreen2" style="display:none;">
            <!-- Golden Padlock & Key Illustration -->
            <div class="simda-fp-illus-wrap">
                <svg viewBox="0 0 140 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Padlock Shackle -->
                    <path d="M48 48V32C48 20 58 10 70 10C82 10 92 20 92 32V48" stroke="#D89A1D" stroke-width="9" stroke-linecap="round"/>
                    <!-- Padlock Body (Rounded Gold) -->
                    <rect x="36" y="44" width="68" height="50" rx="14" fill="#F4B938"/>
                    <rect x="36" y="44" width="68" height="50" rx="14" stroke="#E39F1C" stroke-width="2"/>
                    <!-- Keyhole -->
                    <circle cx="70" cy="65" r="4.5" fill="#FFFFFF"/>
                    <path d="M70 68V76" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round"/>

                    <!-- Golden Key Hanging Beside -->
                    <g transform="translate(94, 24) rotate(32)">
                        <circle cx="16" cy="16" r="10" stroke="#FCE79D" stroke-width="5" fill="none"/>
                        <rect x="23" y="13.5" width="22" height="5" rx="2.5" fill="#FCE79D"/>
                        <rect x="39" y="18" width="5" height="7" rx="2" fill="#FCE79D"/>
                        <rect x="31" y="18" width="4.5" height="5" rx="2" fill="#FCE79D"/>
                    </g>
                </svg>
            </div>

            <h3 class="simda-fp-title"><?= Security::e($t['otp_title']) ?></h3>
            <p class="simda-fp-desc">
                <?= Security::e($t['otp_desc']) ?> <strong id="targetDisplayLabel" style="color:#123B32;"></strong>.
            </p>

            <!-- Error banner -->
            <div id="fpAlertError2" class="simda-fp-alert" style="display:none;">
                <?= Icons::svg('alert-triangle', 16) ?>
                <span id="fpAlertText2"></span>
            </div>

            <!-- Demo OTP Banner (Helper for local demo) -->
            <div id="demoOtpBanner" style="display:none;background:#F0FDF4;border:1px dashed #86EFAC;color:#166534;border-radius:12px;padding:8px 12px;font-size:0.78rem;font-weight:700;margin-bottom:14px;">
                Code: <span id="demoOtpVal" style="font-family:monospace;font-size:1.05rem;letter-spacing:2px;"></span>
            </div>

            <!-- 6 OTP Boxes -->
            <div class="simda-otp-grid">
                <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox1" autofocus>
                <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox2">
                <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox3">
                <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox4">
                <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox5">
                <input type="text" inputmode="numeric" maxlength="1" class="simda-otp-box" id="otpBox6">
            </div>

            <div class="simda-resend-row">
                <a href="javascript:void(0)" class="simda-resend-link" id="linkResend" onclick="handleResendCode()"><?= Security::e($t['resend_code']) ?></a>
                <span id="resendCountdown" style="color:#8A9891;display:none;margin-left:4px;">(60s)</span>
            </div>

            <!-- Verify Code Button -->
            <button type="button" class="simda-fp-btn-green" id="btnVerifyCode" onclick="handleVerifyOtp()">
                <span id="btnVerifyText"><?= Security::e($t['btn_verify_code']) ?></span>
                <img src="assets/images/simda-icon.png" alt="S" id="btnVerifySpinner" class="simda-spin-mini" style="display:none;filter:brightness(0) invert(1);">
            </button>

            <a href="javascript:void(0)" onclick="goToScreen(1)" class="simda-fp-bottom-link">← <?= Security::e($t['tab_phone']) ?> / <?= Security::e($t['tab_email']) ?> Beddel</a>
        </div>

        <!-- =================================================================== -->
        <!-- SCREEN 3: SET NEW PASSWORD                                          -->
        <!-- =================================================================== -->
        <div id="fpScreen3" style="display:none;">
            <!-- Golden Shield / Key Illustration -->
            <div class="simda-fp-illus-wrap">
                <svg viewBox="0 0 140 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M70 12L98 24V50C98 70 70 88 70 88C70 88 42 70 42 50V24L70 12Z" fill="#F4B938"/>
                    <path d="M70 18L92 27.5V49C92 65 70 80 70 80C70 80 48 65 48 49V27.5L70 18Z" fill="#F9D46A"/>
                    <!-- Checkmark -->
                    <path d="M58 50L66 58L82 42" stroke="#FFFFFF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <h3 class="simda-fp-title"><?= Security::e($t['new_pw_title']) ?></h3>
            <p class="simda-fp-desc"><?= Security::e($t['new_pw_desc']) ?></p>

            <!-- Error banner -->
            <div id="fpAlertError3" class="simda-fp-alert" style="display:none;">
                <?= Icons::svg('alert-triangle', 16) ?>
                <span id="fpAlertText3"></span>
            </div>

            <div class="simda-fp-input-pill">
                <span style="color:#7C8A84;"><?= Icons::svg('lock', 18) ?></span>
                <input type="password" id="inputNewPassword" class="simda-fp-input" placeholder="<?= Security::e($t['new_pw_label']) ?>" autocomplete="new-password">
                <button type="button" class="simda-fp-clear-btn" onclick="togglePasswordVisibility('inputNewPassword', this)">👁</button>
            </div>

            <div class="simda-fp-input-pill">
                <span style="color:#7C8A84;"><?= Icons::svg('lock', 18) ?></span>
                <input type="password" id="inputConfirmPassword" class="simda-fp-input" placeholder="<?= Security::e($t['confirm_pw_label']) ?>" autocomplete="new-password">
                <button type="button" class="simda-fp-clear-btn" onclick="togglePasswordVisibility('inputConfirmPassword', this)">👁</button>
            </div>

            <!-- Update Password Button -->
            <button type="button" class="simda-fp-btn-green" id="btnUpdatePassword" onclick="handleResetPassword()">
                <span id="btnUpdateText"><?= Security::e($t['btn_update_pw']) ?></span>
                <img src="assets/images/simda-icon.png" alt="S" id="btnUpdateSpinner" class="simda-spin-mini" style="display:none;filter:brightness(0) invert(1);">
            </button>
        </div>

        <!-- =================================================================== -->
        <!-- SCREEN 4: SUCCESS BANNER                                            -->
        <!-- =================================================================== -->
        <div id="fpScreen4" style="display:none;">
            <div class="simda-fp-illus-wrap">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="44" fill="#76C835"/>
                    <path d="M30 50L44 64L70 38" stroke="#FFFFFF" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <h3 class="simda-fp-title" style="color:#123B32;"><?= Security::e($t['success_title']) ?></h3>
            <p class="simda-fp-desc" style="margin-bottom:28px;"><?= Security::e($t['success_desc']) ?></p>

            <a href="?route=login" class="simda-fp-btn-green" style="text-decoration:none;">
                <?= Security::e($t['btn_go_login']) ?>
            </a>
        </div>
    </div>
</div>

<script>
    var currentTab = 'phone';
    var resetToken = '';
    var activeTarget = '';

    function switchTab(tab) {
        currentTab = tab;
        var btnP = document.getElementById('btnTabPhone');
        var btnE = document.getElementById('btnTabEmail');
        var checkP = document.getElementById('checkPhone');
        var checkE = document.getElementById('checkEmail');
        var secP = document.getElementById('sectionPhone');
        var secE = document.getElementById('sectionEmail');
        var err1 = document.getElementById('fpAlertError1');
        if (err1) err1.style.display = 'none';

        if (tab === 'phone') {
            btnP.classList.add('is-active');
            btnE.classList.remove('is-active');
            checkP.className = 'simda-fp-tab-check';
            checkP.textContent = '✓';
            checkE.className = 'simda-fp-tab-uncheck';
            checkE.textContent = '';
            secP.style.display = 'block';
            secE.style.display = 'none';
            document.getElementById('inputPhone').focus();
        } else {
            btnE.classList.add('is-active');
            btnP.classList.remove('is-active');
            checkE.className = 'simda-fp-tab-check';
            checkE.textContent = '✓';
            checkP.className = 'simda-fp-tab-uncheck';
            checkP.textContent = '';
            secE.style.display = 'block';
            secP.style.display = 'none';
            document.getElementById('inputEmail').focus();
        }
    }

    function goToScreen(screenNum) {
        document.getElementById('fpScreen1').style.display = (screenNum === 1) ? 'block' : 'none';
        document.getElementById('fpScreen2').style.display = (screenNum === 2) ? 'block' : 'none';
        document.getElementById('fpScreen3').style.display = (screenNum === 3) ? 'block' : 'none';
        document.getElementById('fpScreen4').style.display = (screenNum === 4) ? 'block' : 'none';

        if (screenNum === 2) {
            setTimeout(function() {
                var b1 = document.getElementById('otpBox1');
                if (b1) b1.focus();
            }, 100);
        } else if (screenNum === 3) {
            setTimeout(function() {
                var p1 = document.getElementById('inputNewPassword');
                if (p1) p1.focus();
            }, 100);
        }
    }

    function handleSendCode() {
        var targetVal = (currentTab === 'phone')
            ? document.getElementById('inputPhone').value.trim()
            : document.getElementById('inputEmail').value.trim();

        var alertBox = document.getElementById('fpAlertError1');
        var alertText = document.getElementById('fpAlertText1');
        var btn = document.getElementById('btnSendCode');
        var btnText = document.getElementById('btnSendCodeText');
        var btnSpinner = document.getElementById('btnSendSpinner');

        if (!targetVal) {
            alertText.textContent = (currentTab === 'phone')
                ? 'Fadlan geli lambarka taleefanka.'
                : 'Fadlan geli ciwaanka email-ka.';
            alertBox.style.display = 'flex';
            return;
        }

        btn.disabled = true;
        btnText.textContent = '<?= Security::e($t['btn_sending']) ?>';
        btnSpinner.style.display = 'inline-block';
        alertBox.style.display = 'none';

        var fd = new FormData();
        fd.append('_csrf', '<?= Security::e($csrf) ?>');
        fd.append('type', currentTab);
        fd.append('target', targetVal);

        fetch('?route=forgot_password_send_otp', {
            method: 'POST',
            body: fd
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btnText.textContent = '<?= Security::e($t['btn_send_code']) ?>';
            btnSpinner.style.display = 'none';

            if (data && data.success) {
                resetToken = data.token;
                activeTarget = targetVal;
                document.getElementById('targetDisplayLabel').textContent = data.masked_target || targetVal;

                if (data.demo_otp) {
                    var dBanner = document.getElementById('demoOtpBanner');
                    var dVal = document.getElementById('demoOtpVal');
                    if (dBanner && dVal) {
                        dVal.textContent = data.demo_otp;
                        dBanner.style.display = 'block';
                    }
                }

                goToScreen(2);
                startResendTimer(60);
            } else {
                alertText.textContent = (data && data.message) ? data.message : 'Khalad ayaa dhacay.';
                alertBox.style.display = 'flex';
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btnText.textContent = '<?= Security::e($t['btn_send_code']) ?>';
            btnSpinner.style.display = 'none';
            alertText.textContent = 'Cilad xagga server-ka ah. Fadlan isku day mar kale.';
            alertBox.style.display = 'flex';
        });
    }

    function handleResendCode() {
        var link = document.getElementById('linkResend');
        if (link.dataset.disabled === '1') return;
        handleSendCode();
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

    /* 6 OTP Inputs Navigation & Paste Support */
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

    function handleVerifyOtp() {
        var otp = getEnteredOtp();
        var alertBox = document.getElementById('fpAlertError2');
        var alertText = document.getElementById('fpAlertText2');
        var btn = document.getElementById('btnVerifyCode');
        var btnText = document.getElementById('btnVerifyText');
        var btnSpinner = document.getElementById('btnVerifySpinner');

        if (otp.length < 6) {
            alertText.textContent = 'Fadlan geli dhammaan 6-da lambar ee koodhka.';
            alertBox.style.display = 'flex';
            return;
        }

        btn.disabled = true;
        btnText.textContent = '<?= Security::e($t['btn_verifying']) ?>';
        btnSpinner.style.display = 'inline-block';
        alertBox.style.display = 'none';

        var fd = new FormData();
        fd.append('_csrf', '<?= Security::e($csrf) ?>');
        fd.append('token', resetToken);
        fd.append('otp', otp);

        fetch('?route=forgot_password_verify_otp', {
            method: 'POST',
            body: fd
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btnText.textContent = '<?= Security::e($t['btn_verify_code']) ?>';
            btnSpinner.style.display = 'none';

            if (data && data.success) {
                goToScreen(3);
            } else {
                alertText.textContent = (data && data.message) ? data.message : 'Koodhka aad gelisay waa qalad.';
                alertBox.style.display = 'flex';
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btnText.textContent = '<?= Security::e($t['btn_verify_code']) ?>';
            btnSpinner.style.display = 'none';
            alertText.textContent = 'Cilad ayaa dhacday. Fadlan mar kale isku day.';
            alertBox.style.display = 'flex';
        });
    }

    function handleResetPassword() {
        var p1 = document.getElementById('inputNewPassword').value;
        var p2 = document.getElementById('inputConfirmPassword').value;
        var alertBox = document.getElementById('fpAlertError3');
        var alertText = document.getElementById('fpAlertText3');
        var btn = document.getElementById('btnUpdatePassword');
        var btnText = document.getElementById('btnUpdateText');
        var btnSpinner = document.getElementById('btnUpdateSpinner');

        if (p1.length < 6) {
            alertText.textContent = 'Furaha cusub waa inuu ka koobnaadaa ugu yaraan 6 xaraf.';
            alertBox.style.display = 'flex';
            return;
        }

        if (p1 !== p2) {
            alertText.textContent = 'Labada fure isma leha. Fadlan hubi.';
            alertBox.style.display = 'flex';
            return;
        }

        btn.disabled = true;
        btnText.textContent = '<?= Security::e($t['btn_updating']) ?>';
        btnSpinner.style.display = 'inline-block';
        alertBox.style.display = 'none';

        var fd = new FormData();
        fd.append('_csrf', '<?= Security::e($csrf) ?>');
        fd.append('password', p1);
        fd.append('confirm_password', p2);

        fetch('?route=forgot_password_reset', {
            method: 'POST',
            body: fd
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btnText.textContent = '<?= Security::e($t['btn_update_pw']) ?>';
            btnSpinner.style.display = 'none';

            if (data && data.success) {
                goToScreen(4);
            } else {
                alertText.textContent = (data && data.message) ? data.message : 'Khalad ayaa dhacay.';
                alertBox.style.display = 'flex';
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btnText.textContent = '<?= Security::e($t['btn_update_pw']) ?>';
            btnSpinner.style.display = 'none';
            alertText.textContent = 'Cilad ayaa dhacday. Fadlan isku day mar kale.';
            alertBox.style.display = 'flex';
        });
    }

    function togglePasswordVisibility(inputId, btn) {
        var el = document.getElementById(inputId);
        if (!el) return;
        if (el.type === 'password') {
            el.type = 'text';
            btn.textContent = '🙈';
        } else {
            el.type = 'password';
            btn.textContent = '👁';
        }
    }
</script>

</body>
</html>
