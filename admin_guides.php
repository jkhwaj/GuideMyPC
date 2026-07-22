<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

use GuideMyPC\Features\Guides\GuideAdminRepository;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    abort_request(405, 'method_not_allowed', 'This request method is not allowed.');
}

if (is_logged_in()) {
    refresh_current_user_authorization($conn);
}

require_editor();
$listing = (new GuideAdminRepository($conn))->paginate($_GET);
$canDelete = user_can(GuideMyPC\Security\Authorization::DELETE_CONTENT);
$pageUrl = static function (int $page) use ($listing): string {
    $query = $listing['query'];
    $query['page'] = $page;

    return application_url('admin_guides.php?' . http_build_query($query));
};

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="profile-page admin-page">
    <div class="profile-card">
        <h1>Manage Guides</h1>
        <p>Draft, publish, and maintain structured troubleshooting guides.</p>
        <p><a class="primary-btn" href="<?php echo e(application_url('add_guide.php')); ?>">Add guide</a></p>
        <?php render_flash_messages(); ?>

        <form class="admin-filter-form" method="GET">
            <div><label for="guide-query">Search</label><input id="guide-query" type="search" name="q" maxlength="100" value="<?php echo e($listing['query']['q']); ?>"></div>
            <div><label for="guide-status">Publication</label><select id="guide-status" name="status"><option value="all">All</option><option value="published"<?php echo $listing['query']['status'] === 'published' ? ' selected' : ''; ?>>Published</option><option value="unpublished"<?php echo $listing['query']['status'] === 'unpublished' ? ' selected' : ''; ?>>Unpublished</option></select></div>
            <div><label for="guide-category">Category</label><select id="guide-category" name="category"><option value="">All categories</option><?php foreach ($listing['categories'] as $category): ?><option value="<?php echo (int) $category['id']; ?>"<?php echo $listing['query']['category'] === (int) $category['id'] ? ' selected' : ''; ?>><?php echo e($category['name']); ?><?php echo (int) $category['is_published'] === 0 ? ' (unpublished)' : ''; ?></option><?php endforeach; ?></select></div>
            <div><label for="guide-sort">Sort</label><select id="guide-sort" name="sort"><option value="updated"<?php echo $listing['query']['sort'] === 'updated' ? ' selected' : ''; ?>>Last updated</option><option value="title"<?php echo $listing['query']['sort'] === 'title' ? ' selected' : ''; ?>>Title</option><option value="featured"<?php echo $listing['query']['sort'] === 'featured' ? ' selected' : ''; ?>>Featured order</option><option value="reviewed"<?php echo $listing['query']['sort'] === 'reviewed' ? ' selected' : ''; ?>>Last reviewed</option></select></div>
            <div><label for="guide-direction">Direction</label><select id="guide-direction" name="direction"><option value="desc"<?php echo $listing['query']['direction'] === 'desc' ? ' selected' : ''; ?>>Descending</option><option value="asc"<?php echo $listing['query']['direction'] === 'asc' ? ' selected' : ''; ?>>Ascending</option></select></div>
            <div><label for="guide-per-page">Per page</label><select id="guide-per-page" name="per_page"><option value="10"<?php echo $listing['query']['per_page'] === 10 ? ' selected' : ''; ?>>10</option><option value="25"<?php echo $listing['query']['per_page'] === 25 ? ' selected' : ''; ?>>25</option><option value="50"<?php echo $listing['query']['per_page'] === 50 ? ' selected' : ''; ?>>50</option></select></div>
            <button type="submit">Apply filters</button><a class="secondary-btn" href="<?php echo e(application_url('admin_guides.php')); ?>">Reset</a>
        </form>

        <p class="admin-result-count"><?php echo number_format($listing['total']); ?> guide<?php echo $listing['total'] === 1 ? '' : 's'; ?> found.</p>
        <div class="admin-table-wrap"><table class="admin-table"><caption class="visually-hidden">GuideMyPC guide administration results</caption><thead><tr><th scope="col">Guide</th><th scope="col">Publication</th><th scope="col">Category</th><th scope="col">Progress</th><th scope="col">Actions</th></tr></thead><tbody>
            <?php foreach ($listing['rows'] as $guide): ?>
                <tr><td><strong><?php echo e($guide['title']); ?></strong><br><code><?php echo e($guide['slug']); ?></code></td><td><span class="status-badge <?php echo (int) $guide['is_published'] === 1 ? 'status-published' : 'status-draft'; ?>"><?php echo (int) $guide['is_published'] === 1 ? 'Published' : 'Unpublished'; ?></span></td><td><?php echo e($guide['category_name'] ?? 'No category'); ?></td><td><?php echo number_format((int) $guide['step_count']); ?> steps<br><?php echo number_format((int) $guide['progress_count']); ?> saved</td><td><a href="<?php echo e(application_url('edit_guide.php?id=' . (int) $guide['id'])); ?>">Edit</a><?php if ($canDelete): ?><form class="inline-action" action="<?php echo e(application_url('delete_guide.php')); ?>" method="POST" onsubmit="return confirm('Delete this unused guide permanently?');"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int) $guide['id']; ?>"><button type="submit">Delete</button></form><?php endif; ?></td></tr>
            <?php endforeach; ?>
            <?php if ($listing['rows'] === []): ?><tr><td colspan="5">No guides match the selected filters.</td></tr><?php endif; ?>
        </tbody></table></div>
        <?php if ($listing['totalPages'] > 1): ?><nav class="pagination" aria-label="Guide result pages"><?php if ($listing['page'] > 1): ?><a href="<?php echo e($pageUrl($listing['page'] - 1)); ?>">Previous</a><?php endif; ?><span>Page <?php echo $listing['page']; ?> of <?php echo $listing['totalPages']; ?></span><?php if ($listing['page'] < $listing['totalPages']): ?><a href="<?php echo e($pageUrl($listing['page'] + 1)); ?>">Next</a><?php endif; ?></nav><?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
