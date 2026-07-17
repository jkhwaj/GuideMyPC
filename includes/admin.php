<?php

declare(strict_types=1);

function admin_audit(mysqli $connection, string $action, string $targetType, string|int $targetId, array $metadata = []): void
{
    $safe = redact_log_context($metadata);
    $json = json_encode($safe, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $actorId = current_user_id() ?: null;
    $target = (string) $targetId;
    $requestId = request_id();
    $statement = $connection->prepare('INSERT INTO admin_audit_events (actor_user_id, action, target_type, target_id, metadata_json, request_id) VALUES (?, ?, ?, ?, ?, ?)');
    $statement->bind_param('isssss', $actorId, $action, $targetType, $target, $json, $requestId);
    $statement->execute();
    $statement->close();
}

function require_admin_post(): void
{
    require_post();
    require_csrf();
    require_admin();
}
