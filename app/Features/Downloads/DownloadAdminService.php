<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Downloads;

use mysqli;

final class DownloadAdminService
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    public function create(string $name, string $description, string $officialUrl, string $category, string $reviewState, int $isPublished): int
    {
        return \in_transaction($this->connection, function () use ($name, $description, $officialUrl, $category, $reviewState, $isPublished): int {
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
    }

    public function update(int $id, string $name, string $description, string $officialUrl, string $category, string $reviewState, int $isPublished): void
    {
        \in_transaction($this->connection, function () use ($id, $name, $description, $officialUrl, $category, $reviewState, $isPublished): void {
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
}
