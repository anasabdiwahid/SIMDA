<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use PDO;
use RuntimeException;

/**
 * Murabaha goods catalog: the items SIMDA buys and resells to clients at cost plus a fixed markup.
 * Staff with products.manage add each item's name, image, price and description; the New Murabaha form lists them.
 */
final class MurabahaGoodsService
{
    public const IMAGE_DIR = '/public/assets/uploads/goods/';
    public const IMAGE_URL = 'assets/uploads/goods/';
    private const MAX_IMAGE_BYTES = 3 * 1024 * 1024;

    public static function ensureTable(PDO $db): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $isNew = $db->query("SHOW TABLES LIKE 'murabaha_goods'")->fetchColumn() === false;
        $db->exec("
            CREATE TABLE IF NOT EXISTS murabaha_goods (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(160) NOT NULL,
                category VARCHAR(100) NULL,
                description TEXT NULL,
                price DECIMAL(15,2) NOT NULL COMMENT 'SIMDA purchase (cost) price per unit',
                markup_pct DECIMAL(6,2) NOT NULL DEFAULT 6.00,
                image_path VARCHAR(255) NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_by INT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_goods_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        if ($isNew) {
            self::seedDefaults($db);
        }
        $done = true;
    }

    /** The goods the New Murabaha form used to hard-code, so the catalog starts with them (no images). */
    private static function seedDefaults(PDO $db): void
    {
        $items = [
            ['Mooto Bajaaj (Tuk-Tuk)', 'Gaadiidka Fudud', 'Tuk-Tuk 3-taayirle cusub, mashiin 200cc, shidaal yar.', 2500, 8],
            ['Mashiinka Harqaanka (Industrial Sewing Machine)', 'Qalabka Farsamada', 'Mashiinka dharka tola ee casriga ah, miis iyo matoor wata.', 600, 6],
            ['Qalabka Cadceedda (Solar Energy System)', 'Tamarta Cadceedda', '4 Panels (450W), Inverter Hybrid 3KW, iyo Batariga Lithium 200Ah.', 1200, 7],
            ['Badeecad Dukaan (Wholesale Grocery Stock)', 'Badeecadaha Dukaamada', 'Raashin jumlad ah iyo agabka dukaanka (bariis, sonkor, saliid, baasto).', 1500, 5],
            ['Laptop & Qalabka Xafiiska', 'Teknolojiyadda', 'Laptop Core i7 / 16GB RAM / 512GB SSD, daabace iyo UPS.', 800, 6],
            ['Qaboojiyaha Ganacsiga (Deep Freezer)', 'Qaboojiyeyaasha', 'Qaboojiye ganacsi 500 Litir, Inverter Energy Saver.', 950, 6],
            ['Qalabka Dhismaha & Farsamada (Hardware Tools)', 'Dhismaha & Farsamada', 'Mashiinnada goynta biraha, alxanka, iyo qalabka dhismaha.', 2000, 7],
            ['Matoorka Biyaha Beeraha (Water Pump)', 'Beeraha & Waraabka', 'Matoor naafto ah 3-inch oo wata tubbooyin 50 mitir ah.', 1100, 6],
        ];
        $ins = $db->prepare('INSERT INTO murabaha_goods (name, category, description, price, markup_pct) VALUES (?, ?, ?, ?, ?)');
        foreach ($items as $item) {
            $ins->execute($item);
        }
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(PDO $db, bool $activeOnly = false): array
    {
        self::ensureTable($db);
        $sql = 'SELECT * FROM murabaha_goods' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY is_active DESC, name ASC';
        return $db->query($sql)->fetchAll();
    }

    public static function find(PDO $db, int $id): ?array
    {
        self::ensureTable($db);
        $st = $db->prepare('SELECT * FROM murabaha_goods WHERE id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function sellingPrice(array $item): float
    {
        return round((float) $item['price'] * (1 + (float) $item['markup_pct'] / 100), 2);
    }

    /**
     * Creates (id 0) or updates an item. $file is the $_FILES entry for the image, or null.
     */
    public static function save(PDO $db, int $id, array $data, ?array $file, int $userId, bool $isSo): int
    {
        self::ensureTable($db);
        $name = trim((string) ($data['name'] ?? ''));
        $category = trim((string) ($data['category'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $price = (float) ($data['price'] ?? 0);
        $markup = (float) ($data['markup_pct'] ?? 6);

        if ($name === '' || mb_strlen($name) > 160) {
            throw new RuntimeException($isSo ? 'Fadlan geli magaca sheyga (ugu badnaan 160 xaraf).' : 'Please enter the product name (max 160 characters).');
        }
        if ($price <= 0 || $price > 10000000) {
            throw new RuntimeException($isSo ? 'Fadlan geli qiimo sax ah oo ka weyn $0.' : 'Please enter a valid price above $0.');
        }
        if ($markup < 0 || $markup > 100) {
            throw new RuntimeException($isSo ? 'Faa\'iidada (markup) waa inay u dhexeysaa 0% iyo 100%.' : 'Markup must be between 0% and 100%.');
        }

        $existing = $id > 0 ? self::find($db, $id) : null;
        if ($id > 0 && !$existing) {
            throw new RuntimeException($isSo ? 'Sheyga lama helin.' : 'Product not found.');
        }

        $imagePath = $existing['image_path'] ?? null;
        $newImage = self::storeImage($file, $isSo);
        if ($newImage !== null) {
            self::deleteImage($imagePath);
            $imagePath = $newImage;
        } elseif (!empty($data['remove_image'])) {
            self::deleteImage($imagePath);
            $imagePath = null;
        }

        $params = [
            ':name' => $name,
            ':cat' => $category !== '' ? $category : null,
            ':descr' => $description !== '' ? $description : null,
            ':price' => number_format($price, 2, '.', ''),
            ':markup' => number_format($markup, 2, '.', ''),
            ':img' => $imagePath,
        ];

        if ($existing) {
            $params[':id'] = $id;
            $db->prepare('UPDATE murabaha_goods SET name = :name, category = :cat, description = :descr, price = :price, markup_pct = :markup, image_path = :img WHERE id = :id')
                ->execute($params);
            AuditLogger::log('murabaha_goods.updated', 'murabaha_goods', $id, "Updated Murabaha product {$name} (\${$params[':price']}).", [], $userId);
            return $id;
        }

        $params[':by'] = $userId;
        $db->prepare('INSERT INTO murabaha_goods (name, category, description, price, markup_pct, image_path, created_by) VALUES (:name, :cat, :descr, :price, :markup, :img, :by)')
            ->execute($params);
        $newId = (int) $db->lastInsertId();
        AuditLogger::log('murabaha_goods.created', 'murabaha_goods', $newId, "Added Murabaha product {$name} (\${$params[':price']}).", [], $userId);
        return $newId;
    }

    public static function toggle(PDO $db, int $id, int $userId): void
    {
        self::ensureTable($db);
        $db->prepare('UPDATE murabaha_goods SET is_active = 1 - is_active WHERE id = :id')->execute([':id' => $id]);
        AuditLogger::log('murabaha_goods.toggled', 'murabaha_goods', $id, "Toggled Murabaha product #{$id} availability.", [], $userId);
    }

    public static function delete(PDO $db, int $id, int $userId): void
    {
        $item = self::find($db, $id);
        if (!$item) {
            return;
        }
        $db->prepare('DELETE FROM murabaha_goods WHERE id = :id')->execute([':id' => $id]);
        self::deleteImage($item['image_path'] ?? null);
        AuditLogger::log('murabaha_goods.deleted', 'murabaha_goods', $id, "Deleted Murabaha product {$item['name']}.", [], $userId);
    }

    private static function storeImage(?array $file, bool $isSo): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? 0) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            throw new RuntimeException($isSo ? 'Sawirka lama soo gelin karin. Isku day mar kale.' : 'The image could not be uploaded. Please try again.');
        }
        if ((int) $file['size'] > self::MAX_IMAGE_BYTES) {
            throw new RuntimeException($isSo ? 'Sawirku waa inuu ka yaraadaa 3MB.' : 'The image must be smaller than 3MB.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            throw new RuntimeException($isSo ? 'Sawirka waa inuu noqdaa JPG, PNG ama WebP.' : 'The image must be JPG, PNG or WebP.');
        }

        $dir = dirname(__DIR__, 2) . self::IMAGE_DIR;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Unable to create the goods image folder.');
        }
        $fileName = 'goods_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        if (!move_uploaded_file((string) $file['tmp_name'], $dir . $fileName)) {
            throw new RuntimeException($isSo ? 'Sawirka lama kaydin karin.' : 'The image could not be saved.');
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
