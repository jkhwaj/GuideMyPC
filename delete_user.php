<?php
require_once __DIR__ . '/config.php';
require_post();
require_admin();
require_csrf();

$id = (int) ($_POST["id"] ?? 0);

if ($id <= 0) {
    redirect('admin_users.php');
}

// לא לאפשר לאדמין למחוק את עצמו
if ($id == $_SESSION["user_id"]) {
    redirect('admin_users.php');
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

redirect('admin_users.php?success=user_deleted');
