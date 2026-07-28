<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_once dirname(__DIR__) . '/database/runner.php';

use GuideMyPC\Features\Downloads\DownloadAdminService;
use GuideMyPC\Features\Downloads\DownloadPolicy;

function download_catalog_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function rendered_downloads_page(): string
{
    global $conn;

    ob_start();
    include dirname(__DIR__) . '/downloads.php';

    return (string) ob_get_clean();
}

$test = test_database_or_fail();
$policy = new DownloadPolicy();
$service = new DownloadAdminService($test);
$token = bin2hex(random_bytes(5));
$fixtureIds = [];
$previousDatabaseName = getenv('DB_NAME');
$expected = [
    'Malwarebytes' => 'https://www.malwarebytes.com/mwb-download',
    'Rufus' => 'https://rufus.ie/',
    'CPU-Z' => 'https://www.cpuid.com/softwares/cpu-z.html',
    'CrystalDiskInfo' => 'https://crystalmark.info/en/software/CrystalDiskInfo/',
    'HWMonitor' => 'https://www.cpuid.com/softwares/HWmonitor.html',
    'MemTest86' => 'https://www.memtest86.com/',
    'Ninite' => 'https://ninite.com/',
    'Windows 11 Download' => 'https://www.microsoft.com/software-download/windows11',
    'Intel Driver & Support Assistant' => 'https://www.intel.com/content/www/us/en/support/detect.html',
    'AMD Drivers and Support' => 'https://www.amd.com/en/support/download/drivers.html',
    'NVIDIA Drivers' => 'https://www.nvidia.com/en-us/drivers/',
    'Samsung Magician' => 'https://semiconductor.samsung.com/consumer-storage/support/tools/',
];

try {
    $migration = file_get_contents(dirname(__DIR__) . '/database/migrations/027_official_download_catalog.sql');
    $seed = file_get_contents(dirname(__DIR__) . '/database/seeds/004_official_download_catalog.sql');
    download_catalog_assert(is_string($migration) && is_string($seed), 'Catalog migration and seed are readable.');
    database_run_sql($test, $migration, '027_official_download_catalog.sql repeat');
    database_run_sql($test, $migration, '027_official_download_catalog.sql second repeat');
    database_run_sql($test, $seed, '004_official_download_catalog.sql first repeat');
    database_run_sql($test, $seed, '004_official_download_catalog.sql second repeat');

    foreach ($expected as $name => $url) {
        $statement = $test->prepare('SELECT id, name, official_url, review_state, is_published FROM downloads WHERE LOWER(TRIM(name)) = ?');
        $normalizedName = $policy->normalizedName($name);
        $statement->bind_param('s', $normalizedName);
        $statement->execute();
        $rows = $statement->get_result();
        download_catalog_assert($rows->num_rows === 1, $name . ' appears exactly once.');
        $download = $rows->fetch_assoc();
        $statement->close();
        download_catalog_assert($policy->normalizedUrl($download['official_url']) === $policy->normalizedUrl($url), $name . ' keeps the approved normalized official URL.');
        download_catalog_assert($policy->isPublic($download), $name . ' passes public Download eligibility.');
        download_catalog_assert(str_starts_with($download['official_url'], 'https://'), $name . ' uses HTTPS.');
    }

    $legacyUrl = 'HTTPS://WWW.MALWAREBYTES.COM:443/mwb-download/#legacy';
    $legacy = $test->prepare("UPDATE downloads SET name = 'Legacy security entry', official_url = ?, review_state = 'pending', is_published = 0 WHERE name = 'Malwarebytes'");
    $legacy->bind_param('s', $legacyUrl);
    $legacy->execute();
    $legacy->close();
    $rufusLegacyUrl = 'HTTPS://RUFUS.IE:443/#legacy';
    $rufusLegacy = $test->prepare("UPDATE downloads SET name = 'Legacy USB entry', official_url = ?, review_state = 'pending', is_published = 0 WHERE name = 'Rufus'");
    $rufusLegacy->bind_param('s', $rufusLegacyUrl);
    $rufusLegacy->execute();
    $rufusLegacy->close();
    $duplicateLegacy = $test->prepare("INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES ('Another legacy security entry', 'Legacy duplicate.', ?, 'Windows', 'pending', 0)");
    $duplicateLegacy->bind_param('s', $legacyUrl);
    $duplicateLegacy->execute();
    $fixtureIds[] = $duplicateLegacy->insert_id;
    $duplicateLegacy->close();
    $pathCaseName = 'Catalog path case ' . $token;
    $pathCaseUrl = 'https://www.cpuid.com/softwares/CPU-Z.html';
    $pathCase = $test->prepare('INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES (?, ?, ?, ?, ?, ?)');
    $pathCaseDescription = 'Path case fixture.';
    $pathCaseCategory = 'Windows';
    $pathCaseState = 'approved';
    $pathCasePublished = 1;
    $pathCase->bind_param('sssssi', $pathCaseName, $pathCaseDescription, $pathCaseUrl, $pathCaseCategory, $pathCaseState, $pathCasePublished);
    $pathCase->execute();
    $fixtureIds[] = $pathCase->insert_id;
    $pathCase->close();
    database_run_sql($test, $migration, '027_official_download_catalog.sql legacy URL repair');
    $malwarebytes = $test->query("SELECT name, official_url, review_state, is_published FROM downloads WHERE name = 'Malwarebytes'")->fetch_all(MYSQLI_ASSOC);
    download_catalog_assert(count($malwarebytes) === 1 && $policy->normalizedUrl($malwarebytes[0]['official_url']) === $policy->normalizedUrl($expected['Malwarebytes']) && $policy->isPublic($malwarebytes[0]), 'Migration repairs the existing Malwarebytes record through normalized official URL matching.');
    $rufus = $test->query("SELECT name, official_url, review_state, is_published FROM downloads WHERE name = 'Rufus'")->fetch_all(MYSQLI_ASSOC);
    download_catalog_assert(count($rufus) === 1 && $policy->normalizedUrl($rufus[0]['official_url']) === $policy->normalizedUrl($expected['Rufus']) && $policy->isPublic($rufus[0]), 'Migration repairs a trailing-slash official URL through normalized matching.');
    $pathCase = $test->prepare('SELECT official_url FROM downloads WHERE id = ?');
    $pathCase->bind_param('i', $fixtureIds[1]);
    $pathCase->execute();
    $pathCaseRow = $pathCase->get_result()->fetch_assoc();
    $pathCase->close();
    download_catalog_assert(($pathCaseRow['official_url'] ?? null) === $pathCaseUrl, 'Migration preserves a custom URL that differs only by path case.');

    $cpuAndMonitor = $test->query("SELECT COUNT(*) AS total FROM downloads WHERE name IN ('CPU-Z', 'HWMonitor')")->fetch_assoc();
    $microsoftAndWindows = $test->query("SELECT COUNT(*) AS total FROM downloads WHERE name IN ('Microsoft Support', 'Windows 11 Download')")->fetch_assoc();
    download_catalog_assert((int) $cpuAndMonitor['total'] === 2, 'CPU-Z and HWMonitor remain separate CPUID products.');
    download_catalog_assert((int) $microsoftAndWindows['total'] >= 2, 'Microsoft Support and Windows 11 Download remain separate resources.');

    $fixtureName = 'Catalog Fixture ' . $token;
    $fixtureUrl = 'https://catalog-fixture.example.test/products/' . $token;
    $fixtureIds[] = $service->create($fixtureName, 'Fixture.', $fixtureUrl, 'Windows', 'approved', 1);
    $primaryFixtureId = $fixtureIds[array_key_last($fixtureIds)];
    try {
        $service->create('  ' . strtoupper($fixtureName) . '  ', 'Duplicate.', 'https://another.example.test/' . $token, 'Windows', 'approved', 1);
        throw new RuntimeException('Add Download accepted a normalized duplicate name.');
    } catch (DomainException $exception) {
        download_catalog_assert($exception->getMessage() === 'A download with this product name already exists.', 'Duplicate-name validation is clear.');
    }
    try {
        $service->create('Different Fixture ' . $token, 'Duplicate.', 'HTTPS://CATALOG-FIXTURE.EXAMPLE.TEST:443/products/' . $token . '/#fragment', 'Windows', 'approved', 1);
        throw new RuntimeException('Add Download accepted a normalized duplicate URL.');
    } catch (DomainException $exception) {
        download_catalog_assert($exception->getMessage() === 'A download with this official URL already exists.', 'Duplicate-URL validation is clear.');
    }
    $service->update($primaryFixtureId, $fixtureName, 'Updated fixture.', $fixtureUrl, 'Windows', 'approved', 1);
    $otherName = 'Catalog Other ' . $token;
    $fixtureIds[] = $service->create($otherName, 'Other.', 'https://catalog-other.example.test/' . $token, 'Windows', 'approved', 1);
    $otherFixtureId = $fixtureIds[array_key_last($fixtureIds)];
    try {
        $service->update($otherFixtureId, $fixtureName, 'Conflict.', 'https://catalog-other.example.test/' . $token, 'Windows', 'approved', 1);
        throw new RuntimeException('Edit Download accepted another record name.');
    } catch (DomainException) {
    }
    try {
        $service->update($otherFixtureId, $otherName, 'Conflict.', $fixtureUrl, 'Windows', 'approved', 1);
        throw new RuntimeException('Edit Download accepted another record URL.');
    } catch (DomainException) {
    }

    $hiddenName = 'Catalog Hidden ' . $token;
    $unsafeName = 'Catalog Unsafe ' . $token;
    $hidden = $test->prepare('INSERT INTO downloads (name, description, official_url, category, review_state, is_published) VALUES (?, ?, ?, ?, ?, ?)');
    $description = 'Visibility fixture.';
    $category = 'Windows';
    $hiddenUrl = 'https://hidden.example.test/' . $token;
    $pending = 'pending';
    $published = 0;
    $hidden->bind_param('sssssi', $hiddenName, $description, $hiddenUrl, $category, $pending, $published);
    $hidden->execute();
    $fixtureIds[] = $hidden->insert_id;
    $unsafeUrl = 'http://unsafe.example.test/' . $token;
    $approved = 'approved';
    $published = 1;
    $hidden->bind_param('sssssi', $unsafeName, $description, $unsafeUrl, $category, $approved, $published);
    $hidden->execute();
    $fixtureIds[] = $hidden->insert_id;
    $hidden->close();

    require_once dirname(__DIR__) . '/bootstrap/web.php';
    $testDatabaseName = (string) config_value('DB_TEST_NAME');
    $guideMyPcEnvironment['DB_NAME'] = $testDatabaseName;
    putenv('DB_NAME=' . $testDatabaseName);
    require_once dirname(__DIR__) . '/config.php';
    $renderDatabase = $conn->query('SELECT DATABASE() AS name')->fetch_assoc();
    download_catalog_assert(($renderDatabase['name'] ?? null) === $testDatabaseName, 'Rendered Download page uses the isolated test database.');
    $page = rendered_downloads_page();
    foreach ($expected as $name => $url) {
        download_catalog_assert(substr_count($page, '<h3>' . e($name) . '</h3>') === 1, $name . ' renders as one public card.');
        download_catalog_assert(str_contains($page, 'href="' . e($url) . '"') && str_contains($page, 'target="_blank"') && str_contains($page, 'rel="noopener noreferrer"'), $name . ' renders a safe new-tab official link.');
    }
    download_catalog_assert(!str_contains($page, $hiddenName) && !str_contains($page, $unsafeName), 'Unpublished, unapproved, and unsafe records remain hidden.');

    fwrite(STDOUT, "PASS: official Download catalog migration, seed repair, duplicate protection, eligibility, and rendering work.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
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
