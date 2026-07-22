<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin.php';

use GuideMyPC\Features\Community\CommunityAdminService;

function community_admin_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$test = test_database_or_fail();
$token = bin2hex(random_bytes(5));
$userId = 0;
$postId = 0;
$commentPostId = 0;
$commentId = 0;

try {
    $email = 'community-admin-' . $token . '@example.test';
    $name = 'Community Admin Test';
    $password = password_hash('CommunityAdmin1!', PASSWORD_DEFAULT);
    $user = $test->prepare('INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)');
    $user->bind_param('sss', $name, $email, $password);
    $user->execute();
    $userId = $user->insert_id;
    $user->close();
    $_SESSION = ['user_id' => $userId];

    $post = $test->prepare('INSERT INTO community_posts (user_id, title, content) VALUES (?, ?, ?)');
    $title = 'Community post ' . $token;
    $content = 'Community post deletion fixture.';
    $post->bind_param('iss', $userId, $title, $content);
    $post->execute();
    $postId = $post->insert_id;
    $post->close();

    $comment = $test->prepare('INSERT INTO community_comments (post_id, user_id, comment) VALUES (?, ?, ?)');
    $commentText = 'Post deletion comment.';
    $comment->bind_param('iis', $postId, $userId, $commentText);
    $comment->execute();
    $comment->close();
    $like = $test->prepare('INSERT INTO community_likes (post_id, user_id) VALUES (?, ?)');
    $like->bind_param('ii', $postId, $userId);
    $like->execute();
    $like->close();

    $service = new CommunityAdminService($test);
    community_admin_assert($service->deletePost($postId), 'Existing community post deletion succeeds.');
    community_admin_assert((int) $test->query('SELECT COUNT(*) AS total FROM community_posts WHERE id = ' . $postId)->fetch_assoc()['total'] === 0, 'Post deletion removes the post.');
    community_admin_assert((int) $test->query('SELECT COUNT(*) AS total FROM community_comments WHERE post_id = ' . $postId)->fetch_assoc()['total'] === 0, 'Post deletion removes related comments.');
    community_admin_assert((int) $test->query('SELECT COUNT(*) AS total FROM community_likes WHERE post_id = ' . $postId)->fetch_assoc()['total'] === 0, 'Post deletion removes related likes.');
    $postAudit = $test->query("SELECT metadata_json FROM admin_audit_events WHERE action = 'community.post.deleted' AND target_type = 'community_post' AND target_id = '" . $postId . "'")->fetch_assoc();
    community_admin_assert($postAudit !== null && $postAudit['metadata_json'] === '{"likes_deleted":1,"comments_deleted":1}', 'Post deletion records one audit event with related deletion counts.');
    community_admin_assert(!$service->deletePost($postId), 'Missing community post deletion is a no-op.');
    community_admin_assert((int) $test->query("SELECT COUNT(*) AS total FROM admin_audit_events WHERE action = 'community.post.deleted' AND target_type = 'community_post' AND target_id = '" . $postId . "'")->fetch_assoc()['total'] === 1, 'Missing post deletion does not add an audit event.');

    $commentPost = $test->prepare('INSERT INTO community_posts (user_id, title, content) VALUES (?, ?, ?)');
    $commentPostTitle = 'Community comment post ' . $token;
    $commentPostContent = 'Community comment deletion fixture.';
    $commentPost->bind_param('iss', $userId, $commentPostTitle, $commentPostContent);
    $commentPost->execute();
    $commentPostId = $commentPost->insert_id;
    $commentPost->close();
    $comment = $test->prepare('INSERT INTO community_comments (post_id, user_id, comment) VALUES (?, ?, ?)');
    $commentText = 'Direct deletion comment.';
    $comment->bind_param('iis', $commentPostId, $userId, $commentText);
    $comment->execute();
    $commentId = $comment->insert_id;
    $comment->close();

    community_admin_assert($service->deleteComment($commentId), 'Existing community comment deletion succeeds.');
    community_admin_assert((int) $test->query('SELECT COUNT(*) AS total FROM community_comments WHERE id = ' . $commentId)->fetch_assoc()['total'] === 0, 'Comment deletion removes only the target comment.');
    community_admin_assert((int) $test->query("SELECT COUNT(*) AS total FROM admin_audit_events WHERE action = 'community.comment.deleted' AND target_type = 'community_comment' AND target_id = '" . $commentId . "'")->fetch_assoc()['total'] === 1, 'Comment deletion records one audit event.');
    community_admin_assert(!$service->deleteComment($commentId), 'Missing community comment deletion is a no-op.');
    community_admin_assert((int) $test->query("SELECT COUNT(*) AS total FROM admin_audit_events WHERE action = 'community.comment.deleted' AND target_type = 'community_comment' AND target_id = '" . $commentId . "'")->fetch_assoc()['total'] === 1, 'Missing comment deletion does not add an audit event.');

    fwrite(STDOUT, "PASS: community administrator deletions preserve data cleanup and audit events.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
} finally {
    foreach ([$postId, $commentPostId] as $id) {
        if ($id > 0) {
            $test->query('DELETE FROM admin_audit_events WHERE target_type = \'community_post\' AND target_id = \'" . $id . "\'');
            $test->query('DELETE FROM community_posts WHERE id = ' . $id);
        }
    }

    if ($commentId > 0) {
        $test->query('DELETE FROM admin_audit_events WHERE target_type = \'community_comment\' AND target_id = \'" . $commentId . "\'');
    }

    if ($userId > 0) {
        $test->query('DELETE FROM users WHERE id = ' . $userId);
    }

    $test->close();
    $_SESSION = [];
}

if (isset($exitCode)) {
    exit($exitCode);
}
