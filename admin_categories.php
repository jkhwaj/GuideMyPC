<?php
require_once __DIR__ . '/config.php';
require_admin();
include("includes/header.php");
include("includes/navbar.php");

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
                            <form class="inline-action" action="delete_category.php" method="POST" onsubmit="return confirm('Delete this category?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int) $category["id"]; ?>">
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
