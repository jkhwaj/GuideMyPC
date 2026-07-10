<?php
session_start();

include("config.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

$postId = intval($_GET["id"] ?? 0);

if ($postId <= 0) {
    header("Location: admin_community.php");
    exit;
}

// Delete likes
$stmt = $conn->prepare("
    DELETE FROM community_likes
    WHERE post_id = ?
");
$stmt->bind_param("i", $postId);
$stmt->execute();

// Delete comments
$stmt = $conn->prepare("
    DELETE FROM community_comments
    WHERE post_id = ?
");
$stmt->bind_param("i", $postId);
$stmt->execute();

// Delete post
$stmt = $conn->prepare("
    DELETE FROM community_posts
    WHERE id = ?
");
$stmt->bind_param("i", $postId);
$stmt->execute();

header("Location: admin_community.php");
exit;