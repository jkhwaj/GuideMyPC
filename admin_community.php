<?php
session_start();

include("config.php");
include("includes/header.php");
include("includes/navbar.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: index.php");
    exit;
}

$sql = "
SELECT community_posts.*,
       users.full_name
FROM community_posts
JOIN users ON community_posts.user_id = users.id
ORDER BY community_posts.created_at DESC
";

$result = $conn->query($sql);
?>

<section class="profile-page">
    <div class="profile-card">

        <h1>Manage Community</h1>
        <p>Delete inappropriate or unwanted posts.</p>

        <table class="admin-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Author</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

            <?php while($post = $result->fetch_assoc()): ?>

                <tr>

                    <td><?php echo $post["id"]; ?></td>

                    <td>
                        <?php echo htmlspecialchars($post["full_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($post["title"]); ?>
                    </td>

                    <td>
                        <?php echo $post["created_at"]; ?>
                    </td>

                    <td>

                        <a
                            href="delete_post.php?id=<?php echo $post["id"]; ?>"
                            onclick="return confirm('Delete this post?')">
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