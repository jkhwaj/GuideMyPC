<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/guides.php';
require_once __DIR__ . '/includes/accounts.php';

$slug = required_string($_GET['slug'] ?? null, 150) ?? '';
$statement = $conn->prepare(
    'SELECT guides.*, categories.name AS category_name, categories.slug AS category_slug '
    . 'FROM guides JOIN categories ON guides.category_id = categories.id '
    . 'WHERE guides.slug = ? AND guides.is_published = 1 AND categories.is_published = 1'
);
$statement->bind_param('s', $slug);
$statement->execute();
$guide = $statement->get_result()->fetch_assoc();

if ($guide === null) {
    abort_request(404, 'guide_not_found', 'The requested guide was not found.');
}

$guideId = (int) $guide['id'];

if (!isset($_SESSION['viewed_guides'])) {
    $_SESSION['viewed_guides'] = [];
}

if (!in_array($guideId, $_SESSION['viewed_guides'], true)) {
    $viewStatement = $conn->prepare('UPDATE guides SET views = views + 1 WHERE id = ?');
    $viewStatement->bind_param('i', $guideId);
    $viewStatement->execute();
    $viewStatement->close();
    $_SESSION['viewed_guides'][] = $guideId;
    $guide['views'] = (int) $guide['views'] + 1;

    if (current_user_id() > 0) {
        record_user_activity($conn, current_user_id(), 'guide_view', 'guide', $guide['slug']);
    }
}

$userId = current_user_id();

if ($userId > 0 && !empty($_SESSION['_guest_progress'][$guideId]) && is_array($_SESSION['_guest_progress'][$guideId])) {
    guide_merge_guest_progress($conn, $userId, $guideId, array_keys($_SESSION['_guest_progress'][$guideId]));
    unset($_SESSION['_guest_progress'][$guideId]);
    flash('success', 'Your browser-session guide progress was saved to your account.');
}

$favoriteStatement = $conn->prepare('SELECT id FROM favorites WHERE user_id = ? AND guide_id = ?');
$favoriteStatement->bind_param('ii', $userId, $guideId);
$favoriteStatement->execute();
$isFavorite = $userId > 0 && $favoriteStatement->get_result()->num_rows > 0;
$favoriteStatement->close();

$stepsStatement = $conn->prepare(
    'SELECT guide_steps.*, user_progress.id AS progress_id FROM guide_steps '
    . 'LEFT JOIN user_progress ON guide_steps.id = user_progress.guide_step_id AND user_progress.user_id = ? '
    . 'WHERE guide_steps.guide_id = ? ORDER BY guide_steps.step_number ASC'
);
$stepsStatement->bind_param('ii', $userId, $guideId);
$stepsStatement->execute();
$steps = $stepsStatement->get_result();

$toolsStatement = $conn->prepare('SELECT name FROM guide_tools WHERE guide_id = ? ORDER BY sort_order, name');
$toolsStatement->bind_param('i', $guideId);
$toolsStatement->execute();
$tools = [];
$toolResult = $toolsStatement->get_result();

while ($tool = $toolResult->fetch_assoc()) {
    $tools[] = $tool['name'];
}
$toolsStatement->close();

if ($tools === [] && guide_text($guide['required_tools'] ?? '', 2000) !== '') {
    $tools = array_values(array_filter(array_map(static fn (string $tool): string => trim($tool), preg_split('/[\r\n,]+/', $guide['required_tools']) ?: [])));
}

$sourceStatement = $conn->prepare('SELECT title, official_url FROM guide_sources WHERE guide_id = ? ORDER BY sort_order, id');
$sourceStatement->bind_param('i', $guideId);
$sourceStatement->execute();
$sources = [];
$sourceResult = $sourceStatement->get_result();

while ($source = $sourceResult->fetch_assoc()) {
    if (guide_safe_url($source['official_url']) !== null) {
        $sources[] = $source;
    }
}
$sourceStatement->close();

$relatedStatement = $conn->prepare(
    "SELECT knowledge_articles.title, knowledge_articles.slug FROM knowledge_relations "
    . "JOIN knowledge_articles ON knowledge_relations.article_id = knowledge_articles.id "
    . "WHERE knowledge_relations.guide_id = ? AND knowledge_articles.publication_state = 'published' ORDER BY knowledge_relations.sort_order LIMIT 4"
);
$relatedStatement->bind_param('i', $guideId);
$relatedStatement->execute();
$related = $relatedStatement->get_result();

$ratingStatement = $conn->prepare('SELECT ROUND(AVG(rating), 1) AS average_rating, COUNT(*) AS total_ratings FROM guide_ratings WHERE guide_id = ?');
$ratingStatement->bind_param('i', $guideId);
$ratingStatement->execute();
$ratingData = $ratingStatement->get_result()->fetch_assoc();

$userRating = 0;

if ($userId > 0) {
    $userRatingStatement = $conn->prepare('SELECT rating FROM guide_ratings WHERE guide_id = ? AND user_id = ?');
    $userRatingStatement->bind_param('ii', $guideId, $userId);
    $userRatingStatement->execute();
    $rating = $userRatingStatement->get_result()->fetch_assoc();
    $userRating = (int) ($rating['rating'] ?? 0);
    $userRatingStatement->close();
}

$pageTitle = $guide['title'] . ' | GuideMyPC';
$pageDescription = $guide['description'] ?: 'Follow clear, safety-conscious troubleshooting steps.';
$canonicalPath = 'guide.php?slug=' . rawurlencode($guide['slug']);
$videoEmbedUrl = guide_youtube_embed_url($guide['video_url'] ?? null);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="guide-page" aria-labelledby="guide-title">
    <a class="back-link no-print" href="<?php echo e(application_url('guides.php?category=' . rawurlencode($guide['category_slug']))); ?>">Back to <?php echo e($guide['category_name']); ?> guides</a>
    <p class="section-label"><?php echo e($guide['category_name']); ?> guide</p>
    <div class="guide-heading-actions">
        <div>
            <h1 id="guide-title"><?php echo e($guide['title']); ?></h1>
            <p class="guide-description"><?php echo e($guide['description']); ?></p>
        </div>
        <button class="secondary-btn no-print" type="button" onclick="window.print()">Print guide</button>
    </div>

    <?php if ($userId > 0): ?>
        <form class="no-print" action="<?php echo e(application_url('toggle_favorite.php')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="guide_id" value="<?php echo $guideId; ?>">
            <input type="hidden" name="slug" value="<?php echo e($guide['slug']); ?>">
            <button class="favorite-btn" type="submit"><?php echo $isFavorite ? 'Remove from favorites' : 'Add to favorites'; ?></button>
        </form>
    <?php else: ?>
        <p class="meta no-print">Save favorites and keep progress by <a href="<?php echo e(application_url('login.php')); ?>">signing in</a>.</p>
    <?php endif; ?>

    <dl class="guide-meta-grid">
        <div class="meta-card"><dt>Platform</dt><dd><?php echo e($guide['category_name'] . ($guide['platform_version'] ? ' · ' . $guide['platform_version'] : '')); ?></dd></div>
        <div class="meta-card"><dt>Difficulty</dt><dd><?php echo e($guide['difficulty'] ?: 'Not specified'); ?></dd></div>
        <div class="meta-card"><dt>Estimated time</dt><dd><?php echo e($guide['estimated_time'] ?: 'Not specified'); ?></dd></div>
        <div class="meta-card"><dt>Risk level</dt><dd><?php echo e($guide['risk_level'] ?: 'Review each warning'); ?></dd></div>
        <div class="meta-card"><dt>Last reviewed</dt><dd><?php echo e($guide['last_reviewed_at'] ?: 'Not yet reviewed'); ?></dd></div>
        <div class="meta-card"><dt>Views</dt><dd><?php echo number_format((int) $guide['views']); ?></dd></div>
    </dl>

    <div class="guide-safety-grid">
        <aside class="guide-context"><h2>Before you start</h2><p><?php echo nl2br(e($guide['prerequisites'] ?: 'Read every step before making a change.')); ?></p></aside>
        <aside class="guide-warning" role="alert"><h2>Backup and safety</h2><p><?php echo nl2br(e($guide['backup_warning'] ?: 'No specific backup warning is listed. Stop if a step could risk data or hardware.')); ?></p></aside>
        <aside class="guide-context"><h2>Tools</h2><?php if ($tools !== []): ?><ul><?php foreach ($tools as $tool): ?><li><?php echo e($tool); ?></li><?php endforeach; ?></ul><?php else: ?><p>No special tools are listed.</p><?php endif; ?></aside>
    </div>

    <?php if ($videoEmbedUrl !== null): ?>
        <section class="guide-video no-print" aria-labelledby="guide-video-title">
            <h2 id="guide-video-title">Optional video walkthrough</h2>
            <p>The written steps below are the complete guide. Loading the video connects to YouTube.</p>
            <button class="secondary-btn" type="button" data-video-consent data-video-url="<?php echo e($videoEmbedUrl); ?>">Load privacy-enhanced YouTube video</button>
            <p><a href="<?php echo e($guide['video_url']); ?>" target="_blank" rel="noopener noreferrer">Open the video on YouTube</a></p>
            <div class="video-frame" data-video-frame></div>
        </section>
    <?php endif; ?>

    <div class="progress-box">
        <div class="progress-info"><span>Progress</span><span id="progressText">0%</span></div>
        <div class="progress-bar"><div id="progressFill"></div></div>
    </div>
    <div id="completedMessage" class="completed-message" style="display: none;">Guide complete. Review the next actions before closing this page.</div>

    <section class="guide-content" aria-labelledby="steps-heading">
        <h2 id="steps-heading">Step-by-step guide</h2>
        <?php if ($steps->num_rows > 0): ?>
            <ol class="steps-list">
                <?php while ($step = $steps->fetch_assoc()): ?>
                    <?php $stepCompleted = $userId > 0 ? (bool) $step['progress_id'] : !empty($_SESSION['_guest_progress'][$guideId][(int) $step['id']]); ?>
                    <li class="step-card <?php echo $stepCompleted ? 'completed' : ''; ?>">
                        <h3>Step <?php echo (int) $step['step_number']; ?>: <?php echo e($step['step_title'] ?: 'Follow this action'); ?></h3>
                        <?php if ($step['warning_text']): ?><aside class="step-warning" role="alert"><strong>Warning:</strong> <?php echo nl2br(e($step['warning_text'])); ?></aside><?php endif; ?>
                        <p><?php echo nl2br(e($step['step_text'])); ?></p>
                        <?php if (guide_safe_url($step['image_url']) !== null): ?><img class="guide-step-image" src="<?php echo e($step['image_url']); ?>" alt="<?php echo e($step['image_alt'] ?: 'Guide illustration'); ?>"><?php endif; ?>
                        <?php if ($step['expected_result']): ?><p class="step-result"><strong>Expected result:</strong> <?php echo e($step['expected_result']); ?></p><?php endif; ?>
                        <?php if ($step['recovery_text']): ?><p class="step-recovery"><strong>If this does not work:</strong> <?php echo e($step['recovery_text']); ?></p><?php endif; ?>
                        <?php if ($step['video_timestamp'] !== null): ?><p class="meta">Video reference: <?php echo (int) $step['video_timestamp']; ?> seconds.</p><?php endif; ?>
                        <form action="<?php echo e(application_url('save_progress.php')); ?>" method="POST" class="step-progress-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="step_id" value="<?php echo (int) $step['id']; ?>">
                            <input type="hidden" name="guide_slug" value="<?php echo e($guide['slug']); ?>">
                            <input type="hidden" name="completed" value="<?php echo $stepCompleted ? '0' : '1'; ?>">
                            <button class="complete-btn" type="submit"><?php echo $stepCompleted ? 'Mark as incomplete' : 'Mark as completed'; ?></button>
                        </form>
                        <?php if ($userId === 0): ?><p class="meta">Progress stays in this browser session. <a href="<?php echo e(application_url('login.php')); ?>">Sign in</a> to keep it.</p><?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ol>
        <?php else: ?>
            <p><?php echo nl2br(e($guide['content'] ?? 'This guide does not have steps yet.')); ?></p>
        <?php endif; ?>
    </section>

    <?php if ($guide['next_actions']): ?><aside class="guide-context"><h2>Next actions</h2><p><?php echo nl2br(e($guide['next_actions'])); ?></p></aside><?php endif; ?>
    <?php if ($sources !== []): ?><section class="guide-sources"><h2>Official sources</h2><ul><?php foreach ($sources as $source): ?><li><a href="<?php echo e($source['official_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo e($source['title']); ?></a></li><?php endforeach; ?></ul></section><?php endif; ?>
    <?php if ($related->num_rows > 0): ?><aside class="related-searches"><h2>Related help</h2><ul><?php while ($article = $related->fetch_assoc()): ?><li><a href="<?php echo e(application_url('knowledge_article.php?slug=' . rawurlencode($article['slug']))); ?>"><?php echo e($article['title']); ?></a></li><?php endwhile; ?></ul></aside><?php endif; ?>

    <section class="guide-rating no-print" aria-labelledby="rating-heading">
        <h2 id="rating-heading">Guide rating</h2><p><?php echo e((string) ($ratingData['average_rating'] ?? '0')); ?> / 5 (<?php echo (int) ($ratingData['total_ratings'] ?? 0); ?> ratings)</p>
        <?php if ($userId > 0): ?><form action="<?php echo e(application_url('rate_guide.php')); ?>" method="POST"><?php echo csrf_field(); ?><input type="hidden" name="guide_id" value="<?php echo $guideId; ?>"><input type="hidden" name="slug" value="<?php echo e($guide['slug']); ?>"><label for="guide-rating">Your rating</label><select id="guide-rating" name="rating" required><option value="">Rate this guide</option><?php for ($rating = 1; $rating <= 5; $rating++): ?><option value="<?php echo $rating; ?>"<?php echo $userRating === $rating ? ' selected' : ''; ?>><?php echo $rating; ?> star<?php echo $rating > 1 ? 's' : ''; ?></option><?php endfor; ?></select><button type="submit">Save rating</button></form><?php else: ?><p class="meta">Sign in to rate this guide.</p><?php endif; ?>
    </section>
</section>

<?php
$statement->close();
$stepsStatement->close();
$ratingStatement->close();
$relatedStatement->close();
include __DIR__ . '/includes/footer.php';
