<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

api_require_method('POST');
Auth::requireAuth();
api_require_csrf();

$auth = new Auth($db);
$auth->logout();

Response::success('Logged out successfully');
