<?php

require __DIR__ . '/../autoload.php';

use App\Database\Connection;
use App\DemoRunner;

date_default_timezone_set('Europe/Paris');

try {
    $conn = new Connection(
        host: 'db',
        port: 5432,
        db:   'howto',
        user: 'app',
        pass: 'secret',
    );

    (new DemoRunner($conn->pdo()))->run();
    echo "\nDone.\n";

} catch (Throwable $e) {
    fwrite(STDERR, "FATAL_ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
