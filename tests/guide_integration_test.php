<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_once dirname(__DIR__) . '/includes/guides.php';

$guide = $conn->query("SELECT id FROM guides WHERE slug = 'check-windows-update-issue' LIMIT 1")->fetch_assoc();

if ($guide === null) {
    fwrite(STDOUT, "SKIP: seeded structured guide is not available.\n");
    exit(0);
}

$guideId = (int) $guide['id'];
$conn->begin_transaction();

try {
    $categoryId = (int) $conn->query('SELECT id FROM categories ORDER BY id LIMIT 1')->fetch_assoc()['id'];

    if (!guide_exists($conn, $guideId) || !guide_category_exists($conn, $categoryId) || guide_category_exists($conn, PHP_INT_MAX)) {
        throw new RuntimeException('Guide and category validation did not return the expected result.');
    }

    guide_replace_steps($conn, $guideId, [[
        'text' => 'Test the structured action.',
        'title' => 'Test title',
        'expected_result' => 'A safe expected result.',
        'warning_text' => 'A test warning.',
        'recovery_text' => 'A test recovery path.',
        'image_url' => null,
        'image_alt' => '',
        'video_timestamp' => null,
    ]]);
    guide_replace_tools($conn, $guideId, "Test tool\nTest tool\nSecond tool");
    $step = $conn->query('SELECT step_title, expected_result, warning_text, recovery_text FROM guide_steps WHERE guide_id = ' . $guideId)->fetch_assoc();
    $toolCount = (int) $conn->query('SELECT COUNT(*) AS total FROM guide_tools WHERE guide_id = ' . $guideId)->fetch_assoc()['total'];

    if ($step === null || $step['step_title'] !== 'Test title' || $step['expected_result'] !== 'A safe expected result.' || $toolCount !== 2) {
        throw new RuntimeException('Structured guide fields were not saved as expected.');
    }

    $_SESSION['user_id'] = 1;
    admin_audit($conn, 'guide.test', 'guide', $guideId, ['slug' => 'test-guide', 'csrf_token' => 'not-stored']);
    $audit = $conn->query("SELECT metadata_json FROM admin_audit_events WHERE action = 'guide.test' ORDER BY id DESC LIMIT 1")->fetch_assoc();
    $metadata = $audit === null ? null : json_decode($audit['metadata_json'], true);

    if (!is_array($metadata) || ($metadata['slug'] ?? null) !== 'test-guide' || ($metadata['csrf_token'] ?? null) !== '[redacted]') {
        throw new RuntimeException('Guide audit metadata was not stored safely.');
    }

    $conn->rollback();
    fwrite(STDOUT, "PASS: structured guide validation, updates, and audit records are transactional.\n");
} catch (Throwable $exception) {
    $conn->rollback();
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
