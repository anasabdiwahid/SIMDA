<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
?>

<div class="card animate-in" style="margin-bottom:24px;">
    <h2 style="font-size:1.25rem;margin-bottom:6px;"><?= Security::e(Lang::get('menu_guarantors')) ?></h2>
    <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:18px;">
        Module 1 — Every client must have at least one guarantor before applying for financing. You can also attach additional guarantors to an existing client below.
    </p>

    <?php if (in_array($user['role_slug'] ?? '', ['admin', 'loan_officer'], true) && !empty($clients)): ?>
    <form method="POST" action="?route=guarantor_add" style="background:var(--bg-elevated);padding:16px;border-radius:12px;margin-bottom:22px;">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <h4 style="margin-bottom:10px;">Add Additional Guarantor</h4>
        <div class="form-grid">
            <div class="form-group">
                <label>Select Client *</label>
                <select name="client_id" class="form-control" required>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= Security::e($c['full_name'] . ' (' . $c['client_code'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Guarantor Full Name *</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Phone *</label>
                <input type="text" name="phone" class="form-control num-tabular" required>
            </div>
            <div class="form-group">
                <label>ID Number *</label>
                <input type="text" name="id_number" class="form-control num-tabular" required>
            </div>
            <div class="form-group">
                <label>Relationship *</label>
                <input type="text" name="relationship" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Save Guarantor</button>
    </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Guarantor Name</th>
                    <th>ID Number</th>
                    <th>Phone</th>
                    <th>Relationship</th>
                    <th>Guaranteed Client</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($guarantors as $g): ?>
                    <tr>
                        <td><strong><?= Security::e($g['full_name']) ?></strong></td>
                        <td class="num-tabular"><?= Security::e($g['id_number']) ?></td>
                        <td class="num-tabular"><?= Security::e($g['phone']) ?></td>
                        <td><span class="badge badge-gold"><?= Security::e($g['relationship']) ?></span></td>
                        <td><?= Security::e($g['client_name']) ?> (<?= Security::e($g['client_code']) ?>)</td>
                        <td><?= Security::e($g['branch_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

