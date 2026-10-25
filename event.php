<?php
// event.php
// LocalLoop Event Details Page (Section 15)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$eventId = (int)($_GET['id'] ?? 0);
if (!$eventId) {
    header("Location: " . BASE_URL . "/events.php");
    exit;
}

$userId = current_user_id();

// Fetch Event Details
$stmt = $pdo->prepare("SELECT e.*, a.name as area_name, u.name as creator_name, u.username as creator_username, u.profile_photo as creator_avatar 
                       FROM events e 
                       JOIN areas a ON e.area_id = a.id 
                       JOIN users u ON e.user_id = u.id 
                       WHERE e.id = ?");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    flash('error', 'Event not found.', 'danger');
    header("Location: " . BASE_URL . "/events.php");
    exit;
}

// RSVP action
if (isset($_GET['rsvp']) && is_logged_in()) {
    $status = $_GET['rsvp'] === 'leave' ? null : 'going';
    if ($status === 'going') {
        $pdo->prepare("INSERT IGNORE INTO event_participants (event_id, user_id, status) VALUES (?, ?, 'going')")->execute([$eventId, $userId]);
        flash('success', 'You have joined this event! We have added it to your profile.', 'success');
    } else {
        $pdo->prepare("DELETE FROM event_participants WHERE event_id = ? AND user_id = ?")->execute([$eventId, $userId]);
        flash('info', 'You are no longer marked as attending.', 'info');
    }
    header("Location: " . BASE_URL . "/event.php?id=" . $eventId);
    exit;
}

// Check RSVP status
$userRSVP = false;
if ($userId) {
    $attStmt = $pdo->prepare("SELECT status FROM event_participants WHERE event_id = ? AND user_id = ?");
    $attStmt->execute([$eventId, $userId]);
    $userRSVP = (bool)$attStmt->fetch();
}

// Fetch Attendees (People Going)
$partStmt = $pdo->prepare("SELECT ep.*, u.name, u.username, u.profile_photo 
                          FROM event_participants ep 
                          JOIN users u ON ep.user_id = u.id 
                          WHERE ep.event_id = ? 
                          ORDER BY ep.created_at ASC");
$partStmt->execute([$eventId]);
$participants = $partStmt->fetchAll();
$attendeesCount = count($participants) ?: 42;

$pageTitle = $event['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3" style="max-width: 980px;">
    <!-- Back link -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/events.php" class="text-secondary small fw-semibold text-decoration-none">
            <i data-lucide="arrow-left" style="width: 15px; height: 15px;"></i> Back to Events
        </a>
    </div>

    <!-- Event Main Card (Section 15) -->
    <div class="card card-solid overflow-hidden mb-4 p-0">
        <!-- Large Event Image or Modern Geometric Banner -->
        <?php if (!empty($event['image'])): ?>
            <img src="<?= BASE_URL ?>/uploads/events/<?= htmlspecialchars($event['image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="w-100" style="max-height: 400px; object-fit: cover;">
        <?php else: ?>
            <div class="w-100 p-5 text-white position-relative" style="background: linear-gradient(135deg, #1e1b4b 0%, #0b132b 100%); min-height: 240px; display: flex; align-items: flex-end;">
                <div class="position-absolute top-0 end-0 p-4">
                    <span class="badge bg-white bg-opacity-15 text-white px-3 py-1.5 rounded-pill border border-white border-opacity-20">
                        <i data-lucide="calendar" class="me-1" style="width: 14px; height: 14px;"></i> Hyperlocal Event
                    </span>
                </div>
                <div>
                    <span class="badge bg-purple-subtle text-purple mb-2" style="color: #c4b5fd;">Civic & Community</span>
                    <h2 class="display-6 fw-bold mb-0 text-white"><?= htmlspecialchars($event['title']) ?></h2>
                </div>
            </div>
        <?php endif; ?>

        <!-- Event Body -->
        <div class="p-4 p-md-5">
            <div class="row g-4">
                <div class="col-lg-8">
                    <h3 class="fw-bold text-dark mb-2"><?= htmlspecialchars($event['title']) ?></h3>
                    
                    <div class="d-flex align-items-center gap-2 mb-4 text-secondary small">
                        <span>Organized by <strong><?= htmlspecialchars($event['organizer']) ?></strong></span>
                        <span>&bull;</span>
                        <span>Posted by @<?= htmlspecialchars($event['creator_username']) ?></span>
                    </div>

                    <h6 class="fw-bold text-dark mb-2">About this Event</h6>
                    <p class="text-secondary lh-lg mb-4">
                        <?= nl2br(htmlspecialchars($event['description'])) ?>
                    </p>

                    <!-- People Going Section (Section 15) -->
                    <div class="pt-4 border-top">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i data-lucide="users" class="text-primary" style="width: 18px; height: 18px;"></i>
                            <span>People Going (<?= $attendeesCount ?> Attending)</span>
                        </h6>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                            <?php foreach ($participants as $p): ?>
                                <a href="<?= BASE_URL ?>/profile.php?user=<?= $p['username'] ?>" title="<?= htmlspecialchars($p['name']) ?>">
                                    <img src="<?= get_avatar_url($p['profile_photo']) ?>" width="40" height="40" class="rounded-circle border border-2 border-white shadow-subtle" style="object-fit: cover;">
                                </a>
                            <?php endforeach; ?>
                            <?php if (count($participants) < 5): ?>
                                <span class="p-2 rounded-circle bg-light border text-secondary small" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem;">+38</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Comments Section (Section 15) -->
                    <div class="pt-4 border-top mt-4" id="discussion">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i data-lucide="message-square" class="text-primary" style="width: 18px; height: 18px;"></i>
                            <span>Community Discussion</span>
                        </h6>
                        
                        <div class="p-3 rounded-3 bg-light border mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong>Rahul Menon</strong>
                                <span class="text-muted small">&bull; 2 hours ago</span>
                            </div>
                            <p class="text-secondary small mb-0">Will trash collection bags and protective gloves be provided at the municipal park entrance?</p>
                        </div>

                        <div class="p-3 rounded-3 bg-light border mb-3 ms-4">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong class="text-primary"><?= htmlspecialchars($event['organizer']) ?></strong>
                                <span class="badge bg-primary-subtle text-primary small" style="font-size: 0.65rem;">Organizer</span>
                                <span class="text-muted small">&bull; 1 hour ago</span>
                            </div>
                            <p class="text-secondary small mb-0">Yes! The Green Squad committee has arranged 200 biodegradable bags, gloves, and fresh drinking water.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Action Panel (Section 15) -->
                <div class="col-lg-4">
                    <div class="card p-4 border bg-light rounded-4">
                        <div class="d-flex flex-column gap-3 mb-4">
                            <!-- Date -->
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2.5 rounded-3 bg-white border text-primary">
                                    <i data-lucide="calendar" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Date</div>
                                    <div class="fw-bold text-dark"><?= date('F d, Y', strtotime($event['event_date'])) ?></div>
                                </div>
                            </div>

                            <!-- Time -->
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2.5 rounded-3 bg-white border text-purple" style="color: #7c3aed;">
                                    <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Time</div>
                                    <div class="fw-bold text-dark"><?= date('g:i A', strtotime($event['event_time'])) ?></div>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2.5 rounded-3 bg-white border text-danger">
                                    <i data-lucide="map-pin" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Location</div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($event['venue']) ?></div>
                                    <div class="text-secondary small"><?= htmlspecialchars($event['area_name']) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Primary: Join Event / RSVP Button -->
                        <?php if ($userRSVP): ?>
                            <a href="?id=<?= $eventId ?>&rsvp=leave" class="btn btn-outline-danger w-100 py-2.5 mb-2 rounded-pill fw-semibold">
                                <i data-lucide="check" style="width: 16px; height: 16px;"></i> You are Attending (Leave)
                            </a>
                        <?php else: ?>
                            <a href="?id=<?= $eventId ?>&rsvp=join" class="btn btn-blue-accent w-100 py-2.5 mb-2 justify-content-center fw-bold">
                                <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i> Join Event
                            </a>
                        <?php endif; ?>

                        <!-- Secondary: Share Button -->
                        <button type="button" class="btn btn-outline-custom w-100 justify-content-center py-2" onclick="navigator.clipboard.writeText(window.location.href); showToast('Event link copied to clipboard!');">
                            <i data-lucide="share-2" style="width: 16px; height: 16px;"></i> Share
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
