<?php
require_once __DIR__ . '/config.php';
require_admin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_csrf();

    $category = filter_var($_POST['category'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $title = required_string($_POST['title'] ?? null, 150);
    $slug = required_string($_POST['slug'] ?? null, 150);

    if ($category === false || $title === null || $slug === null) {
        remember_old_input($_POST, ['title', 'slug', 'description', 'difficulty', 'estimated_time', 'risk_level']);
        flash('error', 'Choose a category and provide a title and slug of up to 150 characters.');
        redirect('add_guide.php');
    }

    $description = trim((string) ($_POST['description'] ?? ''));
    $difficulty = trim((string) ($_POST['difficulty'] ?? ''));
    $time = trim((string) ($_POST['estimated_time'] ?? ''));
    $risk = trim((string) ($_POST['risk_level'] ?? ''));
    $steps = is_array($_POST['steps'] ?? null) ? $_POST['steps'] : [];

    in_transaction($conn, static function () use ($conn, $category, $title, $slug, $description, $difficulty, $time, $risk, $steps): void {
        $statement = $conn->prepare(
            'INSERT INTO guides (category_id, title, slug, description, difficulty, estimated_time, risk_level, content) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $content = '';
        $statement->bind_param('isssssss', $category, $title, $slug, $description, $difficulty, $time, $risk, $content);
        $statement->execute();
        $guideId = $statement->insert_id;
        $statement->close();

        $stepNumber = 1;

        foreach ($steps as $step) {
            $stepText = required_string($step, 10000);

            if ($stepText === null) {
                continue;
            }

            $insertStep = $conn->prepare('INSERT INTO guide_steps (guide_id, step_number, step_text) VALUES (?, ?, ?)');
            $insertStep->bind_param('iis', $guideId, $stepNumber, $stepText);
            $insertStep->execute();
            $insertStep->close();
            $stepNumber++;
        }
    });

    flash('success', 'Guide created.');
    redirect('admin_guides.php');
}

$categories = $conn->query("SELECT * FROM categories");

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="auth-page">

<div class="auth-card" style="max-width:800px;">

<h1>Add Guide</h1>

<form method="POST">

<?php echo csrf_field(); ?>

<label>Category</label>

<select name="category">

<?php while($cat=$categories->fetch_assoc()): ?>

<option value="<?php echo (int) $cat["id"]; ?>">
<?php echo e($cat["name"]); ?>
</option>

<?php endwhile; ?>

</select>

<label>Title</label>
<input type="text" name="title" value="<?php echo e(old_input('title')); ?>" required>

<label>Slug</label>
<input type="text" name="slug" value="<?php echo e(old_input('slug')); ?>" required>

<label>Description</label>
<textarea name="description"><?php echo e(old_input('description')); ?></textarea>

<label>Difficulty</label>
<input type="text" name="difficulty" value="<?php echo e(old_input('difficulty')); ?>">

<label>Estimated Time</label>
<input type="text" name="estimated_time" value="<?php echo e(old_input('estimated_time')); ?>">

<label>Risk Level</label>
<input type="text" name="risk_level" value="<?php echo e(old_input('risk_level')); ?>">

<hr>

<h3>Guide Steps</h3>

<div id="stepsContainer">

<textarea name="steps[]" placeholder="Step 1"></textarea>

</div>

<br>

<button
type="button"
onclick="addStep()">
+ Add Step
</button>

<br><br>

<button type="submit">
Save Guide
</button>

</form>

</div>

</section>

<script>

function addStep(){

const container=document.getElementById("stepsContainer");

const textarea=document.createElement("textarea");

textarea.name="steps[]";

textarea.placeholder="Next Step";

container.appendChild(textarea);

}

</script>

<?php include("includes/footer.php"); ?>
