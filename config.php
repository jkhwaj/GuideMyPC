<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/security.php';

$host = config_value('DB_HOST');
$user = config_value('DB_USER');
$password = config_value('DB_PASSWORD');
$database = config_value('DB_NAME');
$port = config_value('DB_PORT', '3306');

if ($host === null || $host === '' || $user === null || $database === null || $database === '' || !ctype_digit($port)) {
    http_response_code(500);
    exit('Application configuration is incomplete. See README.md for local setup.');
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();

if ($conn === false || !@mysqli_real_connect($conn, $host, $user, $password ?? '', $database, (int) $port)) {
    http_response_code(500);
    exit('The application database is unavailable. Please try again later.');
}

$conn->set_charset('utf8mb4');
