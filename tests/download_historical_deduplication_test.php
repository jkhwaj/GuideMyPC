<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/database/runner.php';

use GuideMyPC\Features\Downloads\DownloadPolicy;

function download_dedup_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function render_download_dedup_page(string $path): string
{
    global $conn;

    ob_start();
    include dirname(__DIR__) . DIRECTORY_SEPARATOR . $path;

    return (string) ob_get_clean();
}

$test = test_database_or_fail();
$policy = new DownloadPolicy();
$token = bin2hex(random_bytes(5));
$fixtureIds = [];
$eventIds = [];
$auditRequestId = 'download-dedup-' . substr($token, 0, 9);
$previousDatabaseName = getenv('DB_NAME');

$support = [
    'Android Help' => ['https://support.google.com/android/', 'Android'],
    'Apple Support' => ['https://support.apple.com/', 'macOS, iOS, iPadOS'],
    'Microsoft Support' => ['https://support.microsoft.com/', 'Windows'],
];

try {
    $migration = file_get_contents(dirname(__DIR__) . '/database/migrations/028_historical_download_deduplication.sql');
    $seed = file_get_contents(dirname(__DIR__) . '/database/seeds/001_sample_content.sql');
    download_dedup_assert(is_string($migration) && is_string($seed), 'Historical Download cleanup migration and sample seed are readable.');

    $test->query("INSERT INTO download_verification_events (download_id, result_state, note) VALUES (999999, 'reachable', 'download-dedup-orphan-$token')");
    $preflightFailed = false;
    try {
        database_run_sql($test, $migration, '028_historical_download_deduplication.sql orphan preflight');
        throw new RuntimeException('Historical Download cleanup accepted an orphaned verification reference.');
    } catch (RuntimeException $exception) {
        $preflightFailed = str_contains($exception->getMessage(), 'failed:');
    }
    download_dedup_assert($preflightFailed, 'Historical Download cleanup fails closed when a prior migration left an orphaned reference.');
    $test->query("DELETE FROM download_verification_events WHERE note = 'download-dedup-orphan-$token'");
    $test->close();
    $test = test_database_or_fail();

    $insertDuplicate = $test->prepare(
        'INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES (?, ?, ?, ?, ?, ?)'
    );

    $android = $test->query("SELECT id FROM downloads WHERE name = 'Android Help' ORDER BY id DESC LIMIT 1")->fetch_assoc();
    download_dedup_assert($android !== null, 'Android Help seed row exists before seed-only URL repair simulation.');
    $androidLegacyId = (int) $android['id'];
    $androidLegacyUrl = 'HTTPS://SUPPORT.GOOGLE.COM:443/android/#legacy';
    $androidLegacy = $test->prepare("UPDATE downloads SET name = 'Legacy Android resource', official_url = ?, review_state = 'pending', is_published = 0 WHERE id = ?");
    $androidLegacy->bind_param('si', $androidLegacyUrl, $androidLegacyId);
    $androidLegacy->execute();
    $androidLegacy->close();
    database_run_sql($test, $seed, '001_sample_content.sql normalized URL repair');
    $androidRepaired = $test->query("SELECT COUNT(*) AS total FROM downloads WHERE name = 'Android Help'")->fetch_assoc();
    download_dedup_assert((int) $androidRepaired['total'] === 1, 'The sample seed repairs a renamed support record by normalized official URL without inserting a duplicate.');

    $microsoftSeed = $test->query("SELECT id, name, description, official_url, category, review_state, is_published, verified_at FROM downloads WHERE name = 'Microsoft Support' LIMIT 1")->fetch_assoc();
    download_dedup_assert($microsoftSeed !== null, 'Microsoft Support seed row exists before ambiguity simulation.');
    $test->query("DELETE FROM downloads WHERE name = 'Microsoft Support'");
    $ambiguous = $test->query("INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES ('Android Help', 'Ambiguous legacy record.', 'https://support.microsoft.com/', 'Android', 'pending', 1)");
    $fixtureIds[] = $test->insert_id;
    database_run_sql($test, $migration, '028_historical_download_deduplication.sql ambiguous match repair');
    $androidAndMicrosoft = $test->query("SELECT name, COUNT(*) AS total FROM downloads WHERE name IN ('Android Help', 'Microsoft Support') GROUP BY name")->fetch_all(MYSQLI_ASSOC);
    $groupCounts = array_column($androidAndMicrosoft, 'total', 'name');
    download_dedup_assert((int) ($groupCounts['Android Help'] ?? 0) === 1 && (int) ($groupCounts['Microsoft Support'] ?? 0) === 1, 'A cross-catalog legacy row is assigned by normalized name and does not suppress the missing Microsoft Support record.');
    $restoreMicrosoft = $test->prepare('DELETE FROM downloads WHERE name = ?');
    $restoreName = 'Microsoft Support';
    $restoreMicrosoft->bind_param('s', $restoreName);
    $restoreMicrosoft->execute();
    $restoreMicrosoft->close();
    $restoreMicrosoft = $test->prepare('INSERT INTO downloads (id, name, description, official_url, category, review_state, is_published, verified_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $restoreMicrosoft->bind_param('isssssis', $microsoftSeed['id'], $microsoftSeed['name'], $microsoftSeed['description'], $microsoftSeed['official_url'], $microsoftSeed['category'], $microsoftSeed['review_state'], $microsoftSeed['is_published'], $microsoftSeed['verified_at']);
    $restoreMicrosoft->execute();
    $restoreMicrosoft->close();

    foreach ($support as $name => [$url, $category]) {
        $existing = $test->prepare('SELECT id FROM downloads WHERE name = ? ORDER BY id DESC LIMIT 1');
        $existing->bind_param('s', $name);
        $existing->execute();
        $base = $existing->get_result()->fetch_assoc();
        $existing->close();
        download_dedup_assert($base !== null, $name . ' seed row exists before legacy simulation.');
        $baseId = (int) $base['id'];

        $legacy = $test->prepare("UPDATE downloads SET description = 'Legacy duplicate.', review_state = 'pending', is_published = 1 WHERE id = ?");
        $legacy->bind_param('i', $baseId);
        $legacy->execute();
        $legacy->close();

        $legacyName = '  ' . strtoupper($name) . '  ';
        $legacyUrl = 'HTTPS://' . strtoupper((string) parse_url($url, PHP_URL_HOST)) . ':443' . (string) parse_url($url, PHP_URL_PATH) . '#legacy';
        $legacyDescription = 'Legacy duplicate.';
        $pending = 'pending';
        $published = 1;
        $insertDuplicate->bind_param('sssssi', $legacyName, $legacyDescription, $legacyUrl, $category, $pending, $published);
        $insertDuplicate->execute();
        $redundantId = $insertDuplicate->insert_id;
        $fixtureIds[] = $redundantId;

        $event = $test->prepare("INSERT INTO download_verification_events (download_id, result_state, note) VALUES (?, 'reachable', ?)");
        $eventNote = 'download-dedup-event-' . $token . '-' . $name;
        $event->bind_param('is', $redundantId, $eventNote);
        $event->execute();
        $eventIds[] = $event->insert_id;
        $event->close();

        $audit = $test->prepare("INSERT INTO admin_audit_events (action, target_type, target_id, request_id) VALUES ('download.historical_dedup.test', 'download', ?, ?)");
        $redundantTarget = (string) $redundantId;
        $audit->bind_param('ss', $redundantTarget, $auditRequestId);
        $audit->execute();
        $audit->close();
    }
    $insertDuplicate->close();

    $malware = $test->query("SELECT id FROM downloads WHERE name = 'Malwarebytes' LIMIT 1")->fetch_assoc();
    download_dedup_assert($malware !== null, 'Malwarebytes exists before duplicate simulation.');
    $malwareCanonicalId = (int) $malware['id'];
    $malwareDuplicate = $test->prepare("INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES ('Legacy malware entry', 'Legacy duplicate.', 'HTTPS://WWW.MALWAREBYTES.COM:443/mwb-download#legacy', 'Windows', 'pending', 1)");
    $malwareDuplicate->execute();
    $fixtureIds[] = $malwareDuplicate->insert_id;
    $malwareDuplicate->close();

    $customName = 'Custom Microsoft support path ' . $token;
    $customUrl = 'https://support.microsoft.com/custom/' . $token;
    $custom = $test->prepare("INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES (?, 'Custom entry.', ?, 'Windows', 'approved', 1)");
    $custom->bind_param('ss', $customName, $customUrl);
    $custom->execute();
    $fixtureIds[] = $custom->insert_id;
    $custom->close();

    $conflicting = $test->prepare("INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES ('Android Help', 'Conflicting legacy name and URL.', 'https://support.microsoft.com/', 'Android', 'pending', 1)");
    $conflicting->execute();
    $conflictingId = $conflicting->insert_id;
    $fixtureIds[] = $conflictingId;
    $conflicting->close();

    $chrome = $test->query("SELECT id FROM downloads WHERE name = 'Google Chrome' LIMIT 1")->fetch_assoc();
    if ($chrome === null) {
        $chromeFixture = $test->prepare("INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES ('Google Chrome', 'Fast and secure web browser from Google.', 'https://www.google.com/chrome/', 'Browser', 'pending', 1)");
        $chromeFixture->execute();
        $fixtureIds[] = $chromeFixture->insert_id;
        $chromeFixture->close();
    }

    database_run_sql($test, $migration, '028_historical_download_deduplication.sql first repeat');
    database_run_sql($test, $migration, '028_historical_download_deduplication.sql second repeat');
    database_run_sql($test, $seed, '001_sample_content.sql first repeat');
    database_run_sql($test, $seed, '001_sample_content.sql second repeat');

    $normalizedSupportUrls = [];
    foreach ($support as $name => [$url, $category]) {
        $statement = $test->prepare('SELECT id, description, official_url, category, review_state, is_published FROM downloads WHERE LOWER(TRIM(name)) = ?');
        $normalizedName = $policy->normalizedName($name);
        $statement->bind_param('s', $normalizedName);
        $statement->execute();
        $rows = $statement->get_result();
        download_dedup_assert($rows->num_rows === 1, $name . ' has exactly one normalized product name after cleanup and seed repeats.');
        $download = $rows->fetch_assoc();
        $statement->close();
        download_dedup_assert($policy->normalizedUrl($download['official_url']) === $policy->normalizedUrl($url), $name . ' retains its correct normalized official URL.');
        $normalizedSupportUrls[] = $policy->normalizedUrl($download['official_url']);
        download_dedup_assert($download['review_state'] === 'approved' && (int) $download['is_published'] === 1, $name . ' is approved and public after cleanup.');
        download_dedup_assert($download['category'] === $category && mb_strlen((string) $download['description']) > 40, $name . ' has complete platform and description data.');

        $event = $test->prepare('SELECT COUNT(*) AS total FROM download_verification_events WHERE download_id = ?');
        $canonicalId = (int) $download['id'];
        $event->bind_param('i', $canonicalId);
        $event->execute();
        $eventCount = $event->get_result()->fetch_assoc();
        $event->close();
        download_dedup_assert((int) $eventCount['total'] >= 1, $name . ' verification dependency is reassigned to the canonical record.');

        $audit = $test->prepare("SELECT COUNT(*) AS total FROM admin_audit_events WHERE action = 'download.historical_dedup.test' AND target_type = 'download' AND target_id = ?");
        $targetId = (string) $canonicalId;
        $audit->bind_param('s', $targetId);
        $audit->execute();
        $auditCount = $audit->get_result()->fetch_assoc();
        $audit->close();
        download_dedup_assert((int) $auditCount['total'] >= 1, $name . ' audit reference is reassigned to the canonical record.');
    }
    download_dedup_assert(count($normalizedSupportUrls) === count(array_unique($normalizedSupportUrls)), 'Each retained support resource has one distinct normalized official URL.');

    $malwareCount = $test->query("SELECT COUNT(*) AS total FROM downloads WHERE name = 'Malwarebytes'")->fetch_assoc();
    download_dedup_assert((int) $malwareCount['total'] === 1, 'Malwarebytes remains exactly once after cleanup.');
    $malwareAfter = $test->query("SELECT id FROM downloads WHERE name = 'Malwarebytes' LIMIT 1")->fetch_assoc();
    download_dedup_assert((int) $malwareAfter['id'] === $malwareCanonicalId, 'The existing approved Malwarebytes ID is retained.');
    $customAfter = $test->prepare('SELECT official_url FROM downloads WHERE name = ?');
    $customAfter->bind_param('s', $customName);
    $customAfter->execute();
    $customRow = $customAfter->get_result()->fetch_assoc();
    $customAfter->close();
    download_dedup_assert(($customRow['official_url'] ?? null) === $customUrl, 'Unrelated administrator-created support-domain entries are preserved.');
    $conflictingAfter = $test->prepare('SELECT COUNT(*) AS total FROM downloads WHERE id = ?');
    $conflictingAfter->bind_param('i', $conflictingId);
    $conflictingAfter->execute();
    $conflictingCount = $conflictingAfter->get_result()->fetch_assoc();
    $conflictingAfter->close();
    download_dedup_assert((int) $conflictingCount['total'] === 0, 'A conflicting legacy row is assigned to its normalized product name before URL matching and removed safely.');

    $distinct = $test->query("SELECT COUNT(*) AS total FROM downloads WHERE name IN ('CPU-Z', 'HWMonitor', 'Microsoft Support', 'Windows 11 Download')")->fetch_assoc();
    download_dedup_assert((int) $distinct['total'] === 4, 'CPU-Z, HWMonitor, Microsoft Support, and Windows 11 Download remain distinct resources.');
    $chromeCount = $test->query("SELECT COUNT(*) AS total FROM downloads WHERE name = 'Google Chrome'")->fetch_assoc();
    download_dedup_assert((int) $chromeCount['total'] === 1, 'Google Chrome is preserved.');

    require_once dirname(__DIR__) . '/bootstrap/web.php';
    $testDatabaseName = (string) config_value('DB_TEST_NAME');
    $guideMyPcEnvironment['DB_NAME'] = $testDatabaseName;
    putenv('DB_NAME=' . $testDatabaseName);
    require_once dirname(__DIR__) . '/config.php';
    $_SESSION['user_id'] = 1;
    $_SESSION['full_name'] = 'Test Admin';
    $_SESSION['role'] = 'admin';
    $page = render_download_dedup_page('downloads.php');
    $adminPage = render_download_dedup_page('admin_downloads.php');
    foreach (array_keys($support) as $name) {
        download_dedup_assert(substr_count($page, '<h3>' . e($name) . '</h3>') === 1, $name . ' renders once on the public Downloads page.');
        download_dedup_assert(substr_count($adminPage, '<td>' . e($name) . '</td>') === 1, $name . ' renders once in the admin table.');
    }
    download_dedup_assert(str_contains($adminPage, 'target="_blank" rel="noopener noreferrer"'), 'Admin Download links retain safe new-tab attributes.');
    preg_match_all('/<td>([0-9]+)<\/td>/', $adminPage, $idMatches);
    $adminIds = array_map('intval', $idMatches[1] ?? []);
    $sortedIds = $adminIds;
    rsort($sortedIds, SORT_NUMERIC);
    download_dedup_assert($adminIds === $sortedIds, 'Manage Downloads retains descending ID sorting.');

    fwrite(STDOUT, "PASS: historical Download duplicates, references, seeds, public cards, and admin ordering are repaired.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    foreach ($eventIds as $eventId) {
        $statement = $test->prepare('DELETE FROM download_verification_events WHERE id = ?');
        $statement->bind_param('i', $eventId);
        $statement->execute();
        $statement->close();
    }
    $audit = $test->prepare("DELETE FROM admin_audit_events WHERE action = 'download.historical_dedup.test' AND request_id = ?");
    $audit->bind_param('s', $auditRequestId);
    $audit->execute();
    $audit->close();
    foreach ($fixtureIds as $id) {
        $statement = $test->prepare('DELETE FROM downloads WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
    }
    if ($previousDatabaseName === false) {
        putenv('DB_NAME');
    } else {
        putenv('DB_NAME=' . $previousDatabaseName);
    }
    $test->close();
}

if (isset($exitCode)) {
    exit($exitCode);
}
