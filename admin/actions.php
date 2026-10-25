<?php
// admin/actions.php
// LocalLoop Admin Audit Trail & Logs

$adminPageTitle = "Admin Audit Logs";
require_once __DIR__ . '/header.php';

$stmt = $pdo->query("SELECT a.*, u.name as admin_name, u.username as admin_username 
                     FROM admin_actions a 
                     JOIN users u ON a.admin_id = u.id 
                     ORDER BY a.created_at DESC LIMIT 100");
$actions = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-journal-text text-primary me-2"></i> Administrator Audit Logs</h1>
        <p class="text-muted small mb-0">Chronological history of administrative actions, moderation, suspensions, and changes.</p>
    </div>
    <span class="badge bg-secondary fs-6 px-3 py-1.5 rounded-pill"><?= count($actions) ?> Events</span>
</div>

<div class="card card-clean border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Timestamp</th>
                    <th>Administrator</th>
                    <th>Action</th>
                    <th>Target Resource</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody class="small">
                <?php if (empty($actions)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No audit events recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($actions as $act): ?>
                        <tr>
                            <td class="text-muted"><?= date('M j, Y g:i A', strtotime($act['created_at'])) ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($act['admin_name']) ?></div>
                                <span class="text-muted" style="font-size: 0.72rem;">@<?= htmlspecialchars($act['admin_username']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary fw-semibold"><?= htmlspecialchars($act['action']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($act['target_type']) ?> #<?= $act['target_id'] ?>
                                </span>
                            </td>
                            <td class="text-secondary"><?= htmlspecialchars($act['notes'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
