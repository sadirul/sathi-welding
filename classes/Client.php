<?php

declare(strict_types=1);

/**
 * Client records. Financial totals (total/due/paid) are calculated on the fly
 * via SQL aggregation over the transactions table rather than stored/duplicated.
 */
class Client
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(string $name, ?string $mobile, ?string $address): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO clients (name, mobile, address, created_at, updated_at)
             VALUES (:name, :mobile, :address, NOW(), NOW())'
        );

        $stmt->execute([
            'name' => $name,
            'mobile' => $mobile !== '' ? $mobile : null,
            'address' => $address !== '' ? $address : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, ?string $mobile, ?string $address): void
    {
        $stmt = $this->db->prepare(
            'UPDATE clients
             SET name = :name, mobile = :mobile, address = :address, updated_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'mobile' => $mobile !== '' ? $mobile : null,
            'address' => $address !== '' ? $address : null,
        ]);
    }

    /**
     * Returns all clients with computed total/due/paid financial aggregates.
     */
    public function getAll(): array
    {
        $sql = 'SELECT
                    c.id, c.name, c.mobile, c.address, c.created_at,
                    COALESCE(SUM(CASE WHEN t.type = "due" THEN t.amount ELSE 0 END), 0) AS total,
                    COALESCE(SUM(CASE WHEN t.type = "paid" THEN t.amount ELSE 0 END), 0) AS paid
                FROM clients c
                LEFT JOIN transactions t ON t.client_id = c.id
                GROUP BY c.id, c.name, c.mobile, c.address, c.created_at
                ORDER BY c.name ASC';

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        return array_map([self::class, 'withDueBalance'], $rows);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                c.id, c.name, c.mobile, c.address, c.created_at,
                COALESCE(SUM(CASE WHEN t.type = "due" THEN t.amount ELSE 0 END), 0) AS total,
                COALESCE(SUM(CASE WHEN t.type = "paid" THEN t.amount ELSE 0 END), 0) AS paid
             FROM clients c
             LEFT JOIN transactions t ON t.client_id = c.id
             WHERE c.id = :id
             GROUP BY c.id, c.name, c.mobile, c.address, c.created_at'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? self::withDueBalance($row) : null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM clients WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Normalizes numeric aggregate fields and derives the outstanding due balance.
     */
    private static function withDueBalance(array $row): array
    {
        $total = (float) $row['total'];
        $paid = (float) $row['paid'];
        $due = max(0, $total - $paid);

        $row['total'] = $total;
        $row['paid'] = $paid;
        $row['due'] = $due;

        return $row;
    }
}
