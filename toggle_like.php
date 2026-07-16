<?php
require_once __DIR__ . '/config.php';
require_post();
require_login();
require_csrf();

$user_id = current_user_id();
$post_id = (int) ($_POST["post_id"] ?? 0);

if ($post_id <= 0) {
    redirect('community.php');
}

$stmt = $conn->prepare("
    SELECT id FROM community_likes
    WHERE post_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $delete = $conn->prepare("
        DELETE FROM community_likes
        WHERE post_id = ? AND user_id = ?
    ");
    $delete->bind_param("ii", $post_id, $user_id);
    $delete->execute();
} else {
    $insert = $conn->prepare("
        INSERT INTO community_likes (post_id, user_id)
        VALUES (?, ?)
    ");
    $insert->bind_param("ii", $post_id, $user_id);
    $insert->execute();
}

redirect('community.php');
