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
<html lang="<?= Security::e($locale) ?>" data-theme="light">
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
                        if (k !== 'simda-static-shell-v3') { caches.delete(k); }
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
                    <img src="assets/images/simda-icon.png" alt="S" style="height:24px;width:auto;display:none;" class="brand-logo-compact">
                </span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <!-- MAIN -->
            <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_main')) ?></div>
            <a href="?route=dashboard" class="sidebar-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
                <?= Icons::svg('dashboard', 18) ?>
                <span class="sidebar-text"><?= Security::e(Lang::get('menu_dashboard')) ?></span>
            </a>

            <?php if (in_array($role, ['admin', 'branch_manager', 'loan_officer'], true)): ?>
                <!-- CLIENT MANAGEMENT -->
                <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_clients')) ?></div>
                <a href="?route=clients" class="sidebar-link <?= in_array($currentRoute, ['clients', 'client_view'], true) ? 'active' : '' ?>">
                    <?= Icons::svg('users', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_all_clients')) ?></span>
                </a>
                <?php if (in_array($role, ['admin', 'loan_officer'], true)): ?>
                <a href="?route=client_register" class="sidebar-link <?= $currentRoute === 'client_register' ? 'active' : '' ?>">
                    <?= Icons::svg('user-plus', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_register_client')) ?></span>
                </a>
                <?php endif; ?>
                <a href="?route=guarantors" class="sidebar-link <?= $currentRoute === 'guarantors' ? 'active' : '' ?>">
                    <?= Icons::svg('shield', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_guarantors')) ?></span>
                </a>
                <a href="?route=groups" class="sidebar-link <?= $currentRoute === 'groups' ? 'active' : '' ?>">
                    <?= Icons::svg('group', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_client_groups')) ?></span>
                </a>
            <?php endif; ?>

            <!-- FINANCING -->
            <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_financing')) ?></div>
            <?php if (in_array($role, ['admin', 'branch_manager', 'loan_officer'], true)): ?>
                <a href="?route=products" class="sidebar-link <?= $currentRoute === 'products' ? 'active' : '' ?>">
                    <?= Icons::svg('layers', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_products')) ?></span>
                </a>
                <a href="?route=applications" class="sidebar-link <?= $currentRoute === 'applications' ? 'active' : '' ?>">
                    <?= Icons::svg('file-text', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_applications')) ?></span>
                </a>
                <a href="?route=approval_queue" class="sidebar-link <?= $currentRoute === 'approval_queue' ? 'active' : '' ?>">
                    <?= Icons::svg('check-square', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_approval_queue')) ?></span>
                </a>
            <?php endif; ?>

            <a href="?route=financings" class="sidebar-link <?= in_array($currentRoute, ['financings', 'financing_view'], true) ? 'active' : '' ?>">
                <?= Icons::svg('credit-card', 18) ?>
                <span class="sidebar-text"><?= Security::e(Lang::get('menu_active_financing')) ?></span>
            </a>

            <?php if (in_array($role, ['admin', 'branch_manager', 'loan_officer'], true)): ?>
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
            <?php if (in_array($role, ['admin', 'loan_officer'], true)): ?>
                <a href="?route=repayment_record" class="sidebar-link <?= $currentRoute === 'repayment_record' ? 'active' : '' ?>">
                    <?= Icons::svg('clock', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_record_repayment')) ?></span>
                </a>
            <?php endif; ?>
            <a href="?route=repayments" class="sidebar-link <?= $currentRoute === 'repayments' ? 'active' : '' ?>">
                <?= Icons::svg('activity', 18) ?>
                <span class="sidebar-text"><?= Security::e(Lang::get('menu_repayment_history')) ?></span>
            </a>
            <a href="?route=charity_charges" class="sidebar-link <?= $currentRoute === 'charity_charges' ? 'active' : '' ?>">
                <?= Icons::svg('heart-handshake', 18) ?>
                <span class="sidebar-text"><?= Security::e(Lang::get('menu_charity_charges')) ?></span>
            </a>

            <!-- RISK MANAGEMENT -->
            <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_risk')) ?></div>
            <a href="?route=credit_scores" class="sidebar-link <?= $currentRoute === 'credit_scores' ? 'active' : '' ?>">
                <?= Icons::svg('bar-chart', 18) ?>
                <span class="sidebar-text"><?= Security::e(Lang::get('menu_credit_scores')) ?></span>
            </a>
            <?php if (in_array($role, ['admin', 'branch_manager', 'loan_officer'], true)): ?>
                <a href="?route=par30" class="sidebar-link <?= $currentRoute === 'par30' ? 'active' : '' ?>">
                    <?= Icons::svg('alert-triangle', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_par30')) ?></span>
                </a>
            <?php endif; ?>

            <!-- COMMUNICATION -->
            <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_communication')) ?></div>
            <a href="?route=sms_outbox" class="sidebar-link <?= $currentRoute === 'sms_outbox' ? 'active' : '' ?>">
                <?= Icons::svg('message-square', 18) ?>
                <span class="sidebar-text"><?= Security::e(Lang::get('menu_sms_outbox')) ?></span>
            </a>

            <!-- REPORTS & STATEMENTS -->
            <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_reports')) ?></div>
            <a href="?route=statements" class="sidebar-link <?= $currentRoute === 'statements' ? 'active' : '' ?>">
                <?= Icons::svg('file-text', 18) ?>
                <span class="sidebar-text"><?= Security::e(Lang::get('menu_client_statements')) ?></span>
            </a>
            <?php if (in_array($role, ['admin', 'branch_manager'], true)): ?>
                <a href="?route=reports" class="sidebar-link <?= $currentRoute === 'reports' ? 'active' : '' ?>">
                    <?= Icons::svg('bar-chart', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_financial_reports')) ?></span>
                </a>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <!-- ADMINISTRATION -->
                <div class="sidebar-group-label"><?= Security::e(Lang::get('grp_admin')) ?></div>
                <a href="?route=branches" class="sidebar-link <?= $currentRoute === 'branches' ? 'active' : '' ?>">
                    <?= Icons::svg('building', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_branches')) ?></span>
                </a>
                <a href="?route=users" class="sidebar-link <?= $currentRoute === 'users' ? 'active' : '' ?>">
                    <?= Icons::svg('users', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_users')) ?></span>
                </a>
                <a href="?route=audit_logs" class="sidebar-link <?= $currentRoute === 'audit_logs' ? 'active' : '' ?>">
                    <?= Icons::svg('shield', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_audit_logs')) ?></span>
                </a>
                <a href="?route=settings" class="sidebar-link <?= $currentRoute === 'settings' ? 'active' : '' ?>">
                    <?= Icons::svg('settings', 18) ?>
                    <span class="sidebar-text"><?= Security::e(Lang::get('menu_settings')) ?></span>
                </a>
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
                <a href="?route=logout" class="btn btn-sm btn-outline" style="color:#FFF;border-color:rgba(255,255,255,0.22);" title="<?= Security::e(Lang::get('nav_logout')) ?>">
                    <?= Icons::svg('log-out', 15) ?>
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

                <!-- Language Switcher (English Default) -->
                <div style="display:flex;gap:4px;">
                    <a href="?route=<?= Security::e($currentRoute) ?>&lang=en" class="btn btn-sm <?= $locale === 'en' ? 'btn-primary' : 'btn-outline' ?>">EN</a>
                    <a href="?route=<?= Security::e($currentRoute) ?>&lang=so" class="btn btn-sm <?= $locale === 'so' ? 'btn-primary' : 'btn-outline' ?>">SO</a>
                </div>

                <!-- Theme Switcher -->
                <select id="simdaThemeSelect" class="form-control" style="padding:5px 10px;font-size:0.78rem;width:auto;" aria-label="Theme Switcher">
                    <option value="light"><?= Security::e(Lang::get('theme_light')) ?></option>
                    <option value="dark"><?= Security::e(Lang::get('theme_dark')) ?></option>
                    <option value="system"><?= Security::e(Lang::get('theme_system')) ?></option>
                </select>

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
</body>
</html>
