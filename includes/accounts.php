<?php

declare(strict_types=1);

function normalize_email(mixed $value): ?string
{
    $email = is_string($value) ? mb_strtolower(trim($value)) : '';

    return mb_strlen($email) <= 150 && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function valid_display_name(mixed $value): ?string
{
    $name = required_string($value, 100);

    return $name !== null && mb_strlen($name) >= 2 ? $name : null;
}

function record_account_event(mysqli $connection, ?int $userId, string $event): void
{
    $statement = $connection->prepare('INSERT INTO account_security_events (user_id, event_type) VALUES (?, ?)');
    $statement->bind_param('is', $userId, $event);
    $statement->execute();
    $statement->close();
}

function record_user_activity(mysqli $connection, int $userId, string $type, string $subjectType, string $subjectValue): void
{
    if (!in_array($type, ['guide_view', 'search', 'diagnostic'], true) || $userId < 1) {
        return;
    }

    $subjectValue = mb_substr($subjectValue, 0, 150);
    $statement = $connection->prepare('INSERT INTO user_activity (user_id, activity_type, subject_type, subject_value) VALUES (?, ?, ?, ?)');
    $statement->bind_param('isss', $userId, $type, $subjectType, $subjectValue);
    $statement->execute();
    $statement->close();
}

function merge_guest_progress(mysqli $connection, int $userId): void
{
    $guestProgress = $_SESSION['_guest_progress'] ?? [];

    if ($userId < 1 || !is_array($guestProgress)) {
        return;
    }

    $statement = $connection->prepare('INSERT INTO user_progress (user_id, guide_step_id, completed) SELECT ?, guide_steps.id, 1 FROM guide_steps WHERE guide_steps.id = ? ON DUPLICATE KEY UPDATE completed = completed');

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
    unset($_SESSION['_guest_progress']);
}

function create_password_reset_token(mysqli $connection, int $userId): string
{
    $connection->query('DELETE FROM password_reset_tokens WHERE expires_at < UTC_TIMESTAMP() OR used_at IS NOT NULL');
    $invalidate = $connection->prepare('UPDATE password_reset_tokens SET used_at = UTC_TIMESTAMP() WHERE user_id = ? AND used_at IS NULL');
    $invalidate->bind_param('i', $userId);
    $invalidate->execute();
    $invalidate->close();

    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $statement = $connection->prepare('INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, UTC_TIMESTAMP() + INTERVAL 1 HOUR)');
    $statement->bind_param('is', $userId, $hash);
    $statement->execute();
    $statement->close();

    return $token;
}

function consume_password_reset_token(mysqli $connection, string $token, string $password): ?int
{
    if (mb_strlen($password) < 12 || preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
        return null;
    }

    $hash = hash('sha256', $token);
    $statement = $connection->prepare('SELECT id, user_id FROM password_reset_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() LIMIT 1');
    $statement->bind_param('s', $hash);
    $statement->execute();
    $reset = $statement->get_result()->fetch_assoc();
    $statement->close();

    if ($reset === null) {
        return null;
    }

    $resetId = (int) $reset['id'];
    $userId = (int) $reset['user_id'];

    return in_transaction($connection, static function () use ($connection, $resetId, $userId, $password): ?int {
        $use = $connection->prepare('UPDATE password_reset_tokens SET used_at = UTC_TIMESTAMP() WHERE id = ? AND used_at IS NULL');
        $use->bind_param('i', $resetId);
        $use->execute();
        $used = $use->affected_rows === 1;
        $use->close();

        if (!$used) {
            return null;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $connection->prepare("UPDATE users SET password = ? WHERE id = ? AND status = 'active'");
        $update->bind_param('si', $hash, $userId);
        $update->execute();
        $updated = $update->affected_rows === 1;
        $update->close();

        return $updated ? $userId : null;
    });
}
