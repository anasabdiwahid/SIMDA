<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$role = $user['role_slug'] ?? '';
?>

<div class="card animate-in" style="margin-bottom:22px;">
    <?php if ($role === 'client'): ?>
    <?php $isSoR = Lang::locale() === 'so'; ?>
    <h2 style="font-size:1.2rem;margin:6px 0;"><?= $isSoR ? 'Rasiidhadayda' : 'My Receipts' ?></h2>
    <p style="font-size:0.84rem;color:var(--text-secondary);margin-bottom:16px;">
        <?= $isSoR ? 'Lacag kasta oo aad bixisay rasiidkeeda halkan ka eeg ama ka daabac.' : 'View or print the receipt for every payment you made.' ?>
    </p>
    <?php else: ?>
    <h2 style="font-size:1.2rem;margin:6px 0;"><?= Security::e(Lang::get('menu_repayment_history')) ?></h2>
    <p style="font-size:0.84rem;color:var(--text-secondary);margin-bottom:16px;">
        <?= \App\Helpers\Lang::t('Habka qaybinta: <strong>(1) Ganaaxa sadaqada ee aan la bixin</strong> → <strong>(2) Qaybta ugu da\'da weyn ee aan la bixin</strong>. Waxaa la oggol yahay bixin buuxda, qayb ah, iyo bixin hore oo leh dhimista <em>Ibra</em> ee Murabaxada; lacagta dheeraadka ah waxaa loo kaydiyaa macmiilka.', 'Allocation order: <strong>(1) Outstanding charity late charges</strong> → <strong>(2) Oldest unpaid installment</strong>. Supports full, partial and early settlement with the Murabaha <em>Ibra</em> discount; any overpayment is kept as client credit.') ?>
    </p>
    <?php endif; ?>

    <?php if (in_array($role, ['admin', 'loan_officer'], true) && !empty($activeFinancings)): ?>
    <form method="POST" action="?route=repayment_submit" style="background:var(--bg-elevated);padding:16px;border-radius:10px;margin-bottom:20px;" data-confirm="<?= \App\Helpers\Lang::t('Ma diiwaangelinaysaa lacag-bixintan oo ma u qaybinaysaa ganaaxa sadaqada iyo qaybaha?', 'Record this repayment and allocate it across charity charges and installments?') ?>">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <h4 style="margin-bottom:12px;"><?= \App\Helpers\Lang::t('Diiwaangeli Lacag-bixin Cusub', 'Record New Repayment') ?></h4>
        <div class="form-grid">
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Heshiiska Maalgelinta Socda *', 'Active Financing Contract *') ?></label>
                <select name="financing_id" class="form-control" required>
                    <?php foreach ($activeFinancings as $af): ?>
                        <option value="<?= (int) $af['id'] ?>">
                            <?= Security::e($af['financing_number'] . ' — ' . $af['client_name'] . ' (Bal: $' . $af['outstanding_balance'] . ' | Credit: $' . $af['credit_balance'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Lacagta La Bixinayo ($ USD) *', 'Payment Amount ($ USD) *') ?></label>
                <input type="number" name="amount" class="form-control num-tabular" step="0.01" min="0" required value="560.00">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Habka Lacag-bixinta *', 'Payment Method *') ?></label>
                <select name="payment_method" class="form-control" required>
                    <option value="EVC Plus">EVC Plus (Mobile Money)</option>
                    <option value="Zaad">Zaad (Mobile Money)</option>
                    <option value="eDahab">eDahab (Mobile Money)</option>
                    <option value="Cash"><?= \App\Helpers\Lang::t('Lacag Caddaan ah', 'Cash') ?></option>
                </select>
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Tixraaca Lacag-wareejinta *', 'Transaction Reference *') ?></label>
                <input type="text" name="transaction_reference" class="form-control num-tabular" required value="MM-<?= strtoupper(bin2hex(random_bytes(3))) ?>">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Taariikhda Lacag-bixinta *', 'Payment Date *') ?></label>
                <input type="date" name="payment_date" class="form-control num-tabular" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Dhimista Ibra ee Murabaxada ($ USD)', 'Murabaha Ibra Early Discount ($ USD)') ?></label>
                <input type="number" name="ibra_discount" class="form-control num-tabular" step="0.01" min="0" value="0.00">
            </div>
            <div class="form-group">
                <label><?= \App\Helpers\Lang::t('Qoraal', 'Notes') ?></label>
                <input type="text" name="notes" class="form-control" placeholder="<?= \App\Helpers\Lang::t('Qoraal ku saabsan lacag-ururinta', 'Installment collection note') ?>">
            </div>
            <div class="form-group" style="justify-content:center;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:18px;">
                    <input type="checkbox" name="use_client_credit" value="1" checked>
                    <?= \App\Helpers\Lang::t('Isticmaal lacagta dheeraadka ah ee macmiilka u kaydsan', 'Apply any existing client overpayment credit') ?>
                </label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><?= Icons::svg('check-circle', 15) ?> <span><?= \App\Helpers\Lang::t('Diiwaangeli & Qaybi Lacagta', 'Record & Allocate Repayment') ?></span></button>
    </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th><?= \App\Helpers\Lang::t('Rasiidka #', 'Receipt #') ?></th>
                    <th><?= \App\Helpers\Lang::t('Heshiiska #', 'Contract #') ?></th>
                    <th><?= \App\Helpers\Lang::t('Macmiilka', 'Client') ?></th>
                    <th><?= \App\Helpers\Lang::t('Habka & Tixraaca', 'Method & Ref') ?></th>
                    <th><?= \App\Helpers\Lang::t('Lacagta La Bixiyay', 'Cash Paid') ?></th>
                    <th><?= \App\Helpers\Lang::t('Sadaqo', 'Charity Alloc.') ?></th>
                    <th><?= \App\Helpers\Lang::t('Faa\'iido', 'Profit Alloc.') ?></th>
                    <th><?= \App\Helpers\Lang::t('Raasumaal', 'Principal Alloc.') ?></th>
                    <th><?= \App\Helpers\Lang::t('Lacag Dheeraad ah', 'Overpayment Credit') ?></th>
                    <th><?= \App\Helpers\Lang::t('Taariikhda', 'Date') ?></th>
                    <th style="text-align:right;"><?= \App\Helpers\Lang::t('Ficillada', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($repayments as $r): ?>
                    <tr>
                        <td class="num-tabular">
                            <a href="?route=repayment_receipt&id=<?= (int) $r['id'] ?>" style="font-weight:800;color:var(--simda-green);text-decoration:underline;">
                                <?= Security::e($r['receipt_number']) ?>
                            </a>
                        </td>
                        <td class="num-tabular"><a href="?route=financing_view&id=<?= (int) $r['financing_id'] ?>" style="font-weight:700;color:var(--simda-green);"><?= Security::e($r['financing_number']) ?></a></td>
                        <td><?= Security::e($r['client_name']) ?></td>
                        <td>
                            <span class="badge badge-info"><?= Security::e($r['payment_method']) ?></span>
                            <span class="num-tabular" style="font-size:0.75rem;"><?= Security::e($r['transaction_reference']) ?></span>
                        </td>
                        <td class="num-tabular"><strong><?= Security::formatMoney($r['amount']) ?></strong></td>
                        <td class="num-tabular"><?= Security::formatMoney($r['allocated_to_charity']) ?></td>
                        <td class="num-tabular"><?= Security::formatMoney($r['allocated_to_profit']) ?></td>
                        <td class="num-tabular"><?= Security::formatMoney($r['allocated_to_principal']) ?></td>
                        <td class="num-tabular"><?= Security::formatMoney($r['overpayment_credited']) ?></td>
                        <td class="num-tabular"><?= Security::e($r['payment_date']) ?></td>
                        <td style="text-align:right;white-space:nowrap;">
                            <a href="?route=repayment_receipt&id=<?= (int) $r['id'] ?>" class="btn btn-xs btn-outline-primary" style="font-weight:700;display:inline-flex;align-items:center;gap:4px;padding:3px 8px;">
                                <?= Icons::svg('printer', 12) ?> Rasiid
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
