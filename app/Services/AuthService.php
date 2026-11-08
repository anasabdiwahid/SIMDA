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
 * Progressive Security Ladder:
 * - Bcrypt / Argon2id password verification
 * - Tier 0: Normal -> 5 failed attempts -> 15 min lock -> Tier 1
 * - Tier 1: 15 min lock expired -> 3 failed attempts -> 30 min lock -> Tier 2
 * - Tier 2: 30 min lock expired -> 2 failed attempts -> 1 hour lock -> Tier 3
 * - Tier 3: 1 hr lock expired -> 2 failed attempts -> 5 hours lock -> Tier 4
 * - Tier 4: 5 hrs lock expired -> 1 failed attempt -> Permanent Block (Tier 5, is_active=0)
 * - Session ID regeneration after login
 * - Linked Client ID discovery for the `client` role
 * - Full audit trail for successful and failed logins
 */
final class AuthService
{
    public const TIER_CONFIG = [
        0 => ['max_attempts' => 5, 'lockout_seconds' => 15 * 60,  'next_tier' => 1, 'label_so' => '15 daqiiqo',   'label_en' => '15 minutes', 'label_ar' => '15 دقيقة'],
        1 => ['max_attempts' => 3, 'lockout_seconds' => 30 * 60,  'next_tier' => 2, 'label_so' => '30 daqiiqo',   'label_en' => '30 minutes', 'label_ar' => '30 دقيقة'],
        2 => ['max_attempts' => 2, 'lockout_seconds' => 60 * 60,  'next_tier' => 3, 'label_so' => '1 saac',       'label_en' => '1 hour',     'label_ar' => 'ساعة واحدة'],
        3 => ['max_attempts' => 2, 'lockout_seconds' => 5 * 3600, 'next_tier' => 4, 'label_so' => '5 saacadood', 'label_en' => '5 hours',    'label_ar' => '5 ساعات'],
        4 => ['max_attempts' => 1, 'lockout_seconds' => null,     'next_tier' => 5, 'label_so' => 'block',        'label_en' => 'block',      'label_ar' => 'حظر'],
    ];

    /**
     * Check if user account is permanently blocked or in temporary lockout.
     * Returns null if account is cleared to attempt login.
     */
    public static function checkLockoutStatus(array $user, string $locale = 'so'): ?array
    {
        // 1. Permanent security block check
        if ((int) ($user['lockout_tier'] ?? 0) >= 5 || ((int) ($user['is_active'] ?? 1) === 0 && !empty($user['security_block_reason']))) {
            $msg = match ($locale) {
                'en' => 'Please contact administration. Suspicious activity detected.',
                'ar' => 'يرجى مراجعة الإدارة، تم الاشتباه في هويتك.',
                default => 'Maamulka la xariir fadlan, waa kaa shakinay qofka aad tahay.',
            };
            return [
                'blocked' => true,
                'permanent' => true,
                'message' => $msg,
            ];
        }

        // 2. Inactive account for standard reasons
        if ((int) ($user['is_active'] ?? 1) === 0) {
            $msg = match ($locale) {
                'en' => 'This account is currently disabled. Please contact the administrator.',
                'ar' => 'هذا الحساب معطل حالياً. يرجى الاتصال بمسؤول النظام.',
                default => 'Akoonkani ma shaqeynayo. Fadlan maamulka la xariir.',
            };
            return [
                'blocked' => true,
                'permanent' => true,
                'message' => $msg,
            ];
        }

        // 3. Temporary Lockout check
        if (!empty($user['locked_until'])) {
            $lockedUntilTs = strtotime((string) $user['locked_until']);
            $remaining = $lockedUntilTs - time();
            if ($remaining > 0) {
                if ($remaining >= 3600) {
                    $hours = ceil($remaining / 3600);
                    $timeStr = match ($locale) {
                        'en' => "{$hours} " . ($hours > 1 ? 'hours' : 'hour'),
                        'ar' => "{$hours} ساعة",
                        default => "{$hours} saac",
                    };
                } elseif ($remaining >= 60) {
                    $mins = ceil($remaining / 60);
                    $timeStr = match ($locale) {
                        'en' => "{$mins} minutes",
                        'ar' => "{$mins} دقيقة",
                        default => "{$mins} daqiiqo",
                    };
                } else {
                    $timeStr = match ($locale) {
                        'en' => 'less than 1 minute',
                        'ar' => 'أقل من دقيقة',
                        default => 'wax ka yar hal daqiiqo',
                    };
                }

                $msg = match ($locale) {
                    'en' => "Account is temporarily locked for security reasons. Please wait {$timeStr}.",
                    'ar' => "تم تعليق الحساب مؤقتاً لأسباب أمنية. يرجى الانتظار {$timeStr}.",
                    default => "Akoonka waxaa si ku meel gaar ah loo hakiyay sababo amni dartood. Fadlan sug ilaa {$timeStr}.",
                };

                return [
                    'blocked' => true,
                    'permanent' => false,
                    'remaining_seconds' => $remaining,
                    'message' => $msg,
                ];
            }
        }

        return null;
    }

    /**
     * Handle failed password attempt according to progressive security tiers.
     */
    public static function handleFailedPassword(array $user, PDO $db, string $locale = 'so'): array
    {
        $ip = Security::clientIp();
        $userId = (int) $user['id'];
        $identifier = $user['email'] ?: $user['username'];
        $currentTier = (int) ($user['lockout_tier'] ?? 0);
        if ($currentTier > 4) {
            $currentTier = 4;
        }

        $config = self::TIER_CONFIG[$currentTier] ?? self::TIER_CONFIG[0];
        $maxAttempts = (int) $config['max_attempts'];
        $attempts = (int) ($user['failed_login_attempts'] ?? 0) + 1;

        if ($attempts < $maxAttempts) {
            // Failed attempt recorded, but lockout threshold not reached yet
            $db->prepare('UPDATE users SET failed_login_attempts = :att WHERE id = :id')
                ->execute([':att' => $attempts, ':id' => $userId]);

            self::recordAttempt($identifier, $ip, false, $db);
            AuditLogger::log('auth.failed', 'users', $userId, "Failed login attempt {$attempts}/{$maxAttempts} for '{$identifier}'.", ['ip' => $ip]);

            $msg = match ($locale) {
                'en' => 'Incorrect password. Please verify and try again.',
                'ar' => 'كلمة المرور غير صحيحة، يرجى التأكد والمحاولة مرة أخرى.',
                default => 'Fadlan password-ka waa kaa qaldan yahay, iska hubi.',
            };

            return [
                'locked' => false,
                'permanent' => false,
                'message' => $msg,
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts,
                'remaining_attempts' => $maxAttempts - $attempts,
            ];
        }

        // Threshold reached! Advance tier and lock or block
        $nextTier = (int) $config['next_tier'];
        if ($nextTier >= 5) {
            // Permanent Block (Tier 5)
            $db->prepare('
                UPDATE users
                SET is_active = 0,
                    lockout_tier = 5,
                    failed_login_attempts = 0,
                    locked_until = NULL,
                    security_block_reason = :reason
                WHERE id = :id
            ')->execute([
                ':reason' => 'Suspect activity: repeated password failures exceeded tier 4',
                ':id' => $userId,
            ]);

            self::recordAttempt($identifier, $ip, false, $db);
            AuditLogger::log('auth.permanent_block', 'users', $userId, "Account permanently blocked due to repeated password failures for '{$identifier}'.", ['ip' => $ip]);

            $msg = match ($locale) {
                'en' => 'Please contact administration. Suspicious activity detected.',
                'ar' => 'يرجى مراجعة الإدارة، تم الاشتباه في هويتك.',
                default => 'Maamulka la xariir fadlan, waa kaa shakinay qofka aad tahay.',
            };

            return [
                'locked' => true,
                'permanent' => true,
                'message' => $msg,
            ];
        }

        // Temporary lockout
        $lockSeconds = (int) $config['lockout_seconds'];
        $lockedUntil = date('Y-m-d H:i:s', time() + $lockSeconds);

        $db->prepare('
            UPDATE users
            SET lockout_tier = :tier,
                failed_login_attempts = 0,
                locked_until = :until
            WHERE id = :id
        ')->execute([
            ':tier' => $nextTier,
            ':until' => $lockedUntil,
            ':id' => $userId,
        ]);

        self::recordAttempt($identifier, $ip, false, $db);
        AuditLogger::log('auth.lockout_tier_' . $nextTier, 'users', $userId, "Account locked for {$config['label_en']} (tier {$nextTier}) for '{$identifier}'.", ['ip' => $ip]);

        $label = $config['label_' . ($locale === 'en' ? 'en' : ($locale === 'ar' ? 'ar' : 'so'))] ?? $config['label_so'];
        $msg = match ($locale) {
            'en' => "Incorrect password. The account has been locked for {$label} due to multiple failed attempts.",
            'ar' => "كلمة المرور غير صحيحة. تم تعليق الحساب لمدة {$label} بسبب تكرار المحاولات الخاطئة.",
            default => "Furaha sirta ah waa qalad. Akoonka waxaa la hakiyay mudo {$label} ah maadaama lagu celceliyay furaha sirta ah.",
        };

        return [
            'locked' => true,
            'permanent' => false,
            'lockout_seconds' => $lockSeconds,
            'message' => $msg,
        ];
    }

    /**
     * Reset security lockout and attempt counters upon successful login.
     */
    public static function resetSecurityCounters(int $userId, PDO $db): void
    {
        $db->prepare('
            UPDATE users
            SET failed_login_attempts = 0,
                lockout_tier = 0,
                locked_until = NULL
            WHERE id = :id
        ')->execute([':id' => $userId]);
    }

    /**
     * Admin unlock / unblock user
     */
    public static function adminUnblockUser(int $userId, PDO $db, int $adminId): void
    {
        $db->prepare('
            UPDATE users
            SET is_active = 1,
                failed_login_attempts = 0,
                lockout_tier = 0,
                locked_until = NULL,
                security_block_reason = NULL
            WHERE id = :id
        ')->execute([':id' => $userId]);

        AuditLogger::log('auth.admin_unblock', 'users', $userId, "Admin unlocked security restriction for user ID {$userId}.", [], $adminId);
    }

    public static function attemptLogin(string $identifier, string $password, ?PDO $pdo = null): array
    {
        $identifier = trim($identifier);
        if ($identifier === '' || $password === '') {
            throw new RuntimeException('Username/email and password are required.');
        }

        $db = $pdo ?? Database::connection();
        $ip = Security::clientIp();

        $user = ClientAccountService::findLoginUser($db, $identifier);

        if (!$user) {
            self::recordAttempt($identifier, $ip, false, $db);
            AuditLogger::log('auth.failed', 'users', null, "Failed login attempt for nonexistent user '{$identifier}'.", ['ip' => $ip]);
            throw new RuntimeException('Invalid username/email or password.');
        }

        // Check account security lockout status
        $lockout = self::checkLockoutStatus($user, 'so');
        if ($lockout !== null) {
            AuditLogger::log('auth.blocked_attempt', 'users', (int) $user['id'], "Login attempted on locked/blocked account '{$identifier}'.", ['ip' => $ip]);
            throw new RuntimeException($lockout['message']);
        }

        // Verify password
        if (!Security::verifyPassword($password, (string) $user['password_hash'])) {
            $failResult = self::handleFailedPassword($user, $db, 'so');
            throw new RuntimeException($failResult['message']);
        }

        // Successful password verification
        self::recordAttempt($identifier, $ip, true, $db);
        self::resetSecurityCounters((int) $user['id'], $db);

        // Generate a new unique session token (terminates any concurrent session elsewhere)
        $sessionToken = bin2hex(random_bytes(32));

        $db->prepare('UPDATE users SET last_login_at = NOW(), current_session_token = :token WHERE id = :id')
           ->execute([':token' => $sessionToken, ':id' => $user['id']]);

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
            'session_token' => $sessionToken,
            'must_change_password' => !empty($user['must_change_password']),
        ];

        if (session_status() === PHP_SESSION_ACTIVE) {
            Security::regenerateSession();
            $_SESSION['user'] = $sessionUser;
            // Keep the language picked on the login page; fall back to the account's preference.
            $_SESSION['locale'] = $_SESSION['locale'] ?? $sessionUser['preferred_locale'];
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

    public static function completeLoginForUser(array $user, ?PDO $pdo = null): array
    {
        $db = $pdo ?? Database::connection();
        $ip = Security::clientIp();

        // Generate a new unique session token (terminates any concurrent session elsewhere)
        $sessionToken = bin2hex(random_bytes(32));

        self::recordAttempt((string) ($user['email'] ?? $user['username']), $ip, true, $db);
        self::resetSecurityCounters((int) $user['id'], $db);
        $db->prepare('UPDATE users SET last_login_at = NOW(), current_session_token = :token WHERE id = :id')
           ->execute([':token' => $sessionToken, ':id' => $user['id']]);

        $sessionUser = [
            'id' => (int) $user['id'],
            'role_id' => (int) $user['role_id'],
            'role_slug' => (string) $user['role_slug'],
            'role_name_so' => (string) ($user['role_name_so'] ?? 'Maamule'),
            'role_name_en' => (string) ($user['role_name_en'] ?? 'Administrator'),
            'branch_id' => $user['branch_id'] !== null ? (int) $user['branch_id'] : null,
            'branch_name' => $user['branch_name'] ?? 'Head Office',
            'branch_code' => $user['branch_code'] ?? 'HQ',
            'client_id' => $user['client_id'] !== null ? (int) $user['client_id'] : null,
            'client_code' => $user['client_code'] ?? null,
            'full_name' => (string) $user['full_name'],
            'username' => (string) $user['username'],
            'email' => (string) $user['email'],
            'phone' => (string) $user['phone'],
            'preferred_locale' => (string) ($user['preferred_locale'] ?? 'en'),
            'preferred_theme' => (string) ($user['preferred_theme'] ?? 'light'),
            'is_synthetic_demo' => !empty($user['is_synthetic_demo']),
            'session_token' => $sessionToken,
            'must_change_password' => !empty($user['must_change_password']),
        ];

        if (session_status() === PHP_SESSION_ACTIVE) {
            Security::regenerateSession();
            $_SESSION['user'] = $sessionUser;
            // Keep the language picked on the login page; fall back to the account's preference.
            $_SESSION['locale'] = $_SESSION['locale'] ?? $sessionUser['preferred_locale'];
        }

        AuditLogger::log(
            'auth.login_2fa_success',
            'users',
            (int) $user['id'],
            "Admin {$user['username']} ({$user['role_slug']}) completed 8-digit 2FA verification and logged in.",
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
