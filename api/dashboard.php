<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

api_require_method('GET');
Auth::requireAuth();

$dashboard = new Dashboard($db);

Response::success('', $dashboard->getStats());
