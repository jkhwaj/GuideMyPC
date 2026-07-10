<?php
session_start();

include("config.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

$id = intval($_GET["id"] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM guides WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: admin_guides.php?success=guide_deleted");
exit;