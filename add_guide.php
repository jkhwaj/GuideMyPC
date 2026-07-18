<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/guides.php';

require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_admin_post();
    $category = filter_var($_POST['category'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $title = guide_text($_POST['title'] ?? '', 150);
    $slug = guide_text($_POST['slug'] ?? '', 150);
    $steps = guide_normalize_steps($_POST['steps'] ?? []);
    $videoUrl = guide_text($_POST['video_url'] ?? '', 255);

    if ($category === false || $title === '' || $slug === '' || $steps === [] || ($videoUrl !== '' && guide_youtube_embed_url($videoUrl) === null)) {
        flash('error', 'Provide a category, title, slug, at least one step, and an approved YouTube URL when adding a video.');
        redirect('add_guide.php');
    }

    $description = guide_text($_POST['description'] ?? '', 5000);
    $difficulty = guide_text($_POST['difficulty'] ?? '', 50);
    $time = guide_text($_POST['estimated_time'] ?? '', 50);
    $risk = guide_text($_POST['risk_level'] ?? '', 50);
    $platformVersion = guide_text($_POST['platform_version'] ?? '', 100);
    $tools = guide_text($_POST['required_tools'] ?? '', 2000);
    $prerequisites = guide_text($_POST['prerequisites'] ?? '', 5000);
    $backupWarning = guide_text($_POST['backup_warning'] ?? '', 5000);
    $nextActions = guide_text($_POST['next_actions'] ?? '', 5000);
    $content = '';

    if (!guide_category_exists($conn, $category)) {
        flash('error', 'Choose an existing category.');
        redirect('add_guide.php');
    }

    try {
        in_transaction($conn, static function () use ($conn, $category, $title, $slug, $description, $difficulty, $time, $risk, $platformVersion, $tools, $prerequisites, $backupWarning, $nextActions, $videoUrl, $content, $steps): void {
            $insert = $conn->prepare(
                'INSERT INTO guides (category_id, title, slug, description, difficulty, estimated_time, risk_level, content, platform_version, required_tools, prerequisites, backup_warning, last_reviewed_at, next_actions, video_url) '
                . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UTC_DATE(), ?, ?)'
            );
            $insert->bind_param('isssssssssssss', $category, $title, $slug, $description, $difficulty, $time, $risk, $content, $platformVersion, $tools, $prerequisites, $backupWarning, $nextActions, $videoUrl);
            $insert->execute();
            $guideId = $insert->insert_id;
            $insert->close();
            guide_replace_steps($conn, $guideId, $steps);
            guide_replace_tools($conn, $guideId, $tools);
            admin_audit($conn, 'guide.create', 'guide', $guideId, ['slug' => $slug, 'category_id' => $category]);
        });
    } catch (mysqli_sql_exception $exception) {
        if ($exception->getCode() !== 1062) {
            throw $exception;
        }

        flash('error', 'That guide slug is already in use.');
        redirect('add_guide.php');
    }

    flash('success', 'Structured guide created.');
    redirect('admin_guides.php');
}

$categories = $conn->query('SELECT id, name FROM categories ORDER BY name');
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="auth-page"><div class="auth-card" style="max-width:900px;"><h1>Add structured guide</h1>
<form method="POST"><?php echo csrf_field(); ?>
<label for="category">Category</label><select id="category" name="category" required><?php while ($category = $categories->fetch_assoc()): ?><option value="<?php echo (int) $category['id']; ?>"><?php echo e($category['name']); ?></option><?php endwhile; ?></select>
<label for="title">Title</label><input id="title" name="title" required>
<label for="slug">Slug</label><input id="slug" name="slug" required>
<label for="description">Description</label><textarea id="description" name="description"></textarea>
<div class="content-grid"><div><label for="difficulty">Difficulty</label><input id="difficulty" name="difficulty"></div><div><label for="estimated-time">Estimated time</label><input id="estimated-time" name="estimated_time"></div><div><label for="risk">Risk level</label><input id="risk" name="risk_level"></div></div>
<label for="platform-version">Platform/version</label><input id="platform-version" name="platform_version" placeholder="Windows 11 23H2">
<label for="tools">Required tools</label><textarea id="tools" name="required_tools" placeholder="One tool per line or comma-separated"></textarea>
<label for="prerequisites">Prerequisites</label><textarea id="prerequisites" name="prerequisites"></textarea>
<label for="backup-warning">Backup and safety warning</label><textarea id="backup-warning" name="backup_warning"></textarea>
<label for="next-actions">Next actions</label><textarea id="next-actions" name="next_actions"></textarea>
<label for="video-url">Optional YouTube URL</label><input id="video-url" name="video_url" type="url" placeholder="https://www.youtube.com/watch?v=...">
<h2>Steps</h2><div id="stepsContainer"><fieldset class="step-editor"><legend>Step 1</legend><label>Title <input data-step-field="title" name="steps[0][title]" maxlength="180"></label><label>Action <textarea data-step-field="text" name="steps[0][text]" required></textarea></label><label>Expected result <textarea data-step-field="expected_result" name="steps[0][expected_result]"></textarea></label><label>Warning <textarea data-step-field="warning_text" name="steps[0][warning_text]"></textarea></label><label>Recovery path <textarea data-step-field="recovery_text" name="steps[0][recovery_text]"></textarea></label><label>Image URL <input data-step-field="image_url" type="url" name="steps[0][image_url]" maxlength="255"></label><label>Image alt text <input data-step-field="image_alt" name="steps[0][image_alt]" maxlength="255"></label><label>Video timestamp (seconds) <input data-step-field="video_timestamp" type="number" min="0" max="86400" name="steps[0][video_timestamp]"></label><div class="step-editor-actions"><button type="button" data-step-move="up">Move up</button><button type="button" data-step-move="down">Move down</button><button type="button" data-step-remove>Remove step</button></div></fieldset></div>
<button class="secondary-btn" type="button" id="add-step">Add step</button><button type="submit">Save guide</button>
</form></div></section>
<script src="<?php echo e(asset_url('js/guide-editor.js')); ?>"></script>
<?php include __DIR__ . '/includes/footer.php';
