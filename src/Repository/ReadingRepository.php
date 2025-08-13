<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ReadingRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function createTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS readings (
              id SERIAL PRIMARY KEY,
              device_id INT NOT NULL,
              value NUMERIC NOT NULL,
              created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
            )");
    }

    public function seed(): void
    {
        $this->pdo->exec("
            INSERT INTO readings(device_id, value) VALUES
              (1, 10.1), (1, 10.7), (2, 20.3), (2, 20.9)");
        sleep(1); // create ordering gap
        $this->pdo->exec("INSERT INTO readings(device_id, value) VALUES (1, 11.2), (2, 21.5)");
    }

    /** Postgres-specific: DISTINCT ON */
    public function latestPerDevice(): array
    {
        $sql = "
          SELECT DISTINCT ON (device_id)
                 device_id, value, created_at
          FROM readings
          ORDER BY device_id, created_at DESC";
        return $this->pdo->query($sql)->fetchAll();
    }
}
