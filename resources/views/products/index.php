<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isAdmin = ($user['role_slug'] ?? '') === 'admin';
?>

<div class="card animate-in" style="margin-bottom:24px;">
    <span class="badge badge-gold">Module 2 — Financing Product Configuration</span>
    <h2 style="font-size:1.25rem;margin:6px 0;"><?= Security::e(Lang::get('menu_products')) ?></h2>
    <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:18px;">
        Rules: Supports <strong>Murabaha</strong>, <strong>Flat</strong>, and <strong>Declining Balance</strong>. Products currently in use cannot be deleted (they can be deactivated). Rate changes apply strictly to new applications; existing financings retain their original agreed terms.
    </p>

    <?php if ($isAdmin): ?>
    <form method="POST" action="?route=product_save" style="background:var(--bg-elevated);padding:18px;border-radius:12px;margin-bottom:22px;" data-confirm="Save this financing product configuration?">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <h4 style="margin-bottom:12px;">Configure New Financing Product</h4>
        <div class="form-grid">
            <div class="form-group">
                <label>Product Code *</label>
                <input type="text" name="product_code" class="form-control num-tabular" required placeholder="e.g. PRD-MRB-04">
            </div>
            <div class="form-group">
                <label>Name (Somali) *</label>
                <input type="text" name="name_so" class="form-control" required placeholder="e.g. Maalgelinta Gaadiidka Muraabaxa">
            </div>
            <div class="form-group">
                <label>Name (English) *</label>
                <input type="text" name="name_en" class="form-control" required placeholder="e.g. Murabaha Transport Financing">
            </div>
            <div class="form-group">
                <label>Product Type *</label>
                <select name="product_type" class="form-control" required>
                    <option value="murabaha">Murabaha (Markup %)</option>
                    <option value="flat">Flat (Annual Rate %)</option>
                    <option value="declining_balance">Declining Balance (Amortized %)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Rate / Markup (%) *</label>
                <input type="number" name="rate" class="form-control num-tabular" step="0.01" min="0.5" max="100" required value="12.00">
            </div>
            <div class="form-group">
                <label>Min Amount ($ USD) *</label>
                <input type="number" name="min_amount" class="form-control num-tabular" step="0.01" required value="500.00">
            </div>
            <div class="form-group">
                <label>Max Amount ($ USD) *</label>
                <input type="number" name="max_amount" class="form-control num-tabular" step="0.01" required value="12000.00">
            </div>
            <div class="form-group">
                <label>Min / Max Term (Installments) *</label>
                <div style="display:flex;gap:8px;">
                    <input type="number" name="min_term" class="form-control num-tabular" required value="3" min="1">
                    <input type="number" name="max_term" class="form-control num-tabular" required value="24" min="1">
                </div>
            </div>
            <div class="form-group">
                <label>Allowed Frequency *</label>
                <select name="allowed_frequency" class="form-control" required>
                    <option value="both">Weekly & Monthly</option>
                    <option value="monthly">Monthly Only</option>
                    <option value="weekly">Weekly Only</option>
                </select>
            </div>
            <div class="form-group">
                <label>Admin Fee ($ USD) *</label>
                <input type="number" name="admin_fee" class="form-control num-tabular" step="0.01" required value="20.00">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Create Product</button>
    </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Product Name</th>
                    <th>Type</th>
                    <th>Rate / Markup</th>
                    <th>Amount Limits</th>
                    <th>Term Limits</th>
                    <th>Admin Fee</th>
                    <th>Usage</th>
                    <th>Status</th>
                    <?php if ($isAdmin): ?><th>Admin Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="num-tabular"><strong><?= Security::e($p['product_code']) ?></strong></td>
                        <td>
                            <div style="font-weight:600;"><?= Security::e(Lang::field($p, 'name')) ?></div>
                        </td>
                        <td><span class="badge badge-gold"><?= Security::e($p['product_type']) ?></span></td>
                        <td class="num-tabular">
                            <?= Security::e(number_format((float) ($p['product_type'] === 'murabaha' ? $p['murabaha_markup_pct'] : $p['annual_profit_rate']), 2)) ?>%
                        </td>
                        <td class="num-tabular"><?= Security::formatMoney($p['min_amount']) ?> – <?= Security::formatMoney($p['max_amount']) ?></td>
                        <td class="num-tabular"><?= (int) $p['min_term'] ?>–<?= (int) $p['max_term'] ?> (<?= Security::e($p['allowed_frequency']) ?>)</td>
                        <td class="num-tabular"><?= Security::formatMoney($p['admin_fee']) ?></td>
                        <td class="num-tabular"><?= (int) $p['usage_count'] ?> contracts</td>
                        <td>
                            <span class="badge badge-<?= (int) $p['is_active'] === 1 ? 'success' : 'danger' ?>">
                                <?= (int) $p['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <?php if ($isAdmin): ?>
                        <td>
                            <form method="POST" action="?route=product_toggle" style="display:inline;">
                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline">
                                    <?= (int) $p['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                            <?php if ((int) $p['usage_count'] === 0): ?>
                                <form method="POST" action="?route=product_delete" style="display:inline;" data-confirm="Delete this unused product?">
                                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

