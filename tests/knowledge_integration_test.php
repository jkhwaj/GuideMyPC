<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/knowledge.php';
require_once dirname(__DIR__) . '/includes/search.php';

$slug = 'windows-stop-code-0x0000007b';
$article = knowledge_published_article($conn, $slug);

if ($article === null) {
    fwrite(STDOUT, "SKIP: seeded knowledge article is not available.\n");
    exit(0);
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
