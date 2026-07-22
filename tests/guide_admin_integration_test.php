<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_once dirname(__DIR__) . '/includes/guides.php';

use GuideMyPC\Features\Guides\GuideAdminRepository;
use GuideMyPC\Features\Guides\GuideAdminService;

function guide_admin_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$test = test_database_or_fail();
$token = bin2hex(random_bytes(5));
$repository = new GuideAdminRepository($test);
$service = new GuideAdminService($test);
$guideId = 0;
$userId = 0;
$_SESSION = [];

try {
    $category = $test->query('SELECT id FROM categories ORDER BY id LIMIT 1')->fetch_assoc();
    guide_admin_assert($category !== null, 'A seeded category is required for Guide administration tests.');
    $categoryId = (int) $category['id'];
    $valid = $service->validate([
        'category' => $categoryId,
        'title' => 'Guide Admin Test ' . $token,
        'slug' => 'guide-admin-test-' . $token,
        'description' => 'A guide administration fixture.',
        'difficulty' => 'Beginner',
        'estimated_time' => '5 minutes',
        'risk_level' => 'Low',
        'platform_version' => 'Windows 11',
        'required_tools' => 'Settings',
        'prerequisites' => 'Sign in.',
        'backup_warning' => 'No changes are made.',
        'next_actions' => 'Confirm the result.',
        'video_url' => 'https://youtu.be/M7lc1UVf-VE',
        'last_reviewed_at' => '2026-07-18',
        'is_published' => '0',
        'featured_order' => '2',
        'sources' => [
            ['title' => 'Microsoft Support', 'official_url' => 'https://support.microsoft.com/test-' . $token],
            ['title' => 'Apple Support', 'official_url' => 'https://support.apple.com/test-' . $token],
        ],
        'steps' => [['title' => 'Open Settings', 'text' => 'Open Windows Settings.', 'expected_result' => 'Settings opens.']],
    ]);
    guide_admin_assert($valid['errors'] === [], 'Valid Guide administration input passes validation.');
    guide_admin_assert($valid['values']['is_published'] === 0 && count($valid['values']['sources']) === 2 && $valid['values']['sources'][1]['title'] === 'Apple Support', 'Draft state and ordered official sources normalize correctly.');
    guide_admin_assert($valid['values']['video_url'] === 'https://www.youtube.com/watch?v=M7lc1UVf-VE', 'Guide administration stores a canonical YouTube watch URL.');
    $unapprovedSource = $service->validate([
        'category' => $categoryId,
        'title' => 'Unapproved Source ' . $token,
        'slug' => 'unapproved-source-' . $token,
        'is_published' => '0',
        'sources' => [['title' => 'Unapproved', 'official_url' => 'https://example.test/' . $token]],
        'steps' => [['text' => 'Test the source policy.']],
    ]);
    guide_admin_assert($unapprovedSource['errors'] !== [], 'Guide administration rejects unapproved source hosts.');
    $test->query("UPDATE trusted_source_domains SET is_active = 0 WHERE domain = 'support.microsoft.com'");
    $inactiveSource = $service->validate([
        'category' => $categoryId,
        'title' => 'Inactive Source ' . $token,
        'slug' => 'inactive-source-' . $token,
        'is_published' => '0',
        'sources' => [['title' => 'Microsoft Support', 'official_url' => 'https://support.microsoft.com/test-' . $token]],
        'steps' => [['text' => 'Test inactive source rejection.']],
    ]);
    guide_admin_assert($inactiveSource['errors'] !== [], 'Guide administration rejects inactive source domains.');
    $test->query("UPDATE trusted_source_domains SET is_active = 1 WHERE domain = 'support.microsoft.com'");
    $invalid = $service->validate(['category' => 'invalid', 'title' => '', 'slug' => 'Bad Slug', 'video_url' => 'http://example.test', 'is_published' => 'yes', 'featured_order' => '-1', 'sources' => [['title' => '', 'official_url' => 'http://example.test']], 'steps' => []]);
    guide_admin_assert($invalid['errors'] !== [], 'Malformed Guide administration input fails closed.');

    $guideId = $service->create($valid['values']);
    $created = $repository->find($guideId);
    guide_admin_assert($created !== null && (int) $created['is_published'] === 0 && (int) $created['featured_order'] === 2, 'Guide create stores draft publication and featured order.');
    guide_admin_assert($created['video_url'] === 'https://www.youtube.com/watch?v=M7lc1UVf-VE' && guide_youtube_embed_url($created['video_url']) !== null, 'Guide video URLs remain renderable after create.');
    guide_admin_assert(count($repository->sources($guideId)) === 2 && $repository->sources($guideId)[1]['title'] === 'Apple Support', 'Guide create preserves official source order.');
    guide_admin_assert($repository->slugExists($valid['values']['slug']), 'Guide duplicate slug lookup detects existing guides.');

    $listing = $repository->paginate(['q' => $token, 'status' => 'unpublished', 'category' => $categoryId, 'sort' => 'title', 'direction' => 'asc', 'per_page' => 10]);
    guide_admin_assert($listing['total'] === 1 && (int) $listing['rows'][0]['id'] === $guideId, 'Guide listing filters and bounds administrative results.');
    $updated = $valid['values'];
    $updated['is_published'] = 1;
    $updated['sources'] = [
        ['title' => 'Apple Support', 'official_url' => 'https://support.apple.com/updated-' . $token],
        ['title' => 'Updated Support', 'official_url' => 'https://support.microsoft.com/updated-' . $token],
    ];
    guide_admin_assert($service->update($guideId, $updated), 'Guide update succeeds.');
    guide_admin_assert((int) $repository->find($guideId)['is_published'] === 1 && $repository->sources($guideId)[0]['title'] === 'Apple Support' && $repository->sources($guideId)[1]['title'] === 'Updated Support', 'Guide update changes publication and preserves submitted source order.');

    $email = 'guide-admin-' . $token . '@example.test';
    $name = 'Guide Admin Dependency Test';
    $password = password_hash('GuideAdmin1!', PASSWORD_DEFAULT);
    $insertUser = $test->prepare('INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)');
    $insertUser->bind_param('sss', $name, $email, $password);
    $insertUser->execute();
    $userId = $insertUser->insert_id;
    $insertUser->close();
    $favorite = $test->prepare('INSERT INTO favorites (user_id, guide_id) VALUES (?, ?)');
    $favorite->bind_param('ii', $userId, $guideId);
    $favorite->execute();
    $favorite->close();
    $blocked = $service->delete($guideId);
    guide_admin_assert($blocked['status'] === 'blocked' && $blocked['dependencies']['favorites'] === 1, 'Guide deletion blocks durable user dependencies.');
    $test->query('DELETE FROM favorites WHERE guide_id = ' . $guideId);
    $deleted = $service->delete($guideId);
    guide_admin_assert($deleted['status'] === 'deleted' && $repository->find($guideId) === null, 'Unused Guide deletion removes owned Guide data.');
    $audit = $test->query("SELECT COUNT(*) AS total FROM admin_audit_events WHERE target_type = 'guide' AND target_id = '" . $guideId . "'")->fetch_assoc();
    guide_admin_assert((int) $audit['total'] >= 3, 'Guide CRUD writes audit events.');
    fwrite(STDOUT, "PASS: guide administration validation, CRUD, sources, listing, dependencies, and audit work.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
} finally {
    if ($guideId > 0) {
        $test->query('DELETE FROM guide_sources WHERE guide_id = ' . $guideId);
        $test->query('DELETE FROM guide_tools WHERE guide_id = ' . $guideId);
        $test->query('DELETE FROM guides WHERE id = ' . $guideId);
    }

    if ($userId > 0) {
        $test->query('DELETE FROM users WHERE id = ' . $userId);
    }

    $test->query("UPDATE trusted_source_domains SET is_active = 1 WHERE domain = 'support.microsoft.com'");
    $test->query("DELETE FROM admin_audit_events WHERE target_type = 'guide' AND metadata_json LIKE '%guide-admin-test-%'");
}
