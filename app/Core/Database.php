<?php

declare(strict_types=1);

namespace GuideMyPC\Core;

use mysqli;
use RuntimeException;

final class Database
{
    /**
     * @param array{host: ?string, user: ?string, password: ?string, database: ?string, port: ?string} $configuration
     */
    public static function connect(array $configuration): mysqli
    {
        $host = $configuration['host'];
        $user = $configuration['user'];
        $password = $configuration['password'] ?? '';
        $database = $configuration['database'];
        $port = $configuration['port'] ?? '3306';

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
}
