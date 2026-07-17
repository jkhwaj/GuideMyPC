<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/accounts.php';

require_login();
$userId = current_user_id();
$message = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();
    $name = valid_display_name($_POST['full_name'] ?? null);
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $userStatement = $conn->prepare('SELECT password FROM users WHERE id = ? AND status = \'active\'');
    $userStatement->bind_param('i', $userId);
    $userStatement->execute();
    $user = $userStatement->get_result()->fetch_assoc();
    $userStatement->close();

    if ($name === null || $user === null || !password_verify($currentPassword, $user['password'])) {
        $message = 'Enter a valid name and your current password to save changes.';
    } elseif ($newPassword !== '' && mb_strlen($newPassword) < 12) {
        $message = 'A new password must be at least 12 characters.';
    } else {
        if ($newPassword === '') {
            $update = $conn->prepare('UPDATE users SET full_name = ? WHERE id = ?');
            $update->bind_param('si', $name, $userId);
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $conn->prepare('UPDATE users SET full_name = ?, password = ? WHERE id = ?');
            $update->bind_param('ssi', $name, $hash, $userId);
        }
        $update->execute();
        $update->close();
        $_SESSION['full_name'] = $name;
        if ($newPassword !== '') {
            session_regenerate_id(true);
            record_account_event($conn, $userId, 'password_change');
        }
        flash('success', 'Account settings updated.');
        redirect('settings.php');
    }
}

$statement = $conn->prepare('SELECT full_name, email FROM users WHERE id = ?');
$statement->bind_param('i', $userId);
$statement->execute();
$account = $statement->get_result()->fetch_assoc();
$pageTitle = 'Account Settings | GuideMyPC';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="auth-page"><div class="auth-card"><h1>Account settings</h1><p>Your email address remains your sign-in identifier. Email changes require a future verified-email flow.</p><?php if ($message !== ''): ?><div class="auth-message" role="alert"><?php echo e($message); ?></div><?php endif; ?><form method="POST"><?php echo csrf_field(); ?><label for="settings-name">Name</label><input id="settings-name" name="full_name" autocomplete="name" value="<?php echo e($account['full_name']); ?>" required><label for="settings-email">Email</label><input id="settings-email" type="email" value="<?php echo e($account['email']); ?>" disabled><label for="current-password">Current password</label><input id="current-password" name="current_password" type="password" autocomplete="current-password" required><label for="settings-password">New password (optional)</label><input id="settings-password" name="new_password" type="password" autocomplete="new-password" minlength="12"><button type="submit">Save settings</button></form></div></section>
<?php include __DIR__ . '/includes/footer.php';
