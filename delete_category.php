<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';

require_post();

if (is_logged_in()) {
    refresh_current_user_authorization($conn);
}

require_admin();
require_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;

if ($id === 0) {
    flash('error', 'Choose a valid category to delete.');
    redirect('admin_categories.php');
}

$result = (new GuideMyPC\Features\Categories\CategoryAdminService($conn))->delete($id);

if ($result['status'] === 'missing') {
    flash('error', 'The category no longer exists.');
} elseif ($result['status'] === 'blocked') {
    $dependencies = [];

    foreach ($result['dependencies'] as $label => $count) {
        if ($count > 0) {
            $dependencies[] = number_format($count) . ' ' . $label;
        }
    }

    flash('error', 'Category deletion is blocked by: ' . implode(', ', $dependencies) . '. Unpublish it instead.');
} else {
    flash('success', 'Category deleted.');
}

redirect('admin_categories.php');
