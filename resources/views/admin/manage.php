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
            <?php if ($curRole === 'admin'): ?>
                <a href="?route=permissions" class="btn btn-sm <?= ($mode ?? '') === 'permissions' ? 'btn-gold' : 'btn-outline' ?>" style="font-weight:700;">
                    <?= Icons::svg('shield', 14) ?> <?= $isSo ? 'Awoodaha & Ogolaanshaha' : 'Permissions & Access' ?>
                </a>
            <?php endif; ?>
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
            <form method="POST" action="?route=branch_create" style="background:var(--bg-elevated);padding:18px;border-radius:12px;margin:16px 0 22px;border:1px solid var(--border-color);box-shadow:0 4px 14px rgba(0,0,0,0.03);">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="fid-contact-dot" style="background:var(--simda-green);color:#FFFFFF;width:26px;height:26px;"><?= Icons::svg('plus', 14) ?></span>
                        <h4 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);"><?= $isSo ? 'Abuur Laan Cusub (Create New Branch)' : 'Create New Institution Branch' ?></h4>
                    </div>
                    <span class="badge badge-gold" style="font-weight:700;">
                        <?= $isSo ? 'Maamulaha Guud & Admin ayaa awood u leh' : 'Admin & GM Authorized' ?>
                    </span>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label><?= $isSo ? 'Koodhka Laanta *' : 'Branch Code *' ?></label>
                        <div style="display:flex;gap:6px;">
                            <input type="text" name="code" id="branchCodeInput" class="form-control" required placeholder="BR-HOD-02" style="font-weight:700;text-transform:uppercase;">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="suggestBranchCode()" style="white-space:nowrap;font-weight:700;" title="Auto-suggest code">
                                <?= Icons::svg('refresh-cw', 12) ?> Auto
                            </button>
                        </div>
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
                        <input type="text" name="address" class="form-control" required placeholder="Taleex Boulevard, Wadada 21-ka">
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
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700;margin-top:6px;padding:8px 22px;">
                    <?= Icons::svg('plus', 14) ?> <?= $isSo ? 'Keydi Laanta Cusub' : 'Save Branch' ?>
                </button>
            </form>

            <script>
            function suggestBranchCode() {
                var nextNum = <?= count($branches ?? []) + 1 ?>;
                var pad = nextNum < 10 ? '0' + nextNum : nextNum;
                var inp = document.getElementById('branchCodeInput');
                if (inp) {
                    inp.value = 'BR-' + pad;
                }
            }
            </script>
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
                        <th><?= $isSo ? 'Madaxa Laanta' : 'Header Branch' ?></th>
                        <th><?= $isSo ? 'Shaqaalaha' : 'Staff' ?></th>
                        <th><?= $isSo ? 'Macaamiisha' : 'Clients' ?></th>
                        <th><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                        <?php if ($curRole === 'admin'): ?>
                            <th><?= $isSo ? 'Hawlgalka' : 'Action' ?></th>
                        <?php endif; ?>
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
                            <td>
                                <span class="badge <?= $b['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $b['is_active'] ? ($isSo ? 'Firfircoon' : 'Active') : ($isSo ? 'Xiran' : 'Inactive') ?>
                                </span>
                            </td>
                            <?php if ($curRole === 'admin'): ?>
                                <td>
                                    <?php if ((int)$b['id'] !== 1): ?>
                                        <form method="POST" action="?route=branch_toggle" style="display:inline;">
                                            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                            <input type="hidden" name="branch_id" value="<?= (int) $b['id'] ?>">
                                            <button type="submit" class="btn btn-xs <?= $b['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" style="padding:3px 8px;font-size:0.75rem;font-weight:700;">
                                                <?= $b['is_active'] ? ($isSo ? 'Deactivate' : 'Deactivate') : ($isSo ? 'Activate' : 'Activate') ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="font-size:0.72rem;"><?= $isSo ? 'Xarun Joogto (HQ)' : 'Protected HQ' ?></span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
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
            <div style="display:flex;justify-content:space-between;align-items:center;margin:14px 0 10px;">
                <div style="font-size:0.95rem;font-weight:800;color:var(--text-main);display:flex;align-items:center;gap:8px;">
                    <span class="fid-contact-dot" style="background:rgba(35,132,91,0.12);color:var(--simda-green);width:26px;height:26px;"><?= Icons::svg('user-plus', 14) ?></span>
                    <?= $isSo ? 'Diiwaangelinta Shaqaalaha & Isticmaalayaasha' : 'Staff & User Registration' ?>
                </div>
                <button type="button" class="btn btn-xs btn-outline" id="btnToggleCreateUser" onclick="toggleCreateUserForm()" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                    <span id="createUserToggleIcon"><?= Icons::svg('plus', 12) ?></span>
                    <span id="createUserToggleText"><?= $isSo ? 'Foomka Cusub' : 'Toggle Form' ?></span>
                </button>
            </div>

            <form method="POST" action="?route=user_create" id="createUserForm" style="display:none;background:var(--bg-elevated);padding:20px;border-radius:14px;margin-bottom:24px;border:1px solid var(--border-color);box-shadow:0 4px 14px rgba(0,0,0,0.03);animation:simdaFadeIn 0.25s ease;">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border-color);">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="fid-contact-dot" style="background:var(--simda-green);color:#FFFFFF;width:26px;height:26px;"><?= Icons::svg('user-plus', 14) ?></span>
                        <h4 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);"><?= $isSo ? 'Diiwaangeli Isticmaale / Shaqaale Cusub' : 'Create Staff or User Account' ?></h4>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline" onclick="toggleCreateUserForm(false)" style="padding:4px 8px;">✕</button>
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
                                <?= Icons::svg('refresh', 14) ?> <?= $isSo ? 'Dhal Fure Cusub' : 'Regenerate' ?>
                            </button>
                        </div>
                        <small style="display:block;margin-top:5px;font-size:0.8rem;color:var(--text-muted);">
                            <?= $isSo
                                ? '💡 <strong>Fiiro gaar ah:</strong> Qof kasta waxaa loo dhalinayaa fure sir ah oo u gaar ah (magaciisa koowaad + 4 god oo random ah + @! sida <code>ali8788@!</code>). Furahan iyo macluumaadka akoonkiisa waxaa si toos ah loogu diri doonaa email-ka shaqaalaha.'
                                : '💡 <strong>Notice:</strong> Each user gets a unique generated password (e.g. <code>ali8788@!</code>) which will be automatically delivered to their registered email address along with login instructions.' ?>
                        </small>
                    </div>
                </div>
                <div style="display:flex;gap:10px;align-items:center;margin-top:10px;">
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700;padding:9px 22px;">
                        <?= Icons::svg('user-plus', 14) ?> <?= $isSo ? 'Diiwaangeli & U Dir Email-ka' : 'Create User & Send Welcome Email' ?>
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="toggleCreateUserForm(false)">
                        <?= $isSo ? 'Ka noqo' : 'Cancel' ?>
                    </button>
                </div>
            </form>

            <script>
            function toggleCreateUserForm(forceState) {
                const form = document.getElementById('createUserForm');
                const btnText = document.getElementById('createUserToggleText');
                const btnIcon = document.getElementById('createUserToggleIcon');
                if (!form) return;
                const isOpening = (typeof forceState === 'boolean') ? forceState : (form.style.display === 'none' || !form.style.display);
                form.style.display = isOpening ? 'block' : 'none';
                if (btnText) btnText.textContent = isOpening ? '<?= $isSo ? 'Qari Foomka' : 'Hide Form' ?>' : '<?= $isSo ? 'Foomka Cusub' : 'Toggle Form' ?>';
                if (btnIcon) btnIcon.innerHTML = isOpening ? '<?= Icons::svg('x-circle', 12) ?>' : '<?= Icons::svg('plus', 12) ?>';
                if (isOpening) {
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

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

        <?php
        $totalStaff = count($users);
        $activeStaff = 0;
        $inactiveStaff = 0;
        foreach ($users as $u) {
            if ((int) $u['is_active'] === 1) {
                $activeStaff++;
            } else {
                $inactiveStaff++;
            }
        }
        ?>

        <!-- MODERN USERS TOOLBAR & STATS -->
        <div style="background:var(--bg-elevated);border:1px solid var(--border-color);border-radius:14px;padding:16px 20px;margin-bottom:16px;box-shadow:0 2px 10px rgba(0,0,0,0.02);">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
                <!-- Left: Title & Live Stat Badges -->
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <div style="font-weight:800;font-size:1.02rem;color:var(--text-main);display:flex;align-items:center;gap:8px;">
                        <?= Icons::svg('users', 18) ?> <?= $isSo ? 'Diiwaanka Shaqaalaha & Isticmaalayaasha' : 'Registered Staff & Users' ?>
                    </div>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <span class="badge badge-secondary" style="font-weight:700;font-size:0.75rem;" title="Total Users">
                            <?= $isSo ? 'Guud' : 'Total' ?>: <strong style="margin-left:3px;" id="totalStaffCount"><?= $totalStaff ?></strong>
                        </span>
                        <span class="badge badge-success" style="font-weight:700;font-size:0.75rem;background:rgba(35,132,91,0.15);color:var(--simda-green);border:1px solid rgba(35,132,91,0.3);" title="Active Users">
                            ● <?= $isSo ? 'Firfircoon' : 'Active' ?>: <strong style="margin-left:3px;" id="activeStaffCount"><?= $activeStaff ?></strong>
                        </span>
                        <?php if ($inactiveStaff > 0): ?>
                            <span class="badge badge-secondary" style="font-weight:700;font-size:0.75rem;background:rgba(120,120,120,0.15);border:1px solid rgba(120,120,120,0.3);" title="Deactivated Users">
                                ○ <?= $isSo ? 'Dansan' : 'Inactive' ?>: <strong style="margin-left:3px;" id="inactiveStaffCount"><?= $inactiveStaff ?></strong>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Quick Filters & Search -->
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <!-- Filter Pills -->
                    <div style="display:inline-flex;background:var(--bg-surface);padding:3px;border-radius:10px;border:1px solid var(--border-color);">
                        <button type="button" class="staff-filter-pill is-active" data-filter="all" onclick="setStaffFilter('all', this)">
                            <?= $isSo ? 'Dhammaan' : 'All' ?>
                        </button>
                        <button type="button" class="staff-filter-pill" data-filter="active" onclick="setStaffFilter('active', this)">
                            ● <?= $isSo ? 'Firfircoon' : 'Active' ?>
                        </button>
                        <button type="button" class="staff-filter-pill" data-filter="inactive" onclick="setStaffFilter('inactive', this)">
                            ○ <?= $isSo ? 'Dansan' : 'Inactive' ?>
                        </button>
                    </div>

                    <!-- Role Dropdown -->
                    <select id="staffRoleFilter" class="form-control" style="width:auto;min-width:150px;font-size:0.82rem;font-weight:700;padding:6px 10px;height:auto;" onchange="filterStaffTable()">
                        <option value=""><?= $isSo ? 'Dhammaan Doorarka (Roles)' : 'All Roles' ?></option>
                        <option value="admin">System Admin</option>
                        <option value="manager">Manager</option>
                        <option value="branch_manager">Branch Manager</option>
                        <option value="loan_officer">Loan Officer</option>
                        <option value="client">Client</option>
                    </select>

                    <!-- Search Input -->
                    <div style="position:relative;width:240px;">
                        <input type="text" id="staffSearchInput" class="form-control" placeholder="<?= $isSo ? 'Raadi magac, email, door...' : 'Search staff, email, role...' ?>" style="padding-left:32px;font-size:0.83rem;height:34px;" oninput="filterStaffTable()" autocomplete="off">
                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
                            <?= Icons::svg('search', 14) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .staff-filter-pill {
            border: none;
            background: transparent;
            padding: 5px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            color: var(--text-muted);
            transition: all 0.15s ease;
        }
        .staff-filter-pill:hover {
            color: var(--text-main);
        }
        .staff-filter-pill.is-active {
            background: var(--simda-green);
            color: #FFFFFF;
            box-shadow: 0 2px 6px rgba(35,132,91,0.25);
        }
        .simda-staff-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.85rem;
        }
        .simda-staff-table th {
            background: var(--bg-surface);
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 12px 14px;
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
        }
        .simda-staff-table td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            vertical-align: middle;
            background: var(--bg-elevated);
        }
        .simda-staff-table tr.is-deactivated td {
            opacity: 0.72;
            background: rgba(120,120,120,0.03);
        }
        .simda-staff-table tr:hover td {
            background: rgba(35,132,91,0.035);
        }
        .user-avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.82rem;
            color: #FFFFFF;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        }
        .action-btn-group {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }
        /* MODAL STYLES */
        .simda-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 26, 22, 0.72);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            animation: simdaModalFadeIn 0.2s ease-out;
        }
        .simda-modal-box {
            background: var(--bg-elevated);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            width: 100%;
            max-width: 640px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.35);
            overflow: hidden;
            animation: simdaModalScaleUp 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes simdaModalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes simdaModalScaleUp {
            from { opacity: 0; transform: scale(0.94) translateY(12px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        </style>

        <!-- USERS TABLE CONTAINER -->
        <div style="background:var(--bg-elevated);border:1px solid var(--border-color);border-radius:14px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.03);">
            <div class="table-responsive" style="margin:0;">
                <table class="simda-staff-table" id="staffTable">
                    <thead>
                        <tr>
                            <th style="width:260px;"><?= $isSo ? 'Shaqaalaha / Qofka' : 'Staff Profile' ?></th>
                            <th><?= $isSo ? 'Doorka (Role)' : 'Role' ?></th>
                            <th><?= $isSo ? 'Laanta (Branch)' : 'Branch Assignment' ?></th>
                            <th><?= $isSo ? 'Xiriirka (Contact)' : 'Contact' ?></th>
                            <th><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                            <th><?= $isSo ? 'Galitaankii Ugu Dambeeyay' : 'Last Login' ?></th>
                            <th style="text-align:right;padding-right:18px;"><?= $isSo ? 'Ficilada (Actions)' : 'Actions' ?></th>
                        </tr>
                    </thead>
                    <tbody id="staffTableBody">
                        <?php
                        $avatarGradients = [
                            'linear-gradient(135deg, #123B32 0%, #23845B 100%)',
                            'linear-gradient(135deg, #8B6E32 0%, #D6B66C 100%)',
                            'linear-gradient(135deg, #1C4B82 0%, #3B82F6 100%)',
                            'linear-gradient(135deg, #6B21A8 0%, #A855F7 100%)',
                            'linear-gradient(135deg, #0F766E 0%, #14B8A6 100%)',
                            'linear-gradient(135deg, #991B1B 0%, #EF4444 100%)',
                        ];
                        ?>
                        <?php foreach ($users as $idx => $u): ?>
                            <?php
                                $uId = (int) $u['id'];
                                $isActive = (int) ($u['is_active'] ?? 1);
                                $roleSlug = (string) ($u['role_slug'] ?? '');
                                $isPermBlocked = ((int) ($u['lockout_tier'] ?? 0) >= 5) || (!$isActive && !empty($u['security_block_reason']));
                                $isTempLocked = !empty($u['locked_until']) && strtotime((string) $u['locked_until']) > time();
                                $isSelf = ($uId === (int) ($curUser['id'] ?? 0));

                                // Initials for avatar
                                $words = preg_split('/\s+/', trim((string) $u['full_name']));
                                $initials = '';
                                if (!empty($words[0])) $initials .= mb_substr($words[0], 0, 1);
                                if (!empty($words[1])) $initials .= mb_substr($words[1], 0, 1);
                                if ($initials === '') $initials = mb_substr((string) $u['username'], 0, 2);
                                $initials = strtoupper($initials);
                                $gradBg = $avatarGradients[$idx % count($avatarGradients)];

                                // Search metadata
                                $searchMeta = strtolower($u['full_name'] . ' ' . $u['username'] . ' ' . $u['email'] . ' ' . $u['phone'] . ' ' . $roleSlug . ' ' . ($u['branch_name'] ?? ''));
                            ?>
                            <tr class="staff-row <?= $isActive ? 'is-active' : 'is-deactivated' ?>"
                                id="userRow_<?= $uId ?>"
                                data-uid="<?= $uId ?>"
                                data-active="<?= $isActive ? '1' : '0' ?>"
                                data-role="<?= Security::e($roleSlug) ?>"
                                data-search="<?= Security::e($searchMeta) ?>">
                                
                                <!-- USER PROFILE -->
                                <td>
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <div class="user-avatar-circle" style="background:<?= $gradBg ?>;">
                                            <?= Security::e($initials) ?>
                                        </div>
                                        <div style="min-width:0;">
                                            <div style="font-weight:800;font-size:0.9rem;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                <?= Security::e($u['full_name']) ?>
                                                <?php if ($isSelf): ?>
                                                    <span class="badge badge-gold" style="font-size:0.65rem;padding:2px 6px;margin-left:4px;"><?= $isSo ? 'Adiga (You)' : 'You' ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div style="font-size:0.75rem;color:var(--text-muted);display:flex;align-items:center;gap:4px;margin-top:2px;">
                                                <code style="background:rgba(0,0,0,0.04);padding:1px 5px;border-radius:4px;color:var(--simda-green);font-weight:700;">@<?= Security::e($u['username']) ?></code>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- ROLE BADGE -->
                                <td>
                                    <?php
                                    $roleBadgeClass = match ($roleSlug) {
                                        'admin' => 'badge-danger',
                                        'manager' => 'badge-gold',
                                        'branch_manager' => 'badge-info',
                                        'loan_officer' => 'badge-success',
                                        default => 'badge-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $roleBadgeClass ?>" style="font-weight:800;letter-spacing:0.3px;padding:5px 9px;display:inline-flex;align-items:center;gap:4px;">
                                        <?= Icons::svg($roleSlug === 'admin' ? 'shield' : ($roleSlug === 'manager' ? 'award' : 'briefcase'), 11) ?>
                                        <?= Security::e($isSo ? ($u['role_name_so'] ?? $roleSlug) : ($u['role_name_en'] ?? $roleSlug)) ?>
                                    </span>
                                </td>

                                <!-- BRANCH ASSIGNMENT -->
                                <td>
                                    <?php if ($roleSlug === 'manager'): ?>
                                        <span class="badge badge-gold" style="font-weight:700;display:inline-flex;align-items:center;gap:4px;padding:4px 8px;">
                                            ★ <?= $isSo ? 'Dhammaan Laamaha' : 'All Branches (Company-Wide)' ?>
                                        </span>
                                    <?php elseif (!empty($u['branch_name'])): ?>
                                        <div style="display:inline-flex;align-items:center;gap:6px;font-weight:700;color:var(--text-main);">
                                            <span style="color:var(--simda-green);"><?= Icons::svg('building', 13) ?></span>
                                            <?= Security::e($u['branch_name']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="font-weight:600;"><?= $isSo ? 'Xarunta Guud (HQ)' : 'Head Office' ?></span>
                                    <?php endif; ?>
                                </td>

                                <!-- CONTACT -->
                                <td>
                                    <div style="font-size:0.8rem;line-height:1.45;">
                                        <a href="mailto:<?= Security::e($u['email']) ?>" style="color:var(--text-main);text-decoration:none;display:inline-flex;align-items:center;gap:4px;font-weight:600;">
                                            <span style="color:var(--text-muted);"><?= Icons::svg('mail', 12) ?></span>
                                            <?= Security::e($u['email']) ?>
                                        </a>
                                        <?php if (!empty($u['phone'])): ?>
                                            <div style="color:var(--text-muted);font-size:0.75rem;margin-top:2px;display:flex;align-items:center;gap:4px;">
                                                <?= Icons::svg('phone', 11) ?>
                                                <span class="num-tabular"><?= Security::e($u['phone']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- STATUS -->
                                <td>
                                    <?php if ($isPermBlocked): ?>
                                        <span class="badge badge-danger" style="font-size:0.74rem;font-weight:800;display:inline-flex;align-items:center;gap:4px;" title="<?= Security::e($u['security_block_reason'] ?? 'Suspect activity') ?>">
                                            🚨 <?= $isSo ? 'Xaniban' : 'Blocked' ?>
                                        </span>
                                    <?php elseif ($isTempLocked): ?>
                                        <?php
                                            $remSec = strtotime((string) $u['locked_until']) - time();
                                            $remMin = ceil($remSec / 60);
                                        ?>
                                        <span class="badge badge-warning" style="font-size:0.74rem;font-weight:800;display:inline-flex;align-items:center;gap:4px;">
                                            ⏳ <?= $isSo ? "Hakad ({$remMin}d)" : "Locked ({$remMin}m)" ?>
                                        </span>
                                    <?php elseif (!$isActive): ?>
                                        <span class="badge badge-secondary" style="font-size:0.74rem;font-weight:800;background:rgba(120,120,120,0.18);color:var(--text-muted);display:inline-flex;align-items:center;gap:4px;">
                                            ○ <?= $isSo ? 'Dansan' : 'Deactivated' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success" style="font-size:0.74rem;font-weight:800;background:rgba(35,132,91,0.15);color:var(--simda-green);border:1px solid rgba(35,132,91,0.3);display:inline-flex;align-items:center;gap:4px;">
                                            ● <?= $isSo ? 'Shaqeynaya' : 'Active' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- LAST LOGIN -->
                                <td class="num-tabular" style="font-size:0.76rem;color:var(--text-muted);">
                                    <?php if (!empty($u['last_login_at'])): ?>
                                        <div style="display:flex;align-items:center;gap:4px;color:var(--text-main);font-weight:600;">
                                            <?= Icons::svg('clock', 12) ?>
                                            <?= Security::e(date('M d, Y H:i', strtotime((string) $u['last_login_at']))) ?>
                                        </div>
                                    <?php else: ?>
                                        <span><?= $isSo ? 'Weli ma gelin' : 'Never' ?></span>
                                    <?php endif; ?>
                                </td>

                                <!-- ACTIONS: EDIT & ACTIVE/DEACTIVE -->
                                <td style="text-align:right;white-space:nowrap;padding-right:18px;">
                                    <div class="action-btn-group">
                                        <!-- 1. EDIT BUTTON -->
                                        <?php
                                        // Hierarchy permission for editing
                                        $canEditThisUser = false;
                                        if ($curRole === 'admin') {
                                            $canEditThisUser = true;
                                        } elseif ($curRole === 'manager') {
                                            $canEditThisUser = ($roleSlug !== 'admin' && ($roleSlug !== 'manager' || $isSelf));
                                        } elseif ($curRole === 'branch_manager') {
                                            $canEditThisUser = ($isSelf || in_array($roleSlug, ['loan_officer', 'client'], true));
                                        }
                                        ?>
                                        <?php if ($canEditThisUser): ?>
                                            <button type="button"
                                                class="btn btn-xs btn-outline-primary"
                                                style="font-weight:700;display:inline-flex;align-items:center;gap:4px;padding:4px 9px;"
                                                onclick="openEditUserModal(<?= htmlspecialchars(json_encode([
                                                    'id' => $uId,
                                                    'full_name' => $u['full_name'],
                                                    'username' => $u['username'],
                                                    'email' => $u['email'],
                                                    'phone' => $u['phone'],
                                                    'role_id' => (int) $u['role_id'],
                                                    'role_slug' => $roleSlug,
                                                    'branch_id' => (int) ($u['branch_id'] ?? 0),
                                                    'is_active' => $isActive,
                                                ]), ENT_QUOTES, 'UTF-8') ?>)"
                                                title="<?= $isSo ? 'Wax ka beddel macluumaadka isticmaalahan' : 'Edit user details' ?>">
                                                <?= Icons::svg('edit', 12) ?> <?= $isSo ? 'Wax Ka Beddel' : 'Edit' ?>
                                            </button>
                                        <?php endif; ?>

                                        <!-- 2. ACTIVE / DEACTIVE TOGGLE -->
                                        <?php if ($isSelf): ?>
                                            <button type="button" class="btn btn-xs btn-outline" disabled style="opacity:0.5;cursor:not-allowed;font-size:0.72rem;padding:4px 8px;" title="<?= $isSo ? 'Ma damin kartid akoonkaaga aad hadda ku jirto' : 'Cannot deactivate own active account' ?>">
                                                <?= Icons::svg('lock', 11) ?> <?= $isSo ? 'Akoonkaaga' : 'Current' ?>
                                            </button>
                                        <?php else: ?>
                                            <?php if ($isActive): ?>
                                                <!-- DEACTIVATE BUTTON -->
                                                <form method="POST" action="?route=user_toggle_status" style="display:inline;" onsubmit="return confirm('<?= $isSo ? 'Ma hubtaa inaad damiso (deactivate) akoonka isticmaalaha (' . addslashes(Security::e($u['full_name'])) . ')? Gelitaankiisa nidaamka waa la hakinayaa.' : 'Are you sure you want to deactivate (' . addslashes(Security::e($u['full_name'])) . ')? Their active session will be terminated.' ?>');">
                                                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                                    <input type="hidden" name="user_id" value="<?= $uId ?>">
                                                    <button type="submit" class="btn btn-xs btn-outline-warning" style="font-weight:700;display:inline-flex;align-items:center;gap:4px;padding:4px 9px;" title="<?= $isSo ? 'Demi gelitaanka akoonkan' : 'Deactivate this user account' ?>">
                                                        <?= Icons::svg('power', 12) ?> <?= $isSo ? 'Demi' : 'Deactivate' ?>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <!-- ACTIVATE BUTTON -->
                                                <form method="POST" action="?route=user_toggle_status" style="display:inline;" onsubmit="return confirm('<?= $isSo ? 'Ma hubtaa inaad dib u hawlgaliso (activate) akoonka (' . addslashes(Security::e($u['full_name'])) . ')?' : 'Are you sure you want to activate (' . addslashes(Security::e($u['full_name'])) . ')?' ?>');">
                                                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                                    <input type="hidden" name="user_id" value="<?= $uId ?>">
                                                    <button type="submit" class="btn btn-xs btn-outline-success" style="font-weight:700;display:inline-flex;align-items:center;gap:4px;padding:4px 9px;" title="<?= $isSo ? 'Hawlgeli akoonkan' : 'Activate this user account' ?>">
                                                        <?= Icons::svg('check-circle', 12) ?> <?= $isSo ? 'Hawlgeli' : 'Activate' ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- 3. UNBLOCK BUTTON (IF BLOCKED) -->
                                        <?php if (($isPermBlocked || $isTempLocked) && $curRole === 'admin'): ?>
                                            <form method="POST" action="?route=user_security_reset" style="display:inline;" onsubmit="return confirm('<?= $isSo ? 'Ma hubtaa inaad xanibaadda amniga ka qaaddo isticmaalahan?' : 'Are you sure you want to restore access for this user?' ?>');">
                                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                                <input type="hidden" name="user_id" value="<?= $uId ?>">
                                                <button type="submit" class="btn btn-xs btn-outline-danger" style="font-weight:700;display:inline-flex;align-items:center;gap:3px;padding:4px 8px;" title="Reset Security Restrictions">
                                                    🔓 <?= $isSo ? 'Fur' : 'Unlock' ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- 4. PERMISSIONS SHORTCUT (ADMIN ONLY) -->
                                        <?php if ($curRole === 'admin' && $roleSlug !== 'admin'): ?>
                                            <a href="?route=permissions&user_id=<?= $uId ?>" class="btn btn-xs btn-outline" style="font-weight:700;display:inline-flex;align-items:center;gap:4px;padding:4px 8px;" title="<?= $isSo ? 'Habee awoodaha qofkan' : 'Configure permissions' ?>">
                                                <?= Icons::svg('shield', 12) ?> <?= $isSo ? 'Awoodaha' : 'Perms' ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Empty state row -->
                        <tr id="staffNoResultsRow" style="display:none;">
                            <td colspan="7" style="text-align:center;padding:36px;color:var(--text-muted);">
                                <div style="font-size:1.6rem;margin-bottom:8px;">🔍</div>
                                <div style="font-weight:700;font-size:0.95rem;color:var(--text-main);"><?= $isSo ? 'Lama helin wax shaqaale ah oo shuruudahaas buuxiyay' : 'No matching staff or users found' ?></div>
                                <div style="font-size:0.8rem;margin-top:4px;"><?= $isSo ? 'Fadlan hubi xarfaha aad qortay ama dooro filter kale.' : 'Try adjusting your search keywords or filter selection.' ?></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- EDIT USER MODAL DIALOG                                                    -->
        <!-- ========================================================================= -->
        <div id="editUserModalOverlay" class="simda-modal-overlay" onclick="handleModalBackdropClick(event)">
            <div class="simda-modal-box" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
                <!-- Modal Header -->
                <div style="background:var(--bg-surface);padding:18px 22px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span class="fid-contact-dot" style="background:var(--simda-green);color:#FFFFFF;width:30px;height:30px;">
                            <?= Icons::svg('edit', 16) ?>
                        </span>
                        <div>
                            <h3 id="editModalTitle" style="font-size:1.08rem;font-weight:800;margin:0;color:var(--text-main);">
                                <?= $isSo ? 'Wax Ka Beddel Isticmaalaha' : 'Edit Staff Account' ?>
                            </h3>
                            <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">
                                <?= $isSo ? 'Cusbooneysii magaca, xiriirka, doorka, laanta, ama xaaladda akoonka' : 'Update profile details, contact, role hierarchy, or status' ?>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline" onclick="closeEditUserModal()" style="padding:4px 8px;font-size:1rem;line-height:1;border-radius:8px;">✕</button>
                </div>

                <!-- Modal Body Form -->
                <form method="POST" action="?route=user_update" id="editUserForm" style="padding:22px;">
                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                    <input type="hidden" name="user_id" id="modalUserId" value="">

                    <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <!-- Full Name -->
                        <div class="form-group" style="grid-column:1 / -1;">
                            <label style="font-weight:700;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);"><?= $isSo ? 'Magaca Buuxa *' : 'Full Name *' ?></label>
                            <input type="text" name="full_name" id="modalFullName" class="form-control" required style="font-weight:600;">
                        </div>

                        <!-- Username -->
                        <div class="form-group">
                            <label style="font-weight:700;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);"><?= $isSo ? 'Username *' : 'Username *' ?></label>
                            <input type="text" name="username" id="modalUsername" class="form-control" required style="font-weight:700;">
                        </div>

                        <!-- Phone -->
                        <div class="form-group">
                            <label style="font-weight:700;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);"><?= $isSo ? 'Telefoonka *' : 'Phone Number *' ?></label>
                            <input type="text" name="phone" id="modalPhone" class="form-control" required style="font-weight:600;">
                        </div>

                        <!-- Email -->
                        <div class="form-group" style="grid-column:1 / -1;">
                            <label style="font-weight:700;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);"><?= $isSo ? 'Email Address *' : 'Email Address *' ?></label>
                            <input type="email" name="email" id="modalEmail" class="form-control" required style="font-weight:600;">
                        </div>

                        <!-- Role Select -->
                        <div class="form-group">
                            <label style="font-weight:700;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);"><?= $isSo ? 'Doorka Shaqada (Role) *' : 'Role Hierarchy *' ?></label>
                            <select name="role_id" id="modalRoleId" class="form-control" required style="font-weight:700;" onchange="updateModalRoleBranchState()">
                                <?php foreach ($allRoles as $r): ?>
                                    <?php
                                        // Hierarchy restrictions for role selection in modal:
                                        $rSlug = $r['slug'];
                                        $isDisabled = false;
                                        if ($curRole === 'admin') {
                                            // Admin can keep admin or assign any non-admin
                                        } elseif ($curRole === 'manager') {
                                            if (in_array($rSlug, ['admin', 'manager'], true)) $isDisabled = true;
                                        } elseif ($curRole === 'branch_manager') {
                                            if (in_array($rSlug, ['admin', 'manager', 'branch_manager'], true)) $isDisabled = true;
                                        }
                                    ?>
                                    <option value="<?= (int) $r['id'] ?>" data-slug="<?= Security::e($rSlug) ?>" <?= $isDisabled ? 'disabled style="display:none;"' : '' ?>>
                                        <?= Security::e($isSo ? $r['name_so'] : $r['name_en']) ?> (<?= Security::e($rSlug) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status Select (Active / Deactive) -->
                        <div class="form-group">
                            <label style="font-weight:700;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);"><?= $isSo ? 'Xaaladda Akoonka (Status) *' : 'Account Status *' ?></label>
                            <select name="is_active" id="modalIsActive" class="form-control" required style="font-weight:700;">
                                <option value="1">● <?= $isSo ? 'Firfircoon (Active)' : 'Active (Enabled)' ?></option>
                                <option value="0">○ <?= $isSo ? 'Dansan (Deactivated)' : 'Deactivated (Disabled)' ?></option>
                            </select>
                        </div>

                        <!-- Branch Assignment Select -->
                        <div class="form-group" style="grid-column:1 / -1;" id="modalBranchGroup">
                            <label id="modalBranchLabel" style="font-weight:700;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);">
                                <?= $isSo ? 'Laanta Loo Qoondeeyay (Branch Assignment)' : 'Branch Assignment' ?>
                            </label>
                            <select name="branch_id" id="modalBranchId" class="form-control" style="font-weight:600;">
                                <option value="">-- <?= $isSo ? 'Dooro Laan ama Xarun Guud' : 'Select Branch / Head Office' ?> --</option>
                                <?php foreach ($branches as $b): ?>
                                    <option value="<?= (int) $b['id'] ?>"><?= Security::e($b['name']) ?> (<?= Security::e($b['code']) ?>) — <?= Security::e($b['city']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div id="modalBranchManagerNote" style="display:none;margin-top:6px;font-size:0.82rem;color:var(--gold-color);font-weight:700;background:rgba(214,182,108,0.1);padding:8px 12px;border-radius:8px;border:1px solid rgba(214,182,108,0.3);">
                                <?= Icons::svg('check-circle', 14) ?> <?= $isSo ? 'Maamulaha Guud (Manager) wuxuu maamulaa dhammaan laamaha shirkadda (Company-Wide).' : 'General Operations Manager oversees all branches company-wide.' ?>
                            </div>
                        </div>

                        <!-- Password Reset (Optional) -->
                        <div class="form-group" style="grid-column:1 / -1;background:var(--bg-surface);padding:14px;border-radius:10px;border:1px solid var(--border-color);">
                            <label style="font-weight:800;font-size:0.84rem;margin-bottom:4px;display:flex;align-items:center;gap:6px;color:var(--text-main);">
                                <?= Icons::svg('lock', 13) ?> <?= $isSo ? 'Dib u deji Furaha Sirta ah (Password Reset - Ikhtiyaari)' : 'Reset Password (Optional)' ?>
                            </label>
                            <div style="font-size:0.77rem;color:var(--text-muted);margin-bottom:8px;">
                                <?= $isSo ? 'Ku dhaaf meel banaan haddii aadan rabin inaad beddesho furaha qofkan.' : 'Leave blank to keep existing password unchanged.' ?>
                            </div>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <input type="text" name="password" id="modalPasswordInput" class="form-control" placeholder="<?= $isSo ? 'Geli fure cusub ama riix Dhal Fure' : 'Enter new password or click generate' ?>" style="font-family:monospace;font-weight:700;letter-spacing:1px;font-size:0.88rem;">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateModalPassword()" style="white-space:nowrap;font-weight:700;">
                                    <?= Icons::svg('refresh', 13) ?> <?= $isSo ? 'Dhal Fure' : 'Generate' ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions Footer -->
                    <div style="display:flex;justify-content:flex-end;gap:10px;align-items:center;margin-top:18px;padding-top:16px;border-top:1px solid var(--border-color);">
                        <button type="button" class="btn btn-outline btn-sm" onclick="closeEditUserModal()" style="font-weight:700;padding:8px 18px;">
                            <?= $isSo ? 'Ka noqo' : 'Cancel' ?>
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm" style="font-weight:800;padding:8px 22px;display:inline-flex;align-items:center;gap:6px;">
                            <?= Icons::svg('check-circle', 14) ?> <?= $isSo ? 'Keydi Isbeddelka' : 'Save Changes' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        var currentStaffFilter = 'all';

        function setStaffFilter(filterMode, btn) {
            currentStaffFilter = filterMode;
            document.querySelectorAll('.staff-filter-pill').forEach(function (b) {
                b.classList.remove('is-active');
            });
            if (btn) btn.classList.add('is-active');
            filterStaffTable();
        }

        function filterStaffTable() {
            var searchInput = document.getElementById('staffSearchInput');
            var roleSelect = document.getElementById('staffRoleFilter');
            var q = searchInput ? searchInput.value.toLowerCase().trim() : '';
            var selectedRole = roleSelect ? roleSelect.value.toLowerCase().trim() : '';
            var rows = document.querySelectorAll('tr.staff-row');
            var visibleCount = 0;

            rows.forEach(function (row) {
                var searchData = row.getAttribute('data-search') || '';
                var rowActive = row.getAttribute('data-active') === '1';
                var rowRole = (row.getAttribute('data-role') || '').toLowerCase();

                // Search match
                var matchesSearch = (!q || searchData.indexOf(q) !== -1);

                // Filter status match
                var matchesStatus = true;
                if (currentStaffFilter === 'active') {
                    matchesStatus = rowActive;
                } else if (currentStaffFilter === 'inactive') {
                    matchesStatus = !rowActive;
                }

                // Role match
                var matchesRole = (!selectedRole || rowRole === selectedRole);

                if (matchesSearch && matchesStatus && matchesRole) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            var noResultsRow = document.getElementById('staffNoResultsRow');
            if (noResultsRow) {
                noResultsRow.style.display = (visibleCount === 0) ? '' : 'none';
            }
        }

        function openEditUserModal(u) {
            var overlay = document.getElementById('editUserModalOverlay');
            if (!overlay) return;

            document.getElementById('modalUserId').value = u.id || '';
            document.getElementById('modalFullName').value = u.full_name || '';
            document.getElementById('modalUsername').value = u.username || '';
            document.getElementById('modalEmail').value = u.email || '';
            document.getElementById('modalPhone').value = u.phone || '';
            document.getElementById('modalRoleId').value = u.role_id || '';
            document.getElementById('modalIsActive').value = (typeof u.is_active !== 'undefined') ? u.is_active : '1';
            document.getElementById('modalBranchId').value = u.branch_id || '';
            document.getElementById('modalPasswordInput').value = '';

            // Handle role branch logic inside modal
            updateModalRoleBranchState();

            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeEditUserModal() {
            var overlay = document.getElementById('editUserModalOverlay');
            if (overlay) {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        function handleModalBackdropClick(event) {
            if (event.target === document.getElementById('editUserModalOverlay')) {
                closeEditUserModal();
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeEditUserModal();
            }
        });

        function updateModalRoleBranchState() {
            var roleSelect = document.getElementById('modalRoleId');
            var branchSelect = document.getElementById('modalBranchId');
            var branchNote = document.getElementById('modalBranchManagerNote');
            var branchLabel = document.getElementById('modalBranchLabel');
            if (!roleSelect) return;

            var opt = roleSelect.options[roleSelect.selectedIndex];
            var slug = opt ? opt.getAttribute('data-slug') : '';

            if (slug === 'manager' || slug === 'admin') {
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
                        if (branchLabel) branchLabel.innerHTML = '<?= $isSo ? 'Laanta Loo Qoondeeyay * (Waa Qasab)' : 'Branch Assignment * (Required)' ?>';
                    } else {
                        branchSelect.required = false;
                        if (branchLabel) branchLabel.innerHTML = '<?= $isSo ? 'Laanta Loo Qoondeeyay (Branch Assignment)' : 'Branch Assignment' ?>';
                    }
                }
                if (branchNote) branchNote.style.display = 'none';
            }
        }

        function generateModalPassword() {
            var nameInput = document.getElementById('modalFullName');
            var pwdInput = document.getElementById('modalPasswordInput');
            var raw = nameInput ? nameInput.value.trim() : '';
            var first = raw.split(/\s+/)[0] || 'simda';
            var clean = first.toLowerCase().replace(/[^a-z0-9]/g, '') || 'simda';
            var digits = Math.floor(1000 + Math.random() * 9000);
            if (pwdInput) {
                pwdInput.value = clean + digits + '@!';
            }
        }
        </script>

    <!-- ========================================================================= -->
    <!-- 2.5 PERMISSIONS & ACCESS CONTROL MANAGEMENT VIEW                           -->
    <!-- ========================================================================= -->
    <?php elseif (($mode ?? '') === 'permissions'): ?>
        <div style="background:rgba(214,182,108,0.1);border-left:4px solid var(--simda-gold);padding:14px 18px;border-radius:10px;margin:14px 0 20px;">
            <div style="font-weight:800;font-size:0.94rem;color:var(--text-main);margin-bottom:4px;display:flex;align-items:center;gap:8px;">
                <?= Icons::svg('shield', 16) ?>
                <?= $isSo ? 'Maamulka Awoodaha & Ogolaanshaha Isticmaalayaasha' : 'Granular Permissions & Access Control' ?>
            </div>
            <div style="font-size:0.8rem;color:var(--text-main);line-height:1.55;">
                <?= $isSo
                    ? 'Adigoo ah <strong>Maamulaha Guud (Admin)</strong>, waxaad halkan ku xadidi kartaa ama ku ogolaan kartaa waxyaabaha gaarka ah ee uu <strong>Manager-ka</strong> ama <strong>Shaqaalaha kale</strong> ka qaban karaan nidaamka. Qaybaha aad ka saarto calaamadda (uncheck) lagama tusi doono Dashboard-kooda, waana laga horjoogsanayaa gelitaankooda.'
                    : 'As the <strong>System Administrator</strong>, you can grant or restrict specific feature permissions for each Manager, Branch Manager, or Loan Officer. Unchecked permissions will be hidden from their sidebar and access will be blocked.' ?>
            </div>
        </div>

        <!-- SELECT USER DROPDOWN / TABS -->
        <div style="background:var(--bg-elevated);padding:16px 20px;border-radius:12px;border:1px solid var(--border-color);margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <label style="font-weight:700;font-size:0.88rem;color:var(--text-main);margin:0;"><?= $isSo ? 'Dooro Qofka aad rabto inaad awoodaha u habayso:' : 'Select Staff Member to Configure:' ?></label>
                    <select class="form-control" style="font-weight:700;min-width:300px;" onchange="window.location.href='?route=permissions&user_id=' + this.value;">
                        <?php foreach ($users as $u): ?>
                            <?php if ($u['role_slug'] === 'admin') continue; ?>
                            <option value="<?= (int) $u['id'] ?>" <?= ((int) ($selectedUser['id'] ?? 0) === (int) $u['id']) ? 'selected' : '' ?>>
                                <?= Security::e($u['full_name']) ?> — <?= Security::e($u['role_name_so'] ?? $u['role_slug']) ?> (<?= Security::e($u['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($selectedUser): ?>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <span class="badge <?= $selectedUser['role_slug'] === 'manager' ? 'badge-gold' : 'badge-info' ?>" style="font-weight:700;padding:6px 12px;">
                            <?= Security::e($selectedUser['role_name_so'] ?? $selectedUser['role_slug']) ?>
                        </span>
                        <span style="font-size:0.82rem;color:var(--text-muted);">
                            Laanta: <strong><?= Security::e($selectedUser['branch_name'] ?? ($selectedUser['role_slug'] === 'manager' ? ($isSo ? 'Dhammaan Shirkadda' : 'Company-Wide') : 'HQ')) ?></strong>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($selectedUser): ?>
            <form method="POST" action="?route=user_permissions_save" id="permissionsForm">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <input type="hidden" name="user_id" value="<?= (int) $selectedUser['id'] ?>">

                <?php
                $totalPerms = count($permissionsList);
                $grantedCount = 0;
                foreach ($permissionsList as $p) {
                    if (!empty($userPermMap[$p['slug']])) {
                        $grantedCount++;
                    }
                }
                $revokedCount = $totalPerms - $grantedCount;
                $grantedPercent = $totalPerms > 0 ? (int) round(($grantedCount / $totalPerms) * 100) : 0;
                ?>

                <style>
                /* Permissions Table & Modern Switch Styling */
                .simda-perm-card {
                    background: var(--bg-elevated);
                    border: 1px solid var(--border-color);
                    border-radius: 14px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
                    margin-bottom: 22px;
                }
                .simda-perm-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                }
                .simda-perm-table thead th {
                    background: var(--bg-surface);
                    color: var(--text-muted);
                    font-size: 0.76rem;
                    font-weight: 800;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    padding: 13px 16px;
                    border-bottom: 2px solid var(--border-color);
                    vertical-align: middle;
                }
                .simda-perm-table tbody tr.perm-row {
                    transition: background-color 0.15s ease;
                }
                .simda-perm-table tbody tr.perm-row:hover {
                    background: rgba(18, 59, 50, 0.032);
                }
                [data-theme="dark"] .simda-perm-table tbody tr.perm-row:hover {
                    background: rgba(255, 255, 255, 0.035);
                }
                .simda-perm-table tbody tr.is-granted {
                    background: rgba(35, 132, 91, 0.025);
                }
                [data-theme="dark"] .simda-perm-table tbody tr.is-granted {
                    background: rgba(35, 132, 91, 0.06);
                }
                .simda-perm-table td {
                    padding: 13px 16px;
                    border-bottom: 1px solid var(--border-color);
                    vertical-align: middle;
                    font-size: 0.84rem;
                }
                .simda-cat-header-row td {
                    background: var(--bg-surface);
                    border-top: 1.5px solid var(--border-color);
                    border-bottom: 1.5px solid var(--border-color);
                    padding: 11px 18px;
                }
                .simda-perm-code {
                    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
                    font-size: 0.72rem;
                    background: rgba(0, 0, 0, 0.04);
                    color: var(--text-muted);
                    padding: 2px 7px;
                    border-radius: 4px;
                    border: 1px solid var(--border-color);
                    font-weight: 600;
                    letter-spacing: -0.01em;
                }
                [data-theme="dark"] .simda-perm-code {
                    background: rgba(255, 255, 255, 0.05);
                }

                /* Modern Toggle Switch */
                .simda-perm-switch {
                    position: relative;
                    display: inline-block;
                    width: 44px;
                    height: 24px;
                    margin: 0;
                    cursor: pointer;
                    vertical-align: middle;
                }
                .simda-perm-switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                    position: absolute;
                }
                .simda-perm-slider {
                    position: absolute;
                    cursor: pointer;
                    inset: 0;
                    background-color: #CBD5E1;
                    transition: 0.22s cubic-bezier(0.4, 0, 0.2, 1);
                    border-radius: 24px;
                }
                .simda-perm-slider:before {
                    position: absolute;
                    content: "";
                    height: 18px;
                    width: 18px;
                    left: 3px;
                    bottom: 3px;
                    background-color: #FFFFFF;
                    transition: 0.22s cubic-bezier(0.4, 0, 0.2, 1);
                    border-radius: 50%;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.22);
                }
                .simda-perm-switch input:checked + .simda-perm-slider {
                    background-color: #123B32;
                }
                .simda-perm-switch input:checked + .simda-perm-slider:before {
                    transform: translateX(20px);
                }
                [data-theme="dark"] .simda-perm-slider {
                    background-color: #334155;
                }
                [data-theme="dark"] .simda-perm-switch input:checked + .simda-perm-slider {
                    background-color: #23845B;
                }

                /* Filter Tabs Pill */
                .simda-filter-pill-btn {
                    padding: 5px 12px;
                    border-radius: 20px;
                    border: 1px solid var(--border-color);
                    background: var(--bg-surface);
                    color: var(--text-muted);
                    font-size: 0.78rem;
                    font-weight: 700;
                    cursor: pointer;
                    transition: all 0.16s ease;
                }
                .simda-filter-pill-btn:hover {
                    color: var(--text-main);
                    border-color: var(--simda-green);
                }
                .simda-filter-pill-btn.is-active {
                    background: var(--simda-green);
                    color: #FFFFFF;
                    border-color: var(--simda-green);
                }
                </style>

                <!-- TOP STATS & QUICK FILTER TOOLBAR -->
                <div style="background:var(--bg-elevated);padding:16px 20px;border-radius:12px;border:1px solid var(--border-color);margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
                        <!-- Left: Info & Progress Bar -->
                        <div style="min-width:260px;flex:1;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                                <strong style="font-size:0.95rem;color:var(--text-main);">
                                    <?= $isSo ? 'Awoodaha loo asteeyey:' : 'Permissions Configured for:' ?> <u><?= Security::e($selectedUser['full_name']) ?></u>
                                </strong>
                                <span class="badge badge-gold" style="font-weight:700;">
                                    <span id="grantedCountBadge"><?= $grantedCount ?></span> / <?= $totalPerms ?> <?= $isSo ? 'Ogol yahay' : 'Granted' ?>
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="flex:1;height:7px;background:var(--bg-surface);border-radius:10px;overflow:hidden;border:1px solid var(--border-color);">
                                    <div id="grantedProgressBar" style="width:<?= $grantedPercent ?>%;height:100%;background:linear-gradient(90deg, #123B32 0%, #23845B 50%, #D6B66C 100%);transition:width 0.3s ease;"></div>
                                </div>
                                <span id="grantedPercentText" style="font-size:0.78rem;font-weight:800;color:var(--text-main);min-width:38px;text-align:right;">
                                    <?= $grantedPercent ?>%
                                </span>
                            </div>
                        </div>

                        <!-- Right: Bulk Actions (Grant All / Revoke All) -->
                        <div style="display:flex;gap:8px;align-items:center;">
                            <button type="button" class="btn btn-sm btn-outline" onclick="toggleAllPerms(true)" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                                <?= Icons::svg('check-circle', 14) ?> <?= $isSo ? 'Dooro Dhammaan (Grant All)' : 'Grant All' ?>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline" onclick="toggleAllPerms(false)" style="font-weight:700;color:#C94B4B;display:inline-flex;align-items:center;gap:6px;">
                                <?= Icons::svg('x-circle', 14) ?> <?= $isSo ? 'Ka saar Dhammaan (Revoke All)' : 'Revoke All' ?>
                            </button>
                        </div>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-top:14px;padding-top:12px;border-top:1px solid var(--border-color);">
                        <!-- Search Box -->
                        <div style="position:relative;flex:1;max-width:380px;min-width:240px;">
                            <input type="text" id="permSearchInput" class="form-control" placeholder="<?= $isSo ? '🔍 Raadi awoodda, magaca ama slug-ka...' : '🔍 Search permission name or slug...' ?>" oninput="filterPermTable()" style="font-size:0.84rem;padding-left:14px;border-radius:8px;">
                        </div>

                        <!-- Filter Tabs -->
                        <div style="display:flex;gap:6px;align-items:center;">
                            <button type="button" class="simda-filter-pill-btn is-active" data-filter="all" onclick="setFilterMode('all', this)">
                                <?= $isSo ? 'Dhammaan' : 'All' ?> (<?= $totalPerms ?>)
                            </button>
                            <button type="button" class="simda-filter-pill-btn" data-filter="granted" onclick="setFilterMode('granted', this)">
                                <?= $isSo ? 'Ogol yahay' : 'Granted' ?> (<span id="filterGrantedCount"><?= $grantedCount ?></span>)
                            </button>
                            <button type="button" class="simda-filter-pill-btn" data-filter="revoked" onclick="setFilterMode('revoked', this)">
                                <?= $isSo ? 'Diidan' : 'Restricted' ?> (<span id="filterRevokedCount"><?= $revokedCount ?></span>)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- BEAUTIFIED PERMISSIONS TABLE -->
                <div class="simda-perm-card">
                    <div class="table-responsive">
                        <table class="simda-perm-table" id="permissionsTable">
                            <thead>
                                <tr>
                                    <th style="width:52px;text-align:center;">#</th>
                                    <th><?= $isSo ? 'Awoodda & Faahfaahinta' : 'Permission & Description' ?></th>
                                    <th style="width:220px;"><?= $isSo ? 'Qaybta (Module)' : 'Module / Category' ?></th>
                                    <th style="width:140px;text-align:center;"><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                                    <th style="width:110px;text-align:center;"><?= $isSo ? 'Ogolaanshaha' : 'Access' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $modules = [
                                    'administration' => ['title_so' => 'Maamulka & Nidaamka', 'title_en' => 'Administration & Governance', 'icon' => 'settings'],
                                    'clients' => ['title_so' => 'Macaamiisha (Clients)', 'title_en' => 'Clients & Guarantors', 'icon' => 'users'],
                                    'financing' => ['title_so' => 'Maalgelinta & Daymaha (Financing)', 'title_en' => 'Financing, Loans & Murabaha', 'icon' => 'layers'],
                                    'payments' => ['title_so' => 'Lacag-Qabashada & Deeqaha', 'title_en' => 'Repayments & Charity', 'icon' => 'activity'],
                                    'risk' => ['title_so' => 'Khatarta & Dhibcaha (Risk)', 'title_en' => 'Risk & Credit Scores', 'icon' => 'alert-triangle'],
                                    'reports' => ['title_so' => 'Warbixinnada & Statements', 'title_en' => 'Reports & Statements', 'icon' => 'file-text'],
                                    'communication' => ['title_so' => 'Isgaarsiinta (SMS)', 'title_en' => 'Communication & SMS', 'icon' => 'message-square'],
                                ];
                                $groupedPerms = [];
                                foreach ($permissionsList as $p) {
                                    $groupedPerms[$p['module']][] = $p;
                                }

                                $rowIdx = 0;
                                foreach ($modules as $modKey => $modMeta):
                                    if (empty($groupedPerms[$modKey])) continue;
                                    $catPerms = $groupedPerms[$modKey];
                                    $catTotal = count($catPerms);
                                ?>
                                    <!-- Category Header Row -->
                                    <tr class="simda-cat-header-row" data-cat="<?= Security::e($modKey) ?>">
                                        <td colspan="5">
                                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                                                <div style="display:flex;align-items:center;gap:10px;">
                                                    <span style="background:rgba(35,132,91,0.12);color:var(--simda-green);width:26px;height:26px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;">
                                                        <?= Icons::svg($modMeta['icon'], 13) ?>
                                                    </span>
                                                    <span style="font-size:0.88rem;font-weight:800;color:var(--text-main);">
                                                        <?= Security::e($isSo ? $modMeta['title_so'] : $modMeta['title_en']) ?>
                                                    </span>
                                                    <span class="badge badge-secondary" style="font-size:0.7rem;font-weight:700;">
                                                        <?= $catTotal ?> <?= $isSo ? 'Awoodood' : 'Perms' ?>
                                                    </span>
                                                </div>
                                                <div style="display:flex;align-items:center;gap:6px;">
                                                    <button type="button" class="btn btn-xs btn-outline" onclick="toggleModulePerms('<?= Security::e($modKey) ?>', true)" style="font-size:0.72rem;font-weight:700;padding:2px 8px;">
                                                        <?= Icons::svg('check-circle', 11) ?> <?= $isSo ? 'Dooro Qaybtan' : 'Grant Category' ?>
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-outline" onclick="toggleModulePerms('<?= Security::e($modKey) ?>', false)" style="font-size:0.72rem;font-weight:700;padding:2px 8px;color:#C94B4B;">
                                                        <?= Icons::svg('x-circle', 11) ?> <?= $isSo ? 'Ka saar Qaybtan' : 'Revoke Category' ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Permission Rows -->
                                    <?php foreach ($catPerms as $perm):
                                        $rowIdx++;
                                        $isGranted = !empty($userPermMap[$perm['slug']]);
                                        $slugKey = str_replace('.', '_', $perm['slug']);
                                    ?>
                                        <tr class="perm-row <?= $isGranted ? 'is-granted' : 'is-revoked' ?>"
                                            data-slug="<?= Security::e($perm['slug']) ?>"
                                            data-desc="<?= Security::e(mb_strtolower($perm['description'])) ?>"
                                            data-cat="<?= Security::e($modKey) ?>"
                                            data-granted="<?= $isGranted ? '1' : '0' ?>"
                                            id="row_<?= Security::e($slugKey) ?>">

                                            <td style="text-align:center;color:var(--text-muted);font-weight:700;font-size:0.78rem;">
                                                <?= $rowIdx ?>
                                            </td>

                                            <td>
                                                <div style="font-weight:700;font-size:0.86rem;color:var(--text-main);margin-bottom:3px;">
                                                    <?= Security::e($perm['description']) ?>
                                                </div>
                                                <div>
                                                    <span class="simda-perm-code"><?= Security::e($perm['slug']) ?></span>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="badge" style="background:rgba(18,59,50,0.08);color:var(--simda-green);border:1px solid rgba(18,59,50,0.18);font-weight:700;font-size:0.74rem;display:inline-flex;align-items:center;gap:5px;">
                                                    <?= Icons::svg($modMeta['icon'], 12) ?>
                                                    <span><?= Security::e($isSo ? $modMeta['title_so'] : $modMeta['title_en']) ?></span>
                                                </span>
                                            </td>

                                            <td style="text-align:center;">
                                                <span class="perm-status-badge badge <?= $isGranted ? 'badge-success' : 'badge-secondary' ?>"
                                                      id="status_<?= Security::e($slugKey) ?>"
                                                      style="font-size:0.74rem;font-weight:700;padding:4px 9px;display:inline-flex;align-items:center;gap:4px;">
                                                    <?= $isGranted ? '✓ ' . ($isSo ? 'Ogol yahay' : 'Granted') : '✕ ' . ($isSo ? 'Diidan' : 'Restricted') ?>
                                                </span>
                                            </td>

                                            <td style="text-align:center;">
                                                <label class="simda-perm-switch" title="<?= $isSo ? 'Guji si aad u furto ama u xirto awooddan' : 'Click to toggle access' ?>">
                                                    <input type="checkbox"
                                                           name="perms[]"
                                                           value="<?= Security::e($perm['slug']) ?>"
                                                           class="perm-checkbox"
                                                           data-cat="<?= Security::e($modKey) ?>"
                                                           data-slug-key="<?= Security::e($slugKey) ?>"
                                                           <?= $isGranted ? 'checked' : '' ?>
                                                           onchange="handlePermToggle(this, '<?= Security::e($slugKey) ?>')">
                                                    <span class="simda-perm-slider"></span>
                                                </label>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>

                                <!-- Empty Search State -->
                                <tr id="noPermsRow" style="display:none;">
                                    <td colspan="5" style="text-align:center;padding:36px 16px;color:var(--text-muted);">
                                        <div style="font-size:1.1rem;font-weight:700;margin-bottom:4px;">
                                            <?= $isSo ? 'Wax awood ah lama helin' : 'No permissions found' ?>
                                        </div>
                                        <div style="font-size:0.8rem;">
                                            <?= $isSo ? 'Isku day inaad beddesho qoraalka raadinta ama filter-ka' : 'Try adjusting your search query or active filter' ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- STICKY BOTTOM ACTIONS BAR -->
                <div style="position:sticky;bottom:16px;z-index:90;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;background:var(--bg-elevated);padding:14px 22px;border-radius:14px;border:1.5px solid var(--border-color);box-shadow:0 12px 30px rgba(0,0,0,0.12);">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-weight:700;font-size:0.86rem;color:var(--text-main);">
                            <?= $isSo ? 'Isticmaalaha:' : 'Staff Member:' ?> <strong><?= Security::e($selectedUser['full_name']) ?></strong>
                        </span>
                        <span class="badge badge-gold" id="stickyCountBadge" style="font-weight:700;">
                            <span id="stickyGrantedNum"><?= $grantedCount ?></span> / <?= $totalPerms ?> <?= $isSo ? 'Awoodood' : 'Perms' ?>
                        </span>
                    </div>

                    <div style="display:flex;align-items:center;gap:10px;">
                        <a href="?route=users" class="btn btn-outline" style="font-weight:700;">
                            ← <?= $isSo ? 'Ku noqo Liiska Shaqaalaha' : 'Back to Users List' ?>
                        </a>
                        <button type="submit" class="btn btn-primary" style="font-weight:800;padding:10px 26px;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 14px rgba(18,59,50,0.22);">
                            <?= Icons::svg('check-circle', 16) ?>
                            <span><?= $isSo ? 'Keydi Awoodaha Isticmaalaha' : 'Save User Permissions' ?></span>
                        </button>
                    </div>
                </div>
            </form>

            <script>
            var activeFilterMode = 'all';

            function handlePermToggle(cb, slugKey) {
                var isChecked = cb.checked;
                var row = document.getElementById('row_' + slugKey);
                var statusBadge = document.getElementById('status_' + slugKey);

                if (row) {
                    row.setAttribute('data-granted', isChecked ? '1' : '0');
                    if (isChecked) {
                        row.classList.add('is-granted');
                        row.classList.remove('is-revoked');
                    } else {
                        row.classList.remove('is-granted');
                        row.classList.add('is-revoked');
                    }
                }

                if (statusBadge) {
                    if (isChecked) {
                        statusBadge.className = 'perm-status-badge badge badge-success';
                        statusBadge.textContent = '✓ <?= $isSo ? 'Ogol yahay' : 'Granted' ?>';
                    } else {
                        statusBadge.className = 'perm-status-badge badge badge-secondary';
                        statusBadge.textContent = '✕ <?= $isSo ? 'Diidan' : 'Restricted' ?>';
                    }
                }

                recalculatePermStats();
                filterPermTable();
            }

            function recalculatePermStats() {
                var allBoxes = document.querySelectorAll('.perm-checkbox');
                var total = allBoxes.length;
                var granted = 0;

                allBoxes.forEach(function (cb) {
                    if (cb.checked) granted++;
                });

                var revoked = total - granted;
                var percent = total > 0 ? Math.round((granted / total) * 100) : 0;

                var gBadge = document.getElementById('grantedCountBadge');
                if (gBadge) gBadge.textContent = granted;

                var sNum = document.getElementById('stickyGrantedNum');
                if (sNum) sNum.textContent = granted;

                var fGranted = document.getElementById('filterGrantedCount');
                if (fGranted) fGranted.textContent = granted;

                var fRevoked = document.getElementById('filterRevokedCount');
                if (fRevoked) fRevoked.textContent = revoked;

                var pBar = document.getElementById('grantedProgressBar');
                if (pBar) pBar.style.width = percent + '%';

                var pText = document.getElementById('grantedPercentText');
                if (pText) pText.textContent = percent + '%';
            }

            function toggleAllPerms(check) {
                document.querySelectorAll('.perm-checkbox').forEach(function (cb) {
                    cb.checked = check;
                    var slugKey = cb.getAttribute('data-slug-key');
                    if (slugKey) {
                        var row = document.getElementById('row_' + slugKey);
                        var statusBadge = document.getElementById('status_' + slugKey);
                        if (row) {
                            row.setAttribute('data-granted', check ? '1' : '0');
                            if (check) {
                                row.classList.add('is-granted');
                                row.classList.remove('is-revoked');
                            } else {
                                row.classList.remove('is-granted');
                                row.classList.add('is-revoked');
                            }
                        }
                        if (statusBadge) {
                            if (check) {
                                statusBadge.className = 'perm-status-badge badge badge-success';
                                statusBadge.textContent = '✓ <?= $isSo ? 'Ogol yahay' : 'Granted' ?>';
                            } else {
                                statusBadge.className = 'perm-status-badge badge badge-secondary';
                                statusBadge.textContent = '✕ <?= $isSo ? 'Diidan' : 'Restricted' ?>';
                            }
                        }
                    }
                });
                recalculatePermStats();
                filterPermTable();
            }

            function toggleModulePerms(modKey, check) {
                document.querySelectorAll('.perm-checkbox[data-cat="' + modKey + '"]').forEach(function (cb) {
                    cb.checked = check;
                    var slugKey = cb.getAttribute('data-slug-key');
                    if (slugKey) {
                        var row = document.getElementById('row_' + slugKey);
                        var statusBadge = document.getElementById('status_' + slugKey);
                        if (row) {
                            row.setAttribute('data-granted', check ? '1' : '0');
                            if (check) {
                                row.classList.add('is-granted');
                                row.classList.remove('is-revoked');
                            } else {
                                row.classList.remove('is-granted');
                                row.classList.add('is-revoked');
                            }
                        }
                        if (statusBadge) {
                            if (check) {
                                statusBadge.className = 'perm-status-badge badge badge-success';
                                statusBadge.textContent = '✓ <?= $isSo ? 'Ogol yahay' : 'Granted' ?>';
                            } else {
                                statusBadge.className = 'perm-status-badge badge badge-secondary';
                                statusBadge.textContent = '✕ <?= $isSo ? 'Diidan' : 'Restricted' ?>';
                            }
                        }
                    }
                });
                recalculatePermStats();
                filterPermTable();
            }

            function setFilterMode(mode, btn) {
                activeFilterMode = mode;
                document.querySelectorAll('.simda-filter-pill-btn').forEach(function (b) {
                    b.classList.remove('is-active');
                });
                if (btn) btn.classList.add('is-active');
                filterPermTable();
            }

            function filterPermTable() {
                var searchInput = document.getElementById('permSearchInput');
                var query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                var rows = document.querySelectorAll('tr.perm-row');
                var visibleCount = 0;

                // Track visible rows per category to conditionally hide empty category headers
                var catVisibility = {};

                rows.forEach(function (row) {
                    var slug = (row.getAttribute('data-slug') || '').toLowerCase();
                    var desc = (row.getAttribute('data-desc') || '').toLowerCase();
                    var cat = row.getAttribute('data-cat') || '';
                    var isGranted = row.getAttribute('data-granted') === '1';

                    var matchesSearch = (!query || slug.indexOf(query) !== -1 || desc.indexOf(query) !== -1);
                    var matchesFilter = true;

                    if (activeFilterMode === 'granted') {
                        matchesFilter = isGranted;
                    } else if (activeFilterMode === 'revoked') {
                        matchesFilter = !isGranted;
                    }

                    if (matchesSearch && matchesFilter) {
                        row.style.display = '';
                        visibleCount++;
                        catVisibility[cat] = (catVisibility[cat] || 0) + 1;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show/hide category headers
                document.querySelectorAll('tr.simda-cat-header-row').forEach(function (catRow) {
                    var cat = catRow.getAttribute('data-cat') || '';
                    if (catVisibility[cat] && catVisibility[cat] > 0) {
                        catRow.style.display = '';
                    } else {
                        catRow.style.display = 'none';
                    }
                });

                var noPermsRow = document.getElementById('noPermsRow');
                if (noPermsRow) {
                    noPermsRow.style.display = (visibleCount === 0) ? '' : 'none';
                }
            }
            </script>
        <?php else: ?>
            <div style="background:var(--bg-elevated);padding:20px;border-radius:12px;border:1px solid var(--border-color);text-align:center;color:var(--text-muted);">
                <?= $isSo ? 'Weli ma jiraan shaqaale la diiwaangeliyay oo aan Admin ahayn.' : 'No non-admin staff users found to configure.' ?>
            </div>
        <?php endif; ?>

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
