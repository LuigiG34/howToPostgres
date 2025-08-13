<?php

namespace App\Repository;

use PDO;

final class TodoRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function createTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS todos (
              id SERIAL PRIMARY KEY,
              title TEXT NOT NULL,
              done BOOLEAN NOT NULL DEFAULT FALSE,
              created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
            )");
    }

    public function insert(string $title, bool $done = false): int
    {
        $st = $this->pdo->prepare("INSERT INTO todos (title, done) VALUES (:t, :d) RETURNING id");
        $st->execute([':t' => $title, ':d' => $done]);
        return (int)$st->fetchColumn();
    }

    public function latest(int $limit = 5): array
    {
        $st = $this->pdo->prepare("SELECT id, title, done, created_at FROM todos ORDER BY id DESC LIMIT :lim");
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    /** Postgres-specific: UPDATE … RETURNING */
    public function markAllDoneReturning(): array
    {
        $st = $this->pdo->query("UPDATE todos SET done = TRUE WHERE done = FALSE
                                 RETURNING id, title, done, created_at");
        return $st->fetchAll();
    }

    /** Postgres-specific: DELETE … RETURNING */
    public function deleteAllDoneReturning(): array
    {
        $st = $this->pdo->query("DELETE FROM todos WHERE done = TRUE RETURNING id, title");
        return $st->fetchAll();
    }

    /** Postgres ILIKE demo */
    public function findByTitleIlike(string $needle, int $limit = 10): array
    {
        $st = $this->pdo->prepare("SELECT id, title FROM todos WHERE title ILIKE :q ORDER BY id DESC LIMIT :lim");
        $st->bindValue(':q', '%' . $needle . '%');
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
}
