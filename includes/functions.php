<?php

declare(strict_types=1);

function request_id(): string
{
    static $requestId;

    if (!is_string($requestId)) {
        $requestId = bin2hex(random_bytes(12));
    }

    return $requestId;
}

function application_url(string $path = ''): string
{
    $baseUrl = rtrim(config_value('APP_URL', '') ?? '', '/');

    if ($path === '') {
        return $baseUrl;
    }

    return $baseUrl . '/' . ltrim($path, '/');
}

function expects_json(): bool
{
    return str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
}

function set_response_status(int $status): void
{
    if ($status === 419) {
        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        header($protocol . ' 419 Page Expired');
        return;
    }

    http_response_code($status);
}

/**
 * @param array<string, mixed>|null $data
 * @param array{code: string, message: string}|null $error
 * @param array<string, mixed> $meta
 */
function json_response(int $status, ?array $data = null, ?array $error = null, array $meta = []): never
{
    $meta['request_id'] = request_id();
    $response = ['ok' => $status >= 200 && $status < 300, 'meta' => $meta];

    if ($data !== null) {
        $response['data'] = $data;
    }

    if ($error !== null) {
        $response['error'] = $error;
    }

    set_response_status($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function abort_request(int $status, string $code, string $message): never
{
    if (expects_json()) {
        json_response($status, null, ['code' => $code, 'message' => $message]);
    }

    render_error_page($status, $message);
    exit;
}

function required_string(mixed $value, int $maximumLength = 0): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);

    if ($value === '' || ($maximumLength > 0 && mb_strlen($value) > $maximumLength)) {
        return null;
    }

    return $value;
}

/**
 * @param array<string, mixed> $input
 * @param list<string> $keys
 */
function remember_old_input(array $input, array $keys): void
{
    foreach ($keys as $key) {
        if (isset($input[$key]) && is_string($input[$key])) {
            $_SESSION['_old_input'][$key] = $input[$key];
        }
    }
}

function old_input(string $key, string $default = ''): string
{
    $value = $_SESSION['_old_input'][$key] ?? $default;
    unset($_SESSION['_old_input'][$key]);

    return is_string($value) ? $value : $default;
}

/**
 * @return array{page: int, per_page: int, offset: int}
 */
function pagination_values(mixed $page, int $perPage = 20): array
{
    $pageNumber = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;

    return ['page' => $pageNumber, 'per_page' => $perPage, 'offset' => ($pageNumber - 1) * $perPage];
}

function in_transaction(mysqli $connection, callable $callback): mixed
{
    $connection->begin_transaction();

    try {
        $result = $callback();
        $connection->commit();

        return $result;
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function render_flash_messages(): void
{
    foreach (['success', 'error', 'status'] as $type) {
        $message = flash($type);

        if ($message !== null) {
            printf('<div class="flash-message flash-%s" role="status">%s</div>', e($type), e($message));
        }
    }
}
