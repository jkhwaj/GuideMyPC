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

$testPaths = glob($root . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . '*_test.php');

if ($testPaths === false || $testPaths === []) {
    fwrite(STDERR, "FAIL: no test files matching tests/*_test.php were found.\n");
    exit(1);
}

sort($testPaths, SORT_STRING);
$failures = 0;

foreach ($testPaths as $path) {
    $test = str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($root) + 1));
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($path) . $databaseArgument, $exitCode);
    if ($exitCode !== 0) $failures++;
}

fwrite(STDOUT, sprintf("%s: %d test file(s); %d failure(s).\n", $failures === 0 ? 'PASS' : 'FAIL', count($testPaths), $failures));
exit($failures === 0 ? 0 : 1);
