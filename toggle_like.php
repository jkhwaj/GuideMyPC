<?php
session_start();
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$post_id = intval($_GET["post_id"] ?? 0);

if ($post_id <= 0) {
    header("Location: community.php");
    exit;
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

header("Location: community.php");
exit;