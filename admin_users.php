<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit;
}

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<section class="profile-page">
    <div class="profile-card">
        <h1>Manage Users</h1>
        <p>Manage registered users.</p>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "user_updated"): ?>
            <div class="success-message">
                User updated successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["success"]) && $_GET["success"] === "user_deleted"): ?>
            <div class="success-message">
                User deleted successfully.
            </div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user["id"]; ?></td>
                        <td><?php echo htmlspecialchars($user["full_name"]); ?></td>
                        <td><?php echo htmlspecialchars($user["email"]); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($user["role"])); ?></td>
                        <td><?php echo $user["created_at"]; ?></td>

                        <td>
                            <a href="edit_user.php?id=<?php echo $user["id"]; ?>">Edit</a>

                            <?php if ($user["id"] != $_SESSION["user_id"]): ?>
                                |
                                <a
                                    href="delete_user.php?id=<?php echo $user["id"]; ?>"
                                    onclick="return confirm('Delete this user?');">
                                    Delete
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include("includes/footer.php"); ?>