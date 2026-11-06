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

        <nav class="sidebar-nav">
            <?php if ($role === 'admin'): ?>
                <!-- ADMIN SIDEBAR -->
                <?php
                $adminLabel = static fn (string $so, string $en, string $ar): string => $locale === 'so' ? $so : ($locale === 'ar' ? $ar : $en);
                $adminNav = [
                    [
                        'label' => $adminLabel('BORTAALKA MAAMULKA', 'ADMIN PORTAL', 'لوحة المسؤول'),
                        'links' => [
                            ['href' => '?route=dashboard', 'icon' => 'dashboard', 'text' => 'Dashboard', 'routes' => ['dashboard']],
                            ['href' => '?route=users', 'icon' => 'users', 'text' => $adminLabel('All Users', 'All Users', 'جميع المستخدمين'), 'routes' => ['users']],
                            ['href' => '?route=clients', 'icon' => 'user-check', 'text' => $adminLabel('Clients', 'Clients', 'العملاء'), 'routes' => ['clients', 'client_view', 'client_register', 'client_transfer', 'guarantors']],
                            ['href' => '?route=managers', 'icon' => 'briefcase', 'text' => $adminLabel('Managers', 'Managers', 'المديرون'), 'routes' => ['managers']],
                            ['href' => '?route=branch_managers', 'icon' => 'user-check', 'text' => 'Branch Managers', 'routes' => ['branch_managers']],
                            ['href' => '?route=header_office', 'icon' => 'shield', 'text' => 'Header Office', 'routes' => ['header_office']],
                            ['href' => '?route=branches', 'icon' => 'building', 'text' => $adminLabel('Branch', 'Branch', 'الفروع'), 'routes' => ['branches']],
                        ],
                    ],
                    [
                        'label' => $adminLabel('LACAG-BIXINTA & CODSIYADA', 'REPAYMENTS & REQUESTS', 'السداد والطلبات'),
                        'links' => [
                            ['href' => '?route=murabaha&filter=unpaid', 'icon' => 'shopping-bag', 'text' => $adminLabel('Murabaxo Socota', 'Active Murabaha', 'المرابحة الجارية'), 'routes' => ['murabaha', 'murabaha_new']],
                            ['href' => '?route=loans&filter=active', 'icon' => 'credit-card', 'text' => $adminLabel('Dayn Cadaan Socota', 'Active Cash Loans', 'القروض النقدية الجارية'), 'routes' => ['loans']],
                            ['href' => '?route=approval_queue', 'icon' => 'check-square', 'text' => $adminLabel('Codsiyo Sugaya', 'Pending Applications', 'طلبات قيد الانتظار'), 'routes' => ['approval_queue']],
                        ],
                    ],
                    [
                        'label' => $adminLabel('AKOONKA', 'ACCOUNT', 'الحساب'),
                        'links' => [
                            ['href' => '?route=profile', 'icon' => 'user', 'text' => 'Profile', 'routes' => ['profile']],
                        ],
                    ],
                ];
                ?>
                <?php foreach ($adminNav as $navGroup): ?>
                    <div class="sidebar-group-label"><?= Security::e($navGroup['label']) ?></div>
                    <?php foreach ($navGroup['links'] as $navLink): ?>
                        <a href="<?= Security::e($navLink['href']) ?>" class="sidebar-link <?= in_array($currentRoute, $navLink['routes'], true) ? 'active' : '' ?>">
                            <?= Icons::svg($navLink['icon'], 18) ?>
                            <span class="sidebar-text"><?= Security::e($navLink['text']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <a href="?route=logout" class="sidebar-link" style="color:#FF7676;">
                    <?= Icons::svg('log-out', 18) ?>
                    <span class="sidebar-text"><?= $locale === 'so' ? 'Logout (Ka Bax)' : ($locale === 'ar' ? 'تسجيل الخروج' : 'Logout') ?></span>
                </a>

            <?php else: ?>
                <!-- NON-ADMIN ROLES (Staff, Branch Managers, Officers, Clients) -->
                <!-- MAIN -->
                <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_main')) ?></div>
                <a href="?route=dashboard" class="sidebar-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
                    <?= Icons::svg('dashboard', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_dashboard')) ?></span>
                </a>

                <?php if (AuthMiddleware::hasPermission('clients.view')): ?>
                    <!-- CLIENT MANAGEMENT -->
                    <div class="sidebar-group-label"><?= $locale === 'so' ? 'MACAAMIISHA' : 'CLIENTS' ?></div>
                    <a href="?route=clients" class="sidebar-link <?= in_array($currentRoute, ['clients', 'client_view'], true) ? 'active' : '' ?>">
                        <?= Icons::svg('users', 18) ?>
                        <span class="sidebar-text"><?= $locale === 'so' ? 'All Clients (Macaamiisha)' : 'All Clients' ?></span>
                    </a>
                    <?php if (AuthMiddleware::hasPermission('clients.register')): ?>
                    <a href="?route=client_register" class="sidebar-link <?= $currentRoute === 'client_register' ? 'active' : '' ?>">
                        <?= Icons::svg('user-plus', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_register_client')) ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (AuthMiddleware::hasPermission('guarantors.manage')): ?>
                    <a href="?route=guarantors" class="sidebar-link <?= $currentRoute === 'guarantors' ? 'active' : '' ?>">
                        <?= Icons::svg('shield', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_guarantors')) ?></span>
                    </a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (AuthMiddleware::hasPermission('loans.view')): ?>
                <!-- DAYMAHA (LOANS / QARD HASSAN) -->
                <div class="sidebar-group-label"><?= $locale === 'so' ? 'DAYMAHA (LOANS)' : 'CASH LOANS' ?></div>
                <a href="?route=loans&filter=active" class="sidebar-link <?= ($currentRoute === 'loans' && (($_GET['filter'] ?? '') === 'active' || empty($_GET['filter']))) ? 'active' : '' ?>">
                    <?= Icons::svg('activity', 18) ?>
                    <span class="sidebar-text"><?= $locale === 'so' ? 'Dadka Deynta Qaatay' : 'Active Borrowers' ?></span>
                </a>
                <a href="?route=loans&filter=settled_ontime" class="sidebar-link <?= ($currentRoute === 'loans' && ($_GET['filter'] ?? '') === 'settled_ontime') ? 'active' : '' ?>">
                    <?= Icons::svg('check-circle', 18) ?>
                    <span class="sidebar-text"><?= $locale === 'so' ? 'Deynta Iska Bixiyay (Waqtiga)' : 'Settled On-Time' ?></span>
                </a>
                <a href="?route=loans&filter=settled_late" class="sidebar-link <?= ($currentRoute === 'loans' && ($_GET['filter'] ?? '') === 'settled_late') ? 'active' : '' ?>">
                    <?= Icons::svg('clock', 18) ?>
                    <span class="sidebar-text"><?= $locale === 'so' ? 'Deynta Bixiyay (Dib u Dhacay)' : 'Settled Late' ?></span>
                </a>
                <?php endif; ?>

                <?php if (AuthMiddleware::hasPermission('murabaha.view')): ?>
                <!-- MURABAXO (MURABAHA ASSET FINANCING) -->
                <div class="sidebar-group-label"><?= $locale === 'so' ? 'MURABAXO (ASSET FINANCING)' : 'MURABAHA' ?></div>
                <div class="sidebar-dropdown-wrap">
                    <a href="?route=murabaha" class="sidebar-link <?= in_array($currentRoute, ['murabaha', 'murabaha_new'], true) ? 'active' : '' ?>" style="justify-content:space-between;">
                        <span style="display:flex;align-items:center;gap:12px;">
                            <?= Icons::svg('shopping-bag', 18) ?>
                            <span class="sidebar-text"><?= $locale === 'so' ? 'Heshiiska Murabaxada' : 'Murabaha Financing' ?></span>
                        </span>
                        <span style="display:inline-flex;padding:2px;opacity:0.75;" onclick="event.preventDefault(); event.stopPropagation(); const sm = document.getElementById('murabahaSubMenu'); if(sm){ sm.style.display = sm.style.display === 'none' ? 'flex' : 'none'; }">
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="1 1 5 5 9 1"></polyline>
                            </svg>
                        </span>
                    </a>
                    <div class="sidebar-submenu" id="murabahaSubMenu" style="display:<?= in_array($currentRoute, ['murabaha', 'murabaha_new'], true) ? 'flex' : 'flex' ?>;">
                        <?php if (AuthMiddleware::hasPermission('murabaha.create')): ?>
                        <a href="?route=murabaha_new" class="sidebar-sublink <?= $currentRoute === 'murabaha_new' ? 'active' : '' ?>">
                            <?= Icons::svg('plus', 13) ?>
                            <span><?= $locale === 'so' ? 'Qof Cusub u Diwaangeli' : 'New Murabaha Application' ?></span>
                        </a>
                        <?php endif; ?>
                        <a href="?route=murabaha&filter=all" class="sidebar-sublink <?= ($currentRoute === 'murabaha' && (($_GET['filter'] ?? '') === 'all' || empty($_GET['filter']))) ? 'active' : '' ?>">
                            <?= Icons::svg('layers', 13) ?>
                            <span><?= $locale === 'so' ? 'Dadka Murabaxada Qaatay' : 'All Murabaha Contracts' ?></span>
                        </a>
                        <a href="?route=murabaha&filter=settled" class="sidebar-sublink <?= ($currentRoute === 'murabaha' && ($_GET['filter'] ?? '') === 'settled') ? 'active' : '' ?>">
                            <?= Icons::svg('check-circle', 13) ?>
                            <span><?= $locale === 'so' ? 'Inta Qaadatay & Soo Celisay' : 'Settled Murabaha' ?></span>
                        </a>
                        <a href="?route=murabaha&filter=unpaid" class="sidebar-sublink <?= ($currentRoute === 'murabaha' && ($_GET['filter'] ?? '') === 'unpaid') ? 'active' : '' ?>">
                            <?= Icons::svg('alert-triangle', 13) ?>
                            <span><?= $locale === 'so' ? 'Inta Aan Weli Soo Celin' : 'Unpaid / Active' ?></span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- FINANCING -->
                <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_financing')) ?></div>
                <?php if (AuthMiddleware::hasPermission('products.manage')): ?>
                    <a href="?route=products" class="sidebar-link <?= $currentRoute === 'products' ? 'active' : '' ?>">
                        <?= Icons::svg('layers', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_products')) ?></span>
                    </a>
                <?php endif; ?>
                <?php if (AuthMiddleware::hasPermission('applications.view')): ?>
                    <a href="?route=applications" class="sidebar-link <?= $currentRoute === 'applications' ? 'active' : '' ?>">
                        <?= Icons::svg('file-text', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_applications')) ?></span>
                    </a>
                <?php endif; ?>
                <?php if (AuthMiddleware::hasPermission('applications.approve_small') || AuthMiddleware::hasPermission('applications.approve_large')): ?>
                    <a href="?route=approval_queue" class="sidebar-link <?= $currentRoute === 'approval_queue' ? 'active' : '' ?>">
                        <?= Icons::svg('check-square', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_approval_queue')) ?></span>
                    </a>
                <?php endif; ?>

                <a href="?route=financings" class="sidebar-link <?= in_array($currentRoute, ['financings', 'financing_view'], true) ? 'active' : '' ?>">
                    <?= Icons::svg('credit-card', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_active_financing')) ?></span>
                </a>

                <?php if (AuthMiddleware::hasPermission('financings.disburse')): ?>
                    <a href="?route=disbursements" class="sidebar-link <?= $currentRoute === 'disbursements' ? 'active' : '' ?>">
                        <?= Icons::svg('dollar-sign', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_disbursements')) ?></span>
                    </a>
                <?php endif; ?>

                <a href="?route=schedules" class="sidebar-link <?= $currentRoute === 'schedules' ? 'active' : '' ?>">
                    <?= Icons::svg('calendar', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_schedules')) ?></span>
                </a>

                <!-- PAYMENTS -->
                <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_payments')) ?></div>
                <?php if (AuthMiddleware::hasPermission('repayments.record')): ?>
                    <a href="?route=repayment_record" class="sidebar-link <?= $currentRoute === 'repayment_record' ? 'active' : '' ?>">
                        <?= Icons::svg('clock', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_record_repayment')) ?></span>
                    </a>
                <?php endif; ?>
                <?php if (AuthMiddleware::hasPermission('repayments.view')): ?>
                <a href="?route=repayments" class="sidebar-link <?= $currentRoute === 'repayments' ? 'active' : '' ?>">
                    <?= Icons::svg('activity', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_repayment_history')) ?></span>
                </a>
                <?php endif; ?>
                <?php if (AuthMiddleware::hasPermission('charity.view')): ?>
                <a href="?route=charity_charges" class="sidebar-link <?= $currentRoute === 'charity_charges' ? 'active' : '' ?>">
                    <?= Icons::svg('heart-handshake', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_charity_charges')) ?></span>
                </a>
                <?php endif; ?>

                <!-- RISK MANAGEMENT -->
                <?php if (AuthMiddleware::hasPermission('risk.view')): ?>
                <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_risk')) ?></div>
                <a href="?route=credit_scores" class="sidebar-link <?= $currentRoute === 'credit_scores' ? 'active' : '' ?>">
                    <?= Icons::svg('bar-chart', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_credit_scores')) ?></span>
                </a>
                <a href="?route=par30" class="sidebar-link <?= $currentRoute === 'par30' ? 'active' : '' ?>">
                    <?= Icons::svg('alert-triangle', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_par30')) ?></span>
                </a>
                <?php endif; ?>

                <!-- COMMUNICATION -->
                <?php if (AuthMiddleware::hasPermission('communication.sms')): ?>
                <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_communication')) ?></div>
                <a href="?route=sms_outbox" class="sidebar-link <?= $currentRoute === 'sms_outbox' ? 'active' : '' ?>">
                    <?= Icons::svg('message-square', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_sms_outbox')) ?></span>
                </a>
                <?php endif; ?>

                <!-- REPORTS & STATEMENTS -->
                <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_reports')) ?></div>
                <?php if (AuthMiddleware::hasPermission('statements.generate')): ?>
                <a href="?route=statements" class="sidebar-link <?= $currentRoute === 'statements' ? 'active' : '' ?>">
                    <?= Icons::svg('file-text', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_client_statements')) ?></span>
                </a>
                <?php endif; ?>
                <?php if (AuthMiddleware::hasPermission('reports.view')): ?>
                    <a href="?route=reports" class="sidebar-link <?= $currentRoute === 'reports' ? 'active' : '' ?>">
                        <?= Icons::svg('bar-chart', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_financial_reports')) ?></span>
                    </a>
                <?php endif; ?>

                <?php if (AuthMiddleware::hasPermission('branches.manage') || AuthMiddleware::hasPermission('users.manage')): ?>
                    <!-- ADMINISTRATION -->
                    <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_admin')) ?></div>
                    <?php if (AuthMiddleware::hasPermission('branches.manage')): ?>
                    <a href="?route=branches" class="sidebar-link <?= $currentRoute === 'branches' ? 'active' : '' ?>">
                        <?= Icons::svg('building', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_branches')) ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (AuthMiddleware::hasPermission('users.manage')): ?>
                    <a href="?route=users" class="sidebar-link <?= $currentRoute === 'users' ? 'active' : '' ?>">
                        <?= Icons::svg('users', 18) ?>
                        <span class="sidebar-text"><?= Security::e(Lang::get('menu_users')) ?></span>
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div class="sidebar-text" style="min-width:0;">
                    <div style="font-weight:700;font-size:0.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= Security::e($user['full_name'] ?? '') ?>
                    </div>
                    <div style="font-size:0.72rem;color:var(--simda-gold);">
                        <?= Security::e($user['role_name_en'] ?? '') ?> • <?= Security::e($user['branch_code'] ?? 'HQ') ?>
                    </div>
                </div>
                <?php if ($role !== 'admin'): /* Admin already has Logout in the sidebar menu. */ ?>
                <a href="?route=logout" class="btn btn-sm btn-outline" style="color:#FFF;border-color:rgba(255,255,255,0.22);" title="<?= Security::e(Lang::get('nav_logout')) ?>">
                    <?= Icons::svg('log-out', 15) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('nav_logout')) ?></span>
                </a>
                <?php endif; ?>
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
