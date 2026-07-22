<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

$root = dirname(__DIR__);
$failures = [];

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};

$requiredDirectories = [
    'app',
    'bootstrap',
    'config',
    'public/assets',
    'resources/views',
    'routes',
    'database',
    'scripts',
    'tests',
    'docs/submission/documents',
    'docs/submission/screenshots',
    'uml/source',
    'uml/exports',
];

foreach ($requiredDirectories as $directory) {
    $assert(is_dir($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory)), 'Missing required directory: ' . $directory);
}

$requiredFiles = [
    'docs/folder-layout.md',
    'scripts/package-submission.php',
    'docs/submission/documents/README.md',
    'uml/README.md',
    'uml/source/README.md',
    'uml/exports/README.md',
];

foreach ($requiredFiles as $file) {
    $assert(is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file)), 'Missing required file: ' . $file);
}

$composerPath = $root . DIRECTORY_SEPARATOR . 'composer.json';
$composer = is_file($composerPath) ? json_decode((string) file_get_contents($composerPath), true) : null;
$assert(is_array($composer), 'composer.json is missing or invalid JSON.');

if (is_array($composer)) {
    $scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
    $assert(isset($scripts['package:submission']), 'Composer script package:submission is missing.');
    $assert(isset($scripts['package:submission:strict']), 'Composer script package:submission:strict is missing.');
}

$packageScript = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'package-submission.php';
$temporaryZip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'guidemypc-package-test-' . bin2hex(random_bytes(6)) . '.zip';

try {
    $process = proc_open(
        [PHP_BINARY, $packageScript, '--commit=HEAD', '--output=' . $temporaryZip, '--force'],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        $root,
        null,
        ['bypass_shell' => true]
    );

    if (!is_resource($process)) {
        $failures[] = 'Could not start the submission packaging command.';
    } else {
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $assert($exitCode === 0, 'Submission packaging failed: ' . trim(($stderr ?: '') . "\n" . ($stdout ?: '')));
        $assert(is_file($temporaryZip) && filesize($temporaryZip) > 0, 'Submission ZIP was not created.');

        if ($exitCode === 0 && is_file($temporaryZip)) {
            $archive = new PharData($temporaryZip);
            foreach ([
                'GuideMyPC/README.md',
                'GuideMyPC/frontend',
                'GuideMyPC/backend',
                'GuideMyPC/database',
                'GuideMyPC/docs',
                'GuideMyPC/uml',
                'GuideMyPC/PACKAGE-MANIFEST.txt',
            ] as $entry) {
                $assert(isset($archive[$entry]), 'Submission ZIP is missing: ' . $entry);
            }
            unset($archive);
        }
    }
} catch (Throwable $exception) {
    $failures[] = 'Submission package test raised an exception: ' . $exception->getMessage();
} finally {
    if (is_file($temporaryZip)) {
        @unlink($temporaryZip);
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, "PASS: submission folder structure and package layout are valid.\n");
