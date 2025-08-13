<?php

namespace App\Database;

use PDO;
use RuntimeException;
use Throwable;

final class Connection
{
    private PDO $pdo;

    public function __construct(
        private readonly string $host,
        private readonly int    $port,
        private readonly string $db,
        private readonly string $user,
        private readonly string $pass,
        int $retries = 30,
        int $sleepSeconds = 1,
    ) {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $this->host, $this->port, $this->db);

        while ($retries-- > 0) {
            try {
                $this->pdo = new PDO($dsn, $this->user, $this->pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                return;
            } catch (Throwable) {
                if ($retries === 0) break;
                fwrite(STDERR, "Waiting for Postgres…\n");
                sleep($sleepSeconds);
            }
        }
        throw new RuntimeException('Unreachable database.');
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
