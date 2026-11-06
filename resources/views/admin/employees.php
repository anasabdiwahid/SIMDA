<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$locale = Lang::locale();
$isSo = $locale === 'so';
$curRole = (string) ($curUser['role_slug'] ?? 'admin');
$csrf = $csrf ?? Security::csrfToken();

$totalEmployees = count($employees);
$activeEmployees = count(array_filter($employees, fn($e) => (int)($e['is_active'] ?? 1) === 1));
$inactiveEmployees = $totalEmployees - $activeEmployees;
$assignedBranchIds = array_unique(array_filter(array_column($employees, 'branch_id')));
$branchesCount = count($assignedBranchIds);
?>

<div class="dash-content-wrap">

    <!-- PAGE HEADER -->
    <div class="dash-page-header">
        <div class="dash-page-header-text">
            <div class="dash-breadcrumb">
                <span>SIMDA</span>
                <span class="dash-breadcrumb-sep">/</span>
                <span><?= $isSo ? 'Maamulka' : 'Administration' ?></span>
                <span class="dash-breadcrumb-sep">/</span>
                <span class="dash-breadcrumb-current"><?= $isSo ? 'Shaqaalaha' : 'Employees' ?></span>
            </div>
            <h1 class="dash-page-title"><?= $isSo ? 'Maamulka & Diiwaangelinta Shaqaalaha' : 'Employee Registration & Management' ?></h1>
            <p class="dash-page-subtitle">
                <?= $isSo
                    ? 'Diiwaangeli shaqaale cusub, u qoondee laanta, waxaana si toos ah email-kooda loogu dirayaa furaha sirta ah (Password-ka).'
                    : 'Register new employees, assign branches, and credentials with passwords are automatically delivered to their email.' ?>
            </p>
        </div>

        <div class="dash-page-header-actions">
            <button type="button" class="btn btn-primary" id="btnToggleEmpForm" onclick="toggleEmpForm()">
                <span id="empToggleIcon"><?= Icons::svg('user-plus', 16) ?></span>
                <span id="empToggleText"><?= $isSo ? 'Diiwaangeli Shaqaale Cusub' : 'Register New Employee' ?></span>
            </button>
        </div>
    </div>

    <!-- STATS ROW -->
    <div class="dash-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 22px;">
        <div class="dash-stat-card">
            <div class="dash-stat-icon dash-tone-green">
                <?= Icons::svg('briefcase', 22) ?>
            </div>
            <div class="dash-stat-body">
                <span class="dash-stat-label"><?= $isSo ? 'Wadarta Shaqaalaha' : 'Total Employees' ?></span>
                <span class="dash-stat-value num-tabular"><?= $totalEmployees ?></span>
                <span class="dash-stat-meta"><?= $isSo ? 'Dhammaan laamaha' : 'Across all branches' ?></span>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon dash-tone-gold">
                <?= Icons::svg('check-circle', 22) ?>
            </div>
            <div class="dash-stat-body">
                <span class="dash-stat-label"><?= $isSo ? 'Firfircoon (Active)' : 'Active Employees' ?></span>
                <span class="dash-stat-value num-tabular"><?= $activeEmployees ?></span>
                <span class="dash-stat-meta" style="color:var(--simda-green); font-weight:700;">
                    <?= $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100) : 0 ?>% <?= $isSo ? 'shaqaynaya' : 'in service' ?>
                </span>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon dash-tone-muted">
                <?= Icons::svg('x-circle', 22) ?>
            </div>
            <div class="dash-stat-body">
                <span class="dash-stat-label"><?= $isSo ? 'Dansan (Inactive)' : 'Inactive' ?></span>
                <span class="dash-stat-value num-tabular"><?= $inactiveEmployees ?></span>
                <span class="dash-stat-meta"><?= $isSo ? 'Hakad ku jira' : 'Suspended accounts' ?></span>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon dash-tone-info">
                <?= Icons::svg('building', 22) ?>
            </div>
            <div class="dash-stat-body">
                <span class="dash-stat-label"><?= $isSo ? 'Laamaha Shaqaalaysan' : 'Branches Staffed' ?></span>
                <span class="dash-stat-value num-tabular"><?= $branchesCount ?></span>
                <span class="dash-stat-meta"><?= $isSo ? 'Xarumaha nidaamka' : 'Branch locations' ?></span>
            </div>
        </div>
    </div>

    <!-- REGISTRATION FORM CARD (Collapsible / Toggleable) -->
    <div id="createEmpCard" class="adm-card" style="display:none; margin-bottom: 24px; border: 1.5px solid rgba(18,59,50,0.28); box-shadow: 0 10px 30px rgba(0,0,0,0.06); animation: simdaFadeIn 0.22s ease;">
        <div class="adm-card-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); padding: 18px 24px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:50%; background:rgba(18,59,50,0.12); color:var(--simda-green); display:flex; align-items:center; justify-content:center;">
                    <?= Icons::svg('user-plus', 18) ?>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:var(--text-main);">
                        <?= $isSo ? 'Diiwaangelinta Shaqaale Cusub (Employee Registration)' : 'Register New Employee' ?>
                    </h3>
                    <p style="margin:2px 0 0; font-size:0.8rem; color:var(--text-muted);">
                        <?= $isSo ? 'Foomkan wuxuu diiwaangeliyaa shaqaale, wuxuuna si toos ah furaha sirta ah (Password-ka) ugu dirayaa email-kiisa.' : 'Registers a staff loan officer and automatically delivers their login password to their email.' ?>
                    </p>
                </div>
            </div>

            <button type="button" class="btn btn-xs btn-outline" onclick="toggleEmpForm(false)" title="<?= $isSo ? 'Xir' : 'Close' ?>">
                ✕
            </button>
        </div>

        <div style="padding: 24px;">
            <form method="POST" action="?route=employee_create" id="empRegisterForm" onsubmit="return handleEmpSubmit(event)">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <input type="hidden" name="return_route" value="employees">

                <div class="form-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 18px;">
                    <!-- 1. FULL NAME -->
                    <div class="form-group">
                        <label for="empFullName" style="font-weight:700; font-size:0.84rem; display:block; margin-bottom:6px;">
                            <?= $isSo ? 'Magaca Buuxa ee Shaqaalaha *' : 'Employee Full Name *' ?>
                        </label>
                        <div style="position:relative;">
                            <input type="text" name="full_name" id="empFullName" class="form-control" placeholder="<?= $isSo ? 'Tusaale: Maxamed Cali Cabdi' : 'e.g. Mohamed Ali Abdi' ?>" required autocomplete="name">
                        </div>
                    </div>

                    <!-- 2. EMAIL ADDRESS -->
                    <div class="form-group">
                        <label for="empEmail" style="font-weight:700; font-size:0.84rem; display:block; margin-bottom:6px;">
                            <?= $isSo ? 'Email-ka Shaqaalaha * (Halka furaha loogu dirayo)' : 'Email Address * (Credentials will be sent here)' ?>
                        </label>
                        <div style="position:relative;">
                            <input type="email" name="email" id="empEmail" class="form-control" placeholder="<?= $isSo ? 'tusaale: mohamed.ali@simda.so' : 'e.g. mohamed.ali@simda.so' ?>" required autocomplete="email">
                        </div>
                    </div>

                    <!-- 3. BRANCH ASSIGNMENT -->
                    <div class="form-group">
                        <label for="empBranch" style="font-weight:700; font-size:0.84rem; display:block; margin-bottom:6px;">
                            <?= $isSo ? 'Qoondaynta Laanta * (Branch Assignment)' : 'Branch Assignment *' ?>
                        </label>
                        <select name="branch_id" id="empBranch" class="form-control" required>
                            <option value=""><?= $isSo ? '-- Dooro Laanta Loo Qoondeeyay --' : '-- Select Branch Assignment --' ?></option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= (int) $b['id'] ?>">
                                    <?= Security::e($b['name']) ?> (<?= Security::e($b['code']) ?>) — <?= Security::e($b['city'] ?? 'Muqdisho') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 4. PHONE NUMBER (OPTIONAL) -->
                    <div class="form-group">
                        <label for="empPhone" style="font-weight:700; font-size:0.84rem; display:block; margin-bottom:6px;">
                            <?= $isSo ? 'Telefoonka Shaqaalaha (Ikhtiyaari)' : 'Phone Number (Optional)' ?>
                        </label>
                        <input type="text" name="phone" id="empPhone" class="form-control" placeholder="+252 61 000 0000" autocomplete="tel">
                    </div>
                </div>

                <!-- AUTO-GENERATED CREDENTIALS PREVIEW -->
                <div style="background:rgba(18,59,50,0.04); border:1px solid rgba(18,59,50,0.14); border-radius:10px; padding:16px 20px; margin-bottom:20px;">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; align-items:center;">
                        <!-- Username (Auto derived) -->
                        <div>
                            <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:4px;">
                                <?= $isSo ? 'Username-ka Akoonka (Auto-generated)' : 'Account Username (Auto-generated)' ?>
                            </label>
                            <input type="text" name="username" id="empUsername" class="form-control" style="font-family:monospace; font-weight:700; background:var(--bg-surface);" readonly>
                        </div>

                        <!-- Password (Auto-generated) -->
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); margin:0;">
                                    <?= $isSo ? 'Furaha Sirta ah (Password-ka Tooska ah)' : 'Initial Password (Auto-generated)' ?>
                                </label>
                                <button type="button" class="btn btn-xs btn-outline" id="btnEmpRegenPw" onclick="regenerateEmpPassword()" style="font-size:0.74rem; font-weight:700; padding:2px 8px;">
                                    <?= Icons::svg('refresh', 12) ?> <?= $isSo ? 'Dhal Fure Cusub' : 'Regenerate' ?>
                                </button>
                            </div>
                            <input type="text" name="password" id="empPassword" class="form-control" style="font-family:monospace; font-weight:800; letter-spacing:1px; background:var(--bg-surface);" readonly>
                        </div>
                    </div>

                    <!-- Informational Banner -->
                    <div style="display:flex; align-items:center; gap:10px; margin-top:12px; padding-top:12px; border-top:1px dashed rgba(18,59,50,0.18); font-size:0.8rem; color:#123B32;">
                        <span style="font-size:1.1rem; line-height:1;">✉️</span>
                        <span>
                            <?= $isSo
                                ? '<strong>Ogeysiis Ammaan:</strong> Furahan sirta ah iyo xogta gelitaanka waxaa <strong>si toos ah loogu diri doonaa email-ka shaqaalaha</strong> isla marka aad gujiso batoonka hoose.'
                                : '<strong>Security Notice:</strong> This password along with sign-in instructions will be <strong>automatically delivered to the employee\'s registered email</strong> upon submission.' ?>
                        </span>
                    </div>
                </div>

                <!-- SUBMIT ACTIONS -->
                <div style="display:flex; gap:12px; align-items:center;">
                    <button type="submit" class="btn btn-primary" id="btnEmpSubmit" style="padding:10px 24px; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                        <?= Icons::svg('user-plus', 16) ?>
                        <span id="btnEmpSubmitText"><?= $isSo ? 'Diiwaangeli Shaqaalaha & U Dir Email-ka' : 'Register Employee & Send Credentials Email' ?></span>
                    </button>
                    <button type="button" class="btn btn-outline" onclick="toggleEmpForm(false)">
                        <?= $isSo ? 'Ka noqo' : 'Cancel' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DIRECTORY TOOLBAR & FILTERS -->
    <div class="adm-toolbar" style="margin-bottom: 14px;">
        <div class="adm-segment" role="group" aria-label="Status filter">
            <button type="button" class="staff-filter-pill is-active" data-filter="all" onclick="setEmpFilter('all', this)"><?= $isSo ? 'Dhammaan' : 'All' ?> (<?= $totalEmployees ?>)</button>
            <button type="button" class="staff-filter-pill" data-filter="active" onclick="setEmpFilter('active', this)"><?= $isSo ? 'Firfircoon' : 'Active' ?> (<?= $activeEmployees ?>)</button>
            <button type="button" class="staff-filter-pill" data-filter="inactive" onclick="setEmpFilter('inactive', this)"><?= $isSo ? 'Dansan' : 'Inactive' ?> (<?= $inactiveEmployees ?>)</button>
        </div>

        <select id="empBranchFilter" class="form-control adm-select" onchange="filterEmpTable()" aria-label="Branch filter" style="max-width:240px;">
            <option value=""><?= $isSo ? 'Dhammaan Laamaha' : 'All Branches' ?></option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= Security::e($b['name']) ?>"><?= Security::e($b['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="adm-search">
            <?= Icons::svg('search', 15) ?>
            <input type="search" id="empSearchInput" class="form-control" placeholder="<?= $isSo ? 'Raadi magaca, email, laanta ama telefoonka...' : 'Search name, email, branch or phone...' ?>" oninput="filterEmpTable()" autocomplete="off" aria-label="<?= $isSo ? 'Raadi' : 'Search' ?>">
        </div>
    </div>

    <!-- EMPLOYEES DIRECTORY TABLE (NO HORIZONTAL SCROLLBAR) -->
    <div class="adm-table-card">
        <table class="simda-staff-table" id="empTable">
            <thead>
                <tr>
                    <th style="width:28%; padding-left:18px;"><?= $isSo ? 'Shaqaalaha (Employee)' : 'Employee' ?></th>
                    <th style="width:22%;"><?= $isSo ? 'Laanta (Branch)' : 'Branch Assignment' ?></th>
                    <th style="width:24%;"><?= $isSo ? 'Email & Xiriirka' : 'Email & Contact' ?></th>
                    <th style="width:14%;"><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                    <th style="width:12%; text-align:right; padding-right:18px;"><span class="dash-sr"><?= $isSo ? 'Ficilada' : 'Actions' ?></span></th>
                </tr>
            </thead>
            <tbody id="empTableBody">
                <?php if (empty($employees)): ?>
                    <tr id="empEmptyRow">
                        <td colspan="5" style="text-align:center; padding:48px 20px; color:var(--text-muted);">
                            <div style="font-size:2rem; margin-bottom:8px;">👥</div>
                            <div style="font-weight:700; font-size:1.05rem; color:var(--text-main);"><?= $isSo ? 'Weli ma jiro shaqaale la diiwaangeliyay' : 'No employees registered yet' ?></div>
                            <p style="margin:6px 0 16px; font-size:0.84rem;"><?= $isSo ? 'Guji batoonka kore si aad u diiwaangeliso shaqaalahaaga ugu horreeya.' : 'Click the button above to register your first employee.' ?></p>
                            <button type="button" class="btn btn-sm btn-primary" onclick="toggleEmpForm(true)">
                                <?= Icons::svg('user-plus', 14) ?> <?= $isSo ? 'Diiwaangeli Shaqaale Hadda' : 'Register Employee Now' ?>
                            </button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $emp): ?>
                        <?php
                        $eId = (int) $emp['id'];
                        $isActive = (int) ($emp['is_active'] ?? 1);
                        $isPermBlocked = ((int) ($emp['lockout_tier'] ?? 0) >= 5) || (!$isActive && !empty($emp['security_block_reason']));
                        $isTempLocked = !empty($emp['locked_until']) && strtotime((string) $emp['locked_until']) > time();
                        $isSelf = ($eId === (int) ($curUser['id'] ?? 0));

                        $words = preg_split('/\s+/', trim((string) $emp['full_name']));
                        $initials = '';
                        if (!empty($words[0])) $initials .= mb_substr($words[0], 0, 1);
                        if (!empty($words[1])) $initials .= mb_substr($words[1], 0, 1);
                        if ($initials === '') $initials = mb_substr((string) $emp['username'], 0, 2);
                        $initials = mb_strtoupper($initials);

                        $searchMeta = mb_strtolower($emp['full_name'] . ' ' . $emp['username'] . ' ' . $emp['email'] . ' ' . ($emp['phone'] ?? '') . ' ' . ($emp['branch_name'] ?? '') . ' ' . ($emp['branch_code'] ?? ''));
                        ?>
                        <tr class="emp-row <?= $isActive ? 'is-active' : 'is-deactivated' ?>"
                            id="empRow_<?= $eId ?>"
                            data-eid="<?= $eId ?>"
                            data-active="<?= $isActive ? '1' : '0' ?>"
                            data-branch="<?= Security::e($emp['branch_name'] ?? '') ?>"
                            data-search="<?= Security::e($searchMeta) ?>">

                            <!-- PERSON -->
                            <td class="adm-cell-person" style="padding-left:18px;">
                                <div class="adm-person">
                                    <span class="adm-avatar dash-tone-green"><?= Security::e($initials) ?></span>
                                    <div class="adm-person-text">
                                        <span class="adm-primary">
                                            <?= Security::e($emp['full_name']) ?>
                                            <?php if ($isSelf): ?>
                                                <span class="adm-you"><?= $isSo ? 'Adiga' : 'You' ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="adm-secondary">@<?= Security::e($emp['username']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- BRANCH -->
                            <td>
                                <span class="adm-primary adm-with-icon" style="font-weight:700;">
                                    <?= Icons::svg('building', 13) ?> <?= Security::e($emp['branch_name'] ?? ($isSo ? 'Laan Gaar ah' : 'Assigned Branch')) ?>
                                </span>
                                <span class="adm-secondary num-tabular" style="font-size:0.75rem;">
                                    <?= Security::e($emp['branch_code'] ?? '') ?>
                                </span>
                            </td>

                            <!-- EMAIL & CONTACT -->
                            <td class="adm-cell-contact">
                                <a href="mailto:<?= Security::e($emp['email']) ?>" class="adm-email" title="<?= Security::e($emp['email']) ?>">
                                    <?= Security::e($emp['email']) ?>
                                </a>
                                <?php if (!empty($emp['phone'])): ?>
                                    <span class="adm-secondary num-tabular" dir="ltr"><?= Security::e($emp['phone']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- STATUS -->
                            <td>
                                <?php if ($isPermBlocked): ?>
                                    <span class="adm-status is-danger"><?= $isSo ? 'Xaniban' : 'Blocked' ?></span>
                                <?php elseif ($isTempLocked): ?>
                                    <?php $remMin = (int) ceil((strtotime((string) $emp['locked_until']) - time()) / 60); ?>
                                    <span class="adm-status is-warn"><?= $isSo ? "Hakad ({$remMin}m)" : "Locked ({$remMin}m)" ?></span>
                                <?php elseif (!$isActive): ?>
                                    <span class="adm-status is-off"><?= $isSo ? 'Dansan' : 'Inactive' ?></span>
                                <?php else: ?>
                                    <span class="adm-status is-on"><?= $isSo ? 'Firfircoon' : 'Active' ?></span>
                                <?php endif; ?>

                                <span class="adm-secondary num-tabular" style="font-size:0.72rem; margin-top:2px;">
                                    <?= !empty($emp['last_login_at'])
                                        ? Security::e(date('d/m/Y H:i', strtotime((string) $emp['last_login_at'])))
                                        : ($isSo ? 'Weli ma gelin' : 'Never logged in') ?>
                                </span>
                            </td>

                            <!-- ACTIONS -->
                            <td style="text-align:right; padding-right:18px;">
                                <div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">
                                    <!-- EDIT MODAL TRIGGER -->
                                    <button type="button" class="btn btn-xs btn-outline"
                                            onclick="openEmpEditModal(<?= htmlspecialchars(json_encode([
                                                'id' => $eId,
                                                'full_name' => $emp['full_name'],
                                                'username' => $emp['username'],
                                                'email' => $emp['email'],
                                                'phone' => $emp['phone'] ?? '',
                                                'branch_id' => $emp['branch_id'] ?? 0,
                                                'role_id' => $emp['role_id'] ?? 0,
                                                'is_active' => $isActive,
                                            ]), ENT_QUOTES, 'UTF-8') ?>)"
                                            title="<?= $isSo ? 'Wax ka beddel' : 'Edit Employee' ?>">
                                        <?= Icons::svg('edit', 12) ?> <?= $isSo ? 'Beddel' : 'Edit' ?>
                                    </button>

                                    <!-- TOGGLE ACTIVE / DEACTIVATE -->
                                    <?php if (!$isSelf): ?>
                                        <form method="POST" action="?route=user_toggle_status" style="display:inline;" onsubmit="return confirm('<?= $isActive ? ($isSo ? 'Ma hubtaa inaad doonayso inaad damiso akoonka shaqaalahan?' : 'Deactivate this employee?') : ($isSo ? 'Ma hubtaa inaad dib u hawlgelinayso shaqaalahan?' : 'Reactivate this employee?') ?>');">
                                            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                            <input type="hidden" name="user_id" value="<?= $eId ?>">
                                            <input type="hidden" name="return_route" value="employees">
                                            <?php if ($isActive): ?>
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="<?= $isSo ? 'Demi akoonka' : 'Deactivate' ?>">
                                                    <?= $isSo ? 'Demi' : 'Deactivate' ?>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-xs btn-outline-success" title="<?= $isSo ? 'Dib u hawlgeli' : 'Activate' ?>">
                                                    <?= Icons::svg('check-circle', 12) ?> <?= $isSo ? 'Hawlgeli' : 'Activate' ?>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>

                                    <!-- PERMISSIONS SHORTCUT (ADMIN) -->
                                    <?php if ($curRole === 'admin'): ?>
                                        <a href="?route=permissions&user_id=<?= $eId ?>" class="btn btn-xs btn-outline" title="<?= $isSo ? 'Awoodaha' : 'Permissions' ?>">
                                            <?= Icons::svg('shield', 12) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- NO MATCHING SEARCH RESULTS ROW -->
                <tr id="empNoResultsRow" style="display:none;">
                    <td colspan="5" style="text-align:center; padding:36px; color:var(--text-muted);">
                        <div style="font-size:1.6rem; margin-bottom:8px;">🔍</div>
                        <div style="font-weight:700; font-size:0.95rem; color:var(--text-main);"><?= $isSo ? 'Lama helin shaqaale shuruudahaas buuxiyay' : 'No matching employees found' ?></div>
                        <div style="font-size:0.8rem; margin-top:4px;"><?= $isSo ? 'Fadlan hubi magaca ama email-ka aad qortay.' : 'Try adjusting your search keywords.' ?></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<!-- EDIT EMPLOYEE MODAL DIALOG -->
<div id="empEditModalOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.58); backdrop-filter:blur(3px); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:16px; width:100%; max-width:540px; box-shadow:0 24px 60px rgba(0,0,0,0.28); overflow:hidden; animation:simdaFadeIn 0.2s ease;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:18px 24px; border-bottom:1px solid var(--border-color); background:var(--bg-elevated);">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="color:var(--simda-green); font-size:1.2rem;"><?= Icons::svg('edit', 18) ?></span>
                <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:var(--text-main);" id="editEmpModalTitle">
                    <?= $isSo ? 'Wax Ka Beddel Xogta Shaqaalaha' : 'Edit Employee Details' ?>
                </h3>
            </div>
            <button type="button" class="btn btn-xs btn-outline" onclick="closeEmpEditModal()">✕</button>
        </div>

        <form method="POST" action="?route=user_update" style="padding:24px;">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
            <input type="hidden" name="user_id" id="editEmpUserId" value="0">
            <input type="hidden" name="role_id" id="editEmpRoleId" value="<?= (int) ($employeeRole['id'] ?? 0) ?>">
            <input type="hidden" name="return_route" value="employees">

            <div class="form-group" style="margin-bottom:14px;">
                <label for="editEmpFullName" style="font-weight:700; font-size:0.82rem; display:block; margin-bottom:5px;">
                    <?= $isSo ? 'Magaca Buuxa *' : 'Full Name *' ?>
                </label>
                <input type="text" name="full_name" id="editEmpFullName" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label for="editEmpUsername" style="font-weight:700; font-size:0.82rem; display:block; margin-bottom:5px;">
                    <?= $isSo ? 'Username *' : 'Username *' ?>
                </label>
                <input type="text" name="username" id="editEmpUsername" class="form-control" required style="font-family:monospace;">
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label for="editEmpEmail" style="font-weight:700; font-size:0.82rem; display:block; margin-bottom:5px;">
                    <?= $isSo ? 'Email Address *' : 'Email Address *' ?>
                </label>
                <input type="email" name="email" id="editEmpEmail" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label for="editEmpBranch" style="font-weight:700; font-size:0.82rem; display:block; margin-bottom:5px;">
                    <?= $isSo ? 'Laanta Loo Qoondeeyay *' : 'Branch Assignment *' ?>
                </label>
                <select name="branch_id" id="editEmpBranch" class="form-control" required>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= (int) $b['id'] ?>"><?= Security::e($b['name']) ?> (<?= Security::e($b['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label for="editEmpPhone" style="font-weight:700; font-size:0.82rem; display:block; margin-bottom:5px;">
                    <?= $isSo ? 'Telefoonka' : 'Phone' ?>
                </label>
                <input type="text" name="phone" id="editEmpPhone" class="form-control">
            </div>

            <div class="form-group" style="margin-bottom:18px;">
                <label for="editEmpNewPassword" style="font-weight:700; font-size:0.82rem; display:block; margin-bottom:5px;">
                    <?= $isSo ? 'Beddel Furaha Sirta ah (Faaruqi haddii aadan beddelayn)' : 'New Password (Leave blank to keep unchanged)' ?>
                </label>
                <input type="password" name="new_password" id="editEmpNewPassword" class="form-control" placeholder="<?= $isSo ? 'Geli furaha cusub haddii loo baahdo' : 'Enter new password if changing' ?>" autocomplete="new-password">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:14px; border-top:1px solid var(--border-color);">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeEmpEditModal()"><?= $isSo ? 'Ka noqo' : 'Cancel' ?></button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700;">
                    <?= Icons::svg('check-circle', 14) ?> <?= $isSo ? 'Keydi Isbeddelka' : 'Save Changes' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentEmpFilter = 'all';

function generateEmpPassword(rawName) {
    const raw = (rawName || '').trim();
    const first = raw.split(/\s+/)[0] || 'simda';
    const clean = first.toLowerCase().replace(/[^a-z0-9]/g, '') || 'simda';
    const digits = Math.floor(1000 + Math.random() * 9000);
    return clean + digits + '@!';
}

function regenerateEmpPassword() {
    const nameInput = document.getElementById('empFullName');
    const pwdInput = document.getElementById('empPassword');
    if (pwdInput) {
        pwdInput.value = generateEmpPassword(nameInput ? nameInput.value : '');
    }
}

function toggleEmpForm(forceState) {
    const card = document.getElementById('createEmpCard');
    const btnText = document.getElementById('empToggleText');
    const btnIcon = document.getElementById('empToggleIcon');
    if (!card) return;

    const isOpen = (typeof forceState === 'boolean') ? forceState : (card.style.display === 'none' || !card.style.display);
    card.style.display = isOpen ? 'block' : 'none';

    if (btnText) {
        btnText.textContent = isOpen ? '<?= $isSo ? 'Qari Foomka' : 'Hide Form' ?>' : '<?= $isSo ? 'Diiwaangeli Shaqaale Cusub' : 'Register New Employee' ?>';
    }
    if (btnIcon) {
        btnIcon.innerHTML = isOpen ? '<?= Icons::svg('x', 16) ?>' : '<?= Icons::svg('user-plus', 16) ?>';
    }

    if (isOpen) {
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        const nameInput = document.getElementById('empFullName');
        if (nameInput) nameInput.focus();
        // Generate initial password if empty
        const pwdInput = document.getElementById('empPassword');
        if (pwdInput && !pwdInput.value) {
            pwdInput.value = generateEmpPassword('');
        }
    }
}

function handleEmpSubmit(e) {
    const btn = document.getElementById('btnEmpSubmit');
    const btnText = document.getElementById('btnEmpSubmitText');
    if (btn && btnText) {
        btn.disabled = true;
        btnText.textContent = '<?= $isSo ? 'Diiwaangelinayaa & U dirayaa Email-ka...' : 'Registering & Sending Email...' ?>';
    }
    return true;
}

function setEmpFilter(filter, btn) {
    currentEmpFilter = filter;
    document.querySelectorAll('.staff-filter-pill').forEach(b => b.classList.remove('is-active'));
    if (btn) btn.classList.add('is-active');
    filterEmpTable();
}

function filterEmpTable() {
    const searchInput = document.getElementById('empSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const branchFilter = document.getElementById('empBranchFilter');
    const selBranch = branchFilter ? branchFilter.value.toLowerCase().trim() : '';

    const rows = document.querySelectorAll('tr.emp-row');
    let visibleCount = 0;

    rows.forEach(function (row) {
        const searchMeta = row.getAttribute('data-search') || '';
        const branchMeta = (row.getAttribute('data-branch') || '').toLowerCase();
        const isActive = row.getAttribute('data-active') === '1';

        const matchesQuery = !query || searchMeta.indexOf(query) !== -1;
        const matchesBranch = !selBranch || branchMeta.indexOf(selBranch) !== -1;

        let matchesStatus = true;
        if (currentEmpFilter === 'active') matchesStatus = isActive;
        if (currentEmpFilter === 'inactive') matchesStatus = !isActive;

        if (matchesQuery && matchesBranch && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noResults = document.getElementById('empNoResultsRow');
    if (noResults) {
        noResults.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }
}

function openEmpEditModal(emp) {
    document.getElementById('editEmpUserId').value = emp.id;
    document.getElementById('editEmpFullName').value = emp.full_name || '';
    document.getElementById('editEmpUsername').value = emp.username || '';
    document.getElementById('editEmpEmail').value = emp.email || '';
    document.getElementById('editEmpPhone').value = emp.phone || '';
    document.getElementById('editEmpBranch').value = emp.branch_id || '';
    document.getElementById('editEmpRoleId').value = emp.role_id || '';
    document.getElementById('editEmpNewPassword').value = '';

    const overlay = document.getElementById('empEditModalOverlay');
    if (overlay) overlay.style.display = 'flex';
}

function closeEmpEditModal() {
    const overlay = document.getElementById('empEditModalOverlay');
    if (overlay) overlay.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('empFullName');
    const unameInput = document.getElementById('empUsername');
    const pwdInput = document.getElementById('empPassword');

    if (nameInput) {
        nameInput.addEventListener('input', function () {
            const raw = nameInput.value.trim().toLowerCase();
            const words = raw.split(/\s+/);
            if (unameInput) {
                if (words.length >= 2 && words[0] && words[1]) {
                    unameInput.value = words[0].replace(/[^a-z0-9]/g, '') + '.' + words[1].replace(/[^a-z0-9]/g, '');
                } else if (words.length === 1 && words[0]) {
                    unameInput.value = words[0].replace(/[^a-z0-9]/g, '');
                }
            }
            if (pwdInput && (!pwdInput.value || pwdInput.dataset.touched !== 'true')) {
                pwdInput.value = generateEmpPassword(nameInput.value);
            }
        });
    }

    // Initialize password
    if (pwdInput && !pwdInput.value) {
        pwdInput.value = generateEmpPassword('');
    }
});
</script>
