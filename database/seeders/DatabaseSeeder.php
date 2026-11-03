<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Helpers\Security;
use PDO;

/**
 * Clean Production Database Baseline Seeder
 *
 * Strictly installs ONLY essential system structure:
 * - 4 Required Roles (admin, branch_manager, loan_officer, client)
 * - 13 System Permissions
 * - Institutional System Settings
 * - 1 Default Head Office Branch (editable in Branch Management)
 * - 1 Initial System Administrator Account (`admin` / `Admin@123`) so you can sign in and configure your institution
 *
 * Zero fabricated clients, zero fake financings, zero fake repayments, zero demo fixtures.
 */
final class DatabaseSeeder
{
    public static function runSchema(PDO $pdo): void
    {
        $sqlPath = dirname(__DIR__) . '/schema.sql';
        $sql = file_get_contents($sqlPath);
        if ($sql === false) {
            throw new \RuntimeException('Unable to read database/schema.sql');
        }
        $pdo->exec($sql);
    }

    public static function seedSystemBaseline(PDO $pdo): void
    {
        // 1. Roles
        $roles = [
            ['slug' => 'admin', 'name_so' => 'Maamulaha Guud', 'name_en' => 'System Administrator', 'desc' => 'Full institution management and governance'],
            ['slug' => 'branch_manager', 'name_so' => 'Maamulaha Laanta', 'name_en' => 'Branch Manager', 'desc' => 'Branch portfolio oversight, >$5,000 & group approvals, charity waivers'],
            ['slug' => 'loan_officer', 'name_so' => 'Sarkaalka Maalgelinta', 'name_en' => 'Loan Officer', 'desc' => 'Client registration, financing applications, <=$5,000 approvals, disbursements & repayments'],
            ['slug' => 'client', 'name_so' => 'Macmiil', 'name_en' => 'Client', 'desc' => 'Self-service access to own financing, schedule, repayments and PDF statements'],
        ];

        $rStmt = $pdo->prepare('
            INSERT INTO roles (slug, name_so, name_en, description)
            VALUES (:slug, :n_so, :n_en, :desc)
            ON DUPLICATE KEY UPDATE name_so = VALUES(name_so), name_en = VALUES(name_en)
        ');
        foreach ($roles as $r) {
            $rStmt->execute([
                ':slug' => $r['slug'],
                ':n_so' => $r['name_so'],
                ':n_en' => $r['name_en'],
                ':desc' => $r['desc'],
            ]);
        }

        // 2. Permissions
        $permissions = [
            ['branches.manage', 'administration', 'Create and manage institution branches'],
            ['users.manage', 'administration', 'Create and manage staff user accounts'],
            ['products.manage', 'financing', 'Create, update and deactivate financing products'],
            ['clients.register', 'clients', 'Register and update client profiles and guarantors'],
            ['clients.transfer', 'clients', 'Transfer clients between branches'],
            ['applications.create', 'financing', 'Create and submit financing applications'],
            ['applications.approve_standard', 'financing', 'Approve financing applications up to $5,000'],
            ['applications.approve_high', 'financing', 'Approve financing applications above $5,000 and group financing'],
            ['financings.disburse', 'financing', 'Initiate simulated mobile money disbursements'],
            ['repayments.record', 'payments', 'Record cash and mobile money repayments'],
            ['charity.waive', 'risk', 'Waive charity late charges with mandatory reason'],
            ['statements.generate', 'reports', 'Generate and download PDF client statements'],
            ['audit.view', 'administration', 'View security and operational audit logs'],
        ];
        $pStmt = $pdo->prepare('
            INSERT IGNORE INTO permissions (slug, module, description)
            VALUES (:slug, :mod, :desc)
        ');
        foreach ($permissions as $p) {
            $pStmt->execute([':slug' => $p[0], ':mod' => $p[1], ':desc' => $p[2]]);
        }

        // 3. System Settings
        $settings = [
            ['charity_late_fee_amount', '15.00', 'Fixed late charge ($ USD) assessed >7 days after missed due date, directed to Charity Account'],
            ['approval_officer_threshold', '5000.00', 'Maximum financing amount ($ USD) a Loan Officer can approve'],
            ['late_charge_grace_days', '7', 'Grace period in days after due date before charity charge applies'],
            ['disbursement_expiry_days', '30', 'Number of days before an approved undisbursed application expires'],
            ['institution_phone', '+252 61 500 0000', 'Official institution contact phone (configurable)'],
            ['institution_email', 'info@simda.so', 'Official institution contact email (configurable)'],
            ['institution_address', 'Mogadishu, Somalia', 'Headquarters address (configurable)'],
        ];
        $sStmt = $pdo->prepare('
            INSERT INTO system_settings (setting_key, setting_value, description)
            VALUES (:k, :v, :d)
            ON DUPLICATE KEY UPDATE description = VALUES(description)
        ');
        foreach ($settings as $s) {
            $sStmt->execute([':k' => $s[0], ':v' => $s[1], ':d' => $s[2]]);
        }

        // 4. Default Head Office Branch
        $bStmt = $pdo->prepare('
            INSERT INTO branches (code, name, city, district, address, phone, manager_name, is_active)
            VALUES (:code, :name, :city, :dist, :addr, :phone, :mgr, 1)
            ON DUPLICATE KEY UPDATE name = VALUES(name)
        ');
        $bStmt->execute([
            ':code' => 'HQ-01',
            ':name' => 'Head Office Branch',
            ':city' => 'Mogadishu',
            ':dist' => 'Central',
            ':addr' => 'Headquarters',
            ':phone' => '+252 61 500 0000',
            ':mgr' => 'Branch Manager',
        ]);

        // 5. Initial System Administrator Account (Required for first login)
        $adminRoleId = (int) $pdo->query("SELECT id FROM roles WHERE slug = 'admin' LIMIT 1")->fetchColumn();
        $uStmt = $pdo->prepare('
            INSERT INTO users (role_id, branch_id, full_name, username, email, phone, password_hash, preferred_locale, is_active, is_synthetic_demo)
            VALUES (:rid, NULL, :name, :uname, :email, :phone, :phash, \'en\', 1, 0)
            ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), is_synthetic_demo = 0
        ');
        $uStmt->execute([
            ':rid' => $adminRoleId,
            ':name' => 'System Administrator',
            ':uname' => 'admin',
            ':email' => 'admin@simda.so',
            ':phone' => '+252 61 500 0000',
            ':phash' => Security::hashPassword('Admin@123'),
        ]);
    }
}
