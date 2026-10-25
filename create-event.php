<?php
// create-event.php
// LocalLoop Host / Organize Community Event

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();
$currentUser = current_user($pdo);

$errors = [];
$title = '';
$description = '';
$areaId = $currentUser['area_id'] ?? '';
$eventDate = date('Y-m-d', strtotime('+3 days'));
$eventTime = '17:00';
$venue = '';
$organizer = $currentUser['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security token mismatch.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $areaId = (int)($_POST['area_id'] ?? 0);
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $organizer = trim($_POST['organizer'] ?? '');

        if (empty($title)) $errors[] = "Event title is required.";
        if (empty($eventDate)) $errors[] = "Date is required.";
        if (empty($venue)) $errors[] = "Venue location is required.";

        if (empty($errors)) {
            $imageName = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadRes = upload_image_file($_FILES['image'], 'events');
                if ($uploadRes['success']) {
                    $imageName = $uploadRes['fileName'];
                }
            }

            $stmt = $pdo->prepare("INSERT INTO events (user_id, area_id, title, description, event_date, event_time, venue, organizer, image, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'upcoming')");
            $stmt->execute([$currentUser['id'], $areaId, $title, $description, $eventDate, $eventTime, $venue, $organizer, $imageName]);
            $eventId = $pdo->lastInsertId();

            // Auto-join organizer as going
            $pdo->prepare("INSERT INTO event_participants (event_id, user_id, status) VALUES (?, ?, 'going')")->execute([$eventId, $currentUser['id']]);

            award_reputation($pdo, $currentUser['id'], 10, 'Hosted community event');

            $_SESSION['flash_success'] = "Community event scheduled and published!";
            header("Location: " . BASE_URL . "/event.php?id=" . $eventId);
            exit;
        }
    }
}

$pageTitle = "Organize Community Event";
require_once __DIR__ . '/includes/header.php';
$allAreas = get_active_areas($pdo);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="brand-icon" style="width: 44px; height: 44px; background: linear-gradient(135deg, #8b5cf6, #c084fc);">
                        <i class="bi bi-calendar-plus fs-4 text-white"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">Organize a Neighborhood Event</h3>
                        <p class="text-muted small mb-0">Invite locals to a sports tournament, festival, cleanup drive, or workshop.</p>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/create-event.php" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Event Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Kodungallur Maidan Monsoon Football Cup" value="<?= htmlspecialchars($title) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Locality / Town</label>
                            <select name="area_id" class="form-select" required>
                                <?php foreach ($allAreas as $area): ?>
                                    <option value="<?= $area['id'] ?>" <?= $areaId == $area['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($area['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Organizer / Club Name</label>
                            <input type="text" name="organizer" class="form-control" required value="<?= htmlspecialchars($organizer) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Event Date</label>
                            <input type="date" name="event_date" class="form-control" required value="<?= htmlspecialchars($eventDate) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Start Time</label>
                            <input type="time" name="event_time" class="form-control" required value="<?= htmlspecialchars($eventTime) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Venue / Precise Location</label>
                        <input type="text" name="venue" class="form-control" required placeholder="e.g. Municipal Stadium, North Gate" value="<?= htmlspecialchars($venue) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Event Description</label>
                        <textarea name="description" class="form-control" rows="5" required placeholder="Provide schedule, eligibility, prizes, instructions, contact details..."><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Banner Poster Image (Optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/events.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Publish Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
