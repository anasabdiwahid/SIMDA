<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Services\CreditScoreEngine;

$isSo = Lang::locale() === 'so';
$scores = $scores ?? [];
?>

<div class="card animate-in" style="margin-bottom:22px;">
    <h2 style="font-size:1.2rem;margin:6px 0 12px;"><?= Security::e(Lang::get('menu_credit_scores')) ?></h2>

    <!-- Brief, Module 9: the formula must be explainable on screen. Collapsed by default. -->
    <details class="score-howto" style="margin-bottom:16px;border:1px solid var(--border-color);border-radius:10px;padding:10px 14px;">
        <summary style="cursor:pointer;font-weight:700;"><?= $isSo ? 'Sidee loo xisaabiyaa dhibcaha?' : 'How is the score calculated?' ?></summary>
        <div style="font-size:0.85rem;color:var(--text-secondary);line-height:1.7;margin-top:8px;">
            <p style="margin:0 0 6px;"><?= $isSo
                ? 'Macmiil cusub wuxuu ka bilaabaa <strong>600</strong>. Dhibcuhu waxay u dhexeeyaan <strong>300</strong> iyo <strong>850</strong>, waxaana dib loo xisaabiyaa lacag-bixin kasta iyo taariikh kasta oo la dhaafo.'
                : 'New clients start at <strong>600</strong>. The score stays between <strong>300</strong> and <strong>850</strong> and is recalculated after every repayment and every missed due date.' ?></p>
            <ul style="margin:0;padding-left:18px;">
                <li><?= $isSo ? '+15 qayb kasta oo waqtigeeda la bixiyay' : '+15 for each installment paid on time' ?></li>
                <li><?= $isSo ? '+25 marka dayn si buuxda loo bixiyo waqtigeeda ka hor' : '+25 for each financing repaid in full early' ?></li>
                <li><?= $isSo ? '−10 / −25 / −45 qayb la bixiyay 1–7 / 8–30 / in ka badan 30 maalmood dib u dhac' : '−10 / −25 / −45 for an installment paid 1–7 / 8–30 / more than 30 days late' ?></li>
                <li><?= $isSo ? '−15 / −35 / −60 qayb aan weli la bixin oo dhaaftay 1–7 / 8–30 / in ka badan 30 maalmood' : '−15 / −35 / −60 for an installment still unpaid 1–7 / 8–30 / more than 30 days after its due date' ?></li>
                <li><?= $isSo ? 'Ganaax sadaqo oo la cafiyay dhibcaha ma dhimo.' : 'A waived charity charge never lowers the score.' ?></li>
            </ul>
        </div>
    </details>

    <?php if (empty($scores)): ?>
        <p style="color:var(--text-muted);padding:18px 0;"><?= $isSo ? 'Weli ma jiraan macaamiil dhibco leh.' : 'No clients with a credit score yet.' ?></p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th><?= $isSo ? 'Macmiilka' : 'Client' ?></th>
                    <th><?= $isSo ? 'Laanta' : 'Branch' ?></th>
                    <th><?= $isSo ? 'Dhibcaha Hadda' : 'Current Score' ?></th>
                    <th><?= $isSo ? 'Kii Hore' : 'Previous' ?></th>
                    <th><?= $isSo ? 'Isbeddelkii U Dambeeyay' : 'Last Change' ?></th>
                    <th><?= $isSo ? 'Heerka' : 'Category' ?></th>
                    <th><?= $isSo ? 'Waqtiga / Dib u dhac / Hore' : 'On-Time / Late / Early' ?></th>
                    <th><?= $isSo ? 'Sababta Isbeddelka' : 'Reason for Last Change' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scores as $s): ?>
                    <?php $cat = (string) $s['score_category']; $d = (int) $s['last_delta']; ?>
                    <tr>
                        <td><strong><?= Security::e($s['client_name']) ?></strong> (<?= Security::e($s['client_code']) ?>)</td>
                        <td><?= Security::e($s['branch_name']) ?></td>
                        <td class="num-tabular" style="font-size:1.05rem;font-weight:800;"><?= (int) $s['current_score'] ?></td>
                        <td class="num-tabular"><?= (int) $s['previous_score'] ?></td>
                        <td class="num-tabular">
                            <span style="font-weight:700;color:<?= $d > 0 ? 'var(--color-success)' : ($d < 0 ? 'var(--color-danger)' : 'var(--text-muted)') ?>;">
                                <?= $d > 0 ? '+' . $d : $d ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= in_array($cat, ['excellent', 'good'], true) ? 'success' : ($cat === 'fair' ? 'info' : ($cat === 'new' ? 'secondary' : 'danger')) ?>">
                                <?= Security::e(CreditScoreEngine::label($cat, $isSo)) ?>
                            </span>
                        </td>
                        <td class="num-tabular" style="white-space:nowrap;">
                            <span class="badge badge-success"><?= (int) $s['on_time_payments_count'] ?> <?= $isSo ? 'Waqtiga' : 'On-Time' ?></span>
                            <span class="badge badge-danger"><?= (int) $s['late_payments_count'] ?> <?= $isSo ? 'Dib u dhac' : 'Late' ?></span>
                            <span class="badge badge-gold"><?= (int) $s['early_settlements_count'] ?> <?= $isSo ? 'Hore' : 'Early' ?></span>
                        </td>
                        <td style="font-size:0.82rem;color:var(--text-secondary);">
                            <?= Security::e($isSo ? (string) $s['last_change_reason_so'] : (string) $s['last_change_reason_en']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
