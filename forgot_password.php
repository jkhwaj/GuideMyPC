<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/accounts.php';

$message = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();
    $email = normalize_email($_POST['email'] ?? null);

    if (!rate_limit_allows('password-reset-request', 3, 3600)) {
        $message = 'Too many reset requests. Please wait before trying again.';
    } else {
        if ($email !== null) {
            $statement = $conn->prepare("SELECT id, email FROM users WHERE email = ? AND status = 'active' LIMIT 1");
            $statement->bind_param('s', $email);
            $statement->execute();
            $user = $statement->get_result()->fetch_assoc();
            $statement->close();

            if ($user !== null && config_value('APP_MAIL_FROM') !== null && config_value('APP_MAIL_FROM') !== '') {
                $token = create_password_reset_token($conn, (int) $user['id']);
                $url = application_url('reset_password.php?token=' . rawurlencode($token));
                @mail($user['email'], 'GuideMyPC password reset', "Use this one-time link within one hour:\n" . $url, 'From: ' . config_value('APP_MAIL_FROM'));
            }
        }

        $message = 'If an active account matches that email and mail is configured, a reset link will arrive shortly.';
    }
}

$pageTitle = 'Reset Password | GuideMyPC';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="auth-page"><div class="auth-card"><h1>Reset password</h1><p>Enter your email address. We do not confirm whether an account exists.</p><?php if ($message !== ''): ?><div class="auth-message" role="status"><?php echo e($message); ?></div><?php endif; ?><form method="POST"><?php echo csrf_field(); ?><label for="reset-email">Email</label><input id="reset-email" name="email" type="email" autocomplete="email" required><button type="submit">Request reset link</button></form><p class="auth-link"><a href="login.php">Return to sign in</a></p></div></section>
<?php include __DIR__ . '/includes/footer.php';
