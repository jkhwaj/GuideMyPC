<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Accounts;

use mysqli;

final class UserAdminService
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM users WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $user = $statement->get_result()->fetch_assoc();
        $statement->close();

        return is_array($user) ? $user : null;
    }

    public function emailInUse(string $email, int $exceptId): bool
    {
        $statement = $this->connection->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $statement->bind_param('si', $email, $exceptId);
        $statement->execute();
        $inUse = $statement->get_result()->fetch_assoc() !== null;
        $statement->close();

        return $inUse;
    }

    public function update(int $id, string $name, string $email, string $role): void
    {
        \in_transaction($this->connection, function () use ($id, $name, $email, $role): void {
            $statement = $this->connection->prepare('UPDATE users SET full_name = ?, email = ?, role = ? WHERE id = ?');
            $statement->bind_param('sssi', $name, $email, $role, $id);
            $statement->execute();
            $statement->close();

            \admin_audit($this->connection, 'user.updated', 'user', $id, ['role' => $role]);
        });
    }

    public function delete(int $id): bool
    {
        return \in_transaction($this->connection, function () use ($id): bool {
            $statement = $this->connection->prepare('DELETE FROM users WHERE id = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            $deleted = $statement->affected_rows > 0;
            $statement->close();

            if ($deleted) {
                \admin_audit($this->connection, 'user.deleted', 'user', $id);
            }

            return $deleted;
        });
    }
}
