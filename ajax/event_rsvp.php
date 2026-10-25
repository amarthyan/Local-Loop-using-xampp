<?php
// ajax/event_rsvp.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'Please log in to RSVP for events.']);
    exit;
}

$eventId = (int)($_POST['event_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];
$status = ($_POST['status'] ?? 'going') === 'going' ? 'going' : 'interested';

if (!$eventId) {
    echo json_encode(['success' => false, 'message' => 'Invalid event ID.']);
    exit;
}

try {
    $check = $pdo->prepare("SELECT id FROM event_participants WHERE event_id = ? AND user_id = ?");
    $check->execute([$eventId, $userId]);
    $existing = $check->fetch();

    if ($existing) {
        // Cancel RSVP
        $pdo->prepare("DELETE FROM event_participants WHERE event_id = ? AND user_id = ?")->execute([$eventId, $userId]);
        $action = 'cancelled';
        $msg = 'RSVP removed.';
    } else {
        // Join RSVP
        $pdo->prepare("INSERT INTO event_participants (event_id, user_id, status) VALUES (?, ?, ?)")->execute([$eventId, $userId, $status]);
        $action = 'registered';
        $msg = 'You are registered for this event!';

        // Notify event organizer
        $eventOwnerStmt = $pdo->prepare("SELECT user_id, title FROM events WHERE id = ?");
        $eventOwnerStmt->execute([$eventId]);
        $event = $eventOwnerStmt->fetch();
        if ($event && $event['user_id'] != $userId) {
            $senderName = $_SESSION['user_name'] ?? 'Someone';
            create_notification(
                $pdo,
                $event['user_id'],
                $userId,
                'event_rsvp',
                "{$senderName} is attending your event: \"{$event['title']}\"",
                "event.php?id={$eventId}"
            );
        }
    }

    $count = $pdo->prepare("SELECT COUNT(*) FROM event_participants WHERE event_id = ?");
    $count->execute([$eventId]);
    $total = (int)$count->fetchColumn();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'message' => $msg,
        'total_participants' => $total
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
