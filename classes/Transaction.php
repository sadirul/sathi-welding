<?php

declare(strict_types=1);

/**
 * Payment/transaction records for a client. type = 'due' represents billed
 * work/amount owed, type = 'paid' represents an amount received.
 */
class Transaction
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(int $clientId, float $amount, string $type, ?string $notes): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO transactions (client_id, amount, type, notes, created_at, updated_at)
             VALUES (:client_id, :amount, :type, :notes, NOW(), NOW())'
        );

        $stmt->execute([
            'client_id' => $clientId,
            'amount' => $amount,
            'type' => $type,
            'notes' => ($notes !== null && $notes !== '') ? $notes : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getByClient(int $clientId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, client_id, amount, type, notes, created_at
             FROM transactions
             WHERE client_id = :client_id
             ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute(['client_id' => $clientId]);

        return array_map(static function (array $row): array {
            $row['amount'] = (float) $row['amount'];
            return $row;
        }, $stmt->fetchAll());
    }
}
