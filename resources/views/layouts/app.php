<?php

declare(strict_types=1);

/** @var array{title: string, description: string, canonicalPath: string, scripts?: list<array{src: string, integrity?: string}>} $page */
/** @var string $content */

$canonicalUrl = application_url($page['canonicalPath']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e($page['description']); ?>">
    <link rel="canonical" href="<?php echo e($canonicalUrl); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo e($page['title']); ?>">
    <meta property="og:description" content="<?php echo e($page['description']); ?>">
    <meta property="og:url" content="<?php echo e($canonicalUrl); ?>">
    <meta name="twitter:card" content="summary">
    <script>try{var theme=localStorage.getItem('guidemypc-theme');if(theme==='light'||theme==='dark'){document.documentElement.dataset.theme=theme;}}catch(error){}</script>
    <title><?php echo e($page['title']); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset_url('css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('css/design-system.css')); ?>">
</head>
<body>
<?php require __DIR__ . '/../partials/flash-messages.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<main id="main-content" tabindex="-1">
<?php echo $content; ?>
</main>
<?php foreach ($page['scripts'] ?? [] as $script): ?>
    <script src="<?php echo e($script['src']); ?>"<?php if (isset($script['integrity'])): ?> integrity="<?php echo e($script['integrity']); ?>" crossorigin="anonymous" referrerpolicy="no-referrer"<?php endif; ?>></script>
<?php endforeach; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
