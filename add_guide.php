<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
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

    $sql = "INSERT INTO guides
    (category_id,title,slug,description,difficulty,estimated_time,risk_level,content)
    VALUES (?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);

    $emptyContent = "";

    $stmt->bind_param(
        "isssssss",
        $category,
        $title,
        $slug,
        $description,
        $difficulty,
        $time,
        $risk,
        $emptyContent
    );

    $stmt->execute();

    $guideId = $stmt->insert_id;

    if (!empty($_POST["steps"])) {

        $stepNumber = 1;

        foreach ($_POST["steps"] as $step) {

            if (trim($step) == "") continue;

            $insertStep = $conn->prepare(
                "INSERT INTO guide_steps (guide_id,step_number,step_text)
                 VALUES (?,?,?)"
            );

            $insertStep->bind_param(
                "iis",
                $guideId,
                $stepNumber,
                $step
            );

            $insertStep->execute();

            $stepNumber++;
        }
    }

    header("Location: admin_guides.php");
    exit;
}

$categories = $conn->query("SELECT * FROM categories");
?>

<section class="auth-page">

<div class="auth-card" style="max-width:800px;">

<h1>Add Guide</h1>

<form method="POST">

<label>Category</label>

<select name="category">

<?php while($cat=$categories->fetch_assoc()): ?>

<option value="<?php echo $cat["id"]; ?>">
<?php echo $cat["name"]; ?>
</option>

<?php endwhile; ?>

</select>

<label>Title</label>
<input type="text" name="title" required>

<label>Slug</label>
<input type="text" name="slug" required>

<label>Description</label>
<textarea name="description"></textarea>

<label>Difficulty</label>
<input type="text" name="difficulty">

<label>Estimated Time</label>
<input type="text" name="estimated_time">

<label>Risk Level</label>
<input type="text" name="risk_level">

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