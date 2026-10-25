<?php
// admin/areas.php
// LocalLoop Localities & Area Management

$adminPageTitle = "Areas & Localities Management";
require_once __DIR__ . '/header.php';

// Add Area
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_area'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $name = trim($_POST['name'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $state = trim($_POST['state'] ?? 'Kerala');
        $pincode = trim($_POST['pincode'] ?? '');

        if (!empty($name) && !empty($district)) {
            $stmt = $pdo->prepare("INSERT INTO areas (name, district, state, pincode, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$name, $district, $state, $pincode]);
            log_admin_action($pdo, $currentUser['id'], 'create_area', 'areas', $pdo->lastInsertId(), "Created area: {$name}");
            $_SESSION['flash_success'] = "New locality registered!";
            header("Location: " . BASE_URL . "/admin/areas.php");
            exit;
        }
    }
}

// Toggle Status
if (isset($_GET['toggle_id'])) {
    $areaId = (int)$_GET['toggle_id'];
    $curStatus = $pdo->query("SELECT status FROM areas WHERE id = {$areaId}")->fetchColumn();
    $newStatus = $curStatus === 'active' ? 'inactive' : 'active';
    $pdo->prepare("UPDATE areas SET status = ? WHERE id = ?")->execute([$newStatus, $areaId]);
    $_SESSION['flash_info'] = "Area status changed to {$newStatus}.";
    header("Location: " . BASE_URL . "/admin/areas.php");
    exit;
}

$areas = $pdo->query("SELECT a.*, 
                             (SELECT COUNT(*) FROM users WHERE area_id = a.id) as user_count,
                             (SELECT COUNT(*) FROM posts WHERE area_id = a.id) as post_count 
                      FROM areas a ORDER BY a.name ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">Hyperlocal Localities & Towns</h1>
    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addAreaModal">
        <i class="bi bi-plus-lg me-1"></i> Register New Locality
    </button>
</div>

<div class="card card-clean border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Town / Locality</th>
                    <th>District</th>
                    <th>State</th>
                    <th>Pincode</th>
                    <th>Residents</th>
                    <th>Posts</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody class="small">
                <?php foreach ($areas as $area): ?>
                    <tr>
                        <td class="fw-bold text-dark">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <?= htmlspecialchars($area['name']) ?>
                        </td>
                        <td><?= htmlspecialchars($area['district']) ?></td>
                        <td><?= htmlspecialchars($area['state']) ?></td>
                        <td><code><?= htmlspecialchars($area['pincode']) ?></code></td>
                        <td><span class="badge bg-light text-dark border"><?= $area['user_count'] ?></span></td>
                        <td><span class="badge bg-light text-dark border"><?= $area['post_count'] ?></span></td>
                        <td>
                            <span class="badge bg-<?= $area['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($area['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="areas.php?toggle_id=<?= $area['id'] ?>" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill">
                                <?= $area['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Area Modal -->
<div class="modal fade" id="addAreaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fs-6 fw-bold">Add New Locality / Town</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="areas.php">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Locality / Town Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Guruvayur or Kozhikode Beach">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">District</label>
                            <input type="text" name="district" class="form-control" required placeholder="e.g. Thrissur">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Pincode</label>
                            <input type="text" name="pincode" class="form-control" required placeholder="680101">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">State</label>
                        <input type="text" name="state" class="form-control" value="Kerala">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_area" class="btn btn-primary btn-sm px-3">Save Locality</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
