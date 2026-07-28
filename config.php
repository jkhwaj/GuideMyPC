<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap/web.php';
require_once __DIR__ . '/includes/accounts.php';

try {
    $conn = application_database_connection();
} catch (Throwable $exception) {
    application_log('error', 'Database connection failed.', ['message' => $exception->getMessage()]);
    abort_request(500, 'database_unavailable', 'The application is temporarily unavailable. Please try again later.');
}

if (PHP_SAPI !== 'cli') {
    restore_remembered_account_session($conn);
}
