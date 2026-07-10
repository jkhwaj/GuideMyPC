<?php
session_start();

include("config.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: admin_categories.php");
    exit;
}

$checkStmt = $conn->prepare("SELECT COUNT(*) AS total FROM guides WHERE category_id = ?");
$checkStmt->bind_param("i", $id);
$checkStmt->execute();
$check = $checkStmt->get_result()->fetch_assoc();

if ($check["total"] > 0) {
    header("Location: admin_categories.php?error=category_has_guides");
    exit;
}

$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_categories.php");
exit;