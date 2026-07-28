<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/accounts.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_csrf();
    $email = normalize_email($_POST['email'] ?? null);
    $password = (string) ($_POST["password"] ?? '');

    if (!rate_limit_allows('login', 5, 900)) {
        $message = "Too many login attempts. Please try again later.";
    } elseif ($email === null || $password === '') {
        $message = "Invalid email or password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            if (current_user_id() > 0) {
                revoke_current_remembered_device($conn, current_user_id());
            }
            establish_account_session([
                'user_id' => (int) $user['id'],
                'full_name' => (string) $user['full_name'],
                'role' => (string) $user['role'],
            ]);
            merge_guest_progress($conn, (int) $user['id']);
            record_account_event($conn, (int) $user['id'], 'login');

            if (($_POST['remember_me'] ?? null) === '1') {
                issue_remembered_device($conn, (int) $user['id']);
            }

            redirect('index.php');
        } else {
            $message = "Invalid email or password.";
        }
    }
}

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="auth-page">
    <div class="auth-card">
        <h1>Login</h1>
        <p>Welcome back to GuideMyPC.</p>

        <?php if (!empty($message)): ?>
            <div class="auth-message"><?php echo e($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <label for="login-email">Email</label>
            <input id="login-email" type="email" name="email" autocomplete="email" required>

            <label for="login-password">Password</label>
            <input id="login-password" type="password" name="password" autocomplete="current-password" required>

            <label><input type="checkbox" name="remember_me" value="1"> Keep me signed in on this browser for up to 30 days</label>

            <button type="submit">Login</button>
        </form>

        <p class="auth-link">
            Don't have an account?
            <a href="register.php">Register</a>
        </p>
        <p class="auth-link"><a href="forgot_password.php">Forgot your password?</a></p>
    </div>
</section>

<?php include("includes/footer.php"); ?>
