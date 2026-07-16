<?php
require_once __DIR__ . '/config.php';
require_admin();
include("includes/header.php");
include("includes/navbar.php");

$result = $conn->query("SELECT * FROM downloads ORDER BY id DESC");
?>

<section class="profile-page">
    <div class="profile-card">
        <h1>Manage Downloads</h1>
        <p>Add, edit or delete trusted software download links.</p>

        <br>

        <a class="primary-btn" href="add_download.php">
            + Add New Download
        </a>
        <?php if (isset($_GET["success"]) && $_GET["success"] === "download_deleted"): ?>
             <div class="success-message">
                Download deleted successfully.
            </div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Official URL</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while($download = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $download["id"]; ?></td>

                        <td><?php echo htmlspecialchars($download["name"]); ?></td>

                        <td><?php echo htmlspecialchars($download["category"]); ?></td>

                        <td>
                            <a href="<?php echo htmlspecialchars($download["official_url"]); ?>" target="_blank">
                                Open Link
                            </a>
                        </td>

                        <td>
                            <a href="edit_download.php?id=<?php echo $download["id"]; ?>">Edit</a>
                            |
                            <form class="inline-action" action="delete_download.php" method="POST" onsubmit="return confirm('Delete this download?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int) $download["id"]; ?>">
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
