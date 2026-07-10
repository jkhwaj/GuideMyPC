<?php
include("config.php");
include("includes/header.php");
include("includes/navbar.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($full_name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $full_name, $email, $hashedPassword);

        if ($stmt->execute()) {
            $message = "Account created successfully. You can now log in.";
        } else {
            $message = "Email already exists or registration failed.";
        }
    }
}
?>

<section class="auth-page">
    <div class="auth-card">
        <h1>Create Account</h1>
        <p>Join GuideMyPC and save your troubleshooting progress.</p>

        <?php if (!empty($message)): ?>
            <div class="auth-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="full_name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Register</button>
        </form>

        <p class="auth-link">
            Already have an account?
            <a href="login.php">Login</a>
        </p>
    </div>
</section>

<?php include("includes/footer.php"); ?>