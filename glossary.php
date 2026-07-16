<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$result = $conn->query("SELECT knowledge_articles.title, knowledge_articles.slug, knowledge_articles.summary, categories.name AS category_name FROM knowledge_articles JOIN categories ON knowledge_articles.category_id = categories.id WHERE knowledge_articles.publication_state = 'published' AND knowledge_articles.article_type = 'glossary' AND categories.is_published = 1 ORDER BY knowledge_articles.title");
$pageTitle = 'Technology Glossary | GuideMyPC';
$pageDescription = 'Plain-language definitions for common technology support terms.';
$canonicalPath = 'glossary.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="section glossary-page" aria-labelledby="glossary-heading"><p class="section-label">Knowledge Base</p><h1 id="glossary-heading">Technology glossary</h1><p class="section-desc">Definitions that explain support terms without requiring prior technical knowledge.</p><dl class="glossary-list"><?php while ($term = $result->fetch_assoc()): ?><div><dt><a href="<?php echo e(application_url('knowledge_article.php?slug=' . rawurlencode($term['slug']))); ?>"><?php echo e($term['title']); ?></a></dt><dd><?php echo e($term['summary']); ?><span><?php echo e($term['category_name']); ?></span></dd></div><?php endwhile; ?></dl></section>
<?php include __DIR__ . '/includes/footer.php';
