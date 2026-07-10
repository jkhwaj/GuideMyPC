<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

$id = intval($_GET["id"] ?? 0);

$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    die("Category not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $slug = trim($_POST["slug"]);
    $description = trim($_POST["description"]);
    $icon = trim($_POST["icon"]);

    $update = $conn->prepare("
        UPDATE categories
        SET name=?, slug=?, description=?, icon=?
        WHERE id=?
    ");

    $update->bind_param(
        "ssssi",
        $name,
        $slug,
        $description,
        $icon,
        $id
    );

    $update->execute();

    header("Location: admin_categories.php");
    exit;
}
?>

<section class="auth-page">

<div class="auth-card" style="max-width:700px;">

<h1>Edit Category</h1>

<form method="POST">

<label>Name</label>

<input
type="text"
name="name"
value="<?php echo htmlspecialchars($category["name"]); ?>"
required>

<label>Slug</label>

<input
type="text"
name="slug"
value="<?php echo htmlspecialchars($category["slug"]); ?>"
required>

<label>Description</label>

<textarea
name="description"
rows="5"><?php echo htmlspecialchars($category["description"]); ?></textarea>

<label>Icon</label>

<input
type="text"
name="icon"
value="<?php echo htmlspecialchars($category["icon"]); ?>">

<button type="submit">
Save Changes
</button>

</form>

</div>

</section>

<?php include("includes/footer.php"); ?>