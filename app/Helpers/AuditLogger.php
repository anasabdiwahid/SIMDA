<?php
declare(strict_types=1);

namespace App\Helpers;

use PDO;

final class AuditLogger
{
    public static function log(
        string $action,
        string $entityType,
        ?int $entityId,
        string $description,
        array $metadata = [],
        ?int $overrideUserId = null,
        ?int $overrideBranchId = null
    ): void {
        try {
            $pdo = Database::connection();
            $userId = $overrideUserId ?? ($_SESSION['user']['id'] ?? null);
            $branchId = $overrideBranchId ?? ($_SESSION['user']['branch_id'] ?? null);
            $ip = Security::clientIp();

            $stmt = $pdo->prepare('
                INSERT INTO audit_logs (user_id, branch_id, action, entity_type, entity_id, description, metadata, ip_address)
                VALUES (:user_id, :branch_id, :action, :entity_type, :entity_id, :description, :metadata, :ip)
            ');
            $stmt->execute([
                ':user_id' => $userId,
                ':branch_id' => $branchId,
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':description' => $description,
                ':metadata' => !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
                ':ip' => $ip,
            ]);
        } catch (\Throwable) {
            // Never crash business operations if audit logging encounters a transient issue outside transaction
        }
    }
}

