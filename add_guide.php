<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/guides.php';

require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();
    $category = filter_var($_POST['category'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $title = guide_text($_POST['title'] ?? '', 150);
    $slug = guide_text($_POST['slug'] ?? '', 150);
    $steps = guide_normalize_steps($_POST['steps'] ?? []);
    $videoUrl = guide_text($_POST['video_url'] ?? '', 500);

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
    });

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
<h2>Steps</h2><div id="stepsContainer"><fieldset class="step-editor"><legend>Step 1</legend><label>Title <input name="steps[0][title]"></label><label>Action <textarea name="steps[0][text]" required></textarea></label><label>Expected result <textarea name="steps[0][expected_result]"></textarea></label><label>Warning <textarea name="steps[0][warning_text]"></textarea></label><label>Recovery path <textarea name="steps[0][recovery_text]"></textarea></label></fieldset></div>
<button class="secondary-btn" type="button" id="add-step">Add step</button><button type="submit">Save guide</button>
</form></div></section>
<script>document.getElementById('add-step').addEventListener('click',function(){const c=document.getElementById('stepsContainer'),n=c.children.length,f=document.createElement('fieldset'),l=document.createElement('legend');l.textContent='Step '+(n+1);f.className='step-editor';f.append(l);[['title','Title','input'],['text','Action','textarea'],['expected_result','Expected result','textarea'],['warning_text','Warning','textarea'],['recovery_text','Recovery path','textarea']].forEach(function(d){const x=document.createElement('label'),e=document.createElement(d[2]);x.textContent=d[1]+' ';e.name='steps['+n+']['+d[0]+']';if(d[0]==='text'){e.required=true;}x.append(e);f.append(x);});c.append(f);});</script>
<?php include __DIR__ . '/includes/footer.php';
