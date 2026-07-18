<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

use GuideMyPC\Features\Guides\GuideAdminRepository;
use GuideMyPC\Features\Guides\GuideAdminService;

if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    abort_request(405, 'method_not_allowed', 'This request method is not allowed.');
}

if (is_logged_in()) {
    refresh_current_user_authorization($conn);
}

require_editor();
$repository = new GuideAdminRepository($conn);
$service = new GuideAdminService($conn);
$errors = [];
$guide = ['category_id' => 0, 'title' => '', 'slug' => '', 'description' => '', 'difficulty' => '', 'estimated_time' => '', 'risk_level' => '', 'platform_version' => '', 'required_tools' => '', 'prerequisites' => '', 'backup_warning' => '', 'next_actions' => '', 'video_url' => '', 'last_reviewed_at' => '', 'is_published' => 0, 'featured_order' => null];
$steps = [['text' => '', 'title' => '', 'expected_result' => '', 'warning_text' => '', 'recovery_text' => '', 'image_url' => '', 'image_alt' => '', 'video_timestamp' => '']];
$sources = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    require_csrf();
    $validation = $service->validate($_POST);
    $guide = [...$guide, ...$validation['values']];
    $steps = $validation['values']['steps'];
    $sources = $validation['values']['sources'];
    $errors = $validation['errors'];

    if ($errors === [] && $repository->slugExists($guide['slug'])) {
        $errors[] = 'That guide slug is already in use.';
    }

    if ($errors === []) {
        try {
            $service->create($validation['values']);
            flash('success', 'Structured guide created. Publish it when it is ready for public use.');
            redirect('admin_guides.php');
        } catch (DomainException $exception) {
            $errors[] = 'The selected category no longer exists.';
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() !== 1062) {
                throw $exception;
            }

            $errors[] = 'That guide slug or source URL is already in use.';
        }
    }
}

$categories = $repository->paginate([])['categories'];
$formTitle = 'Add structured guide';
$formDescription = 'New guides start unpublished until their content and sources are reviewed.';
$submitLabel = 'Create guide';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/resources/views/admin/guide-form.php';
include __DIR__ . '/includes/footer.php';
