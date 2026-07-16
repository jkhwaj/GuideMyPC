<?php
require_once __DIR__ . '/config.php';
include("includes/header.php");
include("includes/navbar.php");

require_login();

$userId = current_user_id();

$userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

$completedStmt = $conn->prepare("
    SELECT COUNT(*) AS completed_steps
    FROM user_progress
    WHERE user_id = ?
");
$completedStmt->bind_param("i", $userId);
$completedStmt->execute();
$completed = $completedStmt->get_result()->fetch_assoc();

$guidesStartedStmt = $conn->prepare("
    SELECT COUNT(DISTINCT guide_steps.guide_id) AS guides_started
    FROM user_progress
    JOIN guide_steps ON user_progress.guide_step_id = guide_steps.id
    WHERE user_progress.user_id = ?
");
$guidesStartedStmt->bind_param("i", $userId);
$guidesStartedStmt->execute();
$started = $guidesStartedStmt->get_result()->fetch_assoc();

$favoritesStmt = $conn->prepare("
    SELECT guides.title, guides.slug, guides.description
    FROM favorites
    JOIN guides ON favorites.guide_id = guides.id
    WHERE favorites.user_id = ?
    ORDER BY favorites.created_at DESC
");
$favoritesStmt->bind_param("i", $userId);
$favoritesStmt->execute();
$favorites = $favoritesStmt->get_result();
?>

<section class="profile-page">
    <div class="profile-card">
        <h1>My Profile</h1>
        <p>Welcome back, <?php echo htmlspecialchars($user["full_name"]); ?>.</p>

        <div class="profile-info">
            <div>
                <span>Name</span>
                <strong><?php echo htmlspecialchars($user["full_name"]); ?></strong>
            </div>

            <div>
                <span>Email</span>
                <strong><?php echo htmlspecialchars($user["email"]); ?></strong>
            </div>

            <div>
                <span>Role</span>
                <strong><?php echo htmlspecialchars($user["role"]); ?></strong>
            </div>
        </div>
    </div>

    <div class="profile-stats">
        <div class="stat-card">
            <span>Completed Steps</span>
            <strong><?php echo $completed["completed_steps"]; ?></strong>
        </div>

        <div class="stat-card">
            <span>Guides Started</span>
            <strong><?php echo $started["guides_started"]; ?></strong>
        </div>
    </div>

    <div class="profile-card" style="margin-top:24px;">
        <h1 style="font-size:32px;">❤️ My Favorite Guides</h1>

        <?php if ($favorites && $favorites->num_rows > 0): ?>
            <div class="steps-list" style="margin-top:24px;">
                <?php while($fav = $favorites->fetch_assoc()): ?>
                    <a class="step-card" href="guide.php?slug=<?php echo htmlspecialchars($fav["slug"]); ?>">
                        <span><?php echo htmlspecialchars($fav["title"]); ?></span>
                        <p><?php echo htmlspecialchars($fav["description"]); ?></p>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="margin-top:20px;">You have no favorite guides yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php include("includes/footer.php"); ?>
