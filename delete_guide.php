<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

use GuideMyPC\Features\Guides\GuideAdminService;

require_post();

if (is_logged_in()) {
    refresh_current_user_authorization($conn);
}

require_admin();
require_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;

if ($id === 0) {
    flash('error', 'Choose a valid guide to delete.');
    redirect('admin_guides.php');
}

$result = (new GuideAdminService($conn))->delete($id);

if ($result['status'] === 'missing') {
    flash('error', 'That guide no longer exists.');
} elseif ($result['status'] === 'blocked') {
    $dependencies = [];

    foreach ($result['dependencies'] as $label => $count) {
        if ($count > 0) {
            $dependencies[] = number_format($count) . ' ' . $label;
        }
    }

    flash('error', 'Guide deletion is blocked by: ' . implode(', ', $dependencies) . '. Unpublish it instead.');
} else {
    flash('success', 'Guide deleted.');
}

redirect('admin_guides.php');
