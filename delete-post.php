<?php
// delete-post.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$currentUser = current_user($pdo);

$postId = (int)($_GET['id'] ?? 0);
if (!$postId) {
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['flash_error'] = "Post not found.";
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

// Check authorization
if ($post['user_id'] != $currentUser['id'] && $currentUser['role'] !== 'admin') {
    $_SESSION['flash_error'] = "You cannot delete another member's post.";
    header("Location: " . BASE_URL . "/post.php?id=" . $postId);
    exit;
}

// Delete post (cascades to post_images, comments, likes, saved_posts)
$pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$postId]);

if ($currentUser['role'] === 'admin' && $post['user_id'] != $currentUser['id']) {
    log_admin_action($pdo, $currentUser['id'], 'delete_post', 'posts', $postId, "Admin deleted post: {$post['title']}");
}

$_SESSION['flash_success'] = "Post deleted successfully.";
header("Location: " . BASE_URL . "/feed.php");
exit;
