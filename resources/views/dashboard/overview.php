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
?>

<div class="finnova-canvas animate-in">
    <!-- 1. TOP CAPSULE NAVBAR -->
    <header class="finnova-top-nav">
        <!-- Brand & Counter Badge -->
        <div class="finnova-brand-group">
            <a href="?route=dashboard" class="finnova-brand-logo" style="text-decoration:none;">
                <div class="finnova-brand-symbol">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5"></path>
                    </svg>
                </div>
                <div class="finnova-brand-meta">
                    <h2>FINNOVA</h2>
                    <p>Smart Finances, Better Business</p>
                </div>
            </a>
            <span class="finnova-badge-count">80</span>
        </div>

        <!-- Center Capsule Navigation Menu -->
        <nav class="finnova-menu-capsule" aria-label="Finnova Quick Menu">
            <a href="?route=dashboard" class="finnova-menu-link active">
                <span class="bullet"></span> Overview
            </a>
            <a href="?route=applications" class="finnova-menu-link">
                Estimates
            </a>
            <a href="?route=financings" class="finnova-menu-link">
                <span class="bullet"></span> Invoices
            </a>
            <a href="?route=repayments" class="finnova-menu-link">
                Payments
            </a>
            <a href="?route=schedules" class="finnova-menu-link">
                Recurring
            </a>
            <a href="?route=clients" class="finnova-menu-link">
                Checkouts
            </a>
        </nav>

        <!-- Right Utility Icons & Profile -->
        <div class="finnova-utils-group">
            <a href="?route=statements" class="finnova-util-btn" title="Statements & Reports">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </a>
            <a href="?route=products" class="finnova-util-btn" title="Instant Quick Actions">
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
            <a href="?route=charity_charges" class="finnova-util-btn" title="Vault & Charity Bag">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </a>
            <button type="button" class="finnova-util-btn" title="Filters & View Controls" onclick="toggleOperationalDrawer()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="21" x2="4" y2="14"></line>
                    <line x1="4" y1="10" x2="4" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12" y2="3"></line>
                    <line x1="20" y1="21" x2="20" y2="16"></line>
                    <line x1="20" y1="12" x2="20" y2="3"></line>
                    <line x1="1" y1="14" x2="7" y2="14"></line>
                    <line x1="9" y1="8" x2="15" y2="8"></line>
                    <line x1="17" y1="16" x2="23" y2="16"></line>
                </svg>
            </button>
            <a href="?route=approval_queue" class="finnova-util-btn" title="Notifications & Queue">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span class="dot-alert"></span>
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
            <a href="?route=home" class="finnova-back-circle" title="Back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div class="finnova-title-meta">
                <h1>Invoices</h1>
                <p>Manage and track all your invoices in one place.</p>
            </div>
        </div>

        <div class="finnova-title-actions">
            <button type="button" class="finnova-filter-icon-btn" title="Filter View" onclick="toggleOperationalDrawer()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                    <line x1="10" y1="18" x2="14" y2="18"></line>
                </svg>
            </button>
            <a href="?route=client_register" class="finnova-create-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Create an invoice</span>
            </a>
        </div>
    </div>

    <!-- 3. TOP 4 KPI CARDS GRID -->
    <div class="finnova-kpi-grid">
        <!-- CARD 1: OVERDUE -->
        <div class="finnova-kpi-card">
            <div>
                <div class="finnova-kpi-top">
                    <span class="finnova-kpi-title">Overdue</span>
                    <span class="finnova-kpi-icon-pill red">!</span>
                </div>
                <div class="finnova-kpi-val">$ 24,850.00</div>
                <div class="finnova-kpi-trend red">
                    <span>↑ 12.5%</span> from last month
                </div>
            </div>
            <!-- Minimalist Desk Illustration (Vector SVG matching reference image) -->
            <div class="finnova-card-art desk-art">
                <svg width="100%" height="80" viewBox="0 0 260 80" fill="none" preserveAspectRatio="xMidYEnd">
                    <!-- Desk Table Surface -->
                    <rect x="10" y="66" width="240" height="6" rx="2" fill="#E8DEC8"/>
                    <rect x="25" y="72" width="6" height="8" rx="1" fill="#C5BAA2"/>
                    <rect x="229" y="72" width="6" height="8" rx="1" fill="#C5BAA2"/>
                    <!-- Potted Plant on Left -->
                    <path d="M42 44 C42 44, 30 30, 36 20 C42 28, 44 38, 44 44 Z" fill="#2E6B47"/>
                    <path d="M45 44 C45 44, 40 18, 48 10 C53 22, 49 38, 45 44 Z" fill="#3D8B5D"/>
                    <path d="M47 44 C47 44, 60 26, 56 16 C50 25, 48 37, 47 44 Z" fill="#2E6B47"/>
                    <path d="M41 46 L51 46 L49 66 L43 66 Z" fill="#FFFFFF" stroke="#D8DFE8" stroke-width="1"/>
                    <!-- Laptop in Center -->
                    <rect x="88" y="22" width="64" height="42" rx="3" fill="#20232E"/>
                    <rect x="91" y="25" width="58" height="36" rx="1" fill="#151722"/>
                    <!-- Screen Mockup lines -->
                    <rect x="96" y="30" width="24" height="2" rx="1" fill="#5B4EF5"/>
                    <rect x="96" y="34" width="36" height="2" rx="1" fill="#66708A"/>
                    <rect x="96" y="38" width="18" height="2" rx="1" fill="#66708A"/>
                    <!-- Laptop Base -->
                    <path d="M80 64 L160 64 L154 66 L86 66 Z" fill="#9DA4B5"/>
                    <!-- Coffee Mug -->
                    <rect x="168" y="52" width="10" height="14" rx="2" fill="#1E2028"/>
                    <path d="M178 55 C181 55, 181 61, 178 61" stroke="#1E2028" stroke-width="2" fill="none"/>
                    <!-- Minimalist Desk Lamp on Right -->
                    <ellipse cx="215" cy="65" rx="8" ry="2" fill="#20232E"/>
                    <path d="M215 65 L215 48 L198 32" stroke="#20232E" stroke-width="2.5" stroke-linecap="round"/>
                    <polygon points="194,26 206,34 196,44 186,36" fill="#1C1E26"/>
                </svg>
            </div>
        </div>

        <!-- CARD 2: DUE WITHIN NEXT MONTH -->
        <div class="finnova-kpi-card">
            <div>
                <div class="finnova-kpi-top">
                    <span class="finnova-kpi-title">Due within next month</span>
                    <span class="finnova-kpi-icon-pill purple">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                </div>
                <div class="finnova-kpi-val">$ 142,560.00</div>
                <div class="finnova-kpi-trend purple">
                    <span>↑ 8.2%</span> from last month
                </div>
            </div>
            <!-- Vertical Bars Art (Jul-Dec) -->
            <div class="finnova-card-art">
                <svg width="100%" height="82" viewBox="0 0 240 82" fill="none" preserveAspectRatio="xMidYEnd">
                    <!-- Jul -->
                    <rect x="15" y="48" width="16" height="24" rx="4" fill="#CDC9FA"/>
                    <text x="23" y="80" text-anchor="middle" font-size="9" fill="#8E94A8" font-family="sans-serif">Jul</text>
                    <!-- Aug -->
                    <rect x="53" y="38" width="16" height="34" rx="4" fill="#CDC9FA"/>
                    <text x="61" y="80" text-anchor="middle" font-size="9" fill="#8E94A8" font-family="sans-serif">Aug</text>
                    <!-- Sep -->
                    <rect x="91" y="44" width="16" height="28" rx="4" fill="#CDC9FA"/>
                    <text x="99" y="80" text-anchor="middle" font-size="9" fill="#8E94A8" font-family="sans-serif">Sep</text>
                    <!-- Oct -->
                    <rect x="129" y="24" width="16" height="48" rx="4" fill="#CDC9FA"/>
                    <text x="137" y="80" text-anchor="middle" font-size="9" fill="#8E94A8" font-family="sans-serif">Oct</text>
                    <!-- Nov (Active Tall) -->
                    <rect x="167" y="8" width="16" height="64" rx="4" fill="url(#barGradNov)"/>
                    <text x="175" y="80" text-anchor="middle" font-size="9" fill="#8E94A8" font-family="sans-serif">Nov</text>
                    <!-- Dec (Tall) -->
                    <rect x="205" y="16" width="16" height="56" rx="4" fill="url(#barGradNov)"/>
                    <text x="213" y="80" text-anchor="middle" font-size="9" fill="#8E94A8" font-family="sans-serif">Dec</text>
                    <defs>
                        <linearGradient id="barGradNov" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#5B4EF5"/>
                            <stop offset="100%" stop-color="#7B6FF8"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- CARD 3: AVERAGE TIME TO GET PAID -->
        <div class="finnova-kpi-card">
            <div>
                <div class="finnova-kpi-top">
                    <span class="finnova-kpi-title">Average time to get paid</span>
                    <span class="finnova-kpi-icon-pill teal">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </span>
                </div>
                <div class="finnova-kpi-val">16 days</div>
                <div class="finnova-kpi-trend teal">
                    <span>↓ 2 days</span> from last month
                </div>
            </div>
            <!-- Curved Spline Line Art with circular nodes -->
            <div class="finnova-card-art">
                <svg width="100%" height="82" viewBox="0 0 240 82" fill="none" preserveAspectRatio="xMidYEnd">
                    <defs>
                        <linearGradient id="curveGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#5B4EF5" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="#5B4EF5" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <!-- Gradient Area Fill -->
                    <path d="M 10 68 C 45 64, 75 58, 105 46 C 135 34, 165 42, 195 24 C 210 16, 225 14, 235 12 L 235 80 L 10 80 Z" fill="url(#curveGradient)"/>
                    <!-- Smooth Curved Line -->
                    <path d="M 10 68 C 45 64, 75 58, 105 46 C 135 34, 165 42, 195 24 C 210 16, 225 14, 235 12" stroke="#5B4EF5" stroke-width="2.6" stroke-linecap="round" fill="none"/>
                    <!-- Data Nodes -->
                    <circle cx="10" cy="68" r="4.5" fill="#FFFFFF" stroke="#5B4EF5" stroke-width="2"/>
                    <circle cx="45" cy="64" r="4.5" fill="#FFFFFF" stroke="#5B4EF5" stroke-width="2"/>
                    <circle cx="75" cy="58" r="4.5" fill="#FFFFFF" stroke="#5B4EF5" stroke-width="2"/>
                    <circle cx="105" cy="46" r="4.5" fill="#FFFFFF" stroke="#5B4EF5" stroke-width="2"/>
                    <circle cx="140" cy="38" r="4.5" fill="#FFFFFF" stroke="#5B4EF5" stroke-width="2"/>
                    <circle cx="175" cy="34" r="4.5" fill="#FFFFFF" stroke="#5B4EF5" stroke-width="2"/>
                    <circle cx="205" cy="20" r="4.5" fill="#FFFFFF" stroke="#5B4EF5" stroke-width="2"/>
                    <circle cx="235" cy="12" r="5" fill="#5B4EF5" stroke="#FFFFFF" stroke-width="2"/>
                </svg>
            </div>
        </div>

        <!-- CARD 4: AVAILABLE FOR INSTANT PAYOUT -->
        <div class="finnova-kpi-card">
            <div>
                <div class="finnova-kpi-top">
                    <span class="finnova-kpi-title">Available for Instant Payout</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="finnova-kpi-icon-pill green">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <a href="?route=disbursements" class="finnova-util-btn" style="width:24px;height:24px;border-radius:50%;" title="Payout Details">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                <polyline points="7 7 17 7 17 17"></polyline>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="finnova-kpi-val">$ 186,540.00</div>
                <div style="font-size:0.78rem;color:#7E849C;font-weight:600;margin-bottom:10px;">Expects</div>
            </div>

            <!-- 3 Mini Payment Method Cards & Payout Button -->
            <div>
                <div class="finnova-payout-box">
                    <div class="finnova-mini-method">
                        <span class="card-dots">•••• 4242</span>
                        <span class="card-brand" style="color:#2D5BE3;">Visa</span>
                    </div>
                    <div class="finnova-mini-method active">
                        <span class="card-dots">•••• 6789</span>
                        <span class="card-brand">Stripe</span>
                    </div>
                    <div class="finnova-mini-method">
                        <span class="card-dots">•••• 1234</span>
                        <span class="card-brand" style="color:#0079C1;">PayPal</span>
                    </div>
                    <a href="?route=disbursements" class="finnova-payout-now-btn">Payout now</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. ACTIVE FILTERS BAR -->
    <div class="finnova-filter-bar">
        <div class="finnova-filters-left">
            <div class="finnova-filter-badge">
                <span>Active filters</span>
                <span class="badge-circle">2</span>
            </div>
            <button type="button" class="finnova-filter-pill">
                <span>All customers</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <button type="button" class="finnova-filter-pill">
                <span>All statuses</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <button type="button" class="finnova-filter-pill">
                <span>November 2026</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </button>
            <button type="button" class="finnova-filter-pill">
                <span>December 2026</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </button>
        </div>

        <div class="finnova-search-capsule">
            <input type="text" id="finnovaInvoiceSearch" placeholder="Enter invoice #" aria-label="Search Invoices">
            <span class="search-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
        </div>
    </div>

    <!-- 5. SIGNATURE DARK MASTER-DETAIL CONTAINER -->
    <section class="finnova-dark-section">
        <!-- Top bar inside dark card -->
        <div class="finnova-dark-header">
            <h3 class="finnova-dark-title">Unpaid Invoices</h3>

            <!-- Centered Capsule Tab Switcher -->
            <div class="finnova-tabs-capsule">
                <button type="button" class="finnova-tab-btn" onclick="filterFinnovaTabs('all', this)">All Invoices</button>
                <button type="button" class="finnova-tab-btn" onclick="filterFinnovaTabs('draft', this)">Draft <span style="opacity:0.75;">3</span></button>
                <button type="button" class="finnova-tab-btn active" onclick="filterFinnovaTabs('unpaid', this)">Unpaid <span style="background:rgba(255,255,255,0.25);border-radius:50%;width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;font-size:0.72rem;">5</span></button>
            </div>

            <!-- Right icons -->
            <div class="finnova-dark-actions">
                <button type="button" class="finnova-dark-icon-btn" title="Toggle Split View">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                </button>
                <button type="button" class="finnova-dark-icon-btn" title="More Options">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                </button>
            </div>
        </div>

        <!-- 2-COLUMN SPLIT MASTER-DETAIL -->
        <div class="finnova-dark-split">
            <!-- LEFT COLUMN: 5 INVOICE ROWS -->
            <div class="finnova-invoices-list" id="finnovaInvoiceList">
                <!-- ROW 1 -->
                <div class="finnova-inv-row" data-inv="INV-1001" data-amount="$ 68,750.00" data-status="Unsent" data-due="In 2 days" data-company="Skyline Media" data-customer="Sarah Jenkins" data-role="Creative Director" data-b1="$ 25,000.00" data-l1="Brand Identity" data-b2="$ 32,500.00" data-l2="Campaign Asset" data-b3="$ 11,250.00" data-l3="Motion Video" onclick="selectFinnovaInvoice(this)">
                    <div class="finnova-inv-left">
                        <div class="finnova-inv-avatar" style="background:#DE7B88;">
                            <span>SJ</span>
                        </div>
                        <div>
                            <div class="finnova-inv-id"># INV-1001</div>
                            <div class="finnova-inv-due">In 2 days</div>
                        </div>
                    </div>
                    <div class="finnova-inv-right">
                        <span class="finnova-inv-status">Unsent</span>
                        <span class="finnova-inv-amount">$ 68,750.00</span>
                    </div>
                </div>

                <!-- ROW 2 -->
                <div class="finnova-inv-row" data-inv="INV-1002" data-amount="$ 21,480.00" data-status="Viewed" data-due="In 4 days" data-company="Apex Logistics" data-customer="Ahmed Omar" data-role="Operations Lead" data-b1="$ 8,400.00" data-l1="Fleet Routing" data-b2="$ 9,580.00" data-l2="Fuel Management" data-b3="$ 3,500.00" data-l3="Support Srv" onclick="selectFinnovaInvoice(this)">
                    <div class="finnova-inv-left">
                        <div class="finnova-inv-avatar" style="background:#5C87E5;">
                            <span>AO</span>
                        </div>
                        <div>
                            <div class="finnova-inv-id"># INV-1002</div>
                            <div class="finnova-inv-due">In 4 days</div>
                        </div>
                    </div>
                    <div class="finnova-inv-right">
                        <span class="finnova-inv-status">Viewed</span>
                        <span class="finnova-inv-amount">$ 21,480.00</span>
                    </div>
                </div>

                <!-- ROW 3 (DEFAULT ACTIVE AS IN SCREENSHOT) -->
                <div class="finnova-inv-row active" data-inv="INV-1003" data-amount="$ 47,980.00" data-status="Unsent" data-due="In 5 days" data-company="BrightWave ༄" data-customer="James Carter" data-role="Marketing Director" data-b1="$ 15,990.00" data-l1="UI/UX Design" data-b2="$ 21,250.00" data-l2="Development" data-b3="$ 10,740.00" data-l3="QA & Testing" onclick="selectFinnovaInvoice(this)">
                    <div class="finnova-inv-left">
                        <div class="finnova-inv-avatar" style="background:#7F56D9;">
                            <span>JC</span>
                        </div>
                        <div>
                            <div class="finnova-inv-id"># INV-1003</div>
                            <div class="finnova-inv-due">In 5 days</div>
                        </div>
                    </div>
                    <div class="finnova-inv-right">
                        <span class="finnova-inv-status">Unsent</span>
                        <span class="finnova-inv-amount">$ 47,980.00</span>
                    </div>
                </div>

                <!-- ROW 4 -->
                <div class="finnova-inv-row" data-inv="INV-1004" data-amount="$ 55,230.00" data-status="Viewed" data-due="In 16 days" data-company="Nordic Retail" data-customer="Layla Farah" data-role="Procurement VP" data-b1="$ 22,000.00" data-l1="Murabaha Inventory" data-b2="$ 24,500.00" data-l2="POS Terminals" data-b3="$ 8,730.00" data-l3="Storage Units" onclick="selectFinnovaInvoice(this)">
                    <div class="finnova-inv-left">
                        <div class="finnova-inv-avatar" style="background:#E29547;">
                            <span>LF</span>
                        </div>
                        <div>
                            <div class="finnova-inv-id"># INV-1004</div>
                            <div class="finnova-inv-due">In 16 days</div>
                        </div>
                    </div>
                    <div class="finnova-inv-right">
                        <span class="finnova-inv-status">Viewed</span>
                        <span class="finnova-inv-amount">$ 55,230.00</span>
                    </div>
                </div>

                <!-- ROW 5 -->
                <div class="finnova-inv-row" data-inv="INV-1005" data-amount="$ 6,880.00" data-status="Viewed" data-due="In 19 days" data-company="Karan Store" data-customer="Hassan Jama" data-role="Retail Owner" data-b1="$ 3,200.00" data-l1="Store Fixtures" data-b2="$ 2,400.00" data-l2="Solar Backup" data-b3="$ 1,280.00" data-l3="Murabaha Profit" onclick="selectFinnovaInvoice(this)">
                    <div class="finnova-inv-left">
                        <div class="finnova-inv-avatar" style="background:#3EB87F;">
                            <span>HJ</span>
                        </div>
                        <div>
                            <div class="finnova-inv-id"># INV-1005</div>
                            <div class="finnova-inv-due">In 19 days</div>
                        </div>
                    </div>
                    <div class="finnova-inv-right">
                        <span class="finnova-inv-status">Viewed</span>
                        <span class="finnova-inv-amount">$ 6,880.00</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: RICH INTERACTIVE DETAIL CARD -->
            <div class="finnova-detail-card" id="finnovaDetailCard">
                <!-- Header Details -->
                <div class="finnova-detail-header">
                    <div>
                        <div class="finnova-detail-col-label">Invoice details</div>
                        <div class="finnova-detail-inv-title">
                            <h2 id="dtInvId"># INV-1003</h2>
                            <span class="finnova-detail-status-pill" id="dtInvStatus">Unsent</span>
                        </div>
                    </div>

                    <div>
                        <div class="finnova-detail-col-label">Company</div>
                        <div class="finnova-company-box">
                            <span class="finnova-company-name" id="dtInvCompany">BrightWave ༄</span>
                        </div>
                    </div>

                    <div>
                        <div class="finnova-detail-col-label">Customer</div>
                        <div class="finnova-customer-box">
                            <div class="finnova-customer-avatar" id="dtCustomerAvatar">
                                <span style="color:#5B4EF5;font-weight:700;font-size:0.85rem;" id="dtCustomerInitials">JC</span>
                            </div>
                            <div>
                                <div class="finnova-customer-name" id="dtCustomerName">James Carter</div>
                                <div class="finnova-customer-role" id="dtCustomerRole">Marketing Director</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3 Breakdown Boxes + Add Item Box -->
                <div class="finnova-breakdown-grid">
                    <div class="finnova-breakdown-box">
                        <div class="finnova-breakdown-amount">
                            <span id="dtB1">$ 15,990.00</span>
                            <span class="arrow-icon">↗</span>
                        </div>
                        <div class="finnova-breakdown-label" id="dtL1">UI/UX Design</div>
                    </div>

                    <div class="finnova-breakdown-box">
                        <div class="finnova-breakdown-amount">
                            <span id="dtB2">$ 21,250.00</span>
                            <span class="arrow-icon">↗</span>
                        </div>
                        <div class="finnova-breakdown-label" id="dtL2">Development</div>
                    </div>

                    <div class="finnova-breakdown-box">
                        <div class="finnova-breakdown-amount">
                            <span id="dtB3">$ 10,740.00</span>
                            <span class="arrow-icon">↗</span>
                        </div>
                        <div class="finnova-breakdown-label" id="dtL3">QA & Testing</div>
                    </div>

                    <div class="finnova-add-item-box" onclick="alert('Add custom line item feature')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Add item</span>
                    </div>
                </div>

                <!-- Bottom Summary & Action Bar -->
                <div class="finnova-detail-footer">
                    <div class="finnova-totals-group">
                        <div class="finnova-total-item">
                            <div class="tot-label">Sub Total</div>
                            <div class="tot-val" id="dtSubTotal">$ 47,980.00</div>
                        </div>
                        <div class="finnova-total-item">
                            <div class="tot-label">Total</div>
                            <div class="tot-val" id="dtTotal">$ 47,980.00</div>
                        </div>
                        <div class="finnova-total-item">
                            <div class="tot-label">Balance Due</div>
                            <div class="tot-val" id="dtBalanceDue">$ 47,980.00</div>
                        </div>
                    </div>

                    <div class="finnova-detail-actions">
                        <button type="button" class="finnova-action-icon-pill" title="Attachment Link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        </button>
                        <button type="button" class="finnova-action-icon-pill" title="Schedule">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </button>
                        <a href="?route=disbursements" class="finnova-payout-white-btn">Payout now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. OPERATIONAL CHARTS & LIVE DATABASE DRILLDOWN (EXPANDABLE) -->
    <div id="finnovaOperationalSection" class="finnova-operational-drawer" style="margin-top:26px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
            <div>
                <h3 style="font-size:1.05rem;font-weight:700;margin:0;color:var(--text-main,#16192C);">
                    <?= $isSo ? 'Xogta Tooska ah ee Database-ka (Live SQL Metrics)' : 'Live Database SQL Metrics & Institutional Reports' ?>
                </h3>
                <p style="font-size:0.78rem;color:var(--text-muted,#7E849C);margin:3px 0 0 0;">
                    <?= $isSo ? 'Kormeerka Portfolio-ga dhabta ah, Bixinta lacagaha, iyo Xisaabinta Sharciga ah' : 'Real-time loan portfolio status, dual approvals, PAR30, and Murabaha analytics.' ?>
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

    const inv = el.getAttribute('data-inv') || '# INV-1003';
    const amount = el.getAttribute('data-amount') || '$ 47,980.00';
    const status = el.getAttribute('data-status') || 'Unsent';
    const company = el.getAttribute('data-company') || 'BrightWave ༄';
    const customer = el.getAttribute('data-customer') || 'James Carter';
    const role = el.getAttribute('data-role') || 'Marketing Director';
    const b1 = el.getAttribute('data-b1') || '$ 15,990.00';
    const l1 = el.getAttribute('data-l1') || 'UI/UX Design';
    const b2 = el.getAttribute('data-b2') || '$ 21,250.00';
    const l2 = el.getAttribute('data-l2') || 'Development';
    const b3 = el.getAttribute('data-b3') || '$ 10,740.00';
    const l3 = el.getAttribute('data-l3') || 'QA & Testing';

    document.getElementById('dtInvId').textContent = '# ' + inv.replace('#', '').trim();
    document.getElementById('dtInvStatus').textContent = status;
    document.getElementById('dtInvCompany').textContent = company;
    document.getElementById('dtCustomerName').textContent = customer;
    document.getElementById('dtCustomerRole').textContent = role;
    document.getElementById('dtCustomerInitials').textContent = customer.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

    document.getElementById('dtB1').textContent = b1;
    document.getElementById('dtL1').textContent = l1;
    document.getElementById('dtB2').textContent = b2;
    document.getElementById('dtL2').textContent = l2;
    document.getElementById('dtB3').textContent = b3;
    document.getElementById('dtL3').textContent = l3;

    document.getElementById('dtSubTotal').textContent = amount;
    document.getElementById('dtTotal').textContent = amount;
    document.getElementById('dtBalanceDue').textContent = amount;
}

function filterFinnovaTabs(type, btn) {
    document.querySelectorAll('.finnova-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const rows = document.querySelectorAll('.finnova-inv-row');
    rows.forEach(r => {
        if (type === 'all') {
            r.style.display = 'flex';
        } else if (type === 'draft') {
            const status = (r.getAttribute('data-status') || '').toLowerCase();
            r.style.display = (status === 'draft' || status === 'viewed') ? 'flex' : 'none';
        } else if (type === 'unpaid') {
            r.style.display = 'flex';
        }
    });
}

function toggleOperationalDrawer() {
    const d = document.getElementById('finnovaOperationalSection');
    if (!d) return;
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Search input live filtering
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

    // Sidebar toggle hook
    const toggleBtn = document.getElementById('finnovaSidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.app-shell')?.classList.toggle('sidebar-collapsed');
            document.querySelector('.sidebar')?.classList.toggle('mobile-open');
        });
    }

    // Chart.js initialization for operational section
    if (typeof Chart === 'undefined') return;

    const monthlyData = <?= json_encode($m['charts']['monthly_repayments'] ?? []) ?>;
    const byProduct = <?= json_encode($m['charts']['by_product_type'] ?? []) ?>;

    const ctx1 = document.getElementById('chartMonthlyRepayments');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: monthlyData.length ? monthlyData.map(r => r.ym) : ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Principal ($)',
                        data: monthlyData.length ? monthlyData.map(r => parseFloat(r.principal_collected)) : [45000, 52000, 48000, 72000, 95000, 84000],
                        backgroundColor: '#5B4EF5',
                        borderRadius: 6
                    },
                    {
                        label: 'Markup / Profit ($)',
                        data: monthlyData.length ? monthlyData.map(r => parseFloat(r.profit_collected)) : [12000, 14500, 13200, 19800, 26000, 23500],
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
                labels: byProduct.length ? byProduct.map(p => p.product_type.toUpperCase()) : ['Murabaha', 'Salam', 'Istisna'],
                datasets: [{
                    data: byProduct.length ? byProduct.map(p => parseFloat(p.total_principal)) : [65, 25, 10],
                    backgroundColor: ['#5B4EF5', '#C59B47', '#00A389']
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
