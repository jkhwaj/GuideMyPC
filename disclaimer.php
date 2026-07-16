<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pageTitle = 'Disclaimer | GuideMyPC';
$pageDescription = 'Understand the limits of GuideMyPC troubleshooting guidance.';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="section legal-page" aria-labelledby="disclaimer-heading">
    <p class="section-label">Disclaimer</p>
    <h1 id="disclaimer-heading">Know when to pause and ask for qualified help</h1>
    <div class="legal-content">
        <h2>No guarantee of a repair</h2>
        <p>Technology problems have many causes. GuideMyPC cannot guarantee that a guide will fix a specific device or preserve data.</p>
        <h2>Protect your data</h2>
        <p>Back up important files before changing system settings, installing software, or resetting a device. Do not continue if you are unsure how a step affects your data or security.</p>
        <h2>Official sources first</h2>
        <p>Use official vendor support channels for warranty, safety, account recovery, malware, electrical, or hardware concerns. External links are provided for convenience and remain subject to their own terms.</p>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
