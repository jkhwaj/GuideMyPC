<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $location): never
{
    if (str_contains($location, "\r") || str_contains($location, "\n")) {
        abort_request(400, 'invalid_redirect', 'The requested destination is invalid.');
    }

    header('Location: ' . $location, true, 303);
    exit;
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        return;
    }

    header('Allow: POST');
    abort_request(405, 'method_not_allowed', 'This action requires a form submission.');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function require_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        abort_request(419, 'invalid_csrf_token', 'Your form session has expired. Refresh the page and try again.');
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

function current_user_id(): int
{
    return is_logged_in() ? (int) $_SESSION['user_id'] : 0;
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        if (expects_json()) {
            abort_request(401, 'authentication_required', 'Sign in before continuing.');
        }

        flash('error', 'Sign in before continuing.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        abort_request(403, 'admin_required', 'You do not have permission to access this page.');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return is_string($value) ? $value : null;
}

function client_address(): string
{
    return filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: 'unknown';
}

function rate_limit_allows(string $action, int $limit, int $windowSeconds): bool
{
    $directory = private_storage_path('rate-limits');

    if ($directory === null) {
        return false;
    }

    $key = hash('sha256', $action . '|' . client_address());
    $path = $directory . DIRECTORY_SEPARATOR . $key . '.json';
    $now = time();
    $timestamps = [];

    $handle = @fopen($path, 'c+');

    if ($handle === false || !flock($handle, LOCK_EX)) {
        if ($handle !== false) {
            fclose($handle);
        }

        return false;
    }

    $contents = stream_get_contents($handle);
    $stored = is_string($contents) ? json_decode($contents, true) : [];

    if (is_array($stored)) {
        foreach ($stored as $timestamp) {
            if (is_int($timestamp) && $timestamp > $now - $windowSeconds) {
                $timestamps[] = $timestamp;
            }
        }
    }

    $allowed = count($timestamps) < $limit;

    if ($allowed) {
        $timestamps[] = $now;
    }

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($timestamps, JSON_THROW_ON_ERROR));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return $allowed;
}
