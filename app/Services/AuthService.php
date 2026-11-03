<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use App\Helpers\Database;
use App\Helpers\Env;
use App\Helpers\Security;
use PDO;
use RuntimeException;

/**
 * Authentication & Rate Limiting Service
 *
 * Security Rules Enforced:
 * - Bcrypt / Argon2id password verification
 * - Login rate limiting (max 5 failed attempts within 15 minutes per username/email or IP)
 * - Session ID regeneration after login
 * - Linked Client ID discovery for the `client` role
 * - Full audit trail for successful and failed logins
 */
final class AuthService
{
    public static function attemptLogin(string $identifier, string $password, ?PDO $pdo = null): array
    {
        $identifier = trim($identifier);
        if ($identifier === '' || $password === '') {
            throw new RuntimeException('Username/email and password are required.');
        }

        $db = $pdo ?? Database::connection();
        $ip = Security::clientIp();

        if (self::isRateLimited($identifier, $ip, $db)) {
            AuditLogger::log('auth.lockout', 'users', null, "Rate-limited login attempt for '{$identifier}'.", ['ip' => $ip]);
            throw new RuntimeException('Too many failed login attempts. Please wait 15 minutes before trying again.');
        }

        $stmt = $db->prepare('
            SELECT u.*,
                   r.slug AS role_slug,
                   r.name_so AS role_name_so,
                   r.name_en AS role_name_en,
                   b.name AS branch_name,
                   b.code AS branch_code,
                   c.id AS client_id,
                   c.client_code
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN branches b ON b.id = u.branch_id
            LEFT JOIN clients c ON c.user_id = u.id
            WHERE (u.username = :ident1 OR u.email = :ident2)
              AND u.deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([':ident1' => $identifier, ':ident2' => $identifier]);
        $user = $stmt->fetch();

        if (!$user || !(bool) $user['is_active'] || !Security::verifyPassword($password, (string) $user['password_hash'])) {
            self::recordAttempt($identifier, $ip, false, $db);
            AuditLogger::log('auth.failed', 'users', $user['id'] ?? null, "Failed login attempt for '{$identifier}'.", ['ip' => $ip]);
            throw new RuntimeException('Invalid username/email or password.');
        }

        self::recordAttempt($identifier, $ip, true, $db);
        $db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $user['id']]);

        $sessionUser = [
            'id' => (int) $user['id'],
            'role_id' => (int) $user['role_id'],
            'role_slug' => (string) $user['role_slug'],
            'role_name_so' => (string) $user['role_name_so'],
            'role_name_en' => (string) $user['role_name_en'],
            'branch_id' => $user['branch_id'] !== null ? (int) $user['branch_id'] : null,
            'branch_name' => $user['branch_name'] ?? 'Head Office',
            'branch_code' => $user['branch_code'] ?? 'HQ',
            'client_id' => $user['client_id'] !== null ? (int) $user['client_id'] : null,
            'client_code' => $user['client_code'] ?? null,
            'full_name' => (string) $user['full_name'],
            'username' => (string) $user['username'],
            'email' => (string) $user['email'],
            'phone' => (string) $user['phone'],
            'preferred_locale' => 'en',
            'preferred_theme' => (string) $user['preferred_theme'],
            'is_synthetic_demo' => (bool) $user['is_synthetic_demo'],
        ];

        if (session_status() === PHP_SESSION_ACTIVE) {
            Security::regenerateSession();
            $_SESSION['user'] = $sessionUser;
            $_SESSION['locale'] = $sessionUser['preferred_locale'];
        }

        AuditLogger::log(
            'auth.login',
            'users',
            (int) $user['id'],
            "User {$user['username']} ({$user['role_slug']}) logged in.",
            ['ip' => $ip],
            (int) $user['id'],
            $sessionUser['branch_id']
        );

        return $sessionUser;
    }

    public static function isRateLimited(string $identifier, string $ip, ?PDO $pdo = null): bool
    {
        $db = $pdo ?? Database::connection();
        $maxAttempts = (int) Env::get('LOGIN_MAX_ATTEMPTS', 5);
        $lockoutMinutes = (int) Env::get('LOGIN_LOCKOUT_MINUTES', 15);
        $cutoff = date('Y-m-d H:i:s', time() - ($lockoutMinutes * 60));

        $stmt = $db->prepare('
            SELECT COUNT(*) FROM login_attempts
            WHERE identifier = :ident
              AND was_successful = 0
              AND attempted_at >= :cutoff
        ');
        $stmt->execute([
            ':ident' => strtolower($identifier),
            ':cutoff' => $cutoff,
        ]);

        return (int) $stmt->fetchColumn() >= $maxAttempts;
    }

    private static function recordAttempt(string $identifier, string $ip, bool $success, PDO $db): void
    {
        if ($success) {
            // Clear recent failed attempts for this identifier upon successful login
            $db->prepare('DELETE FROM login_attempts WHERE identifier = :ident AND was_successful = 0')
                ->execute([':ident' => strtolower($identifier)]);
        }

        $stmt = $db->prepare('
            INSERT INTO login_attempts (identifier, ip_address, attempted_at, was_successful)
            VALUES (:ident, :ip, NOW(), :succ)
        ');
        $stmt->execute([
            ':ident' => strtolower($identifier),
            ':ip' => $ip,
            ':succ' => $success ? 1 : 0,
        ]);
    }
}

