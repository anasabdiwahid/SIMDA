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
$mustChange = !empty($user['must_change_password']);
?>
<div class="adm">
    <header class="adm-head">
        <h1><?= Security::e($t('Beddel Password-ka', 'Change Password', 'تغيير كلمة المرور')) ?></h1>
        <p><?= Security::e($t('Ilaali akoonkaaga adigoo dooranaya password adiga kuu gaar ah.', 'Keep your account safe with a password only you know.', 'احمِ حسابك بكلمة مرور لا يعرفها غيرك.')) ?></p>
    </header>

    <?php if ($mustChange): ?>
        <div class="alert alert-warning acpw-warn">
            <?= Icons::svg('alert-triangle', 18) ?>
            <span><?= Security::e($t(
                'Weli waxaad isticmaalaysaa password-ka default-ka ah. Si dhaqso ah u beddel si qof kale uusan akoonkaaga u gelin.',
                'You are still using the default password. Change it now so nobody else can open your account.',
                'ما زلت تستخدم كلمة المرور الافتراضية. غيّرها الآن حتى لا يتمكن أحد غيرك من فتح حسابك.'
            )) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="?route=account_password_save" class="adm-form acpw-form" autocomplete="off">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <div class="form-group">
            <label for="acpwCurrent"><?= Security::e($t('Password-ka hadda', 'Current password', 'كلمة المرور الحالية')) ?></label>
            <input type="password" id="acpwCurrent" name="current_password" class="form-control" required autocomplete="current-password">
            <?php if ($mustChange): ?>
                <small class="adm-hint"><?= Security::e($t('Kani waa password-kii lagu siiyay markii lagu diiwaangeliyay.', 'This is the password you were given when you registered.', 'هذه هي كلمة المرور التي حصلت عليها عند التسجيل.')) ?></small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="acpwNew"><?= Security::e($t('Password-ka cusub', 'New password', 'كلمة المرور الجديدة')) ?></label>
            <input type="password" id="acpwNew" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
            <small class="adm-hint"><?= Security::e($t('Ugu yaraan 8 xaraf. Isku dar xarfo, lambarro iyo calaamado.', 'At least 8 characters. Mix letters, numbers and symbols.', '8 أحرف على الأقل. امزج بين الحروف والأرقام والرموز.')) ?></small>
        </div>
        <div class="form-group">
            <label for="acpwConfirm"><?= Security::e($t('Ku celi password-ka cusub', 'Repeat new password', 'أعد كتابة كلمة المرور الجديدة')) ?></label>
            <input type="password" id="acpwConfirm" name="confirm_password" class="form-control" required minlength="8" autocomplete="new-password">
        </div>
        <div class="adm-form-actions">
            <button type="submit" class="btn btn-primary"><?= Icons::svg('shield', 15) ?> <span><?= Security::e($t('Keydi password-ka cusub', 'Save new password', 'حفظ كلمة المرور الجديدة')) ?></span></button>
        </div>
    </form>
</div>
