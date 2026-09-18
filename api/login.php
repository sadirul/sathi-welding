<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

api_require_method('POST');

$body = api_json_body();
$pin = isset($body['pin']) ? trim((string) $body['pin']) : '';

$validator = new Validator();
$validator->required('pin', $pin, 'PIN')
    ->exactDigits('pin', $pin, 6, 'PIN');

if ($validator->fails()) {
    Response::error('Please enter a valid 6-digit PIN', $validator->errors(), 400);
}

$auth = new Auth($db);

if (!$auth->login($pin)) {
    Response::error('Invalid PIN', [], 401);
}

Response::success('Login successful', [
    'csrf_token' => Csrf::token(),
]);
