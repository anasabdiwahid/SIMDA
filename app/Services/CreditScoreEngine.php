<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;

/**
 * Module 9 — Deterministic Credit Score Engine
 *
 * Score Range: 300 to 850.
 * New Clients Baseline: 600.
 *
 * Documented Formula (also detailed in README.md):
 *   Base Score = 600
 *   + (On-Time Paid Installments * 15 points)
 *   + (Full Early Settlements * 25 bonus points)
 *   - (Installments Paid 1–7 Days Late * 10 points)
 *   - (Installments Paid 8–30 Days Late * 25 points)
 *   - (Installments Paid >30 Days Late * 45 points)
 *   - (Currently Unpaid Overdue Installments 1–7 Days * 15 points)
 *   - (Currently Unpaid Overdue Installments 8–30 Days * 35 points)
 *   - (Currently Unpaid Overdue Installments >30 Days * 60 points)
 *
 * Special Rules:
 * - If an installment has a WAIVED charity late charge, its late penalty is neutralized (0 penalty).
 * - Clamped strictly between 300 and 850.
 * - Categories:
 *   750–850: excellent
 *   670–749: good
 *   580–669: fair
 *   480–579: caution
 *   300–479: high_risk
 */
final class CreditScoreEngine
{
    public const BASE_SCORE = 600;
    public const MIN_SCORE = 300;
    public const MAX_SCORE = 850;

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
            'reason_en' => 'Initial client registration baseline score (600).',
            'reason_so' => 'Dhibcaha aasaasiga ah ee diiwaangelinta macmiilka cusub (600).',
        ]];

        $ins = $db->prepare("
            INSERT INTO credit_scores (
                client_id, current_score, previous_score, last_delta, score_category,
                last_change_reason_so, last_change_reason_en, history_log
            ) VALUES (
                :cid, 600, 600, 0, 'fair',
                'Dhibcaha aasaasiga ah ee diiwaangelinta macmiilka cusub (600).',
                'Initial client registration baseline score (600).',
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
                    $score += 15;
                } elseif ($isWaived) {
                    // Waived late charges do not penalize the client's score
                    $onTimeCount++;
                } else {
                    $lateCount++;
                    if ($daysDiff <= 7) {
                        $score -= 10;
                    } elseif ($daysDiff <= 30) {
                        $score -= 25;
                    } else {
                        $score -= 45;
                    }
                }
            } elseif ((float) $inst['remaining_balance'] > 0 && $dueDate < $refDate) {
                // Currently unpaid overdue installment
                if (!$isWaived) {
                    $lateCount++;
                    $daysOverdue = (int) ((strtotime($refDate) - strtotime($dueDate)) / 86400);
                    if ($daysOverdue <= 7) {
                        $score -= 15;
                    } elseif ($daysOverdue <= 30) {
                        $score -= 35;
                    } else {
                        $score -= 60;
                    }
                }
            }
        }

        // Bonus for full early repayment
        $score += ($earlySettlementsCount * 25);

        // Clamp to [300, 850]
        $clampedScore = max(self::MIN_SCORE, min(self::MAX_SCORE, $score));
        $delta = $clampedScore - $previousScore;
        $category = self::categoryForScore($clampedScore);

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

    public static function categoryForScore(int $score): string
    {
        if ($score >= 750) {
            return 'excellent';
        }
        if ($score >= 670) {
            return 'good';
        }
        if ($score >= 580) {
            return 'fair';
        }
        if ($score >= 480) {
            return 'caution';
        }
        return 'high_risk';
    }
}

