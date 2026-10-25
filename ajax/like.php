<?php
// ajax/like.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'Please log in to like posts.']);
    exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit;
}

try {
    // Check if already liked
    $check = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $check->execute([$postId, $userId]);
    $liked = $check->fetch();

    if ($liked) {
        // Unlike
        $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?")->execute([$postId, $userId]);
        $action = 'unliked';
    } else {
        // Like
        $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)")->execute([$postId, $userId]);
        $action = 'liked';

        // Notify post owner
        $authorQuery = $pdo->prepare("SELECT user_id, title FROM posts WHERE id = ?");
        $authorQuery->execute([$postId]);
        $post = $authorQuery->fetch();
        if ($post && $post['user_id'] != $userId) {
            $senderName = $_SESSION['user_name'] ?? 'Someone';
            create_notification(
                $pdo,
                $post['user_id'],
                $userId,
                'like',
                "{$senderName} liked your post: \"" . substr($post['title'], 0, 30) . "...\"",
                "post.php?id={$postId}"
            );
            award_reputation($pdo, $post['user_id'], 2, 'Post liked');
        }
    }

    // Get updated total likes count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $countStmt->execute([$postId]);
    $totalLikes = (int)$countStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes_count' => $totalLikes
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
