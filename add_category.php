<?php
require_once __DIR__ . '/config.php';
require_admin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_csrf();
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

        redirect('admin_categories.php');
    }
}

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="auth-page">
    <div class="auth-card" style="max-width:700px;">
        <h1>Add Category</h1>
        <p>Create a new troubleshooting category.</p>

        <form method="POST">
            <?php echo csrf_field(); ?>
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
