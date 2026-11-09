<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$user = AuthMiddleware::user();
$role = $user['role_slug'] ?? '';
$currentRoute = (string) ($_GET['route'] ?? 'dashboard');
$csrf = Security::csrfToken();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), (int) @filemtime(BASE_PATH . '/public/assets/js/simda.js'), 1);
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" dir="<?= $locale === 'ar' ? 'rtl' : 'ltr' ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#123B32">
    <title><?= Security::e($pageTitle ?? 'SIMDA Portal') ?> — SIMDA</title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="manifest" href="manifest.json">
    <script>
        (function() {
            var p = localStorage.getItem('simda_theme_pref') || 'light';
            var t = (p === 'dark' || p === 'light') ? p : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
            if ('caches' in window) {
                caches.keys().then(function(keys) {
                    keys.forEach(function(k) {
                        if (k !== 'simda-static-shell-v5') { caches.delete(k); }
                    });
                });
            }
        })();
    </script>
    <link rel="stylesheet" href="assets/css/simda.css?v=<?= Security::e($assetVer) ?>">
    <script src="assets/js/cropper.js?v=<?= Security::e((string) @filemtime(BASE_PATH . '/public/assets/js/cropper.js')) ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="offline-banner">
    <?= Security::e(Lang::get('connection_offline')) ?>
</div>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="app-shell">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <a href="?route=dashboard" class="brand-logo-wrap">
                <span class="brand-logo-pill">
                    <img src="assets/images/simda-logo.png" alt="SIMDA Logo" class="brand-logo-full">
                    <img src="assets/images/simda-icon.png" alt="S" style="height:32px;width:auto;display:none;" class="brand-logo-compact">
                </span>
                <span class="sidebar-brand-tagline">
                    <span>SOMALI ISLAMIC MICROFINANCE</span>
                    <span>DEVELOPMENT ASSOCIATION</span>
                </span>
            </a>
        </div>

        <?php
        // Sidebar menu: top-level Dashboard plus collapsible groups (one open at a time).
        $so = $locale === 'so';
        $can = static fn(string $p): bool => AuthMiddleware::hasPermission($p);
        $filter = (string) ($_GET['filter'] ?? '');
        $isNew = ($_GET['new'] ?? '') === '1';
        $on = static fn(array $routes): bool => in_array($currentRoute, $routes, true);
        $sidebarGroups = [
            [
                'label' => $so ? 'Macaamiisha' : 'Clients', 'icon' => 'users', 'show' => $can('clients.view'),
                'items' => [
                    ['route' => 'clients', 'label' => $so ? 'Dhammaan Macaamiisha' : 'All Clients', 'active' => $on(['clients', 'client_view'])],
                    ['route' => 'client_register', 'label' => Lang::get('menu_register_client'), 'show' => $can('clients.register'), 'active' => $on(['client_register'])],
                    ['route' => 'guarantors', 'label' => Lang::get('menu_guarantors'), 'show' => $can('guarantors.manage'), 'active' => $on(['guarantors'])],
                ],
            ],
            [
                'label' => $so ? 'Dayn Lacag Ah' : 'Cash Loans', 'icon' => 'credit-card', 'show' => $can('loans.view'),
                'items' => [
                    ['route' => 'loan_new', 'label' => $so ? 'Sii Dayn Lacag Ah' : 'New Cash Loan', 'cta' => true, 'active' => $on(['loan_new'])],
                    ['route' => 'loans&filter=active', 'label' => $so ? 'Dadka Deynta Qaatay' : 'Active Borrowers', 'active' => $currentRoute === 'loans' && in_array($filter, ['', 'active'], true)],
                    ['route' => 'loans&filter=settled_ontime', 'label' => $so ? 'Bixiyay Waqtiga' : 'Settled On-Time', 'active' => $currentRoute === 'loans' && $filter === 'settled_ontime'],
                    ['route' => 'loans&filter=settled_late', 'label' => $so ? 'Bixiyay Dib u Dhac' : 'Settled Late', 'active' => $currentRoute === 'loans' && $filter === 'settled_late'],
                ],
            ],
            [
                'label' => $so ? 'Murabaxo' : 'Murabaha', 'icon' => 'shopping-bag', 'show' => $can('murabaha.view'),
                'items' => [
                    ['route' => 'murabaha_new', 'label' => $so ? 'Sii Murabaxo' : 'New Murabaha', 'cta' => true, 'active' => $on(['murabaha_new'])],
                    ['route' => 'murabaha_goods', 'label' => $so ? 'Alaabta Murabaxada' : 'Murabaha Products', 'show' => $can('products.manage'), 'active' => $on(['murabaha_goods'])],
                    ['route' => 'murabaha&filter=all', 'label' => $so ? 'Heshiisyada Murabaxada' : 'All Contracts', 'active' => $currentRoute === 'murabaha' && in_array($filter, ['', 'all'], true)],
                    ['route' => 'murabaha&filter=settled', 'label' => $so ? 'La Soo Celiyay' : 'Settled', 'active' => $currentRoute === 'murabaha' && $filter === 'settled'],
                    ['route' => 'murabaha&filter=unpaid', 'label' => $so ? 'Aan Weli La Celin' : 'Unpaid / Active', 'active' => $currentRoute === 'murabaha' && $filter === 'unpaid'],
                ],
            ],
            [
                'label' => Lang::get('grp_financing'), 'icon' => 'layers', 'show' => true,
                'items' => [
                    ['route' => 'products', 'label' => Lang::get('menu_products'), 'show' => $can('products.manage'), 'active' => $on(['products'])],
                    ['route' => 'applications', 'label' => Lang::get('menu_applications'), 'show' => $can('applications.view'), 'active' => $on(['applications'])],
                    ['route' => 'approval_queue', 'label' => Lang::get('menu_approval_queue'), 'show' => false /* shown as the top-level Requests button */, 'active' => $on(['approval_queue'])],
                    ['route' => 'financings', 'label' => Lang::get('menu_active_financing'), 'active' => $on(['financings', 'financing_view'])],
                    ['route' => 'disbursements', 'label' => Lang::get('menu_disbursements'), 'show' => $can('financings.disburse'), 'active' => $on(['disbursements'])],
                    ['route' => 'schedules', 'label' => Lang::get('menu_schedules'), 'active' => $on(['schedules'])],
                    // Client: the receipts for every payment they made (the list only shows their own).
                    ['route' => 'repayments', 'label' => $so ? 'Rasiidhada' : 'Receipts', 'show' => $role === 'client', 'active' => $on(['repayments', 'repayment_receipt'])],
                ],
            ],
            [
                'label' => Lang::get('grp_payments'), 'icon' => 'wallet', 'show' => true,
                'items' => [
                    ['route' => 'repayment_record', 'label' => Lang::get('menu_record_repayment'), 'show' => $can('repayments.record'), 'active' => $on(['repayment_record'])],
                    ['route' => 'repayments', 'label' => Lang::get('menu_repayment_history'), 'show' => $can('repayments.view'), 'active' => $on(['repayments'])],
                    ['route' => 'charity_charges', 'label' => Lang::get('menu_charity_charges'), 'show' => $can('charity.view'), 'active' => $on(['charity_charges'])],
                ],
            ],
            [
                'label' => Lang::get('grp_risk'), 'icon' => 'alert-triangle', 'show' => $can('risk.view'),
                'items' => [
                    ['route' => 'credit_scores', 'label' => Lang::get('menu_credit_scores'), 'active' => $on(['credit_scores'])],
                    ['route' => 'par30', 'label' => Lang::get('menu_par30'), 'active' => $on(['par30'])],
                ],
            ],
            [
                'label' => Lang::get('grp_communication'), 'icon' => 'message-square', 'show' => $can('communication.sms'),
                'items' => [
                    ['route' => 'sms_outbox', 'label' => Lang::get('menu_sms_outbox'), 'active' => $on(['sms_outbox'])],
                ],
            ],
            [
                'label' => Lang::get('grp_reports'), 'icon' => 'bar-chart', 'show' => true,
                'items' => [
                    ['route' => 'statements', 'label' => Lang::get('menu_client_statements'), 'show' => $can('statements.generate'), 'active' => $on(['statements'])],
                    ['route' => 'reports', 'label' => Lang::get('menu_financial_reports'), 'show' => $can('reports.view'), 'active' => $on(['reports'])],
                ],
            ],
            [
                // Staff lists. Registering is done with the two buttons under Dashboard.
                'label' => $so ? 'Shaqaalaha' : 'Staff', 'icon' => 'user-check', 'show' => $can('users.manage'),
                'items' => [
                    // Admin: every account in one list, with edit and activate/deactivate.
                    ['route' => 'users', 'label' => $so ? 'Dhammaan Isticmaalayaasha' : 'All Users', 'show' => $role === 'admin', 'active' => $on(['users']) && !$isNew],
                    ['route' => 'managers', 'label' => $so ? 'Maamulayaasha Guud' : 'General Managers', 'show' => $role === 'admin', 'active' => $on(['managers'])],
                    ['route' => 'branch_managers', 'label' => $so ? 'Maamulayaasha Laamaha' : 'Branch Managers', 'show' => in_array($role, ['admin', 'manager'], true), 'active' => $on(['branch_managers'])],
                    ['route' => 'employees', 'label' => $so ? 'Liiska Shaqaalaha' : 'All Employees', 'active' => $on(['employees', 'employee_register']) && !$isNew],
                ],
            ],
            [
                'label' => Lang::get('grp_admin'), 'icon' => 'settings', 'show' => $can('branches.manage') || $can('users.manage'),
                'items' => [
                    ['route' => 'users', 'label' => Lang::get('menu_users'), 'show' => $can('users.manage') && $role !== 'admin' /* admin has All Users under Staff */, 'active' => $on(['users'])],
                    ['route' => 'branches', 'label' => Lang::get('menu_branches'), 'show' => $can('branches.manage'), 'active' => $on(['branches'])],
                    ['route' => 'permissions', 'label' => $so ? 'Awoodaha' : 'Permissions', 'show' => $role === 'admin', 'active' => $on(['permissions'])],
                    ['route' => 'audit_logs', 'label' => $so ? 'Diiwaanka Dhaqdhaqaaqa' : 'Audit Logs', 'show' => $role === 'admin', 'active' => $on(['audit_logs'])],
                    ['route' => 'partners', 'label' => $so ? 'La-hawlgalayaasha (Partners)' : 'Partners', 'show' => $role === 'admin', 'active' => $on(['partners'])],
                    ['route' => 'settings', 'label' => $so ? 'Dejinta' : 'Settings', 'show' => $role === 'admin', 'active' => $on(['settings'])],
                ],
            ],
        ];
        ?>
        <nav class="sidebar-nav" id="sidebarNav">
            <a href="?route=dashboard" class="sidebar-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
                <?= Icons::svg('dashboard', 18) ?>
                <span class="sidebar-text"><?= Security::e(Lang::get('menu_dashboard')) ?></span>
            </a>

            <?php if (in_array($role, ['admin', 'manager', 'branch_manager', 'loan_officer'], true)): ?>
                <?php
                // Requests waiting for this person's decision (same rules as the Requests page).
                try {
                    $pendingRequests = count(\App\Services\FinancingDomainService::pendingRequestsFor($user));
                } catch (\Throwable $e) {
                    $pendingRequests = 0;
                }
                ?>
                <a href="?route=approval_queue" class="sidebar-link sb-requests <?= $currentRoute === 'approval_queue' ? 'active' : '' ?> <?= $pendingRequests > 0 ? 'has-pending' : '' ?>">
                    <?= Icons::svg('check-square', 18) ?>
                    <span class="sidebar-text"><?= $so ? 'Codsiyada' : 'Requests' ?></span>
                    <?php if ($pendingRequests > 0): ?>
                        <span class="sb-count num-tabular" aria-label="<?= $pendingRequests ?> <?= $so ? 'sugaya' : 'waiting' ?>"><?= $pendingRequests ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if ($can('users.manage') && $role !== 'branch_manager'): ?>
                <?php
                // Manager button: admin starts on General Manager (Branch Manager is in the same dropdown);
                // a General Manager can only create Branch Managers.
                $mgrHref = $role === 'admin' ? 'users&new=1&role=manager' : 'branch_managers&new=1';
                $mgrActive = $isNew && (($currentRoute === 'users' && ($_GET['role'] ?? '') === 'manager') || $currentRoute === 'branch_managers');
                ?>
                <div class="sb-register">
                    <a href="?route=<?= Security::e($mgrHref) ?>" class="sb-register-btn <?= $mgrActive ? 'active' : '' ?>">
                        <?= Icons::svg('briefcase', 16) ?>
                        <span><?= $so ? 'Manager' : 'Manager' ?></span>
                        <?= Icons::svg('plus', 13) ?>
                    </a>
                    <a href="?route=employees&new=1" class="sb-register-btn <?= $isNew && $currentRoute === 'employees' ? 'active' : '' ?>">
                        <?= Icons::svg('user-plus', 16) ?>
                        <span><?= $so ? 'Shaqaale' : 'Employee' ?></span>
                        <?= Icons::svg('plus', 13) ?>
                    </a>
                    <?php if (in_array($role, ['admin', 'manager'], true)): ?>
                        <a href="?route=branches&new=1" class="sb-register-btn sb-register-full <?= $isNew && $currentRoute === 'branches' ? 'active' : '' ?>">
                            <?= Icons::svg('building', 16) ?>
                            <span><?= $so ? 'Laan Cusub (Branch)' : 'New Branch' ?></span>
                            <?= Icons::svg('plus', 13) ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php elseif ($can('users.manage')): ?>
                <div class="sb-register">
                    <a href="?route=employees&new=1" class="sb-register-btn sb-register-full <?= $isNew && $currentRoute === 'employees' ? 'active' : '' ?>">
                        <?= Icons::svg('user-plus', 16) ?>
                        <span><?= $so ? 'Diiwaangeli Shaqaale' : 'Register Employee' ?></span>
                        <?= Icons::svg('plus', 13) ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php foreach ($sidebarGroups as $gi => $group): ?>
                <?php
                if (!$group['show']) {
                    continue;
                }
                $items = array_values(array_filter($group['items'], static fn($it) => $it['show'] ?? true));
                if (!$items) {
                    continue;
                }
                $groupActive = (bool) array_filter($items, static fn($it) => $it['active']);
                $subId = 'sbGroup' . $gi;
                ?>
                <div class="sb-group <?= $groupActive ? 'is-open has-active' : '' ?>">
                    <button type="button" class="sidebar-link sb-group-toggle" aria-expanded="<?= $groupActive ? 'true' : 'false' ?>" aria-controls="<?= $subId ?>">
                        <?= Icons::svg($group['icon'], 18) ?>
                        <span class="sidebar-text"><?= Security::e(mb_strtoupper($group['label']) === $group['label'] ? mb_convert_case(mb_strtolower($group['label']), MB_CASE_TITLE) : $group['label']) ?></span>
                        <span class="sb-chevron"><?= Icons::svg('chevron-down', 16) ?></span>
                    </button>
                    <div class="sb-sub" id="<?= $subId ?>">
                        <div class="sb-sub-inner">
                            <?php foreach ($items as $it): ?>
                                <a href="?route=<?= Security::e($it['route']) ?>" class="sb-sublink <?= !empty($it['cta']) ? 'is-cta' : '' ?> <?= $it['active'] ? 'active' : '' ?>"<?= $it['active'] ? ' aria-current="page"' : '' ?>>
                                    <?php if (!empty($it['cta'])): ?>
                                        <?= Icons::svg('plus', 14) ?>
                                    <?php else: ?>
                                        <span class="sb-dot" aria-hidden="true"></span>
                                    <?php endif; ?>
                                    <span><?= Security::e($it['label']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- MAIN CONTENT SHELL -->
    <div class="app-main">
        <header class="top-header">
            <div class="header-left">
                <button type="button" id="sidebarToggleBtn" class="btn btn-sm btn-outline" aria-label="Toggle Sidebar">
                    <?= Icons::svg('menu', 18) ?>
                </button>
                <div style="min-width:0;">
                    <div style="font-weight:700;font-size:1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= Security::e($pageTitle ?? 'Dashboard') ?></div>
                    <div style="font-size:0.74rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        SIMDA / <?= Security::e($user['branch_name'] ?? 'Institution') ?>
                    </div>
                </div>
            </div>

            <div class="header-right">
                <?php if ($role !== 'client'): ?>
                <form action="" method="GET" class="global-search">
                    <input type="hidden" name="route" value="clients">
                    <?= Icons::svg('search', 15) ?>
                    <input type="search" name="q" placeholder="<?= Security::e(Lang::get('search_placeholder')) ?>" value="<?= Security::e($_GET['q'] ?? '') ?>">
                </form>
                <?php endif; ?>

                <!-- Language Switcher Dropdown (Active flag shown, tap opens dropdown menu) -->
                <?php
                $dashLanguagesList = [
                    'so' => ['label' => 'Af-Soomaali', 'code' => 'SO', 'flag' => 'assets/images/flags/flag-so.jpg'],
                    'en' => ['label' => 'English (US)', 'code' => 'EN', 'flag' => 'assets/images/flags/flag-us.jpg'],
                    'ar' => ['label' => 'العربية', 'code' => 'AR', 'flag' => 'assets/images/flags/flag-ar.jpg'],
                ];
                $dashActiveLang = $dashLanguagesList[$locale] ?? $dashLanguagesList['en'];
                ?>
                <div class="simda-lang-dropdown">
                    <button type="button" class="simda-lang-trigger" aria-haspopup="true" aria-expanded="false" onclick="event.stopPropagation(); const p = this.closest('.simda-lang-dropdown'); const open = p.classList.toggle('is-open'); this.setAttribute('aria-expanded', open);" title="Change Language / Dooro Luqadda">
                        <img src="<?= Security::e($dashActiveLang['flag']) ?>" alt="<?= Security::e($dashActiveLang['label']) ?>" class="simda-lang-flag-circle">
                        <span class="simda-lang-code"><?= Security::e($dashActiveLang['code']) ?></span>
                        <svg class="simda-lang-caret" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="1 1 5 5 9 1"></polyline>
                        </svg>
                    </button>
                    <div class="simda-lang-menu" role="menu">
                        <?php foreach ($dashLanguagesList as $code => $info): ?>
                            <a href="?route=<?= Security::e($currentRoute) ?>&lang=<?= $code ?>" class="simda-lang-item <?= $locale === $code ? 'is-active' : '' ?>" role="menuitem">
                                <img src="<?= Security::e($info['flag']) ?>" alt="<?= Security::e($info['label']) ?>" class="simda-lang-flag-circle">
                                <span class="simda-lang-name"><?= Security::e($info['label']) ?></span>
                                <?php if ($locale === $code): ?>
                                    <svg class="check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Theme Switcher: Sun & Moon Icons -->
                <div class="theme-toggle-pill" id="themeToggleGroup" title="<?= Security::e(Lang::get('theme_light')) ?> / <?= Security::e(Lang::get('theme_dark')) ?>">
                    <button type="button" class="theme-pill-btn" data-theme-val="light" aria-label="Light Mode" title="<?= Security::e(Lang::get('theme_light')) ?>">
                        <?= Icons::svg('sun', 16) ?>
                    </button>
                    <button type="button" class="theme-pill-btn" data-theme-val="dark" aria-label="Dark Mode" title="<?= Security::e(Lang::get('theme_dark')) ?>">
                        <?= Icons::svg('moon', 16) ?>
                    </button>
                </div>

                <!-- Account: profile + sign out (next to the theme switcher) -->
                <div class="hdr-account">
                    <a href="?route=<?= $role === 'client' ? 'account_password' : 'profile' ?>" class="hdr-acc-btn" title="<?= Security::e(($user['full_name'] ?? '') . ' — ' . ($user['role_name_en'] ?? '') . ' • ' . ($user['branch_code'] ?? 'HQ')) ?>" aria-label="<?= Security::e(Lang::get('menu_profile')) ?>">
                        <?= Icons::svg('user', 16) ?>
                    </a>
                    <a href="?route=logout" class="hdr-acc-btn hdr-logout" title="<?= Security::e(Lang::get('nav_logout')) ?>">
                        <?= Icons::svg('log-out', 15) ?>
                        <span><?= Security::e(Lang::get('nav_logout')) ?></span>
                    </a>
                </div>

                <a href="?route=home" class="btn btn-sm btn-outline">
                    <?= Icons::svg('home', 15) ?>
                    <span><?= Security::e(Lang::get('nav_home')) ?></span>
                </a>
            </div>
        </header>

        <main class="content-area">
            <?php if (!empty($user['must_change_password']) && $currentRoute !== 'account_password'): ?>
                <!-- Still on the default password: keep reminding until it is changed -->
                <div class="alert alert-warning pw-nag animate-in" role="alert">
                    <?= Icons::svg('alert-triangle', 18) ?>
                    <span>
                        <strong><?= $so ? 'Digniin:' : ($locale === 'ar' ? 'تنبيه:' : 'Warning:') ?></strong>
                        <?= $so
                            ? 'Weli waxaad isticmaalaysaa password-ka default-ka ah. Si dhaqso ah u beddel si akoonkaagu u ammaan noqdo.'
                            : ($locale === 'ar'
                                ? 'ما زلت تستخدم كلمة المرور الافتراضية. غيّرها بسرعة لحماية حسابك.'
                                : 'You are still using the default password. Change it quickly to keep your account safe.') ?>
                    </span>
                    <a href="?route=account_password" class="btn btn-sm pw-nag-btn"><?= Icons::svg('shield', 14) ?><span><?= $so ? 'Beddel hadda' : ($locale === 'ar' ? 'غيّرها الآن' : 'Change now') ?></span></a>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success animate-in">
                    <?= Icons::svg('check-circle', 18) ?>
                    <span><?= Security::e($_SESSION['flash_success']) ?></span>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger animate-in">
                    <?= Icons::svg('alert-triangle', 18) ?>
                    <span><?= Security::e($_SESSION['flash_error']) ?></span>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_warning'])): ?>
                <div class="alert alert-warning animate-in">
                    <?= Icons::svg('info', 18) ?>
                    <span><?= Security::e($_SESSION['flash_warning']) ?></span>
                </div>
                <?php unset($_SESSION['flash_warning']); ?>
            <?php endif; ?>

            <?php
            $hubTabs = [];
            if (in_array($currentRoute, ['clients', 'client_view', 'client_register', 'client_transfer', 'guarantors', 'groups'], true) && $role !== 'client') {
                $hubTabs = [
                    ['route' => 'clients', 'icon' => 'users', 'label' => $locale === 'so' ? 'Dhammaan Macaamiisha' : 'All Clients', 'show' => AuthMiddleware::hasPermission('clients.view')],
                    ['route' => 'client_register', 'icon' => 'user-plus', 'label' => $locale === 'so' ? '+ Diiwaangeli Macmiil' : '+ Register Client', 'show' => AuthMiddleware::hasPermission('clients.register')],
                    ['route' => 'guarantors', 'icon' => 'shield', 'label' => $locale === 'so' ? 'Damiinnada (Guarantors)' : 'Guarantors', 'show' => AuthMiddleware::hasPermission('guarantors.manage')],
                    ['route' => 'groups', 'icon' => 'users', 'label' => $locale === 'so' ? 'Kooxaha (Groups)' : 'Client Groups', 'show' => AuthMiddleware::hasPermission('groups.manage')],
                ];
            } elseif (in_array($currentRoute, ['approval_queue', 'applications', 'disbursements', 'financings', 'financing_view', 'schedules', 'products'], true) && $role !== 'client') {
                $hubTabs = [
                    ['route' => 'approval_queue', 'icon' => 'check-square', 'label' => $locale === 'so' ? 'Codsiyada Sugaya (Approval Queue)' : 'Approval Queue', 'show' => AuthMiddleware::hasPermission('applications.approve_small') || AuthMiddleware::hasPermission('applications.approve_large')],
                    ['route' => 'applications', 'icon' => 'file-text', 'label' => $locale === 'so' ? 'Codsiyada (Applications)' : 'Applications', 'show' => AuthMiddleware::hasPermission('applications.view')],
                    ['route' => 'disbursements', 'icon' => 'dollar-sign', 'label' => $locale === 'so' ? 'Bixinta Lacagta (Disbursements)' : 'Disbursements', 'show' => AuthMiddleware::hasPermission('financings.disburse')],
                    ['route' => 'financings', 'icon' => 'credit-card', 'label' => $locale === 'so' ? 'Maalgelinta Socota & Jadwalka' : 'Active Financing & Schedules', 'show' => true],
                    ['route' => 'products', 'icon' => 'layers', 'label' => $locale === 'so' ? 'Noocyada Maalgelinta (Products)' : 'Products', 'show' => AuthMiddleware::hasPermission('products.manage')],
                ];
            } elseif (in_array($currentRoute, ['repayments', 'repayment_record', 'charity_charges'], true) && $role !== 'client') {
                $hubTabs = [
                    ['route' => 'repayments', 'icon' => 'activity', 'label' => $locale === 'so' ? 'Qabo Bixin & Taariikhda (Repayments)' : 'Record & Repayment History', 'show' => AuthMiddleware::hasPermission('repayments.view') || AuthMiddleware::hasPermission('repayments.record')],
                    ['route' => 'charity_charges', 'icon' => 'heart-handshake', 'label' => $locale === 'so' ? 'Ganaaxa Sadaqada (Charity Charges)' : 'Charity Late Charges', 'show' => AuthMiddleware::hasPermission('charity.view')],
                ];
            } elseif (in_array($currentRoute, ['reports', 'par30', 'credit_scores', 'statements'], true) && $role !== 'client') {
                $hubTabs = [
                    ['route' => 'reports', 'icon' => 'bar-chart', 'label' => $locale === 'so' ? 'Warbixinta Guud & PAR 30' : 'Financial Reports & PAR 30', 'show' => AuthMiddleware::hasPermission('reports.view') || AuthMiddleware::hasPermission('risk.view')],
                    ['route' => 'credit_scores', 'icon' => 'activity', 'label' => $locale === 'so' ? 'Dhibcaha Macaamiisha (Credit Scores)' : 'Credit Scores', 'show' => AuthMiddleware::hasPermission('risk.view')],
                    ['route' => 'statements', 'icon' => 'file-text', 'label' => $locale === 'so' ? 'Caddeymaha PDF (Statements)' : 'Client Statements (PDF)', 'show' => AuthMiddleware::hasPermission('statements.generate')],
                ];
            }
            $visibleHubTabs = array_values(array_filter($hubTabs, static fn(array $t): bool => !empty($t['show'])));
            ?>
            <?php if (count($visibleHubTabs) > 1): ?>
                <nav class="adm-tabs" style="margin-bottom:16px;" aria-label="Section navigation">
                    <?php foreach ($visibleHubTabs as $ht): ?>
                        <?php
                        $isHubActive = ($currentRoute === $ht['route'])
                            || ($ht['route'] === 'clients' && in_array($currentRoute, ['client_view', 'client_transfer'], true))
                            || ($ht['route'] === 'financings' && in_array($currentRoute, ['financing_view', 'schedules'], true))
                            || ($ht['route'] === 'repayments' && $currentRoute === 'repayment_record')
                            || ($ht['route'] === 'reports' && $currentRoute === 'par30');
                        ?>
                        <a href="?route=<?= Security::e($ht['route']) ?>" class="adm-tab <?= $isHubActive ? 'is-active' : '' ?>" <?= $isHubActive ? 'aria-current="page"' : '' ?>>
                            <?= Icons::svg($ht['icon'], 15) ?>
                            <span><?= Security::e($ht['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <?= $contentHtml ?? '' ?>
        </main>
    </div>
</div>

<?php if (!empty($_SESSION['client_welcome'])): ?>
    <?php
    unset($_SESSION['client_welcome']);
    $wFirst = trim(explode(' ', (string) ($user['full_name'] ?? ''))[0] ?? '');
    $wT = static fn(string $s, string $e, string $a): string => $so ? $s : ($locale === 'ar' ? $a : $e);
    ?>
    <!-- CLIENT FIRST SIGN-IN: balloons + confetti, then a welcome card that warns about the default password -->
    <dialog class="cw-dialog" id="cwDialog" aria-labelledby="cwTitle">
        <div class="cw-party" id="cwParty" aria-hidden="true"></div>
        <div class="cw-card">
        <div class="cw-emoji" aria-hidden="true">🎉</div>
        <h2 id="cwTitle"><?= Security::e($wT('Hambalyo, ' . $wFirst . '!', 'Congratulations, ' . $wFirst . '!', 'مبروك يا ' . $wFirst . '!')) ?></h2>
        <p class="cw-sub"><?= Security::e($wT(
            'Ku soo dhawoow SIMDA! Akoonkaaga si guul leh ayaad u gashay markii ugu horreysay. Halkan waxaad ka arki kartaa daynkaaga, jadwalka bixinta iyo lacagaha aad bixisay.',
            'Welcome to SIMDA! You have signed in to your account for the first time. Here you can follow your financing, your payment schedule and what you have paid.',
            'مرحباً بك في سيمدا! لقد دخلت إلى حسابك لأول مرة. هنا يمكنك متابعة تمويلك وجدول السداد وما دفعته.'
        )) ?></p>
        <div class="cw-warn" role="alert">
            <?= Icons::svg('alert-triangle', 20) ?>
            <div>
                <strong><?= Security::e($wT('Digniin muhiim ah', 'Important warning', 'تنبيه مهم')) ?></strong>
                <span><?= Security::e($wT(
                    'Waxaad gashay password-ka default-ka ah ee qof walba la siiyo. Si dhaqso ah u beddel si qof kale uusan akoonkaaga u gelin.',
                    'You signed in with the default password that every client receives. Change it right away so nobody else can open your account.',
                    'لقد دخلت بكلمة المرور الافتراضية التي يحصل عليها كل عميل. غيّرها فوراً حتى لا يتمكن أحد غيرك من فتح حسابك.'
                )) ?></span>
            </div>
        </div>
        <div class="cw-actions">
            <a href="?route=account_password" class="btn btn-primary cw-go"><?= Icons::svg('shield', 16) ?><span><?= Security::e($wT('Beddel password-ka hadda', 'Change password now', 'غيّر كلمة المرور الآن')) ?></span></a>
            <button type="button" class="btn btn-outline cw-later" data-cw-close><?= Security::e($wT('Mar dambe', 'Later', 'لاحقاً')) ?></button>
        </div>
        </div>
    </dialog>
    <script>
    (function () {
        var party = document.getElementById('cwParty');
        var dlg = document.getElementById('cwDialog');
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var colors = ['#D6B66C', '#23845B', '#123B32', '#E8574F', '#4A90D9', '#F2A93B', '#9B6BD3'];
        function rnd(a, b) { return a + Math.random() * (b - a); }
        if (!reduce && party) {
            for (var i = 0; i < 16; i++) {
                var b = document.createElement('span');
                b.className = 'cw-balloon';
                b.style.left = rnd(2, 94) + '%';
                b.style.setProperty('--c', colors[i % colors.length]);
                b.style.setProperty('--d', rnd(5.5, 9) + 's');
                b.style.setProperty('--delay', rnd(0, 1.8) + 's');
                b.style.setProperty('--sway', rnd(-40, 40) + 'px');
                b.style.setProperty('--s', rnd(0.75, 1.15));
                party.appendChild(b);
            }
            for (var j = 0; j < 90; j++) {
                var c = document.createElement('i');
                c.className = 'cw-confetti';
                c.style.left = rnd(0, 100) + '%';
                c.style.background = colors[j % colors.length];
                c.style.setProperty('--d', rnd(2.6, 4.6) + 's');
                c.style.setProperty('--delay', rnd(0, 1.2) + 's');
                c.style.setProperty('--r', rnd(360, 1080) + 'deg');
                c.style.setProperty('--x', rnd(-80, 80) + 'px');
                party.appendChild(c);
            }
            setTimeout(function () { party.remove(); }, 11500);
        }
        if (dlg && typeof dlg.showModal === 'function') {
            setTimeout(function () { dlg.showModal(); }, reduce ? 0 : 450);
            dlg.querySelectorAll('[data-cw-close]').forEach(function (btn) {
                btn.addEventListener('click', function () { dlg.close(); });
            });
        }
    })();
    </script>
<?php endif; ?>

<script src="assets/js/simda.js?v=<?= Security::e($assetVer) ?>"></script>
<script>
    // Security: Concurrent Session Watcher (Single Active Session Enforcement)
    (function () {
        var isChecking = false;
        function checkActiveSession() {
            if (isChecking) return;
            isChecking = true;
            fetch('?route=auth_session_check', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                isChecking = false;
                if (data && data.valid === false) {
                    // Previous session invalidated because account was opened on another device/browser
                    window.location.href = data.redirect || '?route=login';
                }
            })
            .catch(function() {
                isChecking = false;
            });
        }

        // Periodic heartbeat check every 25 seconds
        setInterval(checkActiveSession, 25000);

        // Immediate check whenever user returns to or focuses this window
        window.addEventListener('focus', checkActiveSession);
    })();
</script>
</body>
</html>
