<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/guides.php';

require_admin();
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false) {
    redirect('admin_guides.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_admin_post();
    $category = filter_var($_POST['category'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $title = guide_text($_POST['title'] ?? '', 150);
    $slug = guide_text($_POST['slug'] ?? '', 150);
    $steps = guide_normalize_steps($_POST['steps'] ?? []);
    $videoUrl = guide_text($_POST['video_url'] ?? '', 255);

    if ($category === false || $title === '' || $slug === '' || $steps === [] || ($videoUrl !== '' && guide_youtube_embed_url($videoUrl) === null)) {
        flash('error', 'Provide a category, title, slug, at least one step, and an approved YouTube URL when adding a video.');
        redirect('edit_guide.php?id=' . $id);
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

    if (!guide_exists($conn, $id)) {
        flash('error', 'That guide no longer exists.');
        redirect('admin_guides.php');
    }

    if (!guide_category_exists($conn, $category)) {
        flash('error', 'Choose an existing category.');
        redirect('edit_guide.php?id=' . $id);
    }

    try {
        in_transaction($conn, static function () use ($conn, $id, $category, $title, $slug, $description, $difficulty, $time, $risk, $platformVersion, $tools, $prerequisites, $backupWarning, $nextActions, $videoUrl, $steps): void {
            $update = $conn->prepare(
                'UPDATE guides SET category_id = ?, title = ?, slug = ?, description = ?, difficulty = ?, estimated_time = ?, risk_level = ?, platform_version = ?, required_tools = ?, prerequisites = ?, backup_warning = ?, last_reviewed_at = UTC_DATE(), next_actions = ?, video_url = ? WHERE id = ?'
            );
            $update->bind_param('issssssssssssi', $category, $title, $slug, $description, $difficulty, $time, $risk, $platformVersion, $tools, $prerequisites, $backupWarning, $nextActions, $videoUrl, $id);
            $update->execute();
            $update->close();
            $stepChanges = guide_sync_steps($conn, $id, $steps);
            guide_replace_tools($conn, $id, $tools);
            admin_audit($conn, 'guide.update', 'guide', $id, [
                'slug' => $slug,
                'category_id' => $category,
                'steps_added' => $stepChanges['added'],
                'steps_updated' => $stepChanges['updated'],
                'steps_deleted' => $stepChanges['deleted'],
                'progress_rows_deleted' => $stepChanges['deleted_progress'],
            ]);
        });
    } catch (mysqli_sql_exception $exception) {
        if ($exception->getCode() !== 1062) {
            throw $exception;
        }

        flash('error', 'That guide slug is already in use.');
        redirect('edit_guide.php?id=' . $id);
    } catch (DomainException $exception) {
        flash('error', 'The submitted step list is invalid or out of date. Reload the guide and try again.');
        redirect('edit_guide.php?id=' . $id);
    }

    flash('success', 'Structured guide updated.');
    redirect('admin_guides.php');
}

$guideStatement = $conn->prepare('SELECT * FROM guides WHERE id = ?');
$guideStatement->bind_param('i', $id);
$guideStatement->execute();
$guide = $guideStatement->get_result()->fetch_assoc();

if ($guide === null) {
    redirect('admin_guides.php');
}

$categories = $conn->query('SELECT id, name FROM categories ORDER BY name');
$stepsStatement = $conn->prepare('SELECT * FROM guide_steps WHERE guide_id = ? ORDER BY step_number');
$stepsStatement->bind_param('i', $id);
$stepsStatement->execute();
$steps = $stepsStatement->get_result();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="auth-page"><div class="auth-card" style="max-width:900px;"><h1>Edit structured guide</h1>
<form method="POST"><?php echo csrf_field(); ?>
<label for="category">Category</label><select id="category" name="category" required><?php while ($category = $categories->fetch_assoc()): ?><option value="<?php echo (int) $category['id']; ?>"<?php echo (int) $category['id'] === (int) $guide['category_id'] ? ' selected' : ''; ?>><?php echo e($category['name']); ?></option><?php endwhile; ?></select>
<label for="title">Title</label><input id="title" name="title" value="<?php echo e($guide['title']); ?>" required><label for="slug">Slug</label><input id="slug" name="slug" value="<?php echo e($guide['slug']); ?>" required><label for="description">Description</label><textarea id="description" name="description"><?php echo e($guide['description']); ?></textarea>
<div class="content-grid"><div><label for="difficulty">Difficulty</label><input id="difficulty" name="difficulty" value="<?php echo e($guide['difficulty']); ?>"></div><div><label for="estimated-time">Estimated time</label><input id="estimated-time" name="estimated_time" value="<?php echo e($guide['estimated_time']); ?>"></div><div><label for="risk">Risk level</label><input id="risk" name="risk_level" value="<?php echo e($guide['risk_level']); ?>"></div></div>
<label for="platform-version">Platform/version</label><input id="platform-version" name="platform_version" value="<?php echo e($guide['platform_version']); ?>"><label for="tools">Required tools</label><textarea id="tools" name="required_tools"><?php echo e($guide['required_tools']); ?></textarea><label for="prerequisites">Prerequisites</label><textarea id="prerequisites" name="prerequisites"><?php echo e($guide['prerequisites']); ?></textarea><label for="backup-warning">Backup and safety warning</label><textarea id="backup-warning" name="backup_warning"><?php echo e($guide['backup_warning']); ?></textarea><label for="next-actions">Next actions</label><textarea id="next-actions" name="next_actions"><?php echo e($guide['next_actions']); ?></textarea><label for="video-url">Optional YouTube URL</label><input id="video-url" name="video_url" type="url" value="<?php echo e($guide['video_url']); ?>">
<h2>Steps</h2><p>Reordering and text edits preserve saved progress. Removing a step deletes progress for that step only.</p><div id="stepsContainer"><?php $stepIndex = 0; while ($step = $steps->fetch_assoc()): ?><fieldset class="step-editor"><legend>Step <?php echo ++$stepIndex; ?></legend><input type="hidden" data-step-field="id" name="steps[<?php echo $stepIndex - 1; ?>][id]" value="<?php echo (int) $step['id']; ?>"><label>Title <input data-step-field="title" name="steps[<?php echo $stepIndex - 1; ?>][title]" maxlength="180" value="<?php echo e($step['step_title']); ?>"></label><label>Action <textarea data-step-field="text" name="steps[<?php echo $stepIndex - 1; ?>][text]" required><?php echo e($step['step_text']); ?></textarea></label><label>Expected result <textarea data-step-field="expected_result" name="steps[<?php echo $stepIndex - 1; ?>][expected_result]"><?php echo e($step['expected_result']); ?></textarea></label><label>Warning <textarea data-step-field="warning_text" name="steps[<?php echo $stepIndex - 1; ?>][warning_text]"><?php echo e($step['warning_text']); ?></textarea></label><label>Recovery path <textarea data-step-field="recovery_text" name="steps[<?php echo $stepIndex - 1; ?>][recovery_text]"><?php echo e($step['recovery_text']); ?></textarea></label><label>Image URL <input data-step-field="image_url" type="url" name="steps[<?php echo $stepIndex - 1; ?>][image_url]" maxlength="255" value="<?php echo e($step['image_url']); ?>"></label><label>Image alt text <input data-step-field="image_alt" name="steps[<?php echo $stepIndex - 1; ?>][image_alt]" maxlength="255" value="<?php echo e($step['image_alt']); ?>"></label><label>Video timestamp (seconds) <input data-step-field="video_timestamp" type="number" min="0" max="86400" name="steps[<?php echo $stepIndex - 1; ?>][video_timestamp]" value="<?php echo e($step['video_timestamp']); ?>"></label><div class="step-editor-actions"><button type="button" data-step-move="up">Move up</button><button type="button" data-step-move="down">Move down</button><button type="button" data-step-remove>Remove step</button></div></fieldset><?php endwhile; ?></div>
<button class="secondary-btn" type="button" id="add-step">Add step</button><button type="submit">Save changes</button></form></div></section>
<script src="<?php echo e(asset_url('js/guide-editor.js')); ?>"></script>
<?php include __DIR__ . '/includes/footer.php';
