<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/diagnostics.php';

function diagnostic_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$test = test_database_or_fail();
$flow = diagnostic_flow($test, 'pc-no-power');

if ($flow === null) {
    fwrite(STDERR, "FAIL: seeded diagnostic flow is not available in DB_TEST_NAME.\n");
    exit(1);
}

$_SESSION = [];
$sessionId = 0;

try {
    $session = diagnostic_start($test, $flow);
    $sessionId = (int) $session['id'];
    $publicId = (string) $session['public_id'];
    diagnostic_assert(preg_match('/^[a-f0-9]{48}$/', $publicId) === 1, 'Diagnostic sessions use an unguessable public identifier.');
    diagnostic_assert(diagnostic_session($test, $publicId) !== null, 'The creating guest session can resume its diagnostic.');

    $ownerSession = $_SESSION;
    $_SESSION = [];
    diagnostic_assert(diagnostic_session($test, $publicId) === null, 'A different guest session cannot access the diagnostic.');
    $_SESSION = $ownerSession;

    diagnostic_assert(
        diagnostic_transition($test, $session, 'answer', 'tampered') === 'invalid_transition',
        'A tampered option is rejected.'
    );
    $unchanged = diagnostic_session($test, $publicId);
    diagnostic_assert($unchanged !== null && $unchanged['current_node_key'] === 'power_lights', 'A rejected option does not advance the session.');

    diagnostic_assert(diagnostic_transition($test, $unchanged, 'answer', 'yes') === 'answered', 'A valid first answer is accepted.');
    $secondQuestion = diagnostic_session($test, $publicId);
    diagnostic_assert($secondQuestion !== null && $secondQuestion['current_node_key'] === 'display_check', 'A valid answer advances exactly one node.');
    diagnostic_assert($secondQuestion['completed_at'] === null, 'A question node does not complete the session.');

    diagnostic_assert(
        diagnostic_transition($test, $unchanged, 'answer', 'no') === 'invalid_transition',
        'A transition based on a stale request snapshot is rejected after locking the current session.'
    );
    $afterStaleRequest = diagnostic_session($test, $publicId);
    diagnostic_assert(
        $afterStaleRequest !== null && $afterStaleRequest['current_node_key'] === 'display_check',
        'A stale request does not advance the current node.'
    );
    diagnostic_assert(diagnostic_transition($test, $secondQuestion, 'answer', 'no') === 'answered', 'A fresh outcome transition is accepted.');
    $outcome = diagnostic_session($test, $publicId);
    diagnostic_assert($outcome !== null && $outcome['current_node_key'] === 'outcome_display', 'The session reaches the selected outcome.');
    diagnostic_assert($outcome['completed_at'] !== null, 'Reaching an outcome records completion.');

    $answerCount = (int) $test->query('SELECT COUNT(*) AS total FROM diagnostic_answers WHERE session_id = ' . $sessionId)->fetch_assoc()['total'];
    diagnostic_assert($answerCount === 2, 'Each answered question has one durable answer.');
    $firstAnswer = $test->query("SELECT option_key FROM diagnostic_answers WHERE session_id = {$sessionId} AND node_key = 'power_lights'")->fetch_assoc();
    diagnostic_assert(is_array($firstAnswer) && $firstAnswer['option_key'] === 'yes', 'A stale request cannot rewrite the prior node answer.');

    diagnostic_assert(diagnostic_transition($test, $outcome, 'back') === 'backed', 'Back is accepted at an outcome.');
    $back = diagnostic_session($test, $publicId);
    diagnostic_assert($back !== null && $back['current_node_key'] === 'display_check', 'Back removes the latest answer and recomputes the current node.');
    diagnostic_assert($back['completed_at'] === null, 'Back clears completion.');

    diagnostic_assert(diagnostic_transition($test, $back, 'restart') === 'restarted', 'Restart is accepted.');
    $restarted = diagnostic_session($test, $publicId);
    diagnostic_assert($restarted !== null && $restarted['current_node_key'] === 'power_lights', 'Restart restores the initial node.');
    diagnostic_assert($restarted['completed_at'] === null, 'Restart clears completion.');
    $answerCount = (int) $test->query('SELECT COUNT(*) AS total FROM diagnostic_answers WHERE session_id = ' . $sessionId)->fetch_assoc()['total'];
    diagnostic_assert($answerCount === 0, 'Restart removes all prior answers atomically.');

    diagnostic_assert(
        diagnostic_transition($test, $restarted, 'unsupported') === 'invalid_action',
        'Unknown diagnostic actions are rejected without mutation.'
    );

    $test->query("UPDATE diagnostic_sessions SET expires_at = UTC_TIMESTAMP() - INTERVAL 1 SECOND WHERE id = {$sessionId}");
    diagnostic_assert(diagnostic_session($test, $publicId) === null, 'Expired diagnostic sessions cannot be resumed.');

    $test->query('DELETE FROM diagnostic_sessions WHERE id = ' . $sessionId);
    $sessionId = 0;
    fwrite(STDOUT, "PASS: diagnostic ownership, transitions, completion, back, restart, and expiry work.\n");
} catch (Throwable $exception) {
    if ($sessionId > 0) {
        $test->query('DELETE FROM diagnostic_sessions WHERE id = ' . $sessionId);
    }

    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
