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

    public function create(int $clientId, float $amount, string $type, ?string $notes, ?int $userId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO transactions (client_id, user_id, amount, type, notes, created_at, updated_at)
             VALUES (:client_id, :user_id, :amount, :type, :notes, NOW(), NOW())'
        );

        $stmt->execute([
            'client_id' => $clientId,
            'user_id' => $userId,
            'amount' => $amount,
            'type' => $type,
            'notes' => ($notes !== null && $notes !== '') ? $notes : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getByClient(int $clientId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.id, t.client_id, t.amount, t.type, t.notes, t.created_at, u.name AS received_by
             FROM transactions t
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.client_id = :client_id
             ORDER BY t.created_at DESC, t.id DESC'
        );
        $stmt->execute(['client_id' => $clientId]);

        return array_map(static function (array $row): array {
            $row['amount'] = (float) $row['amount'];
            return $row;
        }, $stmt->fetchAll());
    }
}
