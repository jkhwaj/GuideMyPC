<?php

declare(strict_types=1);

if (!defined('GUIDEMYPC_ROOT')) {
    define('GUIDEMYPC_ROOT', dirname(__DIR__));
}

/**
 * Load local KEY=value configuration without putting secrets in source control.
 * Values are intentionally kept in process memory only.
 *
 * @return array<string, string>
 */
function load_environment(string $path): array
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

$guideMyPcEnvironment = load_environment(GUIDEMYPC_ROOT . DIRECTORY_SEPARATOR . '.env');

function config_value(string $key, ?string $default = null): ?string
{
    global $guideMyPcEnvironment;

    return $guideMyPcEnvironment[$key] ?? $default;
}

function is_https_request(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
}

function private_storage_path(string $directory = ''): ?string
{
    $configuredPath = config_value('APP_PRIVATE_PATH');
    $basePath = $configuredPath !== null && $configuredPath !== ''
        ? $configuredPath
        : dirname(GUIDEMYPC_ROOT, 2) . DIRECTORY_SEPARATOR . 'guidemypc-private';
    $path = $directory === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . $directory;

    if (!is_dir($path) && !@mkdir($path, 0700, true) && !is_dir($path)) {
        return null;
    }

    return $path;
}

function configure_error_handling(): void
{
    $isLocal = config_value('APP_ENV', 'production') === 'local';
    error_reporting(E_ALL);
    ini_set('display_errors', $isLocal ? '1' : '0');
    ini_set('display_startup_errors', $isLocal ? '1' : '0');
    ini_set('log_errors', '1');

    $logDirectory = private_storage_path('logs');

    if ($logDirectory !== null) {
        ini_set('error_log', $logDirectory . DIRECTORY_SEPARATOR . 'php-error.log');
    }
}

function configure_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', is_https_request() ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    session_name('guidemypc_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function send_security_headers(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), geolocation=(), microphone=(), payment=()');
    header("Content-Security-Policy-Report-Only: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'");
}

configure_error_handling();
configure_session();
send_security_headers();
