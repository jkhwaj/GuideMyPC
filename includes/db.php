<?php

declare(strict_types=1);

function application_database_connection(): mysqli
{
    $host = config_value('DB_HOST');
    $user = config_value('DB_USER');
    $password = config_value('DB_PASSWORD', '') ?? '';
    $database = config_value('DB_NAME');
    $port = config_value('DB_PORT', '3306') ?? '3306';

    if ($host === null || $host === '' || $user === null || $database === null || $database === '' || !ctype_digit($port)) {
        throw new RuntimeException('Database configuration is incomplete.');
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = mysqli_init();

    if ($connection === false) {
        throw new RuntimeException('Unable to initialize the database connection.');
    }

    $connection->real_connect($host, $user, $password, $database, (int) $port);
    $connection->set_charset('utf8mb4');

    return $connection;
}
