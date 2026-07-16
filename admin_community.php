<?php
require_once __DIR__ . '/config.php';
require_admin();
include("includes/header.php");
include("includes/navbar.php");

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

                        <form class="inline-action" action="delete_post.php" method="POST" onsubmit="return confirm('Delete this post?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) $post["id"]; ?>">
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
