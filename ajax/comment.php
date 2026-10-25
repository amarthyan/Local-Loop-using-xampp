<?php
// ajax/comment.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'Please log in to comment.']);
    exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];
$commentText = trim($_POST['comment'] ?? '');

if (!$postId || empty($commentText)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment, status) VALUES (?, ?, ?, 'active')");
    $stmt->execute([$postId, $userId, $commentText]);
    $newCommentId = $pdo->lastInsertId();

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
            'comment',
            "{$senderName} commented on: \"" . substr($post['title'], 0, 30) . "...\"",
            "post.php?id={$postId}#comment-{$newCommentId}"
        );
        award_reputation($pdo, $userId, 3, 'Community comment');
    }

    // Get total comments
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ? AND status = 'active'");
    $countStmt->execute([$postId]);
    $totalComments = (int)$countStmt->fetchColumn();

    $currentUser = current_user($pdo);

    echo json_encode([
        'success' => true,
        'total_comments' => $totalComments,
        'comment' => [
            'id' => $newCommentId,
            'user_name' => htmlspecialchars($currentUser['name']),
            'avatar' => get_avatar_url($currentUser['profile_photo']),
            'comment' => htmlspecialchars($commentText),
            'time' => 'Just now'
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
