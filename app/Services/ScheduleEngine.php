<?php
declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Module 5 — Repayment Schedule Calculation Engine
 *
 * Decimal-safe financial engine using bcmath / integer-cent arithmetic.
 * Never uses binary floating-point arithmetic for monetary totals or installment splits.
 *
 * Supported Product Types:
 * 1. Murabaha: Total Due = Cost + (Cost * Markup Percentage / 100), split into equal installments.
 * 2. Flat: Profit = Principal * (Annual Rate / 100) * Number of Years; Total Due = Principal + Profit, split into equal installments.
 * 3. Declining Balance: Equal-installment amortized schedule where periodic profit is calculated on the remaining outstanding principal.
 *
 * Rules Enforced:
 * - Currency stored and returned with exact two decimal places.
 * - All installments sum EXACTLY to Total Due, Principal sum == Principal, Profit sum == Total Profit.
 * - Rounding differences are absorbed by the final installment.
 * - Period unit: 'minutely' | 'hourly' | 'daily' | 'weekly' | 'monthly', with an interval (every N units).
 *   One single payment = 1 installment with interval N (e.g. pay once after 30 minutes).
 * - Day-based due dates (daily / weekly / monthly) falling on a Friday (ISO-8601 day 5) shift to Saturday (+1 day).
 *   Minute / hour schedules keep their exact time (due_at) and are not shifted.
 * - Produces a reproducible `calculation_inputs` metadata payload.
 */
final class ScheduleEngine
{
    /** Period unit => periods in one year (used to turn an annual rate into a per-period rate). */
    public const FREQUENCIES = [
        'minutely' => '525600',
        'hourly' => '8760',
        'daily' => '365',
        'weekly' => '52',
        'monthly' => '12',
    ];

    /** Limits for the number of installments and the units between them. */
    public const MAX_INSTALLMENTS = 360;
    public const MAX_INTERVAL = 1000;

    /** Interval of the schedule being generated (set by generate()). */
    private static int $interval = 1;
    /**
     * Generate a complete repayment schedule and summary.
     *
     * @param string $productType 'murabaha' | 'flat' | 'declining_balance'
     * @param string|float|int $principal Amount financed / Cost
     * @param string|float|int $rate Markup % (for murabaha) or Annual Profit Rate % (for flat / declining_balance)
     * @param int $termInstallments Number of installments (> 0)
     * @param string $frequency 'minutely' | 'hourly' | 'daily' | 'weekly' | 'monthly'
     * @param string $startDate YYYY-MM-DD or YYYY-MM-DD HH:MM:SS (application time for preview, disbursement time when activated)
     * @param int $interval Units between installments (every N minutes / hours / days / weeks / months)
     * @return array{
     *   product_type: string,
     *   principal: string,
     *   rate: string,
     *   term_installments: int,
     *   frequency: string,
     *   start_date: string,
     *   maturity_date: string,
     *   total_profit: string,
     *   total_due: string,
     *   regular_installment: string,
     *   final_installment: string,
     *   installments: array<int, array<string, mixed>>,
     *   calculation_inputs: array<string, mixed>
     * }
     */
    public static function generate(
        string $productType,
        string|float|int $principal,
        string|float|int $rate,
        int $termInstallments,
        string $frequency = 'monthly',
        ?string $startDate = null,
        int $interval = 1
    ): array {
        $productType = strtolower(trim($productType));
        if (!in_array($productType, ['murabaha', 'flat', 'declining_balance'], true)) {
            throw new InvalidArgumentException("Unsupported product type: {$productType}");
        }
        if ($termInstallments < 1) {
            throw new InvalidArgumentException('Term installments must be at least 1.');
        }
        $frequency = strtolower(trim($frequency));
        if (!isset(self::FREQUENCIES[$frequency])) {
            throw new InvalidArgumentException("Unsupported frequency: {$frequency}");
        }
        if ($interval < 1 || $interval > self::MAX_INTERVAL) {
            throw new InvalidArgumentException('Interval must be between 1 and ' . self::MAX_INTERVAL . '.');
        }
        if ($termInstallments > self::MAX_INSTALLMENTS) {
            throw new InvalidArgumentException('At most ' . self::MAX_INSTALLMENTS . ' installments.');
        }

        $principalCents = self::toCents($principal);
        if ($principalCents <= 0) {
            throw new InvalidArgumentException('Principal amount must be greater than zero.');
        }

        $rateStr = number_format((float) $rate, 4, '.', '');
        // Minute / hour schedules count from the exact start time; day-based ones from the start day.
        if ($frequency === 'minutely' || $frequency === 'hourly') {
            $baseDateStr = $startDate
                ? (strlen($startDate) > 10 ? substr($startDate, 0, 19) : $startDate . ' ' . date('H:i:s'))
                : date('Y-m-d H:i:s');
        } else {
            $baseDateStr = $startDate ? substr($startDate, 0, 10) : date('Y-m-d');
        }
        $baseDate = new DateTimeImmutable($baseDateStr);
        self::$interval = $interval;

        if ($productType === 'murabaha') {
            return self::calculateMurabaha($principalCents, $rateStr, $termInstallments, $frequency, $baseDate);
        }

        if ($productType === 'flat') {
            return self::calculateFlat($principalCents, $rateStr, $termInstallments, $frequency, $baseDate);
        }

        return self::calculateDecliningBalance($principalCents, $rateStr, $termInstallments, $frequency, $baseDate);
    }

    private static function calculateMurabaha(
        int $principalCents,
        string $markupPctStr,
        int $term,
        string $frequency,
        DateTimeImmutable $baseDate
    ): array {
        // Total Profit = round(Cost * (Markup% / 100))
        $principalDec = self::fromCents($principalCents);
        $profitRaw = bcmul($principalDec, bcdiv($markupPctStr, '100', 10), 6);
        $totalProfitCents = self::roundDecimalToCents($profitRaw);
        $totalDueCents = $principalCents + $totalProfitCents;

        return self::buildEqualSplitSchedule(
            'murabaha',
            $principalCents,
            $totalProfitCents,
            $totalDueCents,
            $markupPctStr,
            $term,
            $frequency,
            $baseDate,
            'Total Due = Cost + (Cost * Markup Percentage); equal installment split with final installment rounding reconciliation.'
        );
    }

    private static function calculateFlat(
        int $principalCents,
        string $annualRateStr,
        int $term,
        string $frequency,
        DateTimeImmutable $baseDate
    ): array {
        // Profit = Principal * Annual Rate * Number of Years
        // Number of years = term / 12 (for monthly) or term / 52 (for weekly)
        $periodsPerYear = self::periodsPerYear($frequency);
        $principalDec = self::fromCents($principalCents);
        $annualRateFraction = bcdiv($annualRateStr, '100', 10);
        $yearsDec = bcdiv((string) $term, $periodsPerYear, 10);

        $profitRaw = bcmul(bcmul($principalDec, $annualRateFraction, 10), $yearsDec, 6);
        $totalProfitCents = self::roundDecimalToCents($profitRaw);
        $totalDueCents = $principalCents + $totalProfitCents;

        return self::buildEqualSplitSchedule(
            'flat',
            $principalCents,
            $totalProfitCents,
            $totalDueCents,
            $annualRateStr,
            $term,
            $frequency,
            $baseDate,
            "Profit = Principal * Annual Rate * (Term / {$periodsPerYear}); equal installment split with final installment rounding reconciliation."
        );
    }

    private static function buildEqualSplitSchedule(
        string $productType,
        int $principalCents,
        int $totalProfitCents,
        int $totalDueCents,
        string $rateStr,
        int $term,
        string $frequency,
        DateTimeImmutable $baseDate,
        string $formulaNote
    ): array {
        $regularInstallmentCents = intdiv($totalDueCents, $term);
        $regularPrincipalCents = intdiv($principalCents, $term);
        $regularProfitCents = $regularInstallmentCents - $regularPrincipalCents;

        $accumPrincipalCents = 0;
        $accumProfitCents = 0;
        $remainingPrincipalCents = $principalCents;

        $installments = [];
        for ($i = 1; $i <= $term; $i++) {
            [$origDue, $adjustedDue, $wasShifted, $dueAt] = self::computeDueDate($baseDate, $i * self::$interval, $frequency);

            if ($i < $term) {
                $instPrincipal = $regularPrincipalCents;
                $instProfit = $regularProfitCents;
                $instTotal = $regularInstallmentCents;
            } else {
                // Final installment absorbs exact rounding remainder
                $instPrincipal = $principalCents - $accumPrincipalCents;
                $instProfit = $totalProfitCents - $accumProfitCents;
                $instTotal = $instPrincipal + $instProfit;
            }

            $accumPrincipalCents += $instPrincipal;
            $accumProfitCents += $instProfit;
            $remainingPrincipalCents -= $instPrincipal;

            $installments[] = [
                'installment_number' => $i,
                'original_due_date' => $origDue,
                'due_date' => $adjustedDue,
                'due_at' => $dueAt,
                'was_friday_shifted' => $wasShifted ? 1 : 0,
                'principal_due' => self::fromCents($instPrincipal),
                'profit_due' => self::fromCents($instProfit),
                'total_due' => self::fromCents($instTotal),
                'remaining_balance' => self::fromCents($instTotal),
                'principal_balance_after' => self::fromCents(max(0, $remainingPrincipalCents)),
            ];
        }

        $lastInst = $installments[count($installments) - 1];

        return [
            'product_type' => $productType,
            'principal' => self::fromCents($principalCents),
            'rate' => $rateStr,
            'term_installments' => $term,
            'frequency' => $frequency,
            'interval' => self::$interval,
            'start_date' => $baseDate->format('Y-m-d'),
            'maturity_at' => $lastInst['due_at'],
            'maturity_date' => $lastInst['due_date'],
            'total_profit' => self::fromCents($totalProfitCents),
            'total_due' => self::fromCents($totalDueCents),
            'regular_installment' => $installments[0]['total_due'],
            'final_installment' => $lastInst['total_due'],
            'installments' => $installments,
            'calculation_inputs' => [
                'engine_version' => 'SIMDA-ScheduleEngine-1.0',
                'product_type' => $productType,
                'principal' => self::fromCents($principalCents),
                'rate_pct' => $rateStr,
                'term_installments' => $term,
                'frequency' => $frequency,
                'interval' => self::$interval,
                'start_date' => $baseDate->format('Y-m-d H:i:s'),
                'friday_shift_rule' => 'Friday (ISO-5) -> Saturday (ISO-6)',
                'formula' => $formulaNote,
            ],
        ];
    }

    private static function calculateDecliningBalance(
        int $principalCents,
        string $annualRateStr,
        int $term,
        string $frequency,
        DateTimeImmutable $baseDate
    ): array {
        $periodsPerYear = self::periodsPerYear($frequency);
        $annualRateFraction = bcdiv($annualRateStr, '100', 12);
        $periodicRateStr = bcdiv($annualRateFraction, $periodsPerYear, 12);
        $periodicRateFloat = (float) $periodicRateStr;

        $principalDec = self::fromCents($principalCents);

        if ($periodicRateFloat <= 0.0) {
            return self::buildEqualSplitSchedule(
                'declining_balance',
                $principalCents,
                0,
                $principalCents,
                $annualRateStr,
                $term,
                $frequency,
                $baseDate,
                'Zero-rate declining balance schedule.'
            );
        }

        // Standard amortized equal installment formula:
        // PMT = P * [ r * (1 + r)^n ] / [ (1 + r)^n - 1 ]
        $onePlusR = bcadd('1', $periodicRateStr, 12);
        $pow = bcpow($onePlusR, (string) $term, 12);
        $numerator = bcmul($principalDec, bcmul($periodicRateStr, $pow, 12), 12);
        $denominator = bcsub($pow, '1', 12);
        $pmtRaw = bcdiv($numerator, $denominator, 6);
        $regularPmtCents = self::roundDecimalToCents($pmtRaw);

        $remainingPrincipalCents = $principalCents;
        $totalProfitCents = 0;
        $installments = [];

        for ($i = 1; $i <= $term; $i++) {
            [$origDue, $adjustedDue, $wasShifted, $dueAt] = self::computeDueDate($baseDate, $i * self::$interval, $frequency);

            $remPrincipalDec = self::fromCents($remainingPrincipalCents);
            $periodProfitRaw = bcmul($remPrincipalDec, $periodicRateStr, 6);
            $instProfitCents = self::roundDecimalToCents($periodProfitRaw);

            if ($i < $term) {
                $instTotalCents = $regularPmtCents;
                $instPrincipalCents = $instTotalCents - $instProfitCents;
                if ($instPrincipalCents > $remainingPrincipalCents) {
                    $instPrincipalCents = $remainingPrincipalCents;
                    $instTotalCents = $instPrincipalCents + $instProfitCents;
                }
            } else {
                // Final period: clear exact remaining principal plus final period profit
                $instPrincipalCents = $remainingPrincipalCents;
                $instTotalCents = $instPrincipalCents + $instProfitCents;
            }

            $remainingPrincipalCents -= $instPrincipalCents;
            $totalProfitCents += $instProfitCents;

            $installments[] = [
                'installment_number' => $i,
                'original_due_date' => $origDue,
                'due_date' => $adjustedDue,
                'due_at' => $dueAt,
                'was_friday_shifted' => $wasShifted ? 1 : 0,
                'principal_due' => self::fromCents($instPrincipalCents),
                'profit_due' => self::fromCents($instProfitCents),
                'total_due' => self::fromCents($instTotalCents),
                'remaining_balance' => self::fromCents($instTotalCents),
                'principal_balance_after' => self::fromCents(max(0, $remainingPrincipalCents)),
            ];
        }

        $totalDueCents = $principalCents + $totalProfitCents;
        $lastInst = $installments[count($installments) - 1];

        return [
            'product_type' => 'declining_balance',
            'principal' => self::fromCents($principalCents),
            'rate' => $annualRateStr,
            'term_installments' => $term,
            'frequency' => $frequency,
            'interval' => self::$interval,
            'start_date' => $baseDate->format('Y-m-d'),
            'maturity_at' => $lastInst['due_at'],
            'maturity_date' => $lastInst['due_date'],
            'total_profit' => self::fromCents($totalProfitCents),
            'total_due' => self::fromCents($totalDueCents),
            'regular_installment' => $installments[0]['total_due'],
            'final_installment' => $lastInst['total_due'],
            'installments' => $installments,
            'calculation_inputs' => [
                'engine_version' => 'SIMDA-ScheduleEngine-1.0',
                'product_type' => 'declining_balance',
                'principal' => self::fromCents($principalCents),
                'rate_pct' => $annualRateStr,
                'periodic_rate' => $periodicRateStr,
                'term_installments' => $term,
                'frequency' => $frequency,
                'interval' => self::$interval,
                'start_date' => $baseDate->format('Y-m-d H:i:s'),
                'friday_shift_rule' => 'Friday (ISO-5) -> Saturday (ISO-6)',
                'formula' => 'Amortized declining balance: periodic profit = Outstanding Principal * (Annual Rate / PeriodsPerYear); final installment reconciles exact principal.',
            ],
        ];
    }

    /**
     * Compute due date for period $periodIndex and shift Friday -> Saturday.
     *
     * @return array{0: string, 1: string, 2: bool} [original_due_date, adjusted_due_date, was_friday_shifted]
     */
    public static function computeDueDate(DateTimeImmutable $baseDate, int $periodIndex, string $frequency): array
    {
        $due = match ($frequency) {
            'minutely' => $baseDate->modify("+{$periodIndex} minutes"),
            'hourly' => $baseDate->modify("+{$periodIndex} hours"),
            'daily' => $baseDate->modify("+{$periodIndex} days"),
            'weekly' => $baseDate->modify('+' . ($periodIndex * 7) . ' days'),
            default => $baseDate->modify("+{$periodIndex} months"),
        };

        // Minute / hour schedules: exact moment, no Friday shift.
        if ($frequency === 'minutely' || $frequency === 'hourly') {
            $day = $due->format('Y-m-d');
            return [$day, $day, false, $due->format('Y-m-d H:i:s')];
        }

        $originalStr = $due->format('Y-m-d');
        $wasFriday = ((int) $due->format('N') === 5); // 5 = Friday in ISO-8601
        if ($wasFriday) {
            $due = $due->modify('+1 day'); // Move Friday to Saturday
        }

        return [$originalStr, $due->format('Y-m-d'), $wasFriday, null];
    }

    /** Periods per year for a unit, divided by the interval (every N units). */
    private static function periodsPerYear(string $frequency): string
    {
        return bcdiv(self::FREQUENCIES[$frequency] ?? '12', (string) max(1, self::$interval), 10);
    }

    /**
     * Human description of a plan, e.g. "6 qaybood · bil kasta", "Hal mar · 30 daqiiqo kadib",
     * "4 instalments · every 2 hours".
     */
    public static function describePlan(int $installments, int $interval, string $frequency, string $locale = 'so'): string
    {
        $interval = max(1, $interval);
        $so = ['minutely' => ['daqiiqo', 'daqiiqo'], 'hourly' => ['saac', 'saacadood'], 'daily' => ['maalin', 'maalmood'], 'weekly' => ['toddobaad', 'toddobaad'], 'monthly' => ['bil', 'bilood']];
        $en = ['minutely' => ['minute', 'minutes'], 'hourly' => ['hour', 'hours'], 'daily' => ['day', 'days'], 'weekly' => ['week', 'weeks'], 'monthly' => ['month', 'months']];
        $ar = ['minutely' => ['دقيقة', 'دقائق'], 'hourly' => ['ساعة', 'ساعات'], 'daily' => ['يوم', 'أيام'], 'weekly' => ['أسبوع', 'أسابيع'], 'monthly' => ['شهر', 'أشهر']];
        $u = ($locale === 'en' ? $en : ($locale === 'ar' ? $ar : $so))[$frequency] ?? $so['monthly'];
        $span = $interval === 1 ? $u[0] : $interval . ' ' . $u[1];
        if ($installments <= 1) {
            if ($locale === 'en') {
                return 'One payment · after ' . ($interval === 1 ? '1 ' : '') . $span;
            }
            if ($locale === 'ar') {
                return 'دفعة واحدة · بعد ' . $span;
            }
            return 'Hal mar · ' . ($interval === 1 ? '1 ' : '') . $span . ' kadib';
        }
        if ($locale === 'en') {
            return "{$installments} instalments · every {$span}";
        }
        if ($locale === 'ar') {
            return "{$installments} أقساط · كل {$span}";
        }
        return "{$installments} qaybood · {$span} kasta";
    }

    /** Due moment for display: exact time for minute/hour schedules, the date otherwise. */
    public static function formatDue(array $installment): string
    {
        if (!empty($installment['due_at'])) {
            return date('Y-m-d H:i', strtotime((string) $installment['due_at']));
        }
        return (string) ($installment['due_date'] ?? '');
    }

    public static function toCents(string|float|int $amount): int
    {
        $clean = preg_replace('/[^0-9.\-]/', '', (string) $amount);
        if ($clean === '' || $clean === null) {
            return 0;
        }
        return self::roundDecimalToCents($clean);
    }

    public static function roundDecimalToCents(string $decimalStr): int
    {
        $scaled = bcmul($decimalStr, '100', 4);
        $isNegative = str_starts_with($scaled, '-');
        $offset = $isNegative ? '-0.5' : '0.5';
        $rounded = bcadd($scaled, $offset, 0);
        return (int) $rounded;
    }

    public static function fromCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $abs = abs($cents);
        $dollars = intdiv($abs, 100);
        $rem = $abs % 100;
        return sprintf('%s%d.%02d', $sign, $dollars, $rem);
    }
}

