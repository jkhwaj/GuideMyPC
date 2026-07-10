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
    header("Location: admin_downloads.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $official_url = trim($_POST["official_url"]);
    $category = trim($_POST["category"]);

    $stmt = $conn->prepare("
        UPDATE downloads
        SET name = ?, description = ?, official_url = ?, category = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ssssi", $name, $description, $official_url, $category, $id);
    $stmt->execute();

    header("Location: admin_downloads.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM downloads WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$download = $stmt->get_result()->fetch_assoc();

if (!$download) {
    header("Location: admin_downloads.php");
    exit;
}
?>

<section class="auth-page">
    <div class="auth-card" style="max-width:700px;">
        <h1>Edit Download</h1>
        <p>Update trusted download information.</p>

        <form method="POST">
            <label>Software Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($download["name"]); ?>" required>

            <label>Description</label>
            <textarea name="description"><?php echo htmlspecialchars($download["description"]); ?></textarea>

            <label>Official URL</label>
            <input type="url" name="official_url" value="<?php echo htmlspecialchars($download["official_url"]); ?>" required>

            <label>Category</label>
            <input type="text" name="category" value="<?php echo htmlspecialchars($download["category"]); ?>">

            <button type="submit">Save Changes</button>
        </form>
    </div>
</section>

<?php include("includes/footer.php"); ?>