<?php
// admin/comments.php
// LocalLoop Comments Moderation

$adminPageTitle = "Comments Moderation";
require_once __DIR__ . '/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $cmtId = (int)$_POST['comment_id'];
        $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$cmtId]);
        log_admin_action($pdo, $currentUser['id'], 'delete_comment', 'comments', $cmtId, 'Admin deleted comment');
        $_SESSION['flash_success'] = "Comment deleted.";
        header("Location: " . BASE_URL . "/admin/comments.php");
        exit;
    }
}

$stmt = $pdo->query("SELECT c.*, u.name as user_name, u.username as user_username, p.title as post_title 
                     FROM comments c 
                     JOIN users u ON c.user_id = u.id 
                     JOIN posts p ON c.post_id = p.id 
                     ORDER BY c.created_at DESC LIMIT 100");
$comments = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">Community Comments Moderation</h1>
    <span class="badge bg-primary fs-6 px-3 py-1.5 rounded-pill"><?= count($comments) ?> Comments</span>
</div>

<div class="card card-clean border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Comment</th>
                    <th>Author</th>
                    <th>Post Thread</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="small">
                <?php if (empty($comments)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No comments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <tr>
                            <td style="max-width: 320px;">
                                <div class="text-dark" style="white-space: pre-line;"><?= htmlspecialchars($c['comment']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($c['user_name']) ?></div>
                                <span class="text-muted" style="font-size: 0.72rem;">@<?= htmlspecialchars($c['user_username']) ?></span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/post.php?id=<?= $c['post_id'] ?>" target="_blank" class="text-decoration-none fw-semibold">
                                    <?= htmlspecialchars(substr($c['post_title'], 0, 45)) ?>...
                                </a>
                            </td>
                            <td><?= time_ago($c['created_at']) ?></td>
                            <td class="text-end">
                                <form method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                    <button type="submit" name="delete_comment" class="btn btn-xs btn-outline-danger py-0 px-2 rounded-pill" onclick="return confirm('Delete this comment?');">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
