<?php

namespace App\Repository;

use PDO;

final class UserEmailRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function createTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users_email (
              id SERIAL PRIMARY KEY,
              email TEXT UNIQUE NOT NULL,
              last_seen TIMESTAMPTZ NOT NULL DEFAULT NOW()
            )");
    }

    /** Postgres-specific: UPSERT with RETURNING */
    public function upsert(string $email): array
    {
        $sql = "
            INSERT INTO users_email(email, last_seen)
            VALUES (:e, NOW())
            ON CONFLICT (email) DO UPDATE SET last_seen = EXCLUDED.last_seen
            RETURNING id, email, last_seen";
        $st = $this->pdo->prepare($sql);
        $st->execute([':e' => $email]);
        return $st->fetch() ?: [];
    }
}
