<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isSo = Lang::locale() === 'so';
?>
<div class="adm">
    <header class="adm-head adm-page-head">
        <div>
            <h1><?= Security::e(Lang::get('menu_register_client')) ?></h1>
            <p><?= $isSo ? 'Macmiil kasta wuxuu u baahan yahay aqoonsi iyo telefoon u gaar ah, laan, iyo ugu yaraan hal damiin. Wuxuu ku bilaabmayaa 600 dhibcood oo credit ah.' : 'Each client needs a unique ID and phone number, a branch and at least one guarantor. They start with a credit score of 600.' ?></p>
        </div>
        <div class="adm-head-actions">
            <a href="?route=clients" class="btn btn-sm btn-outline"><?= $isSo ? 'Ku noqo Macaamiisha' : 'Back to Clients' ?></a>
        </div>
    </header>

    <form method="POST" action="?route=client_register_submit" enctype="multipart/form-data" class="reg-form" data-confirm="<?= $isSo ? 'Ma diiwaangelinaysaa macmiilkan iyo damiinkiisa?' : 'Register this client and their guarantor?' ?>">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

        <section class="adm-form">
            <div class="reg-step">
                <span class="reg-step-num">1</span>
                <div>
                    <h3><?= $isSo ? 'Macluumaadka Macmiilka' : 'Client Details' ?></h3>
                    <p><?= $isSo ? 'Aqoonsiga, xiriirka iyo goobta.' : 'Identity, contact and location.' ?></p>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label><?= $isSo ? 'Magaca Buuxa *' : 'Full Name *' ?></label>
                    <input type="text" name="full_name" class="form-control" required placeholder="Faadumo Maxamed Cali">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Nooca Aqoonsiga *' : 'ID Type *' ?></label>
                    <select name="id_type" class="form-control" required>
                        <option value="national_id"><?= $isSo ? 'Aqoonsi Qaran' : 'National ID' ?></option>
                        <option value="passport"><?= $isSo ? 'Baasaboor' : 'Passport' ?></option>
                        <option value="voter_card"><?= $isSo ? 'Kaarka Codbixinta' : 'Voter Card' ?></option>
                        <option value="business_license"><?= $isSo ? 'Ruqsadda Ganacsiga' : 'Business License' ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Lambarka Aqoonsiga *' : 'ID Number *' ?></label>
                    <input type="text" name="id_number" class="form-control num-tabular" required placeholder="SOM-ID-884120">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Telefoonka *' : 'Phone Number *' ?></label>
                    <input type="text" name="phone" class="form-control num-tabular" required placeholder="+252615778899" dir="ltr">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Email-ka Macmiilka (Halkan waxaa loogu diri doonaa Rasiidka) *' : 'Client Email (Receipts will be sent here) *' ?></label>
                    <input type="email" name="email" class="form-control" required placeholder="client@gmail.com">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Magaalada *' : 'City *' ?></label>
                    <input type="text" name="city" class="form-control" required value="Mogadishu">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Degmada *' : 'District *' ?></label>
                    <input type="text" name="district" class="form-control" required placeholder="Hodan">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Cinwaanka *' : 'Address *' ?></label>
                    <input type="text" name="address" class="form-control" required placeholder="<?= $isSo ? 'Wadada ama lambarka dukaanka' : 'Street or market stall number' ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Laanta *' : 'Branch *' ?></label>
                    <select name="branch_id" class="form-control" required>
                        <?php foreach ($branches as $b): ?>
                            <?php if (($user['role_slug'] ?? '') === 'admin' || (int) ($user['branch_id'] ?? 0) === (int) $b['id']): ?>
                                <option value="<?= (int) $b['id'] ?>"><?= Security::e($b['name'] . ' (' . $b['code'] . ')') ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <section class="adm-form">
            <div class="reg-step">
                <span class="reg-step-num">2</span>
                <div>
                    <h3><?= $isSo ? 'Ganacsiga' : 'Business' ?></h3>
                    <p><?= $isSo ? 'Ganacsiga iyo dakhliga bishii.' : 'Business and monthly income.' ?></p>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label><?= $isSo ? 'Nooca Ganacsiga *' : 'Business Type *' ?></label>
                    <input type="text" name="business_type" class="form-control" required placeholder="<?= $isSo ? 'Dukaan bagaash' : 'Retail shop' ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Dakhliga Bishii ($) *' : 'Monthly Income ($) *' ?></label>
                    <input type="number" name="monthly_income" class="form-control num-tabular" step="0.01" min="50" required value="1200.00">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Sawirka Macmiilka (Ikhtiyaari)' : 'Client Photo (Optional)' ?></label>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
        </section>

        <section class="adm-form">
            <div class="reg-step">
                <span class="reg-step-num">3</span>
                <div>
                    <h3><?= $isSo ? 'Damiinka' : 'Guarantor' ?></h3>
                    <p><?= $isSo ? 'Macmiil kasta waa inuu leeyahay damiin.' : 'Every client must have a guarantor.' ?></p>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label><?= $isSo ? 'Magaca Damiinka *' : 'Guarantor Name *' ?></label>
                    <input type="text" name="guarantor_full_name" class="form-control" required placeholder="Cabdullaahi Maxamed Cali">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Telefoonka Damiinka *' : 'Guarantor Phone *' ?></label>
                    <input type="text" name="guarantor_phone" class="form-control num-tabular" required placeholder="+252615443322" dir="ltr">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Aqoonsiga Damiinka *' : 'Guarantor ID Number *' ?></label>
                    <input type="text" name="guarantor_id_number" class="form-control num-tabular" required placeholder="SOM-GID-551900">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Xiriirka uu la leeyahay *' : 'Relationship to Client *' ?></label>
                    <input type="text" name="guarantor_relationship" class="form-control" required placeholder="<?= $isSo ? 'Walaal / Lammaane ganacsi' : 'Brother / Business partner' ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Shaqada Damiinka' : 'Guarantor Occupation' ?></label>
                    <input type="text" name="guarantor_occupation" class="form-control" placeholder="<?= $isSo ? 'Ganacsade jumlo' : 'Wholesale merchant' ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Dakhliga Damiinka ($)' : 'Guarantor Income ($)' ?></label>
                    <input type="number" name="guarantor_income" class="form-control num-tabular" step="0.01" value="1800.00">
                </div>
            </div>
        </section>

        <div class="adm-form-actions">
            <button type="submit" class="btn btn-primary btn-sm"><?= Icons::svg('check-circle', 14) ?> <span><?= $isSo ? 'Diiwaangeli Macmiilka' : 'Register Client' ?></span></button>
            <a href="?route=clients" class="btn btn-outline btn-sm"><?= $isSo ? 'Ka noqo' : 'Cancel' ?></a>
        </div>
    </form>
</div>
