<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$code = required_string($_GET['code'] ?? null, 80) ?? '';
$article = null;

if ($code !== '') {
    $statement = $conn->prepare("SELECT slug, title, error_code, summary FROM knowledge_articles WHERE article_type = 'error_code' AND publication_state = 'published' AND error_code = ? LIMIT 1");
    $statement->bind_param('s', $code);
    $statement->execute();
    $article = $statement->get_result()->fetch_assoc();
    $statement->close();
}

$pageTitle = $code === '' ? 'Error Code Reference | GuideMyPC' : $code . ' Error Code | GuideMyPC';
$pageDescription = 'Look up published GuideMyPC error-code references by their exact code.';
$canonicalPath = 'error-code.php' . ($code === '' ? '' : '?code=' . rawurlencode($code));
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="section" aria-labelledby="error-code-heading"><p class="section-label">Knowledge Base</p><h1 id="error-code-heading">Look up an error code</h1><form class="home-search search-page-form" method="GET"><label for="error-code-input">Exact error code</label><div class="home-search-controls"><input id="error-code-input" name="code" maxlength="80" value="<?php echo e($code); ?>" placeholder="Example: 0x0000007B" required><button class="primary-btn" type="submit">Look up code</button></div></form><?php if ($code !== '' && $article !== null): ?><div class="content-empty"><p class="eyebrow"><?php echo e($article['error_code']); ?></p><h2><a href="<?php echo e(application_url('knowledge_article.php?slug=' . rawurlencode($article['slug']))); ?>"><?php echo e($article['title']); ?></a></h2><p><?php echo e($article['summary']); ?></p></div><?php elseif ($code !== ''): ?><div class="content-empty"><h2>No published reference for that code</h2><p>Check the code carefully, then search the knowledge base or consult the device maker.</p></div><?php endif; ?></section>
<?php include __DIR__ . '/includes/footer.php';
