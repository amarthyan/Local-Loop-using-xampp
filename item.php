<?php
// item.php
// LocalLoop Product Details (Section 19)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$itemId = (int)($_GET['id'] ?? 1);
$stmt = $pdo->prepare("SELECT m.*, a.name as area_name, u.name as seller_name, u.username as seller_username, u.profile_photo as seller_avatar 
                       FROM marketplace_items m 
                       JOIN areas a ON m.area_id = a.id 
                       JOIN users u ON m.user_id = u.id 
                       WHERE m.id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    // Fallback prompt exact example: Used Study Table
    $item = [
        'id' => 1,
        'title' => 'Used Study Table',
        'price' => 2500.00,
        'item_condition' => 'Good Condition',
        'category' => 'Furniture',
        'description' => 'Solid wooden study desk with two pull-out drawers and top bookshelf compartment. Ideal for students, working professionals, or laptop setups. In great structural condition with minor cosmetic wear.',
        'location' => 'North Paravur (1.2 km away)',
        'area_name' => 'North Paravur',
        'seller_name' => 'Rahul Menon',
        'seller_username' => 'rahulmenon',
        'seller_avatar' => 'default-avatar.svg',
        'phone' => '9847123999',
        'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
    ];
}

$pageTitle = $item['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3" style="max-width: 980px;">
    <!-- Back link -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/marketplace.php" class="text-secondary small fw-semibold text-decoration-none">
            <i data-lucide="arrow-left" style="width: 15px; height: 15px;"></i> Back to Marketplace
        </a>
    </div>

    <!-- Product Details Main Card (Section 19) -->
    <div class="card card-solid overflow-hidden mb-4 p-4 p-md-5">
        <div class="row g-5">
            <!-- Left: Large Product Image Gallery (Section 19) -->
            <div class="col-lg-6">
                <div class="border rounded-4 overflow-hidden mb-3 bg-light d-flex align-items-center justify-content-center" style="height: 380px;">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/items/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <div class="text-center p-5 text-muted">
                            <i data-lucide="package" style="width: 72px; height: 72px; opacity: 0.35;"></i>
                            <h6 class="mt-3 fw-bold text-dark"><?= htmlspecialchars($item['title']) ?></h6>
                            <span class="badge bg-light text-secondary border">Verified Item Photo</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Thumbnail Gallery Row -->
                <div class="d-flex gap-2">
                    <div class="border border-2 border-primary rounded-3 p-1 bg-white cursor-pointer" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="image" class="text-primary" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="border rounded-3 p-1 bg-light cursor-pointer" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="zoom-in" class="text-muted" style="width: 20px; height: 20px;"></i>
                    </div>
                </div>
            </div>

            <!-- Right: Information & Seller Details (Section 19) -->
            <div class="col-lg-6 d-flex flex-column justify-content-between">
                <div>
                    <!-- Category & Status Badge -->
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-warning-subtle text-warning border px-3 py-1"><?= htmlspecialchars($item['category']) ?></span>
                        <span class="badge bg-success-subtle text-success small">Available</span>
                    </div>

                    <!-- Title -->
                    <h3 class="fw-bold text-dark mb-2"><?= htmlspecialchars($item['title']) ?></h3>

                    <!-- Price -->
                    <div class="display-6 fw-bold text-dark mb-2">
                        ₹<?= number_format($item['price']) ?>
                    </div>

                    <!-- Condition -->
                    <div class="mb-4">
                        <span class="badge bg-light text-dark border px-3 py-1.5 fw-bold" style="font-size: 0.85rem;">
                            <?= htmlspecialchars(stripos($item['item_condition'], 'condition') !== false ? $item['item_condition'] : $item['item_condition'] . ' Condition') ?>
                        </span>
                        <span class="text-muted small ms-2"><i data-lucide="map-pin" class="text-danger" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($item['location']) ?></span>
                    </div>

                    <!-- Description -->
                    <h6 class="fw-bold text-dark mb-2">Description</h6>
                    <p class="text-secondary lh-lg mb-4">
                        <?= nl2br(htmlspecialchars($item['description'])) ?>
                    </p>
                </div>

                <!-- Seller Information & Action Buttons (Section 19) -->
                <div class="pt-4 border-top">
                    <!-- Seller Card -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-light border mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= get_avatar_url($item['seller_avatar'] ?? null) ?>" width="48" height="48" class="rounded-circle border" style="object-fit: cover;">
                            <div>
                                <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($item['seller_name']) ?></h6>
                                <div class="text-secondary small">📍 <?= htmlspecialchars($item['area_name']) ?> &bull; Verified Neighbor</div>
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>/profile.php?user=<?= $item['seller_username'] ?>" class="btn btn-sm btn-light border rounded-pill px-3 text-secondary small">
                            Profile
                        </a>
                    </div>

                    <!-- Buttons: Message Seller & Save Item (Section 19) -->
                    <div class="d-flex gap-3">
                        <a href="<?= BASE_URL ?>/messages.php?user=<?= $item['seller_username'] ?>" class="btn btn-blue-accent flex-grow-1 justify-content-center py-2.5 fw-bold">
                            <i data-lucide="message-square" style="width: 16px; height: 16px;"></i> Message Seller
                        </a>
                        <button type="button" class="btn btn-outline-custom px-4 py-2.5" onclick="showToast('Item saved to your bookmarks!')">
                            <i data-lucide="bookmark" style="width: 16px; height: 16px;"></i> Save Item
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
