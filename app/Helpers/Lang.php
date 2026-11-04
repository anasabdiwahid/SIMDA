<?php
declare(strict_types=1);

namespace App\Helpers;

final class Lang
{
    private static string $locale = 'en';
    private static array $translations = [];

    public static function init(?string $requestedLocale = null): void
    {
        $supported = ['en', 'so', 'ar'];
        if ($requestedLocale !== null && in_array($requestedLocale, $supported, true)) {
            self::$locale = $requestedLocale;
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['locale'] = $requestedLocale;
            }
        } elseif (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['locale']) && in_array($_SESSION['locale'], $supported, true)) {
            self::$locale = $_SESSION['locale'];
        } else {
            self::$locale = (string) Env::get('DEFAULT_LOCALE', 'en');
        }

        self::loadLocale(self::$locale);
    }

    public static function setLocale(string $locale): void
    {
        if (in_array($locale, ['en', 'so', 'ar'], true)) {
            self::$locale = $locale;
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['locale'] = $locale;
            }
            self::loadLocale($locale);
        }
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    private static function loadLocale(string $locale): void
    {
        $path = dirname(__DIR__, 2) . '/resources/languages/' . $locale . '.php';
        if (is_file($path)) {
            self::$translations[$locale] = require $path;
        } else {
            self::$translations[$locale] = [];
        }
    }

    public static function get(string $key, array $replacements = []): string
    {
        if (!isset(self::$translations[self::$locale])) {
            self::loadLocale(self::$locale);
        }
        $text = self::$translations[self::$locale][$key] ?? $key;
        foreach ($replacements as $k => $v) {
            $text = str_replace(':' . $k, (string) $v, $text);
        }
        return $text;
    }

    public static function field(array $row, string $baseField): string
    {
        $localizedKey = $baseField . '_' . self::$locale;
        $fallbackKey = $baseField . '_en';
        return (string) ($row[$localizedKey] ?? $row[$fallbackKey] ?? $row[$baseField] ?? '');
    }
}
