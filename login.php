<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $message = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"] = $user["role"];

            header("Location: index.php");
            exit;
        } else {
            $message = "Invalid email or password.";
        }
    }
}
?>

<section class="auth-page">
    <div class="auth-card">
        <h1>Login</h1>
        <p>Welcome back to GuideMyPC.</p>

        <?php if (!empty($message)): ?>
            <div class="auth-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <p class="auth-link">
            Don't have an account?
            <a href="register.php">Register</a>
        </p>
    </div>
</section>

<?php include("includes/footer.php"); ?>