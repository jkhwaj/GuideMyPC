<?php

declare(strict_types=1);

/**
 * @return array{query: string, type: string, platform: string, difficulty: string, safety: string, recency: string, page: int}
 */
function search_filters(array $input): array
{
    $type = required_string($input['type'] ?? null, 20) ?? '';
    $recency = required_string($input['recency'] ?? null, 3) ?? '';

    return [
        'query' => normalize_search_query($input['q'] ?? null),
        'type' => in_array($type, ['guide', 'download', 'community', 'article'], true) ? $type : '',
        'platform' => required_string($input['platform'] ?? null, 100) ?? '',
        'difficulty' => required_string($input['difficulty'] ?? null, 50) ?? '',
        'safety' => required_string($input['safety'] ?? null, 50) ?? '',
        'recency' => in_array($recency, ['30', '90'], true) ? $recency : '',
        'page' => pagination_values($input['page'] ?? null, 10)['page'],
    ];
}

function normalize_search_query(mixed $value): string
{
    return GuideMyPC\Features\Search\SearchQuery::normalize($value);
}

function search_query_is_aggregate_safe(string $query): bool
{
    return GuideMyPC\Features\Search\SearchQuery::isAggregateSafe($query);
}

/**
 * @param array{query: string, type: string, platform: string, difficulty: string, safety: string, recency: string, page: int} $filters
 * @return list<array<string, mixed>>
 */
function search_documents(mysqli $connection, array $filters): array
{
    if ($filters['query'] === '') {
        return [];
    }

    $query = $filters['query'];
    $like = '%' . $query . '%';
    $prefix = $query . '%';
    $documents = [];

    if ($filters['type'] === '' || $filters['type'] === 'guide') {
        $where = ['guides.is_published = 1', 'categories.is_published = 1'];
        $types = 'sssss';
        $values = [$query, $prefix, $like, $query, $query];
        $where[] = '(MATCH(guides.title, guides.description, guides.content) AGAINST (? IN NATURAL LANGUAGE MODE) OR MATCH(guide_search_documents.search_text) AGAINST (? IN NATURAL LANGUAGE MODE) OR guides.title LIKE ? OR guides.description LIKE ? OR guides.content LIKE ? OR guide_search_documents.search_text LIKE ? OR categories.name LIKE ?)';
        $types .= 'sssssss';
        array_push($values, $query, $query, $like, $like, $like, $like, $like);
        search_append_guide_filters($where, $types, $values, $filters, 'guides', 'categories');

        $statement = $connection->prepare(
            'SELECT guides.title, guides.slug, guides.description, guides.content, guide_search_documents.search_text, guides.difficulty, guides.risk_level, guides.created_at, '
            . 'categories.name AS platform_name, '
            . '(CASE WHEN LOWER(guides.title) = ? THEN 10000 WHEN LOWER(guides.title) LIKE ? THEN 8000 WHEN guides.title LIKE ? THEN 6000 ELSE 0 END '
            . '+ LEAST(COALESCE(MATCH(guides.title, guides.description, guides.content) AGAINST (? IN NATURAL LANGUAGE MODE), 0), 1000) * 100 '
            . '+ LEAST(COALESCE(MATCH(guide_search_documents.search_text) AGAINST (? IN NATURAL LANGUAGE MODE), 0), 1000) * 100) AS rank_score '
            . 'FROM guides JOIN categories ON guides.category_id = categories.id LEFT JOIN guide_search_documents ON guide_search_documents.guide_id = guides.id WHERE ' . implode(' AND ', $where) . ' LIMIT 60'
        );
        $statement->bind_param($types, ...$values);
        $statement->execute();
        $result = $statement->get_result();

        while ($row = $result->fetch_assoc()) {
            $documents[] = [
                'type' => 'guide', 'label' => 'Guide', 'title' => $row['title'], 'platform' => $row['platform_name'],
                'excerpt' => $row['description'] ?: $row['content'] ?: $row['search_text'], 'url' => application_url('guide.php?slug=' . rawurlencode($row['slug'])),
                'rank' => (float) $row['rank_score'], 'created_at' => $row['created_at'], 'difficulty' => $row['difficulty'], 'safety' => $row['risk_level'],
            ];
        }
        $statement->close();
    }

    if (($filters['type'] === '' || $filters['type'] === 'download') && $filters['difficulty'] === '' && $filters['safety'] === '') {
        $downloadPolicy = new GuideMyPC\Features\Downloads\DownloadPolicy();
        $where = [$downloadPolicy->publicWhereClause('downloads')];
        $types = 'ssss';
        $values = [$query, $prefix, $like, $query];
        $where[] = '(MATCH(downloads.name, downloads.description, downloads.category) AGAINST (? IN NATURAL LANGUAGE MODE) OR downloads.name LIKE ? OR downloads.description LIKE ? OR downloads.category LIKE ?)';
        $types .= 'ssss';
        array_push($values, $query, $like, $like, $like);

        if ($filters['platform'] !== '') {
            $where[] = 'LOWER(downloads.category) = ?';
            $types .= 's';
            $values[] = mb_strtolower(str_replace('-', ' ', $filters['platform']));
        }

        search_append_recency_filter($where, $filters['recency'], 'downloads');
        $statement = $connection->prepare(
            'SELECT downloads.name, downloads.description, downloads.category, downloads.official_url, downloads.created_at, downloads.is_published, downloads.review_state, '
            . '(CASE WHEN LOWER(downloads.name) = ? THEN 10000 WHEN LOWER(downloads.name) LIKE ? THEN 8000 WHEN downloads.name LIKE ? THEN 6000 ELSE 0 END '
            . '+ COALESCE(MATCH(downloads.name, downloads.description, downloads.category) AGAINST (? IN NATURAL LANGUAGE MODE), 0) * 100) AS rank_score '
            . 'FROM downloads WHERE ' . implode(' AND ', $where) . ' LIMIT 60'
        );
        $statement->bind_param($types, ...$values);
        $statement->execute();
        $result = $statement->get_result();

        while ($row = $result->fetch_assoc()) {
            if (!$downloadPolicy->isPublic($row)) {
                continue;
            }

            $documents[] = [
                'type' => 'download', 'label' => 'Official download', 'title' => $row['name'], 'platform' => $row['category'] ?? 'Official source',
                'excerpt' => $row['description'], 'url' => $row['official_url'], 'rank' => (float) $row['rank_score'],
                'created_at' => $row['created_at'], 'difficulty' => '', 'safety' => 'Official source',
            ];
        }
        $statement->close();
    }

    if (($filters['type'] === '' || $filters['type'] === 'community') && $filters['platform'] === '' && $filters['difficulty'] === '' && $filters['safety'] === '') {
        $where = ['community_posts.is_published = 1'];
        $types = 'ssss';
        $values = [$query, $prefix, $like, $query];
        $where[] = '(MATCH(community_posts.title, community_posts.content) AGAINST (? IN NATURAL LANGUAGE MODE) OR community_posts.title LIKE ? OR community_posts.content LIKE ?)';
        $types .= 'sss';
        array_push($values, $query, $like, $like);
        search_append_recency_filter($where, $filters['recency'], 'community_posts');
        $statement = $connection->prepare(
            'SELECT community_posts.title, community_posts.content, community_posts.created_at, '
            . '(CASE WHEN LOWER(community_posts.title) = ? THEN 10000 WHEN LOWER(community_posts.title) LIKE ? THEN 8000 WHEN community_posts.title LIKE ? THEN 6000 ELSE 0 END '
            . '+ COALESCE(MATCH(community_posts.title, community_posts.content) AGAINST (? IN NATURAL LANGUAGE MODE), 0) * 100) AS rank_score '
            . 'FROM community_posts WHERE ' . implode(' AND ', $where) . ' LIMIT 60'
        );
        $statement->bind_param($types, ...$values);
        $statement->execute();
        $result = $statement->get_result();

        while ($row = $result->fetch_assoc()) {
            $documents[] = [
                'type' => 'community', 'label' => 'Community question', 'title' => $row['title'], 'platform' => 'Community',
                'excerpt' => $row['content'], 'url' => application_url('community.php'), 'rank' => (float) $row['rank_score'],
                'created_at' => $row['created_at'], 'difficulty' => '', 'safety' => '',
            ];
        }
        $statement->close();
    }

    if (($filters['type'] === '' || $filters['type'] === 'article') && $filters['difficulty'] === '' && $filters['safety'] === '') {
        $where = ["knowledge_articles.publication_state = 'published'", 'categories.is_published = 1'];
        $types = 'ssss';
        $values = [$query, $query, $prefix, $query];
        $where[] = '(MATCH(knowledge_articles.title, knowledge_articles.error_code, knowledge_articles.summary, knowledge_articles.content) AGAINST (? IN NATURAL LANGUAGE MODE) OR knowledge_articles.title LIKE ? OR knowledge_articles.summary LIKE ? OR knowledge_articles.content LIKE ? OR knowledge_articles.error_code LIKE ? OR categories.name LIKE ?)';
        $types .= 'ssssss';
        array_push($values, $query, $like, $like, $like, $like, $like);

        if ($filters['platform'] !== '') {
            $where[] = 'categories.slug = ?';
            $types .= 's';
            $values[] = $filters['platform'];
        }

        search_append_recency_filter($where, $filters['recency'], 'knowledge_articles');
        $statement = $connection->prepare(
            'SELECT knowledge_articles.title, knowledge_articles.slug, knowledge_articles.error_code, knowledge_articles.summary, knowledge_articles.content, knowledge_articles.article_type, knowledge_articles.created_at, categories.name AS platform_name, '
            . '(CASE WHEN LOWER(knowledge_articles.title) = ? OR LOWER(knowledge_articles.error_code) = ? THEN 10000 WHEN LOWER(knowledge_articles.title) LIKE ? THEN 8000 ELSE 0 END '
            . '+ COALESCE(MATCH(knowledge_articles.title, knowledge_articles.error_code, knowledge_articles.summary, knowledge_articles.content) AGAINST (? IN NATURAL LANGUAGE MODE), 0) * 100) AS rank_score '
            . 'FROM knowledge_articles JOIN categories ON knowledge_articles.category_id = categories.id WHERE ' . implode(' AND ', $where) . ' LIMIT 60'
        );
        $statement->bind_param($types, ...$values);
        $statement->execute();
        $result = $statement->get_result();

        while ($row = $result->fetch_assoc()) {
            $documents[] = [
                'type' => 'article', 'label' => $row['article_type'] === 'error_code' ? 'Error code' : 'Knowledge article', 'title' => $row['title'], 'platform' => $row['platform_name'],
                'excerpt' => $row['summary'] ?: $row['content'], 'url' => application_url('knowledge_article.php?slug=' . rawurlencode($row['slug'])),
                'rank' => (float) $row['rank_score'], 'created_at' => $row['created_at'], 'difficulty' => '', 'safety' => '',
            ];
        }
        $statement->close();
    }

    usort($documents, static function (array $left, array $right): int {
        return $right['rank'] <=> $left['rank'] ?: strcmp((string) $right['created_at'], (string) $left['created_at']);
    });

    return $documents;
}

/**
 * @param list<string> $where
 * @param list<string> $values
 */
function search_append_guide_filters(array &$where, string &$types, array &$values, array $filters, string $guideTable, string $categoryTable): void
{
    if ($filters['platform'] !== '') {
        $where[] = $categoryTable . '.slug = ?';
        $types .= 's';
        $values[] = $filters['platform'];
    }

    if ($filters['difficulty'] !== '') {
        $where[] = $guideTable . '.difficulty = ?';
        $types .= 's';
        $values[] = $filters['difficulty'];
    }

    if ($filters['safety'] !== '') {
        $where[] = $guideTable . '.risk_level = ?';
        $types .= 's';
        $values[] = $filters['safety'];
    }

    search_append_recency_filter($where, $filters['recency'], $guideTable);
}

/** @param list<string> $where */
function search_append_recency_filter(array &$where, string $recency, string $table): void
{
    if ($recency === '30') {
        $where[] = $table . '.created_at >= UTC_TIMESTAMP() - INTERVAL 30 DAY';
    }

    if ($recency === '90') {
        $where[] = $table . '.created_at >= UTC_TIMESTAMP() - INTERVAL 90 DAY';
    }
}

/** @return array{platforms: list<array<string, string>>, difficulties: list<string>, safety_levels: list<string>} */
function search_filter_options(mysqli $connection): array
{
    $platforms = [];
    $result = $connection->query('SELECT slug, name FROM categories WHERE is_published = 1 ORDER BY name');

    while ($row = $result->fetch_assoc()) {
        $platforms[] = ['value' => $row['slug'], 'label' => $row['name']];
    }

    $options = ['platforms' => $platforms, 'difficulties' => [], 'safety_levels' => []];

    foreach (['difficulty' => 'difficulties', 'risk_level' => 'safety_levels'] as $column => $key) {
        $result = $connection->query('SELECT DISTINCT guides.' . $column . ' FROM guides JOIN categories ON categories.id = guides.category_id WHERE guides.is_published = 1 AND categories.is_published = 1 AND guides.' . $column . " IS NOT NULL AND guides." . $column . " <> '' ORDER BY guides." . $column);

        while ($row = $result->fetch_assoc()) {
            $options[$key][] = $row[$column];
        }
    }

    return $options;
}

function search_resolve_alias(mysqli $connection, string $query): string
{
    if ($query === '') {
        return '';
    }

    $statement = $connection->prepare('SELECT replacement FROM search_aliases WHERE alias = ? LIMIT 1');
    $statement->bind_param('s', $query);
    $statement->execute();
    $row = $statement->get_result()->fetch_assoc();
    $statement->close();

    return is_array($row) ? $row['replacement'] : $query;
}

/** @return list<string> */
function search_related_queries(mysqli $connection, string $query): array
{
    $statement = $connection->prepare('SELECT related_query FROM search_related_queries WHERE query_text = ? ORDER BY related_query LIMIT 4');
    $statement->bind_param('s', $query);
    $statement->execute();
    $result = $statement->get_result();
    $related = [];

    while ($row = $result->fetch_assoc()) {
        $related[] = $row['related_query'];
    }

    $statement->close();

    return $related;
}

function search_excerpt(string $content, string $query): string
{
    $excerpt = mb_strimwidth(trim($content), 0, 240, '...');
    $safe = e($excerpt);
    $terms = array_filter(explode(' ', $query), static fn (string $term): bool => mb_strlen($term) >= 2);

    foreach (array_unique($terms) as $term) {
        $safeTerm = e($term);
        $safe = preg_replace('/(' . preg_quote($safeTerm, '/') . ')/iu', '<mark>$1</mark>', $safe) ?? $safe;
    }

    return $safe;
}

function search_event_hash(string $query): string
{
    return hash('sha256', $query);
}

function record_search_event(mysqli $connection, string $query, int $resultCount, string $type = 'search', string $state = 'results'): void
{
    if ($query === '' || !search_query_is_aggregate_safe($query)) {
        return;
    }

    record_search_event_hash($connection, search_event_hash($query), $resultCount, $type, $state);
}

function record_search_event_hash(mysqli $connection, string $queryHash, int $resultCount, string $type = 'search', string $state = 'results'): void
{
    if (preg_match('/^[a-f0-9]{64}$/', $queryHash) !== 1
        || !in_array($type, ['search', 'guide', 'download', 'community', 'article'], true)
        || !in_array($state, ['results', 'zero', 'selection'], true)) {
        return;
    }

    $resultCount = min(max($resultCount, 0), 9999);
    $statement = $connection->prepare(
        'INSERT INTO search_events (event_date, query_hash, result_type, event_state, result_count) VALUES (UTC_DATE(), ?, ?, ?, ?) '
        . 'ON DUPLICATE KEY UPDATE event_count = event_count + 1'
    );
    $statement->bind_param('sssi', $queryHash, $type, $state, $resultCount);
    $statement->execute();
    $statement->close();
}

/** @return list<array{label: string, type: string, url: string}> */
function search_suggestions(mysqli $connection, string $query): array
{
    if (mb_strlen($query) < 2) {
        return [];
    }

    $like = '%' . $query . '%';
    $suggestions = [];
    $statement = $connection->prepare(
        'SELECT guides.title, guides.slug FROM guides JOIN categories ON categories.id = guides.category_id WHERE guides.is_published = 1 AND categories.is_published = 1 AND guides.title LIKE ? ORDER BY guides.title LIMIT 5'
    );
    $statement->bind_param('s', $like);
    $statement->execute();
    $result = $statement->get_result();

    while ($row = $result->fetch_assoc()) {
        $suggestions[] = ['label' => $row['title'], 'type' => 'Guide', 'url' => application_url('guide.php?slug=' . rawurlencode($row['slug']))];
    }
    $statement->close();

    if (count($suggestions) < 8) {
        $statement = $connection->prepare(
            'SELECT name, slug FROM categories WHERE is_published = 1 AND name LIKE ? ORDER BY name LIMIT 4'
        );
        $statement->bind_param('s', $like);
        $statement->execute();
        $result = $statement->get_result();

        while (($row = $result->fetch_assoc()) && count($suggestions) < 8) {
            $suggestions[] = ['label' => $row['name'], 'type' => 'Category', 'url' => application_url('guides.php?category=' . rawurlencode($row['slug']))];
        }
        $statement->close();
    }

    if (count($suggestions) < 8) {
        $statement = $connection->prepare(
            "SELECT knowledge_articles.title, knowledge_articles.slug, knowledge_articles.error_code FROM knowledge_articles JOIN categories ON categories.id = knowledge_articles.category_id WHERE knowledge_articles.publication_state = 'published' AND categories.is_published = 1 AND (knowledge_articles.title LIKE ? OR knowledge_articles.error_code LIKE ?) ORDER BY knowledge_articles.title LIMIT 5"
        );
        $statement->bind_param('ss', $like, $like);
        $statement->execute();
        $result = $statement->get_result();

        while (($row = $result->fetch_assoc()) && count($suggestions) < 8) {
            $suggestions[] = ['label' => $row['error_code'] ?: $row['title'], 'type' => 'Knowledge article', 'url' => application_url('knowledge_article.php?slug=' . rawurlencode($row['slug']))];
        }
        $statement->close();
    }

    if (count($suggestions) < 8) {
        $statement = $connection->prepare(
            'SELECT alias, replacement FROM search_aliases WHERE alias LIKE ? OR replacement LIKE ? ORDER BY alias LIMIT 4'
        );
        $statement->bind_param('ss', $like, $like);
        $statement->execute();
        $result = $statement->get_result();

        while (($row = $result->fetch_assoc()) && count($suggestions) < 8) {
            $suggestions[] = [
                'label' => $row['alias'],
                'type' => 'Suggested search',
                'url' => application_url('search.php?q=' . rawurlencode($row['replacement'])),
            ];
        }
        $statement->close();
    }

    return $suggestions;
}
