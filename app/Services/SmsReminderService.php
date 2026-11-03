<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use DateTimeImmutable;
use PDO;

/**
 * Module 11 — Simulated SMS Reminder Service (Bilingual Somali & English)
 *
 * Rules:
 * - 3 days before each due date: send `upcoming_3_days` reminder to in-app SMS outbox.
 * - 1 day after a missed due date: send `overdue_1_day` late notice.
 * - Messages generated in both Somali and English, including installment amount and due date.
 * - Each installment receives at most ONE reminder per type (enforced by unique key + check).
 * - Paid installments (`status = 'paid'` or `remaining_balance <= 0`) receive NO reminders.
 */
final class SmsReminderService
{
    public static function generateScheduledReminders(?string $referenceDate = null, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $refStr = $referenceDate ?? date('Y-m-d');
        $refDate = new DateTimeImmutable($refStr);

        $upcomingDueTarget = $refDate->modify('+3 days')->format('Y-m-d');
        $missedDueTarget = $refDate->modify('-1 day')->format('Y-m-d');

        $upcomingSent = self::dispatchForDueDate($db, $upcomingDueTarget, 'upcoming_3_days');
        $overdueSent = self::dispatchForDueDate($db, $missedDueTarget, 'overdue_1_day');

        return [
            'reference_date' => $refStr,
            'upcoming_3_days_sent' => $upcomingSent,
            'overdue_1_day_sent' => $overdueSent,
            'total_generated' => $upcomingSent + $overdueSent,
        ];
    }

    private static function dispatchForDueDate(PDO $db, string $targetDueDate, string $reminderType): int
    {
        $stmt = $db->prepare("
            SELECT i.id AS installment_id,
                   i.installment_number,
                   i.due_date,
                   i.remaining_balance,
                   f.id AS financing_id,
                   f.financing_number,
                   c.id AS client_id,
                   c.full_name AS client_name,
                   c.phone AS client_phone
            FROM installments i
            INNER JOIN financings f ON f.id = i.financing_id
            INNER JOIN clients c ON c.id = f.client_id
            LEFT JOIN sms_outbox s
              ON s.installment_id = i.id AND s.reminder_type = :rem_type
            WHERE i.due_date = :target_due
              AND i.status != 'paid'
              AND i.remaining_balance > 0
              AND f.status IN ('active', 'overdue')
              AND s.id IS NULL
        ");
        $stmt->execute([
            ':rem_type' => $reminderType,
            ':target_due' => $targetDueDate,
        ]);

        $rows = $stmt->fetchAll();
        $count = 0;

        $ins = $db->prepare("
            INSERT IGNORE INTO sms_outbox (
                client_id, financing_id, installment_id, phone,
                reminder_type, message_so, message_en, due_date, amount_due, status, sent_at
            ) VALUES (
                :cid, :fid, :iid, :phone,
                :type, :msg_so, :msg_en, :due_date, :amt, 'sent_simulated', NOW()
            )
        ");

        foreach ($rows as $r) {
            $amtFormatted = '$' . number_format((float) $r['remaining_balance'], 2, '.', ',');
            $dueFormatted = $r['due_date'];
            $name = $r['client_name'];
            $finNo = $r['financing_number'];
            $instNo = $r['installment_number'];

            if ($reminderType === 'upcoming_3_days') {
                $msgSo = "SIMDA Xusuusin: Macmiil {$name}, qaybta #{$instNo} ee maalgelintaada ({$finNo}) oo dhan {$amtFormatted} waxay ku egtahay {$dueFormatted} (3 maalmood kadib). Fadlan ku bixi waqtiga loogu talagalay.";
                $msgEn = "SIMDA Reminder: Dear {$name}, installment #{$instNo} for financing ({$finNo}) of {$amtFormatted} is due on {$dueFormatted} (in 3 days). Please ensure timely payment.";
            } else {
                $msgSo = "SIMDA Ogeysiis Dib-u-dhac: Macmiil {$name}, qaybta #{$instNo} ee maalgelintaada ({$finNo}) oo dhan {$amtFormatted} waxay ku ekeyd {$dueFormatted}. Fadlan si degdeg ah u bixi si aad uga fogaato khidmadda sadaqada ee dib-u-dhaca.";
                $msgEn = "SIMDA Late Notice: Dear {$name}, installment #{$instNo} for financing ({$finNo}) of {$amtFormatted} was due on {$dueFormatted}. Please settle immediately to avoid a charity late charge.";
            }

            $ins->execute([
                ':cid' => $r['client_id'],
                ':fid' => $r['financing_id'],
                ':iid' => $r['installment_id'],
                ':phone' => $r['client_phone'],
                ':type' => $reminderType,
                ':msg_so' => $msgSo,
                ':msg_en' => $msgEn,
                ':due_date' => $dueFormatted,
                ':amt' => $r['remaining_balance'],
            ]);

            if ($ins->rowCount() > 0) {
                $count++;
            }
        }

        return $count;
    }
}

