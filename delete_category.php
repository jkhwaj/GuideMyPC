<?php
require_once __DIR__ . '/config.php';
require_post();
require_admin();
require_csrf();

$id = (int) ($_POST["id"] ?? 0);

if ($id <= 0) {
    redirect('admin_categories.php');
}

$checkStmt = $conn->prepare("SELECT COUNT(*) AS total FROM guides WHERE category_id = ?");
$checkStmt->bind_param("i", $id);
$checkStmt->execute();
$check = $checkStmt->get_result()->fetch_assoc();

if ($check["total"] > 0) {
    redirect('admin_categories.php?error=category_has_guides');
}

$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

redirect('admin_categories.php');
