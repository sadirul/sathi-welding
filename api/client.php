<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

api_require_method('GET');
Auth::requireAuth();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    Response::error('Invalid client', [], 400);
}

$clientModel = new Client($db);
$client = $clientModel->findById($id);

if (!$client) {
    Response::error('Client not found', [], 404);
}

$transactionModel = new Transaction($db);
$transactions = $transactionModel->getByClient($id);

Response::success('', [
    'client' => $client,
    'transactions' => $transactions,
]);
