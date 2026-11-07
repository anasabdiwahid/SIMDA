<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
?>

<div class="card animate-in" style="margin-bottom:24px;">
    <h2 style="font-size:1.25rem;margin:6px 0;"><?= Security::e(Lang::get('menu_disbursements')) ?></h2>
    <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:18px;">
        Formula: <code>Net Disbursed = Approved Amount − Administrative Fee</code>. Approved applications expire after 30 days if undisbursed. The repayment schedule activates from the disbursement date.
    </p>

    <h4 style="margin-bottom:12px;">1. Approved Applications Ready for Disbursement</h4>
    <?php if (empty($approvedApps)): ?>
        <div style="padding:20px;background:var(--bg-elevated);border-radius:10px;margin-bottom:22px;color:var(--text-muted);">
            No approved applications currently awaiting disbursement.
        </div>
    <?php else: ?>
        <div class="table-responsive" style="margin-bottom:24px;">
            <table class="simda-table">
                <thead>
                    <tr>
                        <th>App #</th>
                        <th>Client</th>
                        <th>Approved Amount</th>
                        <th>Admin Fee</th>
                        <th>Net Disbursed</th>
                        <th>Expires At</th>
                        <th>Initiate Mobile Money Disbursement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approvedApps as $app): ?>
                        <?php $net = (float) $app['requested_amount'] - (float) $app['agreed_admin_fee']; ?>
                        <tr>
                            <td class="num-tabular"><strong><?= Security::e($app['application_number']) ?></strong></td>
                            <td><?= Security::e($app['client_name']) ?> (<?= Security::e($app['client_phone']) ?>)</td>
                            <td class="num-tabular"><?= Security::formatMoney($app['requested_amount']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($app['agreed_admin_fee']) ?></td>
                            <td class="num-tabular"><strong style="color:var(--color-success);"><?= Security::formatMoney($net) ?></strong></td>
                            <td class="num-tabular"><?= Security::e(substr((string) $app['expires_at'], 0, 10)) ?></td>
                            <td>
                                <form method="POST" action="?route=disburse_submit" style="display:flex;gap:6px;align-items:center;" data-confirm="Execute simulated mobile money disbursement and activate the installment schedule?">
                                    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                    <input type="hidden" name="application_id" value="<?= (int) $app['id'] ?>">
                                    <select name="provider" class="form-control" style="padding:5px 8px;font-size:0.8rem;width:115px;" required>
                                        <option value="EVC Plus">EVC Plus</option>
                                        <option value="Zaad">Zaad</option>
                                        <option value="eDahab">eDahab</option>
                                    </select>
                                    <input type="text" name="phone" class="form-control num-tabular" value="<?= Security::e($app['client_phone']) ?>" style="padding:5px 8px;font-size:0.8rem;width:130px;" required>
                                    <button type="submit" class="btn btn-sm btn-gold">Disburse Now</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <h4 style="margin-bottom:12px;">2. Completed Disbursement Receipts</h4>
    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Contract #</th>
                    <th>Client</th>
                    <th>Provider & Ref</th>
                    <th>Approved Amount</th>
                    <th>Admin Fee</th>
                    <th>Net Disbursed</th>
                    <th>Disbursed Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disbursedList as $d): ?>
                    <tr>
                        <td class="num-tabular"><a href="?route=financing_view&id=<?= (int) $d['id'] ?>" style="font-weight:700;color:var(--simda-green);"><?= Security::e($d['financing_number']) ?></a></td>
                        <td><?= Security::e($d['client_name']) ?></td>
                        <td>
                            <span class="badge badge-gold"><?= Security::e($d['disbursement_provider']) ?></span>
                            <span class="num-tabular" style="font-size:0.78rem;"><?= Security::e($d['disbursement_reference']) ?></span>
                        </td>
                        <td class="num-tabular"><?= Security::formatMoney($d['approved_amount']) ?></td>
                        <td class="num-tabular"><?= Security::formatMoney($d['admin_fee']) ?></td>
                        <td class="num-tabular"><strong><?= Security::formatMoney($d['net_disbursed_amount']) ?></strong></td>
                        <td class="num-tabular"><?= Security::e(substr((string) $d['disbursed_at'], 0, 10)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

