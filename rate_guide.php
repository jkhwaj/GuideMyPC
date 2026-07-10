<?php
session_start();
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$guide_id = intval($_POST["guide_id"] ?? 0);
$rating = intval($_POST["rating"] ?? 0);

if ($guide_id <= 0 || $rating < 1 || $rating > 5) {
    header("Location: guides.php");
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO guide_ratings (guide_id, user_id, rating)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE rating = VALUES(rating)
");

$stmt->bind_param("iii", $guide_id, $user_id, $rating);
$stmt->execute();

$slug = $_POST["slug"] ?? "";
header("Location: guide.php?slug=" . urlencode($slug));
exit;