<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$root = dirname(__DIR__);
$files = [];
$exitCode = 0;

exec('git -C ' . escapeshellarg($root) . ' ls-files --cached --others --exclude-standard -- "*.php" 2>NUL', $files, $exitCode);

if ($exitCode === 0) {
    $files = array_values(array_filter(
        $files,
        static fn (string $file): bool => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file))
    ));
} else {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        if (preg_match('#(^|/)(vendor|node_modules|logs|uploads|storage|coverage)(/|$)#', $relative) === 1) {
            continue;
        }

        $files[] = $relative;
    }
    sort($files, SORT_STRING);
    $exitCode = 0;
}

if ($exitCode !== 0 || $files === []) {
    fwrite(STDERR, "FAIL: Could not list current PHP source files.\n");
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

fwrite(STDOUT, 'PASS: linted ' . count($files) . " current PHP source files.\n");
