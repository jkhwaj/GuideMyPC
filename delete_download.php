<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';
require_post();
require_admin();
require_csrf();

$id = (int) ($_POST["id"] ?? 0);

if ($id > 0) {
    (new GuideMyPC\Features\Downloads\DownloadAdminService($conn))->delete($id);
}

redirect('admin_downloads.php?success=download_deleted');
