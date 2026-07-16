<?php
require_once __DIR__ . '/config.php';
require_admin();
include("includes/header.php");
include("includes/navbar.php");

$sql = "
SELECT community_comments.*,
       users.full_name,
       community_posts.title
FROM community_comments
JOIN users ON community_comments.user_id = users.id
JOIN community_posts ON community_comments.post_id = community_posts.id
ORDER BY community_comments.created_at DESC
";

$result = $conn->query($sql);
?>

<section class="profile-page">
    <div class="profile-card">

        <h1>Manage Comments</h1>
        <p>Delete inappropriate comments.</p>

        <table class="admin-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Author</th>
                    <th>Post</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

            <?php while($comment = $result->fetch_assoc()): ?>

                <tr>

                    <td><?php echo $comment["id"]; ?></td>

                    <td><?php echo htmlspecialchars($comment["full_name"]); ?></td>

                    <td><?php echo htmlspecialchars($comment["title"]); ?></td>

                    <td><?php echo htmlspecialchars($comment["comment"]); ?></td>

                    <td><?php echo $comment["created_at"]; ?></td>

                    <td>
                        <form class="inline-action" action="delete_comment.php" method="POST" onsubmit="return confirm('Delete this comment?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) $comment["id"]; ?>">
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
