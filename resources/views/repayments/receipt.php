<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$isSo = Lang::locale() === 'so';
$r = $repayment;
$prodName = match ($r['product_type'] ?? '') {
    'murabaha' => ($isSo ? 'Murabaxo (Asset Financing)' : 'Murabaha Asset Financing'),
    'qard_hassan' => ($isSo ? 'Dayn Qard Hassan (Cash Loan)' : 'Qard Hassan Cash Loan'),
    default => strtoupper((string) ($r['product_type'] ?? 'Financing')),
};
?>

<style>
.receipt-wrapper {
    max-width: 780px;
    margin: 0 auto 30px;
}
.receipt-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 36px 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    position: relative;
}
.receipt-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 24px;
    border-bottom: 2px solid var(--border-color);
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
.receipt-title {
    font-size: 1.35rem;
    font-weight: 900;
    color: var(--simda-green);
    margin: 0 0 4px;
}
.receipt-badge-verified {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(35,132,91,0.12);
    color: var(--simda-green);
    border: 1px solid rgba(35,132,91,0.3);
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 800;
    font-size: 0.82rem;
}
.receipt-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}
.receipt-meta-box {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px 20px;
}
.receipt-meta-label {
    font-size: 0.74rem;
    font-weight: 800;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.receipt-meta-val {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-main);
}
.receipt-amount-banner {
    background: linear-gradient(135deg, rgba(35,132,91,0.08) 0%, rgba(214,182,108,0.12) 100%);
    border: 2px dashed rgba(35,132,91,0.35);
    border-radius: 14px;
    padding: 24px;
    text-align: center;
    margin-bottom: 26px;
}
.receipt-amount-num {
    font-size: 2.5rem;
    font-weight: 900;
    color: var(--simda-green);
    letter-spacing: -0.5px;
    line-height: 1.1;
}
.receipt-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
    font-size: 0.88rem;
}
.receipt-table td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-main);
}
.receipt-table tr:last-child td {
    border-bottom: none;
}
.receipt-table td.label-col {
    width: 42%;
    color: var(--text-muted);
    font-weight: 600;
}
.receipt-table td.val-col {
    font-weight: 700;
}
@media print {
    body {
        background: #FFFFFF !important;
        color: #000000 !important;
    }
    .main-sidebar, .main-header, .receipt-actions, .dash-nav, .adm-head-actions {
        display: none !important;
    }
    .receipt-wrapper {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .receipt-card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .receipt-amount-banner {
        border: 2px solid #23845B !important;
        background: #F4F8F6 !important;
    }
}
</style>

<div class="receipt-wrapper animate-in">
    <!-- TOP ACTIONS BAR -->
    <div class="receipt-actions" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;gap:10px;align-items:center;">
            <?php if (($user['role_slug'] ?? '') === 'client'): ?>
            <a href="?route=repayments" class="btn btn-sm btn-outline" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('arrow-left', 14) ?> <?= $isSo ? 'Rasiidhadayda' : 'My Receipts' ?>
            </a>
            <?php else: ?>
            <a href="?route=clients" class="btn btn-sm btn-outline" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('arrow-left', 14) ?> <?= $isSo ? 'Ku noqo Macaamiisha' : 'Back to Clients' ?>
            </a>
            <a href="?route=repayments" class="btn btn-sm btn-outline" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('activity', 14) ?> <?= $isSo ? 'Diiwaanka Rasiidhada' : 'All Repayments' ?>
            </a>
            <?php endif; ?>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()" style="font-weight:800;display:inline-flex;align-items:center;gap:6px;padding:8px 20px;">
            <?= Icons::svg('printer', 15) ?> <?= $isSo ? 'Daabac Rasiidka (Print)' : 'Print Receipt' ?>
        </button>
    </div>

    <!-- MAIN RECEIPT CARD -->
    <div class="receipt-card" id="printableReceipt">
        <!-- HEADER -->
        <div class="receipt-header">
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                    <img src="public/assets/images/simda-logo.png" alt="SIMDA" style="height:44px;object-fit:contain;" onerror="this.style.display='none';">
                    <div>
                        <h2 class="receipt-title">SIMDA MICROFINANCE</h2>
                        <div style="font-size:0.75rem;font-weight:700;color:var(--gold-color);letter-spacing:1px;text-transform:uppercase;">
                            Somali Islamic Microfinance Development Association
                        </div>
                    </div>
                </div>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;">
                    <?= Security::e($r['branch_name'] ?? 'Head Office Branch') ?> &bull; <?= Security::e($r['branch_city'] ?? 'Mogadishu, Somalia') ?> &bull; <?= Security::e($r['branch_phone'] ?? '+252 616256534 / +252 613496943') ?>
                </div>
            </div>

            <div style="text-align:right;">
                <div class="receipt-badge-verified">
                    <?= Icons::svg('check-circle', 14) ?> <?= $isSo ? 'RASIID RASMI AH' : 'OFFICIAL RECEIPT' ?>
                </div>
                <div style="margin-top:8px;font-family:monospace;font-size:1.15rem;font-weight:900;color:var(--simda-green);letter-spacing:1px;">
                    <?= Security::e($r['receipt_number']) ?>
                </div>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
                    <?= Security::e(date('d/m/Y H:i', strtotime((string) $r['created_at']))) ?>
                </div>
            </div>
        </div>

        <!-- AMOUNT BANNER -->
        <div class="receipt-amount-banner">
            <div style="font-size:0.85rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">
                <?= $isSo ? 'Cadadka Lacagta La Soo Celiyay' : 'Repayment Amount Received' ?>
            </div>
            <div class="receipt-amount-num">
                $<?= number_format((float) $r['amount'], 2) ?> <span style="font-size:1.2rem;font-weight:700;color:var(--text-muted);">USD</span>
            </div>
            <div style="font-size:0.88rem;color:var(--text-main);margin-top:8px;">
                <?= $isSo ? 'Baaqiga Harsan Heshiiska:' : 'Remaining Balance on Contract:' ?>
                <strong style="color:var(--simda-green);font-size:1.05rem;font-family:monospace;margin-left:4px;">
                    $<?= number_format((float) ($r['outstanding_balance'] ?? 0), 2) ?> USD
                </strong>
            </div>
        </div>

        <!-- 2-COL DETAILS -->
        <div class="receipt-grid">
            <!-- Client Meta Box -->
            <div class="receipt-meta-box">
                <div class="receipt-meta-label"><?= $isSo ? 'Macmiilka' : 'Client Details' ?></div>
                <div class="receipt-meta-val" style="font-size:1.05rem;color:var(--simda-green);">
                    <?= Security::e($r['client_name']) ?>
                </div>
                <div style="font-size:0.82rem;color:var(--text-muted);margin-top:3px;font-family:monospace;">
                    Koodhka: <strong><?= Security::e($r['client_code']) ?></strong>
                </div>
                <?php if (!empty($r['client_phone'])): ?>
                    <div style="font-size:0.82rem;color:var(--text-muted);margin-top:2px;">
                        <?= Icons::svg('phone', 11) ?> <span class="num-tabular"><?= Security::e($r['client_phone']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($r['client_email'])): ?>
                    <div style="font-size:0.82rem;color:var(--text-muted);margin-top:2px;">
                        <?= Icons::svg('mail', 11) ?> <span><?= Security::e($r['client_email']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Financing Meta Box -->
            <div class="receipt-meta-box">
                <div class="receipt-meta-label"><?= $isSo ? 'Heshiiska Maalgelinta' : 'Financing Contract' ?></div>
                <div class="receipt-meta-val" style="font-family:monospace;font-size:1.05rem;color:var(--simda-green);">
                    <?= Security::e($r['financing_number']) ?>
                </div>
                <div style="font-size:0.82rem;color:var(--text-main);margin-top:3px;font-weight:700;">
                    <?= Security::e($prodName) ?>
                </div>
                <div style="font-size:0.82rem;color:var(--text-muted);margin-top:2px;">
                    Xaaladda: <span class="badge badge-success" style="font-size:0.7rem;"><?= Security::e(strtoupper((string) ($r['financing_status'] ?? 'Active'))) ?></span>
                </div>
            </div>
        </div>

        <!-- BREAKDOWN TABLE -->
        <table class="receipt-table">
            <tbody>
                <tr>
                    <td class="label-col"><?= $isSo ? 'Qaabka Bixinta' : 'Payment Method' ?></td>
                    <td class="val-col">
                        <span class="badge badge-info" style="font-weight:800;"><?= Security::e($r['payment_method']) ?></span>
                    </td>
                </tr>
                <tr>
                    <td class="label-col"><?= $isSo ? 'Lambarka Tixraaca (Ref #)' : 'Transaction Reference' ?></td>
                    <td class="val-col" style="font-family:monospace;"><?= Security::e($r['transaction_reference'] ?: 'N/A') ?></td>
                </tr>
                <tr>
                    <td class="label-col"><?= $isSo ? 'Taariikhda Bixinta' : 'Payment Date' ?></td>
                    <td class="val-col num-tabular"><?= Security::e($r['payment_date']) ?></td>
                </tr>
                <?php if ((float) ($r['allocated_to_principal'] ?? 0) > 0): ?>
                <tr>
                    <td class="label-col"><?= $isSo ? 'Qaybta Maamulka Asalka (Principal)' : 'Principal Allocation' ?></td>
                    <td class="val-col num-tabular" style="color:var(--simda-green);">$<?= number_format((float) $r['allocated_to_principal'], 2) ?> USD</td>
                </tr>
                <?php endif; ?>
                <?php if ((float) ($r['allocated_to_profit'] ?? 0) > 0): ?>
                <tr>
                    <td class="label-col"><?= $isSo ? 'Qaybta Faa\'iidada (Profit)' : 'Profit Allocation' ?></td>
                    <td class="val-col num-tabular" style="color:var(--gold-color);">$<?= number_format((float) $r['allocated_to_profit'], 2) ?> USD</td>
                </tr>
                <?php endif; ?>
                <?php if ((float) ($r['allocated_to_charity'] ?? 0) > 0): ?>
                <tr>
                    <td class="label-col"><?= $isSo ? 'Qaybta Sadaqada / Ganaaxa' : 'Charity Allocation' ?></td>
                    <td class="val-col num-tabular" style="color:#E53E3E;">$<?= number_format((float) $r['allocated_to_charity'], 2) ?> USD</td>
                </tr>
                <?php endif; ?>
                <?php if ((float) ($r['overpayment_credited'] ?? 0) > 0): ?>
                <tr>
                    <td class="label-col"><?= $isSo ? 'Dheeraadka Lagu Keydiyay Akoonka (Credit)' : 'Overpayment Credit Retained' ?></td>
                    <td class="val-col num-tabular" style="color:var(--simda-green);">$<?= number_format((float) $r['overpayment_credited'], 2) ?> USD</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="label-col"><?= $isSo ? 'Shaqaalaha Qabtay' : 'Received By (Staff)' ?></td>
                    <td class="val-col"><?= Security::e($r['recorded_by_name'] ?? 'System') ?></td>
                </tr>
                <?php if (!empty($r['notes'])): ?>
                <tr>
                    <td class="label-col"><?= $isSo ? 'Faahfaahin / Qoraal' : 'Notes' ?></td>
                    <td class="val-col" style="font-style:italic;"><?= Security::e($r['notes']) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- FOOTER & SHARIA COMPLIANCE NOTICE -->
        <div style="background:rgba(214,182,108,0.12);border:1px solid rgba(214,182,108,0.3);border-radius:10px;padding:14px 18px;margin-top:14px;font-size:0.8rem;color:var(--text-main);line-height:1.5;">
            <strong><?= $isSo ? 'Xaqiijinta Shareecada & Nidaamka:' : 'Sharia Compliance & Audit Verification:' ?></strong>
            <?= $isSo
                ? 'Rasiidhani waa caddeyn rasmi ah oo muujinaysa bixinta qayb ka mid ah heshiiska maalgelinta. Dhammaan lacagaha waxaa loo habeeyay si waafaqsan mabaadiida shareecada Islaamka (100% Zero-Riba).'
                : 'This document is an authentic institutional receipt confirming repayment allocation under Islamic Microfinance rules. Verified and logged into the institutional audit ledger.' ?>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;padding-top:16px;border-top:1px solid var(--border-color);font-size:0.75rem;color:var(--text-muted);">
            <div>SIMDA Central Microfinance Platform &bull; www.simda.so</div>
            <div><?= $isSo ? 'Daabacaad Nidaamka ah' : 'System Generated' ?>: <?= date('Y-m-d H:i') ?></div>
        </div>
    </div>
</div>
