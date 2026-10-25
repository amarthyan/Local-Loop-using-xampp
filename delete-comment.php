<?php
// delete-comment.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$currentUser = current_user($pdo);

$commentId = (int)($_GET['id'] ?? 0);
$postId = (int)($_GET['post_id'] ?? 0);

if ($commentId) {
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $cmt = $stmt->fetch();

    if ($cmt && ($cmt['user_id'] == $currentUser['id'] || $currentUser['role'] === 'admin')) {
        $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$commentId]);
        $_SESSION['flash_info'] = "Comment deleted.";
    } else {
        $_SESSION['flash_error'] = "Unauthorized to delete this comment.";
    }
}

header("Location: " . BASE_URL . "/post.php?id=" . $postId . "#comments");
exit;
