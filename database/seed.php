<?php

declare(strict_types=1);

require_once __DIR__ . '/runner.php';

try {
    $options = database_runner_options($argv);

    if ($options['help']) {
        exit("Usage: php database/seed.php [--database=guidemypc]\n");
    }

    $connection = database_connection($options['database'], false);
    $lockName = 'guidemypc_schema_migrations';
    database_acquire_lock($connection, $lockName);

    try {
        $migrationTable = $connection->query("SHOW TABLES LIKE 'schema_migrations'");

        if ($migrationTable->num_rows === 0) {
            throw new RuntimeException('Run database/migrate.php before loading sample content.');
        }

        $files = database_sql_files(__DIR__ . '/seeds');

        foreach ($files as $name => $sql) {
            database_run_sql($connection, $sql, $name);
            printf("Loaded %s\n", $name);
        }

        $projection = new GuideMyPC\Features\Guides\GuideSearchProjection($connection);
        $indexed = $projection->rebuildAll();
        printf("Rebuilt %d guide search document(s).\n", $indexed);

        printf("Seed complete: %d file(s) processed.\n", count($files));
    } finally {
        database_release_lock($connection, $lockName);
        $connection->close();
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Seed failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
