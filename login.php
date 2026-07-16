<?php
require_once __DIR__ . '/config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_csrf();
    $email = trim((string) ($_POST["email"] ?? ''));
    $password = (string) ($_POST["password"] ?? '');

    if (!rate_limit_allows('login', 5, 900)) {
        $message = "Too many login attempts. Please try again later.";
    } elseif ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email or password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = (int) $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"] = $user["role"];

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

            <button type="submit">Login</button>
        </form>

        <p class="auth-link">
            Don't have an account?
            <a href="register.php">Register</a>
        </p>
    </div>
</section>

<?php include("includes/footer.php"); ?>
