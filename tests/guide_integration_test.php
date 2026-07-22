<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_once dirname(__DIR__) . '/includes/guides.php';

$conn = test_database_or_fail();

$guide = $conn->query("SELECT id FROM guides WHERE slug = 'check-windows-update-issue' LIMIT 1")->fetch_assoc();

if ($guide === null) {
    fwrite(STDERR, "FAIL: seeded structured guide is not available in DB_TEST_NAME.\n");
    exit(1);
}

$guideId = (int) $guide['id'];
$conn->begin_transaction();

try {
    $categoryId = (int) $conn->query('SELECT id FROM categories ORDER BY id LIMIT 1')->fetch_assoc()['id'];

    if (!guide_exists($conn, $guideId) || !guide_category_exists($conn, $categoryId) || guide_category_exists($conn, PHP_INT_MAX)) {
        throw new RuntimeException('Guide and category validation did not return the expected result.');
    }

    $publicGuide = guide_public_by_id($conn, $guideId, 'check-windows-update-issue');
    $publicStep = $conn->query('SELECT id FROM guide_steps WHERE guide_id = ' . $guideId . ' LIMIT 1')->fetch_assoc();

    if ($publicGuide === null || $publicStep === null || guide_public_step_by_id($conn, (int) $publicStep['id']) === null) {
        throw new RuntimeException('Published guide and step lookups did not return public content.');
    }

    $conn->query('UPDATE categories SET is_published = 0 WHERE id = ' . $categoryId);

    if (guide_public_by_id($conn, $guideId, 'check-windows-update-issue') !== null || guide_public_step_by_id($conn, (int) $publicStep['id']) !== null) {
        throw new RuntimeException('Guide actions must reject guides hidden through an unpublished category.');
    }

    $conn->query('UPDATE categories SET is_published = 1 WHERE id = ' . $categoryId);

    guide_replace_steps($conn, $guideId, guide_normalize_steps([
        ['text' => 'Test the first action.', 'title' => 'Test title'],
        ['text' => 'Test the second action.', 'title' => 'Second title'],
        ['text' => 'Test the third action.', 'title' => 'Third title'],
    ]));
    guide_replace_tools($conn, $guideId, "Test tool\nTest tool\nSecond tool");
    $step = $conn->query('SELECT step_title FROM guide_steps WHERE guide_id = ' . $guideId . ' ORDER BY step_number LIMIT 1')->fetch_assoc();
    $toolCount = (int) $conn->query('SELECT COUNT(*) AS total FROM guide_tools WHERE guide_id = ' . $guideId)->fetch_assoc()['total'];

    if ($step === null || $step['step_title'] !== 'Test title' || $toolCount !== 2) {
        throw new RuntimeException('Structured guide fields were not saved as expected.');
    }

    $email = 'guide-progress-' . bin2hex(random_bytes(4)) . '@example.test';
    $password = password_hash('GuideProgress1!', PASSWORD_DEFAULT);
    $name = 'Guide Progress Test';
    $userInsert = $conn->prepare('INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)');
    $userInsert->bind_param('sss', $name, $email, $password);
    $userInsert->execute();
    $progressUserId = $userInsert->insert_id;
    $userInsert->close();
    $stepRows = $conn->query('SELECT id FROM guide_steps WHERE guide_id = ' . $guideId . ' ORDER BY step_number')->fetch_all(MYSQLI_ASSOC);
    $originalStepIds = array_map('intval', array_column($stepRows, 'id'));
    $progressInsert = $conn->prepare('INSERT INTO user_progress (user_id, guide_step_id, completed) VALUES (?, ?, 1)');

    foreach ($originalStepIds as $stepId) {
        $progressInsert->bind_param('ii', $progressUserId, $stepId);
        $progressInsert->execute();
    }

    $progressInsert->close();
    $reordered = guide_normalize_steps([
        ['id' => $originalStepIds[2], 'text' => 'Updated third action.', 'title' => 'Third title'],
        ['id' => $originalStepIds[0], 'text' => 'Test the first action.', 'title' => 'Test title'],
        ['text' => 'A newly inserted action.', 'title' => 'Inserted title'],
        ['id' => $originalStepIds[1], 'text' => 'Test the second action.', 'title' => 'Second title'],
    ]);
    $changes = guide_sync_steps($conn, $guideId, $reordered);
    $reorderedIds = array_map(
        'intval',
        array_column($conn->query('SELECT id FROM guide_steps WHERE guide_id = ' . $guideId . ' ORDER BY step_number')->fetch_all(MYSQLI_ASSOC), 'id')
    );
    $progressCount = (int) $conn->query('SELECT COUNT(*) AS total FROM user_progress WHERE user_id = ' . $progressUserId)->fetch_assoc()['total'];

    if ($reorderedIds[0] !== $originalStepIds[2] || $reorderedIds[1] !== $originalStepIds[0] || $reorderedIds[3] !== $originalStepIds[1] || count($changes['added']) !== 1 || $progressCount !== 3) {
        throw new RuntimeException('Step reorder/add did not preserve stable IDs and existing progress.');
    }

    $withoutFirstStep = guide_normalize_steps([
        ['id' => $originalStepIds[2], 'text' => 'Updated third action.', 'title' => 'Third title'],
        ['id' => $changes['added'][0], 'text' => 'A newly inserted action.', 'title' => 'Inserted title'],
        ['id' => $originalStepIds[1], 'text' => 'Test the second action.', 'title' => 'Second title'],
    ]);
    $deletion = guide_sync_steps($conn, $guideId, $withoutFirstStep);
    $remainingProgressIds = array_map(
        'intval',
        array_column($conn->query('SELECT guide_step_id FROM user_progress WHERE user_id = ' . $progressUserId . ' ORDER BY guide_step_id')->fetch_all(MYSQLI_ASSOC), 'guide_step_id')
    );

    if ($deletion['deleted'] !== [$originalStepIds[0]] || $deletion['deleted_progress'] !== 1 || count($remainingProgressIds) !== 2 || in_array($originalStepIds[0], $remainingProgressIds, true)) {
        throw new RuntimeException('Removing a step did not delete only that step progress.');
    }

    $currentRows = $conn->query('SELECT id, step_number, step_text FROM guide_steps WHERE guide_id = ' . $guideId . ' ORDER BY step_number')->fetch_all(MYSQLI_ASSOC);
    $noOp = guide_sync_steps($conn, $guideId, $withoutFirstStep);

    if ($noOp !== ['added' => [], 'updated' => [], 'deleted' => [], 'deleted_progress' => 0]) {
        throw new RuntimeException('An unchanged step submission performed writes.');
    }

    if (array_map('intval', array_column($currentRows, 'step_number')) !== [1, 2, 3]) {
        throw new RuntimeException('Mixed step synchronization did not produce contiguous ordering.');
    }

    if (guide_normalize_steps([['id' => $originalStepIds[1], 'text' => '']]) !== []) {
        throw new RuntimeException('A blank persisted step action was not rejected.');
    }

    $otherTitle = 'Cross-guide step test';
    $otherSlug = 'cross-guide-step-' . bin2hex(random_bytes(4));
    $otherGuideInsert = $conn->prepare('INSERT INTO guides (category_id, title, slug, is_published) VALUES (?, ?, ?, 0)');
    $otherGuideInsert->bind_param('iss', $categoryId, $otherTitle, $otherSlug);
    $otherGuideInsert->execute();
    $otherGuideId = $otherGuideInsert->insert_id;
    $otherGuideInsert->close();
    guide_replace_steps($conn, $otherGuideId, guide_normalize_steps([['text' => 'Foreign step.']]));
    $foreignStepId = (int) $conn->query('SELECT id FROM guide_steps WHERE guide_id = ' . $otherGuideId)->fetch_assoc()['id'];
    $beforeTampering = $conn->query('SELECT id, step_number, step_text FROM guide_steps WHERE guide_id = ' . $guideId . ' ORDER BY step_number')->fetch_all(MYSQLI_ASSOC);

    try {
        guide_sync_steps($conn, $guideId, guide_normalize_steps([['id' => $foreignStepId, 'text' => 'Tampered step.']]));
        throw new RuntimeException('Cross-guide step ID tampering was accepted.');
    } catch (DomainException) {
        // Expected: the complete edit is rejected before any step writes.
    }

    try {
        guide_sync_steps($conn, $guideId, guide_normalize_steps([
            ['id' => $originalStepIds[1], 'text' => 'Duplicate step one.'],
            ['id' => $originalStepIds[1], 'text' => 'Duplicate step two.'],
        ]));
        throw new RuntimeException('Duplicate submitted step IDs were accepted.');
    } catch (DomainException) {
        // Expected: duplicate IDs are rejected before writes.
    }

    $afterTampering = $conn->query('SELECT id, step_number, step_text FROM guide_steps WHERE guide_id = ' . $guideId . ' ORDER BY step_number')->fetch_all(MYSQLI_ASSOC);

    if ($afterTampering !== $beforeTampering) {
        throw new RuntimeException('Rejected step tampering changed guide steps.');
    }

    $guestEmail = 'guide-guest-merge-' . bin2hex(random_bytes(4)) . '@example.test';
    $guestName = 'Guide Guest Merge Test';
    $guestInsert = $conn->prepare('INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)');
    $guestInsert->bind_param('sss', $guestName, $guestEmail, $password);
    $guestInsert->execute();
    $guestUserId = $guestInsert->insert_id;
    $guestInsert->close();
    $validStepId = (int) $beforeTampering[0]['id'];
    guide_merge_guest_progress($conn, $guestUserId, $guideId, [$validStepId, $foreignStepId, PHP_INT_MAX, $validStepId]);
    $guestProgress = $conn->query('SELECT guide_step_id FROM user_progress WHERE user_id = ' . $guestUserId)->fetch_all(MYSQLI_ASSOC);

    if (count($guestProgress) !== 1 || (int) $guestProgress[0]['guide_step_id'] !== $validStepId) {
        throw new RuntimeException('Guest progress merge accepted a stale or cross-guide step ID.');
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
