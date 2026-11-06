<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$user = AuthMiddleware::user() ?? [];
$m = $metrics;
$df = $dateFilter ?? ($m['date_filter'] ?? ['period' => 'all', 'year' => date('Y'), 'month' => date('Y-m'), 'day' => date('Y-m-d')]);
$period = $df['period'] ?? 'all';

$t = static fn (string $so, string $en, string $ar): string => match ($locale) {
    'so' => $so,
    'ar' => $ar,
    default => $en,
};

$periodOptions = [
    'all' => $t('Dhammaan', 'All time', 'الكل'),
    'year' => $t('Sanad', 'Year', 'سنة'),
    'month' => $t('Bil', 'Month', 'شهر'),
    'day' => $t('Maalin', 'Day', 'يوم'),
];

if ($period === 'year') {
    $periodLabel = $t('Sanadka ', 'Year ', 'سنة ') . (string) $df['year'];
} elseif ($period === 'month') {
    $monthTs = strtotime($df['month'] . '-01');
    $periodLabel = $t('Bisha ', 'Month ', 'شهر ') . ($monthTs ? date('m/Y', $monthTs) : $df['month']);
} elseif ($period === 'day') {
    $dayTs = strtotime($df['day']);
    $periodLabel = $t('Maalinta ', 'Date ', 'تاريخ ') . ($dayTs ? date('d/m/Y', $dayTs) : $df['day']);
} else {
    $periodLabel = $t('Dhammaan xilliyada', 'All time', 'كل الفترات');
}

$overdueCount = (int) $m['overdue_unsettled_count'];
$kpis = [
    [
        'tone' => 'green',
        'icon' => 'dollar-sign',
        'label' => $t('Inta Lacag La Bixiyay', 'Total Disbursed', 'إجمالي المصروف'),
        'value' => $m['total_disbursed_amount'],
        'sub' => (int) ($m['disbursed_contracts_count'] ?? 0) . ' ' . $t('heshiis oo la siiyay', 'disbursed contracts', 'عقود مصروفة'),
    ],
    [
        'tone' => 'green',
        'icon' => 'check-circle',
        'label' => $t('Inta La Soo Celiyay', 'Total Repaid', 'إجمالي المسدد'),
        'value' => $m['total_repayments_collected'],
        'sub' => (int) ($m['receipts_count'] ?? 0) . ' ' . $t('rasiid', 'receipts', 'إيصالات'),
    ],
    [
        'tone' => 'gold',
        'icon' => 'briefcase',
        'label' => $t('Inta Maqan', 'Total Outstanding', 'إجمالي المتبقي'),
        'value' => $m['total_outstanding_balance'],
        'sub' => (int) $m['active_financing_count'] . ' ' . $t('heshiis firfircoon', 'active contracts', 'عقود نشطة'),
    ],
    [
        'tone' => 'info',
        'icon' => 'clock',
        'label' => $t('Waqtigoodu Dhow (30 maalmood)', 'Due Soon (30 days)', 'مستحق قريباً (30 يوماً)'),
        'value' => $m['due_soon_balance'],
        'sub' => (int) $m['due_soon_count'] . ' ' . $t('heshiis', 'contracts', 'عقود'),
    ],
    [
        'tone' => $overdueCount > 0 ? 'danger' : 'muted',
        'icon' => 'alert-triangle',
        'label' => $t('Waqtigii Dhacay', 'Overdue', 'متأخر'),
        'value' => $m['overdue_unsettled_balance'],
        'sub' => $overdueCount . ' ' . $t('heshiis dib u dhacay', 'overdue contracts', 'عقود متأخرة'),
    ],
];

$loanProfit = (float) ($m['loans']['profit'] ?? 0);
$panels = [
    [
        'tone' => 'gold',
        'icon' => 'shopping-bag',
        'title' => $t('Murabaxo', 'Murabaha', 'المرابحة'),
        'caption' => $t('Maalgelinta hantida', 'Asset financing', 'تمويل الأصول'),
        'data' => $m['murabaha'],
        'badge' => null,
        'link' => '?route=murabaha',
        'stats' => [
            [$t('La Bixiyay', 'Disbursed', 'المصروف'), Security::formatMoney($m['murabaha']['disbursed'])],
            [$t("Faa'iidada", 'Markup', 'هامش الربح'), Security::formatMoney($m['murabaha']['markup'])],
            [$t('La Soo Celiyay', 'Repaid', 'المسدد'), Security::formatMoney($m['murabaha']['collected'])],
            [$t('Maqan', 'Outstanding', 'المتبقي'), Security::formatMoney($m['murabaha']['outstanding'])],
        ],
    ],
    [
        'tone' => 'green',
        'icon' => 'credit-card',
        'title' => $t('Deyn Cad', 'Cash Loans', 'القروض النقدية'),
        'caption' => 'Qard Hassan',
        'data' => $m['loans'],
        'badge' => $loanProfit <= 0.0 ? '0% Riba' : null,
        'link' => '?route=loans',
        'stats' => [
            [$t('La Bixiyay', 'Disbursed', 'المصروف'), Security::formatMoney($m['loans']['disbursed'])],
            [$t('Dheeraad', 'Extra', 'الزيادة'), Security::formatMoney($m['loans']['profit'] ?? '0.00')],
            [$t('La Soo Celiyay', 'Repaid', 'المسدد'), Security::formatMoney($m['loans']['collected'])],
            [$t('Maqan', 'Outstanding', 'المتبقي'), Security::formatMoney($m['loans']['outstanding'])],
        ],
    ],
];

$comparison = $m['charts']['murabaha_vs_loans'] ?? [];
$hasComparison = array_sum(array_merge($comparison['disbursed'] ?? [], $comparison['collected'] ?? [], $comparison['outstanding'] ?? [])) > 0;
$monthly = $m['charts']['monthly_repayments'] ?? [];

$chartText = [
    'locale' => $locale,
    'categories' => [$t('Murabaxo', 'Murabaha', 'المرابحة'), $t('Deyn Cad', 'Cash Loans', 'القروض النقدية')],
    'disbursed' => $t('La Bixiyay', 'Disbursed', 'المصروف'),
    'collected' => $t('La Soo Celiyay', 'Repaid', 'المسدد'),
    'outstanding' => $t('Maqan', 'Outstanding', 'المتبقي'),
    'principal' => $t('Asalka', 'Principal', 'الأصل'),
    'profit' => $t("Faa'iidada", 'Profit', 'الربح'),
    'charity' => $t('Sadaqada', 'Charity', 'الصدقة'),
    'unavailable' => $t('Shaxda lama soo rari karin. Hubi internet-ka.', 'The chart could not load. Check your connection.', 'تعذر تحميل الرسم البياني. تحقق من الاتصال.'),
];
$chartJsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;

$productTypeLabel = static fn (string $type): string => $type === 'murabaha'
    ? $t('Murabaxo', 'Murabaha', 'مرابحة')
    : $t('Deyn Cad', 'Cash Loan', 'قرض نقدي');

$renderEmpty = static function (string $icon, string $title, string $body, ?string $href = null, ?string $action = null): void {
    ?>
    <div class="dash-empty">
        <span class="dash-empty-icon"><?= Icons::svg($icon, 20) ?></span>
        <strong><?= Security::e($title) ?></strong>
        <p><?= Security::e($body) ?></p>
        <?php if ($href !== null && $action !== null): ?>
            <a href="<?= Security::e($href) ?>" class="btn btn-sm btn-outline"><?= Icons::svg('plus', 14) ?> <span><?= Security::e($action) ?></span></a>
        <?php endif; ?>
    </div>
    <?php
};
?>

<div class="dash">
    <!-- PAGE HEADING & QUICK ACTIONS -->
    <header class="dash-head animate-in">
        <div class="dash-head-text">
            <h1><?= Security::e($t('As-Salaamu Calaykum', 'Welcome back', 'السلام عليكم')) ?>, <?= Security::e($user['full_name'] ?? '') ?></h1>
            <p><?= Security::e($t('Kormeerka guud ee maalgelinta, lacag-celinta iyo khatarta SIMDA.', 'An overview of SIMDA financing, repayments and risk.', 'نظرة عامة على التمويل والسداد والمخاطر في سيمدا.')) ?></p>
        </div>
        <div class="dash-actions">
            <a href="?route=client_register" class="btn btn-sm btn-gold"><?= Icons::svg('user-plus', 14) ?> <span><?= Security::e($t('Diiwaangeli Macmiil', 'Register Client', 'تسجيل عميل')) ?></span></a>
            <a href="?route=murabaha_new" class="btn btn-sm btn-outline"><?= Icons::svg('shopping-bag', 14) ?> <span><?= Security::e($t('Murabaxo Cusub', 'New Murabaha', 'مرابحة جديدة')) ?></span></a>
            <a href="?route=applications" class="btn btn-sm btn-outline"><?= Icons::svg('file-text', 14) ?> <span><?= Security::e($t('Codsi Deyn', 'New Loan', 'طلب قرض')) ?></span></a>
            <a href="?route=repayment_record" class="btn btn-sm btn-outline"><?= Icons::svg('wallet', 14) ?> <span><?= Security::e($t('Qabo Bixin', 'Record Repayment', 'تسجيل سداد')) ?></span></a>
        </div>
    </header>

    <!-- PERIOD FILTER -->
    <form method="GET" action="" id="dashboardFilterForm" class="dash-filter animate-in delay-1">
        <input type="hidden" name="route" value="dashboard">
        <input type="hidden" name="filter_period" id="filter_period_input" value="<?= Security::e($period) ?>">

        <div class="dash-filter-row">
            <div class="dash-segment" role="group" aria-label="<?= Security::e($t('Muddada', 'Period', 'الفترة')) ?>">
                <?php foreach ($periodOptions as $value => $label): ?>
                    <button type="button" class="dash-segment-btn <?= $period === $value ? 'is-active' : '' ?>" data-period="<?= Security::e($value) ?>" aria-pressed="<?= $period === $value ? 'true' : 'false' ?>">
                        <?= Security::e($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="dash-filter-inputs">
                <select name="filter_year" class="form-control dash-period-input" data-for="year" aria-label="<?= Security::e($periodOptions['year']) ?>" <?= $period === 'year' ? '' : 'hidden' ?>>
                    <?php
                    $curY = (int) date('Y');
                    for ($y = $curY + 2; $y >= $curY - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= (int) ($df['year'] ?? $curY) === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <input type="month" name="filter_month" class="form-control dash-period-input" data-for="month" aria-label="<?= Security::e($periodOptions['month']) ?>" value="<?= Security::e($df['month'] ?? date('Y-m')) ?>" <?= $period === 'month' ? '' : 'hidden' ?>>
                <input type="date" name="filter_date" class="form-control dash-period-input" data-for="day" aria-label="<?= Security::e($periodOptions['day']) ?>" value="<?= Security::e($df['day'] ?? date('Y-m-d')) ?>" <?= $period === 'day' ? '' : 'hidden' ?>>

                <button type="submit" id="dashboardFilterApply" class="btn btn-sm btn-primary" <?= $period === 'all' ? 'hidden' : '' ?>>
                    <?= Icons::svg('filter', 13) ?> <span><?= Security::e($t('Shaandhee', 'Apply', 'تطبيق')) ?></span>
                </button>
            </div>
        </div>

        <p class="dash-filter-note">
            <strong><?= Security::e($periodLabel) ?></strong>
            <span><?= Security::e($t(
                'Muddadu waxay saameysaa oo keliya "La Bixiyay" iyo "La Soo Celiyay". Inta kale waa xaaladda hadda.',
                'The period only changes Disbursed and Repaid. Other figures show the current position.',
                'تؤثر الفترة على المصروف والمسدد فقط. أما بقية الأرقام فتعرض الوضع الحالي.'
            )) ?></span>
        </p>
    </form>

    <!-- FIVE CORE KPIS -->
    <section class="dash-kpis animate-in delay-2" aria-label="<?= Security::e($t('Tirooyinka muhiimka ah', 'Key figures', 'الأرقام الرئيسية')) ?>">
        <?php foreach ($kpis as $kpi): ?>
            <div class="kpi-card dash-kpi dash-tone-<?= Security::e($kpi['tone']) ?>">
                <div class="kpi-header">
                    <span class="kpi-label"><?= Security::e($kpi['label']) ?></span>
                    <span class="kpi-icon"><?= Icons::svg($kpi['icon'], 17) ?></span>
                </div>
                <div class="kpi-value num-tabular"><?= Security::formatMoney($kpi['value']) ?></div>
                <div class="kpi-sub"><?= Security::e($kpi['sub']) ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- PRODUCT BREAKDOWN -->
    <section class="dash-panels animate-in delay-2" aria-label="<?= Security::e($t('Kala soocidda alaabta', 'Product breakdown', 'تفصيل المنتجات')) ?>">
        <?php foreach ($panels as $panel):
            $collected = (float) $panel['data']['collected'];
            $outstanding = (float) $panel['data']['outstanding'];
            $repaidPct = ($collected + $outstanding) > 0 ? (int) round($collected / ($collected + $outstanding) * 100) : 0;
            ?>
            <article class="card dash-panel dash-tone-<?= Security::e($panel['tone']) ?>">
                <header class="dash-panel-head">
                    <span class="dash-panel-icon"><?= Icons::svg($panel['icon'], 17) ?></span>
                    <div class="dash-panel-title">
                        <h2><?= Security::e($panel['title']) ?></h2>
                        <span><?= Security::e($panel['caption']) ?></span>
                    </div>
                    <?php if ($panel['badge'] !== null): ?>
                        <span class="badge badge-success"><?= Security::e($panel['badge']) ?></span>
                    <?php endif; ?>
                    <a href="<?= Security::e($panel['link']) ?>" class="dash-link"><?= Security::e(Lang::get('btn_view_all')) ?> <?= Icons::svg('arrow-right', 13, 'dash-arrow') ?></a>
                </header>

                <dl class="dash-stats">
                    <?php foreach ($panel['stats'] as [$statLabel, $statValue]): ?>
                        <div>
                            <dt><?= Security::e($statLabel) ?></dt>
                            <dd class="num-tabular"><?= $statValue ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>

                <div class="dash-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $repaidPct ?>" aria-label="<?= Security::e($t('Boqolkiiba la soo celiyay', 'Share repaid', 'نسبة السداد')) ?>">
                    <span style="width:<?= $repaidPct ?>%"></span>
                </div>
                <div class="dash-panel-foot">
                    <span><?= (int) $panel['data']['active_count'] ?> <?= Security::e($t('socda', 'active', 'نشط')) ?> · <?= (int) $panel['data']['settled_count'] ?> <?= Security::e($t('la dhammeeyay', 'settled', 'مسدد')) ?></span>
                    <span class="num-tabular"><?= $repaidPct ?>% <?= Security::e($t('la soo celiyay', 'repaid', 'مسدد')) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <!-- CHARTS -->
    <section class="dashboard-split-grid dash-section animate-in delay-2">
        <article class="card dash-chart-card">
            <header class="dash-card-head">
                <div>
                    <h2><?= Security::e($t('Murabaxo iyo Deyn Cad', 'Murabaha and Cash Loans', 'المرابحة والقروض النقدية')) ?></h2>
                    <p><?= Security::e($t('La bixiyay, la soo celiyay iyo inta maqan', 'Disbursed, repaid and outstanding', 'المصروف والمسدد والمتبقي')) ?></p>
                </div>
            </header>
            <?php if ($hasComparison): ?>
                <div class="dash-chart" data-chart-wrap>
                    <canvas id="chartMurabahaVsLoans" role="img" aria-label="<?= Security::e($t('Murabaxo iyo Deyn Cad', 'Murabaha and Cash Loans', 'المرابحة والقروض النقدية')) ?>"></canvas>
                </div>
            <?php else:
                $renderEmpty('bar-chart', $t('Weli xog ma jirto', 'No data yet', 'لا توجد بيانات بعد'), $t('Shaxdu waxay soo muuqan doontaa marka maalgelin la bixiyo.', 'The chart appears once financing is disbursed.', 'سيظهر الرسم البياني بعد صرف التمويل.'));
            endif; ?>
        </article>

        <article class="card dash-chart-card">
            <header class="dash-card-head">
                <div>
                    <h2><?= Security::e(Lang::get('chart_monthly_title')) ?></h2>
                    <p><?= Security::e(Lang::get('chart_monthly_sub')) ?></p>
                </div>
            </header>
            <?php if (!empty($monthly)): ?>
                <div class="dash-chart" data-chart-wrap>
                    <canvas id="chartMonthlyRepayments" role="img" aria-label="<?= Security::e(Lang::get('chart_monthly_title')) ?>"></canvas>
                </div>
            <?php else:
                $renderEmpty('bar-chart', $t('Weli xog ma jirto', 'No data yet', 'لا توجد بيانات بعد'), $t('Shaxdu waxay soo muuqan doontaa marka lacag la soo celiyo.', 'The chart appears once repayments are recorded.', 'سيظهر الرسم البياني بعد تسجيل السداد.'));
            endif; ?>
        </article>
    </section>

    <!-- RECENT ACTIVITY -->
    <section class="dashboard-split-grid dash-section animate-in delay-3">
        <article class="card">
            <header class="dash-card-head">
                <div>
                    <h2><?= Security::e(Lang::get('menu_active_financing')) ?></h2>
                    <p><?= Security::e($t('Heshiisyadii ugu dambeeyay', 'Latest contracts', 'أحدث العقود')) ?></p>
                </div>
                <a href="?route=financings" class="dash-link"><?= Security::e(Lang::get('btn_view_all')) ?> <?= Icons::svg('arrow-right', 13, 'dash-arrow') ?></a>
            </header>
            <?php if (empty($recentFinancings)):
                $renderEmpty('file-text', $t('Weli heshiisyo ma jiraan', 'No contracts yet', 'لا توجد عقود بعد'), $t('Ku bilow diiwaangelinta macmiil cusub.', 'Start by registering a client.', 'ابدأ بتسجيل عميل.'), '?route=client_register', $t('Diiwaangeli Macmiil', 'Register Client', 'تسجيل عميل'));
            else: ?>
                <div class="table-responsive dash-table-wrap">
                    <table class="simda-table dash-table">
                        <thead>
                            <tr>
                                <th><?= Security::e(Lang::get('th_client')) ?></th>
                                <th class="dash-hide-sm"><?= Security::e(Lang::get('th_type')) ?></th>
                                <th class="dash-num"><?= Security::e(Lang::get('th_outstanding')) ?></th>
                                <th><?= Security::e(Lang::get('th_status')) ?></th>
                                <th><span class="dash-sr"><?= Security::e(Lang::get('th_pdf')) ?></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentFinancings as $f): ?>
                                <tr>
                                    <td>
                                        <span class="dash-primary"><?= Security::e($f['client_name']) ?></span>
                                        <a class="dash-secondary dash-ref" href="?route=financing_view&id=<?= (int) $f['id'] ?>"><?= Security::e($f['financing_number']) ?></a>
                                    </td>
                                    <td class="dash-hide-sm dash-nowrap"><?= Security::e($productTypeLabel((string) $f['product_type'])) ?></td>
                                    <td class="dash-num">
                                        <span class="dash-primary num-tabular"><?= Security::formatMoney($f['outstanding_balance']) ?></span>
                                        <span class="dash-secondary num-tabular"><?= Security::e($t('ka ', 'of ', 'من ')) ?><?= Security::formatMoney($f['total_due']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $f['status'] === 'completed' ? 'success' : ($f['status'] === 'overdue' ? 'danger' : 'info') ?>">
                                            <?= Security::e(Lang::get('status_' . $f['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="dash-icon-cell">
                                        <a href="?route=statement_pdf&id=<?= (int) $f['id'] ?>" class="dash-icon-btn" title="<?= Security::e($t('Soo deji PDF', 'Download PDF', 'تنزيل PDF')) ?>" aria-label="<?= Security::e($t('Soo deji PDF', 'Download PDF', 'تنزيل PDF')) ?> <?= Security::e($f['financing_number']) ?>"><?= Icons::svg('download', 15) ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>

        <article class="card">
            <header class="dash-card-head">
                <div>
                    <h2><?= Security::e(Lang::get('menu_repayment_history')) ?></h2>
                    <p><?= Security::e($t('Lacagihii ugu dambeeyay ee soo xarooday', 'Latest repayments', 'أحدث عمليات السداد')) ?></p>
                </div>
                <a href="?route=repayments" class="dash-link"><?= Security::e(Lang::get('btn_view_all')) ?> <?= Icons::svg('arrow-right', 13, 'dash-arrow') ?></a>
            </header>
            <?php if (empty($recentRepayments)):
                $renderEmpty('check-circle', $t('Weli lacag ma soo xaroon', 'No repayments yet', 'لا توجد عمليات سداد بعد'), $t('Rasiidhadu halkan ayay ka soo muuqan doonaan.', 'Receipts will appear here.', 'ستظهر الإيصالات هنا.'), '?route=repayment_record', $t('Qabo Bixin', 'Record Repayment', 'تسجيل سداد'));
            else: ?>
                <div class="table-responsive dash-table-wrap">
                    <table class="simda-table dash-table">
                        <thead>
                            <tr>
                                <th><?= Security::e(Lang::get('th_client')) ?></th>
                                <th class="dash-hide-sm"><?= Security::e(Lang::get('th_method')) ?></th>
                                <th class="dash-num"><?= Security::e(Lang::get('th_paid')) ?></th>
                                <th class="dash-num"><?= Security::e(Lang::get('th_date')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRepayments as $r): ?>
                                <tr>
                                    <td>
                                        <span class="dash-primary"><?= Security::e($r['client_name']) ?></span>
                                        <span class="dash-secondary dash-ref"><?= Security::e($r['receipt_number']) ?></span>
                                    </td>
                                    <td class="dash-hide-sm dash-nowrap"><?= Security::e($r['payment_method']) ?></td>
                                    <td class="dash-num">
                                        <span class="dash-primary num-tabular"><?= Security::formatMoney($r['amount']) ?></span>
                                        <span class="dash-secondary num-tabular"><?= Security::e($t("Faa'iido ", 'Profit ', 'ربح ')) ?><?= Security::formatMoney($r['allocated_to_profit']) ?></span>
                                    </td>
                                    <td class="dash-num dash-nowrap num-tabular"><?= Security::e($r['payment_date']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>
    </section>
</div>

<script>
(function () {
    // Period filter
    var form = document.getElementById('dashboardFilterForm');
    var periodInput = document.getElementById('filter_period_input');
    var applyBtn = document.getElementById('dashboardFilterApply');
    form.querySelectorAll('.dash-segment-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var period = btn.getAttribute('data-period');
            periodInput.value = period;
            form.querySelectorAll('.dash-segment-btn').forEach(function (other) {
                var active = other === btn;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            form.querySelectorAll('.dash-period-input').forEach(function (input) {
                input.hidden = input.getAttribute('data-for') !== period;
            });
            applyBtn.hidden = period === 'all';
            if (period === 'all') {
                form.submit();
            }
        });
    });

    // Charts
    var text = <?= json_encode($chartText, $chartJsonFlags) ?>;
    var comparison = <?= json_encode($comparison, $chartJsonFlags) ?>;
    var monthly = <?= json_encode($monthly, $chartJsonFlags) ?>;
    var charts = [];

    if (typeof Chart === 'undefined') {
        document.querySelectorAll('[data-chart-wrap]').forEach(function (wrap) {
            wrap.classList.add('dash-chart-unavailable');
            wrap.textContent = text.unavailable;
        });
        return;
    }

    // Palettes validated with the dataviz palette checker (light and dark surfaces).
    var palettes = {
        light: { green: '#1B8A63', gold: '#B8892B', blue: '#2A7FC0' },
        dark: { green: '#27A878', gold: '#B88D2E', blue: '#4F97EC' }
    };

    function money(value) {
        return '$' + Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function monthLabel(ym) {
        var parts = String(ym).split('-');
        var date = new Date(Number(parts[0]), Number(parts[1]) - 1, 1);
        return date.toLocaleDateString(text.locale, { month: 'short', year: '2-digit' });
    }

    function barDataset(label, data, color, surface) {
        return {
            label: label,
            data: data,
            backgroundColor: color,
            hoverBackgroundColor: color,
            borderColor: surface,
            borderWidth: 1,
            borderRadius: 4,
            maxBarThickness: 34
        };
    }

    function chartOptions(stacked, styles) {
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: reduceMotion ? false : { duration: 350 },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: styles.text, boxWidth: 10, boxHeight: 10, useBorderRadius: true, borderRadius: 2, padding: 16, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function (ctx) { return ctx.dataset.label + ': ' + money(ctx.parsed.y); }
                    }
                }
            },
            scales: {
                x: {
                    stacked: stacked,
                    grid: { display: false },
                    border: { color: styles.border },
                    ticks: { color: styles.text, font: { size: 12 } }
                },
                y: {
                    stacked: stacked,
                    beginAtZero: true,
                    grid: { color: styles.grid },
                    border: { display: false },
                    ticks: {
                        color: styles.muted,
                        font: { size: 11 },
                        maxTicksLimit: 6,
                        callback: function (value) {
                            return '$' + Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
                        }
                    }
                }
            }
        };
    }

    function renderCharts() {
        charts.forEach(function (chart) { chart.destroy(); });
        charts = [];

        var css = getComputedStyle(document.documentElement);
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var colors = isDark ? palettes.dark : palettes.light;
        var surface = css.getPropertyValue('--bg-surface').trim();
        var styles = {
            text: css.getPropertyValue('--text-secondary').trim(),
            muted: css.getPropertyValue('--text-muted').trim(),
            border: css.getPropertyValue('--border-strong').trim(),
            grid: css.getPropertyValue('--border-color').trim()
        };

        var comparisonCanvas = document.getElementById('chartMurabahaVsLoans');
        if (comparisonCanvas) {
            charts.push(new Chart(comparisonCanvas, {
                type: 'bar',
                data: {
                    labels: text.categories,
                    datasets: [
                        barDataset(text.disbursed, comparison.disbursed || [], colors.green, surface),
                        barDataset(text.collected, comparison.collected || [], colors.blue, surface),
                        barDataset(text.outstanding, comparison.outstanding || [], colors.gold, surface)
                    ]
                },
                options: chartOptions(false, styles)
            }));
        }

        var monthlyCanvas = document.getElementById('chartMonthlyRepayments');
        if (monthlyCanvas) {
            charts.push(new Chart(monthlyCanvas, {
                type: 'bar',
                data: {
                    labels: monthly.map(function (row) { return monthLabel(row.ym); }),
                    datasets: [
                        barDataset(text.principal, monthly.map(function (row) { return parseFloat(row.principal_collected); }), colors.green, surface),
                        barDataset(text.profit, monthly.map(function (row) { return parseFloat(row.profit_collected); }), colors.blue, surface),
                        barDataset(text.charity, monthly.map(function (row) { return parseFloat(row.charity_collected); }), colors.gold, surface)
                    ]
                },
                options: chartOptions(true, styles)
            }));
        }
    }

    renderCharts();

    // Redraw with the matching palette when the light/dark switch is used.
    new MutationObserver(renderCharts).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
})();
</script>
