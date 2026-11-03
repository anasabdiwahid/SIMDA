<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$canWaive = in_array($user['role_slug'] ?? '', ['admin', 'branch_manager'], true);
?>

<div class="card animate-in">
    <span class="badge badge-gold">Module 8 — Segregated Charity Account & Late-Charge Waivers</span>
    <h2 style="font-size:1.25rem;margin:6px 0;"><?= Security::e(Lang::get('menu_charity_charges')) ?></h2>
    <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:18px;">
        Sharia Governance Rule: An installment unpaid 7 days after its due date receives the configured fixed late charge. Late charges are directed to a separate <strong>Charity Account</strong> and are NEVER counted as institutional income. Branch Managers can waive charges with a mandatory reason.
    </p>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Contract #</th>
                    <th>Client</th>
                    <th>Installment</th>
                    <th>Days Late</th>
                    <th>Assessed Charge</th>
                    <th>Paid to Charity</th>
                    <th>Remaining</th>
                    <th>Status</th>
                    <th>Waiver Details / Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($charges)): ?>
                    <tr><td colspan="10" style="text-align:center;color:var(--text-muted);">No charity late charges recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($charges as $c): ?>
                        <tr>
                            <td class="num-tabular">#<?= (int) $c['id'] ?></td>
                            <td class="num-tabular"><a href="?route=financing_view&id=<?= (int) $c['financing_id'] ?>" style="font-weight:700;color:var(--simda-green);"><?= Security::e($c['financing_number']) ?></a></td>
                            <td><?= Security::e($c['client_name']) ?></td>
                            <td class="num-tabular">Inst #<?= (int) $c['installment_number'] ?> (Due: <?= Security::e($c['due_date']) ?>)</td>
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
                                    <span style="font-size:0.8rem;color:var(--text-secondary);">Waived: <?= Security::e($c['waiver_reason']) ?></span>
                                <?php elseif ($c['status'] !== 'paid' && $canWaive): ?>
                                    <form method="POST" action="?route=charity_waive" style="display:flex;gap:6px;" data-confirm="Waive this charity late charge and log the waiver reason?">
                                        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                        <input type="hidden" name="charge_id" value="<?= (int) $c['id'] ?>">
                                        <input type="text" name="waiver_reason" class="form-control" placeholder="Mandatory waiver reason..." required style="padding:5px 8px;font-size:0.78rem;width:190px;">
                                        <button type="submit" class="btn btn-sm btn-gold">Waive</button>
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

