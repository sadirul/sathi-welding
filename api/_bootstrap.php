<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

spl_autoload_register(static function (string $class): void {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (is_file($file)) {
        require $file;
    }
});

session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Content-Type: application/json; charset=utf-8');

// Turn PHP errors/exceptions into clean JSON instead of leaking HTML/stack traces.
set_exception_handler(static function (Throwable $e): void {
    error_log('Unhandled exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    Response::error('Something went wrong. Please try again.', [], 500);
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

/**
 * Only GET and the request's own declared method are meaningful for these
 * single-purpose endpoints; reject anything else early.
 */
function api_require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        Response::error('Method not allowed', [], 405);
    }
}

/**
 * Validates the CSRF token header for state-changing requests.
 */
function api_require_csrf(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!Csrf::validate($token)) {
        Response::error('Invalid or expired request. Please refresh the page.', [], 400);
    }
}

/**
 * Reads and decodes a JSON request body into an associative array.
 */
function api_json_body(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);

    return is_array($data) ? $data : [];
}

/**
 * Formats a rupee amount using Indian digit grouping (e.g. ₹1,25,000),
 * mirroring assets/js/app.js's formatCurrency() for error messages.
 */
function format_inr(float $amount): string
{
    $isNegative = $amount < 0;
    $amount = abs($amount);
    $wholePart = (string) (int) $amount;
    $decimalPart = round($amount - (int) $amount, 2);

    if (strlen($wholePart) > 3) {
        $lastThree = substr($wholePart, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($wholePart, 0, -3));
        $wholePart = $rest . ',' . $lastThree;
    }

    $formatted = '₹' . $wholePart;
    if ($decimalPart > 0) {
        $formatted .= '.' . str_pad((string) round($decimalPart * 100), 2, '0', STR_PAD_LEFT);
    }

    return ($isNegative ? '-' : '') . $formatted;
}

$db = Database::getConnection();
