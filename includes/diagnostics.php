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

/**
 * @param array<string, mixed> $session
 * @return 'answered'|'backed'|'restarted'|'invalid_action'|'invalid_transition'
 */
function diagnostic_transition(mysqli $connection, array $session, string $action, ?string $optionKey = null): string
{
    if (!in_array($action, ['answer', 'back', 'restart'], true)) {
        return 'invalid_action';
    }

    return in_transaction($connection, static function () use ($connection, $session, $action, $optionKey): string {
        $lockedSession = diagnostic_locked_session($connection, (int) $session['id']);

        if (
            $lockedSession === null
            || (int) $lockedSession['version_id'] !== (int) $session['version_id']
            || (string) $lockedSession['current_node_key'] !== (string) $session['current_node_key']
        ) {
            return 'invalid_transition';
        }

        $sessionId = (int) $lockedSession['id'];

        if ($action === 'restart') {
            $statement = $connection->prepare('DELETE FROM diagnostic_answers WHERE session_id = ?');
            $statement->bind_param('i', $sessionId);
            $statement->execute();
            $statement->close();

            diagnostic_update_session_node(
                $connection,
                $sessionId,
                diagnostic_initial_node_key($connection, (int) $lockedSession['version_id']),
                false
            );

            return 'restarted';
        }

        if ($action === 'back') {
            $statement = $connection->prepare('SELECT id FROM diagnostic_answers WHERE session_id = ? ORDER BY id DESC LIMIT 1');
            $statement->bind_param('i', $sessionId);
            $statement->execute();
            $lastAnswer = $statement->get_result()->fetch_assoc();
            $statement->close();

            if (is_array($lastAnswer)) {
                $answerId = (int) $lastAnswer['id'];
                $statement = $connection->prepare('DELETE FROM diagnostic_answers WHERE id = ?');
                $statement->bind_param('i', $answerId);
                $statement->execute();
                $statement->close();
            }

            diagnostic_update_session_node(
                $connection,
                $sessionId,
                diagnostic_replayed_node_key($connection, $lockedSession),
                false
            );

            return 'backed';
        }

        $node = diagnostic_node($connection, (int) $lockedSession['version_id'], (string) $lockedSession['current_node_key']);

        if ($node === null || $node['node_type'] !== 'question' || $optionKey === null) {
            return 'invalid_transition';
        }

        $nodeId = (int) $node['id'];
        $statement = $connection->prepare('SELECT next_node_key FROM diagnostic_options WHERE node_id = ? AND option_key = ? LIMIT 1');
        $statement->bind_param('is', $nodeId, $optionKey);
        $statement->execute();
        $option = $statement->get_result()->fetch_assoc();
        $statement->close();
        $nextNode = is_array($option)
            ? diagnostic_node($connection, (int) $lockedSession['version_id'], (string) $option['next_node_key'])
            : null;

        if ($nextNode === null) {
            return 'invalid_transition';
        }

        $statement = $connection->prepare(
            'INSERT INTO diagnostic_answers (session_id, node_key, option_key) VALUES (?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE option_key = VALUES(option_key), created_at = UTC_TIMESTAMP()'
        );
        $nodeKey = (string) $node['node_key'];
        $statement->bind_param('iss', $sessionId, $nodeKey, $optionKey);
        $statement->execute();
        $statement->close();

        diagnostic_update_session_node(
            $connection,
            $sessionId,
            (string) $nextNode['node_key'],
            $nextNode['node_type'] === 'outcome'
        );

        return 'answered';
    });
}

function diagnostic_locked_session(mysqli $connection, int $sessionId): ?array
{
    $statement = $connection->prepare(
        'SELECT * FROM diagnostic_sessions WHERE id = ? AND expires_at > UTC_TIMESTAMP() LIMIT 1 FOR UPDATE'
    );
    $statement->bind_param('i', $sessionId);
    $statement->execute();
    $session = $statement->get_result()->fetch_assoc();
    $statement->close();

    return is_array($session) ? $session : null;
}

function diagnostic_initial_node_key(mysqli $connection, int $versionId): string
{
    $statement = $connection->prepare('SELECT initial_node_key FROM diagnostic_flow_versions WHERE id = ? LIMIT 1');
    $statement->bind_param('i', $versionId);
    $statement->execute();
    $version = $statement->get_result()->fetch_assoc();
    $statement->close();

    if (!is_array($version)) {
        throw new RuntimeException('Diagnostic version is unavailable.');
    }

    return (string) $version['initial_node_key'];
}

/** @param array<string, mixed> $session */
function diagnostic_replayed_node_key(mysqli $connection, array $session): string
{
    $nodeKey = diagnostic_initial_node_key($connection, (int) $session['version_id']);
    $statement = $connection->prepare('SELECT node_key, option_key FROM diagnostic_answers WHERE session_id = ? ORDER BY id');
    $statement->bind_param('i', $session['id']);
    $statement->execute();
    $answers = $statement->get_result();

    while ($answer = $answers->fetch_assoc()) {
        $node = diagnostic_node($connection, (int) $session['version_id'], (string) $answer['node_key']);

        if ($node === null) {
            continue;
        }

        $option = $connection->prepare('SELECT next_node_key FROM diagnostic_options WHERE node_id = ? AND option_key = ? LIMIT 1');
        $option->bind_param('is', $node['id'], $answer['option_key']);
        $option->execute();
        $next = $option->get_result()->fetch_assoc();
        $option->close();

        if (is_array($next)) {
            $nodeKey = (string) $next['next_node_key'];
        }
    }

    $statement->close();

    return $nodeKey;
}

function diagnostic_update_session_node(mysqli $connection, int $sessionId, string $nodeKey, bool $completed): void
{
    $completedSql = $completed ? 'UTC_TIMESTAMP()' : 'NULL';
    $statement = $connection->prepare(
        "UPDATE diagnostic_sessions SET current_node_key = ?, completed_at = {$completedSql} WHERE id = ?"
    );
    $statement->bind_param('si', $nodeKey, $sessionId);
    $statement->execute();
    $statement->close();
}
