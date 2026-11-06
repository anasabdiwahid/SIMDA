<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$locale = Lang::locale();
$t = static fn (string $so, string $en, string $ar): string => match ($locale) {
    'so' => $so,
    'ar' => $ar,
    default => $en,
};

$initials = Security::initials((string) $account['full_name']);
$roleName = $locale === 'so' ? $account['role_name_so'] : $account['role_name_en'];

$tools = [
    ['route' => 'products', 'icon' => 'layers', 'label' => $t('Alaabooyinka', 'Products', 'المنتجات')],
    ['route' => 'reports', 'icon' => 'bar-chart', 'label' => 'Reports'],
    ['route' => 'sms_outbox', 'icon' => 'message-square', 'label' => $t('Fariimaha', 'Messages', 'الرسائل')],
    ['route' => 'settings', 'icon' => 'settings', 'label' => 'Settings'],
    ['route' => 'permissions', 'icon' => 'shield', 'label' => $t('Awoodaha', 'Permissions', 'الصلاحيات')],
    ['route' => 'audit_logs', 'icon' => 'activity', 'label' => 'Audit Logs'],
];
?>
<div class="adm">
    <header class="adm-head">
        <h1>Profile</h1>
        <p><?= Security::e($t('Macluumaadkaaga iyo amniga akoonkaaga.', 'Your details and account security.', 'بياناتك وأمان حسابك.')) ?></p>
    </header>

    <section class="prf-identity">
        <span class="adm-avatar dash-tone-danger"><?= Security::e($initials) ?></span>
        <div class="prf-identity-text">
            <strong><?= Security::e($account['full_name']) ?></strong>
            <span class="adm-secondary"><?= Security::e($roleName) ?> · @<?= Security::e($account['username']) ?></span>
            <span class="adm-secondary" dir="ltr"><?= Security::e($account['email']) ?></span>
        </div>
    </section>

    <div class="prf-grid">
        <form method="POST" action="?route=profile_update" class="adm-form">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
            <div class="adm-form-head">
                <h3><?= Security::e($t('Macluumaadka Guud', 'General Details', 'البيانات العامة')) ?></h3>
            </div>
            <div class="form-group">
                <label for="prfName"><?= Security::e($t('Magaca Buuxa', 'Full Name', 'الاسم الكامل')) ?></label>
                <input type="text" id="prfName" name="full_name" class="form-control" required maxlength="120" value="<?= Security::e($account['full_name']) ?>">
            </div>
            <div class="form-group">
                <label for="prfPhone"><?= Security::e($t('Telefoonka', 'Phone', 'الهاتف')) ?></label>
                <input type="text" id="prfPhone" name="phone" class="form-control" required maxlength="30" dir="ltr" value="<?= Security::e($account['phone']) ?>">
            </div>
            <small class="adm-hint"><?= Security::e($t('Isbeddelkan OTP uma baahna.', 'These changes do not need an OTP.', 'لا تحتاج هذه التغييرات إلى رمز تحقق.')) ?></small>
            <div class="adm-form-actions">
                <button type="submit" class="btn btn-primary btn-sm"><?= Icons::svg('check-circle', 14) ?> <span><?= Security::e($t('Keydi', 'Save', 'حفظ')) ?></span></button>
            </div>
        </form>

        <?php if ($otp !== null): ?>
            <?php
            $isEmailChange = $otp['purpose'] === 'email';
            $waitSeconds = max(0, $otpCooldown - (time() - (int) $otp['sent_at']));
            $canResend = (int) $otp['resends'] < $otpMaxResends;
            ?>
            <div class="adm-form prf-otp">
                <div class="adm-form-head">
                    <h3><?= Security::e($isEmailChange ? $t('Xaqiiji Email-ka Cusub', 'Confirm New Email', 'تأكيد البريد الجديد') : $t('Xaqiiji Password-ka Cusub', 'Confirm New Password', 'تأكيد كلمة المرور الجديدة')) ?></h3>
                </div>
                <p class="prf-otp-text">
                    <?= Security::e($t('Geli koodhka 6-da lambar ah ee loo diray', 'Enter the 6-digit code sent to', 'أدخل الرمز المكون من 6 أرقام المرسل إلى')) ?>
                    <strong dir="ltr"><?= Security::e($otp['target']) ?></strong>.
                    <?= Security::e($t('Wuxuu dhacayaa 10 daqiiqo gudahood. Isbeddelku ma dhaqan galo ilaa la xaqiijiyo.', 'It expires within 10 minutes. Nothing changes until you confirm.', 'تنتهي صلاحيته خلال 10 دقائق. لن يتغير شيء حتى تقوم بالتأكيد.')) ?>
                </p>
                <form method="POST" action="?route=profile_otp_verify" class="prf-otp-entry">
                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                    <label for="prfOtp" class="dash-sr">OTP</label>
                    <input type="text" id="prfOtp" name="otp_code" class="form-control prf-otp-input" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" dir="ltr" autofocus>
                    <button type="submit" class="btn btn-primary btn-sm"><?= Security::e($t('Xaqiiji', 'Confirm', 'تأكيد')) ?></button>
                </form>
                <div class="adm-form-actions">
                    <?php if ($canResend): ?>
                        <form method="POST" action="?route=profile_otp_resend" class="adm-inline-form">
                            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                            <button type="submit" class="btn btn-outline btn-sm" id="prfResend" data-wait="<?= (int) $waitSeconds ?>" <?= $waitSeconds > 0 ? 'disabled' : '' ?>><?= Icons::svg('refresh', 14) ?> <span><?= Security::e($t('Dib u dir koodhka', 'Resend code', 'إعادة إرسال الرمز')) ?></span></button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="?route=profile_otp_cancel" class="adm-inline-form">
                        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                        <button type="submit" class="btn btn-outline btn-sm"><?= Security::e($t('Ka noqo', 'Cancel', 'إلغاء')) ?></button>
                    </form>
                </div>
            </div>
            <script>
            (function () {
                var btn = document.getElementById('prfResend');
                if (btn && +btn.dataset.wait > 0) {
                    setTimeout(function () { btn.disabled = false; }, +btn.dataset.wait * 1000);
                }
            })();
            </script>
        <?php else: ?>
            <form method="POST" action="?route=profile_email_request" class="adm-form">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <div class="adm-form-head">
                    <h3><?= Security::e($t('Beddel Email-ka', 'Change Email', 'تغيير البريد الإلكتروني')) ?></h3>
                </div>
                <div class="form-group">
                    <label for="prfNewEmail"><?= Security::e($t('Email-ka Cusub', 'New Email', 'البريد الجديد')) ?></label>
                    <input type="email" id="prfNewEmail" name="new_email" class="form-control" required dir="ltr" autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="prfEmailPw"><?= Security::e($t('Password-ka Hadda', 'Current Password', 'كلمة المرور الحالية')) ?></label>
                    <input type="password" id="prfEmailPw" name="current_password" class="form-control" required autocomplete="current-password">
                </div>
                <small class="adm-hint"><?= Security::e($t('OTP waxaa loo dirayaa email-ka cusub. Marka la xaqiijiyo, email-kii hore waxaa loo dirayaa ogeysiis.', 'The OTP goes to the new email. Once confirmed, your old email receives a notice.', 'يُرسل الرمز إلى البريد الجديد. بعد التأكيد يصل إشعار إلى بريدك القديم.')) ?></small>
                <div class="adm-form-actions">
                    <button type="submit" class="btn btn-primary btn-sm"><?= Icons::svg('send', 14) ?> <span><?= Security::e($t('U dir OTP', 'Send OTP', 'إرسال الرمز')) ?></span></button>
                </div>
            </form>

            <form method="POST" action="?route=profile_password_request" class="adm-form">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <div class="adm-form-head">
                    <h3><?= Security::e($t('Beddel Password-ka', 'Change Password', 'تغيير كلمة المرور')) ?></h3>
                </div>
                <div class="form-group">
                    <label for="prfCurrentPw"><?= Security::e($t('Password-ka Hadda', 'Current Password', 'كلمة المرور الحالية')) ?></label>
                    <input type="password" id="prfCurrentPw" name="current_password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label for="prfNewPw"><?= Security::e($t('Password-ka Cusub', 'New Password', 'كلمة المرور الجديدة')) ?></label>
                    <input type="password" id="prfNewPw" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="prfConfirmPw"><?= Security::e($t('Ku celi Password-ka Cusub', 'Repeat New Password', 'أعد كتابة كلمة المرور الجديدة')) ?></label>
                    <input type="password" id="prfConfirmPw" name="confirm_password" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
                <small class="adm-hint"><?= Security::e($t('OTP waxaa loo dirayaa', 'The OTP goes to', 'يُرسل الرمز إلى')) ?> <span dir="ltr"><?= Security::e($account['email']) ?></span>.</small>
                <div class="adm-form-actions">
                    <button type="submit" class="btn btn-primary btn-sm"><?= Icons::svg('send', 14) ?> <span><?= Security::e($t('U dir OTP', 'Send OTP', 'إرسال الرمز')) ?></span></button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <section class="adm-form">
        <div class="adm-form-head">
            <h3><?= Security::e($t('Qalabka Nidaamka', 'System Tools', 'أدوات النظام')) ?></h3>
        </div>
        <nav class="prf-tools">
            <?php foreach ($tools as $tool): ?>
                <a href="?route=<?= Security::e($tool['route']) ?>" class="prf-tool"><?= Icons::svg($tool['icon'], 16) ?> <span><?= Security::e($tool['label']) ?></span></a>
            <?php endforeach; ?>
        </nav>
    </section>
</div>
