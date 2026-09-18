<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

api_require_method('POST');
Auth::requireAuth();
api_require_csrf();

$body = api_json_body();

$id = isset($body['id']) ? (int) $body['id'] : 0;
$name = isset($body['name']) ? trim((string) $body['name']) : '';
$mobile = isset($body['mobile']) ? trim((string) $body['mobile']) : '';
$address = isset($body['address']) ? trim((string) $body['address']) : '';

$validator = new Validator();
$validator->required('name', $name, 'Name')
    ->maxLength('name', $name, 150, 'Name')
    ->exactDigits('mobile', $mobile, 10, 'Mobile number')
    ->maxLength('address', $address, 500, 'Address');

if ($validator->fails()) {
    Response::error('Please fix the errors below', $validator->errors(), 400);
}

$clientModel = new Client($db);

if ($id <= 0 || !$clientModel->exists($id)) {
    Response::error('Client not found', [], 404);
}

$clientModel->update($id, $name, $mobile !== '' ? $mobile : null, $address !== '' ? $address : null);
$client = $clientModel->findById($id);

Response::success('Client updated successfully', ['client' => $client]);
