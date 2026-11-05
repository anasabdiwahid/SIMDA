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
$df = $dateFilter ?? ($m['date_filter'] ?? ['period' => 'all', 'year' => date('Y'), 'month' => date('Y-m'), 'day' => date('Y-m-d')]);
$period = $df['period'] ?? 'all';
?>

<!-- 1. WELCOME & QUICK ACTIONS STRIP -->
<div class="card animate-in" style="margin-bottom:18px;border-left:4px solid var(--simda-gold);background:var(--bg-surface);">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <h1 style="font-size:1.35rem;font-weight:800;margin:0;color:var(--text-main);">
                    <?= $isSo ? 'As-Salaamu Calaykum' : 'Welcome back' ?>, <?= Security::e($user['full_name'] ?? 'Admin') ?>
                </h1>
                <span class="badge badge-gold" style="font-size:0.72rem;font-weight:700;">
                    <?= Security::e($user['role_name_en'] ?? 'System Administrator') ?>
                </span>
            </div>
            <p style="font-size:0.84rem;color:var(--text-muted);margin:0;">
                <?= $isSo ? 'Kormeerka guud ee hawlgallada Maalgelinta Islaamiga ah, Daymaha, Murabaxada, iyo Lacagaha SIMDA.' : 'Institutional overview of SIMDA Islamic microfinance operations, Cash Loans, Murabaha, and portfolio performance.' ?>
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <a href="?route=client_register" class="btn btn-sm btn-gold" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('user-plus', 14) ?>
                <span><?= $isSo ? 'Diiwaangeli Macmiil' : 'Register Client' ?></span>
            </a>
            <a href="?route=murabaha_new" class="btn btn-sm btn-outline" style="font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('shopping-bag', 14) ?>
                <span><?= $isSo ? 'Murabaxo Cusub' : 'New Murabaha' ?></span>
            </a>
            <a href="?route=applications" class="btn btn-sm btn-outline" style="font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('file-text', 14) ?>
                <span><?= $isSo ? 'Codsi Deyn' : 'New Loan' ?></span>
            </a>
            <a href="?route=repayment_record" class="btn btn-sm btn-outline" style="font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('clock', 14) ?>
                <span><?= $isSo ? 'Qabo Bixin' : 'Record Repayment' ?></span>
            </a>
        </div>
    </div>
</div>

<!-- 2. DATE FILTER TOOLBAR (Button yar oo kala saaraya All iyo Date gooni ah) -->
<div class="card animate-in delay-1" style="margin-bottom:20px;padding:14px 18px;background:var(--bg-elevated);border:1px solid var(--border-color);">
    <form method="GET" action="index.php" id="dashboardFilterForm" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <input type="hidden" name="route" value="dashboard">

        <!-- Left: Quick Selection Buttons -->
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:0.8rem;font-weight:700;color:var(--text-main);margin-right:4px;">
                <?= Icons::svg('calendar', 14) ?> <?= $isSo ? 'Muddada:' : 'Period:' ?>
            </span>

            <button type="button" class="btn btn-sm <?= $period === 'all' ? 'btn-gold' : 'btn-outline' ?>" onclick="selectPeriod('all')" style="font-weight:700;">
                <?= $isSo ? 'Dhammaan (All Time)' : 'All Time' ?>
            </button>

            <button type="button" class="btn btn-sm <?= $period === 'year' ? 'btn-gold' : 'btn-outline' ?>" onclick="selectPeriod('year')" style="font-weight:700;">
                <?= $isSo ? 'Sanad Gooni ah (Year)' : 'By Year' ?>
            </button>

            <button type="button" class="btn btn-sm <?= $period === 'month' ? 'btn-gold' : 'btn-outline' ?>" onclick="selectPeriod('month')" style="font-weight:700;">
                <?= $isSo ? 'Bil Gooni ah (Month)' : 'By Month' ?>
            </button>

            <button type="button" class="btn btn-sm <?= $period === 'day' ? 'btn-gold' : 'btn-outline' ?>" onclick="selectPeriod('day')" style="font-weight:700;">
                <?= $isSo ? 'Maalin Cayiman (Specific Day)' : 'By Specific Day' ?>
            </button>

            <input type="hidden" name="filter_period" id="filter_period_input" value="<?= Security::e($period) ?>">
        </div>

        <!-- Right: Dynamic Date Inputs & Submit Button -->
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <!-- Year Selector -->
            <div id="yearSelectWrap" style="display:<?= $period === 'year' ? 'inline-block' : 'none' ?>;">
                <select name="filter_year" class="form-control form-control-sm" style="height:34px;padding:4px 10px;font-weight:700;background:var(--bg-surface);color:var(--text-main);">
                    <?php
                    $curY = (int) date('Y');
                    for ($y = $curY + 2; $y >= $curY - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= (int) ($df['year'] ?? $curY) === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Month Selector -->
            <div id="monthSelectWrap" style="display:<?= $period === 'month' ? 'inline-block' : 'none' ?>;">
                <input type="month" name="filter_month" class="form-control form-control-sm" style="height:34px;padding:4px 10px;font-weight:600;background:var(--bg-surface);color:var(--text-main);" value="<?= Security::e($df['month'] ?? date('Y-m')) ?>">
            </div>

            <!-- Specific Day Selector -->
            <div id="daySelectWrap" style="display:<?= $period === 'day' ? 'inline-block' : 'none' ?>;">
                <input type="date" name="filter_date" class="form-control form-control-sm" style="height:34px;padding:4px 10px;font-weight:600;background:var(--bg-surface);color:var(--text-main);" value="<?= Security::e($df['day'] ?? date('Y-m-d')) ?>">
            </div>

            <button type="submit" class="btn btn-sm btn-primary" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('filter', 13) ?>
                <span><?= $isSo ? 'Shaandhee' : 'Apply' ?></span>
            </button>

            <?php if ($period !== 'all'): ?>
                <a href="?route=dashboard" class="btn btn-sm btn-outline" style="font-size:0.75rem;" title="<?= $isSo ? 'Dib u deji dhammaan' : 'Reset to All' ?>">
                    ✕ <?= $isSo ? 'Dib u Deji' : 'Reset' ?>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Active Filter Badge Info -->
    <div style="margin-top:10px;font-size:0.76rem;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
        <span class="fid-contact-dot" style="width:7px;height:7px;background:var(--simda-gold);display:inline-block;border-radius:50%;"></span>
        <span>
            <?= $isSo ? 'Xogta hadda ku xulan:' : 'Currently displaying data for:' ?>
            <strong style="color:var(--text-main);">
                <?php
                if ($period === 'year') {
                    echo ($isSo ? 'Sanadka ' : 'Year ') . Security::e((string) $df['year']);
                } elseif ($period === 'month') {
                    $mTime = strtotime($df['month'] . '-01');
                    echo ($isSo ? 'Bisha ' : 'Month ') . ($mTime ? date('F Y', $mTime) : $df['month']);
                } elseif ($period === 'day') {
                    $dTime = strtotime($df['day']);
                    echo ($isSo ? 'Maalinta ' : 'Date ') . ($dTime ? date('d F Y', $dTime) : $df['day']);
                } else {
                    echo $isSo ? 'Dhammaan Xilliyada (All Time Database Records)' : 'All Time (Complete Database)';
                }
                ?>
            </strong>
        </span>
    </div>
</div>

<!-- 3. THE 5 CORE FINANCIAL KPI CARDS (Precisely requested by user) -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:14px;margin-bottom:22px;" class="animate-in delay-2">
    <!-- 1. INTA LACAG LA BIXIYAY (TOTAL DISBURSED) -->
    <div class="kpi-card kpi-gold" style="border-top:3px solid var(--simda-green);">
        <div class="kpi-header">
            <span class="kpi-label"><?= $isSo ? 'Inta Lacag La Bixiyay' : 'Total Disbursed' ?></span>
            <span class="kpi-icon" style="background:rgba(18,59,50,0.12);color:var(--simda-green);"><?= Icons::svg('dollar-sign', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular" style="color:var(--simda-green);"><?= Security::formatMoney($m['total_disbursed_amount']) ?></div>
        <div class="kpi-sub">
            <?= (int) ($m['disbursed_contracts_count'] ?? 0) ?> <?= $isSo ? 'heshiis oo la siiyay' : 'disbursed contracts' ?>
        </div>
    </div>

    <!-- 2. INTA LASOO CELIYAY (TOTAL REPAID) -->
    <div class="kpi-card kpi-success" style="border-top:3px solid #1FAF62;">
        <div class="kpi-header">
            <span class="kpi-label"><?= $isSo ? 'Inta La Soo Celiyay' : 'Total Repaid' ?></span>
            <span class="kpi-icon" style="background:rgba(31,175,98,0.12);color:#1FAF62;"><?= Icons::svg('check-circle', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular" style="color:#1FAF62;"><?= Security::formatMoney($m['total_repayments_collected']) ?></div>
        <div class="kpi-sub">
            <?= (int) ($m['receipts_count'] ?? 0) ?> <?= $isSo ? 'rasiidhada xarooday' : 'receipts collected' ?>
        </div>
    </div>

    <!-- 3. INTA MAQAN (TOTAL OUTSTANDING) -->
    <div class="kpi-card" style="border-top:3px solid var(--simda-gold);">
        <div class="kpi-header">
            <span class="kpi-label"><?= $isSo ? 'Inta Maqan (Portfolio)' : 'Total Outstanding' ?></span>
            <span class="kpi-icon" style="background:rgba(214,182,108,0.18);color:var(--simda-gold);"><?= Icons::svg('briefcase', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular" style="color:var(--simda-gold);"><?= Security::formatMoney($m['total_outstanding_balance']) ?></div>
        <div class="kpi-sub">
            <?= (int) $m['active_financing_count'] ?> <?= $isSo ? 'heshiis firfircoon' : 'active contracts' ?>
        </div>
    </div>

    <!-- 4. INTA WAQTIGOODA DHAWYAHAY (DUE SOON — NEXT 30 DAYS) -->
    <div class="kpi-card" style="border-top:3px solid #2872A1;">
        <div class="kpi-header">
            <span class="kpi-label"><?= $isSo ? 'Inta Waqtigoodu Dhawyahay' : 'Due Soon (30 Days)' ?></span>
            <span class="kpi-icon" style="background:rgba(40,114,161,0.12);color:#2872A1;"><?= Icons::svg('clock', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular" style="color:#2872A1;"><?= Security::formatMoney($m['due_soon_balance']) ?></div>
        <div class="kpi-sub">
            <?= (int) $m['due_soon_count'] ?> <?= $isSo ? 'heshiis xilligoodu dhowyahay' : 'contracts due within 30d' ?>
        </div>
    </div>

    <!-- 5. INTA DEADLINE-KOODA DHAMADAY OO AAN WELI LA SOO CELIN (OVERDUE & UNSETTLED) -->
    <div class="kpi-card <?= ((int) $m['overdue_unsettled_count'] > 0) ? 'kpi-danger' : '' ?>" style="border-top:3px solid #C94B4B;">
        <div class="kpi-header">
            <span class="kpi-label"><?= $isSo ? 'Waqtigii Dhacay (Aan Soo Xaroon)' : 'Overdue & Unsettled' ?></span>
            <span class="kpi-icon" style="background:rgba(201,75,75,0.12);color:#C94B4B;"><?= Icons::svg('alert-triangle', 18) ?></span>
        </div>
        <div class="kpi-value num-tabular" style="color:#C94B4B;"><?= Security::formatMoney($m['overdue_unsettled_balance']) ?></div>
        <div class="kpi-sub">
            <?= (int) $m['overdue_unsettled_count'] ?> <?= $isSo ? 'heshiis dib u dhacay' : 'overdue contracts past deadline' ?>
        </div>
    </div>
</div>

<!-- 4. KALA SAARIDDA MURABAXO IYO DEYN CAD (MURABAHA VS ZERO-RIBA CASH LOANS) -->
<div class="card animate-in delay-2" style="margin-bottom:22px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;border-bottom:1px solid var(--border-color);padding-bottom:10px;">
        <div>
            <h3 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);">
                <?= $isSo ? 'Kala Soocidda: Murabaxo vs Deyn Cad (Qard Hassan)' : 'Product Breakdown: Murabaha vs Cash Loans' ?>
            </h3>
            <div style="font-size:0.76rem;color:var(--text-muted);">
                <?= $isSo ? 'Kala saaridda tooska ah ee hantida Murabaxada iyo deynta bilaashka ah (0% Riba) ee database-ka.' : 'Side-by-side database comparison of Murabaha asset financing vs Zero-Riba cash loans.' ?>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="?route=murabaha" class="btn btn-sm btn-outline" style="font-size:0.78rem;">
                <?= Icons::svg('shopping-bag', 13) ?> <?= $isSo ? 'Bogga Murabaxada' : 'Murabaha Hub' ?> →
            </a>
            <a href="?route=loans" class="btn btn-sm btn-outline" style="font-size:0.78rem;">
                <?= Icons::svg('file-text', 13) ?> <?= $isSo ? 'Bogga Daymaha' : 'Loans Hub' ?> →
            </a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:16px;">
        <!-- BLOCK 1: MURABAXO -->
        <div style="background:var(--bg-elevated);border-radius:12px;padding:16px;border:1px solid rgba(214,182,108,0.25);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;width:24px;height:24px;"><?= Icons::svg('shopping-bag', 14) ?></span>
                    <strong style="font-size:0.96rem;color:var(--text-main);"><?= $isSo ? 'Heshiisyada Murabaxada (Murabaha)' : 'Murabaha Asset Financing' ?></strong>
                </div>
                <span class="badge badge-gold" style="font-size:0.7rem;"><?= (int) $m['murabaha']['total_count'] ?> <?= $isSo ? 'Heshiis' : 'Contracts' ?></span>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:10px;margin-top:12px;">
                <div style="background:var(--bg-surface);padding:10px;border-radius:8px;">
                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'La Bixiyay (Cost)' : 'Disbursed' ?></div>
                    <div class="num-tabular" style="font-size:1.05rem;font-weight:800;color:var(--text-main);margin-top:2px;">
                        <?= Security::formatMoney($m['murabaha']['disbursed']) ?>
                    </div>
                </div>
                <div style="background:var(--bg-surface);padding:10px;border-radius:8px;">
                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Faa\'iidada (Markup)' : 'Markup Profit' ?></div>
                    <div class="num-tabular" style="font-size:1.05rem;font-weight:800;color:var(--simda-gold);margin-top:2px;">
                        +<?= Security::formatMoney($m['murabaha']['markup']) ?>
                    </div>
                </div>
                <div style="background:var(--bg-surface);padding:10px;border-radius:8px;">
                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'La Soo Celiyay' : 'Repaid' ?></div>
                    <div class="num-tabular" style="font-size:1.05rem;font-weight:800;color:#1FAF62;margin-top:2px;">
                        <?= Security::formatMoney($m['murabaha']['collected']) ?>
                    </div>
                </div>
                <div style="background:var(--bg-surface);padding:10px;border-radius:8px;">
                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Maqan (Dhiman)' : 'Outstanding' ?></div>
                    <div class="num-tabular" style="font-size:1.05rem;font-weight:800;color:#C94B4B;margin-top:2px;">
                        <?= Security::formatMoney($m['murabaha']['outstanding']) ?>
                    </div>
                </div>
            </div>

            <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:0.78rem;color:var(--text-muted);">
                <span><?= $isSo ? 'Xaaladda:' : 'Status:' ?> <strong><?= (int) $m['murabaha']['active_count'] ?></strong> <?= $isSo ? 'socota' : 'active' ?> • <strong><?= (int) $m['murabaha']['settled_count'] ?></strong> <?= $isSo ? 'la wada bixiyay' : 'settled' ?></span>
                <a href="?route=murabaha" style="color:var(--simda-green);font-weight:700;text-decoration:underline;"><?= $isSo ? 'Eeg Dhammaan' : 'View All' ?> →</a>
            </div>
        </div>

        <!-- BLOCK 2: DEYN CAD (QARD HASSAN) -->
        <div style="background:var(--bg-elevated);border-radius:12px;padding:16px;border:1px solid rgba(18,59,50,0.2);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="fid-contact-dot" style="background:#123B32;color:#FFFFFF;width:24px;height:24px;"><?= Icons::svg('check', 14) ?></span>
                    <strong style="font-size:0.96rem;color:var(--text-main);"><?= $isSo ? 'Daymaha Tooska ah (Deyn Cad / Qard Hassan)' : 'Zero-Riba Cash Loans' ?></strong>
                </div>
                <span class="badge badge-success" style="font-size:0.7rem;">0% Riba</span>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:10px;margin-top:12px;">
                <div style="background:var(--bg-surface);padding:10px;border-radius:8px;">
                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Deynta La Siiyay' : 'Principal Disbursed' ?></div>
                    <div class="num-tabular" style="font-size:1.05rem;font-weight:800;color:var(--text-main);margin-top:2px;">
                        <?= Security::formatMoney($m['loans']['disbursed']) ?>
                    </div>
                </div>
                <div style="background:var(--bg-surface);padding:10px;border-radius:8px;">
                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Dheeraadka (Riba)' : 'Interest / Extra' ?></div>
                    <div class="num-tabular" style="font-size:1.05rem;font-weight:800;color:#1FAF62;margin-top:2px;">
                        $0.00 (0%)
                    </div>
                </div>
                <div style="background:var(--bg-surface);padding:10px;border-radius:8px;">
                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'La Soo Celiyay' : 'Total Repaid' ?></div>
                    <div class="num-tabular" style="font-size:1.05rem;font-weight:800;color:#1FAF62;margin-top:2px;">
                        <?= Security::formatMoney($m['loans']['collected']) ?>
                    </div>
                </div>
                <div style="background:var(--bg-surface);padding:10px;border-radius:8px;">
                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Maqan (Dhiman)' : 'Outstanding' ?></div>
                    <div class="num-tabular" style="font-size:1.05rem;font-weight:800;color:var(--simda-gold);margin-top:2px;">
                        <?= Security::formatMoney($m['loans']['outstanding']) ?>
                    </div>
                </div>
            </div>

            <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:0.78rem;color:var(--text-muted);">
                <span><?= $isSo ? 'Xaaladda:' : 'Status:' ?> <strong><?= (int) $m['loans']['active_count'] ?></strong> <?= $isSo ? 'firfircoon' : 'active' ?> • <strong><?= (int) $m['loans']['settled_count'] ?></strong> <?= $isSo ? 'la wada bixiyay' : 'settled' ?></span>
                <a href="?route=loans" style="color:var(--simda-green);font-weight:700;text-decoration:underline;"><?= $isSo ? 'Eeg Dhammaan' : 'View All' ?> →</a>
            </div>
        </div>
    </div>
</div>

<!-- 5. REAL DATABASE CHARTS (MURABAHO VS DEYN CAD & MONTHLY PERFORMANCE) -->
<div class="dashboard-split-grid animate-in delay-2" style="margin-bottom:22px;">
    <!-- CHART 1: COMPARISON OF MURABAHA VS CASH LOANS -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;margin:0;font-weight:700;"><?= $isSo ? 'Calaamaynta: Murabaxo vs Deyn Cad' : 'Comparison: Murabaha vs Loans' ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= $isSo ? 'Kala soocidda qiyaasta la bixiyay, la soo celiyay, iyo inta dhiman' : 'Disbursed, Collected, and Outstanding comparison' ?></div>
            </div>
            <span class="badge badge-gold" style="font-weight:700;">Live SQL</span>
        </div>
        <div style="height:260px;position:relative;">
            <canvas id="chartMurabahaVsLoans"></canvas>
        </div>
    </div>

    <!-- CHART 2: MONTHLY REPAYMENTS (PRINCIPAL VS PROFIT) -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;margin:0;font-weight:700;"><?= Security::e(Lang::get('chart_monthly_title')) ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= Security::e(Lang::get('chart_monthly_sub')) ?></div>
            </div>
            <span class="badge badge-info" style="font-weight:700;">0% Riba Audited</span>
        </div>
        <div style="height:260px;position:relative;">
            <canvas id="chartMonthlyRepayments"></canvas>
        </div>
    </div>
</div>

<!-- 6. RECENT ACTIVE FINANCINGS & RECENT REPAYMENTS TABLES -->
<div class="dashboard-split-grid animate-in delay-3">
    <!-- RECENT FINANCINGS -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;margin:0;font-weight:700;"><?= Security::e(Lang::get('menu_active_financing')) ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= $isSo ? 'Heshiisyadii ugu dambeeyay ee la diiwaangeliyay' : 'Latest active microfinance contracts' ?></div>
            </div>
            <a href="?route=financings" class="btn btn-sm btn-outline"><?= Security::e(Lang::get('btn_view_all')) ?> <?= Icons::svg('arrow-right', 14) ?></a>
        </div>
        <?php if (empty($recentFinancings)): ?>
            <div style="padding:32px 20px;text-align:center;color:var(--text-muted);">
                <div style="width:48px;height:48px;border-radius:50%;background:rgba(214,182,108,0.12);color:var(--simda-gold);display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px;">
                    <?= Icons::svg('file-text', 22) ?>
                </div>
                <div style="font-weight:700;font-size:0.92rem;color:var(--text-main);margin-bottom:4px;">
                    <?= $isSo ? 'Weli ma jiraan heshiisyo diiwaangashan' : 'No active financings yet' ?>
                </div>
                <div style="font-size:0.8rem;max-width:340px;margin:0 auto 14px auto;">
                    <?= $isSo ? 'Database-ka hadda waa nadiif. Waxaad bilaabi kartaa adoo diiwaangelinaya macmiil cusub ama codsi maalgelin.' : 'The database is clean. Register a client or process an application to start.' ?>
                </div>
                <a href="?route=client_register" class="btn btn-sm btn-gold">
                    <?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Diiwaangeli Macmiil' : 'Register Client' ?></span>
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="simda-table">
                    <thead>
                        <tr>
                            <th><?= Security::e(Lang::get('th_contract')) ?></th>
                            <th><?= Security::e(Lang::get('th_client')) ?></th>
                            <th><?= Security::e(Lang::get('th_type')) ?></th>
                            <th><?= Security::e(Lang::get('th_total_due')) ?></th>
                            <th><?= Security::e(Lang::get('th_outstanding')) ?></th>
                            <th><?= Security::e(Lang::get('th_status')) ?></th>
                            <th><?= Security::e(Lang::get('th_pdf')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentFinancings as $f): ?>
                            <tr>
                                <td class="num-tabular"><a href="?route=financing_view&id=<?= (int) $f['id'] ?>" style="font-weight:700;color:var(--simda-green);"><?= Security::e($f['financing_number']) ?></a></td>
                                <td><?= Security::e($f['client_name']) ?></td>
                                <td><span class="badge badge-gold"><?= Security::e($f['product_type']) ?></span></td>
                                <td class="num-tabular"><?= Security::formatMoney($f['total_due']) ?></td>
                                <td class="num-tabular"><strong><?= Security::formatMoney($f['outstanding_balance']) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= $f['status'] === 'completed' ? 'success' : ($f['status'] === 'overdue' ? 'danger' : 'info') ?>">
                                        <?= Security::e(Lang::get('status_' . $f['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?route=statement_pdf&id=<?= (int) $f['id'] ?>" class="btn btn-sm btn-outline"><?= Icons::svg('download', 14) ?> PDF</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- RECENT REPAYMENTS -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <div>
                <h3 style="font-size:1rem;margin:0;font-weight:700;"><?= Security::e(Lang::get('menu_repayment_history')) ?></h3>
                <div style="font-size:0.76rem;color:var(--text-muted);"><?= $isSo ? 'Lacagihii ugu dambeeyay ee soo xarooday' : 'Latest collected microfinance repayments' ?></div>
            </div>
            <a href="?route=repayments" class="btn btn-sm btn-outline"><?= Security::e(Lang::get('btn_view_all')) ?> <?= Icons::svg('arrow-right', 14) ?></a>
        </div>
        <?php if (empty($recentRepayments)): ?>
            <div style="padding:32px 20px;text-align:center;color:var(--text-muted);">
                <div style="width:48px;height:48px;border-radius:50%;background:rgba(35,132,91,0.12);color:#23845B;display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px;">
                    <?= Icons::svg('check-circle', 22) ?>
                </div>
                <div style="font-weight:700;font-size:0.92rem;color:var(--text-main);margin-bottom:4px;">
                    <?= $isSo ? 'Weli ma jiraan lacago soo xarooday' : 'No repayments collected yet' ?>
                </div>
                <div style="font-size:0.8rem;max-width:340px;margin:0 auto 14px auto;">
                    <?= $isSo ? 'Marka heshiisyada lacag laga bixiyo, rasiidhadu halkan ayay toos uga soo muuqanayaan.' : 'Repayment receipts will be logged here in real time.' ?>
                </div>
                <a href="?route=repayment_record" class="btn btn-sm btn-outline">
                    <?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Qabo Lacag Bixin' : 'Record Repayment' ?></span>
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="simda-table">
                    <thead>
                        <tr>
                            <th><?= Security::e(Lang::get('th_receipt')) ?></th>
                            <th><?= Security::e(Lang::get('th_client')) ?></th>
                            <th><?= Security::e(Lang::get('th_method')) ?></th>
                            <th><?= Security::e(Lang::get('th_paid')) ?></th>
                            <th><?= Security::e(Lang::get('th_principal_profit')) ?></th>
                            <th><?= Security::e(Lang::get('th_date')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRepayments as $r): ?>
                            <tr>
                                <td class="num-tabular"><strong><?= Security::e($r['receipt_number']) ?></strong></td>
                                <td><?= Security::e($r['client_name']) ?></td>
                                <td><span class="badge badge-info"><?= Security::e($r['payment_method']) ?></span></td>
                                <td class="num-tabular"><strong><?= Security::formatMoney($r['amount']) ?></strong></td>
                                <td class="num-tabular" style="font-size:0.76rem;">
                                    P: <?= Security::formatMoney($r['allocated_to_principal']) ?> |
                                    M: <?= Security::formatMoney($r['allocated_to_profit']) ?>
                                </td>
                                <td class="num-tabular"><?= Security::e($r['payment_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CLIENT-SIDE SCRIPTS: DATE FILTER TOGGLE & CHARTS -->
<script>
function selectPeriod(period) {
    document.getElementById('filter_period_input').value = period;
    document.getElementById('yearSelectWrap').style.display = (period === 'year') ? 'inline-block' : 'none';
    document.getElementById('monthSelectWrap').style.display = (period === 'month') ? 'inline-block' : 'none';
    document.getElementById('daySelectWrap').style.display = (period === 'day') ? 'inline-block' : 'none';

    if (period === 'all') {
        document.getElementById('dashboardFilterForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;

    // 1. Chart Murabaha vs Loans (Kala Calamaynta Murabaxo iyo Deyn Cad)
    const compData = <?= json_encode($m['charts']['murabaha_vs_loans'] ?? []) ?>;
    const ctxComp = document.getElementById('chartMurabahaVsLoans');
    if (ctxComp && compData) {
        new Chart(ctxComp, {
            type: 'bar',
            data: {
                labels: compData.categories || ['Murabaxo', 'Deyn Cad'],
                datasets: [
                    {
                        label: '<?= $isSo ? 'La Bixiyay ($)' : 'Disbursed ($)' ?>',
                        data: compData.disbursed || [0, 0],
                        backgroundColor: '#123B32',
                        borderRadius: 6
                    },
                    {
                        label: '<?= $isSo ? 'La Soo Celiyay ($)' : 'Collected ($)' ?>',
                        data: compData.collected || [0, 0],
                        backgroundColor: '#1FAF62',
                        borderRadius: 6
                    },
                    {
                        label: '<?= $isSo ? 'Maqan / Dhiman ($)' : 'Outstanding ($)' ?>',
                        data: compData.outstanding || [0, 0],
                        backgroundColor: '#D6B66C',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // 2. Chart Monthly Repayments Performance
    const monthlyData = <?= json_encode($m['charts']['monthly_repayments'] ?? []) ?>;
    const ctxMonthly = document.getElementById('chartMonthlyRepayments');
    if (ctxMonthly) {
        new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: monthlyData.length ? monthlyData.map(r => r.ym) : ['No Data Yet'],
                datasets: [
                    {
                        label: '<?= $isSo ? 'Asalka (Principal) $' : 'Principal Collected ($)' ?>',
                        data: monthlyData.length ? monthlyData.map(r => parseFloat(r.principal_collected)) : [0],
                        backgroundColor: '#123B32',
                        borderRadius: 6
                    },
                    {
                        label: '<?= $isSo ? 'Faa\'iidada (Markup) $' : 'Markup / Profit ($)' ?>',
                        data: monthlyData.length ? monthlyData.map(r => parseFloat(r.profit_collected)) : [0],
                        backgroundColor: '#D6B66C',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
});
</script>
