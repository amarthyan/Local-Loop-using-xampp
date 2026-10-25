<?php
// ajax/report.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'Please log in to submit a report.']);
    exit;
}

$reporterId = (int)$_SESSION['user_id'];
$postId = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : null;
$commentId = !empty($_POST['comment_id']) ? (int)$_POST['comment_id'] : null;
$reason = trim($_POST['reason'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Please choose a report reason.']);
    exit;
}

if (!$postId && !$commentId) {
    echo json_encode(['success' => false, 'message' => 'Target content not specified.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO reports (reporter_id, post_id, comment_id, reason, description, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$reporterId, $postId, $commentId, $reason, $description]);

    echo json_encode(['success' => true, 'message' => 'Thank you. The report has been flagged for admin review.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
