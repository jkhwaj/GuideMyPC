<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$homeError = false;
$categories = [];
$popularGuides = [];
$recommendedGuides = [];
$downloads = [];
$communityPosts = [];

try {
    $result = $conn->query(
        'SELECT name, slug, description, icon FROM categories '
        . 'WHERE is_published = 1 '
        . 'ORDER BY featured_order IS NULL, featured_order ASC, name ASC'
    );
    $categories = $result->fetch_all(MYSQLI_ASSOC);

    $guideSql = 'SELECT guides.title, guides.slug, guides.description, guides.difficulty, '
        . 'categories.name AS category_name, categories.slug AS category_slug '
        . 'FROM guides JOIN categories ON guides.category_id = categories.id '
        . 'WHERE guides.is_published = 1 AND categories.is_published = 1 '
        . 'ORDER BY guides.featured_order IS NULL, guides.featured_order ASC, guides.views DESC, guides.created_at DESC LIMIT 4';
    $result = $conn->query($guideSql);
    $popularGuides = $result->fetch_all(MYSQLI_ASSOC);
    $recommendedGuides = array_slice($popularGuides, 0, 3);

    $result = $conn->query(
        'SELECT name, description, official_url, category FROM downloads '
        . 'WHERE is_published = 1 '
        . 'ORDER BY featured_order IS NULL, featured_order ASC, name ASC LIMIT 3'
    );
    $downloads = $result->fetch_all(MYSQLI_ASSOC);

    $result = $conn->query(
        'SELECT community_posts.title, community_posts.created_at, users.full_name '
        . 'FROM community_posts JOIN users ON community_posts.user_id = users.id '
        . 'ORDER BY community_posts.created_at DESC LIMIT 3'
    );
    $communityPosts = $result->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $exception) {
    application_log('warning', 'Homepage content query failed.', ['exception' => $exception->getMessage()]);
    $homeError = true;
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="hero home-hero" aria-labelledby="home-heading">
    <div class="hero-content">
        <p class="badge">Practical support for PCs, phones, and home Wi-Fi</p>
        <h1 id="home-heading">Find a calm next step for your tech problem.</h1>
        <p>Describe what is happening, then start with trustworthy guides and official resources designed for everyday devices.</p>

        <form class="home-search" action="<?php echo e(application_url('search.php')); ?>" method="GET" role="search" data-search-autocomplete data-suggestion-list="home-search-suggestions" data-suggestion-url="<?php echo e(application_url('search_suggestions.php')); ?>">
            <label for="problem-search">Describe your problem</label>
            <div class="home-search-controls">
                <input id="problem-search" name="q" type="search" maxlength="120" placeholder="My Wi-Fi keeps disconnecting" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="home-search-suggestions">
                <button class="primary-btn" type="submit">Search support</button>
            </div>
            <div id="home-search-suggestions" class="search-suggestions" role="listbox" hidden></div>
        </form>

        <div class="hero-buttons">
            <a href="#categories" class="secondary-btn">Browse by device</a>
            <a href="<?php echo e(application_url('ai.php')); ?>" class="text-action">Explore the planned AI assistant</a>
        </div>
    </div>
</section>

<?php if ($homeError): ?>
    <section class="section" aria-labelledby="home-content-unavailable">
        <div class="content-empty" role="status">
            <h2 id="home-content-unavailable">Some homepage content is unavailable</h2>
            <p>Please browse all guides or try again shortly.</p>
            <a class="primary-btn" href="<?php echo e(application_url('guides.php')); ?>">Browse all guides</a>
        </div>
    </section>
<?php else: ?>
    <section class="section" id="categories" aria-labelledby="categories-heading">
        <p class="section-label">Support Categories</p>
        <h2 id="categories-heading">Choose your device or connection</h2>
        <p class="section-desc">Start with the platform you use, then follow a guide at your own pace.</p>

        <?php if ($categories !== []): ?>
            <div class="card-grid category-grid">
                <?php foreach ($categories as $category): ?>
                    <a class="card category-card" href="<?php echo e(application_url('guides.php?category=' . rawurlencode($category['slug']))); ?>">
                        <div class="icon" aria-hidden="true"><?php echo e($category['icon']); ?></div>
                        <h3><?php echo e($category['name']); ?></h3>
                        <p><?php echo e($category['description']); ?></p>
                        <span class="card-action">Browse <?php echo e($category['name']); ?> guides</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="content-empty"><p>Support categories are being prepared. <a href="<?php echo e(application_url('guides.php')); ?>">Browse all available guides</a>.</p></div>
        <?php endif; ?>
    </section>

    <section class="section section-muted" aria-labelledby="common-problems-heading">
        <p class="section-label">Start Here</p>
        <h2 id="common-problems-heading">Common problems</h2>
        <details class="common-problems">
            <summary>Browse common problems and quick starting points</summary>
            <?php if ($popularGuides !== []): ?>
                <ul>
                    <?php foreach ($popularGuides as $guide): ?>
                        <li><a href="<?php echo e(application_url('guide.php?slug=' . rawurlencode($guide['slug']))); ?>"><?php echo e($guide['title']); ?></a><span><?php echo e($guide['category_name']); ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No common problems are featured yet. <a href="<?php echo e(application_url('guides.php')); ?>">Browse all guides</a>.</p>
            <?php endif; ?>
        </details>
    </section>

    <section class="section" aria-labelledby="recommended-heading">
        <p class="section-label">Recommended Articles</p>
        <h2 id="recommended-heading">Trusted next steps</h2>
        <?php if ($recommendedGuides !== []): ?>
            <div class="content-grid">
                <?php foreach ($recommendedGuides as $guide): ?>
                    <article class="curated-card">
                        <p class="eyebrow"><?php echo e($guide['category_name']); ?> · <?php echo e($guide['difficulty'] ?: 'Practical'); ?></p>
                        <h3><a href="<?php echo e(application_url('guide.php?slug=' . rawurlencode($guide['slug']))); ?>"><?php echo e($guide['title']); ?></a></h3>
                        <p><?php echo e($guide['description']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="content-empty"><p>Recommended guides are being prepared. <a href="<?php echo e(application_url('guides.php')); ?>">Browse all guides</a>.</p></div>
        <?php endif; ?>
    </section>

    <section class="section section-muted" aria-labelledby="maintenance-heading">
        <p class="section-label">Maintenance</p>
        <h2 id="maintenance-heading">Prevent small problems from becoming bigger ones</h2>
        <div class="content-grid">
            <article class="curated-card"><h3>Keep updates manageable</h3><p>Install operating-system updates when you have time to restart and verify that everything still works.</p></article>
            <article class="curated-card"><h3>Check your backups</h3><p>Open a recent backup and confirm it contains files you would need if a device failed.</p></article>
            <article class="curated-card"><h3>Review your connection</h3><p>Place your router openly, update its firmware from the manufacturer, and use a strong unique Wi-Fi password.</p></article>
        </div>
    </section>

    <section class="section" aria-labelledby="downloads-heading">
        <p class="section-label">Trusted Downloads</p>
        <h2 id="downloads-heading">Start from official sources</h2>
        <?php if ($downloads !== []): ?>
            <div class="content-grid">
                <?php foreach ($downloads as $download): ?>
                    <article class="curated-card">
                        <p class="eyebrow"><?php echo e($download['category'] ?: 'Official resource'); ?></p>
                        <h3><?php echo e($download['name']); ?></h3>
                        <p><?php echo e($download['description']); ?></p>
                        <a href="<?php echo e($download['official_url']); ?>" target="_blank" rel="noopener noreferrer">Open official source<span class="sr-only"> (opens in a new tab)</span></a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="content-empty"><p>Trusted downloads are being reviewed. <a href="<?php echo e(application_url('downloads.php')); ?>">View the download directory</a>.</p></div>
        <?php endif; ?>
    </section>

    <section class="section section-muted" aria-labelledby="community-heading">
        <p class="section-label">Community</p>
        <h2 id="community-heading">Learn from real questions</h2>
        <?php if ($communityPosts !== []): ?>
            <div class="content-grid">
                <?php foreach ($communityPosts as $post): ?>
                    <article class="curated-card">
                        <h3><?php echo e($post['title']); ?></h3>
                        <p class="meta">Asked by <?php echo e($post['full_name']); ?> on <?php echo e($post['created_at']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="content-empty"><p>No community questions have been published yet. <a href="<?php echo e(application_url('community.php')); ?>">Visit the community</a> to start a conversation.</p></div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
