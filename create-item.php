<?php
// create-item.php
// LocalLoop Post Marketplace Listing

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();
$currentUser = current_user($pdo);

$errors = [];
$title = '';
$category = 'Electronics';
$price = '';
$condition = 'Good';
$description = '';
$location = '';
$phone = $currentUser['phone'] ?? '';
$areaId = $currentUser['area_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security token mismatch.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Other');
        $price = (float)($_POST['price'] ?? 0);
        $condition = in_array($_POST['item_condition'] ?? '', ['New', 'Like New', 'Good', 'Fair']) ? $_POST['item_condition'] : 'Good';
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $areaId = (int)($_POST['area_id'] ?? 0);

        if (empty($title)) $errors[] = "Item title is required.";
        if ($price <= 0) $errors[] = "Please specify a valid asking price.";
        if (empty($description)) $errors[] = "Item description is required.";
        if (empty($location)) $errors[] = "Location is required.";

        if (empty($errors)) {
            $imageName = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadRes = upload_image_file($_FILES['image'], 'marketplace');
                if ($uploadRes['success']) {
                    $imageName = $uploadRes['fileName'];
                }
            }

            $stmt = $pdo->prepare("INSERT INTO marketplace_items (user_id, area_id, title, category, price, item_condition, description, location, image, phone, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available')");
            $stmt->execute([$currentUser['id'], $areaId, $title, $category, $price, $condition, $description, $location, $imageName, $phone]);
            $itemId = $pdo->lastInsertId();

            award_reputation($pdo, $currentUser['id'], 6, 'Listed marketplace item');

            $_SESSION['flash_success'] = "Your item is live on the neighborhood marketplace!";
            header("Location: " . BASE_URL . "/item.php?id=" . $itemId);
            exit;
        }
    }
}

$categories = ['Phones & Tablets', 'Laptops & PCs', 'Bikes & Cycles', 'Furniture', 'Books & Study', 'Electronics', 'Vehicles', 'Other'];
$pageTitle = "Sell an Item";
require_once __DIR__ . '/includes/header.php';
$allAreas = get_active_areas($pdo);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="brand-icon" style="width: 44px; height: 44px; background: linear-gradient(135deg, #d97706, #fbbf24);">
                        <i class="bi bi-tag-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">List an Item for Sale</h3>
                        <p class="text-muted small mb-0">Sell pre-owned devices, cycles, furniture, or books safely nearby.</p>
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

                <form method="POST" action="<?= BASE_URL ?>/create-item.php" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Item Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Dell 24-inch Full HD IPS Monitor" value="<?= htmlspecialchars($title) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Category</label>
                            <select name="category" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Asking Price (₹ INR)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="price" class="form-control" required placeholder="3500.00" value="<?= htmlspecialchars($price) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Item Condition</label>
                            <select name="item_condition" class="form-select">
                                <option value="New" <?= $condition === 'New' ? 'selected' : '' ?>>New (Unopened / Tagged)</option>
                                <option value="Like New" <?= $condition === 'Like New' ? 'selected' : '' ?>>Like New (Barely used)</option>
                                <option value="Good" <?= $condition === 'Good' ? 'selected' : '' ?>>Good (Minor normal wear)</option>
                                <option value="Fair" <?= $condition === 'Fair' ? 'selected' : '' ?>>Fair (Fully functional)</option>
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

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Pickup Landmark / Location</label>
                            <input type="text" name="location" class="form-control" required placeholder="e.g. Near InfoPark Gate 2" value="<?= htmlspecialchars($location) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Phone for Buyers</label>
                            <input type="tel" name="phone" class="form-control" placeholder="9847XXXXXX" value="<?= htmlspecialchars($phone) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Detailed Description</label>
                        <textarea name="description" class="form-control" rows="5" required placeholder="Include age of item, brand, specs, reason for selling, accessories included..."><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Photo of Item (Recommended)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/marketplace.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">Publish Listing</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
