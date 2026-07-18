<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Guides;

use mysqli;

final class GuideAdminRepository
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /**
     * @param array<string, mixed> $input
     * @return array{rows: list<array<string, mixed>>, categories: list<array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int, query: array{q: string, status: string, category: int, sort: string, direction: string, per_page: int}}
     */
    public function paginate(array $input): array
    {
        $q = is_string($input['q'] ?? null) ? mb_substr(trim($input['q']), 0, 100) : '';
        $status = in_array($input['status'] ?? null, ['published', 'unpublished'], true) ? $input['status'] : 'all';
        $category = filter_var($input['category'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $sort = in_array($input['sort'] ?? null, ['updated', 'title', 'featured', 'reviewed'], true) ? $input['sort'] : 'updated';
        $direction = strtolower(is_string($input['direction'] ?? null) ? $input['direction'] : '') === 'asc' ? 'asc' : 'desc';
        $requestedPerPage = filter_var($input['per_page'] ?? null, FILTER_VALIDATE_INT);
        $perPage = in_array($requestedPerPage, [10, 25, 50], true) ? $requestedPerPage : 25;
        $page = pagination_values($input['page'] ?? null, $perPage)['page'];
        $statusValue = $status === 'all' ? -1 : ($status === 'published' ? 1 : 0);
        $pattern = '%' . $q . '%';
        $where = "(? = '' OR guides.title LIKE ? OR guides.slug LIKE ? OR guides.description LIKE ?) AND (? = -1 OR guides.is_published = ?) AND (? = 0 OR guides.category_id = ?)";

        $count = $this->connection->prepare("SELECT COUNT(*) AS total FROM guides WHERE {$where}");
        $count->bind_param('ssssiiii', $q, $pattern, $pattern, $pattern, $statusValue, $statusValue, $category, $category);
        $count->execute();
        $total = (int) ($count->get_result()->fetch_assoc()['total'] ?? 0);
        $count->close();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $columns = [
            'updated' => 'COALESCE(guides.updated_at, guides.created_at)',
            'title' => 'guides.title',
            'featured' => 'guides.featured_order',
            'reviewed' => 'guides.last_reviewed_at',
        ];
        $order = $columns[$sort] . ' ' . strtoupper($direction);

        if ($sort === 'featured') {
            $order = 'guides.featured_order IS NULL ASC, ' . $order;
        }

        $statement = $this->connection->prepare(
            'SELECT guides.*, categories.name AS category_name, '
            . '(SELECT COUNT(*) FROM guide_steps WHERE guide_steps.guide_id = guides.id) AS step_count, '
            . '(SELECT COUNT(*) FROM user_progress JOIN guide_steps ON guide_steps.id = user_progress.guide_step_id WHERE guide_steps.guide_id = guides.id) AS progress_count '
            . "FROM guides LEFT JOIN categories ON categories.id = guides.category_id WHERE {$where} ORDER BY {$order}, guides.id DESC LIMIT ? OFFSET ?"
        );
        $statement->bind_param('ssssiiiiii', $q, $pattern, $pattern, $pattern, $statusValue, $statusValue, $category, $category, $perPage, $offset);
        $statement->execute();
        $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $statement->close();
        $categories = $this->connection->query('SELECT id, name, is_published FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);

        return [
            'rows' => $rows,
            'categories' => $categories,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'query' => ['q' => $q, 'status' => $status, 'category' => $category, 'sort' => $sort, 'direction' => $direction, 'per_page' => $perPage],
        ];
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM guides WHERE id = ? LIMIT 1');
        $statement->bind_param('i', $id);
        $statement->execute();
        $guide = $statement->get_result()->fetch_assoc() ?: null;
        $statement->close();

        return $guide;
    }

    /** @return list<array<string, mixed>> */
    public function sources(int $guideId): array
    {
        $statement = $this->connection->prepare('SELECT title, official_url FROM guide_sources WHERE guide_id = ? ORDER BY sort_order, id');
        $statement->bind_param('i', $guideId);
        $statement->execute();
        $sources = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $statement->close();

        return $sources;
    }

    public function slugExists(string $slug, ?int $excludingId = null): bool
    {
        $id = $excludingId ?? 0;
        $statement = $this->connection->prepare('SELECT id FROM guides WHERE slug = ? AND id <> ? LIMIT 1');
        $statement->bind_param('si', $slug, $id);
        $statement->execute();
        $exists = $statement->get_result()->fetch_assoc() !== null;
        $statement->close();

        return $exists;
    }
}
