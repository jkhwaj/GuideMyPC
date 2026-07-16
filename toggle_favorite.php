<?php
require_once __DIR__ . '/config.php';
require_post();
require_login();
require_csrf();

$user_id = current_user_id();
$guide_id = (int) ($_POST["guide_id"] ?? 0);
$slug = (string) ($_POST["slug"] ?? '');

if ($guide_id <= 0) {
    redirect('guides.php');
}

$stmt = $conn->prepare("
    SELECT id FROM favorites
    WHERE user_id = ? AND guide_id = ?
");
$stmt->bind_param("ii", $user_id, $guide_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $delete = $conn->prepare("
        DELETE FROM favorites
        WHERE user_id = ? AND guide_id = ?
    ");
    $delete->bind_param("ii", $user_id, $guide_id);
    $delete->execute();
} else {
    $insert = $conn->prepare("
        INSERT INTO favorites (user_id, guide_id)
        VALUES (?, ?)
    ");
    $insert->bind_param("ii", $user_id, $guide_id);
    $insert->execute();
}

redirect('guide.php?slug=' . urlencode($slug));
