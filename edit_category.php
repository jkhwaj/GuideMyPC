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
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$repository = new GuideMyPC\Features\Categories\CategoryAdminRepository($conn);
$service = new GuideMyPC\Features\Categories\CategoryAdminService($conn);
$category = $repository->find($id);

if ($category === null) {
    abort_request(404, 'category_not_found', 'The requested category could not be found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $validation = $service->validate($_POST);
    $category = [...$category, ...$validation['values']];
    $errors = $validation['errors'];

    if ($errors === [] && $repository->slugExists($category['slug'], $id)) {
        $errors[] = 'That category slug is already in use.';
    }

    if ($errors === []) {
        try {
            if (!$service->update($id, $validation['values'])) {
                abort_request(404, 'category_not_found', 'The requested category could not be found.');
            }

            flash('success', 'Category updated.');
            redirect('admin_categories.php');
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() !== 1062) {
                throw $exception;
            }

            $errors[] = 'That category slug is already in use.';
        }
    }
}

$formTitle = 'Edit Category';
$formDescription = 'Update category visibility and homepage placement.';
$submitLabel = 'Save changes';
$publicationWarning = 'Unpublishing this category also hides its guides and knowledge articles from public category-based projections.';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/resources/views/admin/category-form.php';
include __DIR__ . '/includes/footer.php';
