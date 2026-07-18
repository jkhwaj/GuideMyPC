<?php

declare(strict_types=1);

/** @var array<string, mixed> $category */
/** @var list<string> $errors */
/** @var string $formTitle */
/** @var string $formDescription */
/** @var string $submitLabel */
/** @var string $publicationWarning */
?>
<section class="auth-page">
    <div class="auth-card admin-form-card">
        <h1><?php echo e($formTitle); ?></h1>
        <p><?php echo e($formDescription); ?></p>

        <?php if ($errors !== []): ?>
            <div class="auth-message" role="alert" aria-labelledby="category-errors-title">
                <strong id="category-errors-title">Check the category details:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <label for="category-name">Name</label>
            <input id="category-name" type="text" name="name" maxlength="100" value="<?php echo e($category['name']); ?>" required>

            <label for="category-slug">Slug</label>
            <input id="category-slug" type="text" name="slug" maxlength="100" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" value="<?php echo e($category['slug']); ?>" aria-describedby="category-slug-help" required>
            <small id="category-slug-help">Lowercase letters, numbers, and single hyphens.</small>

            <label for="category-description">Description</label>
            <textarea id="category-description" name="description" rows="5" maxlength="5000"><?php echo e($category['description']); ?></textarea>

            <label for="category-icon">Icon class</label>
            <input id="category-icon" type="text" name="icon" maxlength="50" value="<?php echo e($category['icon']); ?>" aria-describedby="category-icon-help">
            <small id="category-icon-help">Optional existing icon class, for example <code>fa-brands fa-windows</code>.</small>

            <label for="category-featured-order">Featured order</label>
            <input id="category-featured-order" type="number" name="featured_order" min="1" max="999" value="<?php echo $category['featured_order'] === null ? '' : (int) $category['featured_order']; ?>" aria-describedby="category-featured-help">
            <small id="category-featured-help">Optional. Lower numbers appear first on the homepage.</small>

            <input type="hidden" name="is_published" value="0">
            <label class="checkbox-field" for="category-published">
                <input id="category-published" type="checkbox" name="is_published" value="1" <?php echo (int) $category['is_published'] === 1 ? 'checked' : ''; ?>>
                Published
            </label>
            <p class="form-warning"><?php echo e($publicationWarning); ?></p>

            <div class="form-actions">
                <button type="submit"><?php echo e($submitLabel); ?></button>
                <a class="secondary-btn" href="<?php echo e(application_url('admin_categories.php')); ?>">Cancel</a>
            </div>
        </form>
    </div>
</section>
