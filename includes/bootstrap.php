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
    return GuideMyPC\Core\Environment::load($path);
}

$guideMyPcEnvironment = load_environment(GUIDEMYPC_ROOT . DIRECTORY_SEPARATOR . '.env');

function config_value(string $key, ?string $default = null): ?string
{
    global $guideMyPcEnvironment;

    return GuideMyPC\Core\Environment::value($guideMyPcEnvironment, $key, $default);
}

function is_https_request(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
}

function private_storage_path(string $directory = ''): ?string
{
    return GuideMyPC\Core\Environment::privateStoragePath(
        GUIDEMYPC_ROOT,
        config_value('APP_PRIVATE_PATH'),
        $directory
    );
}

function configure_error_handling(): void
{
    GuideMyPC\Core\Environment::configureErrorReporting(private_storage_path('logs'));
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
    header("Content-Security-Policy-Report-Only: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; frame-src 'self' https://www.youtube-nocookie.com; object-src 'none'");
}
