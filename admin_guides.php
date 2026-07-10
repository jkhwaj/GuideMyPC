<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit;
}

$sql = "SELECT guides.*, categories.name AS category
        FROM guides
        JOIN categories ON guides.category_id = categories.id
        ORDER BY guides.id DESC";

$result = $conn->query($sql);
?>

<section class="profile-page">
    <div class="profile-card">
        <h1>Manage Guides</h1>
        <p>Add, edit or delete troubleshooting guides.</p>

        <br>

        <a class="primary-btn" href="add_guide.php">
            + Add New Guide
        </a>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "guide_deleted"): ?>
            <div class="success-message">
                Guide deleted successfully.
            </div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Difficulty</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while($guide = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $guide["id"]; ?></td>

                        <td><?php echo htmlspecialchars($guide["title"]); ?></td>

                        <td><?php echo htmlspecialchars($guide["category"]); ?></td>

                        <td><?php echo htmlspecialchars($guide["difficulty"]); ?></td>

                        <td>
                            <a href="edit_guide.php?id=<?php echo $guide["id"]; ?>">
                                Edit
                            </a>
                            |
                            <a
                                href="delete_guide.php?id=<?php echo $guide["id"]; ?>"
                                onclick="return confirm('Delete this guide?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include("includes/footer.php"); ?>