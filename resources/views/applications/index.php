<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$role = $user['role_slug'] ?? '';
?>

<div class="card animate-in" style="margin-bottom:22px;">
    <h2 style="font-size:1.2rem;margin:6px 0;"><?= Security::e(Lang::get('menu_applications')) ?></h2>
    <p style="font-size:0.84rem;color:var(--text-secondary);margin-bottom:16px;">
        <?= \App\Helpers\Lang::t('Xeerarka: macmiilku waa inuu leeyahay damiin, lacagta iyo muddadu waa inay ku jiraan xadka nooca maalgelinta, macmiil leh dayn ka dib dhacday 30 maalmood ma codsan karo. Codsiga ilaa $5,000 waxaa ansixiya Loan Officer; wixii ka badan $5,000 iyo maalgelinta kooxaha waxaa ansixiya Maamulaha Laanta. Qofka codsiga sameeyay ma ansixin karo.', 'Rules: the client must have a guarantor, amount and term must be within the product limits, and clients more than 30 days late on an active financing cannot apply. Applications up to $5,000 are approved by a loan officer; above $5,000, and group financing, by the Branch Manager. The person who created an application cannot approve it.') ?>
    </p>

    <?php if (in_array($role, ['admin', 'loan_officer'], true) && !empty($clients) && !empty($products)): ?>
    <form method="POST" action="?route=application_create" style="background:var(--bg-elevated);padding:16px;border-radius:10px;margin-bottom:20px;" data-confirm="<?= \App\Helpers\Lang::t('Ma gudbinaysaa codsigan maalgelinta?', 'Submit this financing application?') ?>">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <h4 style="margin-bottom:12px;"><?= \App\Helpers\Lang::t('Samee Codsi Maalgelin Cusub', 'Create New Financing Application') ?></h4>
        <div class="form-grid">
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Macmiilka *', 'Client *') ?></label>
                <select name="client_id" class="form-control" required>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= Security::e($c['full_name'] . ' (' . $c['client_code'] . ') — Score: ' . ($c['current_score'] ?? 600) . '/850') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Nooca Maalgelinta *', 'Financing Product *') ?></label>
                <select name="product_id" class="form-control" required>
                    <?php foreach ($products as $p): ?>
                        <?php if ((int) $p['is_active'] === 1): ?>
                            <option value="<?= (int) $p['id'] ?>">
                                <?= Security::e($p['name_en']) ?> (<?= Security::formatMoney($p['min_amount']) ?>–<?= Security::formatMoney($p['max_amount']) ?>)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Lacagta Maalgelinta ($ USD) *', 'Financing Amount ($ USD) *') ?></label>
                <input type="number" id="calc_principal" name="requested_amount" class="form-control num-tabular" step="0.01" min="100" required value="3000.00">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Lacag-bixinta Bishii ($ USD) *', 'Monthly Payment ($ USD) *') ?></label>
                <input type="number" id="calc_monthly_payment" class="form-control num-tabular" step="0.01" min="10" value="200.00">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Muddada (Qaybaha) *', 'Duration (Installments) *') ?></label>
                <input type="number" id="calc_term" name="term_installments" class="form-control num-tabular" min="1" max="120" required value="15">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Inta Jeer ee La Bixiyo *', 'Repayment Frequency *') ?></label>
                <select id="calc_frequency" name="repayment_frequency" class="form-control" required>
                    <option value="monthly">Monthly / Bille</option>
                    <option value="weekly">Weekly / Toddobaadle</option>
                    <option value="daily">Daily / Maalinle</option>
                    <option value="hourly">Hourly / Saacadle</option>
                    <option value="minutely">Every minute / Daqiiqad kasta</option>
                </select>
                <label style="margin-top:8px;display:block;">Every / Kasta (1 = each period, 2 = every 2nd …)</label>
                <input type="number" name="repayment_interval" class="form-control num-tabular" min="1" max="1000" value="1" required>
                <input type="hidden" id="calc_product_type" value="flat">
                <input type="hidden" id="calc_rate" value="0.00">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Koox Wadajir ah (ikhtiyaari)', 'Optional Solidarity Group') ?></label>
                <select name="group_id" class="form-control">
                    <option value="">-- Individual Financing --</option>
                    <?php foreach ($groups as $g): ?>
                        <?php if ($g['status'] === 'active'): ?>
                            <option value="<?= (int) $g['id'] ?>"><?= Security::e($g['group_name'] . ' (' . $g['group_code'] . ')') ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Ujeedada Maalgelinta *', 'Financing Purpose *') ?></label>
                <input type="text" name="purpose" class="form-control" required placeholder="e.g. Commercial inventory restocking">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Sharaxaadda Alaabta (Murabaxo waa qasab)', 'Murabaha Goods Description (Required for Murabaha)') ?></label>
                <input type="text" name="murabaha_goods_description" class="form-control" value="Verified commercial inventory goods per supplier pro-forma invoice">
            </div>
        </div>
        <div style="display:flex;gap:10px;margin:8px 0 14px;flex-wrap:wrap;">
            <button type="submit" name="submit_mode" value="submitted" class="btn btn-primary btn-sm"><?= Icons::svg('check-circle', 15) ?> <span><?= \App\Helpers\Lang::t('U Gudbi Codsiga Ansixinta', 'Submit Application to Queue') ?></span></button>
            <button type="submit" name="submit_mode" value="draft" class="btn btn-outline btn-sm"><?= \App\Helpers\Lang::t('Kaydi (Draft)', 'Save as Draft') ?></button>
            <button type="button" id="btnPreviewSchedule" class="btn btn-gold btn-sm"><?= Icons::svg('calendar', 14) ?> <span><?= \App\Helpers\Lang::t('Cusbooneysii Jadwalka', 'Refresh Schedule Preview') ?></span></button>
        </div>
        <div id="schedulePreviewContainer"></div>
    </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th><?= \App\Helpers\Lang::t('Codsiga #', 'Application #') ?></th>
                    <th><?= \App\Helpers\Lang::t('Macmiilka', 'Client') ?></th>
                    <th><?= \App\Helpers\Lang::t('Nooca Maalgelinta', 'Product') ?></th>
                    <th><?= \App\Helpers\Lang::t('Lacagta', 'Amount') ?></th>
                    <th><?= \App\Helpers\Lang::t('Wadarta La Filayo', 'Projected Total') ?></th>
                    <th><?= \App\Helpers\Lang::t('Muddada', 'Term') ?></th>
                    <th><?= \App\Helpers\Lang::t('Heerka Ansixinta', 'Approver Level') ?></th>
                    <th><?= \App\Helpers\Lang::t('Xaaladda', 'Status') ?></th>
                    <th><?= \App\Helpers\Lang::t('Ficillada', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $a): ?>
                    <tr>
                        <td class="num-tabular"><strong><?= Security::e($a['application_number']) ?></strong></td>
                        <td><?= Security::e($a['client_name']) ?></td>
                        <td><?= Security::e($a['product_name_en']) ?></td>
                        <td class="num-tabular"><strong><?= Security::formatMoney($a['requested_amount']) ?></strong></td>
                        <td class="num-tabular"><?= Security::formatMoney($a['projected_total_due']) ?></td>
                        <td class="num-tabular"><?= Security::e(\App\Services\ScheduleEngine::describePlan((int) $a['term_installments'], (int) ($a['repayment_interval'] ?? 1), (string) $a['repayment_frequency'], \App\Helpers\Lang::locale())) ?></td>
                        <td><span class="badge badge-gold"><?= Security::e(\App\Services\FinancingDomainService::approverLabel((string) $a['required_approver_role'])) ?></span></td>
                        <td>
                            <span class="badge badge-<?= $a['status'] === 'approved' || $a['status'] === 'disbursed' ? 'success' : ($a['status'] === 'rejected' ? 'danger' : 'info') ?>">
                                <?= Security::e($a['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($a['status'] === 'draft' && in_array($role, ['admin', 'loan_officer'], true)): ?>
                                <form method="POST" action="?route=application_submit_draft" style="display:inline;">
                                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                    <input type="hidden" name="application_id" value="<?= (int) $a['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-primary"><?= \App\Helpers\Lang::t('Gudbi Draft-ka', 'Submit Draft') ?></button>
                                </form>
                            <?php elseif ($a['status'] === 'rejected' && (int) $a['resubmission_count'] < 1): ?>
                                <form method="POST" action="?route=application_resubmit" style="display:inline;" data-confirm="<?= \App\Helpers\Lang::t('Ma dib u gudbinaysaa codsigan la diiday (hal mar keliya)?', 'Resubmit this rejected application (allowed once)?') ?>">
                                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                    <input type="hidden" name="application_id" value="<?= (int) $a['id'] ?>">
                                    <input type="hidden" name="requested_amount" value="<?= Security::e($a['requested_amount']) ?>">
                                    <input type="hidden" name="term_installments" value="<?= (int) $a['term_installments'] ?>">
                                    <input type="hidden" name="purpose" value="<?= Security::e($a['purpose']) ?>">
                                    <button type="submit" class="btn btn-sm btn-gold"><?= \App\Helpers\Lang::t('Dib u Gudbi (1 mar)', 'Resubmit (1x Allowed)') ?></button>
                                </form>
                            <?php elseif ($a['status'] === 'approved'): ?>
                                <a href="?route=disbursements" class="btn btn-sm btn-gold"><?= \App\Helpers\Lang::t('Bixi', 'Disburse') ?> <?= Icons::svg('arrow-right', 13) ?></a>
                            <?php else: ?>
                                <span style="font-size:0.78rem;color:var(--text-muted);"><?= Security::e($a['status']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
