<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
?>

<div class="card animate-in">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;margin-bottom:16px;">
        <div>
            <h2 style="font-size:1.2rem;margin-top:6px;"><?= Security::e(Lang::get('menu_sms_outbox')) ?></h2>
            <p style="font-size:0.84rem;color:var(--text-secondary);">
                Dispatches 3 days before due date (`upcoming_3_days`) and 1 day after a missed due date (`overdue_1_day`). Deduplicated per installment and reminder type; paid installments never receive reminders.
            </p>
        </div>
        <form method="POST" action="?route=sms_run_dispatcher">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
            <button type="submit" class="btn btn-primary btn-sm">
                <?= Icons::svg('refresh', 14) ?>
                <span>Run Reminder Dispatcher Now</span>
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="simda-table">
            <thead>
                <tr>
                    <th>Client & Phone</th>
                    <th>Contract</th>
                    <th>Type</th>
                    <th>Due Date & Amount</th>
                    <th>Message (English & Somali)</th>
                    <th>Sent At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">No SMS reminders in outbox yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $m): ?>
                        <tr>
                            <td>
                                <strong><?= Security::e($m['client_name']) ?></strong>
                                <div class="num-tabular" style="font-size:0.76rem;color:var(--text-muted);"><?= Security::e($m['phone']) ?></div>
                            </td>
                            <td class="num-tabular"><?= Security::e($m['financing_number']) ?></td>
                            <td>
                                <span class="badge badge-<?= $m['reminder_type'] === 'overdue_1_day' ? 'danger' : 'info' ?>">
                                    <?= Security::e($m['reminder_type']) ?>
                                </span>
                            </td>
                            <td class="num-tabular">
                                <div><?= Security::e($m['due_date']) ?></div>
                                <strong><?= Security::formatMoney($m['amount_due']) ?></strong>
                            </td>
                            <td style="max-width:440px;font-size:0.82rem;">
                                <div style="margin-bottom:4px;"><strong>EN:</strong> <?= Security::e($m['message_en']) ?></div>
                                <div style="color:var(--text-secondary);"><strong>SO:</strong> <?= Security::e($m['message_so']) ?></div>
                            </td>
                            <td class="num-tabular" style="font-size:0.76rem;"><?= Security::e($m['sent_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
