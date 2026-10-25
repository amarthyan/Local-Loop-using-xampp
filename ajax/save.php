<?php
// ajax/save.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'Please log in to save posts.']);
    exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit;
}

try {
    // Check if already saved
    $check = $pdo->prepare("SELECT id FROM saved_posts WHERE post_id = ? AND user_id = ?");
    $check->execute([$postId, $userId]);
    $saved = $check->fetch();

    if ($saved) {
        // Unsave
        $pdo->prepare("DELETE FROM saved_posts WHERE post_id = ? AND user_id = ?")->execute([$postId, $userId]);
        $action = 'unsaved';
    } else {
        // Save
        $pdo->prepare("INSERT INTO saved_posts (post_id, user_id) VALUES (?, ?)")->execute([$postId, $userId]);
        $action = 'saved';
    }

    echo json_encode([
        'success' => true,
        'action' => $action
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
