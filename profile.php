<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/accounts.php';

require_login();
$userId = current_user_id();
$accountStatement = $conn->prepare('SELECT full_name, email, role, created_at FROM users WHERE id = ? AND status = \'active\'');
$accountStatement->bind_param('i', $userId);
$accountStatement->execute();
$account = $accountStatement->get_result()->fetch_assoc();

if ($account === null) {
    abort_request(403, 'account_unavailable', 'This account is unavailable.');
}

$favoritesStatement = $conn->prepare('SELECT guides.title, guides.slug, guides.description FROM favorites JOIN guides ON favorites.guide_id = guides.id WHERE favorites.user_id = ? ORDER BY favorites.created_at DESC LIMIT 10');
$favoritesStatement->bind_param('i', $userId);
$favoritesStatement->execute();
$favorites = $favoritesStatement->get_result();
$progressStatement = $conn->prepare('SELECT guides.title, guides.slug, COUNT(user_progress.id) AS completed_steps FROM user_progress JOIN guide_steps ON user_progress.guide_step_id = guide_steps.id JOIN guides ON guide_steps.guide_id = guides.id WHERE user_progress.user_id = ? GROUP BY guides.id ORDER BY MAX(user_progress.completed_at) DESC LIMIT 10');
$progressStatement->bind_param('i', $userId);
$progressStatement->execute();
$progress = $progressStatement->get_result();
$activityStatement = $conn->prepare('SELECT activity_type, subject_type, subject_value, created_at FROM user_activity WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
$activityStatement->bind_param('i', $userId);
$activityStatement->execute();
$activity = $activityStatement->get_result();
$requestsStatement = $conn->prepare("SELECT request_type, request_status, created_at FROM user_data_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$requestsStatement->bind_param('i', $userId);
$requestsStatement->execute();
$requests = $requestsStatement->get_result();
$pageTitle = 'My Profile | GuideMyPC';
$pageDescription = 'Review saved guides, troubleshooting progress, and account controls.';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="profile-page" aria-labelledby="profile-title">
    <div class="profile-card"><h1 id="profile-title">My profile</h1><p>Welcome back, <?php echo e($account['full_name']); ?>.</p><div class="profile-info"><div><span>Email</span><strong><?php echo e($account['email']); ?></strong></div><div><span>Member since</span><strong><?php echo e($account['created_at']); ?></strong></div><div><span>Account</span><strong><?php echo e($account['role']); ?></strong></div></div><a class="secondary-btn" href="<?php echo e(application_url('settings.php')); ?>">Account settings</a></div>
    <div class="profile-card"><h2>Saved guides</h2><?php if ($favorites->num_rows > 0): ?><div class="steps-list"><?php while ($favorite = $favorites->fetch_assoc()): ?><a class="step-card" href="<?php echo e(application_url('guide.php?slug=' . rawurlencode($favorite['slug']))); ?>"><strong><?php echo e($favorite['title']); ?></strong><p><?php echo e($favorite['description']); ?></p></a><?php endwhile; ?></div><?php else: ?><p>No favorites yet. Save a useful guide to return to it later.</p><?php endif; ?></div>
    <div class="profile-card"><h2>Progress</h2><?php if ($progress->num_rows > 0): ?><ul><?php while ($row = $progress->fetch_assoc()): ?><li><a href="<?php echo e(application_url('guide.php?slug=' . rawurlencode($row['slug']))); ?>"><?php echo e($row['title']); ?></a> - <?php echo (int) $row['completed_steps']; ?> completed steps</li><?php endwhile; ?></ul><?php else: ?><p>Start any guide to keep your checklist progress here.</p><?php endif; ?></div>
    <div class="profile-card"><h2>Recent activity</h2><?php if ($activity->num_rows > 0): ?><ul><?php while ($row = $activity->fetch_assoc()): ?><li><?php echo e(ucwords(str_replace('_', ' ', $row['activity_type']))); ?>: <?php echo e($row['subject_value']); ?> <span class="meta"><?php echo e($row['created_at']); ?></span></li><?php endwhile; ?></ul><?php else: ?><p>Viewed guides will appear here. Search queries are not stored in your account history.</p><?php endif; ?></div>
    <div class="profile-card"><h2>Privacy requests</h2><p>You can request a copy of stored account activity or ask an operator to delete the account. Requests are reviewed before completion in this prototype.</p><form class="inline-action-form" action="<?php echo e(application_url('account_request.php')); ?>" method="POST"><?php echo csrf_field(); ?><button class="secondary-btn" name="request_type" value="export" type="submit">Request data export</button><button class="secondary-btn" name="request_type" value="deletion" type="submit">Request account deletion</button></form><?php if ($requests->num_rows > 0): ?><ul><?php while ($request = $requests->fetch_assoc()): ?><li><?php echo e(ucfirst($request['request_type'])); ?> request: <?php echo e($request['request_status']); ?> (<?php echo e($request['created_at']); ?>)</li><?php endwhile; ?></ul><?php endif; ?></div>
</section>
<?php include __DIR__ . '/includes/footer.php';
