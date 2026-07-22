<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/knowledge.php';

$types = knowledge_article_types();
$selectedType = required_string($_GET['type'] ?? null, 30) ?? '';
$selectedCategory = required_string($_GET['category'] ?? null, 100) ?? '';

if (!array_key_exists($selectedType, $types)) {
    $selectedType = '';
}

$repository = new GuideMyPC\Features\Knowledge\KnowledgeRepository($conn);
$categories = $repository->publishedCategories();
$articles = $repository->publishedArticles($selectedType, $selectedCategory);
$pageTitle = 'Knowledge Base | GuideMyPC';
$pageDescription = 'Browse reviewed technical explanations, FAQs, glossary terms, and error-code references.';
$canonicalPath = 'knowledge.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="section" aria-labelledby="knowledge-heading">
    <p class="section-label">Knowledge Base</p>
    <h1 id="knowledge-heading">Reviewed technical references</h1>
    <p class="section-desc">Short explanations, error-code references, and practical background information to use alongside repair guides.</p>

    <form class="search-filters knowledge-filters" method="GET" aria-label="Filter knowledge articles">
        <div><label for="knowledge-type">Article type</label><select id="knowledge-type" name="type"><option value="">All types</option><?php foreach ($types as $value => $label): ?><option value="<?php echo e($value); ?>"<?php echo $selectedType === $value ? ' selected' : ''; ?>><?php echo e($label); ?></option><?php endforeach; ?></select></div>
        <div><label for="knowledge-category">Platform</label><select id="knowledge-category" name="category"><option value="">All platforms</option><?php foreach ($categories as $category): ?><option value="<?php echo e($category['slug']); ?>"<?php echo $selectedCategory === $category['slug'] ? ' selected' : ''; ?>><?php echo e($category['name']); ?></option><?php endforeach; ?></select></div>
        <button class="secondary-btn" type="submit">Apply filters</button>
        <a href="<?php echo e(application_url('knowledge.php')); ?>">Clear filters</a>
    </form>

    <p class="guide-library-links"><a href="<?php echo e(application_url('glossary.php')); ?>">Browse the glossary</a><span aria-hidden="true">·</span><a href="<?php echo e(application_url('error-code.php')); ?>">Look up an error code</a></p>
    <div class="card-grid">
        <?php if ($articles !== []): ?>
            <?php foreach ($articles as $article): ?>
                <a class="card" href="<?php echo e(application_url('knowledge_article.php?slug=' . rawurlencode($article['slug']))); ?>">
                    <p class="eyebrow"><?php echo e($types[$article['article_type']] ?? 'Knowledge article'); ?> · <?php echo e($article['category_name']); ?></p>
                    <h2><?php echo e($article['error_code'] ? $article['error_code'] . ': ' . $article['title'] : $article['title']); ?></h2>
                    <p><?php echo e($article['summary']); ?></p>
                    <p class="meta">Last reviewed: <?php echo e($article['last_reviewed_at'] ?: 'Pending review'); ?></p>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="content-empty"><p>No published knowledge articles match these filters. Try another platform or <a href="<?php echo e(application_url('knowledge.php')); ?>">clear filters</a>.</p></div>
        <?php endif; ?>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
