<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$role = $user['role_slug'] ?? '';
?>

<div class="card animate-in" style="margin-bottom:24px;">
    <h2 style="font-size:1.25rem;margin:6px 0;"><?= Security::e(Lang::get('menu_client_groups')) ?></h2>
    <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:18px;">
        Rules: Groups require 3 to 10 members and Branch Manager approval. Each member holds their own financing contract. If ANY group member is more than 30 days overdue, no member of that group can receive new financing. Members cannot leave while they have active financing.
    </p>

    <?php if (in_array($role, ['admin', 'loan_officer', 'branch_manager'], true) && count($clients) >= 3): ?>
    <form method="POST" action="?route=group_create" style="background:var(--bg-elevated);padding:18px;border-radius:12px;margin-bottom:22px;" data-confirm="<?= \App\Helpers\Lang::t('Ma samaynaysaa kooxdan wadajirka ah (3–10 xubnood)?', 'Create this 3–10 member solidarity group?') ?>">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <h4 style="margin-bottom:12px;"><?= \App\Helpers\Lang::t('Samee Koox Cusub (Dooro 3–10 Xubnood)', 'Create New Client Group (Select 3–10 Members)') ?></h4>
        <div class="form-grid">
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Magaca Kooxda *', 'Group Name *') ?></label>
                <input type="text" name="group_name" class="form-control" required placeholder="e.g. Iskaashatada Ganacsatada Bakaro">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Laanta *', 'Branch *') ?></label>
                <select name="branch_id" class="form-control" required>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= (int) $b['id'] ?>"><?= Security::e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Hoggaamiyaha Kooxda *', 'Group Leader *') ?></label>
                <select name="leader_client_id" class="form-control" required>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= Security::e($c['full_name'] . ' (' . $c['client_code'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Dooro Xubnaha (Ctrl/Cmd hay si aad u doorato 3–10 macmiil) *', 'Select Members (Hold Ctrl/Cmd to pick 3–10 clients) *') ?></label>
                <select name="member_ids[]" class="form-control" multiple required style="height:105px;">
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" selected><?= Security::e($c['full_name'] . ' (' . $c['client_code'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><?= \App\Helpers\Lang::t('Samee Kooxda (Waxay sugaysaa ansixinta Maamulaha Laanta)', 'Create Group (Pending Branch Manager Approval)') ?></button>
    </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th><?= \App\Helpers\Lang::t('Koodhka Kooxda', 'Group Code') ?></th>
                    <th><?= \App\Helpers\Lang::t('Magaca Kooxda', 'Group Name') ?></th>
                    <th><?= \App\Helpers\Lang::t('Laanta', 'Branch') ?></th>
                    <th><?= \App\Helpers\Lang::t('Hoggaamiyaha', 'Group Leader') ?></th>
                    <th><?= \App\Helpers\Lang::t('Xubnaha Firfircoon', 'Active Members') ?></th>
                    <th>30d Delinquency Lock</th>
                    <th><?= \App\Helpers\Lang::t('Xaaladda', 'Status') ?></th>
                    <th><?= \App\Helpers\Lang::t('Ficillada', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $g): ?>
                    <tr>
                        <td class="num-tabular"><strong><?= Security::e($g['group_code']) ?></strong></td>
                        <td><?= Security::e($g['group_name']) ?></td>
                        <td><?= Security::e($g['branch_name']) ?></td>
                        <td><?= Security::e($g['leader_name']) ?></td>
                        <td class="num-tabular"><?= (int) $g['members_count'] ?> members</td>
                        <td>
                            <?php if ((int) $g['overdue_30_count'] > 0): ?>
                                <span class="badge badge-danger">LOCKED (&gt;30d Overdue Member)</span>
                            <?php else: ?>
                                <span class="badge badge-success"><?= \App\Helpers\Lang::t('Nadiif (wuu xaq u leeyahay)', 'Clear (Eligible)') ?></span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?= $g['status'] === 'active' ? 'success' : 'warning' ?>"><?= Security::e($g['status']) ?></span></td>
                        <td>
                            <?php if ($g['status'] === 'pending_approval' && in_array($role, ['admin', 'branch_manager'], true)): ?>
                                <form method="POST" action="?route=group_approve" style="display:inline;" data-confirm="<?= \App\Helpers\Lang::t('Ma ansixinaysaa kooxdan si ay u hesho maalgelin kooxeed?', 'Approve this group for group financing?') ?>">
                                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                    <input type="hidden" name="group_id" value="<?= (int) $g['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-primary"><?= \App\Helpers\Lang::t('Ansixi Kooxda', 'Approve Group') ?></button>
                                </form>
                            <?php else: ?>
                                <span style="font-size:0.78rem;color:var(--text-muted);"><?= \App\Helpers\Lang::t('La Xaqiijiyay', 'Verified') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

