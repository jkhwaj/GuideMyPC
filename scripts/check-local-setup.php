<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

/**
 * Read simple KEY=value entries without exposing local secrets in output.
 * Application configuration will adopt this contract in task 002.
 *
 * @return array<string, string>
 */
function readLocalEnvironment(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $values = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return [];
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);

        if (preg_match('/^[A-Z][A-Z0-9_]*$/', $key) !== 1) {
            continue;
        }

        $values[$key] = trim($value);
    }

    return $values;
}

function report(bool $passed, string $message): void
{
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $message);
}

$projectRoot = dirname(__DIR__);
$environment = readLocalEnvironment($projectRoot . DIRECTORY_SEPARATOR . '.env');
$failures = 0;
$requiredExtensions = ['mysqli', 'mbstring', 'openssl', 'fileinfo', 'json', 'curl'];

printf("GuideMyPC local setup check (PHP %s)\n", PHP_VERSION);

if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
    report(true, 'PHP 8.2 or newer is available.');
} else {
    report(false, 'PHP 8.2 or newer is required. Run C:\\xampp\\php\\php.exe to use the XAMPP PHP runtime.');
    $failures++;
}

foreach ($requiredExtensions as $extension) {
    if (extension_loaded($extension)) {
        report(true, sprintf('PHP extension %s is enabled.', $extension));
        continue;
    }

    report(false, sprintf('PHP extension %s is missing. Enable it in C:\\xampp\\php\\php.ini and restart Apache.', $extension));
    $failures++;
}

if ($environment === []) {
    report(false, 'Missing .env. Copy .env.example to .env before running the check.');
    $failures++;
} else {
    report(true, 'Local .env file is present.');
}

$database = [
    'host' => $environment['DB_HOST'] ?? 'localhost',
    'port' => $environment['DB_PORT'] ?? '3306',
    'name' => $environment['DB_NAME'] ?? 'guidemypc',
    'user' => $environment['DB_USER'] ?? 'root',
    'password' => $environment['DB_PASSWORD'] ?? '',
];

if (!ctype_digit($database['port']) || (int) $database['port'] < 1 || (int) $database['port'] > 65535) {
    report(false, 'DB_PORT must be a number between 1 and 65535.');
    $failures++;
} elseif (extension_loaded('mysqli')) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $connection = mysqli_init();
    $connected = $connection !== false && @mysqli_real_connect(
        $connection,
        $database['host'],
        $database['user'],
        $database['password'],
        $database['name'],
        (int) $database['port']
    );

    if ($connected) {
        report(true, 'MariaDB accepted the local database connection.');
        mysqli_close($connection);
    } else {
        report(false, 'MariaDB connection failed. Start MySQL in XAMPP and verify DB_HOST, DB_PORT, DB_NAME, and local account access in .env.');
        $failures++;
    }
}

if ($failures > 0) {
    printf("\nSetup check failed with %d issue(s). See README.md for local XAMPP fixes.\n", $failures);
    exit(1);
}

printf("\nLocal setup check passed. Open http://localhost/GuideMyPC/ in your browser.\n");
