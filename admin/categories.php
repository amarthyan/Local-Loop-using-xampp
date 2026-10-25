<?php
// admin/categories.php
// LocalLoop Category Management

$adminPageTitle = "Categories Management";
require_once __DIR__ . '/header.php';

// Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $name = trim($_POST['name'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');
        $color = trim($_POST['color'] ?? '#2563eb');

        if (!empty($name) && !empty($slug)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon, color, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$name, $slug, $description, $icon, $color]);
            log_admin_action($pdo, $currentUser['id'], 'create_category', 'categories', $pdo->lastInsertId(), "Created category: {$name}");
            $_SESSION['flash_success'] = "New category added!";
            header("Location: " . BASE_URL . "/admin/categories.php");
            exit;
        }
    }
}

// Toggle Status
if (isset($_GET['toggle_id'])) {
    $catId = (int)$_GET['toggle_id'];
    $curStatus = $pdo->query("SELECT status FROM categories WHERE id = {$catId}")->fetchColumn();
    $newStatus = $curStatus === 'active' ? 'inactive' : 'active';
    $pdo->prepare("UPDATE categories SET status = ? WHERE id = ?")->execute([$newStatus, $catId]);
    $_SESSION['flash_info'] = "Category status changed to {$newStatus}.";
    header("Location: " . BASE_URL . "/admin/categories.php");
    exit;
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM posts WHERE category_id = c.id) as post_count 
                           FROM categories c ORDER BY c.id ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">Community Channels & Categories</h1>
    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-lg me-1"></i> Add Category
    </button>
</div>

<div class="card card-clean border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Category</th>
                    <th>Slug</th>
                    <th>Icon & Color</th>
                    <th>Description</th>
                    <th>Posts</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody class="small">
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="fw-bold text-dark">
                            <i class="bi <?= htmlspecialchars($cat['icon']) ?> me-2" style="color: <?= htmlspecialchars($cat['color']) ?>;"></i>
                            <?= htmlspecialchars($cat['name']) ?>
                        </td>
                        <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                        <td>
                            <span class="badge" style="background-color: <?= htmlspecialchars($cat['color']) ?>; color: #fff;">
                                <?= htmlspecialchars($cat['color']) ?>
                            </span>
                        </td>
                        <td class="text-muted" style="max-width: 250px;"><?= htmlspecialchars($cat['description']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $cat['post_count'] ?></span></td>
                        <td>
                            <span class="badge bg-<?= $cat['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($cat['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="categories.php?toggle_id=<?= $cat['id'] ?>" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill">
                                <?= $cat['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fs-6 fw-bold">Add New Channel Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="categories.php">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Category Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Volunteer Opportunities">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Bootstrap Icon Class</label>
                            <input type="text" name="icon" class="form-control" value="bi-tag-fill">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Brand Color</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="#4f46e5">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief channel purpose..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_category" class="btn btn-primary btn-sm px-3">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
