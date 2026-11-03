<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$f = $statement['financing'];
$installments = $statement['installments'];
$repayments = $statement['repayments'];
$charityCharges = $statement['charity_charges'];
?>

<div class="card animate-in" style="margin-bottom:22px;">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;padding-bottom:16px;border-bottom:1px solid var(--border-color);">
        <div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span class="badge badge-gold"><?= Security::e(strtoupper($f['product_type'])) ?> CONTRACT</span>
                <span class="badge badge-<?= $statement['is_fully_repaid'] ? 'success' : ($f['status'] === 'overdue' ? 'danger' : 'info') ?>">
                    <?= $statement['is_fully_repaid'] ? 'FULLY REPAID' : Security::e(strtoupper((string) $f['status'])) ?>
                </span>
            </div>
            <h2 class="num-tabular" style="font-size:1.35rem;margin-top:6px;">
                <?= Security::e($f['financing_number']) ?> — <?= Security::e($f['client_name']) ?>
            </h2>
            <div style="font-size:0.82rem;color:var(--text-secondary);">
                Client Code: <strong><?= Security::e($f['client_code']) ?></strong> •
                Branch: <strong><?= Security::e($f['branch_name']) ?></strong> •
                Disbursed via <strong><?= Security::e($f['disbursement_provider']) ?></strong> (Ref: <code><?= Security::e($f['disbursement_reference']) ?></code>)
            </div>
        </div>

        <div class="no-print" style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="?route=statement_pdf&id=<?= (int) $f['id'] ?>" class="btn btn-gold">
                <?= Icons::svg('download', 16) ?>
                <span><?= Security::e(Lang::get('download_pdf_statement')) ?></span>
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline">
                <?= Icons::svg('printer', 16) ?>
                <span><?= Security::e(Lang::get('print_statement')) ?></span>
            </button>
        </div>
    </div>

    <!-- SUMMARY METRICS -->
    <div class="kpi-grid" style="margin-top:16px;margin-bottom:18px;">
        <div class="kpi-card">
            <div class="kpi-label">Approved Principal / Cost</div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($f['approved_amount']) ?></div>
            <div class="kpi-sub">Net Disbursed: <?= Security::formatMoney($f['net_disbursed_amount']) ?> (Fee: <?= Security::formatMoney($f['admin_fee']) ?>)</div>
        </div>
        <div class="kpi-card kpi-gold">
            <div class="kpi-label">Agreed Profit / Markup</div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($f['total_profit']) ?></div>
            <div class="kpi-sub">Ibra Discount Granted: <?= Security::formatMoney($f['ibra_discount']) ?></div>
        </div>
        <div class="kpi-card kpi-success">
            <div class="kpi-label">Total Paid to Date</div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($f['total_paid']) ?></div>
            <div class="kpi-sub">Client Overpayment Credit: <?= Security::formatMoney($f['credit_balance']) ?></div>
        </div>
        <div class="kpi-card kpi-danger">
            <div class="kpi-label">Total Remaining Balance</div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($statement['total_remaining_balance']) ?></div>
            <div class="kpi-sub">Installments: <?= Security::formatMoney($f['outstanding_balance']) ?> + Charity: <?= Security::formatMoney($statement['unpaid_charity_total']) ?></div>
        </div>
    </div>

    <!-- INSTALLMENT SCHEDULE TABLE -->
    <h3 style="font-size:1rem;margin-bottom:10px;">1. Repayment Schedule (Module 5 — Friday → Saturday Adjusted)</h3>
    <div class="table-responsive" style="margin-bottom:22px;">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Due Date</th>
                    <th>Principal Due</th>
                    <th>Profit Due</th>
                    <th>Total Installment</th>
                    <th>Amount Paid</th>
                    <th>Remaining</th>
                    <th>Principal After</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($installments as $inst): ?>
                    <tr>
                        <td class="num-tabular"><strong>#<?= (int) $inst['installment_number'] ?></strong></td>
                        <td class="num-tabular">
                            <?= Security::e($inst['due_date']) ?>
                            <?php if ((int) $inst['was_friday_shifted'] === 1): ?>
                                <span class="badge badge-gold" title="Shifted from Friday (<?= Security::e($inst['original_due_date']) ?>) to Saturday">Sat Shift</span>
                            <?php endif; ?>
                        </td>
                        <td class="num-tabular"><?= Security::formatMoney($inst['principal_due']) ?></td>
                        <td class="num-tabular"><?= Security::formatMoney($inst['profit_due']) ?></td>
                        <td class="num-tabular"><strong><?= Security::formatMoney($inst['total_due']) ?></strong></td>
                        <td class="num-tabular" style="color:var(--color-success);"><?= Security::formatMoney($inst['total_paid']) ?></td>
                        <td class="num-tabular"><strong><?= Security::formatMoney($inst['remaining_balance']) ?></strong></td>
                        <td class="num-tabular"><?= Security::formatMoney($inst['principal_balance_after']) ?></td>
                        <td>
                            <span class="badge badge-<?= $inst['status'] === 'paid' ? 'success' : ($inst['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                <?= Security::e($inst['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- PAYMENT HISTORY TABLE -->
    <h3 style="font-size:1rem;margin-bottom:10px;">2. Payment History & Allocation Breakdown (Module 7)</h3>
    <div class="table-responsive" style="margin-bottom:22px;">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Payment Date</th>
                    <th>Method & Ref</th>
                    <th>Cash Paid</th>
                    <th>Credit Used</th>
                    <th>Allocated Charity</th>
                    <th>Allocated Profit</th>
                    <th>Allocated Principal</th>
                    <th>Overpayment Credited</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($repayments)): ?>
                    <tr><td colspan="9" style="text-align:center;color:var(--text-muted);">No repayments recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($repayments as $r): ?>
                        <tr>
                            <td class="num-tabular"><strong><?= Security::e($r['receipt_number']) ?></strong></td>
                            <td class="num-tabular"><?= Security::e($r['payment_date']) ?></td>
                            <td><?= Security::e($r['payment_method']) ?> (<code><?= Security::e($r['transaction_reference']) ?></code>)</td>
                            <td class="num-tabular"><strong><?= Security::formatMoney($r['amount']) ?></strong></td>
                            <td class="num-tabular"><?= Security::formatMoney($r['credit_applied']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($r['allocated_to_charity']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($r['allocated_to_profit']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($r['allocated_to_principal']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($r['overpayment_credited']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- CHARITY LATE CHARGES TABLE -->
    <h3 style="font-size:1rem;margin-bottom:10px;">3. Segregated Charity Late Charges (Module 8 — Non-Income Charity Account)</h3>
    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Installment #</th>
                    <th>Assessed Amount</th>
                    <th>Paid to Charity</th>
                    <th>Remaining</th>
                    <th>Status</th>
                    <th>Waiver Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($charityCharges)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">No charity late charges assessed ($0.00).</td></tr>
                <?php else: ?>
                    <?php foreach ($charityCharges as $cc): ?>
                        <tr>
                            <td class="num-tabular">#<?= (int) $cc['installment_number'] ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($cc['amount']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($cc['amount_paid']) ?></td>
                            <td class="num-tabular"><strong><?= Security::formatMoney($cc['remaining_amount']) ?></strong></td>
                            <td><span class="badge badge-<?= $cc['status'] === 'paid' || $cc['status'] === 'waived' ? 'success' : 'danger' ?>"><?= Security::e($cc['status']) ?></span></td>
                            <td><?= Security::e($cc['waiver_reason'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
