<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    abort_request(405, 'method_not_allowed', 'This request method is not allowed.');
}

if (is_logged_in()) {
    refresh_current_user_authorization($conn);
}

require_editor();

$repository = new GuideMyPC\Features\Categories\CategoryAdminRepository($conn);
$listing = $repository->paginate($_GET);
$canDelete = user_can(GuideMyPC\Security\Authorization::DELETE_CONTENT);
$pageUrl = static function (int $page) use ($listing): string {
    return application_url('admin_categories.php?' . http_build_query([...$listing['query'], 'page' => $page]));
};

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="profile-page admin-page">
    <div class="profile-card">
        <div class="dashboard-panel-header">
            <div>
                <h1>Manage Categories</h1>
                <p>Control the published support taxonomy and homepage order.</p>
            </div>
            <a class="primary-btn" href="<?php echo e(application_url('add_category.php')); ?>">Add category</a>
        </div>

        <?php render_flash_messages(); ?>

        <form class="admin-filter-form" method="GET" action="<?php echo e(application_url('admin_categories.php')); ?>">
            <div>
                <label for="category-search">Search</label>
                <input id="category-search" name="q" type="search" maxlength="100" value="<?php echo e($listing['query']['q']); ?>" placeholder="Name, slug, or description">
            </div>
            <div>
                <label for="category-status">Publication</label>
                <select id="category-status" name="status">
                    <option value="all" <?php echo $listing['query']['status'] === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="published" <?php echo $listing['query']['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="unpublished" <?php echo $listing['query']['status'] === 'unpublished' ? 'selected' : ''; ?>>Unpublished</option>
                </select>
            </div>
            <div>
                <label for="category-sort">Sort</label>
                <select id="category-sort" name="sort">
                    <option value="updated" <?php echo $listing['query']['sort'] === 'updated' ? 'selected' : ''; ?>>Last updated</option>
                    <option value="name" <?php echo $listing['query']['sort'] === 'name' ? 'selected' : ''; ?>>Name</option>
                    <option value="slug" <?php echo $listing['query']['sort'] === 'slug' ? 'selected' : ''; ?>>Slug</option>
                    <option value="featured" <?php echo $listing['query']['sort'] === 'featured' ? 'selected' : ''; ?>>Featured order</option>
                </select>
            </div>
            <div>
                <label for="category-direction">Direction</label>
                <select id="category-direction" name="direction">
                    <option value="asc" <?php echo $listing['query']['direction'] === 'asc' ? 'selected' : ''; ?>>Ascending</option>
                    <option value="desc" <?php echo $listing['query']['direction'] === 'desc' ? 'selected' : ''; ?>>Descending</option>
                </select>
            </div>
            <div>
                <label for="category-page-size">Per page</label>
                <select id="category-page-size" name="per_page">
                    <?php foreach ([10, 25, 50] as $size): ?>
                        <option value="<?php echo $size; ?>" <?php echo $listing['query']['per_page'] === $size ? 'selected' : ''; ?>><?php echo $size; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Apply filters</button>
            <a class="secondary-btn" href="<?php echo e(application_url('admin_categories.php')); ?>">Reset</a>
        </form>

        <p class="admin-result-count"><?php echo number_format($listing['total']); ?> categor<?php echo $listing['total'] === 1 ? 'y' : 'ies'; ?> found.</p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <caption class="visually-hidden">GuideMyPC category administration results</caption>
                <thead>
                    <tr>
                        <th scope="col">Category</th>
                        <th scope="col">Publication</th>
                        <th scope="col">Featured</th>
                        <th scope="col">Content</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listing['rows'] as $category): ?>
                        <tr>
                            <td><strong><?php echo e($category['name']); ?></strong><br><code><?php echo e($category['slug']); ?></code></td>
                            <td><span class="status-badge <?php echo (int) $category['is_published'] === 1 ? 'status-published' : 'status-draft'; ?>"><?php echo (int) $category['is_published'] === 1 ? 'Published' : 'Unpublished'; ?></span></td>
                            <td><?php echo $category['featured_order'] === null ? 'Not featured' : '#' . number_format((int) $category['featured_order']); ?></td>
                            <td><?php echo number_format((int) $category['guide_count']); ?> guides<br><?php echo number_format((int) $category['knowledge_count']); ?> articles</td>
                            <td>
                                <a href="<?php echo e(application_url('edit_category.php?id=' . (int) $category['id'])); ?>">Edit</a>
                                <?php if ($canDelete): ?>
                                    <form class="inline-action" action="<?php echo e(application_url('delete_category.php')); ?>" method="POST" onsubmit="return confirm('Delete this unused category permanently?');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int) $category['id']; ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($listing['rows'] === []): ?>
                        <tr><td colspan="5">No categories match the selected filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($listing['totalPages'] > 1): ?>
            <nav class="pagination" aria-label="Category result pages">
                <?php if ($listing['page'] > 1): ?><a href="<?php echo e($pageUrl($listing['page'] - 1)); ?>">Previous</a><?php endif; ?>
                <span>Page <?php echo $listing['page']; ?> of <?php echo $listing['totalPages']; ?></span>
                <?php if ($listing['page'] < $listing['totalPages']): ?><a href="<?php echo e($pageUrl($listing['page'] + 1)); ?>">Next</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
