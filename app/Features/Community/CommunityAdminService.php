<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Community;

use mysqli;

final class CommunityAdminService
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    public function deletePost(int $id): bool
    {
        return \in_transaction($this->connection, function () use ($id): bool {
            $likes = $this->deleteByPostId('community_likes', $id);
            $comments = $this->deleteByPostId('community_comments', $id);
            $deleted = $this->deleteById('community_posts', $id);

            if ($deleted) {
                \admin_audit($this->connection, 'community.post.deleted', 'community_post', $id, [
                    'likes_deleted' => $likes,
                    'comments_deleted' => $comments,
                ]);
            }

            return $deleted;
        });
    }

    public function deleteComment(int $id): bool
    {
        return \in_transaction($this->connection, function () use ($id): bool {
            $deleted = $this->deleteById('community_comments', $id);

            if ($deleted) {
                \admin_audit($this->connection, 'community.comment.deleted', 'community_comment', $id);
            }

            return $deleted;
        });
    }

    private function deleteByPostId(string $table, int $postId): int
    {
        $statement = $this->connection->prepare("DELETE FROM {$table} WHERE post_id = ?");
        $statement->bind_param('i', $postId);
        $statement->execute();
        $deleted = $statement->affected_rows;
        $statement->close();

        return $deleted;
    }

    private function deleteById(string $table, int $id): bool
    {
        $statement = $this->connection->prepare("DELETE FROM {$table} WHERE id = ?");
        $statement->bind_param('i', $id);
        $statement->execute();
        $deleted = $statement->affected_rows > 0;
        $statement->close();

        return $deleted;
    }
}
