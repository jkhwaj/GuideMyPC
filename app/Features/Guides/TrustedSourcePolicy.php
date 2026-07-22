<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Guides;

use mysqli;

final class TrustedSourcePolicy
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /** @return array{official_url: string, trusted_source_domain_id: int, source_last_reviewed_at: string|null}|null */
    public function approvedSource(string $url): ?array
    {
        $parts = parse_url($url);

        if (filter_var($url, FILTER_VALIDATE_URL) === false
            || !is_array($parts)
            || ($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['port'])) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($host === '' || filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return null;
        }

        $statement = $this->connection->prepare(
            'SELECT id, last_reviewed_at FROM trusted_source_domains WHERE domain = ? AND is_active = 1 LIMIT 1'
        );
        $statement->bind_param('s', $host);
        $statement->execute();
        $domain = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($domain === null) {
            return null;
        }

        return [
            'official_url' => $url,
            'trusted_source_domain_id' => (int) $domain['id'],
            'source_last_reviewed_at' => is_string($domain['last_reviewed_at'] ?? null) ? $domain['last_reviewed_at'] : null,
        ];
    }
}
