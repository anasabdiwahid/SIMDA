<?php
declare(strict_types=1);

namespace App\Helpers;

final class Env
{
    private static bool $loaded = false;
    private static array $vars = [];

    public static function load(string $filePath): void
    {
        if (self::$loaded || !is_file($filePath)) {
            return;
        }
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            $parts = explode('=', $trimmed, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            self::$vars[$key] = $value;
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        if (array_key_exists($key, self::$vars)) {
            return self::$vars[$key];
        }
        $val = getenv($key);
        return $val !== false ? $val : $default;
    }
}

