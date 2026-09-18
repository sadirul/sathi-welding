<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

api_require_method('POST');
Auth::requireAuth();
api_require_csrf();

$body = api_json_body();

$clientId = isset($body['client_id']) ? (int) $body['client_id'] : 0;
$amount = $body['amount'] ?? '';
$type = isset($body['type']) ? trim((string) $body['type']) : '';
$notes = isset($body['notes']) ? trim((string) $body['notes']) : '';

$validator = new Validator();
$validator->required('amount', $amount, 'Amount')
    ->numeric('amount', $amount, 'Amount')
    ->greaterThan('amount', $amount, 0, 'Amount')
    ->required('type', $type, 'Type')
    ->in('type', $type, ['due', 'paid'], 'Type')
    ->maxLength('notes', $notes, 500, 'Notes');

if ($validator->fails()) {
    Response::error('Please fix the errors below', $validator->errors(), 400);
}

$clientModel = new Client($db);
$existingClient = $clientId > 0 ? $clientModel->findById($clientId) : null;

if (!$existingClient) {
    Response::error('Client not found', [], 404);
}

if ($type === 'paid' && (float) $amount > $existingClient['due']) {
    Response::error('Please fix the errors below', [
        'amount' => 'Paid amount cannot exceed the due amount (' . format_inr($existingClient['due']) . ')',
    ], 400);
}

$transactionModel = new Transaction($db);
$transactionModel->create($clientId, (float) $amount, $type, $notes !== '' ? $notes : null, Auth::id());

$client = $clientModel->findById($clientId);
$transactions = $transactionModel->getByClient($clientId);

Response::success('Payment added successfully', [
    'client' => $client,
    'transactions' => $transactions,
], 201);
