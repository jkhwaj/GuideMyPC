<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/guides.php';
require_post();
require_login();
require_csrf();

$user_id = current_user_id();
$guide_id = (int) ($_POST["guide_id"] ?? 0);
$rating = (int) ($_POST["rating"] ?? 0);
$slug = required_string($_POST['slug'] ?? null, 150);

if ($guide_id <= 0 || $rating < 1 || $rating > 5 || $slug === null || guide_public_by_id($conn, $guide_id, $slug) === null) {
    redirect('guides.php');
}

$stmt = $conn->prepare("
    INSERT INTO guide_ratings (guide_id, user_id, rating)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE rating = VALUES(rating)
");

$stmt->bind_param("iii", $guide_id, $user_id, $rating);
$stmt->execute();

redirect('guide.php?slug=' . urlencode($slug));
