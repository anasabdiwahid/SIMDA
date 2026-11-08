<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Lang;
use App\Helpers\Security;

final class AuthMiddleware
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        if (empty($_SESSION['user']['id'])) {
            return false;
        }

        return self::validateActiveSession();
    }

    /**
     * Enforce single active session per user account (kicks out prior session if logged in elsewhere)
     */
    public static function validateActiveSession(): bool
    {
        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        $sessionToken = (string) ($_SESSION['user']['session_token'] ?? '');
        if ($sessionToken === '') {
            return true;
        }

        // Cache per HTTP request
        $cacheKey = $userId . ':' . $sessionToken;
        static $validatedCache = [];
        if (isset($validatedCache[$cacheKey])) {
            return $validatedCache[$cacheKey];
        }

        try {
            $pdo = \App\Helpers\Database::connection();
            $st = $pdo->prepare('SELECT current_session_token, is_active FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
            $st->execute([':id' => $userId]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);

            if (!$row || (int) $row['is_active'] !== 1) {
                self::forceKickUser('account_disabled');
                $validatedCache[$cacheKey] = false;
                return false;
            }

            $dbToken = (string) ($row['current_session_token'] ?? '');

            // If token in DB does not match this session, another login occurred on another device/browser
            if ($dbToken !== '' && !hash_equals($dbToken, $sessionToken)) {
                self::forceKickUser('concurrent_login');
                $validatedCache[$cacheKey] = false;
                return false;
            }

            $validatedCache[$cacheKey] = true;
            return true;
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Invalidate session and set security kick notification
     */
    public static function forceKickUser(string $reason = 'concurrent_login'): void
    {
        $locale = Lang::locale();
        $isSo = $locale === 'so';
        $isAr = $locale === 'ar';

        if ($reason === 'concurrent_login') {
            $msg = $isSo
                ? 'Akoonkaaga waxaa laga soo galay aalad kale ama browser kale. Amnigaaga dartii, kalfadhigan hore si toos ah ayaa looga saaray (Logged out).'
                : ($isAr
                    ? 'تم تسجيل الدخول إلى حسابك من جهاز أو متصفح آخر. لدواعي الأمان، تم إنهاء هذه الجلسة.'
                    : 'Your account was signed in from another device or browser. For your security, this previous session was ended.');
        } else {
            $msg = $isSo
                ? 'Akoonkaaga waa la damiyey ama lama heli karo. Fadlan la xiriir maamulka.'
                : ($isAr
                    ? 'تم تعطيل حسابك أو تعليقه. يرجى مراجعة الإدارة.'
                    : 'Your account is deactivated or unavailable. Please contact administration.');
        }

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        if ($userId > 0) {
            \App\Helpers\AuditLogger::log(
                'auth.concurrent_kick',
                'users',
                $userId,
                "Concurrent session terminated for user ID {$userId} (signed in from another device/browser)."
            );
        }

        $_SESSION = ['flash_error' => $msg];
    }

    public static function role(): string
    {
        return (string) ($_SESSION['user']['role_slug'] ?? '');
    }

    public static function branchId(): ?int
    {
        $bid = $_SESSION['user']['branch_id'] ?? null;
        return $bid !== null ? (int) $bid : null;
    }

    public static function clientId(): ?int
    {
        $cid = $_SESSION['user']['client_id'] ?? null;
        return $cid !== null ? (int) $cid : null;
    }

    public static function requireAuth(): array
    {
        if (!self::check()) {
            if (empty($_SESSION['flash_error'])) {
                $_SESSION['flash_error'] = Lang::get('please_login_first');
            }
            header('Location: ?route=login');
            exit;
        }
        return $_SESSION['user'];
    }

    public static function requireRoles(array $allowedRoles): array
    {
        $user = self::requireAuth();
        if (($user['role_slug'] ?? '') === 'admin') {
            return $user;
        }
        if (!in_array($user['role_slug'], $allowedRoles, true)) {
            http_response_code(403);
            $_SESSION['flash_error'] = Lang::get('unauthorized_access');
            header('Location: ?route=dashboard');
            exit;
        }
        return $user;
    }

    /**
     * Check if user has specific granular permission
     * Admin always has full access (returns true)
     */
    public static function hasPermission(string $permissionSlug, ?array $user = null): bool
    {
        $u = $user ?? self::user();
        if (!$u) {
            return false;
        }
        $role = (string) ($u['role_slug'] ?? '');
        if ($role === 'admin') {
            return true;
        }

        $userId = (int) ($u['id'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        static $cache = [];
        $cacheKey = $userId . ':' . $permissionSlug;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        try {
            $pdo = \App\Helpers\Database::connection();

            // 1. User-specific custom override
            $st = $pdo->prepare('SELECT is_granted FROM user_permissions WHERE user_id = :uid AND permission_slug = :slug LIMIT 1');
            $st->execute([':uid' => $userId, ':slug' => $permissionSlug]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if ($row !== false) {
                $allowed = (bool) $row['is_granted'];
                $cache[$cacheKey] = $allowed;
                return $allowed;
            }

            // 2. Role-level permissions table
            $roleId = (int) ($u['role_id'] ?? 0);
            $rst = $pdo->prepare('
                SELECT 1 FROM role_permissions rp
                JOIN permissions p ON p.id = rp.permission_id
                WHERE rp.role_id = :rid AND p.slug = :slug
                LIMIT 1
            ');
            $rst->execute([':rid' => $roleId, ':slug' => $permissionSlug]);
            if ($rst->fetchColumn()) {
                $cache[$cacheKey] = true;
                return true;
            }
        } catch (\Throwable $e) {
            // fallback gracefully if table query fails
        }

        // 3. Fallback to default role matrix
        $allowed = self::defaultRolePermission($role, $permissionSlug);
        $cache[$cacheKey] = $allowed;
        return $allowed;
    }

    /**
     * Enforce granular permission or abort with redirect
     */
    public static function requirePermission(string $permissionSlug): array
    {
        $user = self::requireAuth();
        if (!self::hasPermission($permissionSlug, $user)) {
            http_response_code(403);
            $locale = Lang::locale();
            $_SESSION['flash_error'] = $locale === 'so'
                ? "Ma laguu ogola awooddan ({$permissionSlug}). Maamulaha Guud (Admin) ayaa go'aamiya waxyaabaha aad geli karto."
                : "You are not granted permission for ({$permissionSlug}). Contact the System Administrator for access.";
            $referer = $_SERVER['HTTP_REFERER'] ?? '?route=dashboard';
            header('Location: ' . $referer);
            exit;
        }
        return $user;
    }

    /**
     * Default role baseline permissions when not explicitly overridden
     */
    public static function defaultRolePermission(string $role, string $slug): bool
    {
        if ($role === 'admin') {
            return true;
        }

        $managerPerms = [
            'branches.manage', 'users.manage', 'clients.view', 'clients.register',
            'clients.transfer', 'guarantors.manage', 'loans.view', 'murabaha.view',
            'murabaha.create', 'products.manage', 'applications.view', 'applications.create',
            'applications.approve_small', 'applications.approve_large', 'financings.disburse',
            'repayments.view', 'repayments.record', 'charity.view', 'charity.waive',
            'risk.view', 'reports.view', 'statements.generate', 'communication.sms', 'audit.view'
        ];

        $branchManagerPerms = [
            'branches.manage', 'users.manage', 'clients.view', 'clients.register',
            'guarantors.manage', 'loans.view', 'murabaha.view', 'murabaha.create',
            // No products.manage: Branch Managers cannot add, edit or remove Murabaha / financing products.
            'applications.view', 'applications.create',
            'applications.approve_small', 'applications.approve_large', 'financings.disburse',
            'repayments.view', 'repayments.record', 'charity.view', 'charity.waive',
            'risk.view', 'reports.view', 'statements.generate', 'communication.sms'
        ];

        $loanOfficerPerms = [
            'clients.view', 'clients.register', 'guarantors.manage', 'loans.view',
            'murabaha.view', 'murabaha.create', 'applications.view', 'applications.create',
            // No approving and no disbursing: an employee's loan or Murabaha is a request for the Branch Manager.
            'repayments.view', 'repayments.record', 'statements.generate'
        ];

        return match ($role) {
            'manager' => in_array($slug, $managerPerms, true),
            'branch_manager' => in_array($slug, $branchManagerPerms, true),
            'loan_officer' => in_array($slug, $loanOfficerPerms, true),
            default => false,
        };
    }

    public static function canAccessBranch(?int $recordBranchId, ?array $user = null): bool
    {
        $u = $user ?? self::user();
        if (!$u) {
            return false;
        }
        if (in_array($u['role_slug'], ['admin', 'manager'], true)) {
            return true;
        }
        if ($recordBranchId === null) {
            return false;
        }
        return (int) ($u['branch_id'] ?? 0) === (int) $recordBranchId;
    }

    public static function canAccessClientRecord(array $clientRecord, ?array $user = null): bool
    {
        $u = $user ?? self::user();
        if (!$u) {
            return false;
        }
        // Clients are shared across branches: any staff member can open any client's record,
        // so a client registered at one branch can be served at another. A client sees only themself.
        if (in_array($u['role_slug'], ['admin', 'manager', 'branch_manager', 'loan_officer'], true)) {
            return true;
        }
        if ($u['role_slug'] === 'client') {
            return (int) ($u['client_id'] ?? 0) === (int) ($clientRecord['id'] ?? -1);
        }
        return false;
    }

    public static function verifyCsrfOrAbort(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Security::verifyCsrfToken(is_string($token) ? $token : null)) {
            http_response_code(419);
            $_SESSION['flash_error'] = Lang::get('csrf_invalid');
            $referer = $_SERVER['HTTP_REFERER'] ?? '?route=dashboard';
            header('Location: ' . $referer);
            exit;
        }
    }
}

