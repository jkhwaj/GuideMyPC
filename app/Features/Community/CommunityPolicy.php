<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Community;

final class CommunityPolicy
{
    /**
     * @param array<string, mixed> $post
     */
    public function isPublic(array $post): bool
    {
        return (int) ($post['is_published'] ?? 0) === 1;
    }

    public function publicWhereClause(string $table = ''): string
    {
        return ($table === '' ? '' : $table . '.') . 'is_published = 1';
    }
}
