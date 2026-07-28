<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Downloads;

use DomainException;
use mysqli;

final class DownloadAdminService
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    public function create(string $name, string $description, string $officialUrl, string $category, string $reviewState, int $isPublished): int
    {
        return $this->withUniqueLock(function () use ($name, $description, $officialUrl, $category, $reviewState, $isPublished): int {
            return \in_transaction($this->connection, function () use ($name, $description, $officialUrl, $category, $reviewState, $isPublished): int {
                $this->assertUnique($name, $officialUrl, null);
            $statement = $this->connection->prepare(
                'INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $statement->bind_param('sssssi', $name, $description, $officialUrl, $category, $reviewState, $isPublished);
            $statement->execute();
            $downloadId = $this->connection->insert_id;
            $statement->close();

            \admin_audit($this->connection, 'download.created', 'download', $downloadId, [
                'name' => $name,
                'review_state' => $reviewState,
                'is_published' => $isPublished,
            ]);

            return $downloadId;
            });
        });
    }

    public function update(int $id, string $name, string $description, string $officialUrl, string $category, string $reviewState, int $isPublished): void
    {
        $this->withUniqueLock(function () use ($id, $name, $description, $officialUrl, $category, $reviewState, $isPublished): void {
            \in_transaction($this->connection, function () use ($id, $name, $description, $officialUrl, $category, $reviewState, $isPublished): void {
                $this->assertUnique($name, $officialUrl, $id);
            $statement = $this->connection->prepare(
                'UPDATE downloads SET name = ?, description = ?, official_url = ?, category = ?, review_state = ?, is_published = ? WHERE id = ?'
            );
            $statement->bind_param('sssssii', $name, $description, $officialUrl, $category, $reviewState, $isPublished, $id);
            $statement->execute();
            $updated = $statement->affected_rows > 0;
            $statement->close();

            if ($updated) {
                \admin_audit($this->connection, 'download.updated', 'download', $id, [
                    'name' => $name,
                    'review_state' => $reviewState,
                    'is_published' => $isPublished,
                ]);
            }
            });
        });
    }

    public function delete(int $id): void
    {
        \in_transaction($this->connection, function () use ($id): void {
            $statement = $this->connection->prepare('DELETE FROM downloads WHERE id = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            $deleted = $statement->affected_rows > 0;
            $statement->close();

            if ($deleted) {
                \admin_audit($this->connection, 'download.deleted', 'download', $id);
            }
        });
    }

    private function assertUnique(string $name, string $officialUrl, ?int $currentId): void
    {
        $policy = new DownloadPolicy();
        $normalizedName = $policy->normalizedName($name);
        $normalizedUrl = $policy->normalizedUrl($officialUrl);

        if ($normalizedName === null || $normalizedUrl === null) {
            return;
        }

        $statement = $this->connection->prepare('SELECT id, name, official_url FROM downloads');
        $statement->execute();
        $result = $statement->get_result();

        while ($download = $result->fetch_assoc()) {
            if ($currentId !== null && (int) $download['id'] === $currentId) {
                continue;
            }

            if ($policy->normalizedName($download['name']) === $normalizedName) {
                $statement->close();
                throw new DomainException('A download with this product name already exists.');
            }

            if ($policy->normalizedUrl($download['official_url']) === $normalizedUrl) {
                $statement->close();
                throw new DomainException('A download with this official URL already exists.');
            }
        }

        $statement->close();
    }

    private function withUniqueLock(callable $operation): mixed
    {
        $lockName = 'guidemypc_download_catalog_admin';
        $statement = $this->connection->prepare('SELECT GET_LOCK(?, 5) AS locked');
        $statement->bind_param('s', $lockName);
        $statement->execute();
        $locked = (int) ($statement->get_result()->fetch_assoc()['locked'] ?? 0) === 1;
        $statement->close();

        if (!$locked) {
            throw new DomainException('Download administration is busy. Please try again.');
        }

        try {
            return $operation();
        } finally {
            $release = $this->connection->prepare('SELECT RELEASE_LOCK(?)');
            $release->bind_param('s', $lockName);
            $release->execute();
            $release->close();
        }
    }
}
