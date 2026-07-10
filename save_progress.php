<?php
session_start();
include("config.php");

if (!isset($_SESSION["user_id"])) {
    exit("Not logged in");
}

$user_id = $_SESSION["user_id"];

$step_id = intval($_POST["step_id"] ?? 0);
$completed = intval($_POST["completed"] ?? 0);

if ($step_id <= 0) {
    exit("Invalid step");
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

echo "success";