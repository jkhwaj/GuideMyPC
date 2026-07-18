<?php

declare(strict_types=1);

/** @var array{user: array{name: string, canViewDashboard: bool, isAdmin: bool}|null} $navigation */
$user = $navigation['user'];
?>
<a class="skip-link" href="#main-content">Skip to main content</a>
<header class="site-header">
    <nav class="navbar" aria-label="Primary navigation">
        <a class="logo" href="<?php echo e(application_url('index.php')); ?>" aria-label="GuideMyPC home">
            <div class="logo-box">G</div>
            <div>
                <h3>GuideMyPC</h3>
                <span>Your Trusted Tech Support</span>
            </div>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
            <span class="sr-only">Toggle navigation</span>
            <span aria-hidden="true">Menu</span>
        </button>
        <div class="nav-links" id="primary-navigation">
            <a href="<?php echo e(application_url('index.php')); ?>">Home</a>
            <a href="<?php echo e(application_url('guides.php')); ?>">Guides</a>
            <a href="<?php echo e(application_url('knowledge.php')); ?>">Knowledge</a>
            <a href="<?php echo e(application_url('downloads.php')); ?>">Downloads</a>
            <a href="<?php echo e(application_url('community.php')); ?>">Community</a>
        </div>
        <div class="auth-buttons">
            <button class="theme-toggle" type="button" aria-pressed="false">
                <span aria-hidden="true">Theme</span><span class="sr-only">Toggle color theme</span>
            </button>
            <?php if ($user !== null): ?>
                <span class="user-name">Hello, <?php echo e($user['name']); ?></span>
                <a class="secondary-btn" href="<?php echo e(application_url('profile.php')); ?>">Profile</a>
                <?php if ($user['canViewDashboard']): ?>
                    <a class="secondary-btn" href="<?php echo e(application_url('dashboard.php')); ?>">Dashboard</a>
                <?php endif; ?>
                <?php if ($user['isAdmin']): ?>
                    <a class="secondary-btn" href="<?php echo e(application_url('admin.php')); ?>">Admin</a>
                <?php endif; ?>
                <form action="<?php echo e(application_url('logout.php')); ?>" method="POST" class="logout-form">
                    <?php echo csrf_field(); ?>
                    <button class="login-btn" type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a class="secondary-btn" href="<?php echo e(application_url('register.php')); ?>">Register</a>
                <a class="login-btn" href="<?php echo e(application_url('login.php')); ?>">Login</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
