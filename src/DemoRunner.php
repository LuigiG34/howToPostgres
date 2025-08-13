<?php

namespace App;

use App\Repository\EventRepository;
use App\Repository\ReadingRepository;
use App\Repository\TagSetRepository;
use App\Repository\TodoRepository;
use App\Repository\UserEmailRepository;
use PDO;

final class DemoRunner
{
    public function __construct(private readonly PDO $pdo) {}

    public function run(): void
    {
        // Import repositories
        $todos = new TodoRepository($this->pdo);
        $users = new UserEmailRepository($this->pdo);
        $events = new EventRepository($this->pdo);
        $tags = new TagSetRepository($this->pdo);
        $readings = new ReadingRepository($this->pdo);

        // Create tables
        $todos->createTable();
        $users->createTable();
        $events->createTable();
        $tags->createTable();
        $readings->createTable();

        $this->section('Basic INSERT + SELECT (RETURNING)');
        $id = $todos->insert('Hello Postgres ' . date('c'));
        echo "Inserted todo id={$id}\n";
        echo json_encode($todos->latest(5), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $this->section('UPDATE … RETURNING (mark all done)');
        echo json_encode($todos->markAllDoneReturning(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $this->section('DELETE … RETURNING (delete done)');
        echo json_encode($todos->deleteAllDoneReturning(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $this->section('UPSERT (INSERT … ON CONFLICT … DO UPDATE)');
        echo "[first]  " . json_encode($users->upsert('user@example.com'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        sleep(1);
        echo "[second] " . json_encode($users->upsert('user@example.com'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $this->section('ILIKE (case-insensitive LIKE)');
        $todos->insert('Hello World');
        $todos->insert('hello postgres');
        echo json_encode($todos->findByTitleIlike('hello'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $this->section('JSONB (@> containment)');
        $events->insert(['type' => 'signup', 'user' => ['id' => 1, 'role' => 'admin']]);
        $events->insert(['type' => 'login', 'user' => ['id' => 1]]);
        echo json_encode($events->findWhereContainsType('signup'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $this->section('Arrays (text[]) + ANY()');
        $tags->insertLabels(['php','postgres','demo']);
        $tags->insertLabels(['symfony','orm']);
        echo json_encode($tags->findHavingLabel('php'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $this->section('DISTINCT ON (latest per device)');
        $readings->seed();
        echo json_encode($readings->latestPerDevice(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }

    private function section(string $title): void
    {
        echo "\n==== {$title} ====\n";
    }
}
