<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';

use GuideMyPC\Features\Dashboard\DashboardReadModel;

function dashboard_metrics(array $metrics): array
{
    $indexed = [];

    foreach ($metrics as $metric) {
        $indexed[$metric['label']] = $metric['value'];
    }

    return $indexed;
}

function dashboard_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$test = test_database_or_fail();
$test->begin_transaction();
$token = bin2hex(random_bytes(5));
$readModel = new DashboardReadModel($test);

try {
    $baseline = dashboard_metrics($readModel->operational(false)['metrics']);

    $categoryName = 'Dashboard Test ' . $token;
    $categorySlug = 'dashboard-test-' . $token;
    $categoryInsert = $test->prepare('INSERT INTO categories (name, slug, description, is_published) VALUES (?, ?, ?, 1)');
    $description = 'Temporary dashboard integration fixture.';
    $categoryInsert->bind_param('sss', $categoryName, $categorySlug, $description);
    $categoryInsert->execute();
    $categoryId = $categoryInsert->insert_id;
    $categoryInsert->close();

    $email = 'dashboard-' . $token . '@example.test';
    $password = password_hash('DashboardPassword1!', PASSWORD_DEFAULT);
    $role = 'editor';
    $userInsert = $test->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
    $userName = 'Dashboard Fixture ' . $token;
    $userInsert->bind_param('ssss', $userName, $email, $password, $role);
    $userInsert->execute();
    $userId = $userInsert->insert_id;
    $userInsert->close();

    $guideTitle = 'Dashboard Guide ' . $token;
    $guideSlug = 'dashboard-guide-' . $token;
    $guideInsert = $test->prepare('INSERT INTO guides (category_id, title, slug, description, is_published) VALUES (?, ?, ?, ?, 1)');
    $guideInsert->bind_param('isss', $categoryId, $guideTitle, $guideSlug, $description);
    $guideInsert->execute();
    $guideId = $guideInsert->insert_id;
    $guideInsert->close();

    $stepInsert = $test->prepare('INSERT INTO guide_steps (guide_id, step_number, step_text) VALUES (?, ?, ?)');
    $stepIds = [];

    foreach ([1 => 'First dashboard test step.', 2 => 'Second dashboard test step.'] as $number => $text) {
        $stepInsert->bind_param('iis', $guideId, $number, $text);
        $stepInsert->execute();
        $stepIds[] = $stepInsert->insert_id;
    }

    $stepInsert->close();

    $knowledgeTitle = 'Dashboard Article ' . $token;
    $knowledgeSlug = 'dashboard-article-' . $token;
    $knowledgeInsert = $test->prepare(
        "INSERT INTO knowledge_articles (category_id, article_type, title, slug, summary, content, publication_state, author_id, published_at) "
        . "VALUES (?, 'explanation', ?, ?, ?, ?, 'published', ?, CURRENT_TIMESTAMP)"
    );
    $knowledgeInsert->bind_param('issssi', $categoryId, $knowledgeTitle, $knowledgeSlug, $description, $description, $userId);
    $knowledgeInsert->execute();
    $knowledgeInsert->close();

    $downloadName = 'Dashboard Download ' . $token;
    $downloadUrl = 'https://downloads.example.test/' . $token;
    $downloadInsert = $test->prepare(
        "INSERT INTO downloads (name, description, official_url, category, is_published, review_state) VALUES (?, ?, ?, 'Support', 1, 'approved')"
    );
    $downloadInsert->bind_param('sss', $downloadName, $description, $downloadUrl);
    $downloadInsert->execute();
    $downloadInsert->close();

    $postTitle = 'Dashboard Post ' . $token;
    $postInsert = $test->prepare('INSERT INTO community_posts (user_id, title, content, is_published) VALUES (?, ?, ?, 1)');
    $postInsert->bind_param('iss', $userId, $postTitle, $description);
    $postInsert->execute();
    $postInsert->close();

    $progressInsert = $test->prepare('INSERT INTO user_progress (user_id, guide_step_id, completed) VALUES (?, ?, 1)');

    foreach ($stepIds as $stepId) {
        $progressInsert->bind_param('ii', $userId, $stepId);
        $progressInsert->execute();
    }

    $progressInsert->close();
    $test->query('INSERT INTO favorites (user_id, guide_id) VALUES (' . $userId . ', ' . $guideId . ')');
    $test->query('INSERT INTO guide_ratings (guide_id, user_id, rating) VALUES (' . $guideId . ', ' . $userId . ', 5)');

    $activityInsert = $test->prepare("INSERT INTO user_activity (user_id, activity_type, subject_type, subject_value) VALUES (?, 'guide_view', 'guide', ?)");
    $activityInsert->bind_param('is', $userId, $guideSlug);
    $activityInsert->execute();
    $activityInsert->close();

    $personal = $readModel->personal($userId);
    $personalMetrics = dashboard_metrics($personal['metrics']);
    dashboard_assert($personal['kind'] === 'personal', 'Registered users receive the personal projection.');
    dashboard_assert($personalMetrics['Guides started'] === 1, 'Personal started-guide count is scoped to the current user.');
    dashboard_assert($personalMetrics['Guides completed'] === 1, 'Personal completion requires all guide steps.');
    dashboard_assert($personalMetrics['Favorites'] === 1 && $personalMetrics['Ratings submitted'] === 1, 'Personal favorites and ratings are scoped to the current user.');
    dashboard_assert(count($personal['activity']) === 1 && $personal['activity'][0]['detail'] === $guideSlug, 'Personal activity returns the current user history.');

    $editorProjection = $readModel->operational(false);
    $editorMetrics = dashboard_metrics($editorProjection['metrics']);
    dashboard_assert($editorMetrics['Published guides'] === $baseline['Published guides'] + 1, 'Published guide KPI uses publication state.');
    dashboard_assert($editorMetrics['Registered users'] === $baseline['Registered users'] + 1, 'Registered user KPI uses active non-deleted accounts.');
    dashboard_assert($editorMetrics['Completions this month'] === $baseline['Completions this month'] + 1, 'Monthly completion KPI counts complete guide/user pairs.');
    dashboard_assert($editorMetrics['Published knowledge'] === $baseline['Published knowledge'] + 1, 'Knowledge KPI uses published articles.');
    dashboard_assert($editorMetrics['Approved downloads'] === $baseline['Approved downloads'] + 1, 'Download KPI applies the public download policy.');
    dashboard_assert($editorMetrics['Published community posts'] === $baseline['Published community posts'] + 1, 'Community KPI uses published legacy posts.');
    dashboard_assert($editorProjection['recentUsers'] === [] && $editorProjection['auditEvents'] === [], 'Editor projections do not query administrative identity or audit lists.');

    $categoryIndex = array_search($categoryName, $editorProjection['categoryChart']['labels'], true);
    dashboard_assert($categoryIndex !== false, 'Category chart includes the published fixture category.');
    dashboard_assert($editorProjection['categoryChart']['guides'][$categoryIndex] === 1, 'Category chart counts published guides.');
    dashboard_assert($editorProjection['categoryChart']['articles'][$categoryIndex] === 1, 'Category chart counts published knowledge.');
    dashboard_assert(count($editorProjection['categoryChart']['labels']) <= 12, 'Category chart remains bounded.');
    dashboard_assert(count($editorProjection['registrationChart']['labels']) === 6, 'Registration chart always returns six monthly buckets.');

    $adminProjection = $readModel->operational(true);
    $recentUserNames = array_column($adminProjection['recentUsers'], 'full_name');
    dashboard_assert(in_array($userName, $recentUserNames, true), 'Administrator projection includes recent user identities.');

    $_SESSION = ['user_id' => $userId, 'full_name' => $userName, 'role' => 'admin'];
    dashboard_assert(refresh_current_user_authorization($test), 'Active account authorization state refreshes successfully.');
    dashboard_assert(current_user_role() === 'editor', 'Database role replaces a stale privileged session role.');
    $test->query("UPDATE users SET status = 'disabled' WHERE id = " . $userId);
    dashboard_assert(!refresh_current_user_authorization($test), 'Disabled accounts lose dashboard authorization.');
    dashboard_assert(!is_logged_in(), 'Disabled account identity is removed from the session.');

    fwrite(STDOUT, "PASS: personal, editor, and administrator dashboard projections work.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    $test->rollback();
    $test->close();
}

if (isset($exitCode)) {
    exit($exitCode);
}
