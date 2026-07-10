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
                        <a href="delete_comment.php?id=<?php echo $comment["id"]; ?>"
                           onclick="return confirm('Delete this comment?')">
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