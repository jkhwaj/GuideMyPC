<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Guides;

use mysqli;

final class GuideRepository
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /** @return array<string, mixed>|null */
    public function publishedCategory(string $slug): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM categories WHERE slug = ? AND is_published = 1');
        $statement->bind_param('s', $slug);
        $statement->execute();
        $category = $statement->get_result()->fetch_assoc();
        $statement->close();

        return is_array($category) ? $category : null;
    }

    /**
     * @param array{page: int, per_page: int, offset: int} $pagination
     * @return array{guides: list<array<string, mixed>>, total: int, pagination: array{page: int, per_page: int, offset: int}}
     */
    public function publishedGuides(?int $categoryId, string $search, array $pagination): array
    {
        $where = ['guides.is_published = 1', 'categories.is_published = 1'];
        $types = '';
        $values = [];

        if ($categoryId !== null) {
            $where[] = 'guides.category_id = ?';
            $types .= 'i';
            $values[] = $categoryId;
        }

        if ($search !== '') {
            $where[] = '(guides.title LIKE ? OR guides.description LIKE ? OR guide_search_documents.search_text LIKE ?)';
            $types .= 'sss';
            $searchTerm = '%' . $search . '%';
            $values[] = $searchTerm;
            $values[] = $searchTerm;
            $values[] = $searchTerm;
        }

        $countStatement = $this->connection->prepare(
            'SELECT COUNT(*) AS total FROM guides JOIN categories ON guides.category_id = categories.id LEFT JOIN guide_search_documents ON guide_search_documents.guide_id = guides.id WHERE ' . implode(' AND ', $where)
        );

        if ($types !== '') {
            $countStatement->bind_param($types, ...$values);
        }

        $countStatement->execute();
        $total = (int) ($countStatement->get_result()->fetch_assoc()['total'] ?? 0);
        $countStatement->close();

        $totalPages = max(1, (int) ceil($total / $pagination['per_page']));
        $pagination['page'] = min($pagination['page'], $totalPages);
        $pagination['offset'] = ($pagination['page'] - 1) * $pagination['per_page'];

        $statement = $this->connection->prepare(
            'SELECT guides.*, categories.name AS category_name, categories.slug AS category_slug, '
            . 'ROUND(AVG(guide_ratings.rating), 1) AS average_rating, COUNT(guide_ratings.id) AS total_ratings '
            . 'FROM guides JOIN categories ON guides.category_id = categories.id '
            . 'LEFT JOIN guide_search_documents ON guide_search_documents.guide_id = guides.id '
            . 'LEFT JOIN guide_ratings ON guides.id = guide_ratings.guide_id '
            . 'WHERE ' . implode(' AND ', $where) . ' '
            . 'GROUP BY guides.id, categories.name, categories.slug '
            . 'ORDER BY guides.featured_order IS NULL, guides.featured_order ASC, guides.created_at DESC LIMIT ? OFFSET ?'
        );
        $queryTypes = $types . 'ii';
        $queryValues = [...$values, $pagination['per_page'], $pagination['offset']];
        $statement->bind_param($queryTypes, ...$queryValues);
        $statement->execute();
        $guides = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $statement->close();

        return ['guides' => $guides, 'total' => $total, 'pagination' => $pagination];
    }
}
