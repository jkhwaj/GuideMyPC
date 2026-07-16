<?php
require_once __DIR__ . '/config.php';

$slug = $_GET["slug"] ?? "";

$sql = "
    SELECT guides.*,
           categories.name AS category_name,
           categories.slug AS category_slug
    FROM guides
    JOIN categories ON guides.category_id = categories.id
     WHERE guides.slug = ?
       AND guides.is_published = 1
       AND categories.is_published = 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();

$result = $stmt->get_result();
$guide = $result->fetch_assoc();

if (!$guide) {
    abort_request(404, 'guide_not_found', 'The requested guide was not found.');
}

/*
 * Count one view per guide during the current browser session.
 * Refreshing the same guide will not repeatedly increase the counter.
 */
if (!isset($_SESSION["viewed_guides"])) {
    $_SESSION["viewed_guides"] = [];
}

$guideId = (int) $guide["id"];

if (!in_array($guideId, $_SESSION["viewed_guides"], true)) {
    $viewStmt = $conn->prepare("
        UPDATE guides
        SET views = views + 1
        WHERE id = ?
    ");

    $viewStmt->bind_param("i", $guideId);
    $viewStmt->execute();
    $viewStmt->close();

    $_SESSION["viewed_guides"][] = $guideId;

    $guide["views"] = (int) $guide["views"] + 1;
}

$userId = $_SESSION["user_id"] ?? 0;

$isFavorite = false;

if ($userId) {
    $favStmt = $conn->prepare("
        SELECT id
        FROM favorites
        WHERE user_id = ? AND guide_id = ?
    ");

    $favStmt->bind_param("ii", $userId, $guide["id"]);
    $favStmt->execute();

    $isFavorite = $favStmt->get_result()->num_rows > 0;

    $favStmt->close();
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

if ($userId) {
    $userRatingStmt = $conn->prepare("
        SELECT rating
        FROM guide_ratings
        WHERE guide_id = ? AND user_id = ?
    ");

    $userRatingStmt->bind_param("ii", $guide["id"], $userId);
    $userRatingStmt->execute();

    $userRatingResult = $userRatingStmt->get_result();

    if ($userRatingResult->num_rows > 0) {
        $userRating = (int) $userRatingResult->fetch_assoc()["rating"];
    }

    $userRatingStmt->close();
}

$pageTitle = $guide['title'] . ' | GuideMyPC';
$pageDescription = $guide['description'] ?: 'Follow clear, safety-conscious troubleshooting steps.';
$canonicalPath = 'guide.php?slug=' . rawurlencode($guide['slug']);

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="guide-page">
    <a
        class="back-link"
        href="guides.php?category=<?php echo urlencode($guide["category_slug"]); ?>"
    >
        ← Back to
        <?php echo htmlspecialchars($guide["category_name"]); ?>
        Guides
    </a>

    <p class="section-label">
        <?php echo htmlspecialchars($guide["category_name"]); ?> Guide
    </p>

    <h1>
        <?php echo htmlspecialchars($guide["title"]); ?>
    </h1>

    <p class="guide-description">
        <?php echo htmlspecialchars($guide["description"]); ?>
    </p>

    <?php if ($userId): ?>
        <form action="toggle_favorite.php" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="guide_id" value="<?php echo (int) $guide["id"]; ?>">
            <input type="hidden" name="slug" value="<?php echo e($guide["slug"]); ?>">
            <button class="favorite-btn" type="submit">
                <?php echo $isFavorite ? "Remove from Favorites" : "Add to Favorites"; ?>
            </button>
        </form>
    <?php else: ?>
        <p class="meta">Login to save this guide to favorites.</p>
    <?php endif; ?>

    <div class="guide-meta-grid">
        <div class="meta-card">
            <span>Difficulty</span>

            <strong>
                <?php echo htmlspecialchars($guide["difficulty"]); ?>
            </strong>
        </div>

        <div class="meta-card">
            <span>Estimated Time</span>

            <strong>
                <?php echo htmlspecialchars($guide["estimated_time"]); ?>
            </strong>
        </div>

        <div class="meta-card">
            <span>Risk Level</span>

            <strong>
                <?php echo htmlspecialchars($guide["risk_level"]); ?>
            </strong>
        </div>

        <div class="meta-card">
            <span>Views</span>

            <strong>
                👁️ <?php echo number_format((int) $guide["views"]); ?>
            </strong>
        </div>
    </div>

    <div class="guide-rating">
        <h3>Guide Rating</h3>

        <p>
            ⭐ <?php echo $ratingData["average_rating"] ?? "0"; ?> / 5
            (<?php echo (int) $ratingData["total_ratings"]; ?> Ratings)
        </p>

        <?php if ($userId): ?>
            <form action="rate_guide.php" method="POST">
                <?php echo csrf_field(); ?>
                <input
                    type="hidden"
                    name="guide_id"
                    value="<?php echo (int) $guide["id"]; ?>"
                >

                <input
                    type="hidden"
                    name="slug"
                    value="<?php echo htmlspecialchars($guide["slug"]); ?>"
                >

                <label class="sr-only" for="guide-rating">Your rating</label>
                <select id="guide-rating" name="rating" required>
                    <option value="">Rate this guide</option>

                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option
                            value="<?php echo $i; ?>"
                            <?php echo $userRating === $i ? "selected" : ""; ?>
                        >
                            <?php echo $i; ?>
                            Star<?php echo $i > 1 ? "s" : ""; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <button type="submit">
                    Save Rating
                </button>
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

    <div
        id="completedMessage"
        class="completed-message"
        style="display: none;"
    >
        🎉 Guide Completed! Great job completing all steps.
    </div>

    <div class="guide-content">
        <h2>Step-by-step guide</h2>

        <?php if ($stepsResult && $stepsResult->num_rows > 0): ?>
            <div class="steps-list">
                <?php while ($step = $stepsResult->fetch_assoc()): ?>
                    <div
                        class="step-card <?php
                            echo $step["progress_id"] ? "completed" : "";
                        ?>"
                    >
                        <span class="step-title">
                            Step <?php echo (int) $step["step_number"]; ?>
                        </span>

                        <p>
                            <?php echo htmlspecialchars($step["step_text"]); ?>
                        </p>

                        <?php if ($userId): ?>
                            <form action="save_progress.php" method="POST" class="step-progress-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="step_id" value="<?php echo (int) $step["id"]; ?>">
                                <input type="hidden" name="guide_slug" value="<?php echo e($guide["slug"]); ?>">
                                <input
                                    type="hidden"
                                    name="completed"
                                    value="<?php echo $step["progress_id"] ? '0' : '1'; ?>"
                                >
                                <button class="complete-btn" type="submit">
                                    <?php echo $step["progress_id"] ? "Mark as Incomplete" : "Mark as Completed"; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="complete-btn" type="button" disabled>Sign in to save progress</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>
                <?php
                echo nl2br(
                    htmlspecialchars($guide["content"] ?? "")
                );
                ?>
            </p>
        <?php endif; ?>
    </div>
</section>

<?php
$stmt->close();
$stepsStmt->close();
$ratingStmt->close();

include("includes/footer.php");
?>
