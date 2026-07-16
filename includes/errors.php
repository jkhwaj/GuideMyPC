<?php

declare(strict_types=1);

/**
 * @param array<string, mixed> $context
 */
function application_log(string $level, string $message, array $context = []): void
{
    $directory = private_storage_path('logs');

    if ($directory === null) {
        return;
    }

    $record = [
        'time' => gmdate('c'),
        'level' => $level,
        'request_id' => request_id(),
        'message' => $message,
        'context' => redact_log_context($context),
    ];

    @file_put_contents(
        $directory . DIRECTORY_SEPARATOR . 'application.log',
        json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

function redact_log_context(mixed $value, string $key = ''): mixed
{
    if (preg_match('/(?:password|token|secret|cookie|authorization|csrf)/i', $key) === 1) {
        return '[redacted]';
    }

    if (!is_array($value)) {
        return is_string($value) && mb_strlen($value) > 500 ? mb_substr($value, 0, 500) . '...' : $value;
    }

    $redacted = [];

    foreach ($value as $childKey => $childValue) {
        $redacted[(string) $childKey] = redact_log_context($childValue, (string) $childKey);
    }

    return $redacted;
}

function render_error_page(int $status, string $message): void
{
    $titles = [
        403 => 'Access denied',
        404 => 'Page not found',
        405 => 'Method not allowed',
        419 => 'Form expired',
        429 => 'Try again later',
        500 => 'Something went wrong',
    ];
    $title = $titles[$status] ?? 'Request failed';

    set_response_status($status);
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> | GuideMyPC</title>
    <link rel="stylesheet" href="<?php echo e(application_url('css/style.css')); ?>">
</head>
<body>
    <main class="error-page" aria-labelledby="error-title">
        <p class="section-label">Error <?php echo (int) $status; ?></p>
        <h1 id="error-title"><?php echo e($title); ?></h1>
        <p><?php echo e($message); ?></p>
        <a class="primary-btn" href="<?php echo e(application_url('index.php')); ?>">Return home</a>
    </main>
</body>
</html>
<?php
}

function application_exception_handler(Throwable $exception): void
{
    application_log('error', 'Unhandled application exception.', [
        'type' => $exception::class,
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ]);
    abort_request(500, 'internal_error', 'We could not complete that request. Please try again.');
}

function application_error_handler(int $severity, string $message, string $file, int $line): bool
{
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
}

function configure_application_error_handling(): void
{
    set_error_handler('application_error_handler');
    set_exception_handler('application_exception_handler');

    register_shutdown_function(static function (): void {
        $error = error_get_last();

        if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            return;
        }

        application_log('critical', 'Fatal PHP error.', $error);

        if (!headers_sent()) {
            render_error_page(500, 'We could not complete that request. Please try again.');
        }
    });
}
