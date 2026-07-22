<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';
require_post();
require_admin();
require_csrf();

$postId = (int) ($_POST["id"] ?? 0);

if ($postId <= 0) {
    redirect('admin_community.php');
}

(new GuideMyPC\Features\Community\CommunityAdminService($conn))->deletePost($postId);

redirect('admin_community.php');
