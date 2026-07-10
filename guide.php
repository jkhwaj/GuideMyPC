<?php
include("config.php");
include("includes/header.php");
include("includes/navbar.php");

$slug = $_GET['slug'] ?? '';

$sql = "SELECT guides.*, categories.name AS category_name, categories.slug AS category_slug
        FROM guides
        JOIN categories ON guides.category_id = categories.id
        WHERE guides.slug = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$guide = $result->fetch_assoc();

if (!$guide) {
    echo "<section class='section'><h2>Guide not found</h2></section>";
    include("includes/footer.php");
    exit;
}

$userId = $_SESSION["user_id"] ?? 0;

$isFavorite = false;

if ($userId) {
    $favStmt = $conn->prepare("
        SELECT id FROM favorites
        WHERE user_id = ? AND guide_id = ?
    ");
    $favStmt->bind_param("ii", $userId, $guide["id"]);
    $favStmt->execute();
    $isFavorite = $favStmt->get_result()->num_rows > 0;
}

$stepsSql = "
    SELECT guide_steps.*,
           user_progress.id AS progress_id
    FROM guide_steps
    LEFT JOIN user_progress
        ON guide_steps.id = user_progress.guide_step_id
        AND user_progress.user_id = ?
    WHERE guide_steps.guide_id = ?
    ORDER BY guide_steps.step_number ASC
";

$stepsStmt = $conn->prepare($stepsSql);
$stepsStmt->bind_param("ii", $userId, $guide["id"]);
$stepsStmt->execute();
$stepsResult = $stepsStmt->get_result();

$ratingStmt = $conn->prepare("
    SELECT ROUND(AVG(rating), 1) AS average_rating,
           COUNT(*) AS total_ratings
    FROM guide_ratings
    WHERE guide_id = ?
");
$ratingStmt->bind_param("i", $guide["id"]);
$ratingStmt->execute();
$ratingData = $ratingStmt->get_result()->fetch_assoc();

$userRating = 0;

if (isset($_SESSION["user_id"])) {
    $userRatingStmt = $conn->prepare("
        SELECT rating
        FROM guide_ratings
        WHERE guide_id = ? AND user_id = ?
    ");
    $userRatingStmt->bind_param("ii", $guide["id"], $_SESSION["user_id"]);
    $userRatingStmt->execute();
    $userRatingResult = $userRatingStmt->get_result();

    if ($userRatingResult->num_rows > 0) {
        $userRating = $userRatingResult->fetch_assoc()["rating"];
    }
}
?>

<section class="guide-page">
    <a class="back-link" href="guides.php?category=<?php echo $guide['category_slug']; ?>">
        ← Back to <?php echo $guide['category_name']; ?> Guides
    </a>

    <p class="section-label"><?php echo $guide['category_name']; ?> Guide</p>

    <h1><?php echo $guide['title']; ?></h1>

    <p class="guide-description">
        <?php echo $guide['description']; ?>
    </p>

    <?php if(isset($_SESSION["user_id"])): ?>
        <a class="favorite-btn" href="toggle_favorite.php?guide_id=<?php echo $guide["id"]; ?>&slug=<?php echo urlencode($guide["slug"]); ?>">
            <?php echo $isFavorite ? "💔 Remove from Favorites" : "❤️ Add to Favorites"; ?>
        </a>
    <?php else: ?>
        <p class="meta">Login to save this guide to favorites.</p>
    <?php endif; ?>

    <div class="guide-meta-grid">
        <div class="meta-card">
            <span>Difficulty</span>
            <strong><?php echo $guide['difficulty']; ?></strong>
        </div>

        <div class="meta-card">
            <span>Estimated Time</span>
            <strong><?php echo $guide['estimated_time']; ?></strong>
        </div>

        <div class="meta-card">
            <span>Risk Level</span>
            <strong><?php echo $guide['risk_level']; ?></strong>
        </div>
    </div>

    <div class="guide-rating">
        <h3>Guide Rating</h3>

        <p>
            ⭐ <?php echo $ratingData["average_rating"] ?? "0"; ?> / 5
            (<?php echo $ratingData["total_ratings"]; ?> Ratings)
        </p>

        <?php if(isset($_SESSION["user_id"])): ?>
            <form action="rate_guide.php" method="POST">
                <input type="hidden" name="guide_id" value="<?php echo $guide["id"]; ?>">
                <input type="hidden" name="slug" value="<?php echo htmlspecialchars($guide["slug"]); ?>">

                <select name="rating" required>
                    <option value="">Rate this guide</option>

                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $userRating == $i ? "selected" : ""; ?>>
                            <?php echo $i; ?> Star<?php echo $i > 1 ? "s" : ""; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <button type="submit">Save Rating</button>
            </form>
        <?php else: ?>
            <p class="meta">Login to rate this guide.</p>
        <?php endif; ?>
    </div>

    <div class="progress-box">
        <div class="progress-info">
            <span>Progress</span>
            <span id="progressText">0%</span>
        </div>

        <div class="progress-bar">
            <div id="progressFill"></div>
        </div>
    </div>

    <div id="completedMessage" class="completed-message" style="display:none;">
        🎉 Guide Completed! Great job completing all steps.
    </div>

    <div class="guide-content">
        <h2>Step-by-step guide</h2>

        <?php if ($stepsResult && $stepsResult->num_rows > 0): ?>
            <div class="steps-list">
                <?php while ($step = $stepsResult->fetch_assoc()): ?>
                    <div class="step-card <?php echo $step['progress_id'] ? 'completed' : ''; ?>">
                        <span class="step-title">
                            Step <?php echo $step['step_number']; ?>
                        </span>

                        <p>
                            <?php echo htmlspecialchars($step['step_text']); ?>
                        </p>

                        <button
                            class="complete-btn"
                            data-step-id="<?php echo $step['id']; ?>"
                            onclick="toggleStep(this)">
                            <?php echo $step['progress_id'] ? '✓ Completed' : 'Mark as Completed'; ?>
                        </button>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p><?php echo nl2br($guide['content']); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php include("includes/footer.php"); ?>