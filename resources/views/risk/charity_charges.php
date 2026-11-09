<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$canWaive = in_array($user['role_slug'] ?? '', ['admin', 'branch_manager'], true);
?>

<div class="card animate-in">
    <h2 style="font-size:1.25rem;margin:6px 0;"><?= Security::e(Lang::get('menu_charity_charges')) ?></h2>
    <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:18px;">
        <?= Lang::t(
            'Qaybta aan la bixin 7 maalmood kadib taariikhdeeda waxaa la saaraa ganaax go\'an. Ganaaxyadu waxay galaan <strong>Sanduuqa Sadaqada</strong> oo gooni ah, weligoodna looma xisaabiyo dakhliga hay\'adda. Maamulaha Laanta ayaa cafin kara ganaax, isagoo qoraya sababta.',
            'An installment unpaid 7 days after its due date receives the fixed late charge. Late charges go to a separate <strong>Charity Account</strong> and are never counted as income. Branch Managers can waive a charge with a mandatory reason.'
        ) ?>
    </p>

    <?php if (!empty($account)): ?>
    <!-- Charity account report (brief, Module 8): balance and its use. -->
    <div class="kpi-grid" style="margin-bottom:18px;">
        <div class="kpi-card">
            <div class="kpi-label"><?= Lang::t('Ganaaxa La Saaray', 'Charges Assessed') ?></div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($account['assessed']) ?></div>
            <div class="kpi-sub"><?= Lang::t('La cafiyay', 'Waived') ?>: <?= Security::formatMoney($account['waived']) ?> · <?= Lang::t('Weli lagu leeyahay', 'Still owed') ?>: <?= Security::formatMoney($account['outstanding']) ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><?= Lang::t('La Ururiyay (Sanduuqa Sadaqada)', 'Collected (Charity Account)') ?></div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($account['collected']) ?></div>
            <div class="kpi-sub"><?= Lang::t('Lama xisaabiyo dakhli', 'Never counted as income') ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><?= Lang::t('La Bixiyay (Isticmaalka)', 'Given Out (Use)') ?></div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($account['used']) ?></div>
            <div class="kpi-sub"><?= count($payouts ?? []) ?> <?= Lang::t('bixin', 'payout(s)') ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><?= Lang::t('Haraaga Sanduuqa Sadaqada', 'Charity Account Balance') ?></div>
            <div class="kpi-value num-tabular"><?= Security::formatMoney($account['balance']) ?></div>
            <div class="kpi-sub"><?= Lang::t('La ururiyay − la bixiyay', 'Collected − given out') ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= \App\Helpers\Lang::t('Heshiiska #', 'Contract #') ?></th>
                    <th><?= \App\Helpers\Lang::t('Macmiilka', 'Client') ?></th>
                    <th><?= \App\Helpers\Lang::t('Qaybta', 'Installment') ?></th>
                    <th><?= \App\Helpers\Lang::t('Maalmaha Dib u Dhaca', 'Days Late') ?></th>
                    <th><?= \App\Helpers\Lang::t('Ganaaxa La Saaray', 'Assessed Charge') ?></th>
                    <th><?= \App\Helpers\Lang::t('La Siiyay Sadaqada', 'Paid to Charity') ?></th>
                    <th><?= \App\Helpers\Lang::t('Haraaga', 'Remaining') ?></th>
                    <th><?= \App\Helpers\Lang::t('Xaaladda', 'Status') ?></th>
                    <th><?= \App\Helpers\Lang::t('Cafiska / Ficil', 'Waiver Details / Action') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($charges)): ?>
                    <tr><td colspan="10" style="text-align:center;color:var(--text-muted);"><?= \App\Helpers\Lang::t('Ganaax sadaqo lama diiwaangelin.', 'No charity late charges recorded.') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($charges as $c): ?>
                        <tr>
                            <td class="num-tabular">#<?= (int) $c['id'] ?></td>
                            <td class="num-tabular"><a href="?route=financing_view&id=<?= (int) $c['financing_id'] ?>" style="font-weight:700;color:var(--simda-green);"><?= Security::e($c['financing_number']) ?></a></td>
                            <td><?= Security::e($c['client_name']) ?></td>
                            <td class="num-tabular"><?= \App\Helpers\Lang::t('Qaybta #', 'Inst #') ?><?= (int) $c['installment_number'] ?> (<?= \App\Helpers\Lang::t('Taariikhda: ', 'Due: ') ?><?= Security::e($c['due_date']) ?>)</td>
                            <td class="num-tabular"><?= (int) $c['days_overdue_when_assessed'] ?> days</td>
                            <td class="num-tabular"><?= Security::formatMoney($c['amount']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($c['amount_paid']) ?></td>
                            <td class="num-tabular"><strong><?= Security::formatMoney($c['remaining_amount']) ?></strong></td>
                            <td>
                                <span class="badge badge-<?= $c['status'] === 'paid' || $c['status'] === 'waived' ? 'success' : 'danger' ?>">
                                    <?= Security::e($c['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($c['status'] === 'waived'): ?>
                                    <span style="font-size:0.8rem;color:var(--text-secondary);"><?= \App\Helpers\Lang::t('La cafiyay: ', 'Waived: ') ?><?= Security::e($c['waiver_reason']) ?></span>
                                <?php elseif ($c['status'] !== 'paid' && $canWaive): ?>
                                    <form method="POST" action="?route=charity_waive" style="display:flex;gap:6px;" data-confirm="<?= \App\Helpers\Lang::t('Ma cafinaysaa ganaaxan sadaqada oo ma diiwaangelinaysaa sababta?', 'Waive this charity late charge and log the reason?') ?>">
                                        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                        <input type="hidden" name="charge_id" value="<?= (int) $c['id'] ?>">
                                        <input type="text" name="waiver_reason" class="form-control" placeholder="<?= \App\Helpers\Lang::t('Sababta cafiska (qasab)...', 'Mandatory waiver reason...') ?>" required style="padding:5px 8px;font-size:0.78rem;width:190px;">
                                        <button type="submit" class="btn btn-sm btn-gold"><?= \App\Helpers\Lang::t('Cafi', 'Waive') ?></button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:0.78rem;color:var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

    <?php if (!empty($account)): ?>
    <h3 style="font-size:1.05rem;margin:26px 0 10px;"><?= Lang::t('Isticmaalka Sanduuqa Sadaqada', 'Use of the Charity Account') ?></h3>

    <?php if ($canWaive): ?>
    <form method="POST" action="?route=charity_payout" style="background:var(--bg-elevated);padding:14px;border-radius:10px;margin-bottom:14px;" data-confirm="<?= Lang::t('Ma diiwaangelinaysaa lacagtan laga bixiyay sanduuqa sadaqada?', 'Record this payout from the charity account?') ?>">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <div class="form-grid">
            <div class="form-group">
                <label><?= Lang::t('Lacagta ($ USD) *', 'Amount ($ USD) *') ?></label>
                <input type="number" name="amount" class="form-control num-tabular" step="0.01" min="0.01" max="<?= Security::e($account['balance']) ?>" required>
            </div>
            <div class="form-group">
                <label><?= Lang::t('Cidda la siiyay *', 'Recipient *') ?></label>
                <input type="text" name="recipient" class="form-control" maxlength="150" required>
            </div>
            <div class="form-group">
                <label><?= Lang::t('Ujeedada *', 'Purpose *') ?></label>
                <input type="text" name="purpose" class="form-control" maxlength="255" required>
            </div>
            <div class="form-group">
                <label><?= Lang::t('Taariikhda *', 'Date *') ?></label>
                <input type="date" name="paid_on" class="form-control num-tabular" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><?= Lang::t('Diiwaangeli Bixinta', 'Record Payout') ?></button>
    </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th><?= Lang::t('Taariikhda', 'Date') ?></th>
                    <th><?= Lang::t('Cidda la siiyay', 'Recipient') ?></th>
                    <th><?= Lang::t('Ujeedada', 'Purpose') ?></th>
                    <th><?= Lang::t('Laanta', 'Branch') ?></th>
                    <th><?= Lang::t('Lacagta', 'Amount') ?></th>
                    <th><?= Lang::t('Waxaa diiwaangeliyay', 'Recorded by') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payouts)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);"><?= Lang::t('Weli lacag lagama bixin sanduuqa sadaqada.', 'Nothing has been given out from the charity account yet.') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($payouts as $p): ?>
                        <tr>
                            <td class="num-tabular"><?= Security::e($p['paid_on']) ?></td>
                            <td><?= Security::e($p['recipient']) ?></td>
                            <td><?= Security::e($p['purpose']) ?></td>
                            <td><?= Security::e($p['branch_name'] ?? Lang::t('Hay\'adda oo dhan', 'Whole institution')) ?></td>
                            <td class="num-tabular"><strong><?= Security::formatMoney($p['amount']) ?></strong></td>
                            <td><?= Security::e($p['recorded_by_name'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
