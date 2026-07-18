<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

require_get();

$categorySlug = required_string($_GET['category'] ?? null, 100) ?? '';
$search = required_string($_GET['search'] ?? null, 120) ?? '';
$category = null;

if ($categorySlug !== '') {
    $categoryStatement = $conn->prepare('SELECT * FROM categories WHERE slug = ? AND is_published = 1');
    $categoryStatement->bind_param('s', $categorySlug);
    $categoryStatement->execute();
    $category = $categoryStatement->get_result()->fetch_assoc();
    $categoryStatement->close();

    if ($category === null) {
        abort_request(404, 'category_not_found', 'The requested guide category was not found.');
    }
}

$where = ['guides.is_published = 1', 'categories.is_published = 1'];
$types = '';
$values = [];

if ($category !== null) {
    $where[] = 'guides.category_id = ?';
    $types .= 'i';
    $values[] = (int) $category['id'];
}

if ($search !== '') {
    $where[] = '(guides.title LIKE ? OR guides.description LIKE ?)';
    $types .= 'ss';
    $searchTerm = '%' . $search . '%';
    $values[] = $searchTerm;
    $values[] = $searchTerm;
}

$guidesSql = 'SELECT guides.*, categories.name AS category_name, categories.slug AS category_slug, '
    . 'ROUND(AVG(guide_ratings.rating), 1) AS average_rating, COUNT(guide_ratings.id) AS total_ratings '
    . 'FROM guides JOIN categories ON guides.category_id = categories.id '
    . 'LEFT JOIN guide_ratings ON guides.id = guide_ratings.guide_id '
    . 'WHERE ' . implode(' AND ', $where) . ' '
    . 'GROUP BY guides.id, categories.name, categories.slug '
    . 'ORDER BY guides.featured_order IS NULL, guides.featured_order ASC, guides.created_at DESC';

$guidesStatement = $conn->prepare($guidesSql);

if ($types !== '') {
    $guidesStatement->bind_param($types, ...$values);
}

$guidesStatement->execute();
$guidesResult = $guidesStatement->get_result();

$pageTitle = $category !== null
    ? $category['name'] . ' Guides | GuideMyPC'
    : 'All Guides | GuideMyPC';
$pageDescription = $category !== null
    ? ($category['description'] ?: 'Browse step-by-step technology troubleshooting guides.')
    : 'Browse published, step-by-step technology troubleshooting guides.';
$canonicalPath = $category !== null
    ? 'guides.php?category=' . rawurlencode($categorySlug)
    : 'guides.php';

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
        <?php if ($guidesResult->num_rows > 0): ?>
            <?php while ($guide = $guidesResult->fetch_assoc()): ?>
                <a class="card" href="<?php echo e(application_url('guide.php?slug=' . rawurlencode($guide['slug']))); ?>">
                    <p class="eyebrow"><?php echo e($guide['category_name']); ?></p>
                    <h2><?php echo e($guide['title']); ?></h2>
                    <p><?php echo e($guide['description']); ?></p>
                    <p class="meta">Rating: <?php echo e((string) ($guide['average_rating'] ?? '0')); ?> / 5 (<?php echo (int) $guide['total_ratings']; ?> ratings)</p>
                    <p class="meta"><?php echo e($guide['difficulty'] ?: 'Practical'); ?> · <?php echo e($guide['estimated_time'] ?: 'Self-paced'); ?> · <?php echo e($guide['risk_level'] ?: 'Low'); ?> risk</p>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="content-empty"><p>No published guides match this view. Try a different filter or <a href="<?php echo e(application_url('guides.php')); ?>">browse all guides</a>.</p></div>
        <?php endif; ?>
    </div>
</section>

<?php
$guidesStatement->close();
include __DIR__ . '/includes/footer.php';
