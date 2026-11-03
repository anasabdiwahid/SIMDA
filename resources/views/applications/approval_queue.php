<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$currentUserId = (int) ($user['id'] ?? 0);
$role = $user['role_slug'] ?? '';
?>

<div class="card animate-in">
    <span class="badge badge-gold"><?= Icons::svg('check-square', 13) ?> Module 4 — Dual-Threshold Approval Workflow</span>
    <h2 style="font-size:1.2rem;margin:6px 0;"><?= Security::e(Lang::get('menu_approval_queue')) ?></h2>
    <p style="font-size:0.84rem;color:var(--text-secondary);margin-bottom:18px;">
        Thresholds: Loan Officer (&le; $5,000); Branch Manager (&gt; $5,000 or Group Financing). Conflict-of-interest rule: Application creators cannot approve their own applications.
    </p>

    <?php if (empty($queue)): ?>
        <div style="padding:32px;text-align:center;color:var(--text-muted);">
            <strong>No Pending Applications in Queue</strong>
            <div>All submitted financing applications have been reviewed.</div>
        </div>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:14px;">
            <?php foreach ($queue as $q): ?>
                <?php
                $isCreator = ((int) $q['created_by'] === $currentUserId);
                $needsBM = ((float) $q['requested_amount'] > 5000.00 || !empty($q['group_id']));
                $canApproveRole = $needsBM ? in_array($role, ['branch_manager', 'admin'], true) : in_array($role, ['loan_officer', 'branch_manager', 'admin'], true);
                ?>
                <div class="card" style="border-left:4px solid var(--simda-gold);background:var(--bg-elevated);">
                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:14px;">
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
                                <strong class="num-tabular" style="font-size:1rem;"><?= Security::e($q['application_number']) ?></strong>
                                <span class="badge badge-gold"><?= Security::e(strtoupper($q['product_type'])) ?></span>
                                <span class="badge badge-info">Credit Score: <?= (int) ($q['current_score'] ?? 600) ?> (<?= Security::e($q['score_category'] ?? 'fair') ?>)</span>
                            </div>
                            <div style="font-size:0.88rem;">
                                <strong>Client:</strong> <?= Security::e($q['client_name']) ?> (<?= Security::e($q['client_code']) ?>) •
                                <strong>Branch:</strong> <?= Security::e($q['branch_name']) ?> •
                                <strong>Created By:</strong> <?= Security::e($q['creator_name']) ?>
                            </div>
                            <div style="font-size:0.84rem;color:var(--text-secondary);margin-top:4px;">
                                <strong>Purpose:</strong> <?= Security::e($q['purpose']) ?>
                                <?php if (!empty($q['murabaha_goods_description'])): ?>
                                    | <strong>Murabaha Goods:</strong> <?= Security::e($q['murabaha_goods_description']) ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="text-align:right;">
                            <div style="font-size:0.74rem;color:var(--text-muted);">Requested Principal / Projected Total Due</div>
                            <div class="num-tabular" style="font-size:1.2rem;font-weight:800;">
                                <?= Security::formatMoney($q['requested_amount']) ?> → <?= Security::formatMoney($q['projected_total_due']) ?>
                            </div>
                            <div style="font-size:0.76rem;color:var(--text-secondary);">
                                <?= (int) $q['term_installments'] ?> <?= Security::e($q['repayment_frequency']) ?> installments @ <?= Security::e($q['agreed_rate']) ?>%
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border-color);display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;">
                        <div style="font-size:0.8rem;">
                            <?php if ($isCreator): ?>
                                <span class="badge badge-danger"><?= Icons::svg('alert-triangle', 12) ?> Conflict of Interest Guard: Created by You (Requires peer/manager approval)</span>
                            <?php elseif (!$canApproveRole): ?>
                                <span class="badge badge-warning">Requires Branch Manager Approval (&gt;$5,000 or Group)</span>
                            <?php else: ?>
                                <span class="badge badge-success"><?= Icons::svg('check-circle', 12) ?> Authorized to review this application</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!$isCreator && $canApproveRole): ?>
                            <form method="POST" action="?route=application_review" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;" data-confirm="Record this approval/rejection decision in the SIMDA audit log?">
                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                <input type="hidden" name="application_id" value="<?= (int) $q['id'] ?>">
                                <input type="text" name="comments" class="form-control" placeholder="Review comments / conditions..." required style="width:230px;padding:6px 10px;font-size:0.8rem;">
                                <button type="submit" name="decision" value="approved" class="btn btn-sm btn-primary"><?= Icons::svg('check-circle', 14) ?> <span>Approve</span></button>
                                <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-danger"><?= Icons::svg('x-circle', 14) ?> <span>Reject</span></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
