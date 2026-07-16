<?php
require_once __DIR__ . '/config.php';
require_post();
require_login();
require_csrf();

$user_id = current_user_id();

$step_id = (int) ($_POST["step_id"] ?? 0);
$completed = (int) ($_POST["completed"] ?? 0);

if ($step_id <= 0 || !in_array($completed, [0, 1], true)) {
    abort_request(422, 'invalid_progress', 'Choose a valid guide step and progress state.');
}

$stepStatement = $conn->prepare('SELECT id FROM guide_steps WHERE id = ?');
$stepStatement->bind_param('i', $step_id);
$stepStatement->execute();

if ($stepStatement->get_result()->num_rows === 0) {
    abort_request(404, 'guide_step_not_found', 'That guide step is no longer available.');
}

if ($completed == 1) {

    $sql = "INSERT INTO user_progress (user_id, guide_step_id, completed)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE completed = 1";

} else {

    $sql = "DELETE FROM user_progress
            WHERE user_id = ? AND guide_step_id = ?";

}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $step_id);
$stmt->execute();

if (expects_json()) {
    json_response(200, ['step_id' => $step_id, 'completed' => $completed === 1]);
}

$slug = required_string($_POST['guide_slug'] ?? null, 150);
flash('success', $completed === 1 ? 'Step marked as complete.' : 'Step marked as incomplete.');
redirect($slug === null ? 'guides.php' : 'guide.php?slug=' . urlencode($slug));
