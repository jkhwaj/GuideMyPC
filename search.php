<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$query = required_string($_GET['q'] ?? null, 120) ?? '';
$results = null;

if ($query !== '') {
    $searchTerm = '%' . $query . '%';
    $statement = $conn->prepare(
        'SELECT guides.title, guides.slug, guides.description, categories.name AS category_name '
        . 'FROM guides JOIN categories ON guides.category_id = categories.id '
        . 'WHERE guides.is_published = 1 AND categories.is_published = 1 '
        . 'AND (guides.title LIKE ? OR guides.description LIKE ? OR guides.content LIKE ? OR categories.name LIKE ?) '
        . 'ORDER BY guides.featured_order IS NULL, guides.featured_order ASC, guides.views DESC, guides.title ASC LIMIT 30'
    );
    $statement->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $statement->execute();
    $results = $statement->get_result();
}

$pageTitle = $query === '' ? 'Search Support | GuideMyPC' : 'Search: ' . $query . ' | GuideMyPC';
$pageDescription = 'Search published GuideMyPC guides by problem, device, or error.';
$canonicalPath = 'search.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="section" aria-labelledby="search-heading">
    <p class="section-label">Support Search</p>
    <h1 id="search-heading">Find a troubleshooting guide</h1>
    <p class="section-desc">Use plain language, an error message, or a device name. Search ranking and filters will expand in a later release.</p>

    <form class="home-search search-page-form" action="<?php echo e(application_url('search.php')); ?>" method="GET" role="search">
        <label for="support-search">Describe your problem</label>
        <div class="home-search-controls">
            <input id="support-search" name="q" type="search" maxlength="120" value="<?php echo e($query); ?>" placeholder="Example: laptop is slow" required>
            <button class="primary-btn" type="submit">Search support</button>
        </div>
    </form>

    <?php if ($query === ''): ?>
        <div class="content-empty"><p>Enter a problem above, or <a href="<?php echo e(application_url('guides.php')); ?>">browse all guides</a>.</p></div>
    <?php elseif ($results !== null && $results->num_rows > 0): ?>
        <p class="search-summary">Results for <strong><?php echo e($query); ?></strong></p>
        <div class="card-grid">
            <?php while ($guide = $results->fetch_assoc()): ?>
                <a class="card" href="<?php echo e(application_url('guide.php?slug=' . rawurlencode($guide['slug']))); ?>">
                    <p class="eyebrow"><?php echo e($guide['category_name']); ?></p>
                    <h2><?php echo e($guide['title']); ?></h2>
                    <p><?php echo e($guide['description']); ?></p>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="content-empty"><h2>No guides matched that search</h2><p>Try a shorter phrase, a device name, or <a href="<?php echo e(application_url('guides.php')); ?>">browse all guides</a>.</p></div>
    <?php endif; ?>
</section>

<?php
if ($results instanceof mysqli_result) {
    $results->free();
}

if (isset($statement)) {
    $statement->close();
}

include __DIR__ . '/includes/footer.php';
