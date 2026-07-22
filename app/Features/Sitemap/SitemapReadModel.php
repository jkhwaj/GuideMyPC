<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Sitemap;

use GuideMyPC\Core\Url;
use mysqli;

final class SitemapReadModel
{
    /** @var list<string> */
    private const STATIC_PATHS = ['index.php', 'guides.php', 'knowledge.php', 'downloads.php', 'about.php', 'contact.php'];

    public function __construct(private readonly mysqli $connection)
    {
    }

    /** @return list<string> */
    public function publicUrls(?string $baseUrl): array
    {
        $urls = array_map(
            static fn (string $path): string => Url::applicationUrl($baseUrl, $path),
            self::STATIC_PATHS
        );

        $guides = $this->connection->query(
            'SELECT guides.slug, guides.updated_at FROM guides '
            . 'JOIN categories ON categories.id = guides.category_id '
            . 'WHERE guides.is_published = 1 AND categories.is_published = 1 '
            . 'ORDER BY guides.updated_at DESC'
        );

        while ($guide = $guides->fetch_assoc()) {
            $urls[] = Url::applicationUrl($baseUrl, 'guide.php?slug=' . rawurlencode($guide['slug']));
        }

        $articles = $this->connection->query(
            'SELECT knowledge_articles.slug, knowledge_articles.updated_at FROM knowledge_articles '
            . 'JOIN categories ON categories.id = knowledge_articles.category_id '
            . "WHERE knowledge_articles.publication_state = 'published' AND categories.is_published = 1 "
            . 'ORDER BY knowledge_articles.updated_at DESC'
        );

        while ($article = $articles->fetch_assoc()) {
            $urls[] = Url::applicationUrl($baseUrl, 'knowledge_article.php?slug=' . rawurlencode($article['slug']));
        }

        return $urls;
    }
}
