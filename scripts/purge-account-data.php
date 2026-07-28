<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/database/runner.php';

try {
    $database = config_value('DB_NAME', 'guidemypc') ?? 'guidemypc';
    $connection = database_connection($database, false);
    $connection->query('DELETE FROM password_reset_tokens WHERE expires_at < UTC_TIMESTAMP() OR used_at IS NOT NULL');
    $connection->query('DELETE FROM account_remember_tokens WHERE expires_at < UTC_TIMESTAMP() OR (revoked_at IS NOT NULL AND revoked_at < UTC_TIMESTAMP() - INTERVAL 30 DAY)');
    $connection->query('DELETE FROM user_activity WHERE created_at < UTC_TIMESTAMP() - INTERVAL 90 DAY');
    $connection->query('DELETE FROM account_security_events WHERE created_at < UTC_TIMESTAMP() - INTERVAL 365 DAY');
    $connection->close();
    fwrite(STDOUT, "Account retention cleanup completed.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'Account retention cleanup failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
