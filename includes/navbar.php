<nav class="navbar">
    <div class="logo">
        <div class="logo-box">G</div>
        <div>
            <h3>GuideMyPC</h3>
            <span>Your Trusted Tech Support</span>
        </div>
    </div>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="guides.php">Guides</a>
        <a href="downloads.php">Downloads</a>
        <a href="community.php">Community</a>
        <a href="ai.php">AI Assistant</a>
    </div>

    <div class="auth-buttons">
        <?php if (isset($_SESSION["user_id"])): ?>

            <span class="user-name">
                Hello, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
            </span>

            <a class="secondary-btn" href="profile.php">Profile</a>

            <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                <a class="secondary-btn" href="admin.php">Admin</a>
            <?php endif; ?>

            <form action="logout.php" method="POST" class="logout-form">
                <?php echo csrf_field(); ?>
                <button class="login-btn" type="submit">Logout</button>
            </form>

        <?php else: ?>

            <a class="secondary-btn" href="register.php">Register</a>
            <a class="login-btn" href="login.php">Login</a>

        <?php endif; ?>
    </div>
</nav>
