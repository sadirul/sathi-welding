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

/**
 * Appends the file's last-modified time as a cache-busting query string,
 * so browsers/CDNs pick up new CSS/JS immediately after a deploy instead of
 * serving a stale cached copy under the same filename.
 */
function asset_url(string $path): string
{
    $file = __DIR__ . '/' . $path;
    $version = is_file($file) ? filemtime($file) : time();

    return $path . '?v=' . $version;
}
