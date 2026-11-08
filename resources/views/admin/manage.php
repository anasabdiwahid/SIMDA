<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isSo = Lang::locale() === 'so';
$curUser = $currentUser ?? ($user ?? []);
$curRole = $curUser['role_slug'] ?? 'admin';
$roleFocus = $roleFocus ?? null;
$returnRoute = $returnRoute ?? 'users';
$isEmployeesRoute = ($returnRoute === 'employees') || (($_GET['route'] ?? '') === 'employees') || ($roleFocus === 'loan_officer');
$focusLabel = match ($roleFocus) {
    'manager' => 'Manager',
    'branch_manager' => 'Branch Manager',
    'loan_officer' => $isSo ? 'Shaqaale' : 'Employee',
    default => null,
};
$newUserLabel = $isEmployeesRoute
    ? ($isSo ? 'Diiwaangeli Shaqaale Cusub' : 'Register New Employee')
    : ($focusLabel !== null
        ? ($isSo ? $focusLabel . ' Cusub' : 'New ' . $focusLabel)
        : ($isSo ? 'Isticmaale Cusub' : 'New User'));
$returnField = '<input type="hidden" name="return_route" value="' . Security::e($returnRoute) . '">';
?>

<?php
$adminDescriptions = [
    'branches' => $isSo ? 'Maamul laamaha SIMDA, xiriirkooda iyo xaaladdooda.' : 'Manage SIMDA branches, their contacts and status.',
    'users' => $isEmployeesRoute
        ? ($isSo ? 'Diiwaangelinta shaqaalaha cusub iyo maamulka akoonnadooda laamaha.' : 'Register new employees, assign branches, and manage staff accounts.')
        : match ($roleFocus) {
            'manager' => $isSo ? 'Maamulayaasha guud ee shirkadda oo dhan. Waxay go\'aamiyaan codsiyada ka badan $50,000.' : 'General managers of the whole company. They decide applications above $50,000.',
            'branch_manager' => $isSo ? 'Madaxda laamaha. Waxay go\'aamiyaan codsiyada laantooda ee $5,000 ilaa $50,000.' : 'Branch heads. They decide their branch\'s applications from $5,000 to $50,000.',
            'loan_officer' => $isSo ? 'Shaqaalaha (Employees) laamaha SIMDA.' : 'Employees and loan officers of SIMDA branches.',
            default => $isSo ? 'Maamul shaqaalaha iyo isticmaalayaasha, doorkooda iyo gelitaankooda.' : 'Manage staff and users, their roles and access.',
        },
    'permissions' => $isSo ? 'Dooro waxa uu qof kasta ka arki karo menu-ka nidaamka.' : 'Choose what each person sees in the system menu.',
    'settings' => $isSo ? 'Xeerarka maalgelinta iyo macluumaadka xiriirka ee hay\'adda.' : 'Financing rules and institution contact details.',
    'audit_logs' => $isSo ? '100-kii dhacdo ee ugu dambeeyay ee nidaamka.' : 'The latest 100 system events.',
];
$adminTabs = [
    ['route' => 'users', 'icon' => 'users', 'label' => $isSo ? 'All Users' : 'All Users', 'count' => null, 'show' => true],
    ['route' => 'managers', 'icon' => 'briefcase', 'label' => $isSo ? 'Maamulayaasha Guud (GM)' : 'General Managers', 'count' => null, 'show' => $curRole === 'admin'],
    ['route' => 'branch_managers', 'icon' => 'shield', 'label' => $isSo ? 'Maamulayaasha Laamaha (BM)' : 'Branch Managers', 'count' => null, 'show' => in_array($curRole, ['admin', 'manager'], true)],
    ['route' => 'employees', 'icon' => 'user-check', 'label' => $isSo ? 'Shaqaalaha (Employees)' : 'Employees', 'count' => null, 'show' => true],
    ['route' => 'branches', 'icon' => 'building', 'label' => $isSo ? 'Laamaha' : 'Branches', 'count' => count($branches ?? []), 'show' => true],
    ['route' => 'permissions', 'icon' => 'shield', 'label' => $isSo ? 'Awoodaha' : 'Permissions', 'count' => null, 'show' => $curRole === 'admin'],
    ['route' => 'audit_logs', 'icon' => 'activity', 'label' => $isSo ? 'Diiwaanka Audit' : 'Audit Logs', 'count' => null, 'show' => in_array($curRole, ['admin', 'manager'], true)],
    ['route' => 'settings', 'icon' => 'settings', 'label' => 'Settings', 'count' => null, 'show' => in_array($curRole, ['admin', 'manager'], true)],
];
?>
<div class="adm">
    <header class="adm-head adm-page-head">
        <div>
            <h1><?= Security::e($isEmployeesRoute ? ($isSo ? 'Shaqaalaha (Employees)' : 'Employees') : ($sectionTitle ?? ($isSo ? 'Maamulka Nidaamka' : 'System Administration'))) ?></h1>
            <p><?= Security::e($adminDescriptions[$mode ?? ''] ?? '') ?></p>
        </div>
        <?php if ($roleFocus !== null && !$isEmployeesRoute && !empty($roles)): ?>
            <div class="adm-head-actions">
                <button type="button" class="btn btn-sm btn-gold" id="btnToggleCreateUser" onclick="toggleCreateUserForm()">
                    <span id="createUserToggleIcon"><?= Icons::svg('plus', 14) ?></span>
                    <span id="createUserToggleText"><?= Security::e($newUserLabel) ?></span>
                </button>
            </div>
        <?php elseif (($mode ?? '') === 'branches' && in_array($curRole, ['admin', 'manager'], true)): ?>
            <div class="adm-head-actions">
                <button type="button" class="btn btn-sm btn-gold" onclick="toggleBranchForm()">
                    <?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Laan Cusub' : 'New Branch' ?></span>
                </button>
            </div>
        <?php endif; ?>
    </header>

    <nav class="adm-tabs" aria-label="<?= $isSo ? 'Qaybaha maamulka' : 'Administration sections' ?>">
        <?php foreach ($adminTabs as $tab): ?>
            <?php if (!$tab['show']) continue; ?>
            <?php
            $tabRoute = $tab['route'];
            $isActiveTab = false;
            if ($isEmployeesRoute && $tabRoute === 'employees') {
                $isActiveTab = true;
            } elseif ($roleFocus === 'manager' && $tabRoute === 'managers') {
                $isActiveTab = true;
            } elseif ($roleFocus === 'branch_manager' && $tabRoute === 'branch_managers') {
                $isActiveTab = true;
            } elseif (!$isEmployeesRoute && ($mode ?? '') === 'users' && $roleFocus === null && $tabRoute === 'users') {
                $isActiveTab = true;
            } elseif (($mode ?? '') === $tabRoute && $roleFocus === null) {
                $isActiveTab = true;
            }
            ?>
            <a href="?route=<?= Security::e($tab['route']) ?>" class="adm-tab <?= $isActiveTab ? 'is-active' : '' ?>" <?= $isActiveTab ? 'aria-current="page"' : '' ?>>
                <?= Icons::svg($tab['icon'], 15) ?>
                <span><?= Security::e($tab['label']) ?></span>
                <?php if ($tab['count'] !== null): ?>
                    <span class="adm-tab-count num-tabular"><?= (int) $tab['count'] ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- ========================================================================= -->
    <!-- 1. BRANCHES MANAGEMENT VIEW                                               -->
    <!-- ========================================================================= -->
    <?php if (($mode ?? '') === 'branches'): ?>
        <?php
        $branchKpis = [
            ['label' => $isSo ? 'Laamaha' : 'Branches', 'value' => count($branches), 'sub' => $isSo ? 'Guud ahaan' : 'In total', 'icon' => 'building', 'tone' => 'info'],
            ['label' => $isSo ? 'Firfircoon' : 'Active', 'value' => count(array_filter($branches, fn ($b) => (int) $b['is_active'] === 1)), 'sub' => $isSo ? 'Laamaha shaqaynaya' : 'Branches open', 'icon' => 'check-circle', 'tone' => 'green'],
            ['label' => $isSo ? 'Shaqaalaha' : 'Staff', 'value' => array_sum(array_column($branches, 'staff_count')), 'sub' => $isSo ? 'Laamaha oo dhan' : 'Across branches', 'icon' => 'users', 'tone' => 'gold'],
            ['label' => $isSo ? 'Macaamiisha' : 'Clients', 'value' => array_sum(array_column($branches, 'clients_count')), 'sub' => $isSo ? 'Laamaha oo dhan' : 'Across branches', 'icon' => 'user-check', 'tone' => 'muted'],
        ];
        ?>
        <section class="dash-kpis" aria-label="<?= $isSo ? 'Tirooyinka muhiimka ah' : 'Key figures' ?>">
            <?php foreach ($branchKpis as $kpi): ?>
                <div class="kpi-card dash-kpi dash-tone-<?= Security::e($kpi['tone']) ?>">
                    <div class="kpi-header">
                        <span class="kpi-label"><?= Security::e($kpi['label']) ?></span>
                        <span class="kpi-icon"><?= Icons::svg($kpi['icon'], 17) ?></span>
                    </div>
                    <div class="kpi-value num-tabular"><?= (int) $kpi['value'] ?></div>
                    <div class="kpi-sub"><?= Security::e($kpi['sub']) ?></div>
                </div>
            <?php endforeach; ?>
        </section>

        <?php if (in_array($curRole, ['admin', 'manager'], true)): ?>
            <form method="POST" action="?route=branch_create" id="createBranchForm" class="adm-form" style="display:none;">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <div class="adm-form-head">
                    <h3><?= $isSo ? 'Abuur Laan Cusub' : 'Create New Branch' ?></h3>
                    <button type="button" class="adm-icon-btn" onclick="toggleBranchForm(false)" aria-label="<?= $isSo ? 'Xir' : 'Close' ?>"><?= Icons::svg('x', 15) ?></button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label><?= $isSo ? 'Koodhka Laanta *' : 'Branch Code *' ?></label>
                        <div style="display:flex;gap:6px;">
                            <input type="text" name="code" id="branchCodeInput" class="form-control" required placeholder="BR-HOD-02" style="font-weight:700;text-transform:uppercase;">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="suggestBranchCode()" style="white-space:nowrap;font-weight:700;" title="Auto-suggest code">
                                <?= Icons::svg('refresh', 12) ?> Auto
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
                        <label><?= $isSo ? 'Madaxa Laanta (Branch Manager)' : 'Branch Manager Name' ?></label>
                        <input type="text" name="manager_name" class="form-control" placeholder="Cabdiraxmaan Cali">
                    </div>
                </div>
                <div class="adm-form-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Keydi Laanta Cusub' : 'Save Branch' ?></span>
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="toggleBranchForm(false)"><?= $isSo ? 'Ka noqo' : 'Cancel' ?></button>
                </div>
            </form>

            <script>
            function toggleBranchForm(forceState) {
                var form = document.getElementById('createBranchForm');
                if (!form) return;
                var isOpening = (typeof forceState === 'boolean') ? forceState : form.style.display === 'none';
                form.style.display = isOpening ? 'block' : 'none';
                if (isOpening) {
                    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
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
            <div class="adm-note">
                <?= Icons::svg('info', 16) ?>
                <div><?= $isSo ? 'Waxaad arkaysaa macluumaadka laamaha. Abuurista laamaha cusub waxaa awood u leh Maamulaha Guud iyo Admin-ka.' : 'You can view the branch directory. Creating branches is reserved for the General Manager and Admin.' ?></div>
            </div>
        <?php endif; ?>

        <div class="adm-table-card">
            <div class="table-responsive adm-scroll">
                <table class="simda-table adm-table">
                    <thead>
                        <tr>
                            <th><?= $isSo ? 'Laanta' : 'Branch' ?></th>
                            <th><?= $isSo ? 'Goobta' : 'Location' ?></th>
                            <th><?= $isSo ? 'Telefoonka' : 'Phone' ?></th>
                            <th><?= $isSo ? 'Madaxa Laanta' : 'Manager' ?></th>
                            <th class="adm-num"><?= $isSo ? 'Shaqaale' : 'Staff' ?></th>
                            <th class="adm-num"><?= $isSo ? 'Macaamiil' : 'Clients' ?></th>
                            <th><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                            <?php if ($curRole === 'admin'): ?>
                                <th><span class="dash-sr"><?= $isSo ? 'Ficilka' : 'Action' ?></span></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($branches as $b): ?>
                            <tr class="<?= $b['is_active'] ? '' : 'is-muted' ?>">
                                <td>
                                    <div class="adm-person">
                                        <span class="adm-avatar dash-tone-<?= $b['is_active'] ? 'green' : 'muted' ?>"><?= Icons::svg('building', 16) ?></span>
                                        <div class="adm-person-text">
                                            <span class="adm-primary"><?= Security::e($b['name']) ?></span>
                                            <span class="adm-secondary num-tabular"><?= Security::e($b['code']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= Security::e($b['city'] . ($b['district'] !== '' ? ', ' . $b['district'] : '')) ?></td>
                                <td class="num-tabular adm-nowrap" dir="ltr"><?= Security::e($b['phone']) ?></td>
                                <td><?= !empty($b['manager_name']) ? Security::e($b['manager_name']) : '<span class="adm-secondary">—</span>' ?></td>
                                <td class="adm-num num-tabular"><?= (int) ($b['staff_count'] ?? 0) ?></td>
                                <td class="adm-num num-tabular"><?= (int) ($b['clients_count'] ?? 0) ?></td>
                                <td>
                                    <span class="adm-status <?= $b['is_active'] ? 'is-on' : 'is-off' ?>">
                                        <?= $b['is_active'] ? ($isSo ? 'Firfircoon' : 'Active') : ($isSo ? 'Xiran' : 'Inactive') ?>
                                    </span>
                                </td>
                                <?php if ($curRole === 'admin'): ?>
                                    <td class="adm-end">
                                        <?php if ((int) $b['id'] !== 1): ?>
                                            <form method="POST" action="?route=branch_toggle" class="adm-inline-form" data-confirm-text="<?= Security::e($b['is_active'] ? ($isSo ? 'Ma xiraysaa ' . $b['name'] . '?' : 'Deactivate ' . $b['name'] . '?') : ($isSo ? 'Ma furaysaa ' . $b['name'] . '?' : 'Activate ' . $b['name'] . '?')) ?>" onsubmit="return confirm(this.dataset.confirmText);">
                                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                                <input type="hidden" name="branch_id" value="<?= (int) $b['id'] ?>">
                                                <button type="submit" class="adm-icon-btn <?= $b['is_active'] ? 'is-danger' : 'is-success' ?>" title="<?= $b['is_active'] ? ($isSo ? 'Xir' : 'Deactivate') : ($isSo ? 'Fur' : 'Activate') ?>" aria-label="<?= $b['is_active'] ? ($isSo ? 'Xir' : 'Deactivate') : ($isSo ? 'Fur' : 'Activate') ?> <?= Security::e($b['name']) ?>">
                                                    <?= Icons::svg($b['is_active'] ? 'power' : 'check-circle', 15) ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="adm-secondary adm-nowrap"><?= Icons::svg('lock', 12) ?> <?= $isSo ? 'Xarunta (HQ)' : 'Protected HQ' ?></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- ========================================================================= -->
    <!-- 2. USERS & STAFF MANAGEMENT VIEW (Strict Hierarchy Rule)                  -->
    <!-- ========================================================================= -->
    <?php elseif (($mode ?? '') === 'users'): ?>
        <!-- HIERARCHY RULES EXPLANATION BANNER -->
        <?php if ($roleFocus === null): ?>
        <div class="adm-note">
            <?= Icons::svg('shield', 16) ?>
            <div>
                <?php if ($curRole === 'admin'): ?>
                    <?= $isSo
                        ? 'Adigu waxaad tahay <strong>Admin (Maamulaha Guud)</strong>. Waxaad abuuri kartaa: <strong>Manager, Branch Manager, Header Office, iyo Client</strong>. Xeerka nidaamku <u>ma ogola</u> inaad abuurto Admin kale.'
                        : 'You are signed in as <strong>System Administrator</strong>. You can create: <strong>Manager, Branch Manager, Header Office, and Client</strong>. By institutional policy, you <u>cannot</u> create another Admin.' ?>
                <?php elseif ($curRole === 'manager'): ?>
                    <?= $isSo
                        ? 'Adigu waxaad tahay <strong>General Manager</strong>. Waxaad abuuri kartaa: <strong>Branch Manager, Header Office, iyo Client</strong>. Xeerka nidaamku <u>ma ogola</u> inaad abuurto Admin ama Manager kale.'
                        : 'You are signed in as <strong>General Operations Manager</strong>. You can create: <strong>Branch Manager, Header Office, and Client</strong>. You <u>cannot</u> create an Admin or another Manager.' ?>
                <?php elseif ($curRole === 'branch_manager'): ?>
                    <?= $isSo
                        ? 'Adigu waxaad tahay <strong>Branch Manager (Maamulaha Laanta)</strong>. Waxaad abuuri kartaa: <strong>Header Office iyo Client</strong> oo ka tirsan laantaada kaliya. Xeerka nidaamku <u>ma ogola</u> inaad abuurto Maamule kale.'
                        : 'You are signed in as <strong>Branch Manager</strong>. You can create: <strong>Header Office staff and Clients</strong> strictly for your assigned branch. You <u>cannot</u> create another Manager.' ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- CREATE STAFF USER FORM -->
        <?php if (!empty($roles)): ?>
            <?php
            $totalStaff = count($users);
            $activeStaff = 0;
            foreach ($users as $staffRow) {
                if ((int) $staffRow['is_active'] === 1) {
                    $activeStaff++;
                }
            }
            $inactiveStaff = $totalStaff - $activeStaff;
            ?>
            <?php if ($roleFocus !== null && !$isEmployeesRoute): ?>
                <?php
                $focusLimit = match ($roleFocus) {
                    'manager' => ['> $50,000', $isSo ? 'Codsiyada ka badan' : 'Applications above'],
                    'branch_manager' => ['$50,000', $isSo ? 'Ilaa xadkan, laga bilaabo $5,000' : 'Up to this, from $5,000'],
                    default => ['$5,000', $isSo ? 'Xadkan iyo wixii ka yar' : 'This or less'],
                };
                $focusKpis = [
                    ['label' => $isSo ? 'Guud' : 'Total', 'value' => (string) $totalStaff, 'sub' => $focusLabel, 'icon' => 'users', 'tone' => 'info'],
                    ['label' => $isSo ? 'Firfircoon' : 'Active', 'value' => (string) $activeStaff, 'sub' => $isSo ? 'Way geli karaan' : 'Can sign in', 'icon' => 'check-circle', 'tone' => 'green'],
                    ['label' => $isSo ? 'Dansan' : 'Inactive', 'value' => (string) $inactiveStaff, 'sub' => $isSo ? 'Ma geli karaan' : 'Cannot sign in', 'icon' => 'power', 'tone' => 'muted'],
                    ['label' => $isSo ? 'Xadka Ogolaanshaha' : 'Approval Limit', 'value' => $focusLimit[0], 'sub' => $focusLimit[1], 'icon' => 'shield', 'tone' => 'gold'],
                ];
                ?>
                <section class="dash-kpis" aria-label="<?= $isSo ? 'Tirooyinka muhiimka ah' : 'Key figures' ?>">
                    <?php foreach ($focusKpis as $kpi): ?>
                        <div class="kpi-card dash-kpi dash-tone-<?= Security::e($kpi['tone']) ?>">
                            <div class="kpi-header">
                                <span class="kpi-label"><?= Security::e($kpi['label']) ?></span>
                                <span class="kpi-icon"><?= Icons::svg($kpi['icon'], 17) ?></span>
                            </div>
                            <div class="kpi-value num-tabular"><?= Security::e($kpi['value']) ?></div>
                            <div class="kpi-sub"><?= Security::e($kpi['sub']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
            <div class="adm-bar">
                <div class="adm-bar-title">
                    <h2><?= Security::e($isEmployeesRoute ? ($isSo ? 'Shaqaalaha (Employees)' : 'Employees') : ($isSo ? 'Isticmaalayaasha' : 'Staff & Users')) ?></h2>
                    <div class="adm-counts">
                        <span><strong class="num-tabular"><?= $totalStaff ?></strong> <?= $isSo ? 'guud' : 'total' ?></span>
                        <span><i class="adm-dot is-on"></i><strong class="num-tabular"><?= $activeStaff ?></strong> <?= $isSo ? 'firfircoon' : 'active' ?></span>
                        <span><i class="adm-dot is-off"></i><strong class="num-tabular"><?= $inactiveStaff ?></strong> <?= $isSo ? 'dansan' : 'inactive' ?></span>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-gold" id="btnToggleCreateUser" onclick="toggleCreateUserForm()">
                    <span id="createUserToggleIcon"><?= Icons::svg('plus', 14) ?></span>
                    <span id="createUserToggleText"><?= Security::e($newUserLabel) ?></span>
                </button>
            </div>
            <?php endif; ?>

            <div class="simda-loading" id="createUserLoading" role="status" aria-live="polite" hidden>
                <div class="simda-loading-card">
                    <img src="assets/images/simda-logo-white.png" alt="SIMDA" class="simda-loading-logo">
                    <span class="simda-loading-bar" aria-hidden="true"><span></span></span>
                    <strong><?= $isSo ? 'Akoonka waa la samaynayaa...' : 'Creating the account...' ?></strong>
                    <p><?= $isSo ? 'Macluumaadka gelitaanka waxaa loo dirayaa email-kiisa. Fadlan ha xirin boggan.' : 'The login details are being emailed. Please keep this page open.' ?></p>
                </div>
            </div>

            <form method="POST" action="?route=user_create" id="createUserForm" class="adm-form" style="display:none;" data-loading="createUserLoading">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <?= $returnField ?>
                <div class="adm-form-head">
                    <h3><?= $focusLabel !== null ? Security::e($newUserLabel) : ($isSo ? 'Diiwaangeli Isticmaale Cusub' : 'Create Staff or User Account') ?></h3>
                    <button type="button" class="adm-icon-btn" onclick="toggleCreateUserForm(false)" aria-label="<?= $isSo ? 'Xir' : 'Close' ?>"><?= Icons::svg('x', 15) ?></button>
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
                        <label><?= $isSo ? 'Email-ka *' : 'Email Address *' ?></label>
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
                                    <?= Security::e($focusLabel ?? ($isSo ? $r['name_so'] : $r['name_en'])) ?>
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
                        <div class="adm-input-row">
                            <input type="text" name="password" id="userPasswordInput" class="form-control adm-mono" placeholder="<?= $isSo ? 'Si toos ah ayaa loo dhalinayaa (tusaale: ali8788@!)' : 'Auto-generated (e.g. ali8788@!)' ?>">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnGenPassword">
                                <?= Icons::svg('refresh', 14) ?> <?= $isSo ? 'Dhal Fure Cusub' : 'Regenerate' ?>
                            </button>
                        </div>
                        <small class="adm-hint">
                            <?= $isSo
                                ? 'Qof kasta waxaa loo dhalinayaa fure u gaar ah (sida <code>ali8788@!</code>). Furahan iyo macluumaadka akoonka waxaa loogu diri doonaa email-kiisa.'
                                : 'Each user gets a unique generated password (e.g. <code>ali8788@!</code>), delivered to their email address with login instructions.' ?>
                        </small>
                    </div>
                </div>
                <div class="adm-form-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <?= Icons::svg('user-plus', 14) ?> <span><?= $isSo ? 'Diiwaangeli & U Dir Email-ka' : 'Create User & Send Welcome Email' ?></span>
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
                if (btnText) btnText.textContent = isOpening ? '<?= $isSo ? 'Qari Foomka' : 'Hide Form' ?>' : <?= json_encode($newUserLabel) ?>;
                if (btnIcon) btnIcon.innerHTML = isOpening ? '<?= Icons::svg('x', 14) ?>' : '<?= Icons::svg('plus', 14) ?>';
                if (isOpening) {
                    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Opened from a sidebar "Register ..." link: show the registration form straight away.
                if (new URLSearchParams(window.location.search).get('new') === '1') {
                    toggleCreateUserForm(true);
                    const first = document.getElementById('userFullNameInput');
                    if (first) setTimeout(function () { first.focus(); }, 250);
                }
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
                                if (branchLabel) branchLabel.innerHTML = '<?= $isSo ? 'Qoondaynta Laanta * (Waa Qasab)' : 'Branch Assignment * (Required)' ?>';
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


        <!-- MODERN USERS TOOLBAR & STATS -->
        <div class="adm-toolbar">
            <div class="adm-segment" role="group" aria-label="<?= $isSo ? 'Xaaladda' : 'Status' ?>">
                <button type="button" class="staff-filter-pill is-active" data-filter="all" onclick="setStaffFilter('all', this)"><?= $isSo ? 'Dhammaan' : 'All' ?></button>
                <button type="button" class="staff-filter-pill" data-filter="active" onclick="setStaffFilter('active', this)"><?= $isSo ? 'Firfircoon' : 'Active' ?></button>
                <button type="button" class="staff-filter-pill" data-filter="inactive" onclick="setStaffFilter('inactive', this)"><?= $isSo ? 'Dansan' : 'Inactive' ?></button>
            </div>

            <?php if ($roleFocus === null): ?>
            <select id="staffRoleFilter" class="form-control adm-select" onchange="filterStaffTable()" aria-label="<?= $isSo ? 'Doorka' : 'Role' ?>">
                <option value=""><?= $isSo ? 'Dhammaan Doorarka' : 'All Roles' ?></option>
                <?php foreach ($allRoles as $r): ?>
                    <option value="<?= Security::e($r['slug']) ?>"><?= Security::e($isSo ? $r['name_so'] : $r['name_en']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <div class="adm-search">
                <?= Icons::svg('search', 15) ?>
                <input type="search" id="staffSearchInput" class="form-control" placeholder="<?= $isSo ? 'Raadi magac, email, door...' : 'Search name, email, role...' ?>" oninput="filterStaffTable()" autocomplete="off" aria-label="<?= $isSo ? 'Raadi' : 'Search' ?>">
            </div>
        </div>


        <!-- USERS TABLE CONTAINER (NO HORIZONTAL SCROLLBAR) -->
        <div class="adm-table-card">
            <table class="simda-staff-table" id="staffTable">
                <thead>
                    <tr>
                        <th><?= $isSo ? 'Qofka' : 'Person' ?></th>
                        <th><?= $isSo ? 'Doorka & Laanta' : 'Role & Branch' ?></th>
                        <th><?= $isSo ? 'Xiriirka' : 'Contact' ?></th>
                        <th><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                        <th class="adm-end"><span class="dash-sr"><?= $isSo ? 'Ficilada' : 'Actions' ?></span></th>
                    </tr>
                </thead>
                <tbody id="staffTableBody">
                    <?php foreach ($users as $u): ?>
                        <?php
                            $uId = (int) $u['id'];
                            $isActive = (int) ($u['is_active'] ?? 1);
                            $roleSlug = (string) ($u['role_slug'] ?? '');
                            $isPermBlocked = ((int) ($u['lockout_tier'] ?? 0) >= 5) || (!$isActive && !empty($u['security_block_reason']));
                            $isTempLocked = !empty($u['locked_until']) && strtotime((string) $u['locked_until']) > time();
                            $isSelf = ($uId === (int) ($curUser['id'] ?? 0));

                            $words = preg_split('/\s+/', trim((string) $u['full_name']));
                            $initials = '';
                            if (!empty($words[0])) $initials .= mb_substr($words[0], 0, 1);
                            if (!empty($words[1])) $initials .= mb_substr($words[1], 0, 1);
                            if ($initials === '') $initials = mb_substr((string) $u['username'], 0, 2);
                            $initials = mb_strtoupper($initials);

                            $searchMeta = mb_strtolower($u['full_name'] . ' ' . $u['username'] . ' ' . $u['email'] . ' ' . $u['phone'] . ' ' . $roleSlug . ' ' . ($u['role_name_so'] ?? '') . ' ' . ($u['role_name_en'] ?? '') . ' ' . ($u['branch_name'] ?? ''));

                            $roleTone = match ($roleSlug) {
                                'admin' => 'danger',
                                'manager' => 'gold',
                                'branch_manager' => 'info',
                                'loan_officer' => 'green',
                                default => 'muted',
                            };

                            $canEditThisUser = false;
                            if ($curRole === 'admin') {
                                $canEditThisUser = true;
                            } elseif ($curRole === 'manager') {
                                $canEditThisUser = ($roleSlug !== 'admin' && ($roleSlug !== 'manager' || $isSelf));
                            } elseif ($curRole === 'branch_manager') {
                                $canEditThisUser = ($isSelf || in_array($roleSlug, ['loan_officer', 'client'], true));
                            }
                        ?>
                        <tr class="staff-row <?= $isActive ? 'is-active' : 'is-deactivated' ?>"
                            id="userRow_<?= $uId ?>"
                            data-uid="<?= $uId ?>"
                            data-active="<?= $isActive ? '1' : '0' ?>"
                            data-role="<?= Security::e($roleSlug) ?>"
                            data-search="<?= Security::e($searchMeta) ?>">

                            <td class="adm-cell-person">
                                <div class="adm-person">
                                    <span class="adm-avatar dash-tone-<?= $roleTone ?>"><?= Security::e($initials) ?></span>
                                    <div class="adm-person-text">
                                        <span class="adm-primary">
                                            <?= Security::e($u['full_name']) ?>
                                            <?php if ($isSelf): ?>
                                                <span class="adm-you"><?= $isSo ? 'Adiga' : 'You' ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="adm-secondary">@<?= Security::e($u['username']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="adm-role dash-tone-<?= $roleTone ?>"><?= Security::e($isSo ? ($u['role_name_so'] ?? $roleSlug) : ($u['role_name_en'] ?? $roleSlug)) ?></span>
                                <span class="adm-secondary adm-with-icon">
                                    <?php if ($roleSlug === 'manager'): ?>
                                        <?= Icons::svg('globe', 12) ?> <?= $isSo ? 'Dhammaan Laamaha' : 'All Branches' ?>
                                    <?php elseif (!empty($u['branch_name'])): ?>
                                        <?= Icons::svg('building', 12) ?> <?= Security::e($u['branch_name']) ?>
                                    <?php else: ?>
                                        <?= Icons::svg('building', 12) ?> <?= $isSo ? 'Xarunta Guud' : 'Head Office' ?>
                                    <?php endif; ?>
                                </span>
                            </td>

                            <td class="adm-cell-contact">
                                <a href="mailto:<?= Security::e($u['email']) ?>" class="adm-email" title="<?= Security::e($u['email']) ?>"><?= Security::e($u['email']) ?></a>
                                <?php if (!empty($u['phone'])): ?>
                                    <span class="adm-secondary num-tabular" dir="ltr"><?= Security::e($u['phone']) ?></span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($isPermBlocked): ?>
                                    <span class="adm-status is-danger" title="<?= Security::e($u['security_block_reason'] ?? '') ?>"><?= $isSo ? 'Xaniban' : 'Blocked' ?></span>
                                <?php elseif ($isTempLocked): ?>
                                    <?php $remMin = (int) ceil((strtotime((string) $u['locked_until']) - time()) / 60); ?>
                                    <span class="adm-status is-warn"><?= $isSo ? "Hakad ({$remMin} daq)" : "Locked ({$remMin}m)" ?></span>
                                <?php elseif (!$isActive): ?>
                                    <span class="adm-status is-off"><?= $isSo ? 'Dansan' : 'Inactive' ?></span>
                                <?php else: ?>
                                    <span class="adm-status is-on"><?= $isSo ? 'Firfircoon' : 'Active' ?></span>
                                <?php endif; ?>
                                <span class="adm-secondary num-tabular">
                                    <?= !empty($u['last_login_at'])
                                        ? Security::e(date('d/m/Y H:i', strtotime((string) $u['last_login_at'])))
                                        : ($isSo ? 'Weli ma gelin' : 'Never signed in') ?>
                                </span>
                            </td>

                            <td class="adm-end adm-cell-actions">
                                <div class="adm-actions">
                                    <?php if ($isSelf && $curRole === 'admin'): ?>
                                        <a href="?route=profile" class="adm-icon-btn" title="Profile" aria-label="Profile">
                                            <?= Icons::svg('edit', 15) ?>
                                        </a>
                                    <?php elseif ($canEditThisUser): ?>
                                        <button type="button"
                                            class="adm-icon-btn"
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
                                            title="<?= $isSo ? 'Wax ka beddel' : 'Edit' ?>"
                                            aria-label="<?= $isSo ? 'Wax ka beddel' : 'Edit' ?> <?= Security::e($u['full_name']) ?>">
                                            <?= Icons::svg('edit', 15) ?>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($canEditThisUser && !empty($u['email'])): ?>
                                        <button type="button"
                                            class="adm-icon-btn"
                                            onclick="openResendCredsModal(<?= htmlspecialchars(json_encode([
                                                'id' => $uId,
                                                'full_name' => $u['full_name'],
                                                'username' => $u['username'],
                                                'email' => $u['email'],
                                            ]), ENT_QUOTES, 'UTF-8') ?>)"
                                            title="<?= $isSo ? 'U dir macluumaadka gelitaanka email-kiisa (Inbox)' : 'Send login credentials email (Inbox)' ?>"
                                            aria-label="<?= $isSo ? 'U dir email' : 'Send email' ?> <?= Security::e($u['full_name']) ?>"
                                            style="color:var(--brand-primary,#1B7A53);">
                                            <?= Icons::svg('mail', 15) ?>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($curRole === 'admin' && $roleSlug !== 'admin'): ?>
                                        <a href="?route=permissions&user_id=<?= $uId ?>" class="adm-icon-btn" title="<?= $isSo ? 'Awoodaha' : 'Permissions' ?>" aria-label="<?= $isSo ? 'Awoodaha' : 'Permissions' ?> <?= Security::e($u['full_name']) ?>">
                                            <?= Icons::svg('shield', 15) ?>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (($isPermBlocked || $isTempLocked) && $curRole === 'admin'): ?>
                                        <form method="POST" action="?route=user_security_reset" class="adm-inline-form" data-confirm-text="<?= Security::e($isSo ? 'Ma hubtaa inaad xanibaadda amniga ka qaaddo ' . $u['full_name'] . '?' : 'Restore access for ' . $u['full_name'] . '?') ?>" onsubmit="return confirm(this.dataset.confirmText);">
                                            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                            <?= $returnField ?>
                                            <input type="hidden" name="user_id" value="<?= $uId ?>">
                                            <button type="submit" class="adm-icon-btn is-warn" title="<?= $isSo ? 'Ka qaad xanibaadda' : 'Unlock' ?>" aria-label="<?= $isSo ? 'Ka qaad xanibaadda' : 'Unlock' ?> <?= Security::e($u['full_name']) ?>">
                                                <?= Icons::svg('lock', 15) ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (!$isSelf): ?>
                                        <?php if ($isActive): ?>
                                            <form method="POST" action="?route=user_toggle_status" class="adm-inline-form" data-confirm-text="<?= Security::e($isSo ? 'Ma hubtaa inaad damiso akoonka ' . $u['full_name'] . '? Gelitaankiisa nidaamka waa la hakinayaa.' : 'Deactivate ' . $u['full_name'] . '? Their active session will end.') ?>" onsubmit="return confirm(this.dataset.confirmText);">
                                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                                <?= $returnField ?>
                                                <input type="hidden" name="user_id" value="<?= $uId ?>">
                                                <button type="submit" class="adm-icon-btn is-danger" title="<?= $isSo ? 'Demi akoonka' : 'Deactivate' ?>" aria-label="<?= $isSo ? 'Demi akoonka' : 'Deactivate' ?> <?= Security::e($u['full_name']) ?>">
                                                    <?= Icons::svg('power', 15) ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="?route=user_toggle_status" class="adm-inline-form" data-confirm-text="<?= Security::e($isSo ? 'Ma hubtaa inaad dib u hawlgeliso akoonka ' . $u['full_name'] . '?' : 'Activate ' . $u['full_name'] . '?') ?>" onsubmit="return confirm(this.dataset.confirmText);">
                                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                                <?= $returnField ?>
                                                <input type="hidden" name="user_id" value="<?= $uId ?>">
                                                <button type="submit" class="adm-icon-btn is-success" title="<?= $isSo ? 'Hawlgeli akoonka' : 'Activate' ?>" aria-label="<?= $isSo ? 'Hawlgeli akoonka' : 'Activate' ?> <?= Security::e($u['full_name']) ?>">
                                                    <?= Icons::svg('check-circle', 15) ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr id="staffNoResultsRow" <?= empty($users) ? '' : 'style="display:none;"' ?>>
                        <td colspan="5">
                            <div class="dash-empty">
                                <span class="dash-empty-icon"><?= Icons::svg('search', 20) ?></span>
                                <strong><?= $isSo ? 'Qofna lama helin' : 'No matching users' ?></strong>
                                <p><?= $isSo ? 'Hubi xarfaha aad qortay ama dooro shaandhayn kale.' : 'Check your search or choose another filter.' ?></p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
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
                    <button type="button" class="adm-icon-btn" onclick="closeEditUserModal()" aria-label="<?= $isSo ? 'Xir' : 'Close' ?>"><?= Icons::svg('x', 16) ?></button>
                </div>

                <!-- Modal Body Form -->
                <form method="POST" action="?route=user_update" id="editUserForm" class="adm-modal-form">
                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                    <input type="hidden" name="user_id" id="modalUserId" value="">
                    <?= $returnField ?>

                    <div class="form-grid adm-modal-grid">
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
                                        <?= Security::e($isSo ? $r['name_so'] : $r['name_en']) ?>
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

                        <!-- Send Credentials Email Checkbox -->
                        <div class="form-group" style="grid-column:1 / -1;background:rgba(27,122,83,0.06);padding:12px 14px;border-radius:10px;border:1px solid rgba(27,122,83,0.25);">
                            <label style="display:flex;align-items:center;gap:10px;margin:0;cursor:pointer;">
                                <input type="checkbox" name="send_credentials_email" id="modalSendCredsCheck" value="1" checked style="width:17px;height:17px;accent-color:#1B7A53;cursor:pointer;">
                                <span style="font-size:0.85rem;font-weight:700;color:var(--text-main);">
                                    <?= Icons::svg('mail', 14) ?> <?= $isSo ? 'U dir furaha cusub iyo macluumaadka gelitaanka email-ka qofkan' : 'Email new password and login credentials to this user' ?>
                                </span>
                            </label>
                            <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;margin-left:27px;">
                                <?= $isSo ? 'Fariintu waxay toos u galaysaa Inbox-ka email-ka kor ku qoran.' : 'Delivers credentials directly to the email specified above.' ?>
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

        <!-- RESEND CREDENTIALS MODAL DIALOG -->
        <div id="resendCredsModalOverlay" class="simda-modal-overlay" onclick="handleResendModalBackdrop(event)">
            <div class="simda-modal-box" role="dialog" aria-modal="true" aria-labelledby="resendModalTitle" style="max-width:480px;">
                <div class="adm-modal-head" style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border-color);padding-bottom:12px;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="background:rgba(27,122,83,0.12);color:#1B7A53;padding:8px;border-radius:10px;display:inline-flex;">
                            <?= Icons::svg('mail', 18) ?>
                        </span>
                        <div>
                            <h3 id="resendModalTitle" style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);">
                                <?= $isSo ? 'U Dir Macluumaadka Gelitaanka' : 'Send Login Credentials' ?>
                            </h3>
                            <div style="font-size:0.78rem;color:var(--text-muted);">
                                <?= $isSo ? 'Toos ugu dir sanduuqa Inbox-ka shaqaalaha' : 'Delivers directly to the recipient\'s Inbox' ?>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="adm-icon-btn" onclick="closeResendCredsModal()" aria-label="<?= $isSo ? 'Xir' : 'Close' ?>"><?= Icons::svg('x', 16) ?></button>
                </div>

                <form method="POST" action="?route=user_resend_credentials" class="adm-modal-form">
                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                    <input type="hidden" name="user_id" id="resendUserId" value="">
                    <?= $returnField ?>

                    <div style="background:var(--bg-surface);padding:14px;border-radius:10px;border:1px solid var(--border-color);margin-bottom:14px;">
                        <div style="font-size:0.88rem;font-weight:700;color:var(--text-main);" id="resendFullName"></div>
                        <div style="font-size:0.82rem;color:var(--brand-primary,#1B7A53);font-weight:600;margin-top:2px;" id="resendEmail"></div>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-weight:700;font-size:0.84rem;margin-bottom:6px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Furaha Sirta ah ee Loo Dirayo (Password) *' : 'Password to Deliver *' ?>
                        </label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="text" name="custom_password" id="resendPasswordInput" class="form-control" required style="font-family:monospace;font-weight:800;letter-spacing:1px;font-size:0.92rem;">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateResendPassword()" style="white-space:nowrap;font-weight:700;">
                                <?= Icons::svg('refresh', 13) ?> <?= $isSo ? 'Fure Cusub' : 'Generate' ?>
                            </button>
                        </div>
                        <div style="font-size:0.76rem;color:var(--text-muted);margin-top:5px;">
                            <?= $isSo ? 'Furahan waxaa lagu keydin doonaa nidaamka, waxaana si toos ah loogu diri doonaa email-kiisa.' : 'This password will be updated and delivered via secure email.' ?>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;align-items:center;padding-top:14px;border-top:1px solid var(--border-color);">
                        <button type="button" class="btn btn-outline btn-sm" onclick="closeResendCredsModal()" style="font-weight:700;padding:8px 18px;">
                            <?= $isSo ? 'Ka noqo' : 'Cancel' ?>
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm" style="font-weight:800;padding:8px 22px;display:inline-flex;align-items:center;gap:6px;background:#1B7A53;border-color:#1B7A53;">
                            <?= Icons::svg('send', 14) ?> <?= $isSo ? 'Dir Hadda (Send to Inbox)' : 'Send Now' ?>
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

        var resendCurrentUserName = '';
        function openResendCredsModal(u) {
            resendCurrentUserName = u.full_name || '';
            document.getElementById('resendUserId').value = u.id;
            document.getElementById('resendFullName').textContent = u.full_name + ' (@' + (u.username || '') + ')';
            document.getElementById('resendEmail').textContent = '📧 ' + u.email;
            generateResendPassword(resendCurrentUserName);
            var overlay = document.getElementById('resendCredsModalOverlay');
            if (overlay) {
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeResendCredsModal() {
            var overlay = document.getElementById('resendCredsModalOverlay');
            if (overlay) {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        function handleResendModalBackdrop(event) {
            if (event.target === document.getElementById('resendCredsModalOverlay')) {
                closeResendCredsModal();
            }
        }

        function generateResendPassword(name) {
            var src = name || resendCurrentUserName || 'simda';
            var clean = src.trim().split(/\s+/)[0].toLowerCase().replace(/[^a-z0-9]/g, '');
            if (clean.length < 2) clean = 'simda';
            var digits = Math.floor(1000 + Math.random() * 9000);
            var input = document.getElementById('resendPasswordInput');
            if (input) input.value = clean + digits + '@!';
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeEditUserModal();
                closeResendCredsModal();
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
        <div class="adm-note">
            <?= Icons::svg('info', 16) ?>
            <div>
                <?= $isSo
                    ? 'Dooro awoodaha uu qof kasta leeyahay. Waxyaabaha aad ka saarto calaamadda waa laga qarinayaa menu-ka qofkaas.'
                    : 'Choose the permissions for each person. Unchecked items are hidden from their sidebar menu.' ?>
            </div>
        </div>

        <!-- SELECT USER DROPDOWN / TABS -->
        <div style="background:var(--bg-elevated);padding:16px 20px;border-radius:12px;border:1px solid var(--border-color);margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <label style="font-weight:700;font-size:0.88rem;color:var(--text-main);margin:0;"><?= $isSo ? 'Dooro Qofka aad rabto inaad awoodaha u habayso:' : 'Select Staff Member to Configure:' ?></label>
                    <select class="form-control" style="font-weight:700;min-width:min(300px,100%);max-width:100%;" onchange="window.location.href='?route=permissions&user_id=' + this.value;">
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
                            <input type="search" id="permSearchInput" class="form-control" placeholder="<?= $isSo ? 'Raadi awoodda ama magaceeda...' : 'Search permission name or slug...' ?>" oninput="filterPermTable()" style="font-size:0.84rem;padding-left:14px;border-radius:8px;">
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
                                    <th><?= $isSo ? 'Awoodda & Faahfaahinta' : 'Permission & Description' ?></th>
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
                                        <td colspan="3">
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

                                            <td>
                                                <div style="font-weight:700;font-size:0.86rem;color:var(--text-main);margin-bottom:3px;">
                                                    <?= Security::e($perm['description']) ?>
                                                </div>
                                                <div>
                                                    <span class="simda-perm-code"><?= Security::e($perm['slug']) ?></span>
                                                </div>
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
                                    <td colspan="3" style="text-align:center;padding:36px 16px;color:var(--text-muted);">
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
        <?php
        $settingMeta = [
            'charity_late_fee_amount' => ['group' => 'rules', 'so' => 'Ganaaxa sadaqada ($)', 'en' => 'Charity late fee ($)', 'hint_so' => 'Waxaa la saaraa qaybta dib u dhacda, wuxuuna galaa Sanduuqa Sadaqada.', 'hint_en' => 'Charged on late installments and paid into the Charity Account.'],
            'approval_officer_threshold' => ['group' => 'rules', 'env' => 'APPROVAL_OFFICER_LIMIT', 'so' => 'Xadka ansixinta Header Office ($)', 'en' => 'Header Office approval limit ($)'],
            'approval_branch_threshold' => ['group' => 'rules', 'env' => 'APPROVAL_BRANCH_LIMIT', 'so' => 'Xadka ansixinta Branch Manager ($)', 'en' => 'Branch Manager approval limit ($)'],
            'late_charge_grace_days' => ['group' => 'rules', 'env' => 'LATE_CHARGE_GRACE_DAYS', 'so' => 'Maalmaha sugitaanka ganaaxa', 'en' => 'Late charge grace days'],
            'disbursement_expiry_days' => ['group' => 'rules', 'env' => 'DISBURSEMENT_EXPIRY_DAYS', 'so' => 'Maalmaha uu dhacayo ansixinta', 'en' => 'Approval expiry days'],
            'institution_phone' => ['group' => 'contact', 'so' => 'Telefoonka hay\'adda', 'en' => 'Institution phone'],
            'institution_email' => ['group' => 'contact', 'so' => 'Email-ka hay\'adda', 'en' => 'Institution email'],
            'institution_address' => ['group' => 'contact', 'so' => 'Cinwaanka xarunta', 'en' => 'Headquarters address'],
        ];
        $settingGroups = [
            'rules' => ['icon' => 'layers', 'title' => $isSo ? 'Xeerarka Maalgelinta' : 'Financing Rules', 'items' => []],
            'contact' => ['icon' => 'phone', 'title' => $isSo ? 'Xiriirka Hay\'adda' : 'Institution Contact', 'items' => []],
            'other' => ['icon' => 'settings', 'title' => $isSo ? 'Kuwa Kale' : 'Other', 'items' => []],
        ];
        foreach ($settingsList as $s) {
            $settingGroups[$settingMeta[$s['setting_key']]['group'] ?? 'other']['items'][] = $s;
        }
        ?>
        <form method="POST" action="?route=settings_save" class="adm-settings">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
            <?php foreach ($settingGroups as $group): ?>
                <?php if (empty($group['items'])) continue; ?>
                <section class="adm-table-card adm-settings-group">
                    <h2><?= Icons::svg($group['icon'], 16) ?> <?= Security::e($group['title']) ?></h2>
                    <?php foreach ($group['items'] as $s):
                        $meta = $settingMeta[$s['setting_key']] ?? [];
                        $fieldId = 'setting_' . preg_replace('/\W/', '_', (string) $s['setting_key']);
                        ?>
                        <div class="adm-setting">
                            <div class="adm-setting-text">
                                <label for="<?= Security::e($fieldId) ?>"><?= Security::e($isSo ? ($meta['so'] ?? $s['setting_key']) : ($meta['en'] ?? $s['setting_key'])) ?></label>
                                <?php if (!empty($meta['env'])): ?>
                                    <small class="adm-setting-warn"><?= Icons::svg('alert-triangle', 12) ?> <?= $isSo ? 'Hadda nidaamku wuxuu qiimahan ka akhriyaa <code>.env</code> (' . Security::e($meta['env']) . '), halkan beddelkiisu saameyn ma leh.' : 'The system currently reads this from <code>.env</code> (' . Security::e($meta['env']) . '), so changing it here has no effect.' ?></small>
                                <?php elseif (!empty($meta['hint_so'])): ?>
                                    <small><?= Security::e($isSo ? $meta['hint_so'] : $meta['hint_en']) ?></small>
                                <?php elseif (empty($meta)): ?>
                                    <small><?= Security::e($s['description'] ?? '') ?></small>
                                <?php endif; ?>
                            </div>
                            <input type="text" id="<?= Security::e($fieldId) ?>" name="settings[<?= Security::e($s['setting_key']) ?>]" class="form-control num-tabular" value="<?= Security::e($s['setting_value']) ?>">
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>
            <div class="adm-form-actions">
                <button type="submit" class="btn btn-primary btn-sm"><?= Icons::svg('check-circle', 14) ?> <span><?= $isSo ? 'Keydi Settings-ka' : 'Save Settings' ?></span></button>
            </div>
        </form>

        <!-- EMAIL CONFIGURATION & INBOX DELIVERY TEST CARD -->
        <section class="adm-table-card adm-settings-group" style="margin-top:24px;">
            <h2><?= Icons::svg('mail', 16) ?> <?= $isSo ? 'Tijaabada & Hubinta Email-ka (Direct-to-Inbox Test)' : 'Email Inbox Delivery Test' ?></h2>
            <p style="font-size:0.84rem;color:var(--text-muted);margin-bottom:16px;">
                <?= $isSo ? 'Adeegga SMTP waxaa loo habeeyay inuu fariimaha si toos ah u geeyo Inbox-ka (oo uusan marnaba gelin Spam). Halkan ku tijaabi adigoo email kasta u diraya tijaabo degdeg ah.' : 'The SMTP service is optimized to deliver directly to Inbox without spam flags. Test any email address below.' ?>
            </p>
            <form method="POST" action="?route=mail_test_send" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <input type="email" name="test_email" class="form-control" placeholder="Tusaale: magacaa@gmail.com" required style="max-width:320px;font-weight:600;">
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;background:#1B7A53;border-color:#1B7A53;">
                    <?= Icons::svg('send', 14) ?> <span><?= $isSo ? 'Dir Test Email' : 'Send Test Email' ?></span>
                </button>
            </form>
        </section>

    <!-- ========================================================================= -->
    <!-- 4. AUDIT LOGS VIEW                                                        -->
    <!-- ========================================================================= -->
    <?php else: ?>
        <?php if (empty($auditLogs)): ?>
            <div class="adm-table-card">
                <div class="dash-empty">
                    <span class="dash-empty-icon"><?= Icons::svg('activity', 20) ?></span>
                    <strong><?= $isSo ? 'Weli dhacdo ma jirto' : 'No events yet' ?></strong>
                </div>
            </div>
        <?php else: ?>
            <div class="adm-toolbar">
                <div class="adm-search">
                    <?= Icons::svg('search', 15) ?>
                    <input type="search" id="auditSearchInput" class="form-control" placeholder="<?= $isSo ? 'Raadi qof, ficil ama sharaxaad...' : 'Search person, action or description...' ?>" autocomplete="off" aria-label="<?= $isSo ? 'Raadi' : 'Search' ?>" oninput="var q=this.value.toLowerCase().trim();document.querySelectorAll('tr.audit-row').forEach(function(r){r.style.display=!q||r.textContent.toLowerCase().indexOf(q)!==-1?'':'none';});">
                </div>
            </div>
            <div class="adm-table-card">
                <div class="table-responsive adm-scroll">
                    <table class="simda-table adm-table adm-audit">
                        <thead>
                            <tr>
                                <th><?= $isSo ? 'Waqtiga' : 'Time' ?></th>
                                <th><?= $isSo ? 'Qofka' : 'Person' ?></th>
                                <th><?= $isSo ? 'Ficilka' : 'Action' ?></th>
                                <th><?= $isSo ? 'Sharaxaad' : 'Description' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditLogs as $log):
                                $logTs = strtotime((string) $log['created_at']);
                                $logAction = (string) $log['action'];
                                $logIsBad = (bool) preg_match('/failed|block|lockout|kick/', $logAction);
                                ?>
                                <tr class="audit-row">
                                    <td class="adm-nowrap">
                                        <span class="adm-primary num-tabular"><?= Security::e($logTs ? date('d/m/Y', $logTs) : (string) $log['created_at']) ?></span>
                                        <span class="adm-secondary num-tabular"><?= Security::e($logTs ? date('H:i:s', $logTs) : '') ?></span>
                                    </td>
                                    <td>
                                        <span class="adm-primary"><?= Security::e($log['user_name'] ?? ($isSo ? 'Nidaamka' : 'System')) ?></span>
                                        <?php if (!empty($log['ip_address'])): ?>
                                            <span class="adm-secondary num-tabular" dir="ltr"><?= Security::e($log['ip_address']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="adm-code <?= $logIsBad ? 'is-danger' : '' ?>"><?= Security::e($logAction) ?></span>
                                        <span class="adm-secondary num-tabular"><?= Security::e($log['entity_type']) ?><?= !empty($log['entity_id']) ? ' #' . (int) $log['entity_id'] : '' ?></span>
                                    </td>
                                    <td class="adm-audit-desc"><?= Security::e($log['description']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
