<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isSo = Lang::locale() === 'so';

// Repopulate user's entered data if submission failed with duplicate or error
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$flashError = (string) ($_SESSION['flash_error'] ?? '');
$duplicateField = null;
if (stripos($flashError, 'ID number') !== false || stripos($flashError, 'id_number') !== false) {
    $duplicateField = 'id_number';
} elseif (stripos($flashError, 'phone') !== false) {
    $duplicateField = 'phone';
} elseif (stripos($flashError, 'email') !== false) {
    $duplicateField = 'email';
}
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

    <!-- Client-side error banner placeholder -->
    <div id="clientRegisterLiveAlert" class="alert alert-danger" style="display:none; margin-bottom:18px; border-radius:10px; font-weight:700;">
        <span id="clientRegisterLiveAlertText"></span>
    </div>

    <form method="POST" action="?route=client_register_submit" enctype="multipart/form-data" class="reg-form" id="clientRegisterForm" data-confirm="<?= $isSo ? 'Ma diiwaangelinaysaa macmiilkan iyo damiinkiisa?' : 'Register this client and their guarantor?' ?>">
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
                <!-- FULL NAME -->
                <div class="form-group">
                    <label><?= $isSo ? 'Magaca Buuxa *' : 'Full Name *' ?></label>
                    <input type="text" name="full_name" id="field_full_name" class="form-control" required placeholder="Faadumo Maxamed Cali" value="<?= Security::e($old['full_name'] ?? '') ?>" autocomplete="name">
                </div>

                <!-- ID TYPE -->
                <div class="form-group">
                    <label><?= $isSo ? 'Nooca Aqoonsiga *' : 'ID Type *' ?></label>
                    <?php $selectedIdType = $old['id_type'] ?? 'national_id'; ?>
                    <select name="id_type" id="field_id_type" class="form-control" required>
                        <option value="national_id" <?= $selectedIdType === 'national_id' ? 'selected' : '' ?>><?= $isSo ? 'Aqoonsi Qaran' : 'National ID' ?></option>
                        <option value="passport" <?= $selectedIdType === 'passport' ? 'selected' : '' ?>><?= $isSo ? 'Baasaboor' : 'Passport' ?></option>
                        <option value="voter_card" <?= $selectedIdType === 'voter_card' ? 'selected' : '' ?>><?= $isSo ? 'Kaarka Codbixinta' : 'Voter Card' ?></option>
                        <option value="business_license" <?= $selectedIdType === 'business_license' ? 'selected' : '' ?>><?= $isSo ? 'Ruqsadda Ganacsiga' : 'Business License' ?></option>
                    </select>
                </div>

                <!-- ID NUMBER (WITH LIVE DUPLICATE CHECK & HIGHLIGHT) -->
                <div class="form-group" id="group_id_number">
                    <label for="field_id_number"><?= $isSo ? 'Lambarka Aqoonsiga *' : 'ID Number *' ?></label>
                    <input type="text" name="id_number" id="field_id_number" class="form-control num-tabular" required placeholder="SOM-ID-884120" value="<?= Security::e($old['id_number'] ?? '') ?>" autocomplete="off">
                    <div id="error_id_number" class="field-error-feedback" style="display:none; color:#C53030; font-size:0.79rem; font-weight:700; margin-top:5px;"></div>
                </div>

                <!-- PHONE NUMBER (WITH LIVE DUPLICATE CHECK & HIGHLIGHT) -->
                <div class="form-group" id="group_phone">
                    <label for="field_phone"><?= $isSo ? 'Telefoonka *' : 'Phone Number *' ?></label>
                    <input type="text" name="phone" id="field_phone" class="form-control num-tabular" required placeholder="+252615778899" dir="ltr" value="<?= Security::e($old['phone'] ?? '') ?>" autocomplete="tel">
                    <div id="error_phone" class="field-error-feedback" style="display:none; color:#C53030; font-size:0.79rem; font-weight:700; margin-top:5px;"></div>
                </div>

                <!-- CLIENT EMAIL (WITH LIVE DUPLICATE CHECK & HIGHLIGHT) -->
                <div class="form-group" id="group_email">
                    <label for="field_email"><?= $isSo ? 'Email-ka Macmiilka (Halkan waxaa loogu diri doonaa Rasiidka) *' : 'Client Email (Receipts will be sent here) *' ?></label>
                    <input type="email" name="email" id="field_email" class="form-control" required placeholder="client@gmail.com" value="<?= Security::e($old['email'] ?? '') ?>" autocomplete="email">
                    <div id="error_email" class="field-error-feedback" style="display:none; color:#C53030; font-size:0.79rem; font-weight:700; margin-top:5px;"></div>
                </div>

                <!-- CITY -->
                <div class="form-group">
                    <label><?= $isSo ? 'Magaalada *' : 'City *' ?></label>
                    <input type="text" name="city" id="field_city" class="form-control" required value="<?= Security::e($old['city'] ?? 'Mogadishu') ?>">
                </div>

                <!-- DISTRICT -->
                <div class="form-group">
                    <label><?= $isSo ? 'Degmada *' : 'District *' ?></label>
                    <input type="text" name="district" id="field_district" class="form-control" required placeholder="Hodan" value="<?= Security::e($old['district'] ?? '') ?>">
                </div>

                <!-- ADDRESS -->
                <div class="form-group">
                    <label><?= $isSo ? 'Cinwaanka *' : 'Address *' ?></label>
                    <input type="text" name="address" id="field_address" class="form-control" required placeholder="<?= $isSo ? 'Wadada ama lambarka dukaanka' : 'Street or market stall number' ?>" value="<?= Security::e($old['address'] ?? '') ?>">
                </div>

                <!-- BRANCH -->
                <div class="form-group">
                    <label><?= $isSo ? 'Laanta *' : 'Branch *' ?></label>
                    <?php $selectedBranch = (int) ($old['branch_id'] ?? 0); ?>
                    <select name="branch_id" id="field_branch_id" class="form-control" required>
                        <?php foreach ($branches as $b): ?>
                            <?php if (($user['role_slug'] ?? '') === 'admin' || (int) ($user['branch_id'] ?? 0) === (int) $b['id']): ?>
                                <option value="<?= (int) $b['id'] ?>" <?= ($selectedBranch === (int) $b['id']) ? 'selected' : '' ?>>
                                    <?= Security::e($b['name'] . ' (' . $b['code'] . ')') ?>
                                </option>
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
                    <label><?= $isSo ? 'Nooca Ganacsiga (Ikhtiyaari)' : 'Business Type (Optional)' ?></label>
                    <input type="text" name="business_type" id="field_business_type" class="form-control" placeholder="<?= $isSo ? 'Dukaan bagaash' : 'Retail shop' ?>" value="<?= Security::e($old['business_type'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Dakhliga Bishii ($) (Ikhtiyaari)' : 'Monthly Income ($) (Optional)' ?></label>
                    <input type="number" name="monthly_income" id="field_monthly_income" class="form-control num-tabular" step="0.01" min="0" placeholder="0.00" value="<?= Security::e($old['monthly_income'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Sawirka Macmiilka (Ikhtiyaari)' : 'Client Photo (Optional)' ?></label>
                    <input type="file" name="photo" id="field_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
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
                    <input type="text" name="guarantor_full_name" id="field_guarantor_full_name" class="form-control" required placeholder="Cabdullaahi Maxamed Cali" value="<?= Security::e($old['guarantor_full_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Telefoonka Damiinka *' : 'Guarantor Phone *' ?></label>
                    <input type="text" name="guarantor_phone" id="field_guarantor_phone" class="form-control num-tabular" required placeholder="+252615443322" dir="ltr" value="<?= Security::e($old['guarantor_phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Aqoonsiga Damiinka *' : 'Guarantor ID Number *' ?></label>
                    <input type="text" name="guarantor_id_number" id="field_guarantor_id_number" class="form-control num-tabular" required placeholder="SOM-GID-551900" value="<?= Security::e($old['guarantor_id_number'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Xiriirka uu la leeyahay *' : 'Relationship to Client *' ?></label>
                    <input type="text" name="guarantor_relationship" id="field_guarantor_relationship" class="form-control" required placeholder="<?= $isSo ? 'Walaal / Lammaane ganacsi' : 'Brother / Business partner' ?>" value="<?= Security::e($old['guarantor_relationship'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Shaqada Damiinka' : 'Guarantor Occupation' ?></label>
                    <input type="text" name="guarantor_occupation" id="field_guarantor_occupation" class="form-control" placeholder="<?= $isSo ? 'Ganacsade jumlo' : 'Wholesale merchant' ?>" value="<?= Security::e($old['guarantor_occupation'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Dakhliga Damiinka ($)' : 'Guarantor Income ($)' ?></label>
                    <input type="number" name="guarantor_income" id="field_guarantor_income" class="form-control num-tabular" step="0.01" value="<?= Security::e($old['guarantor_income'] ?? '1800.00') ?>">
                </div>
            </div>
        </section>

        <div class="adm-form-actions">
            <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitClientReg">
                <?= Icons::svg('check-circle', 14) ?> <span><?= $isSo ? 'Diiwaangeli Macmiilka' : 'Register Client' ?></span>
            </button>
            <a href="?route=clients" class="btn btn-outline btn-sm"><?= $isSo ? 'Ka noqo' : 'Cancel' ?></a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isSo = <?= json_encode($isSo) ?>;
    const form = document.getElementById('clientRegisterForm');
    const alertBox = document.getElementById('clientRegisterLiveAlert');
    const alertText = document.getElementById('clientRegisterLiveAlertText');

    // Server-side error highlight on page load if reloaded
    const serverDuplicateField = <?= json_encode($duplicateField) ?>;
    if (serverDuplicateField) {
        const input = document.getElementById('field_' + serverDuplicateField);
        if (input) {
            highlightFieldInvalid(input, <?= json_encode($flashError) ?>);
            input.focus();
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function highlightFieldInvalid(input, message) {
        input.style.borderColor = '#E53E3E';
        input.style.boxShadow = '0 0 0 3px rgba(229, 62, 62, 0.22)';
        input.style.backgroundColor = 'rgba(229, 62, 62, 0.03)';
        input.dataset.duplicate = 'true';

        const fieldName = input.name;
        const errDiv = document.getElementById('error_' + fieldName);
        if (errDiv) {
            errDiv.innerHTML = '⚠️ ' + message;
            errDiv.style.display = 'block';
        }
    }

    function clearFieldInvalid(input) {
        input.style.borderColor = '';
        input.style.boxShadow = '';
        input.style.backgroundColor = '';
        input.dataset.duplicate = 'false';

        const fieldName = input.name;
        const errDiv = document.getElementById('error_' + fieldName);
        if (errDiv) {
            errDiv.innerHTML = '';
            errDiv.style.display = 'none';
        }
    }

    function highlightFieldValid(input) {
        input.style.borderColor = '#23845B';
        input.style.boxShadow = '0 0 0 2px rgba(35, 132, 91, 0.15)';
        input.style.backgroundColor = '';
        input.dataset.duplicate = 'false';

        const fieldName = input.name;
        const errDiv = document.getElementById('error_' + fieldName);
        if (errDiv) {
            errDiv.innerHTML = '';
            errDiv.style.display = 'none';
        }
    }

    // Debounced live duplicate checker
    const checkTimers = {};
    function scheduleDuplicateCheck(input) {
        const fieldName = input.name;
        const val = input.value.trim();

        clearTimeout(checkTimers[fieldName]);
        if (!val) {
            clearFieldInvalid(input);
            return;
        }

        checkTimers[fieldName] = setTimeout(function () {
            fetch('?route=client_check_duplicate&field=' + encodeURIComponent(fieldName) + '&value=' + encodeURIComponent(val))
                .then(res => res.json())
                .then(data => {
                    if (data && data.exists) {
                        highlightFieldInvalid(input, data.message);
                    } else {
                        highlightFieldValid(input);
                    }
                })
                .catch(() => {
                    // Network silent fallback
                });
        }, 350);
    }

    // Attach listeners to ID number, Phone, and Email
    ['field_id_number', 'field_phone', 'field_email'].forEach(function (id) {
        const inp = document.getElementById(id);
        if (inp) {
            inp.addEventListener('input', function () {
                scheduleDuplicateCheck(inp);
            });
            inp.addEventListener('blur', function () {
                scheduleDuplicateCheck(inp);
            });
        }
    });

    // On Form Submit: Stop if any field is known duplicate without wiping data!
    if (form) {
        form.addEventListener('submit', function (e) {
            const dupInputs = form.querySelectorAll('input[data-duplicate="true"]');
            if (dupInputs.length > 0) {
                e.preventDefault();
                e.stopPropagation();

                const firstDup = dupInputs[0];
                firstDup.focus();
                firstDup.scrollIntoView({ behavior: 'smooth', block: 'center' });

                if (alertBox && alertText) {
                    alertText.textContent = isSo
                        ? 'Fadlan sax qaybta casaanka ah (xogtaan horey ayaa loo qaatay). Macluumaadkaagii kale meelna kama bixin!'
                        : 'Please fix the highlighted field (already registered). Your other entered data has been preserved!';
                    alertBox.style.display = 'block';
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                return false;
            }
        });
    }
});
</script>
