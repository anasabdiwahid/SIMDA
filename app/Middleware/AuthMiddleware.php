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
        return !empty($_SESSION['user']['id']);
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
            $_SESSION['flash_error'] = Lang::get('please_login_first');
            header('Location: ?route=login');
            exit;
        }
        return $_SESSION['user'];
    }

    public static function requireRoles(array $allowedRoles): array
    {
        $user = self::requireAuth();
        if (!in_array($user['role_slug'], $allowedRoles, true)) {
            http_response_code(403);
            $_SESSION['flash_error'] = Lang::get('unauthorized_access');
            header('Location: ?route=dashboard');
            exit;
        }
        return $user;
    }

    public static function canAccessBranch(?int $recordBranchId, ?array $user = null): bool
    {
        $u = $user ?? self::user();
        if (!$u) {
            return false;
        }
        if ($u['role_slug'] === 'admin') {
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
        if ($u['role_slug'] === 'admin') {
            return true;
        }
        if ($u['role_slug'] === 'client') {
            return (int) ($u['client_id'] ?? 0) === (int) ($clientRecord['id'] ?? -1);
        }
        return (int) ($u['branch_id'] ?? 0) === (int) ($clientRecord['branch_id'] ?? -2);
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

