<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap/web.php';

$pageTitle = 'Terms | GuideMyPC';
$pageDescription = 'Read the terms for using GuideMyPC.';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="section legal-page" aria-labelledby="terms-heading">
    <p class="section-label">Terms</p>
    <h1 id="terms-heading">Use support guidance carefully</h1>
    <div class="legal-content">
        <h2>Practical guidance</h2>
        <p>GuideMyPC provides general troubleshooting information. Follow steps only when they match your device and experience level, and stop if a step is unclear or introduces risk.</p>
        <h2>Your account</h2>
        <p>Keep your account credentials private. You are responsible for activity performed through your account and for the content you post to the community.</p>
        <h2>Community conduct</h2>
        <p>Do not post harmful instructions, personal information, copyrighted material you do not have permission to share, or content that interferes with the service.</p>
        <h2>Changes</h2>
        <p>This prototype may change while its planned features are implemented. Material production terms require review before public launch.</p>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
