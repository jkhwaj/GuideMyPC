<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $slug = trim($_POST["slug"]);
    $description = trim($_POST["description"]);
    $icon = trim($_POST["icon"]);

    if ($name !== "" && $slug !== "") {
        $stmt = $conn->prepare("
            INSERT INTO categories (name, slug, description, icon)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $name, $slug, $description, $icon);
        $stmt->execute();

        header("Location: admin_categories.php");
        exit;
    }
}
?>

<section class="auth-page">
    <div class="auth-card" style="max-width:700px;">
        <h1>Add Category</h1>
        <p>Create a new troubleshooting category.</p>

        <form method="POST">
            <label>Name</label>
            <input type="text" name="name" placeholder="Example: Printers" required>

            <label>Slug</label>
            <input type="text" name="slug" placeholder="example: printers" required>

            <label>Description</label>
            <textarea name="description" placeholder="Short category description"></textarea>

            <label>Icon</label>
            <input type="text" name="icon" placeholder="Example: 🖨️">

            <button type="submit">Save Category</button>
        </form>
    </div>
</section>

<?php include("includes/footer.php"); ?>