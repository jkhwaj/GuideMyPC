<?php
require_once __DIR__ . '/config.php';
require_admin();

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: admin_guides.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_csrf();
    $category = $_POST["category"];
    $title = $_POST["title"];
    $slug = $_POST["slug"];
    $description = $_POST["description"];
    $difficulty = $_POST["difficulty"];
    $time = $_POST["estimated_time"];
    $risk = $_POST["risk_level"];

    $stmt = $conn->prepare("
        UPDATE guides
        SET category_id = ?, title = ?, slug = ?, description = ?, difficulty = ?, estimated_time = ?, risk_level = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "issssssi",
        $category,
        $title,
        $slug,
        $description,
        $difficulty,
        $time,
        $risk,
        $id
    );

    $stmt->execute();

    $deleteSteps = $conn->prepare('DELETE FROM guide_steps WHERE guide_id = ?');
    $deleteSteps->bind_param('i', $id);
    $deleteSteps->execute();

    if (!empty($_POST["steps"])) {
        $stepNumber = 1;

        foreach ($_POST["steps"] as $step) {
            if (trim($step) == "") continue;

            $insertStep = $conn->prepare("
                INSERT INTO guide_steps (guide_id, step_number, step_text)
                VALUES (?, ?, ?)
            ");

            $insertStep->bind_param("iis", $id, $stepNumber, $step);
            $insertStep->execute();

            $stepNumber++;
        }
    }

    redirect('admin_guides.php');
}

$guideStmt = $conn->prepare("SELECT * FROM guides WHERE id = ?");
$guideStmt->bind_param("i", $id);
$guideStmt->execute();
$guide = $guideStmt->get_result()->fetch_assoc();

if (!$guide) {
    redirect('admin_guides.php');
}

$categories = $conn->query("SELECT * FROM categories");

$stepsStmt = $conn->prepare('SELECT * FROM guide_steps WHERE guide_id = ? ORDER BY step_number ASC');
$stepsStmt->bind_param('i', $id);
$stepsStmt->execute();
$steps = $stepsStmt->get_result();

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="auth-page">
    <div class="auth-card" style="max-width:800px;">
        <h1>Edit Guide</h1>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <label>Category</label>
            <select name="category">
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo (int) $cat["id"]; ?>" <?php echo $cat["id"] == $guide["category_id"] ? "selected" : ""; ?>>
                        <?php echo e($cat["name"]); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($guide["title"]); ?>" required>

            <label>Slug</label>
            <input type="text" name="slug" value="<?php echo htmlspecialchars($guide["slug"]); ?>" required>

            <label>Description</label>
            <textarea name="description"><?php echo htmlspecialchars($guide["description"]); ?></textarea>

            <label>Difficulty</label>
            <input type="text" name="difficulty" value="<?php echo htmlspecialchars($guide["difficulty"]); ?>">

            <label>Estimated Time</label>
            <input type="text" name="estimated_time" value="<?php echo htmlspecialchars($guide["estimated_time"]); ?>">

            <label>Risk Level</label>
            <input type="text" name="risk_level" value="<?php echo htmlspecialchars($guide["risk_level"]); ?>">

            <hr>

            <h3>Guide Steps</h3>

            <div id="stepsContainer">
                <?php while($step = $steps->fetch_assoc()): ?>
                    <textarea name="steps[]"><?php echo htmlspecialchars($step["step_text"]); ?></textarea>
                <?php endwhile; ?>
            </div>

            <br>

            <button type="button" onclick="addStep()">+ Add Step</button>

            <br><br>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</section>

<script>
function addStep() {
    const container = document.getElementById("stepsContainer");
    const textarea = document.createElement("textarea");

    textarea.name = "steps[]";
    textarea.placeholder = "Next Step";

    container.appendChild(textarea);
}
</script>

<?php include("includes/footer.php"); ?>
