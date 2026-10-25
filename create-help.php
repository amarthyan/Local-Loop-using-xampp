<?php
// create-help.php
// LocalLoop Post Community Help Request

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();
$currentUser = current_user($pdo);

$errors = [];
$title = '';
$urgency = 'Medium';
$location = '';
$areaId = $currentUser['area_id'] ?? '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security token mismatch.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $urgency = in_array($_POST['urgency'] ?? '', ['Low', 'Medium', 'High', 'Critical']) ? $_POST['urgency'] : 'Medium';
        $location = trim($_POST['location'] ?? '');
        $areaId = (int)($_POST['area_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if (empty($title)) $errors[] = "Please provide a short summary of the help needed.";
        if (empty($description)) $errors[] = "Please describe the situation in detail.";
        if (empty($location)) $errors[] = "Location / Hospital / Landmark is required.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO help_requests (user_id, area_id, title, urgency, description, location, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, 'open')");
            $stmt->execute([$currentUser['id'], $areaId, $title, $urgency, $description, $location]);
            $helpId = $pdo->lastInsertId();

            $_SESSION['flash_success'] = "Help request broadcasted to your neighborhood network!";
            header("Location: " . BASE_URL . "/help-request.php?id=" . $helpId);
            exit;
        }
    }
}

$pageTitle = "Request Community Assistance";
require_once __DIR__ . '/includes/header.php';
$allAreas = get_active_areas($pdo);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="brand-icon" style="width: 44px; height: 44px; background: linear-gradient(135deg, #dc2626, #f87171);">
                        <i class="bi bi-heart-pulse-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">Ask for Community Help</h3>
                        <p class="text-muted small mb-0">Broadcast emergency needs, medical equipment loans, or volunteer requests.</p>
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

                <form method="POST" action="<?= BASE_URL ?>/create-help.php">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Help Summary / What do you need?</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Need manual wheelchair for 4 days / O+ Blood needed at Taluk Hospital" value="<?= htmlspecialchars($title) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Urgency Level</label>
                            <select name="urgency" class="form-select" required>
                                <option value="Low" <?= $urgency === 'Low' ? 'selected' : '' ?>>Low (Flexible timeframe)</option>
                                <option value="Medium" <?= $urgency === 'Medium' ? 'selected' : '' ?>>Medium (Needed within 24-48 hours)</option>
                                <option value="High" <?= $urgency === 'High' ? 'selected' : '' ?>>High (Needed today)</option>
                                <option value="Critical" <?= $urgency === 'Critical' ? 'selected' : '' ?>>Critical / Emergency (Immediate action)</option>
                            </select>
                        </div>
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
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Location / Landmark / Hospital</label>
                        <input type="text" name="location" class="form-control" required placeholder="e.g. West Fort Hospital Room 204 or Bypass Junction" value="<?= htmlspecialchars($location) ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Full Situation Details</label>
                        <textarea name="description" class="form-control" rows="5" required placeholder="Describe who needs help, exact requirements, contact instructions, and any safety notes..."><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/help.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Broadcast Help Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
