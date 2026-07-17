<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/community.php';

$message = '';
$communityPolicy = new GuideMyPC\Features\Community\CommunityPolicy();

/* Add new post */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_post"])) {
    require_csrf();
    require_login();

    $title = trim((string) ($_POST["title"] ?? ''));
    $content = trim((string) ($_POST["content"] ?? ''));
    $user_id = current_user_id();

    if (!rate_limit_allows('community-post', 5, 3600)) {
        $message = 'Too many posts. Please try again later.';
    } elseif ($title !== '' && $content !== '') {
        $stmt = $conn->prepare("
            INSERT INTO community_posts (user_id, title, content)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $user_id, $title, $content);
        $stmt->execute();

        redirect('community.php');
    }
}

/* Add comment */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_comment"])) {
    require_csrf();
    require_login();

    $post_id = (int) ($_POST["post_id"] ?? 0);
    $comment = trim((string) ($_POST["comment"] ?? ''));
    $user_id = current_user_id();

    if (!rate_limit_allows('community-comment', 15, 3600)) {
        $message = 'Too many comments. Please try again later.';
    } elseif ($post_id > 0 && $comment !== '') {
        $postStatement = $conn->prepare(
            'SELECT id FROM community_posts WHERE id = ? AND ' . $communityPolicy->publicWhereClause('community_posts')
        );
        $postStatement->bind_param('i', $post_id);
        $postStatement->execute();
        $post = $postStatement->get_result()->fetch_assoc();
        $postStatement->close();

        if ($post === null) {
            $message = 'This post is no longer available for comments.';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO community_comments (post_id, user_id, comment)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iis", $post_id, $user_id, $comment);
            $stmt->execute();

            redirect('community.php');
        }
    }
}

$sql = "
SELECT community_posts.*, users.full_name
FROM community_posts
JOIN users ON community_posts.user_id = users.id
WHERE " . $communityPolicy->publicWhereClause('community_posts') . "
ORDER BY community_posts.created_at DESC
";

$result = $conn->query($sql);

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="profile-page">
    <div class="profile-card">
        <h1>Community</h1>
        <p>Ask questions and help other users solve technical problems.</p>

        <?php if ($message !== ''): ?>
            <div class="auth-message"><?php echo e($message); ?></div>
        <?php endif; ?>

        <?php if(isset($_SESSION["user_id"])): ?>
            <form method="POST" class="auth-card" style="margin-top:30px; max-width:100%;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="add_post" value="1">

                <label>Question Title</label>
                <input type="text" name="title" placeholder="Example: My Wi-Fi keeps disconnecting" required>

                <label>Description</label>
                <textarea name="content" rows="5" placeholder="Describe your issue..." required></textarea>

                <button type="submit">Post Question</button>
            </form>
        <?php else: ?>
            <p style="margin-top:25px;">Login to create a post.</p>
        <?php endif; ?>

        <br><br>

        <?php if($result && $result->num_rows > 0): ?>
            <?php while($post = $result->fetch_assoc()): ?>

                <div class="guide-content">
                    <h2><?php echo htmlspecialchars($post["title"]); ?></h2>

                    <p><?php echo nl2br(htmlspecialchars($post["content"])); ?></p>

                    <p class="meta">
                        Posted by
                        <strong><?php echo htmlspecialchars($post["full_name"]); ?></strong>
                        • <?php echo $post["created_at"]; ?>
                    </p>

                    <?php
                    $likesStmt = $conn->prepare("
                        SELECT COUNT(*) AS total
                        FROM community_likes
                        WHERE post_id = ?
                    ");
                    $likesStmt->bind_param("i", $post["id"]);
                    $likesStmt->execute();
                    $likesCount = $likesStmt->get_result()->fetch_assoc();

                    $userLiked = false;

                    if (isset($_SESSION["user_id"])) {
                        $likedStmt = $conn->prepare("
                            SELECT id
                            FROM community_likes
                            WHERE post_id = ? AND user_id = ?
                        ");
                        $likedStmt->bind_param("ii", $post["id"], $_SESSION["user_id"]);
                        $likedStmt->execute();
                        $userLiked = $likedStmt->get_result()->num_rows > 0;
                    }
                    ?>

                        <div class="like-row">
                            <?php if(isset($_SESSION["user_id"])): ?>
                                <form action="toggle_like.php" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="post_id" value="<?php echo (int) $post["id"]; ?>">
                                    <button class="like-btn" type="submit">
                                        <?php echo $userLiked ? "Liked" : "Like"; ?>
                                    </button>
                                </form>
                            <?php endif; ?>

                        <span><?php echo $likesCount["total"]; ?> likes</span>
                    </div>

                    <hr style="border:0; border-top:1px solid #1e293b; margin:24px 0;">

                    <h3>Comments</h3>

                    <?php
                    $commentsStmt = $conn->prepare("
                        SELECT community_comments.*, users.full_name
                        FROM community_comments
                        JOIN users ON community_comments.user_id = users.id
                        WHERE community_comments.post_id = ?
                        ORDER BY community_comments.created_at ASC
                    ");
                    $commentsStmt->bind_param("i", $post["id"]);
                    $commentsStmt->execute();
                    $comments = $commentsStmt->get_result();
                    ?>

                    <?php if($comments && $comments->num_rows > 0): ?>
                        <?php while($comment = $comments->fetch_assoc()): ?>
                            <div class="comment-box">
                                <p><?php echo nl2br(htmlspecialchars($comment["comment"])); ?></p>
                                <small>
                                    By <?php echo htmlspecialchars($comment["full_name"]); ?>
                                    • <?php echo $comment["created_at"]; ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="meta">No comments yet.</p>
                    <?php endif; ?>

                    <?php if(isset($_SESSION["user_id"])): ?>
                        <form method="POST" class="comment-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="add_comment" value="1">
                            <input type="hidden" name="post_id" value="<?php echo $post["id"]; ?>">

                            <textarea name="comment" rows="3" placeholder="Write a comment..." required></textarea>

                            <button type="submit">Add Comment</button>
                        </form>
                    <?php endif; ?>
                </div>

                <br>

            <?php endwhile; ?>
        <?php else: ?>
            <p>No questions have been posted yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php include("includes/footer.php"); ?>
