<?php
require_once __DIR__ . '/config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_csrf();
    $full_name = trim((string) ($_POST["full_name"] ?? ''));
    $email = trim((string) ($_POST["email"] ?? ''));
    $password = (string) ($_POST["password"] ?? '');

    if (!rate_limit_allows('registration', 3, 3600)) {
        $message = "Too many registration attempts. Please try again later.";
    } elseif ($full_name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
        $message = "Use a valid name and email, and a password of at least 12 characters.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $full_name, $email, $hashedPassword);

        if ($stmt->execute()) {
            $message = "Account created successfully. You can now log in.";
        } else {
            $message = "Unable to create the account. Review the details and try again.";
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
            <label>Full Name</label>
            <input type="text" name="full_name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" minlength="12" required>

            <button type="submit">Register</button>
        </form>

        <p class="auth-link">
            Already have an account?
            <a href="login.php">Login</a>
        </p>
    </div>
</section>

<?php include("includes/footer.php"); ?>
