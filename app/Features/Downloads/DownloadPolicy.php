<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Downloads;

final class DownloadPolicy
{
    /** @var list<string> */
    private const REVIEW_STATES = ['pending', 'approved', 'stale', 'rejected', 'archived'];

    public function trustedUrl(mixed $value): ?string
    {
        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($value);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (
            strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || $host === ''
            || (filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
        ) {
            return null;
        }

        return $value;
    }

    public function normalizedName(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $name = preg_replace('/\s+/u', ' ', trim($value));

        return $name === null || $name === '' ? null : mb_strtolower($name);
    }

    public function normalizedUrl(mixed $value): ?string
    {
        $trustedUrl = $this->trustedUrl($value);

        if ($trustedUrl === null) {
            return null;
        }

        $parts = parse_url($trustedUrl);
        $scheme = strtolower((string) $parts['scheme']);
        $host = strtolower((string) $parts['host']);
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        $path = rtrim((string) ($parts['path'] ?? ''), '/');
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        $authority = $host;
        if ($port !== null && !(($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80))) {
            $authority .= ':' . $port;
        }

        return $scheme . '://' . $authority . $path . $query;
    }

    /**
     * @param array<string, mixed> $download
     */
    public function isPublic(array $download): bool
    {
        return (int) ($download['is_published'] ?? 0) === 1
            && ($download['review_state'] ?? '') === 'approved'
            && $this->trustedUrl($download['official_url'] ?? null) !== null;
    }

    public function publicWhereClause(string $table = ''): string
    {
        $prefix = $table === '' ? '' : $table . '.';

        return $prefix . "is_published = 1 AND " . $prefix . "review_state = 'approved'";
    }

    public function reviewStateIsValid(mixed $value): bool
    {
        return is_string($value) && in_array($value, self::REVIEW_STATES, true);
    }
}
