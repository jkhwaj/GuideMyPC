<?php

declare(strict_types=1);

namespace GuideMyPC\Core;

final class Environment
{
    /**
     * @return array<string, string>
     */
    public static function load(string $path): array
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

    /**
     * @param array<string, string> $values
     */
    public static function value(array $values, string $key, ?string $default = null): ?string
    {
        return $values[$key] ?? $default;
    }

    public static function privateStoragePath(string $root, ?string $configuredPath, string $directory = ''): ?string
    {
        $basePath = $configuredPath !== null && $configuredPath !== ''
            ? $configuredPath
            : dirname($root, 2) . DIRECTORY_SEPARATOR . 'guidemypc-private';
        $path = $directory === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . $directory;

        if (!is_dir($path) && !@mkdir($path, 0700, true) && !is_dir($path)) {
            return null;
        }

        return $path;
    }

    public static function configureErrorReporting(?string $logDirectory): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');

        if ($logDirectory !== null) {
            ini_set('error_log', $logDirectory . DIRECTORY_SEPARATOR . 'php-error.log');
        }
    }
}
