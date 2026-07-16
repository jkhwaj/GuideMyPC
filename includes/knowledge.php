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
    $statement = $connection->prepare(
        "SELECT knowledge_articles.*, categories.name AS category_name, categories.slug AS category_slug FROM knowledge_articles JOIN categories ON knowledge_articles.category_id = categories.id WHERE knowledge_articles.slug = ? AND knowledge_articles.publication_state = 'published' AND categories.is_published = 1 LIMIT 1"
    );
    $statement->bind_param('s', $slug);
    $statement->execute();
    $article = $statement->get_result()->fetch_assoc();
    $statement->close();

    return is_array($article) ? $article : null;
}
