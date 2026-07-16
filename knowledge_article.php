<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/knowledge.php';

$slug = required_string($_GET['slug'] ?? null, 180) ?? '';

if ($slug === '') {
    abort_request(404, 'knowledge_article_not_found', 'The requested knowledge article was not found.');
}

$article = knowledge_published_article($conn, $slug);

if ($article === null) {
    abort_request(404, 'knowledge_article_not_found', 'The requested knowledge article was not found.');
}

$sourcesStatement = $conn->prepare('SELECT title, official_url FROM knowledge_sources WHERE article_id = ? ORDER BY sort_order, id');
$sourcesStatement->bind_param('i', $article['id']);
$sourcesStatement->execute();
$sources = $sourcesStatement->get_result();
$relationsStatement = $conn->prepare(
    "SELECT knowledge_relations.relation_type, knowledge_relations.label, knowledge_relations.external_url, related.slug AS related_slug, related.title AS related_title, guides.slug AS guide_slug, guides.title AS guide_title FROM knowledge_relations LEFT JOIN knowledge_articles AS related ON knowledge_relations.related_article_id = related.id AND related.publication_state = 'published' LEFT JOIN guides ON knowledge_relations.guide_id = guides.id AND guides.is_published = 1 WHERE knowledge_relations.article_id = ? ORDER BY knowledge_relations.sort_order, knowledge_relations.id"
);
$relationsStatement->bind_param('i', $article['id']);
$relationsStatement->execute();
$relations = $relationsStatement->get_result();
$types = knowledge_article_types();
$pageTitle = $article['title'] . ' | GuideMyPC';
$pageDescription = $article['summary'];
$canonicalPath = 'knowledge_article.php?slug=' . rawurlencode($article['slug']);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<article class="knowledge-article section" aria-labelledby="article-title">
    <p class="eyebrow"><?php echo e($types[$article['article_type']] ?? 'Knowledge article'); ?> · <?php echo e($article['category_name']); ?></p>
    <h1 id="article-title"><?php echo e($article['error_code'] ? $article['error_code'] . ': ' . $article['title'] : $article['title']); ?></h1>
    <p class="section-desc"><?php echo e($article['summary']); ?></p>
    <dl class="article-metadata"><div><dt>Platform</dt><dd><a href="<?php echo e(application_url('knowledge.php?category=' . rawurlencode($article['category_slug']))); ?>"><?php echo e($article['category_name']); ?></a></dd></div><div><dt>Last reviewed</dt><dd><?php echo e($article['last_reviewed_at'] ?: 'Not yet reviewed'); ?></dd></div></dl>
    <div class="article-content"><?php echo knowledge_render_content($article['content']); ?></div>

    <?php if ($sources->num_rows > 0): ?><section class="article-sources" aria-labelledby="sources-heading"><h2 id="sources-heading">Official sources</h2><ul><?php while ($source = $sources->fetch_assoc()): ?><?php $url = knowledge_safe_reference_url($source['official_url']); ?><?php if ($url !== null): ?><li><a href="<?php echo e($url); ?>" target="_blank" rel="noopener noreferrer"><?php echo e($source['title']); ?></a></li><?php endif; ?><?php endwhile; ?></ul></section><?php endif; ?>
    <?php if ($relations->num_rows > 0): ?><section class="article-sources" aria-labelledby="related-heading"><h2 id="related-heading">Related help</h2><ul><?php while ($relation = $relations->fetch_assoc()): ?><?php if ($relation['related_slug']): ?><li><a href="<?php echo e(application_url('knowledge_article.php?slug=' . rawurlencode($relation['related_slug']))); ?>"><?php echo e($relation['related_title']); ?></a></li><?php elseif ($relation['guide_slug']): ?><li><a href="<?php echo e(application_url('guide.php?slug=' . rawurlencode($relation['guide_slug']))); ?>"><?php echo e($relation['guide_title']); ?></a></li><?php elseif (($url = knowledge_safe_reference_url($relation['external_url'])) !== null): ?><li><a href="<?php echo e($url); ?>" target="_blank" rel="noopener noreferrer"><?php echo e($relation['label'] ?: 'Official reference'); ?></a></li><?php endif; ?><?php endwhile; ?></ul></section><?php endif; ?>
</article>

<?php
$sourcesStatement->close();
$relationsStatement->close();
include __DIR__ . '/includes/footer.php';
