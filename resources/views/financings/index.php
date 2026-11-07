<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;
?>

<div class="card animate-in">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <div>
            <h2 style="font-size:1.25rem;margin-top:6px;"><?= Security::e(Lang::get('menu_active_financing')) ?></h2>
        </div>
    </div>

    <?php if (empty($financings)): ?>
        <div style="padding:32px;text-align:center;color:var(--text-muted);">
            <strong><?= Security::e(Lang::get('empty_state_title')) ?></strong>
            <div><?= Security::e(Lang::get('empty_state_desc')) ?></div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="simda-table">
                <thead>
                    <tr>
                        <th>Contract #</th>
                        <th>Client</th>
                        <th>Branch</th>
                        <th>Type</th>
                        <th>Principal</th>
                        <th>Profit / Markup</th>
                        <th>Total Due</th>
                        <th>Total Paid</th>
                        <th>Outstanding</th>
                        <th>Status</th>
                        <th>Statement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($financings as $f): ?>
                        <tr>
                            <td class="num-tabular">
                                <a href="?route=financing_view&id=<?= (int) $f['id'] ?>" style="font-weight:700;color:var(--simda-green);text-decoration:underline;">
                                    <?= Security::e($f['financing_number']) ?>
                                </a>
                            </td>
                            <td><?= Security::e($f['client_name']) ?></td>
                            <td><?= Security::e($f['branch_name']) ?></td>
                            <td><span class="badge badge-gold"><?= Security::e($f['product_type']) ?></span></td>
                            <td class="num-tabular"><?= Security::formatMoney($f['approved_amount']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($f['total_profit']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($f['total_due']) ?></td>
                            <td class="num-tabular" style="color:var(--color-success);"><?= Security::formatMoney($f['total_paid']) ?></td>
                            <td class="num-tabular"><strong><?= Security::formatMoney($f['outstanding_balance']) ?></strong></td>
                            <td>
                                <span class="badge badge-<?= $f['status'] === 'completed' ? 'success' : ($f['status'] === 'overdue' ? 'danger' : 'info') ?>">
                                    <?= $f['status'] === 'completed' ? 'FULLY REPAID' : Security::e($f['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <a href="?route=financing_view&id=<?= (int) $f['id'] ?>" class="btn btn-sm btn-outline">View</a>
                                    <a href="?route=statement_pdf&id=<?= (int) $f['id'] ?>" class="btn btn-sm btn-primary">PDF</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

