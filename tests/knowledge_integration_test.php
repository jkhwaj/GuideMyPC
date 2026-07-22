<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/knowledge.php';
require_once dirname(__DIR__) . '/includes/search.php';

$conn = test_database_or_fail();

$slug = 'windows-stop-code-0x0000007b';
$article = knowledge_published_article($conn, $slug);

if ($article === null) {
    fwrite(STDERR, "FAIL: seeded knowledge article is not available in DB_TEST_NAME.\n");
    exit(1);
}

$repository = new GuideMyPC\Features\Knowledge\KnowledgeRepository($conn);
$articles = $repository->publishedArticles('error_code', (string) $article['category_slug']);

if (!in_array($slug, array_column($articles, 'slug'), true)) {
    fwrite(STDERR, "FAIL: published Knowledge list filtering excluded the seeded article.\n");
    exit(1);
}

$conn->begin_transaction();

try {
    $statement = $conn->prepare("UPDATE knowledge_articles SET publication_state = 'draft' WHERE id = ?");
    $statement->bind_param('i', $article['id']);
    $statement->execute();
    $statement->close();

    if (knowledge_published_article($conn, $slug) !== null) {
        throw new RuntimeException('Draft content was available through the direct article helper.');
    }

    if (in_array($slug, array_column($repository->publishedArticles('error_code', (string) $article['category_slug']), 'slug'), true)) {
        throw new RuntimeException('Draft content was available through the Knowledge list query.');
    }

    $results = search_documents($conn, search_filters(['q' => '0x0000007B', 'type' => 'article']));

    foreach ($results as $result) {
        if ($result['title'] === $article['title']) {
            throw new RuntimeException('Draft content was available through search.');
        }
    }

    $conn->rollback();
    fwrite(STDOUT, "PASS: draft knowledge content is excluded from direct access and search.\n");
} catch (Throwable $exception) {
    $conn->rollback();
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
