<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_once dirname(__DIR__) . '/includes/diagnostics.php';

use GuideMyPC\Features\Categories\CategoryAdminRepository;
use GuideMyPC\Features\Categories\CategoryAdminService;

function category_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$test = test_database_or_fail();
$token = bin2hex(random_bytes(5));
$repository = new CategoryAdminRepository($test);
$service = new CategoryAdminService($test);
$_SESSION = [];
$categoryId = 0;
$guideId = 0;
$diagnosticId = 0;

// Remove fixtures left by interrupted runs before this test used explicit cleanup.
$test->query("DELETE FROM categories WHERE slug LIKE 'paged-%' OR slug LIKE 'category-test-%'");
$test->query("DELETE FROM admin_audit_events WHERE target_type = 'category' AND metadata_json LIKE '%category-test-%'");

try {
    $valid = $service->validate([
        'name' => 'Category Test ' . $token,
        'slug' => 'category-test-' . $token,
        'description' => 'A deterministic category integration fixture.',
        'icon' => 'fa-solid fa-display',
        'is_published' => '0',
        'featured_order' => '',
    ]);
    category_assert($valid['errors'] === [], 'Valid category input passes validation.');
    category_assert($valid['values']['is_published'] === 0 && $valid['values']['featured_order'] === null, 'Draft and empty feature order normalize correctly.');

    $invalid = $service->validate([
        'name' => ['invalid'],
        'slug' => 'Invalid Slug',
        'description' => str_repeat('x', 5001),
        'icon' => '<script>',
        'is_published' => 'yes',
        'featured_order' => '-2',
    ]);
    category_assert(count($invalid['errors']) === 6, 'Malformed, oversized, and tampered category fields fail closed.');

    $categoryId = $service->create($valid['values']);
    $created = $repository->find($categoryId);
    category_assert($created !== null && $created['slug'] === $valid['values']['slug'], 'Category create stores normalized fields.');
    category_assert((int) $created['is_published'] === 0 && $created['featured_order'] === null, 'Category create preserves draft and unfeatured state.');
    category_assert($repository->slugExists($valid['values']['slug']), 'Duplicate slug lookup detects an existing category.');
    category_assert(!$repository->slugExists($valid['values']['slug'], $categoryId), 'Duplicate slug lookup excludes the current category during editing.');

    $updatedValues = $valid['values'];
    $updatedValues['name'] = 'Updated Category Test ' . $token;
    $updatedValues['is_published'] = 1;
    $updatedValues['featured_order'] = 3;
    category_assert($service->update($categoryId, $updatedValues), 'Existing category update succeeds.');
    $updated = $repository->find($categoryId);
    category_assert($updated !== null && (int) $updated['is_published'] === 1 && (int) $updated['featured_order'] === 3, 'Category update stores publication and featured order.');

    $diagnosticSlug = 'category-diagnostic-' . $token;
    $diagnosticTitle = 'Category Diagnostic ' . $token;
    $diagnosticSummary = 'Published diagnostic category policy fixture.';
    $diagnosticInsert = $test->prepare("INSERT INTO diagnostic_flows (category_id, slug, title, summary, publication_state) VALUES (?, ?, ?, ?, 'published')");
    $diagnosticInsert->bind_param('isss', $categoryId, $diagnosticSlug, $diagnosticTitle, $diagnosticSummary);
    $diagnosticInsert->execute();
    $diagnosticId = $diagnosticInsert->insert_id;
    $diagnosticInsert->close();
    $initialNode = 'start';
    $versionInsert = $test->prepare("INSERT INTO diagnostic_flow_versions (flow_id, version_number, initial_node_key, publication_state) VALUES (?, 1, ?, 'published')");
    $versionInsert->bind_param('is', $diagnosticId, $initialNode);
    $versionInsert->execute();
    $versionInsert->close();
    category_assert(diagnostic_flow($test, $diagnosticSlug) !== null, 'Published diagnostic is visible through a published category.');

    $unpublishedValues = $updatedValues;
    $unpublishedValues['is_published'] = 0;
    category_assert($service->update($categoryId, $unpublishedValues), 'Category can be unpublished.');
    category_assert(diagnostic_flow($test, $diagnosticSlug) === null, 'Unpublishing a category hides its published diagnostic flow.');
    category_assert($service->update($categoryId, $updatedValues), 'Category can be republished.');

    $fixtureInsert = $test->prepare(
        'INSERT INTO categories (name, slug, description, icon, is_published, featured_order) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $fixtureIds = [];

    for ($index = 1; $index <= 27; $index++) {
        $name = sprintf('Paged %s %02d', $token, $index);
        $slug = sprintf('paged-%s-%02d', $token, $index);
        $description = 'Pagination fixture ' . $token;
        $icon = '';
        $published = $index % 2;
        $featured = $index <= 3 ? $index : null;
        $fixtureInsert->bind_param('ssssii', $name, $slug, $description, $icon, $published, $featured);
        $fixtureInsert->execute();
        $fixtureIds[] = $fixtureInsert->insert_id;
    }

    $fixtureInsert->close();
    $listing = $repository->paginate([
        'q' => 'Paged ' . $token,
        'status' => 'all',
        'sort' => 'name',
        'direction' => 'asc',
        'per_page' => '10',
        'page' => '2',
    ]);
    category_assert($listing['total'] === 27 && count($listing['rows']) === 10, 'Category listing paginates matching records with a bounded page size.');
    category_assert($listing['page'] === 2 && $listing['totalPages'] === 3, 'Category listing reports deterministic page metadata.');
    category_assert($listing['rows'][0]['name'] === sprintf('Paged %s 11', $token), 'Category name sorting remains ordered across pages.');

    $publishedListing = $repository->paginate([
        'q' => 'Paged ' . $token,
        'status' => 'published',
        'sort' => 'invalid',
        'direction' => 'invalid',
        'per_page' => '1000',
    ]);
    category_assert($publishedListing['total'] === 14, 'Category publication filter returns only published records.');
    category_assert($publishedListing['query']['sort'] === 'updated' && $publishedListing['query']['per_page'] === 25, 'Unknown sort and page-size input use safe defaults.');

    $guideTitle = 'Category Dependency ' . $token;
    $guideSlug = 'category-dependency-' . $token;
    $guideInsert = $test->prepare('INSERT INTO guides (category_id, title, slug, is_published) VALUES (?, ?, ?, 0)');
    $guideInsert->bind_param('iss', $categoryId, $guideTitle, $guideSlug);
    $guideInsert->execute();
    $guideId = $guideInsert->insert_id;
    $guideInsert->close();
    $blocked = $service->delete($categoryId);
    category_assert($blocked['status'] === 'blocked' && $blocked['dependencies']['guides'] === 1 && $blocked['dependencies']['diagnostic flows'] === 1, 'Category deletion reports guide and diagnostic dependencies.');
    category_assert($repository->find($categoryId) !== null, 'Blocked category deletion leaves the category intact.');

    $test->query('DELETE FROM guides WHERE id = ' . $guideId);
    $guideId = 0;
    $test->query('DELETE FROM diagnostic_flows WHERE id = ' . $diagnosticId);
    $diagnosticId = 0;
    $deleted = $service->delete($categoryId);
    category_assert($deleted['status'] === 'deleted' && $repository->find($categoryId) === null, 'Unused category deletion succeeds.');

    $auditStatement = $test->prepare("SELECT action FROM admin_audit_events WHERE target_type = 'category' AND target_id = ? ORDER BY id ASC");
    $targetId = (string) $categoryId;
    $auditStatement->bind_param('s', $targetId);
    $auditStatement->execute();
    $auditActions = array_column($auditStatement->get_result()->fetch_all(MYSQLI_ASSOC), 'action');
    $auditStatement->close();
    category_assert($auditActions === ['category.created', 'category.updated', 'category.updated', 'category.updated', 'category.deleted'], 'Successful category mutations record one ordered audit event each.');

    fwrite(STDOUT, "PASS: category validation, CRUD, publication, pagination, dependencies, and audit work.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    if ($guideId > 0) {
        $test->query('DELETE FROM guides WHERE id = ' . $guideId);
    }

    if ($diagnosticId > 0) {
        $test->query('DELETE FROM diagnostic_flows WHERE id = ' . $diagnosticId);
    }

    $slugPattern = '%-' . $token . '%';
    $cleanupCategories = $test->prepare('DELETE FROM categories WHERE slug LIKE ?');
    $cleanupCategories->bind_param('s', $slugPattern);
    $cleanupCategories->execute();
    $cleanupCategories->close();
    $auditPattern = '%' . $token . '%';
    $cleanupAudit = $test->prepare("DELETE FROM admin_audit_events WHERE target_type = 'category' AND metadata_json LIKE ?");
    $cleanupAudit->bind_param('s', $auditPattern);
    $cleanupAudit->execute();
    $cleanupAudit->close();
    $test->close();
    $_SESSION = [];
}

if (isset($exitCode)) {
    exit($exitCode);
}
