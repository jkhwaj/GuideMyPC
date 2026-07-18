<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Categories;

use mysqli;

final class CategoryAdminService
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /**
     * @param array<string, mixed> $input
     * @return array{values: array{name: string, slug: string, description: string, icon: string, is_published: int, featured_order: int|null}, errors: list<string>}
     */
    public function validate(array $input): array
    {
        $errors = [];
        $name = $this->boundedString($input['name'] ?? null, 100);
        $slug = $this->boundedString($input['slug'] ?? null, 100);
        $description = $this->boundedString($input['description'] ?? '', 5000, true);
        $icon = $this->boundedString($input['icon'] ?? '', 50, true);
        $publication = $input['is_published'] ?? null;
        $featuredInput = is_string($input['featured_order'] ?? null) ? trim($input['featured_order']) : $input['featured_order'] ?? null;
        $featuredOrder = $featuredInput === '' || $featuredInput === null
            ? null
            : filter_var($featuredInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);

        if ($name === null || mb_strlen($name) < 2) {
            $errors[] = 'Name must be between 2 and 100 characters.';
        }

        if ($slug === null || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            $errors[] = 'Slug must use lowercase letters, numbers, and single hyphens.';
        }

        if ($description === null) {
            $errors[] = 'Description must be 5,000 characters or fewer.';
        }

        if ($icon === null || ($icon !== '' && preg_match('/^[A-Za-z0-9 _-]+$/', $icon) !== 1)) {
            $errors[] = 'Icon must be 50 characters or fewer and contain only letters, numbers, spaces, underscores, or hyphens.';
        }

        if (!in_array($publication, ['0', '1', 0, 1], true)) {
            $errors[] = 'Choose a valid publication state.';
        }

        if ($featuredOrder === false) {
            $errors[] = 'Featured order must be empty or a number from 1 to 999.';
        }

        return [
            'values' => [
                'name' => $name ?? '',
                'slug' => $slug ?? '',
                'description' => $description ?? '',
                'icon' => $icon ?? '',
                'is_published' => in_array($publication, ['1', 1], true) ? 1 : 0,
                'featured_order' => is_int($featuredOrder) ? $featuredOrder : null,
            ],
            'errors' => $errors,
        ];
    }

    /** @param array{name: string, slug: string, description: string, icon: string, is_published: int, featured_order: int|null} $values */
    public function create(array $values): int
    {
        return \in_transaction($this->connection, function () use ($values): int {
            $statement = $this->connection->prepare(
                'INSERT INTO categories (name, slug, description, icon, is_published, featured_order) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $statement->bind_param(
                'ssssii',
                $values['name'],
                $values['slug'],
                $values['description'],
                $values['icon'],
                $values['is_published'],
                $values['featured_order']
            );
            $statement->execute();
            $categoryId = $statement->insert_id;
            $statement->close();
            \admin_audit($this->connection, 'category.created', 'category', $categoryId, [
                'slug' => $values['slug'],
                'is_published' => $values['is_published'],
                'featured_order' => $values['featured_order'],
            ]);

            return $categoryId;
        });
    }

    /** @param array{name: string, slug: string, description: string, icon: string, is_published: int, featured_order: int|null} $values */
    public function update(int $id, array $values): bool
    {
        return \in_transaction($this->connection, function () use ($id, $values): bool {
            $lock = $this->connection->prepare('SELECT is_published FROM categories WHERE id = ? FOR UPDATE');
            $lock->bind_param('i', $id);
            $lock->execute();
            $existing = $lock->get_result()->fetch_assoc();
            $lock->close();

            if ($existing === null) {
                return false;
            }

            $statement = $this->connection->prepare(
                'UPDATE categories SET name = ?, slug = ?, description = ?, icon = ?, is_published = ?, featured_order = ? WHERE id = ?'
            );
            $statement->bind_param(
                'ssssiii',
                $values['name'],
                $values['slug'],
                $values['description'],
                $values['icon'],
                $values['is_published'],
                $values['featured_order'],
                $id
            );
            $statement->execute();
            $statement->close();
            \admin_audit($this->connection, 'category.updated', 'category', $id, [
                'slug' => $values['slug'],
                'publication_from' => (int) $existing['is_published'],
                'publication_to' => $values['is_published'],
                'featured_order' => $values['featured_order'],
            ]);

            return true;
        });
    }

    /**
     * @return array{status: string, dependencies: array<string, int>}
     */
    public function delete(int $id): array
    {
        return \in_transaction($this->connection, function () use ($id): array {
            $lock = $this->connection->prepare('SELECT slug, is_published FROM categories WHERE id = ? FOR UPDATE');
            $lock->bind_param('i', $id);
            $lock->execute();
            $category = $lock->get_result()->fetch_assoc();
            $lock->close();

            if ($category === null) {
                return ['status' => 'missing', 'dependencies' => []];
            }

            $dependencies = $this->dependencyCounts($id);

            if (array_sum($dependencies) > 0) {
                return ['status' => 'blocked', 'dependencies' => $dependencies];
            }

            $statement = $this->connection->prepare('DELETE FROM categories WHERE id = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            $statement->close();
            \admin_audit($this->connection, 'category.deleted', 'category', $id, [
                'slug' => $category['slug'],
                'was_published' => (int) $category['is_published'],
            ]);

            return ['status' => 'deleted', 'dependencies' => []];
        });
    }

    /** @return array<string, int> */
    private function dependencyCounts(int $id): array
    {
        $statement = $this->connection->prepare(
            'SELECT '
            . '(SELECT COUNT(*) FROM guides WHERE category_id = ?) AS guides, '
            . '(SELECT COUNT(*) FROM knowledge_articles WHERE category_id = ?) AS knowledge, '
            . '(SELECT COUNT(*) FROM diagnostic_flows WHERE category_id = ?) AS diagnostics, '
            . '(SELECT COUNT(*) FROM maintenance_recommendations WHERE category_id = ?) AS maintenance, '
            . '(SELECT COUNT(*) FROM community_questions WHERE category_id = ?) AS community'
        );
        $statement->bind_param('iiiii', $id, $id, $id, $id, $id);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc() ?: [];
        $statement->close();

        return [
            'guides' => (int) ($row['guides'] ?? 0),
            'knowledge articles' => (int) ($row['knowledge'] ?? 0),
            'diagnostic flows' => (int) ($row['diagnostics'] ?? 0),
            'maintenance recommendations' => (int) ($row['maintenance'] ?? 0),
            'community questions' => (int) ($row['community'] ?? 0),
        ];
    }

    private function boundedString(mixed $value, int $maximum, bool $allowEmpty = false): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ((!$allowEmpty && $value === '') || mb_strlen($value) > $maximum) {
            return null;
        }

        return $value;
    }
}
