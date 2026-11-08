<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use App\Helpers\Security;
use PDO;

/**
 * Portal accounts for clients.
 *
 * Every client gets a login account. They sign in with their email or phone number and the
 * default password below, and are told to change it on their first visit
 * (users.must_change_password stays 1 until they do).
 */
final class ClientAccountService
{
    public const DEFAULT_PASSWORD = '123456@@';
    public const MIN_PASSWORD_LENGTH = 8;

    /** Adds users.must_change_password on older installs. */
    public static function ensureSchema(PDO $db): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $has = $db->query("SHOW COLUMNS FROM users LIKE 'must_change_password'")->fetchColumn() !== false;
        if (!$has) {
            $db->exec('ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash');
        }
        $done = true;
    }

    /** Last 9 digits of a phone number, so 061..., 61... and +25261... all match. '' when too short. */
    public static function phoneKey(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        return strlen($digits) >= 7 ? substr($digits, -9) : '';
    }

    /**
     * Creates the login account for a client and returns its user id.
     * Uses the client's email when no other account has it, else a placeholder address
     * (the client can still sign in with their phone number).
     */
    public static function createForClient(PDO $db, string $clientCode, string $fullName, string $phone, ?string $email, int $branchId, bool $isDemo = false): int
    {
        self::ensureSchema($db);
        $roleId = (int) $db->query("SELECT id FROM roles WHERE slug = 'client' LIMIT 1")->fetchColumn();
        if ($roleId <= 0) {
            throw new \RuntimeException('The client role is missing.');
        }

        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $clientCode) ?: 'client');
        $username = $base;
        for ($i = 2; self::exists($db, 'username', $username); $i++) {
            $username = $base . $i;
        }

        $email = trim((string) $email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || self::exists($db, 'email', $email)) {
            $email = $username . '@client.simda.so';
        }

        $db->prepare('
            INSERT INTO users (role_id, branch_id, full_name, username, email, phone, password_hash, must_change_password, is_synthetic_demo)
            VALUES (:rid, :bid, :name, :uname, :email, :phone, :phash, 1, :demo)
        ')->execute([
            ':rid' => $roleId,
            ':bid' => $branchId > 0 ? $branchId : null,
            ':name' => $fullName,
            ':uname' => $username,
            ':email' => $email,
            ':phone' => $phone,
            ':phash' => Security::hashPassword(self::DEFAULT_PASSWORD),
            ':demo' => $isDemo ? 1 : 0,
        ]);
        return (int) $db->lastInsertId();
    }

    /** Gives every existing client without a login account one (runs once per request at most). */
    public static function backfill(PDO $db): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        self::ensureSchema($db);
        $rows = $db->query('
            SELECT id, client_code, full_name, phone, email, branch_id, is_synthetic_demo
            FROM clients
            WHERE user_id IS NULL AND deleted_at IS NULL
        ')->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $c) {
            $db->beginTransaction();
            try {
                $uid = self::createForClient(
                    $db,
                    (string) $c['client_code'],
                    (string) $c['full_name'],
                    (string) $c['phone'],
                    $c['email'] !== null ? (string) $c['email'] : null,
                    (int) $c['branch_id'],
                    !empty($c['is_synthetic_demo'])
                );
                $db->prepare('UPDATE clients SET user_id = :uid WHERE id = :id AND user_id IS NULL')
                    ->execute([':uid' => $uid, ':id' => (int) $c['id']]);
                $db->commit();
                AuditLogger::log('client.portal_account_created', 'clients', (int) $c['id'], "Login account created for client {$c['client_code']} with the default password.");
            } catch (\Throwable $e) {
                $db->rollBack();
                error_log('Client account backfill failed for client ' . $c['id'] . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Finds the account for a login identifier: user name or email first, then phone number.
     * A phone number only matches when exactly one account has it.
     */
    public static function findLoginUser(PDO $db, string $identifier): ?array
    {
        self::ensureSchema($db);
        self::backfill($db);
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $select = '
            SELECT u.*, r.slug AS role_slug, r.name_so AS role_name_so, r.name_en AS role_name_en,
                   b.name AS branch_name, b.code AS branch_code,
                   c.id AS client_id, c.client_code
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN branches b ON b.id = u.branch_id
            LEFT JOIN clients c ON c.user_id = u.id
            WHERE u.deleted_at IS NULL AND ';

        $st = $db->prepare($select . '(u.email = :id1 OR u.username = :id2) LIMIT 1');
        $st->execute([':id1' => $identifier, ':id2' => $identifier]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            return $user;
        }

        $key = self::phoneKey($identifier);
        if ($key === '' || preg_match('/[a-zA-Z@]/', $identifier)) {
            return null;
        }
        $st = $db->prepare($select . "RIGHT(REGEXP_REPLACE(u.phone, '[^0-9]', ''), 9) = :pk LIMIT 2");
        $st->execute([':pk' => $key]);
        $matches = $st->fetchAll(PDO::FETCH_ASSOC);
        return count($matches) === 1 ? $matches[0] : null;
    }

    /** Sets a new password chosen by the user and clears the must-change flag. */
    public static function changeOwnPassword(PDO $db, int $userId, string $current, string $new, string $confirm, string $locale): void
    {
        self::ensureSchema($db);
        $tr = static fn(string $so, string $en, string $ar): string => $locale === 'so' ? $so : ($locale === 'ar' ? $ar : $en);

        $st = $db->prepare('SELECT password_hash FROM users WHERE id = :id AND deleted_at IS NULL');
        $st->execute([':id' => $userId]);
        $hash = (string) $st->fetchColumn();
        if ($hash === '' || !Security::verifyPassword($current, $hash)) {
            throw new \RuntimeException($tr('Password-ka hadda waa khalad.', 'Your current password is wrong.', 'كلمة المرور الحالية غير صحيحة.'));
        }
        if (strlen($new) < self::MIN_PASSWORD_LENGTH) {
            throw new \RuntimeException($tr('Password-ka cusub waa inuu ahaadaa ugu yaraan 8 xaraf.', 'The new password must be at least 8 characters.', 'يجب ألا تقل كلمة المرور الجديدة عن 8 أحرف.'));
        }
        if ($new !== $confirm) {
            throw new \RuntimeException($tr('Labada password ee cusub isma waafaqsana.', 'The two new passwords do not match.', 'كلمتا المرور الجديدتان غير متطابقتين.'));
        }
        if ($new === self::DEFAULT_PASSWORD || Security::verifyPassword($new, $hash)) {
            throw new \RuntimeException($tr('Password-ka cusub waa inuu ka duwanaadaa kii hore.', 'The new password must be different from the old one.', 'يجب أن تختلف كلمة المرور الجديدة عن القديمة.'));
        }

        $db->prepare('UPDATE users SET password_hash = :h, must_change_password = 0 WHERE id = :id')
            ->execute([':h' => Security::hashPassword($new), ':id' => $userId]);
        AuditLogger::log('auth.password_changed', 'users', $userId, 'User changed own password.', [], $userId);
    }

    private static function exists(PDO $db, string $column, string $value): bool
    {
        $st = $db->prepare("SELECT 1 FROM users WHERE {$column} = :v LIMIT 1");
        $st->execute([':v' => $value]);
        return (bool) $st->fetchColumn();
    }
}
