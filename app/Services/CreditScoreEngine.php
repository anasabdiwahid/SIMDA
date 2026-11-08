<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;

/**
 * Module 9 — Credit Score Engine (0 to 100)
 *
 * New clients start at 0; trust is built payment by payment.
 *   + 5  per installment paid on time (or late with the charity charge waived)
 *   + 10 per full early settlement
 *   - 3 / 8 / 15 per installment paid 1–7 / 8–30 / >30 days late
 *   - 5 / 10 / 20 per installment unpaid and 1–7 / 8–30 / >30 days overdue
 * Clamped to 0–100.
 *
 * Categories: new (no payments yet); otherwise 80+ excellent, 60+ good, 40+ fair, 20+ caution,
 * below 20 high_risk. A client who has never paid late is never placed below fair.
 */
final class CreditScoreEngine
{
    public const BASE_SCORE = 0;
    public const MIN_SCORE = 0;
    public const MAX_SCORE = 100;

    public static function initializeForClient(int $clientId, ?PDO $pdo = null): void
    {
        $db = $pdo ?? Database::connection();
        $stmt = $db->prepare('SELECT id FROM credit_scores WHERE client_id = :cid LIMIT 1');
        $stmt->execute([':cid' => $clientId]);
        if ($stmt->fetch()) {
            return;
        }

        $history = [[
            'date' => date('Y-m-d H:i:s'),
            'previous' => self::BASE_SCORE,
            'score' => self::BASE_SCORE,
            'delta' => 0,
            'reason_en' => 'New client: score starts at 0 and grows with on-time payments.',
            'reason_so' => 'Macmiil cusub: dhibcuhu waxay ka bilaabmaan 0, waxayna korayaan marka uu waqtiga ku bixiyo.',
        ]];

        $ins = $db->prepare("
            INSERT INTO credit_scores (
                client_id, current_score, previous_score, last_delta, score_category,
                last_change_reason_so, last_change_reason_en, history_log
            ) VALUES (
                :cid, 0, 0, 0, 'new',
                'Macmiil cusub: dhibcuhu waxay ka bilaabmaan 0, waxayna korayaan marka uu waqtiga ku bixiyo.',
                'New client: score starts at 0 and grows with on-time payments.',
                :history
            )
        ");
        $ins->execute([
            ':cid' => $clientId,
            ':history' => json_encode($history, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public static function recalculateForClient(
        int $clientId,
        string $reasonEn = 'Repayment performance evaluation update',
        string $reasonSo = 'Cusbooneysiinta qiimeynta bixinta maalgelinta',
        ?PDO $pdo = null,
        ?string $referenceDate = null
    ): array {
        $db = $pdo ?? Database::connection();
        self::initializeForClient($clientId, $db);

        $refDate = $referenceDate ?? date('Y-m-d');

        $curStmt = $db->prepare('SELECT * FROM credit_scores WHERE client_id = :cid LIMIT 1');
        $curStmt->execute([':cid' => $clientId]);
        $existing = $curStmt->fetch();
        $previousScore = (int) ($existing['current_score'] ?? self::BASE_SCORE);

        // Fetch all installments for this client across active/completed/overdue financings
        $instStmt = $db->prepare("
            SELECT i.*,
                   f.status AS financing_status,
                   cc.status AS charity_status
            FROM installments i
            INNER JOIN financings f ON f.id = i.financing_id
            LEFT JOIN charity_charges cc ON cc.installment_id = i.id
            WHERE f.client_id = :cid
        ");
        $instStmt->execute([':cid' => $clientId]);
        $installments = $instStmt->fetchAll();

        // Count early settlements
        $earlyStmt = $db->prepare('
            SELECT COUNT(*) FROM repayments
            WHERE client_id = :cid AND is_early_settlement = 1
        ');
        $earlyStmt->execute([':cid' => $clientId]);
        $earlySettlementsCount = (int) $earlyStmt->fetchColumn();

        $score = self::BASE_SCORE;
        $onTimeCount = 0;
        $lateCount = 0;

        foreach ($installments as $inst) {
            $dueDate = $inst['due_date'];
            $isWaived = ($inst['charity_status'] === 'waived');

            if ($inst['status'] === 'paid' && !empty($inst['paid_at'])) {
                $paidDate = substr((string) $inst['paid_at'], 0, 10);
                $daysDiff = (int) ((strtotime($paidDate) - strtotime($dueDate)) / 86400);

                if ($daysDiff <= 0) {
                    $onTimeCount++;
                    $score += 5;
                } elseif ($isWaived) {
                    // Waived late charges do not penalize the client's score
                    $onTimeCount++;
                } else {
                    $lateCount++;
                    if ($daysDiff <= 7) {
                        $score -= 3;
                    } elseif ($daysDiff <= 30) {
                        $score -= 8;
                    } else {
                        $score -= 15;
                    }
                }
            } elseif ((float) $inst['remaining_balance'] > 0 && $dueDate < $refDate) {
                // Currently unpaid overdue installment
                if (!$isWaived) {
                    $lateCount++;
                    $daysOverdue = (int) ((strtotime($refDate) - strtotime($dueDate)) / 86400);
                    if ($daysOverdue <= 7) {
                        $score -= 5;
                    } elseif ($daysOverdue <= 30) {
                        $score -= 10;
                    } else {
                        $score -= 20;
                    }
                }
            }
        }

        // Bonus for full early repayment
        $score += ($earlySettlementsCount * 10);

        // Clamp to [0, 100]
        $clampedScore = max(self::MIN_SCORE, min(self::MAX_SCORE, $score));
        $delta = $clampedScore - $previousScore;
        $category = self::categoryForScore($clampedScore, $onTimeCount + $lateCount + $earlySettlementsCount > 0, $lateCount > 0);

        $history = json_decode((string) ($existing['history_log'] ?? '[]'), true);
        if (!is_array($history)) {
            $history = [];
        }
        $history[] = [
            'date' => date('Y-m-d H:i:s'),
            'previous' => $previousScore,
            'score' => $clampedScore,
            'delta' => $delta,
            'reason_en' => $reasonEn,
            'reason_so' => $reasonSo,
        ];
        if (count($history) > 25) {
            $history = array_slice($history, -25);
        }

        $upd = $db->prepare('
            UPDATE credit_scores
            SET previous_score = :prev,
                current_score = :curr,
                last_delta = :delta,
                score_category = :cat,
                last_change_reason_so = :r_so,
                last_change_reason_en = :r_en,
                on_time_payments_count = :ontime,
                late_payments_count = :late,
                early_settlements_count = :early,
                history_log = :hist
            WHERE client_id = :cid
        ');
        $upd->execute([
            ':prev' => $previousScore,
            ':curr' => $clampedScore,
            ':delta' => $delta,
            ':cat' => $category,
            ':r_so' => $reasonSo,
            ':r_en' => $reasonEn,
            ':ontime' => $onTimeCount,
            ':late' => $lateCount,
            ':early' => $earlySettlementsCount,
            ':hist' => json_encode($history, JSON_UNESCAPED_UNICODE),
            ':cid' => $clientId,
        ]);

        return [
            'client_id' => $clientId,
            'previous_score' => $previousScore,
            'current_score' => $clampedScore,
            'last_delta' => $delta,
            'score_category' => $category,
            'on_time_payments_count' => $onTimeCount,
            'late_payments_count' => $lateCount,
            'early_settlements_count' => $earlySettlementsCount,
            'last_change_reason_en' => $reasonEn,
            'last_change_reason_so' => $reasonSo,
        ];
    }

    public static function categoryForScore(int $score, bool $hasHistory = true, bool $everLate = true): string
    {
        if (!$hasHistory) {
            return 'new';
        }
        if ($score >= 80) {
            return 'excellent';
        }
        if ($score >= 60) {
            return 'good';
        }
        if ($score >= 40 || !$everLate) {
            return 'fair';
        }
        if ($score >= 20) {
            return 'caution';
        }
        return 'high_risk';
    }


    /** Display label for a category, in Somali or English. */
    public static function label(string $category, bool $isSo): string
    {
        return match ($category) {
            'new' => $isSo ? 'Cusub' : 'New',
            'excellent' => $isSo ? 'Aad u fiican' : 'Excellent',
            'good' => $isSo ? 'Fiican' : 'Good',
            'fair' => $isSo ? 'Caadi' : 'Fair',
            'caution' => $isSo ? 'Taxaddar' : 'Caution',
            'high_risk' => $isSo ? 'Khatar' : 'High risk',
            default => ucfirst(str_replace('_', ' ', $category)),
        };
    }

    /** Colour tone for a category: green, gold, danger or muted. */
    public static function tone(string $category): string
    {
        return match ($category) {
            'excellent', 'good' => 'green',
            'fair' => 'gold',
            'caution', 'high_risk' => 'danger',
            default => 'muted',
        };
    }
}
