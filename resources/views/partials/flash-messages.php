<?php

declare(strict_types=1);

/** @var list<array{type: string, message: string}> $flashMessages */
?>
<?php foreach ($flashMessages as $flashMessage): ?>
    <div class="flash-message flash-<?php echo e($flashMessage['type']); ?>" role="status"><?php echo e($flashMessage['message']); ?></div>
<?php endforeach; ?>
