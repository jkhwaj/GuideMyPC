<?php
require_once __DIR__ . '/config.php';
require_post();
require_admin();
require_csrf();

$postId = (int) ($_POST["id"] ?? 0);

if ($postId <= 0) {
    redirect('admin_community.php');
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

redirect('admin_community.php');
