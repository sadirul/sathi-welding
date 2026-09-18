<?php

declare(strict_types=1);

/**
 * Aggregate financial statistics across all clients for the dashboard.
 */
class Dashboard
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getStats(): array
    {
        $stmt = $this->db->query(
            'SELECT
                COALESCE(SUM(CASE WHEN type = "due" THEN amount ELSE 0 END), 0) AS total,
                COALESCE(SUM(CASE WHEN type = "paid" THEN amount ELSE 0 END), 0) AS paid
             FROM transactions'
        );
        $row = $stmt->fetch();

        $total = (float) $row['total'];
        $paid = (float) $row['paid'];
        $due = max(0, $total - $paid);

        $clientCount = (int) $this->db->query('SELECT COUNT(*) FROM clients')->fetchColumn();

        return [
            'total' => $total,
            'due' => $due,
            'paid' => $paid,
            'client_count' => $clientCount,
        ];
    }
}
