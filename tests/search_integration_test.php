<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/search.php';

$conn = test_database_or_fail();

$statement = $conn->prepare("SELECT id FROM guides WHERE slug = 'check-windows-update-issue' LIMIT 1");
$statement->execute();
$guide = $statement->get_result()->fetch_assoc();
$statement->close();

if ($guide === null) {
    fwrite(STDERR, "FAIL: seeded search guide is not available in DB_TEST_NAME.\n");
    exit(1);
}

$guideId = (int) $guide['id'];
$conn->begin_transaction();

try {
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
    fwrite(STDOUT, "PASS: unpublished guide is excluded from search.\n");
} catch (Throwable $exception) {
    $conn->rollback();
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
