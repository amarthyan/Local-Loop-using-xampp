<?php
// admin/posts.php
// LocalLoop Post Content Moderation

$adminPageTitle = "Post Moderation";
require_once __DIR__ . '/header.php';

// Post actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_action'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $postId = (int)$_POST['target_post_id'];
        $action = $_POST['post_action'];

        if ($action === 'approve') {
            $pdo->prepare("UPDATE posts SET status = 'approved' WHERE id = ?")->execute([$postId]);
            log_admin_action($pdo, $currentUser['id'], 'approve_post', 'posts', $postId, 'Admin approved post');
            $_SESSION['flash_success'] = "Post approved and made visible in feed.";
        } elseif ($action === 'hide') {
            $pdo->prepare("UPDATE posts SET status = 'hidden' WHERE id = ?")->execute([$postId]);
            log_admin_action($pdo, $currentUser['id'], 'hide_post', 'posts', $postId, 'Admin hid post from feed');
            $_SESSION['flash_info'] = "Post hidden from feed.";
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$postId]);
            log_admin_action($pdo, $currentUser['id'], 'delete_post', 'posts', $postId, 'Admin deleted post permanently');
            $_SESSION['flash_success'] = "Post deleted.";
        }
        header("Location: " . BASE_URL . "/admin/posts.php");
        exit;
    }
}

// Filters
$categoryFilter = !empty($_GET['category']) ? (int)$_GET['category'] : 0;
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$where = ["1=1"];
$params = [];

if ($categoryFilter) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryFilter;
}
if (!empty($statusFilter)) {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}
if (!empty($search)) {
    $where[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT p.*, u.name as author_name, u.username as author_username, 
                              a.name as area_name, c.name as cat_name, c.color as cat_color,
                              (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
                              (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as total_comments
                       FROM posts p 
                       JOIN users u ON p.user_id = u.id 
                       JOIN areas a ON p.area_id = a.id 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE {$whereSql} 
                       ORDER BY p.created_at DESC");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$categories = get_active_categories($pdo);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">Community Posts Moderation</h1>
    <span class="badge bg-primary fs-6 px-3 py-1.5 rounded-pill"><?= count($posts) ?> Posts</span>
</div>

<!-- Filters -->
<div class="card card-clean border-0 shadow-sm p-3 mb-4">
    <form method="GET" action="posts.php" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search post title or content..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $categoryFilter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="hidden" <?= $statusFilter === 'hidden' ? 'selected' : '' ?>>Hidden</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-primary w-100 rounded-pill">Filter</button>
        </div>
    </form>
</div>

<!-- Posts Table -->
<div class="card card-clean border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Title & Content</th>
                    <th>Author</th>
                    <th>Channel</th>
                    <th>Locality</th>
                    <th>Engagement</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="small">
                <?php if (empty($posts)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No posts found matching filter.</td></tr>
                <?php else: ?>
                    <?php foreach ($posts as $p): ?>
                        <tr>
                            <td style="max-width: 280px;">
                                <div class="fw-bold text-dark text-truncate">
                                    <a href="<?= BASE_URL ?>/post.php?id=<?= $p['id'] ?>" target="_blank" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($p['title']) ?>
                                    </a>
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 0.72rem;"><?= htmlspecialchars(substr($p['description'], 0, 75)) ?>...</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($p['author_name']) ?></div>
                                <span class="text-muted" style="font-size: 0.72rem;">@<?= htmlspecialchars($p['author_username']) ?></span>
                            </td>
                            <td>
                                <span class="badge" style="background-color: <?= htmlspecialchars($p['cat_color']) ?>; color: #fff;">
                                    <?= htmlspecialchars($p['cat_name']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($p['area_name']) ?></td>
                            <td>
                                <span class="text-danger me-1"><i class="bi bi-heart-fill"></i> <?= $p['total_likes'] ?></span>
                                <span class="text-primary"><i class="bi bi-chat-fill"></i> <?= $p['total_comments'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $p['status'] === 'approved' ? 'success' : ($p['status'] === 'hidden' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?php if ($p['status'] === 'hidden'): ?>
                                        <form method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="target_post_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="post_action" value="approve">
                                            <button type="submit" class="btn btn-xs btn-outline-success py-0 px-2 rounded-pill" title="Approve Post">Approve</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="target_post_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="post_action" value="hide">
                                            <button type="submit" class="btn btn-xs btn-outline-warning py-0 px-2 rounded-pill" title="Hide Post">Hide</button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="target_post_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="post_action" value="delete">
                                        <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2 rounded-pill" onclick="return confirm('Permanently delete this post?');" title="Delete Post">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
