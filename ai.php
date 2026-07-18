<?php

require_once __DIR__ . '/bootstrap/web.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="error-page" aria-labelledby="ai-title">
    <p class="section-label">AI Assistant</p>
    <h1 id="ai-title">AI troubleshooting is not available yet</h1>
    <p>Use a guide or the community area for help while the AI assistant is developed and reviewed for safe use.</p>
    <a class="primary-btn" href="<?php echo e(application_url('diagnostic.php?flow=pc-no-power')); ?>">Start a guided diagnostic</a>
</section>

<?php include 'includes/footer.php'; ?>
