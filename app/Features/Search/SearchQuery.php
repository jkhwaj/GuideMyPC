<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Search;

final class SearchQuery
{
    public static function normalize(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $query = trim($value);

        if ($query === '' || mb_strlen($query) > 120) {
            return '';
        }

        return mb_strtolower((string) preg_replace('/\s+/u', ' ', $query));
    }

    public static function isAggregateSafe(string $query): bool
    {
        return preg_match('/(?:@|https?:\/\/|\b\d{7,}\b)/iu', $query) !== 1;
    }
}
