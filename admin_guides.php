<?php
require_once __DIR__ . '/config.php';
require_admin();
include("includes/header.php");
include("includes/navbar.php");

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
                            <form class="inline-action" action="delete_guide.php" method="POST" onsubmit="return confirm('Delete this guide?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int) $guide["id"]; ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include("includes/footer.php"); ?>
