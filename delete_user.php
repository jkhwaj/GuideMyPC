<?php
session_start();

include("config.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: admin_users.php");
    exit;
}

// לא לאפשר לאדמין למחוק את עצמו
if ($id == $_SESSION["user_id"]) {
    header("Location: admin_users.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_users.php?success=user_deleted");
exit;