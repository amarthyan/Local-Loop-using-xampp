<?php
// ajax/help_offer.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'Please log in to offer help.']);
    exit;
}

$requestId = (int)($_POST['request_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];
$message = trim($_POST['message'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (!$requestId || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a message explaining how you can help.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO help_offers (request_id, user_id, message, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$requestId, $userId, $message, $phone]);

    // Notify requester
    $reqStmt = $pdo->prepare("SELECT user_id, title FROM help_requests WHERE id = ?");
    $reqStmt->execute([$requestId]);
    $req = $reqStmt->fetch();
    if ($req && $req['user_id'] != $userId) {
        $senderName = $_SESSION['user_name'] ?? 'Someone';
        create_notification(
            $pdo,
            $req['user_id'],
            $userId,
            'help_offer',
            "{$senderName} offered to assist on your help request: \"{$req['title']}\"",
            "help-request.php?id={$requestId}"
        );
        award_reputation($pdo, $userId, 5, 'Community help offer');
    }

    echo json_encode(['success' => true, 'message' => 'Your offer to help has been sent to the requester!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
