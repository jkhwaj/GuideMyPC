<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap/web.php';

$pageTitle = 'Support GuideMyPC | GuideMyPC';
$pageDescription = 'Learn how to support the continued development of GuideMyPC.';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="section legal-page" aria-labelledby="donate-heading">
    <p class="section-label">Support GuideMyPC</p>
    <h1 id="donate-heading">Help keep practical support accessible</h1>
    <div class="legal-content">
        <p>GuideMyPC does not collect donations or payment details in this prototype. Future support options will be reviewed for security, transparency, and accessibility before they are introduced.</p>
        <p>For now, the most useful support is thoughtful feedback on missing guides, unclear steps, and trusted official resources.</p>
        <p><a href="<?php echo e(application_url('contact.php')); ?>">Share feedback through the contact page</a>.</p>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
