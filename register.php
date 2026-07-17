<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/accounts.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_csrf();
    $full_name = valid_display_name($_POST['full_name'] ?? null);
    $email = normalize_email($_POST['email'] ?? null);
    $password = (string) ($_POST["password"] ?? '');

    if (!rate_limit_allows('registration', 3, 3600)) {
        $message = "Too many registration attempts. Please try again later.";
    } elseif ($full_name === null || $email === null || mb_strlen($password) < 12) {
        $message = "Use a valid name and email, and a password of at least 12 characters.";
    } else {
        $existing = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $existing->bind_param('s', $email);
        $existing->execute();
        $exists = $existing->get_result()->num_rows > 0;
        $existing->close();

        if ($exists) {
            $message = 'Unable to create the account. Review the details and try again.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $full_name, $email, $hashedPassword);
            $stmt->execute();
            $userId = $stmt->insert_id;
            $stmt->close();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role'] = 'user';
            merge_guest_progress($conn, $userId);
            record_account_event($conn, $userId, 'registration');
            flash('success', 'Account created. Your guest progress was saved to your account.');
            redirect('profile.php');
        }
    }
}

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="auth-page">
    <div class="auth-card">
        <h1>Create Account</h1>
        <p>Join GuideMyPC and save your troubleshooting progress.</p>

        <?php if (!empty($message)): ?>
            <div class="auth-message"><?php echo e($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <label for="register-name">Full Name</label>
            <input id="register-name" type="text" name="full_name" autocomplete="name" required>

            <label for="register-email">Email</label>
            <input id="register-email" type="email" name="email" autocomplete="email" required>

            <label for="register-password">Password</label>
            <input id="register-password" type="password" name="password" autocomplete="new-password" minlength="12" required>

            <button type="submit">Register</button>
        </form>

        <p class="auth-link">
            Already have an account?
            <a href="login.php">Login</a>
        </p>
    </div>
</section>

<?php include("includes/footer.php"); ?>
