<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\' => __DIR__ . '/app/',
        'Database\\' => __DIR__ . '/database/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }
});

\App\Helpers\Env::load(__DIR__ . '/.env');
date_default_timezone_set((string) \App\Helpers\Env::get('APP_TIMEZONE', 'Africa/Mogadishu'));

