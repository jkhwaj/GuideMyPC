<?php
require_once __DIR__ . '/config.php';
require_post();
require_admin();
require_csrf();

$id = (int) ($_POST["id"] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM guides WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

redirect('admin_guides.php?success=guide_deleted');
