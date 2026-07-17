<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$root = dirname(__DIR__);
$tests = ['tests/helpers_test.php', 'tests/search_integration_test.php', 'tests/knowledge_integration_test.php', 'tests/guide_integration_test.php', 'tests/account_integration_test.php'];
$failures = 0;

foreach ($tests as $test) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $test);
    if (!is_file($path)) continue;
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($path), $exitCode);
    if ($exitCode !== 0) $failures++;
}

exit($failures === 0 ? 0 : 1);
