<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/accounts.php';

$token = required_string($_GET['token'] ?? $_POST['token'] ?? null, 64) ?? '';
$message = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if ($password !== $confirmation) {
        $message = 'Use matching passwords of at least 12 characters.';
    } else {
        $userId = consume_password_reset_token($conn, $token, $password);

        if ($userId === null) {
            $message = 'This reset link is invalid or has expired.';
        } else {
            record_account_event($conn, $userId, 'password_reset');
            session_regenerate_id(true);
            flash('success', 'Password updated. Sign in with your new password.');
            redirect('login.php');
        }
    }
}

$pageTitle = 'Choose a New Password | GuideMyPC';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="auth-page"><div class="auth-card"><h1>Choose a new password</h1><?php if ($message !== ''): ?><div class="auth-message" role="alert"><?php echo e($message); ?></div><?php endif; ?><form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="token" value="<?php echo e($token); ?>"><label for="new-password">New password</label><input id="new-password" name="password" type="password" autocomplete="new-password" minlength="12" required><label for="confirm-password">Confirm new password</label><input id="confirm-password" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" required><button type="submit">Update password</button></form></div></section>
<?php include __DIR__ . '/includes/footer.php';
