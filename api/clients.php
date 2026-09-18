<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

api_require_method('GET');
Auth::requireAuth();

$clientModel = new Client($db);

Response::success('', ['clients' => $clientModel->getAll()]);
