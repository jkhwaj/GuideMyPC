<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Knowledge;

use mysqli;

final class KnowledgeRepository
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function publishedArticle(string $slug): ?array
    {
        $statement = $this->connection->prepare(
            "SELECT knowledge_articles.*, categories.name AS category_name, categories.slug AS category_slug FROM knowledge_articles JOIN categories ON knowledge_articles.category_id = categories.id WHERE knowledge_articles.slug = ? AND knowledge_articles.publication_state = 'published' AND categories.is_published = 1 LIMIT 1"
        );
        $statement->bind_param('s', $slug);
        $statement->execute();
        $article = $statement->get_result()->fetch_assoc();
        $statement->close();

        return is_array($article) ? $article : null;
    }
}
