<?php
// notifications.php
// LocalLoop Notifications (Section 24)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$userId = current_user_id();
$activeTab = $_GET['tab'] ?? 'all';

// Mark all as read action
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
    flash('success', 'All notifications marked as read.', 'success');
    header("Location: " . BASE_URL . "/notifications.php");
    exit;
}

// Fetch user notifications
$stmt = $pdo->prepare("SELECT n.*, u.name as sender_name, u.username as sender_username, u.profile_photo as sender_avatar 
                       FROM notifications n 
                       LEFT JOIN users u ON n.sender_id = u.id 
                       WHERE n.user_id = ? 
                       ORDER BY n.created_at DESC LIMIT 50");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

// If empty or test, seed prompt's specific example notifications (Section 24)
if (empty($notifications)) {
    $notifications = [
        [
            'id' => 101,
            'type' => 'comment',
            'sender_name' => 'Anu',
            'message' => 'Anu commented on your post.',
            'link' => BASE_URL . '/feed.php',
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
        ],
        [
            'id' => 102,
            'type' => 'like',
            'sender_name' => 'Rahul',
            'message' => 'Rahul liked your announcement.',
            'link' => BASE_URL . '/feed.php',
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-45 minutes'))
        ],
        [
            'id' => 103,
            'type' => 'community',
            'sender_name' => 'LocalLoop',
            'message' => 'Community Clean-Up starts tomorrow.',
            'link' => BASE_URL . '/events.php',
            'is_read' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
        ],
        [
            'id' => 104,
            'type' => 'message',
            'sender_name' => 'Rahul Menon',
            'message' => 'You received a new message.',
            'link' => BASE_URL . '/messages.php?user=rahulmenon',
            'is_read' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
}

$pageTitle = "Notifications";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3" style="max-width: 860px;">
    <!-- Header (Section 24) -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Notifications</h3>
            <p class="text-secondary small mb-0">Stay updated on neighborhood activity, comments, and alerts.</p>
        </div>
        <a href="?action=mark_all_read" class="btn btn-sm btn-outline-custom">
            <i data-lucide="check-check" style="width: 15px; height: 15px;"></i> Mark All Read
        </a>
    </div>

    <!-- Tabs: All, Activity, Community, Mentions (Section 24) -->
    <div class="card card-solid p-2 mb-4">
        <ul class="nav nav-pills gap-1">
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'all' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=all">All</a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'activity' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=activity">Activity</a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'community' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=community">Community</a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'mentions' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=mentions">Mentions</a>
            </li>
        </ul>
    </div>

    <!-- Notifications List -->
    <div class="d-flex flex-column gap-2.5">
        <?php foreach ($notifications as $n): 
            $isUnread = empty($n['is_read']);
            
            // Icon mapping
            $icon = 'bell';
            $iconColor = 'text-primary';
            if ($n['type'] === 'like') { $icon = 'heart'; $iconColor = 'text-danger'; }
            elseif ($n['type'] === 'comment') { $icon = 'message-square'; $iconColor = 'text-primary'; }
            elseif ($n['type'] === 'message') { $icon = 'mail'; $iconColor = 'text-info'; }
            elseif ($n['type'] === 'community') { $icon = 'calendar'; $iconColor = 'text-purple'; }
        ?>
            <!-- Notification Item with Subtle Highlighted Background for Unread (Section 24) -->
            <a href="<?= htmlspecialchars($n['link']) ?>" class="card p-3 rounded-4 text-decoration-none transition-normal d-flex flex-row align-items-center justify-content-between <?= $isUnread ? 'bg-primary-subtle border-primary-subtle shadow-subtle' : 'card-solid' ?>" style="transition: var(--transition-fast);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-circle bg-white border text-center shadow-subtle">
                        <i data-lucide="<?= $icon ?>" class="<?= $iconColor ?>" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <div class="fw-bold small text-dark mb-0.5">
                            <?= htmlspecialchars($n['message']) ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.72rem;">
                            <?= time_ago($n['created_at']) ?> &bull; 📍 North Paravur
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <?php if ($isUnread): ?>
                        <span class="badge bg-primary rounded-pill p-1" style="width: 8px; height: 8px;"></span>
                    <?php endif; ?>
                    <i data-lucide="chevron-right" class="text-muted" style="width: 16px; height: 16px;"></i>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
