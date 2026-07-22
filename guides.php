<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

require_get();

$categorySlug = required_string($_GET['category'] ?? null, 100) ?? '';
$search = required_string($_GET['search'] ?? null, 120) ?? '';
$category = null;
$repository = new GuideMyPC\Features\Guides\GuideRepository($conn);

if ($categorySlug !== '') {
    $category = $repository->publishedCategory($categorySlug);

    if ($category === null) {
        abort_request(404, 'category_not_found', 'The requested guide category was not found.');
    }
}

$pagination = pagination_values($_GET['page'] ?? null, 12);
$listing = $repository->publishedGuides($category === null ? null : (int) $category['id'], $search, $pagination);
$pagination = $listing['pagination'];
$totalPages = max(1, (int) ceil($listing['total'] / $pagination['per_page']));
$guides = $listing['guides'];

$pageTitle = $category !== null
    ? $category['name'] . ' Guides | GuideMyPC'
    : 'All Guides | GuideMyPC';
$pageDescription = $category !== null
    ? ($category['description'] ?: 'Browse step-by-step technology troubleshooting guides.')
    : 'Browse published, step-by-step technology troubleshooting guides.';
$canonicalPath = $category !== null
    ? 'guides.php?category=' . rawurlencode($categorySlug)
    : 'guides.php';
$pageUrl = static function (int $page) use ($categorySlug, $category, $search): string {
    $parameters = ['category' => $category === null ? null : $categorySlug, 'search' => $search, 'page' => $page];
    $parameters = array_filter($parameters, static fn (mixed $value): bool => $value !== null && $value !== '');

    return application_url('guides.php?' . http_build_query($parameters));
};

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="section" aria-labelledby="guides-heading">
    <p class="section-label"><?php echo $category !== null ? e($category['name']) : 'Guide Library'; ?></p>
    <h1 id="guides-heading"><?php echo $category !== null ? e($category['name']) . ' troubleshooting' : 'All troubleshooting guides'; ?></h1>
    <p class="section-desc"><?php echo e($pageDescription); ?></p>

    <form method="GET" class="guide-search" role="search">
        <?php if ($category !== null): ?>
            <input type="hidden" name="category" value="<?php echo e($categorySlug); ?>">
        <?php endif; ?>

        <label class="sr-only" for="guide-search">Filter these guides</label>
        <input id="guide-search" type="search" name="search" maxlength="120" value="<?php echo e($search); ?>" placeholder="Filter guides by problem or device">
        <button type="submit">Filter guides</button>

        <?php if ($search !== ''): ?>
            <a href="<?php echo e(application_url($category !== null ? 'guides.php?category=' . rawurlencode($categorySlug) : 'guides.php')); ?>">Clear filter</a>
        <?php endif; ?>
    </form>

    <p class="guide-library-links"><a href="<?php echo e(application_url('index.php#categories')); ?>">Browse by category</a> <span aria-hidden="true">·</span> <a href="<?php echo e(application_url('search.php')); ?>">Search all support</a></p>

    <div class="card-grid">
        <?php if ($guides !== []): ?>
            <?php foreach ($guides as $guide): ?>
                <a class="card" href="<?php echo e(application_url('guide.php?slug=' . rawurlencode($guide['slug']))); ?>">
                    <p class="eyebrow"><?php echo e($guide['category_name']); ?></p>
                    <h2><?php echo e($guide['title']); ?></h2>
                    <p><?php echo e($guide['description']); ?></p>
                    <p class="meta">Rating: <?php echo e((string) ($guide['average_rating'] ?? '0')); ?> / 5 (<?php echo (int) $guide['total_ratings']; ?> ratings)</p>
                    <p class="meta"><?php echo e($guide['difficulty'] ?: 'Practical'); ?> · <?php echo e($guide['estimated_time'] ?: 'Self-paced'); ?> · <?php echo e($guide['risk_level'] ?: 'Low'); ?> risk</p>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="content-empty"><p>No published guides match this view. Try a different filter or <a href="<?php echo e(application_url('guides.php')); ?>">browse all guides</a>.</p></div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Guide library pages">
            <?php if ($pagination['page'] > 1): ?><a class="secondary-btn" href="<?php echo e($pageUrl($pagination['page'] - 1)); ?>">Previous</a><?php endif; ?>
            <span>Page <?php echo (int) $pagination['page']; ?> of <?php echo $totalPages; ?></span>
            <?php if ($pagination['page'] < $totalPages): ?><a class="secondary-btn" href="<?php echo e($pageUrl($pagination['page'] + 1)); ?>">Next</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>

<?php
include __DIR__ . '/includes/footer.php';
