<?php
require_once __DIR__ . '/config.php';
require_post();
require_csrf();

$user_id = current_user_id();

$step_id = (int) ($_POST["step_id"] ?? 0);
$completed = (int) ($_POST["completed"] ?? 0);

if ($step_id <= 0 || !in_array($completed, [0, 1], true)) {
    abort_request(422, 'invalid_progress', 'Choose a valid guide step and progress state.');
}

$stepStatement = $conn->prepare('SELECT guide_steps.id, guide_steps.guide_id FROM guide_steps JOIN guides ON guide_steps.guide_id = guides.id WHERE guide_steps.id = ? AND guides.is_published = 1');
$stepStatement->bind_param('i', $step_id);
$stepStatement->execute();
$step = $stepStatement->get_result()->fetch_assoc();
$stepStatement->close();

if ($step === null) {
    abort_request(404, 'guide_step_not_found', 'That guide step is no longer available.');
}

if ($user_id === 0) {
    $guideId = (int) $step['guide_id'];
    $_SESSION['_guest_progress'][$guideId] = $_SESSION['_guest_progress'][$guideId] ?? [];

    if ($completed === 1) {
        $_SESSION['_guest_progress'][$guideId][$step_id] = true;
    } else {
        unset($_SESSION['_guest_progress'][$guideId][$step_id]);
    }
} else {
    if ($completed === 1) {
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
    $stmt->close();
}

if (expects_json()) {
    json_response(200, ['step_id' => $step_id, 'completed' => $completed === 1, 'guest' => $user_id === 0]);
}

$slug = required_string($_POST['guide_slug'] ?? null, 150);
flash('success', $completed === 1 ? ($user_id === 0 ? 'Step marked for this browser session. Sign in to save it permanently.' : 'Step marked as complete.') : 'Step marked as incomplete.');
redirect($slug === null ? 'guides.php' : 'guide.php?slug=' . urlencode($slug));
