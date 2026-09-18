<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

api_require_method('POST');
Auth::requireAuth();
api_require_csrf();

$body = api_json_body();
$id = isset($body['id']) ? (int) $body['id'] : 0;

$clientModel = new Client($db);

if ($id <= 0 || !$clientModel->exists($id)) {
    Response::error('Client not found', [], 404);
}

$clientModel->delete($id);

Response::success('Client deleted successfully');
