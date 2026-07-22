<?php

declare(strict_types=1);

function diagnostic_flow(mysqli $connection, string $slug): ?array
{
    return (new GuideMyPC\Features\Diagnostics\DiagnosticRepository($connection))->publishedFlow($slug);
}

function diagnostic_start(mysqli $connection, array $flow): array
{
    $publicId = bin2hex(random_bytes(24)); $token = bin2hex(random_bytes(24)); $userId = current_user_id() ?: null;
    $tokenHash = $userId === null ? hash('sha256', $token) : null;
    $statement = $connection->prepare('INSERT INTO diagnostic_sessions (public_id, version_id, user_id, guest_token_hash, current_node_key, expires_at) VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP() + INTERVAL 7 DAY)');
    $statement->bind_param('siiss', $publicId, $flow['version_id'], $userId, $tokenHash, $flow['initial_node_key']); $statement->execute(); $sessionId = $statement->insert_id; $statement->close();
    if ($userId === null) { $_SESSION['_diagnostic_tokens'][$publicId] = $token; }
    return ['id' => $sessionId, 'public_id' => $publicId, 'version_id' => $flow['version_id'], 'current_node_key' => $flow['initial_node_key'], 'user_id' => $userId];
}

function diagnostic_session(mysqli $connection, string $publicId): ?array
{
    $statement = $connection->prepare('SELECT * FROM diagnostic_sessions WHERE public_id = ? AND expires_at > UTC_TIMESTAMP() LIMIT 1'); $statement->bind_param('s', $publicId); $statement->execute(); $session = $statement->get_result()->fetch_assoc(); $statement->close();
    if (!is_array($session)) return null;
    $userId = current_user_id();
    if ($session['user_id'] !== null && (int) $session['user_id'] === $userId) return $session;
    $token = $_SESSION['_diagnostic_tokens'][$publicId] ?? '';
    return $session['user_id'] === null && is_string($token) && hash_equals($session['guest_token_hash'], hash('sha256', $token)) ? $session : null;
}

function diagnostic_node(mysqli $connection, int $versionId, string $nodeKey): ?array
{
    return (new GuideMyPC\Features\Diagnostics\DiagnosticRepository($connection))->node($versionId, $nodeKey);
}
