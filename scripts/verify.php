<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$root = dirname(__DIR__);
$databaseArgument = '';

foreach ($_SERVER['argv'] ?? [] as $argument) {
    if (is_string($argument) && str_starts_with($argument, '--database=')) {
        $databaseArgument = ' ' . escapeshellarg($argument);
    }
}

$tests = [
    'tests/helpers_test.php',
    'tests/authorization_test.php',
    'tests/search_integration_test.php',
    'tests/knowledge_integration_test.php',
    'tests/guide_integration_test.php',
    'tests/account_integration_test.php',
    'tests/dashboard_integration_test.php',
];
$failures = 0;

foreach ($tests as $test) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $test);
    if (!is_file($path)) {
        fwrite(STDERR, 'FAIL: missing required test file ' . $test . PHP_EOL);
        $failures++;
        continue;
    }
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($path) . $databaseArgument, $exitCode);
    if ($exitCode !== 0) $failures++;
}

exit($failures === 0 ? 0 : 1);
