<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Guides;

use DomainException;
use mysqli;

final class GuideAdminService
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /**
     * @param array<string, mixed> $input
     * @return array{values: array<string, mixed>, errors: list<string>}
     */
    public function validate(array $input): array
    {
        $errors = [];
        $categoryId = filter_var($input['category'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $title = $this->text($input['title'] ?? null, 150);
        $slug = $this->text($input['slug'] ?? null, 150);
        $description = $this->text($input['description'] ?? '', 5000, true);
        $difficulty = $this->text($input['difficulty'] ?? '', 50, true);
        $estimatedTime = $this->text($input['estimated_time'] ?? '', 50, true);
        $riskLevel = $this->text($input['risk_level'] ?? '', 50, true);
        $platformVersion = $this->text($input['platform_version'] ?? '', 100, true);
        $tools = $this->text($input['required_tools'] ?? '', 2000, true);
        $prerequisites = $this->text($input['prerequisites'] ?? '', 5000, true);
        $backupWarning = $this->text($input['backup_warning'] ?? '', 5000, true);
        $nextActions = $this->text($input['next_actions'] ?? '', 5000, true);
        $videoInput = $this->text($input['video_url'] ?? '', 255, true);
        $videoUrl = \guide_youtube_watch_url($videoInput ?? '');
        $publication = $input['is_published'] ?? null;
        $featured = is_string($input['featured_order'] ?? null) ? trim($input['featured_order']) : $input['featured_order'] ?? null;
        $featuredOrder = $featured === '' || $featured === null ? null : filter_var($featured, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);
        $reviewedAt = $this->date($input['last_reviewed_at'] ?? '');
        $steps = \guide_normalize_steps($input['steps'] ?? []);
        $sources = $this->sources($input['sources'] ?? [], $errors);

        if ($categoryId === 0) {
            $errors[] = 'Choose an existing category.';
        }

        if ($title === null || $title === '') {
            $errors[] = 'Title is required and must be 150 characters or fewer.';
        }

        if ($slug === null || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            $errors[] = 'Slug must use lowercase letters, numbers, and single hyphens.';
        }

        foreach ([$description, $difficulty, $estimatedTime, $riskLevel, $platformVersion, $tools, $prerequisites, $backupWarning, $nextActions] as $value) {
            if ($value === null) {
                $errors[] = 'One or more text fields exceed the allowed length.';
                break;
            }
        }

        if ($videoInput === null || ($videoInput !== '' && $videoUrl === null)) {
            $errors[] = 'Use a valid YouTube URL when adding a video.';
        }

        if (!in_array($publication, ['0', '1', 0, 1], true)) {
            $errors[] = 'Choose a valid publication state.';
        }

        if ($featuredOrder === false) {
            $errors[] = 'Featured order must be empty or a number from 1 to 999.';
        }

        if ($reviewedAt === false) {
            $errors[] = 'Last reviewed date must use YYYY-MM-DD.';
        }

        if ($steps === []) {
            $errors[] = 'Provide at least one valid step.';
        }

        if (in_array($publication, ['1', 1], true)) {
            $requiredMetadata = [
                'platform/version' => $platformVersion,
                'difficulty' => $difficulty,
                'estimated time' => $estimatedTime,
                'risk level' => $riskLevel,
                'required tools' => $tools,
                'prerequisites' => $prerequisites,
                'backup and safety warning' => $backupWarning,
                'last reviewed date' => $reviewedAt,
                'next actions' => $nextActions,
            ];
            $missingMetadata = array_keys(array_filter($requiredMetadata, static fn (mixed $value): bool => $value === ''));

            if ($missingMetadata !== []) {
                $errors[] = 'Published guides require: ' . implode(', ', $missingMetadata) . '.';
            }

            if ($sources === []) {
                $errors[] = 'A published guide requires at least one approved official source.';
            }
        }

        return [
            'values' => [
                'category_id' => $categoryId,
                'title' => $title ?? '',
                'slug' => $slug ?? '',
                'description' => $description ?? '',
                'difficulty' => $difficulty ?? '',
                'estimated_time' => $estimatedTime ?? '',
                'risk_level' => $riskLevel ?? '',
                'platform_version' => $platformVersion ?? '',
                'required_tools' => $tools ?? '',
                'prerequisites' => $prerequisites ?? '',
                'backup_warning' => $backupWarning ?? '',
                'next_actions' => $nextActions ?? '',
                'video_url' => $videoUrl,
                'is_published' => in_array($publication, ['1', 1], true) ? 1 : 0,
                'featured_order' => is_int($featuredOrder) ? $featuredOrder : null,
                'last_reviewed_at' => $reviewedAt === '' ? null : $reviewedAt,
                'steps' => $steps,
                'sources' => $sources,
            ],
            'errors' => array_values(array_unique($errors)),
        ];
    }

    /** @param array<string, mixed> $values */
    public function create(array $values): int
    {
        return \in_transaction($this->connection, function () use ($values): int {
            $this->assertCategory($values['category_id']);
            $statement = $this->connection->prepare(
                'INSERT INTO guides (category_id, title, slug, description, difficulty, estimated_time, risk_level, content, platform_version, required_tools, prerequisites, backup_warning, last_reviewed_at, next_actions, video_url, is_published, featured_order) VALUES (?, ?, ?, ?, ?, ?, ?, \'\', ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->bind_param('isssssssssssssii', $values['category_id'], $values['title'], $values['slug'], $values['description'], $values['difficulty'], $values['estimated_time'], $values['risk_level'], $values['platform_version'], $values['required_tools'], $values['prerequisites'], $values['backup_warning'], $values['last_reviewed_at'], $values['next_actions'], $values['video_url'], $values['is_published'], $values['featured_order']);
            $statement->execute();
            $guideId = $statement->insert_id;
            $statement->close();
            \guide_replace_steps($this->connection, $guideId, $values['steps']);
            \guide_replace_tools($this->connection, $guideId, $values['required_tools']);
            $this->replaceSources($guideId, $values['sources']);
            (new GuideSearchProjection($this->connection))->rebuildGuide($guideId);
            \admin_audit($this->connection, 'guide.created', 'guide', $guideId, ['slug' => $values['slug'], 'is_published' => $values['is_published'], 'source_count' => count($values['sources'])]);

            return $guideId;
        });
    }

    /** @param array<string, mixed> $values */
    public function update(int $id, array $values): bool
    {
        return \in_transaction($this->connection, function () use ($id, $values): bool {
            $lock = $this->connection->prepare('SELECT is_published FROM guides WHERE id = ? FOR UPDATE');
            $lock->bind_param('i', $id);
            $lock->execute();
            $guide = $lock->get_result()->fetch_assoc();
            $lock->close();

            if ($guide === null) {
                return false;
            }

            $this->assertCategory($values['category_id']);
            $statement = $this->connection->prepare('UPDATE guides SET category_id = ?, title = ?, slug = ?, description = ?, difficulty = ?, estimated_time = ?, risk_level = ?, platform_version = ?, required_tools = ?, prerequisites = ?, backup_warning = ?, last_reviewed_at = ?, next_actions = ?, video_url = ?, is_published = ?, featured_order = ? WHERE id = ?');
            $statement->bind_param('isssssssssssssiii', $values['category_id'], $values['title'], $values['slug'], $values['description'], $values['difficulty'], $values['estimated_time'], $values['risk_level'], $values['platform_version'], $values['required_tools'], $values['prerequisites'], $values['backup_warning'], $values['last_reviewed_at'], $values['next_actions'], $values['video_url'], $values['is_published'], $values['featured_order'], $id);
            $statement->execute();
            $statement->close();
            $steps = \guide_sync_steps($this->connection, $id, $values['steps']);
            \guide_replace_tools($this->connection, $id, $values['required_tools']);
            $this->replaceSources($id, $values['sources']);
            (new GuideSearchProjection($this->connection))->rebuildGuide($id);
            \admin_audit($this->connection, 'guide.updated', 'guide', $id, ['slug' => $values['slug'], 'publication_from' => (int) $guide['is_published'], 'publication_to' => $values['is_published'], 'source_count' => count($values['sources']), 'steps_added' => $steps['added'], 'steps_deleted' => $steps['deleted'], 'progress_rows_deleted' => $steps['deleted_progress']]);

            return true;
        });
    }

    /** @return array{status: string, dependencies: array<string, int>} */
    public function delete(int $id): array
    {
        return \in_transaction($this->connection, function () use ($id): array {
            $lock = $this->connection->prepare('SELECT slug, is_published FROM guides WHERE id = ? FOR UPDATE');
            $lock->bind_param('i', $id);
            $lock->execute();
            $guide = $lock->get_result()->fetch_assoc();
            $lock->close();

            if ($guide === null) {
                return ['status' => 'missing', 'dependencies' => []];
            }

            $dependencies = $this->dependencies($id);

            if (array_sum($dependencies) > 0) {
                return ['status' => 'blocked', 'dependencies' => $dependencies];
            }

            foreach (['guide_sources', 'guide_tools'] as $table) {
                $statement = $this->connection->prepare("DELETE FROM {$table} WHERE guide_id = ?");
                $statement->bind_param('i', $id);
                $statement->execute();
                $statement->close();
            }

            $statement = $this->connection->prepare('DELETE FROM guides WHERE id = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            $statement->close();
            \admin_audit($this->connection, 'guide.deleted', 'guide', $id, ['slug' => $guide['slug'], 'was_published' => (int) $guide['is_published']]);

            return ['status' => 'deleted', 'dependencies' => []];
        });
    }

    private function assertCategory(int $categoryId): void
    {
        if (!\guide_category_exists($this->connection, $categoryId)) {
            throw new DomainException('The selected category no longer exists.');
        }
    }

    /** @param mixed $input @param list<string> $errors @return list<array{title: string, official_url: string, trusted_source_domain_id: int, source_last_reviewed_at: string|null}> */
    private function sources(mixed $input, array &$errors): array
    {
        if (!is_array($input)) {
            $errors[] = 'Sources must be submitted as a list.';
            return [];
        }

        $sources = [];
        $seen = [];

        foreach ($input as $source) {
            if (!is_array($source)) {
                $errors[] = 'Each source must include a title and URL.';
                continue;
            }

            $title = $this->text($source['title'] ?? '', 180, true);
            $url = $this->text($source['official_url'] ?? '', 255, true);

            if ($title === '' && $url === '') {
                continue;
            }

            $approved = $url === null ? null : (new TrustedSourcePolicy($this->connection))->approvedSource($url);

            if ($title === null || $title === '' || $approved === null) {
                $errors[] = 'Each source requires a title and an approved HTTPS URL.';
                continue;
            }

            if (isset($seen[$url])) {
                $errors[] = 'Source URLs must be unique.';
                continue;
            }

            $seen[$url] = true;
            $sources[] = [
                'title' => $title,
                'official_url' => $approved['official_url'],
                'trusted_source_domain_id' => $approved['trusted_source_domain_id'],
                'source_last_reviewed_at' => $approved['source_last_reviewed_at'],
            ];
        }

        return $sources;
    }

    /** @param list<array{title: string, official_url: string, trusted_source_domain_id: int, source_last_reviewed_at: string|null}> $sources */
    private function replaceSources(int $guideId, array $sources): void
    {
        $delete = $this->connection->prepare('DELETE FROM guide_sources WHERE guide_id = ?');
        $delete->bind_param('i', $guideId);
        $delete->execute();
        $delete->close();
        $insert = $this->connection->prepare('INSERT INTO guide_sources (guide_id, title, official_url, trusted_source_domain_id, source_last_reviewed_at, sort_order) VALUES (?, ?, ?, ?, ?, ?)');

        foreach ($sources as $position => $source) {
            $order = $position + 1;
            $insert->bind_param('issisi', $guideId, $source['title'], $source['official_url'], $source['trusted_source_domain_id'], $source['source_last_reviewed_at'], $order);
            $insert->execute();
        }

        $insert->close();
    }

    /** @return array<string, int> */
    private function dependencies(int $id): array
    {
        $statement = $this->connection->prepare('SELECT (SELECT COUNT(*) FROM user_progress JOIN guide_steps ON guide_steps.id = user_progress.guide_step_id WHERE guide_steps.guide_id = ?) AS progress, (SELECT COUNT(*) FROM favorites WHERE guide_id = ?) AS favorites, (SELECT COUNT(*) FROM guide_ratings WHERE guide_id = ?) AS ratings, (SELECT COUNT(*) FROM knowledge_relations WHERE guide_id = ?) AS relations');
        $statement->bind_param('iiii', $id, $id, $id, $id);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc() ?: [];
        $statement->close();

        return ['saved progress' => (int) ($row['progress'] ?? 0), 'favorites' => (int) ($row['favorites'] ?? 0), 'ratings' => (int) ($row['ratings'] ?? 0), 'knowledge relations' => (int) ($row['relations'] ?? 0)];
    }

    private function text(mixed $value, int $maximum, bool $allowEmpty = false): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return ($allowEmpty || $value !== '') && mb_strlen($value) <= $maximum ? $value : null;
    }

    private function date(mixed $value): string|false
    {
        if ($value === null || $value === '') {
            return '';
        }

        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 && strtotime($value) !== false ? $value : false;
    }
}
