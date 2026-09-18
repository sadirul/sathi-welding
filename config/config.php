<?php
/**
 * Application configuration.
 * Keep this file outside the public web root's direct reach (blocked via .htaccess).
 */

declare(strict_types=1);

// Load KEY=VALUE pairs from a .env file at the project root, if present,
// into getenv()/$_ENV — without overriding real OS environment variables.
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim(trim($value), "\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
unset($envFile, $line, $key, $value);

// Show errors only in development. Set APP_ENV=production on the live server.
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_NAME', 'Sathi Welding Karkhana');

if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Errors are always logged, never leaked to the browser response.
ini_set('log_errors', '1');

date_default_timezone_set('Asia/Kolkata');

// Session configuration - must run before session_start() anywhere in the app.
define('SESSION_NAME', 'sathi_welding_session');

$sessionSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $sessionSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
