<?php

declare(strict_types=1);
?>
<footer class="site-footer">
    <div>
        <strong>GuideMyPC</strong>
        <p>Practical, trustworthy help for everyday technology.</p>
    </div>
    <nav aria-label="Footer navigation">
        <a href="<?php echo e(application_url('about.php')); ?>">About</a>
        <a href="<?php echo e(application_url('contact.php')); ?>">Contact</a>
        <a href="<?php echo e(application_url('guides.php')); ?>">Guides</a>
        <a href="<?php echo e(application_url('knowledge.php')); ?>">Knowledge</a>
        <a href="<?php echo e(application_url('privacy.php')); ?>">Privacy</a>
        <a href="<?php echo e(application_url('terms.php')); ?>">Terms</a>
        <a href="<?php echo e(application_url('disclaimer.php')); ?>">Disclaimer</a>
    </nav>
</footer>
<script src="<?php echo e(asset_url('js/script.js')); ?>"></script>
