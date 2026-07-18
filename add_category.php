<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    abort_request(405, 'method_not_allowed', 'This request method is not allowed.');
}

if (is_logged_in()) {
    refresh_current_user_authorization($conn);
}

require_editor();
$repository = new GuideMyPC\Features\Categories\CategoryAdminRepository($conn);
$service = new GuideMyPC\Features\Categories\CategoryAdminService($conn);
$category = ['name' => '', 'slug' => '', 'description' => '', 'icon' => '', 'is_published' => 0, 'featured_order' => null];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $validation = $service->validate($_POST);
    $category = $validation['values'];
    $errors = $validation['errors'];

    if ($errors === [] && $repository->slugExists($category['slug'])) {
        $errors[] = 'That category slug is already in use.';
    }

    if ($errors === []) {
        try {
            $service->create($category);
            flash('success', 'Category created.');
            redirect('admin_categories.php');
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() !== 1062) {
                throw $exception;
            }

            $errors[] = 'That category slug is already in use.';
        }
    }
}

$formTitle = 'Add Category';
$formDescription = 'Create a support category as an unpublished draft or publish it immediately.';
$submitLabel = 'Create category';
$publicationWarning = 'Publishing makes this category available to public Guides, Knowledge, Search, and homepage projections.';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/resources/views/admin/category-form.php';
include __DIR__ . '/includes/footer.php';
