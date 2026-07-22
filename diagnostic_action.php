<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/accounts.php';
require_once __DIR__ . '/includes/diagnostics.php';

require_post();
require_csrf();

$publicId = required_string($_POST['session'] ?? null, 48) ?? '';
$session = diagnostic_session($conn, $publicId);

if ($session === null) {
    abort_request(404, 'diagnostic_session_not_found', 'This diagnostic session is unavailable or expired.');
}

$action = required_string($_POST['action'] ?? null, 20) ?? '';
$result = diagnostic_transition(
    $conn,
    $session,
    $action,
    required_string($_POST['option'] ?? null, 100)
);

if ($result === 'invalid_action') {
    abort_request(422, 'diagnostic_action_invalid', 'Choose a valid diagnostic action.');
}

if ($result === 'invalid_transition') {
    abort_request(422, 'diagnostic_transition_invalid', 'That answer is not valid for this question.');
}

if ($action === 'answer' && current_user_id()) {
    record_user_activity($conn, current_user_id(), 'diagnostic', 'session', $publicId);
}

redirect('diagnostic.php?session=' . rawurlencode($publicId));
