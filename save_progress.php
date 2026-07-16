<?php
require_once __DIR__ . '/config.php';
require_post();
require_login();
require_csrf();

$user_id = current_user_id();

$step_id = (int) ($_POST["step_id"] ?? 0);
$completed = (int) ($_POST["completed"] ?? 0);

if ($step_id <= 0 || !in_array($completed, [0, 1], true)) {
    http_response_code(422);
    exit('Invalid progress request.');
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

http_response_code(204);
