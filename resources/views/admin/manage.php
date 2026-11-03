<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
?>

<div class="card animate-in" style="margin-bottom:24px;">
    <span class="badge badge-gold">Administration & Security Governance</span>
    <h2 style="font-size:1.25rem;margin:6px 0;"><?= Security::e($sectionTitle ?? 'System Administration') ?></h2>

    <?php if (($mode ?? '') === 'branches'): ?>
        <form method="POST" action="?route=branch_create" style="background:var(--bg-elevated);padding:16px;border-radius:12px;margin:14px 0 22px;" data-confirm="Create this new branch?">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
            <h4 style="margin-bottom:10px;">Create New Branch</h4>
            <div class="form-grid">
                <div class="form-group"><label>Branch Code *</label><input type="text" name="code" class="form-control" required placeholder="BR-BOS-04"></div>
                <div class="form-group"><label>Branch Name *</label><input type="text" name="name" class="form-control" required placeholder="Bosaso Port Branch"></div>
                <div class="form-group"><label>City *</label><input type="text" name="city" class="form-control" required placeholder="Bosaso"></div>
                <div class="form-group"><label>District *</label><input type="text" name="district" class="form-control" required placeholder="Bari"></div>
                <div class="form-group"><label>Address *</label><input type="text" name="address" class="form-control" required placeholder="Commercial Road"></div>
                <div class="form-group"><label>Phone *</label><input type="text" name="phone" class="form-control" required placeholder="+252 90 700 4004"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Save Branch</button>
        </form>

        <div class="table-responsive">
            <table class="simda-table">
                <thead><tr><th>Code</th><th>Branch Name</th><th>City / District</th><th>Phone</th><th>Manager</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($branches as $b): ?>
                        <tr>
                            <td class="num-tabular"><strong><?= Security::e($b['code']) ?></strong></td>
                            <td><?= Security::e($b['name']) ?></td>
                            <td><?= Security::e($b['city'] . ' / ' . $b['district']) ?></td>
                            <td class="num-tabular"><?= Security::e($b['phone']) ?></td>
                            <td><?= Security::e($b['manager_name'] ?? '—') ?></td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif (($mode ?? '') === 'users'): ?>
        <form method="POST" action="?route=user_create" style="background:var(--bg-elevated);padding:16px;border-radius:12px;margin:14px 0 22px;" data-confirm="Create this staff user account?">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
            <h4 style="margin-bottom:10px;">Create Staff User Account</h4>
            <div class="form-grid">
                <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" class="form-control" required></div>
                <div class="form-group"><label>Username *</label><input type="text" name="username" class="form-control" required></div>
                <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label>Phone *</label><input type="text" name="phone" class="form-control" required></div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role_id" class="form-control" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int) $r['id'] ?>"><?= Security::e($r['name_en']) ?> (<?= Security::e($r['slug']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Branch Assignment</label>
                    <select name="branch_id" class="form-control">
                        <option value="">-- Head Office (All Branches) --</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= (int) $b['id'] ?>"><?= Security::e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Initial Password *</label><input type="password" name="password" class="form-control" required value="Staff@123"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Create User</button>
        </form>

        <div class="table-responsive">
            <table class="simda-table">
                <thead><tr><th>Username</th><th>Full Name</th><th>Role</th><th>Branch</th><th>Email / Phone</th><th>Last Login</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?= Security::e($u['username']) ?></strong></td>
                            <td><?= Security::e($u['full_name']) ?></td>
                            <td><span class="badge badge-gold"><?= Security::e($u['role_slug']) ?></span></td>
                            <td><?= Security::e($u['branch_name'] ?? 'Head Office') ?></td>
                            <td><?= Security::e($u['email']) ?> • <?= Security::e($u['phone']) ?></td>
                            <td class="num-tabular"><?= Security::e($u['last_login_at'] ?? 'Never') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif (($mode ?? '') === 'settings'): ?>
        <form method="POST" action="?route=settings_save" style="margin-top:16px;" data-confirm="Update institutional system settings?">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
            <div class="table-responsive">
                <table class="simda-table">
                    <thead><tr><th>Setting Key</th><th>Value</th><th>Description</th></tr></thead>
                    <tbody>
                        <?php foreach ($settingsList as $s): ?>
                            <tr>
                                <td><code><?= Security::e($s['setting_key']) ?></code></td>
                                <td>
                                    <input type="text" name="settings[<?= Security::e($s['setting_key']) ?>]" class="form-control num-tabular" value="<?= Security::e($s['setting_value']) ?>">
                                </td>
                                <td style="color:var(--text-secondary);font-size:0.84rem;"><?= Security::e($s['description']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:14px;">Save System Settings</button>
        </form>

    <?php else: /* audit_logs */ ?>
        <div class="table-responsive" style="margin-top:14px;">
            <table class="simda-table">
                <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Entity</th><th>Description</th><th>IP Address</th></tr></thead>
                <tbody>
                    <?php foreach ($auditLogs as $log): ?>
                        <tr>
                            <td class="num-tabular" style="font-size:0.78rem;"><?= Security::e($log['created_at']) ?></td>
                            <td><?= Security::e($log['user_name'] ?? 'System') ?></td>
                            <td><span class="badge badge-info"><?= Security::e($log['action']) ?></span></td>
                            <td class="num-tabular"><?= Security::e($log['entity_type']) ?> #<?= (int) ($log['entity_id'] ?? 0) ?></td>
                            <td style="font-size:0.84rem;"><?= Security::e($log['description']) ?></td>
                            <td class="num-tabular" style="font-size:0.78rem;"><?= Security::e($log['ip_address'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

