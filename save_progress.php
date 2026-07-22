<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/guides.php';

use GuideMyPC\Features\Guides\GuideProgressService;

require_post();
require_csrf();

$user_id = current_user_id();

$step_id = (int) ($_POST["step_id"] ?? 0);
$completed = (int) ($_POST["completed"] ?? 0);

if ($step_id <= 0 || !in_array($completed, [0, 1], true)) {
    abort_request(422, 'invalid_progress', 'Choose a valid guide step and progress state.');
}

$step = guide_public_step_by_id($conn, $step_id);

if ($step === null) {
    abort_request(404, 'guide_step_not_found', 'That guide step is no longer available.');
}

(new GuideProgressService($conn))->save($user_id, (int) $step['guide_id'], $step_id, $completed === 1, $_SESSION);

if (expects_json()) {
    json_response(200, ['step_id' => $step_id, 'completed' => $completed === 1, 'guest' => $user_id === 0]);
}

$slug = required_string($_POST['guide_slug'] ?? null, 150);
flash('success', $completed === 1 ? ($user_id === 0 ? 'Step marked for this browser session. Sign in to save it permanently.' : 'Step marked as complete.') : 'Step marked as incomplete.');
redirect($slug === $step['slug'] ? 'guide.php?slug=' . urlencode($slug) : 'guides.php');
