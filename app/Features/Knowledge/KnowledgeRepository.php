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

    /** @return list<array{name: string, slug: string}> */
    public function publishedCategories(): array
    {
        $result = $this->connection->query('SELECT name, slug FROM categories WHERE is_published = 1 ORDER BY name');

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * @return list<array{title: string, slug: string, article_type: string, error_code: string|null, summary: string, last_reviewed_at: string|null, category_name: string}>
     */
    public function publishedArticles(string $type = '', string $category = ''): array
    {
        $where = ["knowledge_articles.publication_state = 'published'", 'categories.is_published = 1'];
        $bindTypes = '';
        $bindValues = [];

        if ($type !== '') {
            $where[] = 'knowledge_articles.article_type = ?';
            $bindTypes .= 's';
            $bindValues[] = $type;
        }

        if ($category !== '') {
            $where[] = 'categories.slug = ?';
            $bindTypes .= 's';
            $bindValues[] = $category;
        }

        $statement = $this->connection->prepare(
            'SELECT knowledge_articles.title, knowledge_articles.slug, knowledge_articles.article_type, knowledge_articles.error_code, knowledge_articles.summary, knowledge_articles.last_reviewed_at, categories.name AS category_name '
            . 'FROM knowledge_articles JOIN categories ON knowledge_articles.category_id = categories.id WHERE ' . implode(' AND ', $where) . ' '
            . 'ORDER BY knowledge_articles.last_reviewed_at DESC, knowledge_articles.title ASC'
        );

        if ($bindTypes !== '') {
            $statement->bind_param($bindTypes, ...$bindValues);
        }

        $statement->execute();
        $articles = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $statement->close();

        return $articles;
    }
}
