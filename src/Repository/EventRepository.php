<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class EventRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function createTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS events (
              id SERIAL PRIMARY KEY,
              data JSONB NOT NULL
            )");
    }

    public function insert(array $data): void
    {
        $st = $this->pdo->prepare("INSERT INTO events(data) VALUES (:d::jsonb)");
        $st->execute([':d' => json_encode($data, JSON_UNESCAPED_SLASHES)]);
    }

    /** Postgres-specific: JSONB containment @> */
    public function findWhereContainsType(string $type): array
    {
        $sql = "SELECT id, data FROM events WHERE data @> :q::jsonb";
        $st = $this->pdo->prepare($sql);
        $st->execute([':q' => json_encode(['type' => $type])]);
        return $st->fetchAll();
    }
}
