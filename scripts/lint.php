<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$root = dirname(__DIR__);
$files = [];
$exitCode = 0;

exec('git -C ' . escapeshellarg($root) . ' ls-files -- "*.php"', $files, $exitCode);

if ($exitCode !== 0 || $files === []) {
    fwrite(STDERR, "FAIL: Could not list tracked PHP files.\n");
    exit(1);
}

foreach ($files as $file) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    $output = [];

    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path), $output, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, implode(PHP_EOL, $output) . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, 'PASS: linted ' . count($files) . " tracked PHP files.\n");
