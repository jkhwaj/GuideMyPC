<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';

$connection = test_database_or_fail();
$categories = $connection->query(
    'SELECT categories.slug, COUNT(guides.id) AS guide_count '
    . 'FROM categories LEFT JOIN guides ON guides.category_id = categories.id AND guides.is_published = 1 '
    . 'WHERE categories.slug IN ("windows", "macos", "linux", "android", "iphone", "wifi") '
    . 'GROUP BY categories.id, categories.slug'
);

try {
    $counts = [];

    while ($category = $categories->fetch_assoc()) {
        $counts[$category['slug']] = (int) $category['guide_count'];
    }

    foreach (['windows', 'macos', 'linux', 'android', 'iphone', 'wifi'] as $slug) {
        if (($counts[$slug] ?? 0) < 2) {
            throw new RuntimeException(sprintf('Category %s must have at least two published guides.', $slug));
        }
    }

    $incomplete = $connection->query(
        'SELECT COUNT(*) AS total FROM guides '
        . 'JOIN guide_steps ON guide_steps.guide_id = guides.id '
        . 'WHERE guides.is_published = 1 AND (guide_steps.step_title IS NULL OR guide_steps.step_title = "" OR guide_steps.expected_result IS NULL OR guide_steps.expected_result = "")'
    )->fetch_assoc();

    if ((int) ($incomplete['total'] ?? 0) !== 0) {
        throw new RuntimeException('Every published guide step must have a title and expected result.');
    }

    $missingSources = $connection->query(
        'SELECT COUNT(*) AS total FROM guides '
        . 'LEFT JOIN guide_sources ON guide_sources.guide_id = guides.id '
        . 'LEFT JOIN trusted_source_domains ON trusted_source_domains.id = guide_sources.trusted_source_domain_id AND trusted_source_domains.is_active = 1 '
        . 'WHERE guides.is_published = 1 GROUP BY guides.id HAVING COUNT(trusted_source_domains.id) = 0'
    );

    if ($missingSources->num_rows !== 0) {
        throw new RuntimeException('Every published guide must have an active official source.');
    }

    fwrite(STDOUT, "PASS: expanded guide seed coverage and structured content requirements work.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
