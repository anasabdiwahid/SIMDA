<?php
declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $pdo = null;
    private static bool $useTestDb = false;

    public static function useTestDatabase(bool $useTest = true): void
    {
        self::$useTestDb = $useTest;
        self::$pdo = null;
    }

    public static function setConnection(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function connection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $dbName = self::$useTestDb ? $config['test_database'] : $config['database'];
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $dbName,
            $config['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return self::$pdo;
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public static function ensureDatabaseExists(bool $testDb = false): void
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $dbName = $testDb ? $config['test_database'] : $config['database'];
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $config['host'], $config['port'], $config['charset']);
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
}

