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
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$repository = new GuideAdminRepository($conn);
$service = new GuideAdminService($conn);
$guide = $repository->find($id);

if ($guide === null) {
    abort_request(404, 'guide_not_found', 'The requested guide could not be found.');
}

$stepsStatement = $conn->prepare('SELECT * FROM guide_steps WHERE guide_id = ? ORDER BY step_number');
$stepsStatement->bind_param('i', $id);
$stepsStatement->execute();
$steps = $stepsStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$stepsStatement->close();
$sources = $repository->sources($id);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    require_csrf();
    $validation = $service->validate($_POST);
    $guide = [...$guide, ...$validation['values']];
    $steps = $validation['values']['steps'];
    $sources = $validation['values']['sources'];
    $errors = $validation['errors'];

    if ($errors === [] && $repository->slugExists($guide['slug'], $id)) {
        $errors[] = 'That guide slug is already in use.';
    }

    if ($errors === []) {
        try {
            if (!$service->update($id, $validation['values'])) {
                abort_request(404, 'guide_not_found', 'The requested guide could not be found.');
            }

            flash('success', 'Structured guide updated.');
            redirect('admin_guides.php');
        } catch (DomainException $exception) {
            $errors[] = 'The selected category or submitted step is no longer valid. Reload the guide and try again.';
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() !== 1062) {
                throw $exception;
            }

            $errors[] = 'That guide slug or source URL is already in use.';
        }
    }
}

$categories = $repository->paginate([])['categories'];
$formTitle = 'Edit structured guide';
$formDescription = 'Changes to retained steps preserve saved progress. Removing a step removes progress for that step only.';
$submitLabel = 'Save changes';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/resources/views/admin/guide-form.php';
include __DIR__ . '/includes/footer.php';
