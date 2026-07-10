<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit;
}

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: admin_guides.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    $conn->query("DELETE FROM guide_steps WHERE guide_id = $id");

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

    header("Location: admin_guides.php");
    exit;
}

$guideStmt = $conn->prepare("SELECT * FROM guides WHERE id = ?");
$guideStmt->bind_param("i", $id);
$guideStmt->execute();
$guide = $guideStmt->get_result()->fetch_assoc();

$categories = $conn->query("SELECT * FROM categories");

$steps = $conn->query("SELECT * FROM guide_steps WHERE guide_id = $id ORDER BY step_number ASC");
?>

<section class="auth-page">
    <div class="auth-card" style="max-width:800px;">
        <h1>Edit Guide</h1>

        <form method="POST">
            <label>Category</label>
            <select name="category">
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat["id"]; ?>" <?php echo $cat["id"] == $guide["category_id"] ? "selected" : ""; ?>>
                        <?php echo $cat["name"]; ?>
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