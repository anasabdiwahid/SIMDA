<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use PDO;
use RuntimeException;

/**
 * Partners shown on the public home page: a logo, a name and a link. Managed by the admin.
 */
final class PartnerService
{
    public const IMAGE_DIR = '/public/assets/uploads/partners/';
    public const IMAGE_URL = 'assets/uploads/partners/';
    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024;

    public static function ensureTable(PDO $db): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $db->exec("
            CREATE TABLE IF NOT EXISTS partners (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                link_url VARCHAR(255) NULL,
                logo_path VARCHAR(255) NULL,
                sort_order INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_by INT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_partners_active (is_active, sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $done = true;
    }

    /** @return list<array<string, mixed>> */
    public static function all(PDO $db, bool $activeOnly = false): array
    {
        self::ensureTable($db);
        $sql = 'SELECT * FROM partners' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY sort_order ASC, id ASC';
        return $db->query($sql)->fetchAll();
    }

    public static function find(PDO $db, int $id): ?array
    {
        self::ensureTable($db);
        $st = $db->prepare('SELECT * FROM partners WHERE id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** Creates (id 0) or updates a partner. $file is the $_FILES entry for the logo, or null. */
    public static function save(PDO $db, int $id, array $data, ?array $file, int $userId, bool $isSo): int
    {
        self::ensureTable($db);
        $name = trim((string) ($data['name'] ?? ''));
        $link = trim((string) ($data['link_url'] ?? ''));
        $order = (int) ($data['sort_order'] ?? 0);

        if ($name === '' || mb_strlen($name) > 120) {
            throw new RuntimeException($isSo ? 'Fadlan geli magaca la-hawlgalaha (ugu badnaan 120 xaraf).' : 'Please enter the partner name (max 120 characters).');
        }
        if ($link !== '') {
            if (!preg_match('~^https?://~i', $link)) {
                $link = 'https://' . $link;
            }
            // Only real web addresses: no javascript:, data: or other schemes.
            $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));
            if (!filter_var($link, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true) || mb_strlen($link) > 255) {
                throw new RuntimeException($isSo ? 'Link-ga ma saxna. Tusaale: https://example.com' : 'The link is not valid. Example: https://example.com');
            }
        }

        $existing = $id > 0 ? self::find($db, $id) : null;
        if ($id > 0 && !$existing) {
            throw new RuntimeException($isSo ? 'La-hawlgalaha lama helin.' : 'Partner not found.');
        }

        $logo = $existing['logo_path'] ?? null;
        $newLogo = self::storeImage($file, $isSo);
        if ($newLogo !== null) {
            self::deleteImage($logo);
            $logo = $newLogo;
        } elseif (!empty($data['remove_logo'])) {
            self::deleteImage($logo);
            $logo = null;
        }
        if (!$logo) {
            throw new RuntimeException($isSo ? 'Fadlan soo geli sawirka (logo) la-hawlgalaha.' : 'Please upload the partner logo.');
        }

        $params = [':name' => $name, ':link' => $link !== '' ? $link : null, ':logo' => $logo, ':ord' => $order];
        if ($existing) {
            $params[':id'] = $id;
            $db->prepare('UPDATE partners SET name = :name, link_url = :link, logo_path = :logo, sort_order = :ord WHERE id = :id')->execute($params);
            AuditLogger::log('partner.updated', 'partners', $id, "Updated partner {$name}.", [], $userId);
            return $id;
        }
        $params[':by'] = $userId;
        $db->prepare('INSERT INTO partners (name, link_url, logo_path, sort_order, created_by) VALUES (:name, :link, :logo, :ord, :by)')->execute($params);
        $newId = (int) $db->lastInsertId();
        AuditLogger::log('partner.created', 'partners', $newId, "Added partner {$name}.", [], $userId);
        return $newId;
    }

    public static function toggle(PDO $db, int $id, int $userId): void
    {
        self::ensureTable($db);
        $db->prepare('UPDATE partners SET is_active = 1 - is_active WHERE id = :id')->execute([':id' => $id]);
        AuditLogger::log('partner.toggled', 'partners', $id, "Toggled partner #{$id} visibility.", [], $userId);
    }

    public static function delete(PDO $db, int $id, int $userId): void
    {
        $p = self::find($db, $id);
        if (!$p) {
            return;
        }
        $db->prepare('DELETE FROM partners WHERE id = :id')->execute([':id' => $id]);
        self::deleteImage($p['logo_path'] ?? null);
        AuditLogger::log('partner.deleted', 'partners', $id, "Deleted partner {$p['name']}.", [], $userId);
    }

    private static function storeImage(?array $file, bool $isSo): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? 0) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            throw new RuntimeException($isSo ? 'Sawirka lama soo gelin karin. Isku day mar kale.' : 'The logo could not be uploaded. Please try again.');
        }
        if ((int) $file['size'] > self::MAX_IMAGE_BYTES) {
            throw new RuntimeException($isSo ? 'Sawirku waa inuu ka yaraadaa 2MB.' : 'The logo must be smaller than 2MB.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            throw new RuntimeException($isSo ? 'Sawirka waa inuu noqdaa JPG, PNG ama WebP.' : 'The logo must be JPG, PNG or WebP.');
        }
        $dir = dirname(__DIR__, 2) . self::IMAGE_DIR;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Unable to create the partners image folder.');
        }
        $fileName = 'partner_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        if (!move_uploaded_file((string) $file['tmp_name'], $dir . $fileName)) {
            throw new RuntimeException($isSo ? 'Sawirka lama kaydin karin.' : 'The logo could not be saved.');
        }
        return self::IMAGE_URL . $fileName;
    }

    private static function deleteImage(?string $path): void
    {
        if (!$path || !str_starts_with($path, self::IMAGE_URL)) {
            return;
        }
        $full = dirname(__DIR__, 2) . self::IMAGE_DIR . basename($path);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
