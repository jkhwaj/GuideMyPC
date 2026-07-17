<?php

declare(strict_types=1);

const UPLOAD_MAX_BYTES = 5_242_880;

function upload_private_directory(): ?string
{
    return private_storage_path('uploads');
}

function upload_allowed_mime(string $mime): bool
{
    return in_array($mime, ['image/jpeg', 'image/png', 'text/plain'], true);
}

function upload_safe_original_name(mixed $name): ?string
{
    if (!is_string($name) || str_contains($name, "\0") || preg_match('/(?:\.php|\.phtml|\.phar|\.svg|\.html?)$/i', $name) === 1) return null;
    $base = basename($name);
    return $base !== '' && !str_contains($base, '..') && mb_strlen($base) <= 255 ? $base : null;
}
