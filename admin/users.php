<?php
// admin/users.php
// LocalLoop User Management

$adminPageTitle = "User Management";
require_once __DIR__ . '/header.php';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_action'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $targetUserId = (int)$_POST['target_user_id'];
        $action = $_POST['user_action'];

        if ($targetUserId === $currentUser['id'] && in_array($action, ['suspend', 'delete', 'demote'])) {
            $_SESSION['flash_error'] = "You cannot modify your own administrative status.";
        } else {
            if ($action === 'warn') {
                $pdo->prepare("UPDATE users SET status = 'warned' WHERE id = ?")->execute([$targetUserId]);
                create_notification($pdo, $targetUserId, $currentUser['id'], 'admin_warning', 'Your account has received a warning from administration for violating guidelines.', 'about.php#guidelines');
                log_admin_action($pdo, $currentUser['id'], 'warn_user', 'users', $targetUserId, 'Admin issued warning');
                $_SESSION['flash_success'] = "User marked as warned.";
            } elseif ($action === 'suspend') {
                $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?")->execute([$targetUserId]);
                log_admin_action($pdo, $currentUser['id'], 'suspend_user', 'users', $targetUserId, 'Admin suspended user account');
                $_SESSION['flash_success'] = "User suspended.";
            } elseif ($action === 'activate') {
                $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$targetUserId]);
                log_admin_action($pdo, $currentUser['id'], 'activate_user', 'users', $targetUserId, 'Admin restored user account');
                $_SESSION['flash_success'] = "User activated.";
            } elseif ($action === 'make_admin') {
                $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$targetUserId]);
                log_admin_action($pdo, $currentUser['id'], 'make_admin', 'users', $targetUserId, 'Admin promoted user to admin');
                $_SESSION['flash_success'] = "User promoted to Administrator.";
            } elseif ($action === 'make_user') {
                $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?")->execute([$targetUserId]);
                log_admin_action($pdo, $currentUser['id'], 'make_user', 'users', $targetUserId, 'Admin demoted user to standard role');
                $_SESSION['flash_success'] = "Admin privilege removed.";
            } elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$targetUserId]);
                log_admin_action($pdo, $currentUser['id'], 'delete_user', 'users', $targetUserId, 'Admin deleted user permanently');
                $_SESSION['flash_success'] = "User account deleted.";
            }
        }
        header("Location: " . BASE_URL . "/admin/users.php");
        exit;
    }
}

// Filters
$search = trim($_GET['q'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if (!empty($roleFilter)) {
    $where[] = "u.role = ?";
    $params[] = $roleFilter;
}
if (!empty($statusFilter)) {
    $where[] = "u.status = ?";
    $params[] = $statusFilter;
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT u.*, a.name as area_name, 
                             (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count 
                      FROM users u 
                      LEFT JOIN areas a ON u.area_id = a.id 
                      WHERE {$whereSql} 
                      ORDER BY u.created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">Registered Community Members</h1>
    <span class="badge bg-primary fs-6 px-3 py-1.5 rounded-pill"><?= count($users) ?> Users</span>
</div>

<!-- Filter Card -->
<div class="card card-clean border-0 shadow-sm p-3 mb-4">
    <form method="GET" action="users.php" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by name, username or email..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select form-select-sm">
                <option value="">All Roles</option>
                <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>Citizen / Resident</option>
                <option value="business" <?= $roleFilter === 'business' ? 'selected' : '' ?>>Local Business</option>
                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Administrator</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="warned" <?= $statusFilter === 'warned' ? 'selected' : '' ?>>Warned</option>
                <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-primary w-100 rounded-pill">Filter</button>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="card card-clean border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Member</th>
                    <th>Locality</th>
                    <th>Role</th>
                    <th>Reputation</th>
                    <th>Posts</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="small">
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No users found matching query.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= get_avatar_url($u['profile_photo']) ?>" width="34" height="34" class="rounded-circle border">
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="text-muted" style="font-size: 0.72rem;">@<?= htmlspecialchars($u['username']) ?> &bull; <?= htmlspecialchars($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['area_name'] ?? 'Not set') ?></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= ucfirst($u['role']) ?></span></td>
                            <td><span class="fw-semibold text-success"><?= $u['reputation_points'] ?> pts</span></td>
                            <td><?= $u['post_count'] ?></td>
                            <td>
                                <span class="badge bg-<?= $u['status'] === 'active' ? 'success' : ($u['status'] === 'warned' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($u['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border py-0 px-2" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 small">
                                        <li>
                                            <a class="dropdown-item" href="<?= BASE_URL ?>/profile.php?user=<?= $u['username'] ?>" target="_blank">
                                                <i class="bi bi-person me-2"></i> View Profile
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php if ($u['status'] !== 'warned'): ?>
                                            <li>
                                                <form method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="user_action" value="warn">
                                                    <button type="submit" class="dropdown-item text-warning"><i class="bi bi-exclamation-triangle me-2"></i> Issue Warning</button>
                                                </form>
                                            </li>
                                        <?php endif; ?>

                                        <?php if ($u['status'] !== 'suspended'): ?>
                                            <li>
                                                <form method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="user_action" value="suspend">
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Suspend this user account?');"><i class="bi bi-slash-circle me-2"></i> Suspend Account</button>
                                                </form>
                                            </li>
                                        <?php else: ?>
                                            <li>
                                                <form method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="user_action" value="activate">
                                                    <button type="submit" class="dropdown-item text-success"><i class="bi bi-check-circle me-2"></i> Reactivate Account</button>
                                                </form>
                                            </li>
                                        <?php endif; ?>

                                        <?php if ($u['role'] !== 'admin'): ?>
                                            <li>
                                                <form method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="user_action" value="make_admin">
                                                    <button type="submit" class="dropdown-item text-primary" onclick="return confirm('Promote user to Administrator?');"><i class="bi bi-shield-check me-2"></i> Promote to Admin</button>
                                                </form>
                                            </li>
                                        <?php endif; ?>

                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                <input type="hidden" name="user_action" value="delete">
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Permanently delete this user and all their posts?');"><i class="bi bi-trash me-2"></i> Delete Account</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
