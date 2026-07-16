<?php

declare(strict_types=1);

require_once __DIR__ . '/runner.php';

try {
    $options = database_runner_options($argv);

    if ($options['help']) {
        exit("Usage: php database/migrate.php [--database=guidemypc]\n");
    }

    $connection = database_connection($options['database'], true);
    $lockName = 'guidemypc_schema_migrations';
    database_acquire_lock($connection, $lockName);

    try {
        $connection->query(
            'CREATE TABLE IF NOT EXISTS schema_migrations ('
            . 'version VARCHAR(255) NOT NULL PRIMARY KEY, '
            . 'checksum CHAR(64) NOT NULL, '
            . 'applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $installed = [];
        $result = $connection->query('SELECT version, checksum FROM schema_migrations');

        while ($row = $result->fetch_assoc()) {
            $installed[$row['version']] = $row['checksum'];
        }

        $files = database_sql_files(__DIR__ . '/migrations');
        $applied = 0;

        foreach ($files as $version => $sql) {
            $checksum = hash('sha256', $sql);

            if (isset($installed[$version])) {
                if (!hash_equals($installed[$version], $checksum)) {
                    throw new RuntimeException(sprintf('Migration %s was changed after it was applied.', $version));
                }

                continue;
            }

            database_run_sql($connection, $sql, $version);
            $statement = $connection->prepare('INSERT INTO schema_migrations (version, checksum) VALUES (?, ?)');
            $statement->bind_param('ss', $version, $checksum);
            $statement->execute();
            $statement->close();
            $applied++;
            printf("Applied %s\n", $version);
        }

        printf("Migration complete: %d applied, %d total.\n", $applied, count($files));
    } finally {
        database_release_lock($connection, $lockName);
        $connection->close();
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
