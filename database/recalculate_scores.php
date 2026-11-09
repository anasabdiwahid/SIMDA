<?php
declare(strict_types=1);

/**
 * Recalculates every client's credit score with the current formula (CreditScoreEngine, README section 4).
 * Run after changing the formula or the scale:  C:\xampp\php\php.exe database/recalculate_scores.php
 */
require_once dirname(__DIR__) . '/bootstrap.php';

use App\Helpers\Database;
use App\Services\CreditScoreEngine;

$pdo = Database::connection();
$ids = $pdo->query('SELECT id FROM clients WHERE deleted_at IS NULL ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
foreach ($ids as $id) {
    $r = CreditScoreEngine::recalculateForClient(
        (int) $id,
        'Score recalculated on the 300-850 scale.',
        'Dhibcaha waxaa dib loogu xisaabiyay qiyaasta 300-850.',
        $pdo
    );
    echo "client {$id}: {$r['previous_score']} -> {$r['current_score']} ({$r['score_category']})\n";
}
echo count($ids) . " client(s) recalculated.\n";
