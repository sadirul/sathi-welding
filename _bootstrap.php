<?php

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

spl_autoload_register(static function (string $class): void {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (is_file($file)) {
        require $file;
    }
});

session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
