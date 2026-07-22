<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/database/runner.php';

try {
    $options = database_runner_options($argv);

    if ($options['help']) {
        exit("Usage: php scripts/rebuild-guide-search.php [--database=guidemypc]\n");
    }

    $connection = database_connection($options['database'], false);
    $projection = new GuideMyPC\Features\Guides\GuideSearchProjection($connection);
    $count = $projection->rebuildAll();
    $connection->close();
    printf("Rebuilt %d guide search document(s).\n", $count);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Guide search rebuild failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
