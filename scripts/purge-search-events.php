<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/database/runner.php';

try {
    $days = 90;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help') {
            exit("Usage: php scripts/purge-search-events.php [--days=90]\n");
        }

        if (str_starts_with($argument, '--days=')) {
            $days = (int) substr($argument, strlen('--days='));
            continue;
        }

        throw new InvalidArgumentException(sprintf('Unknown option: %s', $argument));
    }

    if ($days < 1 || $days > 365) {
        throw new InvalidArgumentException('Retention must be between 1 and 365 days.');
    }

    $database = config_value('DB_NAME', 'guidemypc') ?? 'guidemypc';
    $connection = database_connection($database, false);
    $cutoff = gmdate('Y-m-d', strtotime('-' . $days . ' days'));
    $statement = $connection->prepare('DELETE FROM search_events WHERE event_date < ?');
    $statement->bind_param('s', $cutoff);
    $statement->execute();
    printf("Purged %d aggregate search event row(s).\n", $statement->affected_rows);
    $statement->close();
    $connection->close();
} catch (Throwable $exception) {
    fwrite(STDERR, 'Search-event purge failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
