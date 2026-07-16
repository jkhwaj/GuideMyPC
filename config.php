<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/errors.php';
require_once __DIR__ . '/includes/db.php';

configure_application_error_handling();

try {
    $conn = application_database_connection();
} catch (Throwable $exception) {
    application_log('error', 'Database connection failed.', ['message' => $exception->getMessage()]);
    abort_request(500, 'database_unavailable', 'The application is temporarily unavailable. Please try again later.');
}
