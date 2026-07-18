<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Accounts;

use mysqli;
use Throwable;

final class AccountService
{
    public function __construct(private readonly mysqli $connection)
    {
    }

    public function recordSecurityEvent(?int $userId, string $event): void
    {
        $statement = $this->connection->prepare('INSERT INTO account_security_events (user_id, event_type) VALUES (?, ?)');
        $statement->bind_param('is', $userId, $event);
        $statement->execute();
        $statement->close();
    }

    public function recordActivity(int $userId, string $type, string $subjectType, string $subjectValue): void
    {
        if (!in_array($type, ['guide_view', 'search', 'diagnostic'], true) || $userId < 1) {
            return;
        }

        $subjectValue = mb_substr($subjectValue, 0, 150);
        $statement = $this->connection->prepare('INSERT INTO user_activity (user_id, activity_type, subject_type, subject_value) VALUES (?, ?, ?, ?)');
        $statement->bind_param('isss', $userId, $type, $subjectType, $subjectValue);
        $statement->execute();
        $statement->close();
    }

    /**
     * @param array<mixed, mixed> $guestProgress
     */
    public function mergeGuestProgress(int $userId, array $guestProgress): void
    {
        if ($userId < 1) {
            return;
        }

        $statement = $this->connection->prepare('INSERT INTO user_progress (user_id, guide_step_id, completed) SELECT ?, guide_steps.id, 1 FROM guide_steps JOIN guides ON guides.id = guide_steps.guide_id JOIN categories ON categories.id = guides.category_id WHERE guide_steps.id = ? AND guides.is_published = 1 AND categories.is_published = 1 ON DUPLICATE KEY UPDATE completed = completed');

        foreach ($guestProgress as $steps) {
            if (!is_array($steps)) {
                continue;
            }

            foreach (array_keys($steps) as $stepId) {
                $stepId = (int) $stepId;

                if ($stepId > 0) {
                    $statement->bind_param('ii', $userId, $stepId);
                    $statement->execute();
                }
            }
        }

        $statement->close();
    }

    public function createPasswordResetToken(int $userId): string
    {
        $this->connection->query('DELETE FROM password_reset_tokens WHERE expires_at < UTC_TIMESTAMP() OR used_at IS NOT NULL');
        $invalidate = $this->connection->prepare('UPDATE password_reset_tokens SET used_at = UTC_TIMESTAMP() WHERE user_id = ? AND used_at IS NULL');
        $invalidate->bind_param('i', $userId);
        $invalidate->execute();
        $invalidate->close();

        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $statement = $this->connection->prepare('INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, UTC_TIMESTAMP() + INTERVAL 1 HOUR)');
        $statement->bind_param('is', $userId, $hash);
        $statement->execute();
        $statement->close();

        return $token;
    }

    public function consumePasswordResetToken(string $token, string $password): ?int
    {
        if (mb_strlen($password) < 12 || preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return null;
        }

        $hash = hash('sha256', $token);
        $statement = $this->connection->prepare('SELECT id, user_id FROM password_reset_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() LIMIT 1');
        $statement->bind_param('s', $hash);
        $statement->execute();
        $reset = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($reset === null) {
            return null;
        }

        $resetId = (int) $reset['id'];
        $userId = (int) $reset['user_id'];
        $this->connection->begin_transaction();

        try {
            $use = $this->connection->prepare('UPDATE password_reset_tokens SET used_at = UTC_TIMESTAMP() WHERE id = ? AND used_at IS NULL');
            $use->bind_param('i', $resetId);
            $use->execute();
            $used = $use->affected_rows === 1;
            $use->close();

            if (!$used) {
                $this->connection->commit();
                return null;
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $this->connection->prepare("UPDATE users SET password = ? WHERE id = ? AND status = 'active'");
            $update->bind_param('si', $passwordHash, $userId);
            $update->execute();
            $updated = $update->affected_rows === 1;
            $update->close();
            $this->connection->commit();

            return $updated ? $userId : null;
        } catch (Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }
}
