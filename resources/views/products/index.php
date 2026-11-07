<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isAdmin = ($user['role_slug'] ?? '') === 'admin';
$isSo = Lang::locale() === 'so';
$products = $products ?? [];
$activeCount = count(array_filter($products, static fn($p) => (int) $p['is_active'] === 1));
$typeLabel = static fn(string $t): string => match ($t) {
    'murabaha' => 'Murabaha',
    'flat' => $isSo ? 'Qard Xasan (0%)' : 'Qard Hassan (0%)',
    'declining_balance' => $isSo ? 'Hoos u dhacaya' : 'Declining Balance',
    default => ucfirst($t),
};
$typeTone = ['murabaha' => 'gold', 'flat' => 'green', 'declining_balance' => 'info'];
$freqLabel = static fn(string $f): string => match ($f) {
    'weekly' => $isSo ? 'Toddobaadle' : 'Weekly',
    'monthly' => $isSo ? 'Bille' : 'Monthly',
    default => $isSo ? 'Bille & Toddobaadle' : 'Weekly & Monthly',
};
?>

<div class="mf-head">
    <div class="mf-head-title">
        <span class="mf-head-icon"><?= Icons::svg('layers', 22) ?></span>
        <div>
            <h1><?= Security::e(Lang::get('menu_products')) ?></h1>
            <p><?= $isSo
                ? 'Noocyada maalgelinta iyo xeerarkooda. Isbeddelka qiimaha wuxuu khuseeyaa codsiyada cusub oo keliya.'
                : 'Financing types and their rules. Rate changes apply to new applications only; existing contracts keep their terms.' ?></p>
        </div>
    </div>
    <div class="pr-stats">
        <span><b class="num-tabular"><?= count($products) ?></b> <?= $isSo ? 'wadar' : 'total' ?></span>
        <span class="is-green"><b class="num-tabular"><?= $activeCount ?></b> <?= $isSo ? 'firfircoon' : 'active' ?></span>
    </div>
</div>

<?php if ($isAdmin): ?>
<form method="POST" action="?route=product_save" class="card pr-form" data-confirm="<?= $isSo ? 'Kaydi nooca maalgelinta?' : 'Save this financing product?' ?>">
    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
    <div class="mg-form-head">
        <span class="mf-step-num"><?= Icons::svg('plus', 14) ?></span>
        <div>
            <h2><?= $isSo ? 'Nooc maalgelin cusub' : 'New financing product' ?></h2>
            <p><?= $isSo ? 'Geli 0.00 haddii aan faa\'iido ama khidmad lahayn.' : 'Use 0.00 for no profit rate or no admin fee.' ?></p>
        </div>
    </div>

    <div class="pr-section-label"><?= $isSo ? 'Aqoonsiga' : 'Identity' ?></div>
    <div class="pr-grid">
        <div class="form-group">
            <label for="pCode"><?= $isSo ? 'Koodhka *' : 'Product code *' ?></label>
            <input type="text" id="pCode" name="product_code" class="form-control num-tabular" required placeholder="PRD-MRB-04">
        </div>
        <div class="form-group">
            <label for="pNameSo"><?= $isSo ? 'Magaca (Soomaali) *' : 'Name (Somali) *' ?></label>
            <input type="text" id="pNameSo" name="name_so" class="form-control" required placeholder="Maalgelinta Gaadiidka">
        </div>
        <div class="form-group">
            <label for="pNameEn"><?= $isSo ? 'Magaca (Ingiriis) *' : 'Name (English) *' ?></label>
            <input type="text" id="pNameEn" name="name_en" class="form-control" required placeholder="Transport Financing">
        </div>
        <div class="form-group">
            <label for="pType"><?= $isSo ? 'Nooca *' : 'Type *' ?></label>
            <select id="pType" name="product_type" class="form-control" required>
                <option value="flat"><?= $isSo ? 'Qard Xasan (0% faa\'iido)' : 'Qard Hassan (0% extra)' ?></option>
                <option value="murabaha">Murabaha</option>
                <option value="declining_balance"><?= $isSo ? 'Hoos u dhacaya' : 'Declining Balance' ?></option>
            </select>
        </div>
    </div>

    <div class="pr-section-label"><?= $isSo ? 'Xeerarka' : 'Rules' ?></div>
    <div class="pr-grid">
        <div class="form-group">
            <label for="pRate"><?= $isSo ? 'Faa\'iidada *' : 'Rate / markup *' ?></label>
            <div class="mf-input mf-input-suffix"><input type="number" id="pRate" name="rate" class="form-control num-tabular" step="0.01" min="0" max="100" required value="0.00"><span>%</span></div>
        </div>
        <div class="form-group">
            <label for="pFee"><?= $isSo ? 'Khidmadda *' : 'Admin fee *' ?></label>
            <div class="mf-input mf-input-prefix"><span>$</span><input type="number" id="pFee" name="admin_fee" class="form-control num-tabular" step="0.01" min="0" required value="0.00"></div>
        </div>
        <div class="form-group">
            <label><?= $isSo ? 'Lacagta (ugu yar – ugu badan) *' : 'Amount (min – max) *' ?></label>
            <div class="pr-range">
                <div class="mf-input mf-input-prefix"><span>$</span><input type="number" name="min_amount" class="form-control num-tabular" step="0.01" required value="100.00" aria-label="Min amount"></div>
                <span class="pr-dash">–</span>
                <div class="mf-input mf-input-prefix"><span>$</span><input type="number" name="max_amount" class="form-control num-tabular" step="0.01" required value="50000.00" aria-label="Max amount"></div>
            </div>
        </div>
        <div class="form-group">
            <label><?= $isSo ? 'Qaybaha (ugu yar – ugu badan) *' : 'Installments (min – max) *' ?></label>
            <div class="pr-range">
                <input type="number" name="min_term" class="form-control num-tabular" required value="1" min="1" aria-label="Min term">
                <span class="pr-dash">–</span>
                <input type="number" name="max_term" class="form-control num-tabular" required value="36" min="1" aria-label="Max term">
            </div>
        </div>
        <div class="form-group">
            <label for="pFreq"><?= $isSo ? 'Habka bixinta *' : 'Frequency *' ?></label>
            <select id="pFreq" name="allowed_frequency" class="form-control" required>
                <option value="both"><?= $freqLabel('both') ?></option>
                <option value="monthly"><?= $freqLabel('monthly') ?></option>
                <option value="weekly"><?= $freqLabel('weekly') ?></option>
            </select>
        </div>
    </div>

    <div class="mg-form-actions">
        <button type="submit" class="btn btn-primary"><?= Icons::svg('check', 15) ?> <?= $isSo ? 'Abuur nooca' : 'Create product' ?></button>
    </div>
</form>
<?php endif; ?>

<div class="card pr-table-card">
    <div class="table-responsive">
        <table class="simda-table pr-table">
            <thead>
                <tr>
                    <th><?= $isSo ? 'Nooca' : 'Product' ?></th>
                    <th><?= $isSo ? 'Faa\'iido' : 'Rate' ?></th>
                    <th><?= $isSo ? 'Lacagta' : 'Amount' ?></th>
                    <th><?= $isSo ? 'Qaybaha' : 'Term' ?></th>
                    <th><?= $isSo ? 'Khidmad' : 'Fee' ?></th>
                    <th><?= $isSo ? 'Isticmaal' : 'Usage' ?></th>
                    <th><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                    <?php if ($isAdmin): ?><th class="pr-end"></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!$products): ?>
                    <tr><td colspan="8" class="pr-empty"><?= $isSo ? 'Weli nooc maalgelin lama abuurin.' : 'No financing products yet.' ?></td></tr>
                <?php endif; ?>
                <?php foreach ($products as $p): ?>
                    <?php $on = (int) $p['is_active'] === 1; ?>
                    <tr class="<?= $on ? '' : 'is-off' ?>">
                        <td>
                            <div class="pr-name"><?= Security::e(Lang::field($p, 'name')) ?></div>
                            <div class="pr-sub">
                                <span class="num-tabular"><?= Security::e($p['product_code']) ?></span>
                                <span class="adm-role dash-tone-<?= $typeTone[$p['product_type']] ?? 'muted' ?>"><?= Security::e($typeLabel((string) $p['product_type'])) ?></span>
                            </div>
                        </td>
                        <td class="num-tabular"><?= Security::e(number_format((float) ($p['product_type'] === 'murabaha' ? $p['murabaha_markup_pct'] : $p['annual_profit_rate']), 2)) ?>%</td>
                        <td class="num-tabular"><?= Security::formatMoney($p['min_amount']) ?> – <?= Security::formatMoney($p['max_amount']) ?></td>
                        <td class="num-tabular"><?= (int) $p['min_term'] ?>–<?= (int) $p['max_term'] ?><div class="pr-sub"><?= Security::e($freqLabel((string) $p['allowed_frequency'])) ?></div></td>
                        <td class="num-tabular"><?= Security::formatMoney($p['admin_fee']) ?></td>
                        <td class="num-tabular"><?= (int) $p['usage_count'] ?> <span class="pr-sub-inline"><?= $isSo ? 'heshiis' : 'contracts' ?></span></td>
                        <td><span class="pr-status <?= $on ? 'is-on' : '' ?>"><?= $on ? ($isSo ? 'Firfircoon' : 'Active') : ($isSo ? 'La joojiyay' : 'Inactive') ?></span></td>
                        <?php if ($isAdmin): ?>
                        <td class="pr-end">
                            <div class="pr-actions">
                                <form method="POST" action="?route=product_toggle">
                                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline"><?= $on ? ($isSo ? 'Jooji' : 'Deactivate') : ($isSo ? 'Dhaqaaji' : 'Activate') ?></button>
                                </form>
                                <?php if ((int) $p['usage_count'] === 0): ?>
                                    <form method="POST" action="?route=product_delete" data-confirm="<?= $isSo ? 'Tirtir noocan aan la isticmaalin?' : 'Delete this unused product?' ?>">
                                        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline mg-del" aria-label="<?= $isSo ? 'Tirtir' : 'Delete' ?>"><?= Icons::svg('x', 13) ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
