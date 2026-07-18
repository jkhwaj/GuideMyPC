<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Dashboard;

use GuideMyPC\Features\Downloads\DownloadPolicy;
use mysqli;

final class DashboardReadModel
{
    public function __construct(
        private readonly mysqli $connection,
        private readonly DownloadPolicy $downloadPolicy = new DownloadPolicy()
    ) {
    }

    /**
     * @return array{kind: string, metrics: list<array{label: string, value: int}>, activity: list<array{label: string, detail: string, date: string}>}
     */
    public function personal(int $userId): array
    {
        $statement = $this->connection->prepare(
            'SELECT '
            . '(SELECT COUNT(DISTINCT guide_steps.guide_id) FROM user_progress JOIN guide_steps ON user_progress.guide_step_id = guide_steps.id WHERE user_progress.user_id = ?) AS started_guides, '
            . '(SELECT COUNT(*) FROM favorites WHERE user_id = ?) AS favorites, '
            . '(SELECT COUNT(*) FROM guide_ratings WHERE user_id = ?) AS ratings'
        );
        $statement->bind_param('iii', $userId, $userId, $userId);
        $statement->execute();
        $summary = $statement->get_result()->fetch_assoc() ?: [];
        $statement->close();

        $completionStatement = $this->connection->prepare(
            'SELECT COUNT(*) AS total FROM ('
            . 'SELECT guide_steps.guide_id FROM guide_steps '
            . 'LEFT JOIN user_progress ON user_progress.guide_step_id = guide_steps.id AND user_progress.user_id = ? '
            . 'GROUP BY guide_steps.guide_id '
            . 'HAVING COUNT(guide_steps.id) > 0 AND COUNT(user_progress.id) = COUNT(guide_steps.id)'
            . ') AS completed_guides'
        );
        $completionStatement->bind_param('i', $userId);
        $completionStatement->execute();
        $completed = (int) ($completionStatement->get_result()->fetch_assoc()['total'] ?? 0);
        $completionStatement->close();

        $activityStatement = $this->connection->prepare(
            'SELECT activity_type, subject_value, created_at FROM user_activity WHERE user_id = ? ORDER BY created_at DESC LIMIT 6'
        );
        $activityStatement->bind_param('i', $userId);
        $activityStatement->execute();
        $activityResult = $activityStatement->get_result();
        $activity = [];

        while ($row = $activityResult->fetch_assoc()) {
            $activity[] = [
                'label' => $this->activityLabel((string) $row['activity_type']),
                'detail' => (string) $row['subject_value'],
                'date' => (string) $row['created_at'],
            ];
        }

        $activityStatement->close();

        return [
            'kind' => 'personal',
            'metrics' => [
                ['label' => 'Guides started', 'value' => (int) ($summary['started_guides'] ?? 0)],
                ['label' => 'Guides completed', 'value' => $completed],
                ['label' => 'Favorites', 'value' => (int) ($summary['favorites'] ?? 0)],
                ['label' => 'Ratings submitted', 'value' => (int) ($summary['ratings'] ?? 0)],
            ],
            'activity' => $activity,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function operational(bool $includeAdministrativeDetails): array
    {
        $currentMonth = new \DateTimeImmutable('first day of this month 00:00:00');
        $monthStart = $currentMonth->format('Y-m-d H:i:s');
        $nextMonth = $currentMonth->modify('+1 month')->format('Y-m-d H:i:s');
        $completionStatement = $this->connection->prepare(
            'SELECT COUNT(*) AS total FROM ('
            . 'SELECT user_progress.user_id, guide_steps.guide_id '
            . 'FROM user_progress JOIN guide_steps ON user_progress.guide_step_id = guide_steps.id '
            . 'GROUP BY user_progress.user_id, guide_steps.guide_id '
            . 'HAVING COUNT(DISTINCT user_progress.guide_step_id) = (SELECT COUNT(*) FROM guide_steps AS expected_steps WHERE expected_steps.guide_id = guide_steps.guide_id) '
            . 'AND MAX(user_progress.completed_at) >= ? AND MAX(user_progress.completed_at) < ?'
            . ') AS monthly_completions'
        );
        $completionStatement->bind_param('ss', $monthStart, $nextMonth);
        $completionStatement->execute();
        $monthlyCompletions = (int) ($completionStatement->get_result()->fetch_assoc()['total'] ?? 0);
        $completionStatement->close();

        $metrics = [
            ['label' => 'Published guides', 'value' => $this->count("SELECT COUNT(*) AS total FROM guides WHERE is_published = 1")],
            ['label' => 'Registered users', 'value' => $this->count("SELECT COUNT(*) AS total FROM users WHERE status = 'active' AND deleted_at IS NULL")],
            ['label' => 'Completions this month', 'value' => $monthlyCompletions],
            ['label' => 'Published knowledge', 'value' => $this->count("SELECT COUNT(*) AS total FROM knowledge_articles WHERE publication_state = 'published'")],
            ['label' => 'Approved downloads', 'value' => $this->approvedDownloadCount()],
            ['label' => 'Published community posts', 'value' => $this->count('SELECT COUNT(*) AS total FROM community_posts WHERE is_published = 1')],
        ];

        $result = [
            'kind' => 'operational',
            'metrics' => $metrics,
            'categoryChart' => $this->contentByCategory(),
            'registrationChart' => $this->registrationsByMonth(),
            'recentGuides' => $this->rows(
                'SELECT title, slug, created_at FROM guides WHERE is_published = 1 ORDER BY created_at DESC LIMIT 5'
            ),
            'recentPosts' => $this->rows(
                'SELECT community_posts.title, community_posts.created_at, users.full_name '
                . 'FROM community_posts JOIN users ON community_posts.user_id = users.id '
                . 'WHERE community_posts.is_published = 1 ORDER BY community_posts.created_at DESC LIMIT 5'
            ),
            'recentUsers' => [],
            'auditEvents' => [],
        ];

        if ($includeAdministrativeDetails) {
            $result['recentUsers'] = $this->rows(
                "SELECT full_name, role, created_at FROM users WHERE status = 'active' AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 5"
            );
            $result['auditEvents'] = $this->rows(
                'SELECT action, target_type, target_id, created_at FROM admin_audit_events ORDER BY created_at DESC LIMIT 5'
            );
        }

        return $result;
    }

    private function count(string $sql): int
    {
        return (int) ($this->connection->query($sql)->fetch_assoc()['total'] ?? 0);
    }

    private function approvedDownloadCount(): int
    {
        $result = $this->connection->query(
            "SELECT is_published, review_state, official_url FROM downloads WHERE is_published = 1 AND review_state = 'approved'"
        );
        $total = 0;

        while ($download = $result->fetch_assoc()) {
            if ($this->downloadPolicy->isPublic($download)) {
                $total++;
            }
        }

        return $total;
    }

    /**
     * @return array{labels: list<string>, guides: list<int>, articles: list<int>}
     */
    private function contentByCategory(): array
    {
        $result = $this->connection->query(
            'SELECT category_content.name, category_content.guide_count, category_content.article_count FROM ('
            . "SELECT categories.name, "
            . "(SELECT COUNT(*) FROM guides WHERE guides.category_id = categories.id AND guides.is_published = 1) AS guide_count, "
            . "(SELECT COUNT(*) FROM knowledge_articles WHERE knowledge_articles.category_id = categories.id AND knowledge_articles.publication_state = 'published') AS article_count "
            . 'FROM categories WHERE categories.is_published = 1'
            . ') AS category_content ORDER BY (category_content.guide_count + category_content.article_count) DESC, category_content.name ASC LIMIT 12'
        );
        $chart = ['labels' => [], 'guides' => [], 'articles' => []];

        while ($row = $result->fetch_assoc()) {
            $chart['labels'][] = (string) $row['name'];
            $chart['guides'][] = (int) $row['guide_count'];
            $chart['articles'][] = (int) $row['article_count'];
        }

        return $chart;
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function registrationsByMonth(): array
    {
        $months = [];
        $currentMonth = new \DateTimeImmutable('first day of this month 00:00:00');

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = $currentMonth->modify('-' . $offset . ' months');
            $months[$month->format('Y-m')] = 0;
        }

        $start = array_key_first($months) . '-01 00:00:00';
        $statement = $this->connection->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, COUNT(*) AS total "
            . 'FROM users WHERE created_at >= ? GROUP BY month_key ORDER BY month_key ASC'
        );
        $statement->bind_param('s', $start);
        $statement->execute();
        $result = $statement->get_result();

        while ($row = $result->fetch_assoc()) {
            $key = (string) $row['month_key'];

            if (array_key_exists($key, $months)) {
                $months[$key] = (int) $row['total'];
            }
        }

        $statement->close();

        return [
            'labels' => array_map(
                static fn (string $month): string => date('M Y', strtotime($month . '-01')),
                array_keys($months)
            ),
            'values' => array_values($months),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rows(string $sql): array
    {
        $result = $this->connection->query($sql);
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function activityLabel(string $activityType): string
    {
        return match ($activityType) {
            'guide_view' => 'Viewed guide',
            'search' => 'Searched support',
            'diagnostic' => 'Ran diagnostic',
            default => 'Account activity',
        };
    }
}
