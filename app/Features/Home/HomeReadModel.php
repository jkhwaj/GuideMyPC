<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Home;

use GuideMyPC\Features\Downloads\DownloadPolicy;
use mysqli;

final class HomeReadModel
{
    public function __construct(
        private readonly mysqli $connection,
        private readonly DownloadPolicy $downloadPolicy = new DownloadPolicy()
    ) {
    }

    /**
     * @return array{categories: list<array<string, mixed>>, popularGuides: list<array<string, mixed>>, recommendedGuides: list<array<string, mixed>>, downloads: list<array<string, mixed>>, communityPosts: list<array<string, mixed>>}
     */
    public function content(): array
    {
        $categories = $this->connection->query(
            'SELECT name, slug, description, icon FROM categories '
            . 'WHERE is_published = 1 '
            . 'ORDER BY featured_order IS NULL, featured_order ASC, name ASC'
        )->fetch_all(MYSQLI_ASSOC);

        $popularGuides = $this->connection->query(
            'SELECT guides.title, guides.slug, guides.description, guides.difficulty, '
            . 'categories.name AS category_name, categories.slug AS category_slug '
            . 'FROM guides JOIN categories ON guides.category_id = categories.id '
            . 'WHERE guides.is_published = 1 AND categories.is_published = 1 '
            . 'ORDER BY guides.featured_order IS NULL, guides.featured_order ASC, guides.views DESC, guides.created_at DESC LIMIT 4'
        )->fetch_all(MYSQLI_ASSOC);

        $downloads = $this->connection->query(
            'SELECT name, description, official_url, category, is_published, review_state FROM downloads '
            . 'WHERE ' . $this->downloadPolicy->publicWhereClause('downloads') . ' '
            . 'ORDER BY featured_order IS NULL, featured_order ASC, name ASC LIMIT 3'
        )->fetch_all(MYSQLI_ASSOC);

        $communityPosts = $this->connection->query(
            'SELECT community_posts.title, community_posts.created_at, users.full_name '
            . 'FROM community_posts JOIN users ON community_posts.user_id = users.id '
            . 'WHERE community_posts.is_published = 1 '
            . 'ORDER BY community_posts.created_at DESC LIMIT 3'
        )->fetch_all(MYSQLI_ASSOC);

        return [
            'categories' => $categories,
            'popularGuides' => $popularGuides,
            'recommendedGuides' => array_slice($popularGuides, 0, 3),
            'downloads' => array_values(array_filter(
                $downloads,
                fn (array $download): bool => $this->downloadPolicy->isPublic($download)
            )),
            'communityPosts' => $communityPosts,
        ];
    }
}
