<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Categories;

use mysqli;

final class CategoryAdminRepository
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /**
     * @param array<string, mixed> $input
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int, query: array{q: string, status: string, sort: string, direction: string, per_page: int}}
     */
    public function paginate(array $input): array
    {
        $query = is_string($input['q'] ?? null) ? trim($input['q']) : '';
        $query = mb_substr($query, 0, 100);
        $status = in_array($input['status'] ?? null, ['published', 'unpublished'], true)
            ? (string) $input['status']
            : 'all';
        $sort = in_array($input['sort'] ?? null, ['name', 'slug', 'featured', 'updated'], true)
            ? (string) $input['sort']
            : 'updated';
        $direction = strtolower(is_string($input['direction'] ?? null) ? $input['direction'] : '') === 'asc'
            ? 'asc'
            : 'desc';
        $requestedPageSize = filter_var($input['per_page'] ?? null, FILTER_VALIDATE_INT);
        $perPage = in_array($requestedPageSize, [10, 25, 50], true) ? $requestedPageSize : 25;
        $page = pagination_values($input['page'] ?? null, $perPage)['page'];
        $statusValue = $status === 'all' ? -1 : ($status === 'published' ? 1 : 0);
        $pattern = '%' . $query . '%';

        $countStatement = $this->connection->prepare(
            "SELECT COUNT(*) AS total FROM categories "
            . "WHERE (? = '' OR name LIKE ? OR slug LIKE ? OR description LIKE ?) "
            . 'AND (? = -1 OR is_published = ?)'
        );
        $countStatement->bind_param('ssssii', $query, $pattern, $pattern, $pattern, $statusValue, $statusValue);
        $countStatement->execute();
        $total = (int) ($countStatement->get_result()->fetch_assoc()['total'] ?? 0);
        $countStatement->close();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $sortColumns = [
            'name' => 'categories.name',
            'slug' => 'categories.slug',
            'featured' => 'categories.featured_order',
            'updated' => 'COALESCE(categories.updated_at, categories.created_at)',
        ];
        $orderBy = $sortColumns[$sort] . ' ' . strtoupper($direction);

        if ($sort === 'featured') {
            $orderBy = 'categories.featured_order IS NULL ASC, ' . $orderBy;
        }

        $statement = $this->connection->prepare(
            "SELECT categories.*, "
            . '(SELECT COUNT(*) FROM guides WHERE guides.category_id = categories.id) AS guide_count, '
            . '(SELECT COUNT(*) FROM knowledge_articles WHERE knowledge_articles.category_id = categories.id) AS knowledge_count '
            . "FROM categories WHERE (? = '' OR categories.name LIKE ? OR categories.slug LIKE ? OR categories.description LIKE ?) "
            . "AND (? = -1 OR categories.is_published = ?) ORDER BY {$orderBy}, categories.id DESC LIMIT ? OFFSET ?"
        );
        $statement->bind_param('ssssiiii', $query, $pattern, $pattern, $pattern, $statusValue, $statusValue, $perPage, $offset);
        $statement->execute();
        $result = $statement->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $statement->close();

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'query' => [
                'q' => $query,
                'status' => $status,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => $perPage,
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
        $statement->bind_param('i', $id);
        $statement->execute();
        $category = $statement->get_result()->fetch_assoc() ?: null;
        $statement->close();

        return $category;
    }

    public function slugExists(string $slug, ?int $excludingId = null): bool
    {
        $excludedId = $excludingId ?? 0;
        $statement = $this->connection->prepare('SELECT id FROM categories WHERE slug = ? AND id <> ? LIMIT 1');
        $statement->bind_param('si', $slug, $excludedId);
        $statement->execute();
        $exists = $statement->get_result()->fetch_assoc() !== null;
        $statement->close();

        return $exists;
    }
}
