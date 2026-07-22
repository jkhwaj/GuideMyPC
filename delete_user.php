<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';
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

(new GuideMyPC\Features\Accounts\UserAdminService($conn))->delete($id);

redirect('admin_users.php?success=user_deleted');
