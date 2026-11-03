<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
?>

<div class="card animate-in" style="margin-bottom:22px;">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;margin-bottom:16px;">
        <div>
            <h2 style="font-size:1.2rem;"><?= Security::e(Lang::get('menu_all_clients')) ?></h2>
            <p style="font-size:0.84rem;color:var(--text-secondary);">
                Module 1 — Unique ID & Phone enforcement, mandatory Guarantor verification, and Branch-level access control.
            </p>
        </div>
        <div style="display:flex;gap:10px;">
            <?php if (in_array($user['role_slug'] ?? '', ['admin', 'loan_officer'], true)): ?>
                <a href="?route=client_register" class="btn btn-primary"><?= Icons::svg('user-plus', 16) ?> <span><?= Security::e(Lang::get('menu_register_client')) ?></span></a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($clients)): ?>
        <div style="padding:32px;text-align:center;color:var(--text-muted);">
            <strong><?= Security::e(Lang::get('empty_state_title')) ?></strong>
            <div><?= Security::e(Lang::get('empty_state_desc')) ?></div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="simda-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Full Name</th>
                        <th>ID Number</th>
                        <th>Phone</th>
                        <th>Branch</th>
                        <th>Business & Income</th>
                        <th>Guarantors</th>
                        <th>Credit Score</th>
                        <th>Credit Bal.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): ?>
                        <tr>
                            <td class="num-tabular"><strong><?= Security::e($c['client_code']) ?></strong></td>
                            <td>
                                <div style="font-weight:600;"><?= Security::e($c['full_name']) ?></div>
                                <div style="font-size:0.75rem;color:var(--text-muted);"><?= Security::e($c['district'] . ', ' . $c['city']) ?></div>
                            </td>
                            <td class="num-tabular"><?= Security::e($c['id_number']) ?> <span class="badge badge-info"><?= Security::e($c['id_type']) ?></span></td>
                            <td class="num-tabular"><?= Security::e($c['phone']) ?></td>
                            <td><?= Security::e($c['branch_name']) ?></td>
                            <td>
                                <div><?= Security::e($c['business_type']) ?></div>
                                <div class="num-tabular" style="font-size:0.76rem;color:var(--text-secondary);"><?= Security::formatMoney($c['monthly_income']) ?>/mo</div>
                            </td>
                            <td class="num-tabular">
                                <span class="badge badge-success"><?= Icons::svg('shield', 12) ?> <?= (int) $c['guarantors_count'] ?> Guarantor(s)</span>
                            </td>
                            <td class="num-tabular">
                                <strong><?= (int) ($c['current_score'] ?? 600) ?></strong>
                                <span class="badge badge-gold"><?= Security::e($c['score_category'] ?? 'fair') ?></span>
                            </td>
                            <td class="num-tabular"><?= Security::formatMoney($c['credit_balance']) ?></td>
                            <td>
                                <?php if (($user['role_slug'] ?? '') === 'admin'): ?>
                                    <form method="POST" action="?route=client_transfer" style="display:inline-flex;gap:4px;align-items:center;" data-confirm="Transfer this client to the selected branch?">
                                        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                        <input type="hidden" name="client_id" value="<?= (int) $c['id'] ?>">
                                        <select name="new_branch_id" class="form-control" style="padding:4px 6px;font-size:0.75rem;width:110px;">
                                            <?php foreach ($branches as $b): ?>
                                                <option value="<?= (int) $b['id'] ?>" <?= (int) $b['id'] === (int) $c['branch_id'] ? 'selected' : '' ?>>
                                                    <?= Security::e($b['code']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline">Transfer</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-info">Branch Scoped</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
