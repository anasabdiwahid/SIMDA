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
                        if (k !== 'simda-static-shell-v4') { caches.delete(k); }
                    });
                });
            }
        })();
    </script>
    <link rel="stylesheet" href="assets/css/simda.css?v=<?= Security::e($assetVer) ?>">
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
                    ['route' => 'approval_queue', 'label' => Lang::get('menu_approval_queue'), 'show' => $can('applications.approve_small') || $can('applications.approve_large'), 'active' => $on(['approval_queue'])],
                    ['route' => 'financings', 'label' => Lang::get('menu_active_financing'), 'active' => $on(['financings', 'financing_view'])],
                    ['route' => 'disbursements', 'label' => Lang::get('menu_disbursements'), 'show' => $can('financings.disburse'), 'active' => $on(['disbursements'])],
                    ['route' => 'schedules', 'label' => Lang::get('menu_schedules'), 'active' => $on(['schedules'])],
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
                // Staff: register and list managers and employees. Each role only sees the levels below it.
                'label' => $so ? 'Shaqaalaha' : 'Staff', 'icon' => 'user-check', 'show' => $can('users.manage'),
                'items' => [
                    ['route' => 'managers&new=1', 'label' => $so ? 'Diiwaangeli Maamule Guud' : 'Register General Manager', 'cta' => true, 'show' => $role === 'admin', 'active' => $on(['managers']) && $isNew],
                    ['route' => 'branch_managers&new=1', 'label' => $so ? 'Diiwaangeli Maamule Laan' : 'Register Branch Manager', 'cta' => true, 'show' => in_array($role, ['admin', 'manager'], true), 'active' => $on(['branch_managers']) && $isNew],
                    ['route' => 'employees&new=1', 'label' => $so ? 'Diiwaangeli Shaqaale' : 'Register Employee', 'cta' => true, 'active' => $on(['employees']) && $isNew],
                    ['route' => 'managers', 'label' => $so ? 'Maamulayaasha Guud' : 'General Managers', 'show' => $role === 'admin', 'active' => $on(['managers']) && !$isNew],
                    ['route' => 'branch_managers', 'label' => $so ? 'Maamulayaasha Laamaha' : 'Branch Managers', 'show' => in_array($role, ['admin', 'manager'], true), 'active' => $on(['branch_managers']) && !$isNew],
                    ['route' => 'employees', 'label' => $so ? 'Liiska Shaqaalaha' : 'All Employees', 'active' => $on(['employees', 'employee_register']) && !$isNew],
                ],
            ],
            [
                'label' => Lang::get('grp_admin'), 'icon' => 'settings', 'show' => $can('branches.manage') || $can('users.manage'),
                'items' => [
                    ['route' => 'users', 'label' => Lang::get('menu_users'), 'show' => $can('users.manage'), 'active' => $on(['users'])],
                    ['route' => 'branches', 'label' => Lang::get('menu_branches'), 'show' => $can('branches.manage'), 'active' => $on(['branches'])],
                    ['route' => 'permissions', 'label' => $so ? 'Awoodaha' : 'Permissions', 'show' => $role === 'admin', 'active' => $on(['permissions'])],
                    ['route' => 'audit_logs', 'label' => $so ? 'Diiwaanka Dhaqdhaqaaqa' : 'Audit Logs', 'show' => $role === 'admin', 'active' => $on(['audit_logs'])],
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

        <div class="sidebar-footer">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                <a href="?route=profile" class="sidebar-text" style="min-width:0;text-decoration:none;color:inherit;" title="<?= Security::e(Lang::get('menu_profile')) ?>">
                    <div style="font-weight:700;font-size:0.75rem;line-height:1.15;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= Security::e($user['full_name'] ?? '') ?>
                    </div>
                    <div style="font-size:0.65rem;line-height:1.15;color:var(--simda-gold);">
                        <?= Security::e($user['role_name_en'] ?? '') ?> • <?= Security::e($user['branch_code'] ?? 'HQ') ?>
                    </div>
                </a>
                <a href="?route=logout" class="btn btn-sm btn-outline" style="color:#FFF;border-color:rgba(255,255,255,0.22);padding:3px 7px;font-size:0.7rem;" title="<?= Security::e(Lang::get('nav_logout')) ?>">
                    <?= Icons::svg('log-out', 13) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('nav_logout')) ?></span>
                </a>
            </div>
        </div>
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

                <a href="?route=home" class="btn btn-sm btn-outline">
                    <?= Icons::svg('home', 15) ?>
                    <span><?= Security::e(Lang::get('nav_home')) ?></span>
                </a>
            </div>
        </header>

        <main class="content-area">
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
