<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/bootstrap/cli.php';

/**
 * @return array{database: string, help: bool}
 */
function database_runner_options(array $arguments): array
{
    $options = ['database' => config_value('DB_NAME', 'guidemypc') ?? 'guidemypc', 'help' => false];

    foreach (array_slice($arguments, 1) as $argument) {
        if ($argument === '--help') {
            $options['help'] = true;
            continue;
        }

        if (str_starts_with($argument, '--database=')) {
            $options['database'] = substr($argument, strlen('--database='));
            continue;
        }

        throw new InvalidArgumentException(sprintf('Unknown option: %s', $argument));
    }

    if (preg_match('/^[A-Za-z0-9_]+$/', $options['database']) !== 1) {
        throw new InvalidArgumentException('Database names may contain only letters, numbers, and underscores.');
    }

    return $options;
}

function database_connection(string $database, bool $createDatabase): mysqli
{
    $host = config_value('DB_HOST');
    $user = config_value('DB_USER');
    $password = config_value('DB_PASSWORD', '') ?? '';
    $port = config_value('DB_PORT', '3306') ?? '3306';

    if ($host === null || $host === '' || $user === null || $user === '' || !ctype_digit($port)) {
        throw new RuntimeException('Database configuration is incomplete. See README.md.');
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = mysqli_init();

    if ($connection === false) {
        throw new RuntimeException('Unable to initialize the database connection.');
    }

    $connection->real_connect($host, $user, $password, null, (int) $port);
    $connection->set_charset('utf8mb4');

    if ($createDatabase) {
        $connection->query(sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            $database
        ));
    }

    $connection->select_db($database);

    return $connection;
}

/**
 * @return array<string, string>
 */
function database_sql_files(string $directory): array
{
    $files = glob($directory . DIRECTORY_SEPARATOR . '*.sql') ?: [];
    sort($files, SORT_NATURAL | SORT_FLAG_CASE);
    $result = [];

    foreach ($files as $file) {
        $name = basename($file);

        if (preg_match('/^\d{3}_[a-z0-9_]+\.sql$/', $name) !== 1) {
            throw new RuntimeException(sprintf('Invalid SQL file name: %s', $name));
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException(sprintf('Unable to read %s.', $name));
        }

        $result[$name] = $contents;
    }

    return $result;
}

function database_run_sql(mysqli $connection, string $sql, string $name): void
{
    if (trim($sql) === '') {
        throw new RuntimeException(sprintf('%s is empty.', $name));
    }

    if (!$connection->multi_query($sql)) {
        throw new RuntimeException(sprintf('%s failed: %s', $name, $connection->error));
    }

    while (true) {
        $result = $connection->store_result();

        if ($result instanceof mysqli_result) {
            $result->free();
        }

        if (!$connection->more_results()) {
            break;
        }

        try {
            $advanced = $connection->next_result();
        } catch (Throwable $exception) {
            throw new RuntimeException(sprintf('%s failed: %s', $name, $exception->getMessage()), 0, $exception);
        }

        if (!$advanced) {
            throw new RuntimeException(sprintf('%s failed: %s', $name, $connection->error));
        }
    }
}

function database_acquire_lock(mysqli $connection, string $name): void
{
    $statement = $connection->prepare('SELECT GET_LOCK(?, 10) AS acquired');
    $statement->bind_param('s', $name);
    $statement->execute();
    $result = $statement->get_result();
    $row = $result->fetch_assoc();
    $statement->close();

    if (($row['acquired'] ?? 0) !== '1' && ($row['acquired'] ?? 0) !== 1) {
        throw new RuntimeException('Another migration or seed process is running. Try again shortly.');
    }
}

function database_release_lock(mysqli $connection, string $name): void
{
    $statement = $connection->prepare('SELECT RELEASE_LOCK(?)');
    $statement->bind_param('s', $name);
    $statement->execute();
    $statement->close();
}
