<?php

declare(strict_types=1);

function diagnostic_flow(mysqli $connection, string $slug): ?array
{
    $statement = $connection->prepare("SELECT diagnostic_flows.*, diagnostic_flow_versions.id AS version_id, diagnostic_flow_versions.initial_node_key FROM diagnostic_flows JOIN diagnostic_flow_versions ON diagnostic_flow_versions.flow_id = diagnostic_flows.id LEFT JOIN categories ON categories.id = diagnostic_flows.category_id WHERE diagnostic_flows.slug = ? AND diagnostic_flows.publication_state = 'published' AND diagnostic_flow_versions.publication_state = 'published' AND (diagnostic_flows.category_id IS NULL OR categories.is_published = 1) ORDER BY diagnostic_flow_versions.version_number DESC LIMIT 1");
    $statement->bind_param('s', $slug); $statement->execute(); $flow = $statement->get_result()->fetch_assoc(); $statement->close();
    return is_array($flow) ? $flow : null;
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
    $statement = $connection->prepare('SELECT * FROM diagnostic_nodes WHERE version_id = ? AND node_key = ? LIMIT 1'); $statement->bind_param('is', $versionId, $nodeKey); $statement->execute(); $node = $statement->get_result()->fetch_assoc(); $statement->close();
    return is_array($node) ? $node : null;
}
