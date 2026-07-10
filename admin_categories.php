<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<section class="profile-page">
    <div class="profile-card">
        <h1>Manage Categories</h1>
        <p>Add, edit or delete guide categories.</p>

        <br>

        <a class="primary-btn" href="add_category.php">
            + Add New Category
        </a>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Icon</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while($category = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $category["id"]; ?></td>
                        <td><?php echo htmlspecialchars($category["icon"]); ?></td>
                        <td><?php echo htmlspecialchars($category["name"]); ?></td>
                        <td><?php echo htmlspecialchars($category["slug"]); ?></td>
                        <td><?php echo htmlspecialchars($category["description"]); ?></td>

                        <td>
                            <a href="edit_category.php?id=<?php echo $category["id"]; ?>">Edit</a>
                            |
                            <a
                                href="delete_category.php?id=<?php echo $category["id"]; ?>"
                                onclick="return confirm('Delete this category?');">
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