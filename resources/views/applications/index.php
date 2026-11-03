<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$role = $user['role_slug'] ?? '';
?>

<div class="card animate-in" style="margin-bottom:22px;">
    <span class="badge badge-gold"><?= Icons::svg('file-text', 13) ?> Module 3 — Financing Application Builder & Schedule Preview</span>
    <h2 style="font-size:1.2rem;margin:6px 0;"><?= Security::e(Lang::get('menu_applications')) ?></h2>
    <p style="font-size:0.84rem;color:var(--text-secondary);margin-bottom:16px;">
        Rules enforced: Client must have a Guarantor, amount and term must be within product limits, and clients &gt;30 days overdue on active financing are blocked. Applications &le; $5,000 route to Loan Officer approval; &gt; $5,000 or Group Financings route to Branch Manager approval.
    </p>

    <?php if (in_array($role, ['admin', 'loan_officer'], true) && !empty($clients) && !empty($products)): ?>
    <form method="POST" action="?route=application_create" style="background:var(--bg-elevated);padding:16px;border-radius:10px;margin-bottom:20px;" data-confirm="Submit this financing application?">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <h4 style="margin-bottom:12px;">Create New Financing Application</h4>
        <div class="form-grid">
            <div class="form-group">
                <label>Client *</label>
                <select name="client_id" class="form-control" required>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= Security::e($c['full_name'] . ' (' . $c['client_code'] . ') — Score: ' . ($c['current_score'] ?? 600)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Financing Product *</label>
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
                <label>Financing Amount ($ USD) *</label>
                <input type="number" name="requested_amount" class="form-control num-tabular" step="0.01" min="100" required value="1000.00">
            </div>
            <div class="form-group">
                <label>Term (Installments) *</label>
                <input type="number" name="term_installments" class="form-control num-tabular" min="1" max="36" required value="5">
            </div>
            <div class="form-group">
                <label>Repayment Frequency *</label>
                <select name="repayment_frequency" class="form-control" required>
                    <option value="monthly">Monthly</option>
                    <option value="weekly">Weekly</option>
                </select>
            </div>
            <div class="form-group">
                <label>Optional Solidarity Group</label>
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
                <label>Financing Purpose *</label>
                <input type="text" name="purpose" class="form-control" required placeholder="e.g. Commercial inventory restocking">
            </div>
            <div class="form-group">
                <label>Murabaha Goods Description (Required for Murabaha)</label>
                <input type="text" name="murabaha_goods_description" class="form-control" value="Verified commercial inventory goods per supplier pro-forma invoice">
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px;">
            <button type="submit" name="submit_mode" value="submitted" class="btn btn-primary btn-sm"><?= Icons::svg('check-circle', 15) ?> <span>Submit Application to Queue</span></button>
            <button type="submit" name="submit_mode" value="draft" class="btn btn-outline btn-sm">Save as Draft</button>
        </div>
    </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Application #</th>
                    <th>Client</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Projected Total</th>
                    <th>Term</th>
                    <th>Approver Level</th>
                    <th>Status</th>
                    <th>Actions</th>
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
                        <td class="num-tabular"><?= (int) $a['term_installments'] ?> (<?= Security::e($a['repayment_frequency']) ?>)</td>
                        <td><span class="badge badge-gold"><?= Security::e($a['required_approver_role']) ?></span></td>
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
                                    <button type="submit" class="btn btn-sm btn-primary">Submit Draft</button>
                                </form>
                            <?php elseif ($a['status'] === 'rejected' && (int) $a['resubmission_count'] < 1): ?>
                                <form method="POST" action="?route=application_resubmit" style="display:inline;" data-confirm="Resubmit this rejected application (allowed once)?">
                                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                    <input type="hidden" name="application_id" value="<?= (int) $a['id'] ?>">
                                    <input type="hidden" name="requested_amount" value="<?= Security::e($a['requested_amount']) ?>">
                                    <input type="hidden" name="term_installments" value="<?= (int) $a['term_installments'] ?>">
                                    <input type="hidden" name="purpose" value="<?= Security::e($a['purpose']) ?>">
                                    <button type="submit" class="btn btn-sm btn-gold">Resubmit (1x Allowed)</button>
                                </form>
                            <?php elseif ($a['status'] === 'approved'): ?>
                                <a href="?route=disbursements" class="btn btn-sm btn-gold">Disburse <?= Icons::svg('arrow-right', 13) ?></a>
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
