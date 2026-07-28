<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

require_login();
$devices = remembered_device_service($conn)->devicesForUser(
    current_user_id(),
    is_string($_SESSION['_remember_selector'] ?? null) ? $_SESSION['_remember_selector'] : null,
);
$pageTitle = 'Signed-in Browsers | GuideMyPC';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="profile-page" aria-labelledby="devices-title">
    <div class="profile-card">
        <h1 id="devices-title">Signed-in browsers</h1>
        <p>Only browsers where you selected “Keep me signed in” appear here. Device secrets are never displayed.</p>
        <?php if ($devices === []): ?>
            <p>No persistent browser sign-ins are active.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($devices as $device): ?>
                    <li>
                        <strong><?php echo e($device['device_label']); ?></strong><?php if ($device['is_current']): ?> (this browser)<?php endif; ?>
                        <span class="meta">Created <?php echo e($device['created_at']); ?>. Last used <?php echo e($device['last_used_at'] ?? 'not yet'); ?>. Expires <?php echo e($device['expires_at']); ?>.</span>
                        <form class="inline-action-form" action="<?php echo e(application_url('revoke_device.php')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="device_id" value="<?php echo (int) $device['id']; ?>">
                            <button class="secondary-btn" type="submit">Sign out this browser</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form class="inline-action-form" action="<?php echo e(application_url('logout_all.php')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button class="secondary-btn" type="submit">Sign out all browsers</button>
        </form>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
