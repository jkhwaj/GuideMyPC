<?php

declare(strict_types=1);

function trusted_download_url(mixed $value): ?string
{
    if (!is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) return null;
    $parts = parse_url($value);
    $host = strtolower((string) ($parts['host'] ?? ''));
    if (($parts['scheme'] ?? '') !== 'https' || $host === '' || filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return null;
    return $value;
}

function download_is_public(array $download): bool
{
    return ($download['review_state'] ?? '') === 'approved' && trusted_download_url($download['official_url'] ?? null) !== null;
}
