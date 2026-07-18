<?php

declare(strict_types=1);

/** @return array<string, string> */
function knowledge_article_types(): array
{
    return [
        'explanation' => 'Explanation', 'error_code' => 'Error code', 'faq' => 'FAQ', 'glossary' => 'Glossary',
        'maintenance' => 'Maintenance', 'security' => 'Security', 'hardware' => 'Hardware', 'software' => 'Software', 'networking' => 'Networking',
    ];
}

function knowledge_safe_reference_url(?string $url): ?string
{
    if (!is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return null;
    }

    return parse_url($url, PHP_URL_SCHEME) === 'https' ? $url : null;
}

function knowledge_render_content(string $content): string
{
    return nl2br(e($content));
}

/** @return array<string, mixed>|null */
function knowledge_published_article(mysqli $connection, string $slug): ?array
{
    return (new GuideMyPC\Features\Knowledge\KnowledgeRepository($connection))->publishedArticle($slug);
}
