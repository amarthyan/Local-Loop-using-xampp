<?php
// create-lost-found.php
// LocalLoop Report Lost & Found Item

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();
$currentUser = current_user($pdo);

$errors = [];
$type = $_GET['type'] ?? 'lost';
$title = '';
$description = '';
$itemDate = date('Y-m-d');
$location = '';
$contactInfo = $currentUser['phone'] ?? $currentUser['email'];
$areaId = $currentUser['area_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security token mismatch.";
    } else {
        $type = in_array($_POST['type'] ?? '', ['lost', 'found']) ? $_POST['type'] : 'lost';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $itemDate = trim($_POST['item_date'] ?? date('Y-m-d'));
        $location = trim($_POST['location'] ?? '');
        $contactInfo = trim($_POST['contact_info'] ?? '');
        $areaId = (int)($_POST['area_id'] ?? 0);

        if (empty($title)) $errors[] = "Item title is required.";
        if (empty($description)) $errors[] = "Description is required.";
        if (empty($location)) $errors[] = "Location is required.";
        if (empty($contactInfo)) $errors[] = "Contact information is required.";

        if (empty($errors)) {
            $imageName = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadRes = upload_image_file($_FILES['image'], 'lostfound');
                if ($uploadRes['success']) {
                    $imageName = $uploadRes['fileName'];
                }
            }

            $stmt = $pdo->prepare("INSERT INTO lost_found (user_id, area_id, type, title, description, item_date, location, contact_info, image, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')");
            $stmt->execute([$currentUser['id'], $areaId, $type, $title, $description, $itemDate, $location, $contactInfo, $imageName]);

            award_reputation($pdo, $currentUser['id'], 5, 'Reported lost/found item');

            $_SESSION['flash_success'] = "Lost & Found report published to your locality!";
            header("Location: " . BASE_URL . "/lost-found.php");
            exit;
        }
    }
}

$pageTitle = "Report Lost or Found Item";
require_once __DIR__ . '/includes/header.php';
$allAreas = get_active_areas($pdo);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="brand-icon" style="width: 44px; height: 44px; background: linear-gradient(135deg, #ea580c, #fb923c);">
                        <i class="bi bi-binoculars-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">Report Lost or Found Item</h3>
                        <p class="text-muted small mb-0">Help neighbors recover misplaced valuables, keys, or IDs.</p>
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

                <form method="POST" action="<?= BASE_URL ?>/create-lost-found.php" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Report Type</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="type" id="typeLost" value="lost" <?= $type === 'lost' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-danger w-100 py-2 fw-semibold" for="typeLost">
                                    <i class="bi bi-exclamation-circle me-1"></i> I LOST an Item
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="type" id="typeFound" value="found" <?= $type === 'found' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-success w-100 py-2 fw-semibold" for="typeFound">
                                    <i class="bi bi-check-circle me-1"></i> I FOUND an Item
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Item Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Black Wildhorn Leather Wallet / Honda Bike Key with Blue Keychain" value="<?= htmlspecialchars($title) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Date of Incident</label>
                            <input type="date" name="item_date" class="form-control" required value="<?= htmlspecialchars($itemDate) ?>">
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
                        <label class="form-label fw-semibold small">Approximate Spot / Location</label>
                        <input type="text" name="location" class="form-control" required placeholder="e.g. Near Kodungallur Private Bus Stand bakery counter" value="<?= htmlspecialchars($location) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Detailed Description</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Describe appearance, color, brand, contents (do not share sensitive private pin/passwords)..."><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Info for Claims</label>
                            <input type="text" name="contact_info" class="form-control" required placeholder="Phone number or WhatsApp" value="<?= htmlspecialchars($contactInfo) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Photo (Optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/lost-found.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-accent-gradient rounded-pill px-4 fw-bold">Publish Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
