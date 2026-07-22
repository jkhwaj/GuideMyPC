<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';

function guide_library_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function render_guide_library_page(int $page, string $search = ''): string
{
    global $conn;

    $_GET = ['page' => (string) $page, 'search' => $search];
    ob_start();
    include dirname(__DIR__) . '/guides.php';

    return (string) ob_get_clean();
}

$test = test_database_or_fail();
$token = bin2hex(random_bytes(5));
$slug = 'guide-library-page-' . $token;

try {
    $category = $test->query("SELECT id FROM categories WHERE slug = 'windows' LIMIT 1")->fetch_assoc();
    guide_library_assert($category !== null, 'The Windows seed category is required for guide library pagination tests.');
    $categoryId = (int) $category['id'];
    $title = 'Guide library page fixture ' . $token;
    $insert = $test->prepare('INSERT INTO guides (category_id, title, slug, description, is_published) VALUES (?, ?, ?, ?, 1)');
    $description = 'Temporary public guide library pagination fixture.';
    $insert->bind_param('isss', $categoryId, $title, $slug, $description);
    $insert->execute();
    $insert->close();

    require_once dirname(__DIR__) . '/bootstrap/web.php';
    $guideMyPcEnvironment['DB_NAME'] = config_value('DB_TEST_NAME');
    require_once dirname(__DIR__) . '/config.php';
    $firstPage = render_guide_library_page(1);
    $lastPage = render_guide_library_page(999);
    $structuredSearch = render_guide_library_page(1, 'stable internet connection');

    guide_library_assert(str_contains($firstPage, 'Page 1 of 2') && str_contains($firstPage, 'Next'), 'Guide library renders a bounded first page with a next link.');
    guide_library_assert(str_contains($lastPage, 'Page 2 of 2') && str_contains($lastPage, 'Previous') && !str_contains($lastPage, '>Next<'), 'Guide library clamps out-of-range pages to the final page.');
    guide_library_assert(str_contains($structuredSearch, 'Check a Windows update issue'), 'Guide library filters search structured guide step and tool content.');

    fwrite(STDOUT, "PASS: public guide library pagination and structured filtering work.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    $delete = $test->prepare('DELETE FROM guides WHERE slug = ?');
    $delete->bind_param('s', $slug);
    $delete->execute();
    $delete->close();
    $test->close();
}

if (isset($exitCode)) {
    exit($exitCode);
}
