<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Helpers\Database;
use Database\Seeders\DatabaseSeeder;

echo "=== SIMDA Clean Production Database Setup ===\n";

Database::ensureDatabaseExists(false);
Database::useTestDatabase(false);
$pdo = Database::connection();

// Wipe all tables to ensure zero demo/fake records remain
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$tables = [
    'audit_logs', 'notifications', 'sms_outbox', 'credit_scores', 'repayments',
    'charity_charges', 'installments', 'financings', 'financing_applications',
    'products', 'group_members', 'groups', 'guarantors', 'clients',
    'login_attempts', 'users', 'branches', 'role_permissions', 'permissions',
    'roles', 'system_settings',
];
foreach ($tables as $t) {
    $pdo->exec("DROP TABLE IF EXISTS `{$t}`");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

echo "-> Applying relational schema (21 tables) to main database...\n";
DatabaseSeeder::runSchema($pdo);

echo "-> Installing clean system baseline (Roles, Permissions, Settings, HQ Branch & Admin account)...\n";
DatabaseSeeder::seedSystemBaseline($pdo);

echo "=== Clean Production Database Ready (Zero Demo Data) ===\n";
