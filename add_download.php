<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $official_url = trim($_POST["official_url"]);
    $category = trim($_POST["category"]);

    if ($name == "" || $official_url == "") {
        $message = "Name and official URL are required.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO downloads (name, description, official_url, category)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param("ssss", $name, $description, $official_url, $category);
        $stmt->execute();

        header("Location: admin_downloads.php");
        exit;
    }
}
?>

<section class="auth-page">
    <div class="auth-card" style="max-width:700px;">
        <h1>Add Download</h1>
        <p>Add trusted official software download links.</p>

        <?php if ($message != ""): ?>
            <div class="auth-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Software Name</label>
            <input type="text" name="name" placeholder="Example: Malwarebytes" required>

            <label>Description</label>
            <textarea name="description" placeholder="Short description of the tool"></textarea>

            <label>Official URL</label>
            <input type="url" name="official_url" placeholder="https://example.com" required>

            <label>Category</label>
            <input type="text" name="category" placeholder="Example: Security, Browser, Utility">

            <button type="submit">Save Download</button>
        </form>
    </div>
</section>

<?php include("includes/footer.php"); ?>