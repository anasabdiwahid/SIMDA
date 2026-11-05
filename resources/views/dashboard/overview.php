<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$user = AuthMiddleware::user() ?? [];
$m = $metrics;
$isSo = $locale === 'so';

$totalClients = (int) ($m['total_clients'] ?? 0);
$activeCount = (int) ($m['active_financing_count'] ?? 0);
$overdueCount = (int) ($m['overdue_financing_count'] ?? 0);
$pendingApps = (int) ($m['pending_applications_count'] ?? 0);
$outstandingBalance = (float) ($m['total_outstanding_balance'] ?? 0);
$disbursedTotal = (float) ($m['total_disbursed_amount'] ?? 0);
$repaymentsTotal = (float) ($m['total_repayments_collected'] ?? 0);
$grossReceipts = (float) ($m['gross_receipts'] ?? 0);
$par30Percent = (float) ($m['par30_percentage'] ?? 0);
$par30Risky = (float) ($m['par30_risky_balance'] ?? 0);
$charityBalance = (float) ($m['charity_account_balance'] ?? 0);

$financingsList = $recentFinancings ?? [];
$financingsCount = count($financingsList);
?>

<div class="finnova-canvas animate-in">
    <!-- 1. TOP CAPSULE NAVBAR -->
    <header class="finnova-top-nav">
        <!-- Brand & Real Live Count Badge -->
        <div class="finnova-brand-group">
            <a href="?route=dashboard" class="finnova-brand-logo" style="text-decoration:none;">
                <img src="assets/images/simda-icon.png" alt="SIMDA" style="height:36px;width:auto;border-radius:10px;box-shadow:0 3px 10px rgba(18,59,50,0.18);">
                <div class="finnova-brand-meta">
                    <h2>SIMDA</h2>
                    <p><?= $isSo ? 'Maalgelinta Islaamiga ah' : 'Islamic Microfinance Development' ?></p>
                </div>
            </a>
            <?php if ($totalClients > 0): ?>
                <span class="finnova-badge-count" title="<?= $isSo ? 'Wadarta Macaamiisha Diiwaangashan' : 'Total Registered Clients' ?>">
                    <?= $totalClients ?> <?= $isSo ? 'Macaamiil' : 'Clients' ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Right Utility Icons & Profile -->
        <div class="finnova-utils-group">
            <a href="?route=statements" class="finnova-util-btn" title="Statements & Reports">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </a>
            <a href="?route=products" class="finnova-util-btn" title="Products & Services">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
            </a>
            <a href="?route=schedules" class="finnova-util-btn" title="Calendar & Schedules">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </a>
            <a href="?route=charity_charges" class="finnova-util-btn" title="Charity Fund">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </a>
            <button type="button" class="finnova-util-btn" title="Operational Data" onclick="toggleOperationalDrawer()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="21" x2="4" y2="14"></line>
                    <line x1="4" y1="10" x2="4" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12" y2="3"></line>
                    <line x1="20" y1="21" x2="20" y2="16"></line>
                    <line x1="20" y1="12" x2="20" y2="3"></line>
                </svg>
            </button>
            <a href="?route=approval_queue" class="finnova-util-btn" title="Pending Applications">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if ($pendingApps > 0): ?>
                    <span class="dot-alert"></span>
                <?php endif; ?>
            </a>
            <a href="?route=settings" class="finnova-util-btn" title="Settings">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
            </a>
            <div class="finnova-user-avatar" title="<?= Security::e($user['full_name'] ?? 'Admin') ?>">
                <span><?= strtoupper(substr($user['full_name'] ?? 'AD', 0, 2)) ?></span>
            </div>
        </div>
    </header>

    <!-- 2. SUB-HEADER / TITLE ROW -->
    <div class="finnova-title-row">
        <div class="finnova-title-left">
            <a href="?route=home" class="finnova-back-circle" title="Home">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div class="finnova-title-meta">
                <h1><?= $isSo ? 'Maalgelinta & Qaansheegyada' : 'Financings & Invoices' ?></h1>
                <p><?= $isSo ? 'Maamul oo la soco dhammaan heshiisyada iyo xisaabaadka tooska ah.' : 'Manage and track all live financing contracts in one place.' ?></p>
            </div>
        </div>

        <div class="finnova-title-actions">
            <a href="?route=client_register" class="finnova-create-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span><?= $isSo ? 'Diiwaangeli Macmiil' : 'Register Client' ?></span>
            </a>
        </div>
    </div>

    <!-- 3. TOP 4 KPI CARDS GRID (100% REAL LIVE DATABASE METRICS) -->
    <div class="finnova-kpi-grid">
        <!-- CARD 1: OVERDUE / PAR30 RISKY -->
        <div class="finnova-kpi-card">
            <div>
                <div class="finnova-kpi-top">
                    <span class="finnova-kpi-title"><?= $isSo ? 'Lacagaha Dib u Dhacay' : 'Overdue Portfolio' ?></span>
                    <span class="finnova-kpi-icon-pill red">!</span>
                </div>
                <div class="finnova-kpi-val"><?= Security::formatMoney($par30Risky) ?></div>
                <div class="finnova-kpi-trend red">
                    <span><?= $overdueCount ?></span> <?= $isSo ? 'heshiis dib u dhacay' : 'contracts overdue' ?>
                </div>
            </div>
            <div style="font-size:0.75rem;color:#7E849C;margin-top:auto;padding-top:12px;border-top:1px dashed #EFF1F7;">
                PAR30 Risk Ratio: <strong style="color:<?= $par30Percent > 5 ? '#E5484D' : '#123B32' ?>;"><?= Security::e($m['par30_percentage'] ?? '0.00') ?>%</strong>
            </div>
        </div>

        <!-- CARD 2: TOTAL OUTSTANDING BALANCE -->
        <div class="finnova-kpi-card">
            <div>
                <div class="finnova-kpi-top">
                    <span class="finnova-kpi-title"><?= $isSo ? 'Hadhaaga Maalgelinta' : 'Total Outstanding Balance' ?></span>
                    <span class="finnova-kpi-icon-pill purple">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                </div>
                <div class="finnova-kpi-val"><?= Security::formatMoney($outstandingBalance) ?></div>
                <div class="finnova-kpi-trend purple">
                    <span><?= $activeCount ?></span> <?= $isSo ? 'heshiis firfircoon' : 'active contracts' ?>
                </div>
            </div>
            <div style="font-size:0.75rem;color:#7E849C;margin-top:auto;padding-top:12px;border-top:1px dashed #EFF1F7;">
                <?= $isSo ? 'Lacagaha sugaya bixinta' : 'Portfolio in circulation' ?>
            </div>
        </div>

        <!-- CARD 3: TOTAL REPAYMENTS COLLECTED -->
        <div class="finnova-kpi-card">
            <div>
                <div class="finnova-kpi-top">
                    <span class="finnova-kpi-title"><?= $isSo ? 'Lacagaha Soo Xarooday' : 'Total Repayments Collected' ?></span>
                    <span class="finnova-kpi-icon-pill teal">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </span>
                </div>
                <div class="finnova-kpi-val"><?= Security::formatMoney($repaymentsTotal) ?></div>
                <div class="finnova-kpi-trend teal">
                    <?= $isSo ? 'Gross Receipts' : 'Gross Receipts' ?>: <?= Security::formatMoney($grossReceipts) ?>
                </div>
            </div>
            <div style="font-size:0.75rem;color:#7E849C;margin-top:auto;padding-top:12px;border-top:1px dashed #EFF1F7;">
                <a href="?route=repayments" style="color:#00A389;text-decoration:none;font-weight:600;"><?= $isSo ? 'Eeg Taariikhda Bixinta' : 'View Repayment Logs' ?> →</a>
            </div>
        </div>

        <!-- CARD 4: TOTAL DISBURSED AMOUNT -->
        <div class="finnova-kpi-card">
            <div>
                <div class="finnova-kpi-top">
                    <span class="finnova-kpi-title"><?= $isSo ? 'Wadarta La Bixiyay' : 'Total Disbursed' ?></span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="finnova-kpi-icon-pill green">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <a href="?route=disbursements" class="finnova-util-btn" style="width:24px;height:24px;border-radius:50%;" title="Disbursements">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                <polyline points="7 7 17 7 17 17"></polyline>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="finnova-kpi-val"><?= Security::formatMoney($disbursedTotal) ?></div>
                <div style="font-size:0.78rem;color:#7E849C;font-weight:600;margin-bottom:6px;">
                    <?= $isSo ? 'Sanduuqa Tabarucaadka' : 'Charity Account' ?>: <?= Security::formatMoney($charityBalance) ?>
                </div>
            </div>
            <div style="margin-top:auto;padding-top:10px;border-top:1px dashed #EFF1F7;">
                <a href="?route=disbursements" class="finnova-payout-now-btn" style="width:100%;text-align:center;">
                    <?= $isSo ? 'Kormeerka Bixinta' : 'Disbursements' ?>
                </a>
            </div>
        </div>
    </div>

    <!-- 4. ACTIVE FILTERS BAR (REAL DYNAMIC TIME & STATUS) -->
    <div class="finnova-filter-bar">
        <div class="finnova-filters-left">
            <div class="finnova-filter-badge">
                <span><?= $isSo ? 'Xogta Tooska ah' : 'Live Filters' ?></span>
            </div>
            <a href="?route=financings" class="finnova-filter-pill">
                <span><?= $isSo ? 'Dhammaan Heshiisyada' : 'All Financings' ?> (<?= $financingsCount ?>)</span>
            </a>
            <div class="finnova-filter-pill">
                <span><?= date('F Y') ?></span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
        </div>

        <div class="finnova-search-capsule">
            <input type="text" id="finnovaInvoiceSearch" placeholder="<?= $isSo ? 'Raadi heshiis...' : 'Search contract #...' ?>" aria-label="Search Invoices">
            <span class="search-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
        </div>
    </div>

    <!-- 5. SIGNATURE DARK MASTER-DETAIL CONTAINER (REAL DATABASE RECORDS ONLY) -->
    <section class="finnova-dark-section">
        <!-- Top bar inside dark card -->
        <div class="finnova-dark-header">
            <h3 class="finnova-dark-title"><?= $isSo ? 'Heshiisyada & Qaansheegyada' : 'Financings & Invoices' ?></h3>

            <!-- Centered Capsule Tab Switcher -->
            <div class="finnova-tabs-capsule">
                <button type="button" class="finnova-tab-btn active" onclick="filterFinnovaTabs('all', this)">
                    <?= $isSo ? 'Dhammaan' : 'All' ?> (<?= $financingsCount ?>)
                </button>
                <button type="button" class="finnova-tab-btn" onclick="filterFinnovaTabs('active', this)">
                    <?= $isSo ? 'Firfircoon' : 'Active' ?> (<?= $activeCount ?>)
                </button>
                <button type="button" class="finnova-tab-btn" onclick="filterFinnovaTabs('overdue', this)">
                    <?= $isSo ? 'Dib u dhacay' : 'Overdue' ?> (<?= $overdueCount ?>)
                </button>
            </div>

            <!-- Right link -->
            <div class="finnova-dark-actions">
                <a href="?route=financings" class="btn btn-sm btn-outline" style="color:#FFF;border-color:rgba(255,255,255,0.25);">
                    <?= $isSo ? 'Eeg Dhammaan' : 'View All' ?>
                </a>
            </div>
        </div>

        <?php if (empty($financingsList)): ?>
            <!-- CLEAN EMPTY STATE (100% REAL ZERO-DATA BASELINE) -->
            <div style="text-align:center;padding:50px 20px;color:rgba(255,255,255,0.75);">
                <div style="width:64px;height:64px;border-radius:50%;background:rgba(214,182,108,0.14);color:#D6B66C;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                </div>
                <h3 style="font-size:1.15rem;font-weight:700;color:#FFFFFF;margin:0 0 8px 0;">
                    <?= $isSo ? 'Weli ma jiraan qaansheegyo ama heshiisyo maalgelin' : 'No Invoices or Financing Records Yet' ?>
                </h3>
                <p style="font-size:0.86rem;color:#9DA3BE;max-width:460px;margin:0 auto 22px auto;line-height:1.55;">
                    <?= $isSo ? 'Database-ka hadda waa nadiif. Dhammaan xogtu waxay toos uga imanaysaa kaydka dhabta ah. Waxaad bilaabi kartaa adoo diiwaangelinaya macmiil cusub ama codsi maalgelin.' : 'The database is currently clean with real baseline data. You can start by registering a new client or creating a financing application.' ?>
                </p>
                <div style="display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;">
                    <a href="?route=client_register" class="btn btn-sm btn-gold" style="font-weight:700;padding:8px 20px;">
                        <?= Icons::svg('user-plus', 16) ?>
                        <span><?= $isSo ? 'Diiwaangeli Macmiil Cusub' : 'Register New Client' ?></span>
                    </a>
                    <a href="?route=products" class="btn btn-sm btn-outline" style="color:#FFF;border-color:rgba(255,255,255,0.3);padding:8px 18px;">
                        <?= Icons::svg('layers', 16) ?>
                        <span><?= $isSo ? 'Badeecadaha Maalgelinta' : 'Financing Products' ?></span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- 2-COLUMN SPLIT MASTER-DETAIL (REAL DATABASE DATA) -->
            <div class="finnova-dark-split">
                <!-- LEFT COLUMN: REAL FINANCINGS LIST -->
                <div class="finnova-invoices-list" id="finnovaInvoiceList">
                    <?php foreach ($financingsList as $idx => $f): ?>
                        <?php
                        $isActiveRow = ($idx === 0);
                        $fDue = Security::formatMoney($f['total_due'] ?? 0);
                        $fBal = Security::formatMoney($f['outstanding_balance'] ?? 0);
                        $fPrin = Security::formatMoney($f['principal_amount'] ?? 0);
                        $fProf = Security::formatMoney($f['profit_markup_amount'] ?? 0);
                        $fPaid = Security::formatMoney((float)($f['total_due'] ?? 0) - (float)($f['outstanding_balance'] ?? 0));
                        ?>
                        <div class="finnova-inv-row <?= $isActiveRow ? 'active' : '' ?>"
                             data-inv="<?= Security::e($f['financing_number']) ?>"
                             data-id="<?= (int)$f['id'] ?>"
                             data-amount="<?= $fDue ?>"
                             data-status="<?= Security::e($f['status']) ?>"
                             data-company="SIMDA • <?= Security::e($f['product_type']) ?>"
                             data-customer="<?= Security::e($f['client_name']) ?>"
                             data-role="<?= Security::e($f['product_type']) ?>"
                             data-b1="<?= $fPrin ?>" data-l1="Principal"
                             data-b2="<?= $fProf ?>" data-l2="Murabaha Profit"
                             data-b3="<?= $fPaid ?>" data-l3="Repaid"
                             data-bal="<?= $fBal ?>"
                             onclick="selectFinnovaInvoice(this)">
                            <div class="finnova-inv-left">
                                <div class="finnova-inv-avatar" style="background:#1B5246;">
                                    <span><?= strtoupper(substr($f['client_name'] ?? 'CL', 0, 2)) ?></span>
                                </div>
                                <div>
                                    <div class="finnova-inv-id"># <?= Security::e($f['financing_number']) ?></div>
                                    <div class="finnova-inv-due"><?= Security::e($f['client_name']) ?></div>
                                </div>
                            </div>
                            <div class="finnova-inv-right">
                                <span class="finnova-inv-status"><?= Security::e($f['status']) ?></span>
                                <span class="finnova-inv-amount"><?= $fDue ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- RIGHT COLUMN: REAL DETAIL VIEW CARD -->
                <?php $firstF = $financingsList[0]; ?>
                <div class="finnova-detail-card" id="finnovaDetailCard">
                    <!-- Header Details -->
                    <div class="finnova-detail-header">
                        <div>
                            <div class="finnova-detail-col-label">Financing Contract</div>
                            <div class="finnova-detail-inv-title">
                                <h2 id="dtInvId"># <?= Security::e($firstF['financing_number']) ?></h2>
                                <span class="finnova-detail-status-pill" id="dtInvStatus"><?= Security::e($firstF['status']) ?></span>
                            </div>
                        </div>

                        <div>
                            <div class="finnova-detail-col-label">Institution & Product</div>
                            <div class="finnova-company-box">
                                <span class="finnova-company-name" id="dtInvCompany">SIMDA • <?= Security::e($firstF['product_type']) ?></span>
                            </div>
                        </div>

                        <div>
                            <div class="finnova-detail-col-label">Client</div>
                            <div class="finnova-customer-box">
                                <div class="finnova-customer-avatar" id="dtCustomerAvatar">
                                    <span style="color:#123B32;font-weight:700;font-size:0.85rem;" id="dtCustomerInitials">
                                        <?= strtoupper(substr($firstF['client_name'] ?? 'CL', 0, 2)) ?>
                                    </span>
                                </div>
                                <div>
                                    <div class="finnova-customer-name" id="dtCustomerName"><?= Security::e($firstF['client_name']) ?></div>
                                    <div class="finnova-customer-role" id="dtCustomerRole"><?= Security::e($firstF['product_type']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3 Breakdown Boxes -->
                    <div class="finnova-breakdown-grid">
                        <div class="finnova-breakdown-box">
                            <div class="finnova-breakdown-amount">
                                <span id="dtB1"><?= Security::formatMoney($firstF['principal_amount'] ?? 0) ?></span>
                                <span class="arrow-icon">↗</span>
                            </div>
                            <div class="finnova-breakdown-label" id="dtL1">Principal</div>
                        </div>

                        <div class="finnova-breakdown-box">
                            <div class="finnova-breakdown-amount">
                                <span id="dtB2"><?= Security::formatMoney($firstF['profit_markup_amount'] ?? 0) ?></span>
                                <span class="arrow-icon">↗</span>
                            </div>
                            <div class="finnova-breakdown-label" id="dtL2">Murabaha Profit</div>
                        </div>

                        <div class="finnova-breakdown-box">
                            <div class="finnova-breakdown-amount">
                                <span id="dtB3"><?= Security::formatMoney((float)($firstF['total_due'] ?? 0) - (float)($firstF['outstanding_balance'] ?? 0)) ?></span>
                                <span class="arrow-icon">↗</span>
                            </div>
                            <div class="finnova-breakdown-label" id="dtL3">Repaid Amount</div>
                        </div>

                        <a id="dtViewLink" href="?route=financing_view&id=<?= (int)$firstF['id'] ?>" class="finnova-add-item-box" style="text-decoration:none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <span><?= $isSo ? 'Eeg Heshiiska' : 'View Details' ?></span>
                        </a>
                    </div>

                    <!-- Bottom Summary & Action Bar -->
                    <div class="finnova-detail-footer">
                        <div class="finnova-totals-group">
                            <div class="finnova-total-item">
                                <div class="tot-label">Total Contract Due</div>
                                <div class="tot-val" id="dtTotal"><?= Security::formatMoney($firstF['total_due'] ?? 0) ?></div>
                            </div>
                            <div class="finnova-total-item">
                                <div class="tot-label">Outstanding Balance</div>
                                <div class="tot-val" id="dtBalanceDue" style="color:#F3E2BD;"><?= Security::formatMoney($firstF['outstanding_balance'] ?? 0) ?></div>
                            </div>
                        </div>

                        <div class="finnova-detail-actions">
                            <a id="dtPdfLink" href="?route=statement_pdf&id=<?= (int)$firstF['id'] ?>" class="finnova-action-icon-pill" title="Download PDF">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
                            <a id="dtRepayLink" href="?route=repayment_record" class="finnova-payout-white-btn">
                                <?= $isSo ? 'Qabo Lacag Bixin' : 'Record Repayment' ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- 6. OPERATIONAL CHARTS & LIVE DATABASE DRILLDOWN -->
    <div id="finnovaOperationalSection" class="finnova-operational-drawer" style="margin-top:26px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
            <div>
                <h3 style="font-size:1.05rem;font-weight:700;margin:0;color:var(--text-main,#16192C);">
                    <?= $isSo ? 'Xogta Tooska ah ee Database-ka (Live SQL Metrics)' : 'Live Database SQL Metrics & Analytics' ?>
                </h3>
                <p style="font-size:0.78rem;color:var(--text-muted,#7E849C);margin:3px 0 0 0;">
                    <?= $isSo ? 'Kormeerka Portfolio-ga dhabta ah, Bixinta lacagaha, iyo Xisaabinta Sharciga ah' : 'Real-time loan portfolio status, PAR30 risk, and Murabaha analytics.' ?>
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="badge badge-gold" style="font-weight:700;">Live SQL</span>
                <a href="?route=financings" class="btn btn-sm btn-outline"><?= Security::e(Lang::get('btn_view_all')) ?></a>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
            <div style="background:#FFF;border-radius:16px;padding:18px;border:1px solid #EFF1F7;">
                <h4 style="font-size:0.88rem;margin:0 0 10px 0;font-weight:700;"><?= Security::e(Lang::get('chart_monthly_title')) ?></h4>
                <div style="height:230px;position:relative;">
                    <canvas id="chartMonthlyRepayments"></canvas>
                </div>
            </div>

            <div style="background:#FFF;border-radius:16px;padding:18px;border:1px solid #EFF1F7;">
                <h4 style="font-size:0.88rem;margin:0 0 10px 0;font-weight:700;"><?= Security::e(Lang::get('chart_branch_title')) ?></h4>
                <div style="height:230px;position:relative;">
                    <canvas id="chartProductMix"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CLIENT-SIDE INTERACTIVITY SCRIPT -->
<script>
function selectFinnovaInvoice(el) {
    document.querySelectorAll('.finnova-inv-row').forEach(r => r.classList.remove('active'));
    el.classList.add('active');

    const inv = el.getAttribute('data-inv') || '';
    const id = el.getAttribute('data-id') || '0';
    const amount = el.getAttribute('data-amount') || '$0.00';
    const status = el.getAttribute('data-status') || '';
    const company = el.getAttribute('data-company') || '';
    const customer = el.getAttribute('data-customer') || '';
    const role = el.getAttribute('data-role') || '';
    const b1 = el.getAttribute('data-b1') || '$0.00';
    const l1 = el.getAttribute('data-l1') || 'Principal';
    const b2 = el.getAttribute('data-b2') || '$0.00';
    const l2 = el.getAttribute('data-l2') || 'Profit';
    const b3 = el.getAttribute('data-b3') || '$0.00';
    const l3 = el.getAttribute('data-l3') || 'Repaid';
    const bal = el.getAttribute('data-bal') || '$0.00';

    const invIdEl = document.getElementById('dtInvId');
    if (invIdEl) invIdEl.textContent = '# ' + inv;
    const invStatusEl = document.getElementById('dtInvStatus');
    if (invStatusEl) invStatusEl.textContent = status;
    const compEl = document.getElementById('dtInvCompany');
    if (compEl) compEl.textContent = company;
    const custEl = document.getElementById('dtCustomerName');
    if (custEl) custEl.textContent = customer;
    const roleEl = document.getElementById('dtCustomerRole');
    if (roleEl) roleEl.textContent = role;
    const initEl = document.getElementById('dtCustomerInitials');
    if (initEl) initEl.textContent = customer.substring(0, 2).toUpperCase();

    const b1El = document.getElementById('dtB1');
    if (b1El) b1El.textContent = b1;
    const b2El = document.getElementById('dtB2');
    if (b2El) b2El.textContent = b2;
    const b3El = document.getElementById('dtB3');
    if (b3El) b3El.textContent = b3;

    const totEl = document.getElementById('dtTotal');
    if (totEl) totEl.textContent = amount;
    const balEl = document.getElementById('dtBalanceDue');
    if (balEl) balEl.textContent = bal;

    const viewLink = document.getElementById('dtViewLink');
    if (viewLink) viewLink.href = '?route=financing_view&id=' + id;
    const pdfLink = document.getElementById('dtPdfLink');
    if (pdfLink) pdfLink.href = '?route=statement_pdf&id=' + id;
}

function filterFinnovaTabs(type, btn) {
    document.querySelectorAll('.finnova-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const rows = document.querySelectorAll('.finnova-inv-row');
    rows.forEach(r => {
        if (type === 'all') {
            r.style.display = 'flex';
        } else if (type === 'active') {
            const status = (r.getAttribute('data-status') || '').toLowerCase();
            r.style.display = (status === 'active' || status === 'approved') ? 'flex' : 'none';
        } else if (type === 'overdue') {
            const status = (r.getAttribute('data-status') || '').toLowerCase();
            r.style.display = (status === 'overdue') ? 'flex' : 'none';
        }
    });
}

function toggleOperationalDrawer() {
    const d = document.getElementById('finnovaOperationalSection');
    if (!d) return;
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('finnovaInvoiceSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.finnova-inv-row').forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(q) ? 'flex' : 'none';
            });
        });
    }

    if (typeof Chart === 'undefined') return;

    const monthlyData = <?= json_encode($m['charts']['monthly_repayments'] ?? []) ?>;
    const byProduct = <?= json_encode($m['charts']['by_product_type'] ?? []) ?>;

    const ctx1 = document.getElementById('chartMonthlyRepayments');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: monthlyData.length ? monthlyData.map(r => r.ym) : ['No Repayments Yet'],
                datasets: [
                    {
                        label: 'Principal ($)',
                        data: monthlyData.length ? monthlyData.map(r => parseFloat(r.principal_collected)) : [0],
                        backgroundColor: '#123B32',
                        borderRadius: 6
                    },
                    {
                        label: 'Markup / Profit ($)',
                        data: monthlyData.length ? monthlyData.map(r => parseFloat(r.profit_collected)) : [0],
                        backgroundColor: '#C59B47',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const ctx2 = document.getElementById('chartProductMix');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: byProduct.length ? byProduct.map(p => p.product_type.toUpperCase()) : ['No Products Active'],
                datasets: [{
                    data: byProduct.length ? byProduct.map(p => parseFloat(p.total_principal)) : [0],
                    backgroundColor: ['#123B32', '#C59B47', '#1E6B5C']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
