<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isSo = Lang::locale() === 'so';
$curUser = $currentUser ?? ($user ?? []);
$curRole = $curUser['role_slug'] ?? 'admin';
?>

<div class="card animate-in" style="margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:12px;">
        <div>
            <span class="badge badge-gold" style="font-weight:700;">
                <?= $isSo ? 'Maamulka & Nidaamka Awoodaha' : 'Institutional Governance & Hierarchy' ?>
            </span>
            <h2 style="font-size:1.3rem;font-weight:800;margin:6px 0 2px 0;color:var(--text-main);">
                <?= Security::e($sectionTitle ?? ($isSo ? 'Maamulka Nidaamka' : 'System Administration')) ?>
            </h2>
            <div style="font-size:0.8rem;color:var(--text-muted);">
                <?= $isSo ? 'Maamul laamaha SIMDA, shaqaalaha, awoodaha shaqada, iyo xogta nidaamka.' : 'Manage institution branches, staff accounts, role hierarchy, and audit logs.' ?>
            </div>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="?route=branches" class="btn btn-sm <?= ($mode ?? '') === 'branches' ? 'btn-gold' : 'btn-outline' ?>" style="font-weight:700;">
                <?= Icons::svg('building', 14) ?> <?= $isSo ? 'Laamaha' : 'Branches' ?> (<?= count($branches ?? []) ?>)
            </a>
            <a href="?route=users" class="btn btn-sm <?= ($mode ?? '') === 'users' ? 'btn-gold' : 'btn-outline' ?>" style="font-weight:700;">
                <?= Icons::svg('users', 14) ?> <?= $isSo ? 'Shaqaalaha & Users' : 'Users & Staff' ?> (<?= count($users ?? []) ?>)
            </a>
            <?php if (in_array($curRole, ['admin', 'manager'], true)): ?>
                <a href="?route=audit_logs" class="btn btn-sm <?= ($mode ?? '') === 'audit_logs' ? 'btn-gold' : 'btn-outline' ?>" style="font-weight:700;">
                    <?= Icons::svg('shield', 14) ?> <?= $isSo ? 'Diiwaanka Audit' : 'Audit Logs' ?>
                </a>
                <a href="?route=settings" class="btn btn-sm <?= ($mode ?? '') === 'settings' ? 'btn-gold' : 'btn-outline' ?>" style="font-weight:700;">
                    <?= Icons::svg('settings', 14) ?> <?= $isSo ? 'Settings' : 'Settings' ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. BRANCHES MANAGEMENT VIEW                                               -->
    <!-- ========================================================================= -->
    <?php if (($mode ?? '') === 'branches'): ?>
        <?php if (in_array($curRole, ['admin', 'manager'], true)): ?>
            <form method="POST" action="?route=branch_create" style="background:var(--bg-elevated);padding:18px;border-radius:12px;margin:16px 0 22px;border:1px solid var(--border-color);">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <span class="fid-contact-dot" style="background:var(--simda-green);color:#FFFFFF;width:24px;height:24px;"><?= Icons::svg('plus', 14) ?></span>
                    <h4 style="font-size:1rem;font-weight:800;margin:0;color:var(--text-main);"><?= $isSo ? 'Abuur Laan Cusub (Create New Branch)' : 'Create New Institution Branch' ?></h4>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label><?= $isSo ? 'Koodhka Laanta *' : 'Branch Code *' ?></label>
                        <input type="text" name="code" class="form-control" required placeholder="BR-HOD-02">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Magaca Laanta *' : 'Branch Name *' ?></label>
                        <input type="text" name="name" class="form-control" required placeholder="Hodan Central Branch">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Magaalada *' : 'City *' ?></label>
                        <input type="text" name="city" class="form-control" required placeholder="Mogadishu">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Degmada *' : 'District *' ?></label>
                        <input type="text" name="district" class="form-control" required placeholder="Hodan">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Cinwaanka / Goobta *' : 'Physical Address *' ?></label>
                        <input type="text" name="address" class="form-control" required placeholder="Taleex Boulevard">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Telefoonka Laanta *' : 'Branch Phone *' ?></label>
                        <input type="text" name="phone" class="form-control" required placeholder="+252 61 625 0000">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Madaxa Laanta (Header Branch)' : 'Header Branch / Manager Name' ?></label>
                        <input type="text" name="manager_name" class="form-control" placeholder="Cabdiraxmaan Cali">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700;margin-top:4px;">
                    <?= Icons::svg('plus', 14) ?> <?= $isSo ? 'Keydi Laanta Cusub' : 'Save Branch' ?>
                </button>
            </form>
        <?php else: ?>
            <div style="background:var(--bg-elevated);padding:12px 16px;border-radius:10px;margin:14px 0;font-size:0.82rem;color:var(--text-muted);">
                <?= $isSo ? 'Adigu waxaad tahay Maamule Laan (Header Branch). Waxaad arkaysaa macluumaadka laamaha, laakiin abuurista laamaha cusub waxaa awood u leh Maamulaha Guud iyo Admin-ka.' : 'As a Branch Manager, you can view the branch directory. Creation of new branches is reserved for General Manager and Admin.' ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="simda-table">
                <thead>
                    <tr>
                        <th><?= $isSo ? 'Koodhka' : 'Code' ?></th>
                        <th><?= $isSo ? 'Magaca Laanta' : 'Branch Name' ?></th>
                        <th><?= $isSo ? 'Magaalada / Degmada' : 'City / District' ?></th>
                        <th><?= $isSo ? 'Telefoonka' : 'Phone' ?></th>
                        <th><?= $isSo ? 'Madaxa Laanta (Header Branch)' : 'Header Branch / Manager' ?></th>
                        <th><?= $isSo ? 'Shaqaalaha' : 'Staff' ?></th>
                        <th><?= $isSo ? 'Macaamiisha' : 'Clients' ?></th>
                        <th><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($branches as $b): ?>
                        <tr>
                            <td class="num-tabular"><strong><?= Security::e($b['code']) ?></strong></td>
                            <td><strong><?= Security::e($b['name']) ?></strong></td>
                            <td><?= Security::e($b['city'] . ' / ' . $b['district']) ?></td>
                            <td class="num-tabular"><?= Security::e($b['phone']) ?></td>
                            <td>
                                <?php if (!empty($b['manager_name'])): ?>
                                    <span class="badge badge-gold" style="font-weight:700;"><?= Security::e($b['manager_name']) ?></span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="num-tabular">
                                <span class="badge badge-info"><?= (int) ($b['staff_count'] ?? 0) ?> <?= $isSo ? 'shaqaale' : 'staff' ?></span>
                            </td>
                            <td class="num-tabular">
                                <span class="badge badge-success"><?= (int) ($b['clients_count'] ?? 0) ?> <?= $isSo ? 'macmiil' : 'clients' ?></span>
                            </td>
                            <td><span class="badge badge-success"><?= $isSo ? 'Firfircoon' : 'Active' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <!-- ========================================================================= -->
    <!-- 2. USERS & STAFF MANAGEMENT VIEW (Strict Hierarchy Rule)                  -->
    <!-- ========================================================================= -->
    <?php elseif (($mode ?? '') === 'users'): ?>
        <!-- HIERARCHY RULES EXPLANATION BANNER -->
        <div style="background:rgba(214,182,108,0.12);border-left:4px solid var(--simda-gold);padding:14px 18px;border-radius:10px;margin:14px 0 18px;">
            <div style="font-weight:800;font-size:0.88rem;color:var(--text-main);margin-bottom:4px;">
                <?= Icons::svg('shield', 15) ?>
                <?= $isSo ? 'Xeerarka Awoodaha & Sameynta Isticmaalayaasha (Hierarchy Rules)' : 'Strict User Creation Hierarchy Rules' ?>
            </div>
            <div style="font-size:0.78rem;color:var(--text-main);line-height:1.55;">
                <?php if ($curRole === 'admin'): ?>
                    <?= $isSo
                        ? 'Adigu waxaad tahay <strong>Admin (Maamulaha Guud)</strong>. Waxaad abuuri kartaa: <strong>Manager, Header Branch, Employee (Shaqaale), iyo Client</strong>. Xeerka nidaamku <u>ma ogola</u> inaad abuurto Admin kale.'
                        : 'You are signed in as <strong>System Administrator</strong>. You can create: <strong>Manager, Header Branch, Employee, and Client</strong>. By institutional policy, you <u>cannot</u> create another Admin.' ?>
                <?php elseif ($curRole === 'manager'): ?>
                    <?= $isSo
                        ? 'Adigu waxaad tahay <strong>General Manager</strong>. Waxaad abuuri kartaa: <strong>Header Branch, Employee (Shaqaale), iyo Client</strong>. Xeerka nidaamku <u>ma ogola</u> inaad abuurto Admin ama Manager kale.'
                        : 'You are signed in as <strong>General Operations Manager</strong>. You can create: <strong>Header Branch, Employee, and Client</strong>. You <u>cannot</u> create an Admin or another Manager.' ?>
                <?php elseif ($curRole === 'branch_manager'): ?>
                    <?= $isSo
                        ? 'Adigu waxaad tahay <strong>Header Branch (Maamulaha Laanta)</strong>. Waxaad abuuri kartaa: <strong>Employee (Shaqaale) iyo Client</strong> oo ka tirsan laantaada kaliya. Xeerka nidaamku <u>ma ogola</u> inaad abuurto Maamule kale.'
                        : 'You are signed in as <strong>Header Branch (Branch Manager)</strong>. You can create: <strong>Employees and Clients</strong> strictly for your assigned branch. You <u>cannot</u> create another Manager.' ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- CREATE STAFF USER FORM -->
        <?php if (!empty($roles)): ?>
            <form method="POST" action="?route=user_create" id="createUserForm" style="background:var(--bg-elevated);padding:20px;border-radius:14px;margin:14px 0 24px;border:1px solid var(--border-color);box-shadow:0 4px 14px rgba(0,0,0,0.03);">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                    <span class="fid-contact-dot" style="background:var(--simda-green);color:#FFFFFF;width:26px;height:26px;"><?= Icons::svg('user-plus', 14) ?></span>
                    <h4 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);"><?= $isSo ? 'Diiwaangeli Isticmaale / Shaqaale Cusub' : 'Create Staff or User Account' ?></h4>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label><?= $isSo ? 'Magaca Buuxa *' : 'Full Name *' ?></label>
                        <input type="text" name="full_name" id="userFullNameInput" class="form-control" required placeholder="Cali Cabdi Cali" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Username *' : 'Username *' ?></label>
                        <input type="text" name="username" id="userUsernameInput" class="form-control" required placeholder="ali.abdi" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Email-ka Shaqaalaha * (Halkan ayaa farriinta loogu diri doonaa)' : 'Email Address * (Credentials will be sent here)' ?></label>
                        <input type="email" name="email" id="userEmailInput" class="form-control" required placeholder="ali@simda.so" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Telefoonka *' : 'Phone Number *' ?></label>
                        <input type="text" name="phone" class="form-control" required placeholder="+252 61 700 0000">
                    </div>
                    <div class="form-group">
                        <label><?= $isSo ? 'Doorka Shaqada (Role) *' : 'Assignable Role *' ?></label>
                        <select name="role_id" id="userRoleSelect" class="form-control" required style="font-weight:700;">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int) $r['id'] ?>" data-slug="<?= Security::e($r['slug']) ?>">
                                    <?= Security::e($isSo ? $r['name_so'] : $r['name_en']) ?> (<?= Security::e($r['slug']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="userBranchFormGroup">
                        <label id="userBranchLabel"><?= $isSo ? 'Qoondaynta Laanta * (Branch Assignment)' : 'Branch Assignment *' ?></label>
                        <?php if ($curRole === 'branch_manager'): ?>
                            <!-- Branch manager is locked to their own branch -->
                            <input type="hidden" name="branch_id" value="<?= (int) ($curUser['branch_id'] ?? 0) ?>">
                            <input type="text" class="form-control" value="<?= Security::e($curUser['branch_name'] ?? 'Your Assigned Branch') ?> (Locked to your branch)" readonly style="background:var(--bg-surface);color:var(--text-muted);font-weight:700;">
                        <?php else: ?>
                            <select name="branch_id" id="userBranchSelect" class="form-control" style="font-weight:600;">
                                <option value="" id="userBranchEmptyOption">-- <?= $isSo ? 'Dooro Laanta Gaarka ah' : 'Select Branch' ?> --</option>
                                <?php foreach ($branches as $b): ?>
                                    <option value="<?= (int) $b['id'] ?>"><?= Security::e($b['name']) ?> (<?= Security::e($b['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div id="userBranchManagerNote" style="display:none;margin-top:6px;font-size:0.83rem;color:var(--gold-color);font-weight:700;background:rgba(214,182,108,0.1);padding:8px 12px;border-radius:8px;border:1px solid rgba(214,182,108,0.3);">
                                <?= Icons::svg('check-circle', 14) ?> <?= $isSo ? 'Maamulaha Guud (Manager) wuxuu maamulaa dhammaan shirkadda (Dhammaan Laamaha / Company-Wide) looma qoondeeyo hal laan.' : 'General Operations Manager oversees the entire company across all branches.' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><?= $isSo ? 'Furaha Sirta ah (Password) *' : 'Account Initial Password *' ?></label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="text" name="password" id="userPasswordInput" class="form-control" placeholder="<?= $isSo ? 'Si toos ah ayaa loo dhalinayaa (tusaale: ali8788@!)' : 'Auto-generated (e.g. ali8788@!)' ?>" style="font-family:monospace;font-weight:800;letter-spacing:1px;max-width:380px;">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnGenPassword" style="white-space:nowrap;font-weight:700;">
                                <?= Icons::svg('refresh-cw', 14) ?> <?= $isSo ? 'Dhal Fure Cusub' : 'Regenerate' ?>
                            </button>
                        </div>
                        <small style="display:block;margin-top:5px;font-size:0.8rem;color:var(--text-muted);">
                            <?= $isSo
                                ? '💡 <strong>Fiiro gaar ah:</strong> Qof kasta waxaa loo dhalinayaa fure sir ah oo u gaar ah (magaciisa koowaad + 4 god oo random ah + @! sida <code>ali8788@!</code>). Furahan iyo macluumaadka akoonkiisa waxaa si toos ah loogu diri doonaa email-ka shaqaalaha.'
                                : '💡 <strong>Notice:</strong> Each user gets a unique generated password (e.g. <code>ali8788@!</code>) which will be automatically delivered to their registered email address along with login instructions.' ?>
                        </small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700;margin-top:6px;padding:8px 20px;">
                    <?= Icons::svg('user-plus', 14) ?> <?= $isSo ? 'Diiwaangeli & U Dir Email-ka' : 'Create User & Send Welcome Email' ?>
                </button>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const roleSelect = document.getElementById('userRoleSelect');
                const branchSelect = document.getElementById('userBranchSelect');
                const branchNote = document.getElementById('userBranchManagerNote');
                const branchLabel = document.getElementById('userBranchLabel');
                const nameInput = document.getElementById('userFullNameInput');
                const usernameInput = document.getElementById('userUsernameInput');
                const pwdInput = document.getElementById('userPasswordInput');
                const genBtn = document.getElementById('btnGenPassword');

                function updateRoleBranchState() {
                    if (!roleSelect) return;
                    const opt = roleSelect.options[roleSelect.selectedIndex];
                    const slug = opt ? opt.getAttribute('data-slug') : '';

                    if (slug === 'manager') {
                        if (branchSelect) {
                            branchSelect.value = '';
                            branchSelect.disabled = true;
                            branchSelect.required = false;
                            branchSelect.style.opacity = '0.55';
                        }
                        if (branchNote) branchNote.style.display = 'block';
                        if (branchLabel) branchLabel.innerHTML = '<?= $isSo ? 'Maamulka Laamaha: Dhammaan Shirkadda (Company-Wide)' : 'Branch Management: Company-Wide (All Branches)' ?>';
                    } else {
                        if (branchSelect) {
                            branchSelect.disabled = false;
                            branchSelect.style.opacity = '1';
                            if (slug === 'branch_manager' || slug === 'loan_officer') {
                                branchSelect.required = true;
                                if (branchLabel) branchLabel.innerHTML = '<?= $isSo ? 'Qoondaynta Laanta * (Waa Qasab in la doorto)' : 'Branch Assignment * (Required)' ?>';
                            } else {
                                branchSelect.required = false;
                                if (branchLabel) branchLabel.innerHTML = '<?= $isSo ? 'Qoondaynta Laanta (Branch Assignment)' : 'Branch Assignment' ?>';
                            }
                        }
                        if (branchNote) branchNote.style.display = 'none';
                    }
                }

                function makeUniquePassword() {
                    const raw = nameInput ? nameInput.value.trim() : '';
                    const first = raw.split(/\s+/)[0] || 'simda';
                    const clean = first.toLowerCase().replace(/[^a-z0-9]/g, '') || 'simda';
                    const digits = Math.floor(1000 + Math.random() * 9000);
                    return clean + digits + '@!';
                }

                if (genBtn && pwdInput) {
                    genBtn.addEventListener('click', function () {
                        pwdInput.value = makeUniquePassword();
                    });
                }

                if (nameInput && pwdInput) {
                    nameInput.addEventListener('input', function () {
                        // Generate username suggestion if empty
                        if (usernameInput && (!usernameInput.value || usernameInput.dataset.auto === 'true')) {
                            const words = nameInput.value.trim().toLowerCase().split(/\s+/);
                            if (words.length >= 2) {
                                usernameInput.value = words[0].replace(/[^a-z0-9]/g, '') + '.' + words[1].replace(/[^a-z0-9]/g, '');
                                usernameInput.dataset.auto = 'true';
                            } else if (words.length === 1 && words[0]) {
                                usernameInput.value = words[0].replace(/[^a-z0-9]/g, '');
                                usernameInput.dataset.auto = 'true';
                            }
                        }
                        // Generate password suggestion
                        if (!pwdInput.value || pwdInput.dataset.auto === 'true') {
                            pwdInput.value = makeUniquePassword();
                            pwdInput.dataset.auto = 'true';
                        }
                    });
                    pwdInput.addEventListener('input', function () {
                        pwdInput.dataset.auto = 'false';
                    });
                }

                if (usernameInput) {
                    usernameInput.addEventListener('input', function () {
                        usernameInput.dataset.auto = 'false';
                    });
                }

                if (roleSelect) {
                    roleSelect.addEventListener('change', updateRoleBranchState);
                    updateRoleBranchState();
                }
            });
            </script>
        <?php endif; ?>

        <!-- USERS TABLE -->
        <div class="table-responsive">
            <table class="simda-table">
                <thead>
                    <tr>
                        <th><?= $isSo ? 'Username' : 'Username' ?></th>
                        <th><?= $isSo ? 'Magaca Buuxa' : 'Full Name' ?></th>
                        <th><?= $isSo ? 'Doorka (Role)' : 'Role' ?></th>
                        <th><?= $isSo ? 'Laanta (Branch Assignment)' : 'Branch Assignment' ?></th>
                        <th><?= $isSo ? 'Email / Telefoon' : 'Contact' ?></th>
                        <th><?= $isSo ? 'Galitaankii Ugu Dambeeyay' : 'Last Login' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?= Security::e($u['username']) ?></strong></td>
                            <td><?= Security::e($u['full_name']) ?></td>
                            <td>
                                <span class="badge <?= $u['role_slug'] === 'admin' ? 'badge-danger' : ($u['role_slug'] === 'manager' ? 'badge-gold' : ($u['role_slug'] === 'branch_manager' ? 'badge-info' : 'badge-success')) ?>" style="font-weight:700;">
                                    <?= Security::e($isSo ? ($u['role_name_so'] ?? $u['role_slug']) : ($u['role_name_en'] ?? $u['role_slug'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['role_slug'] === 'manager'): ?>
                                    <span class="badge badge-gold" style="font-weight:700;display:inline-flex;align-items:center;gap:4px;">
                                        ★ <?= $isSo ? 'Dhammaan Laamaha (All Branches)' : 'All Branches (Company-Wide)' ?>
                                    </span>
                                <?php elseif (!empty($u['branch_name'])): ?>
                                    <span style="font-weight:600;color:var(--simda-green);"><?= Security::e($u['branch_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?= $isSo ? 'Xarunta Guud (HQ)' : 'Head Office' ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= Security::e($u['email']) ?> • <span class="num-tabular"><?= Security::e($u['phone']) ?></span></td>
                            <td class="num-tabular" style="font-size:0.78rem;"><?= Security::e($u['last_login_at'] ?? ($isSo ? 'Weli ma gelin' : 'Never')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <!-- ========================================================================= -->
    <!-- 3. SYSTEM SETTINGS VIEW                                                   -->
    <!-- ========================================================================= -->
    <?php elseif (($mode ?? '') === 'settings'): ?>
        <form method="POST" action="?route=settings_save" style="margin-top:16px;">
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
            <button type="submit" class="btn btn-primary" style="margin-top:14px;"><?= $isSo ? 'Keydi Settings-ka' : 'Save System Settings' ?></button>
        </form>

    <!-- ========================================================================= -->
    <!-- 4. AUDIT LOGS VIEW                                                        -->
    <!-- ========================================================================= -->
    <?php else: ?>
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
