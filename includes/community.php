<?php

declare(strict_types=1);

function community_visible_state(string $state): bool
{
    return in_array($state, ['open', 'solved'], true);
}

function community_safe_content(string $content): string
{
    return nl2br(e($content));
}
