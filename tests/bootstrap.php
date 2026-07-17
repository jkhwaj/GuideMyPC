<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap/test.php';

// Integration tests must report failures to the CLI, not render browser error pages.
restore_error_handler();
restore_exception_handler();

function test_database_name_is_safe(string $database, string $applicationDatabase): bool
{
    return $database !== ''
        && $database !== $applicationDatabase
        && preg_match('/^[A-Za-z0-9_]+_test$/', $database) === 1;
}

function test_database_connection(): mysqli
{
    $database = config_value('DB_TEST_NAME');
    $applicationDatabase = config_value('DB_NAME', 'guidemypc') ?? 'guidemypc';

    if (!is_string($database) || !test_database_name_is_safe($database, $applicationDatabase)) {
        throw new RuntimeException('Set DB_TEST_NAME to a dedicated database ending in _test and distinct from DB_NAME.');
    }

    $host = config_value('DB_HOST');
    $user = config_value('DB_USER');
    $password = config_value('DB_PASSWORD', '') ?? '';
    $port = config_value('DB_PORT', '3306') ?? '3306';

    if ($host === null || $host === '' || $user === null || $user === '' || !ctype_digit($port)) {
        throw new RuntimeException('Database configuration is incomplete. See database/README.md.');
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli($host, $user, $password, $database, (int) $port);
    $connection->set_charset('utf8mb4');

    return $connection;
}

function test_database_or_fail(): mysqli
{
    try {
        return test_database_connection();
    } catch (Throwable $exception) {
        fwrite(STDERR, 'FAIL: test database configuration: ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}
