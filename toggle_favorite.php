<?php
session_start();

include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$guide_id = intval($_GET["guide_id"] ?? 0);
$slug = $_GET["slug"] ?? "";

if ($guide_id <= 0) {
    header("Location: guides.php");
    exit;
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

header("Location: guide.php?slug=" . urlencode($slug));
exit;