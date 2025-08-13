<?php
declare(strict_types=1);

namespace App\Repository;

use App\Util\PgArray;
use PDO;

final class TagSetRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function createTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS tag_sets (
              id SERIAL PRIMARY KEY,
              labels TEXT[] NOT NULL
            )");
    }

    public function insertLabels(array $labels): void
    {
        $literal = PgArray::fromList($labels);
        $st = $this->pdo->prepare("INSERT INTO tag_sets(labels) VALUES (:labels::text[])");
        $st->execute([':labels' => $literal]);
    }

    /** Postgres arrays: ANY() */
    public function findHavingLabel(string $label): array
    {
        $st = $this->pdo->prepare("SELECT id, labels FROM tag_sets WHERE :needle = ANY(labels) ORDER BY id DESC");
        $st->execute([':needle' => $label]);
        return $st->fetchAll();
    }
}
