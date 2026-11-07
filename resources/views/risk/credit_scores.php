<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
?>

<div class="card animate-in" style="margin-bottom:22px;">
    <h2 style="font-size:1.2rem;margin:6px 0;"><?= Security::e(Lang::get('menu_credit_scores')) ?></h2>
    <p style="font-size:0.84rem;color:var(--text-secondary);margin-bottom:16px;">
        Formula: <code>Score = 600 + (OnTime × 15) + (EarlySettlement × 25) − (Late1_7d × 10) − (Late8_30d × 25) − (Late&gt;30d × 45) − (UnpaidOverduePenalties)</code>. Waived charity late charges do not penalize the score.
    </p>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Branch</th>
                    <th>Current Score</th>
                    <th>Previous</th>
                    <th>Last Change</th>
                    <th>Category</th>
                    <th>On-Time / Late / Early</th>
                    <th>Reason for Last Change</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scores as $s): ?>
                    <tr>
                        <td><strong><?= Security::e($s['client_name']) ?></strong> (<?= Security::e($s['client_code']) ?>)</td>
                        <td><?= Security::e($s['branch_name']) ?></td>
                        <td class="num-tabular" style="font-size:1.05rem;font-weight:800;"><?= (int) $s['current_score'] ?></td>
                        <td class="num-tabular"><?= (int) $s['previous_score'] ?></td>
                        <td class="num-tabular">
                            <?php $d = (int) $s['last_delta']; ?>
                            <span style="font-weight:700;color:<?= $d > 0 ? 'var(--color-success)' : ($d < 0 ? 'var(--color-danger)' : 'var(--text-muted)') ?>;">
                                <?= $d > 0 ? '+' . $d : $d ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= in_array($s['score_category'], ['excellent', 'good'], true) ? 'success' : ($s['score_category'] === 'fair' ? 'info' : 'danger') ?>">
                                <?= Security::e($s['score_category']) ?>
                            </span>
                        </td>
                        <td class="num-tabular" style="white-space:nowrap;">
                            <span class="badge badge-success"><?= (int) $s['on_time_payments_count'] ?> On-Time</span>
                            <span class="badge badge-danger"><?= (int) $s['late_payments_count'] ?> Late</span>
                            <span class="badge badge-gold"><?= (int) $s['early_settlements_count'] ?> Early</span>
                        </td>
                        <td style="font-size:0.82rem;color:var(--text-secondary);">
                            <?= Security::e($s['last_change_reason_en']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
