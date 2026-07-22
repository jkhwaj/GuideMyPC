<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/search.php';

$conn = test_database_or_fail();

$statement = $conn->prepare("SELECT id, category_id FROM guides WHERE slug = 'check-windows-update-issue' LIMIT 1");
$statement->execute();
$guide = $statement->get_result()->fetch_assoc();
$statement->close();

if ($guide === null) {
    fwrite(STDERR, "FAIL: seeded search guide is not available in DB_TEST_NAME.\n");
    exit(1);
}

$guideId = (int) $guide['id'];
$categoryId = (int) $guide['category_id'];
$conn->begin_transaction();

try {
    $clampedPage = search_result_pagination(11, 999);

    if ($clampedPage !== ['page' => 2, 'per_page' => 10, 'offset' => 10, 'total_pages' => 2]) {
        throw new RuntimeException('Search pagination did not clamp an out-of-range requested page.');
    }

    $structuredResults = search_documents($conn, search_filters(['q' => 'stable internet connection', 'type' => 'guide']));
    $structuredMatch = false;

    foreach ($structuredResults as $result) {
        if ($result['title'] === 'Check a Windows update issue') {
            $structuredMatch = true;
            break;
        }
    }

    if (!$structuredMatch) {
        throw new RuntimeException('Structured guide step and tool text must be searchable.');
    }

    $hideCategory = $conn->prepare('UPDATE categories SET is_published = 0 WHERE id = ?');
    $hideCategory->bind_param('i', $categoryId);
    $hideCategory->execute();
    $hideCategory->close();
    $hiddenCategoryResults = search_documents($conn, search_filters(['q' => 'check a windows update issue', 'type' => 'guide']));
    $hiddenCategorySuggestions = search_suggestions($conn, 'check a windows');

    foreach (array_merge($hiddenCategoryResults, $hiddenCategorySuggestions) as $result) {
        if (($result['title'] ?? $result['label'] ?? '') === 'Check a Windows update issue') {
            throw new RuntimeException('Guides in unpublished categories must not appear in search projections.');
        }
    }

    $showCategory = $conn->prepare('UPDATE categories SET is_published = 1 WHERE id = ?');
    $showCategory->bind_param('i', $categoryId);
    $showCategory->execute();
    $showCategory->close();

    $update = $conn->prepare('UPDATE guides SET is_published = 0 WHERE id = ?');
    $update->bind_param('i', $guideId);
    $update->execute();
    $update->close();

    $results = search_documents($conn, search_filters(['q' => 'check a windows update issue', 'type' => 'guide']));

    foreach ($results as $result) {
        if ($result['title'] === 'Check a Windows update issue') {
            throw new RuntimeException('Unpublished guides must not appear in search results.');
        }
    }

    $conn->rollback();
    fwrite(STDOUT, "PASS: search pagination, structured guide content, and publication filtering work.\n");
} catch (Throwable $exception) {
    $conn->rollback();
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
