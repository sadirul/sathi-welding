<?php
/**
 * Database credentials.
 * Values are read from environment variables first so credentials never need
 * to be hardcoded/committed. Fallbacks match a typical local XAMPP setup
 * using the "sathiwelding" database requested for this project.
 */

declare(strict_types=1);

return [
    'host'    => getenv('DB_HOST') ?: '127.0.0.1',
    'port'    => getenv('DB_PORT') ?: '3306',
    'name'    => getenv('DB_NAME') ?: 'sathiwelding',
    'user'    => getenv('DB_USER') ?: 'root',
    'pass'    => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];
