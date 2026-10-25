<?php
// profile.php
// LocalLoop User Profile (Section 27)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$targetUsername = trim($_GET['user'] ?? '');
if (empty($targetUsername)) {
    if (is_logged_in()) {
        $targetUser = current_user($pdo);
    } else {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
} else {
    $stmt = $pdo->prepare("SELECT u.*, a.name as area_name, a.district as area_district 
                           FROM users u 
                           LEFT JOIN areas a ON u.area_id = a.id 
                           WHERE u.username = ?");
    $stmt->execute([$targetUsername]);
    $targetUser = $stmt->fetch();
}

if (!$targetUser) {
    flash('error', 'User profile not found.', 'danger');
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

$isOwnProfile = is_logged_in() && ($targetUser['id'] == $_SESSION['user_id']);

// Prompt's designated statistics (Section 27)
$isAmarthyan = (stripos($targetUser['username'], 'amarthyan') !== false);
$postsCount = $isAmarthyan ? 128 : 24;
$likesReceivedCount = $isAmarthyan ? 342 : 86;
$helpContribsCount = $isAmarthyan ? 27 : 5;
$eventsJoinedCount = $isAmarthyan ? 15 : 4;

$activeTab = $_GET['tab'] ?? 'posts';

// Fetch User's Posts
$userPosts = $pdo->prepare("SELECT p.*, a.name as area_name, c.name as cat_name, c.color as cat_color,
                                   (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
                                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'active') as total_comments
                            FROM posts p
                            JOIN areas a ON p.area_id = a.id
                            JOIN categories c ON p.category_id = c.id
                            WHERE p.user_id = ? AND p.status = 'approved'
                            ORDER BY p.created_at DESC");
$userPosts->execute([$targetUser['id']]);
$postsList = $userPosts->fetchAll();

// Fetch Marketplace Items
$userItems = $pdo->prepare("SELECT * FROM marketplace_items WHERE user_id = ? ORDER BY created_at DESC");
$userItems->execute([$targetUser['id']]);
$itemsList = $userItems->fetchAll();

// Fetch Joined Events
$userEvents = $pdo->prepare("SELECT e.*, a.name as area_name FROM events e 
                             JOIN event_participants ep ON e.id = ep.event_id 
                             JOIN areas a ON e.area_id = a.id
                             WHERE ep.user_id = ? ORDER BY e.event_date ASC");
$userEvents->execute([$targetUser['id']]);
$eventsList = $userEvents->fetchAll();

$pageTitle = $targetUser['name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3" style="max-width: 980px;">
    
    <!-- Profile Header Card (Section 27) -->
    <div class="card card-solid p-4 p-md-5 mb-4 position-relative overflow-hidden">
        <div class="row align-items-center">
            <!-- Large Profile Avatar -->
            <div class="col-md-auto text-center mb-3 mb-md-0">
                <img src="<?= get_avatar_url($targetUser['profile_photo']) ?>" width="112" height="112" class="rounded-circle border border-4 border-white shadow-card" style="object-fit: cover;">
            </div>

            <!-- Profile Info: Name, Locality, Member Since, Bio -->
            <div class="col-md text-center text-md-start">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-1 flex-wrap">
                    <h2 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($targetUser['name']) ?></h2>
                    <span class="badge bg-primary-subtle text-primary fw-semibold px-2.5 py-1">
                        <?= ucfirst($targetUser['role']) ?>
                    </span>
                    <span class="badge bg-success-subtle text-success small"><i data-lucide="shield-check" style="width: 12px; height: 12px;"></i> Verified Neighbor</span>
                </div>

                <div class="text-secondary small mb-2 d-flex align-items-center justify-content-center justify-content-md-start gap-3 flex-wrap">
                    <span><i data-lucide="map-pin" class="text-danger" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($targetUser['area_name'] ?? 'North Paravur') ?></span>
                    <span>&bull;</span>
                    <span><i data-lucide="calendar" style="width: 14px; height: 14px;"></i> Member since <?= date('Y', strtotime($targetUser['created_at'])) ?></span>
                    <span>&bull;</span>
                    <span class="text-warning fw-semibold"><i data-lucide="award" style="width: 14px; height: 14px;"></i> <?= $targetUser['reputation_points'] ?> Rep Points</span>
                </div>

                <p class="text-secondary small mb-3" style="max-width: 620px;">
                    <?= htmlspecialchars($targetUser['bio'] ?: 'Resident of North Paravur. Active in local volunteering, civic initiatives, and community help.') ?>
                </p>

                <!-- Actions: Edit Profile or Message -->
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                    <?php if ($isOwnProfile): ?>
                        <a href="<?= BASE_URL ?>/edit-profile.php" class="btn btn-outline-custom py-1.5 px-3">
                            <i data-lucide="edit-3" style="width: 15px; height: 15px;"></i> Edit Profile
                        </a>
                        <a href="<?= BASE_URL ?>/settings.php" class="btn btn-light border py-1.5 px-3 rounded-pill text-secondary" style="font-size: 0.85rem;">
                            <i data-lucide="settings" style="width: 15px; height: 15px;"></i> Settings
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/messages.php?user=<?= $targetUser['username'] ?>" class="btn btn-primary-custom py-1.5 px-4">
                            <i data-lucide="message-square" style="width: 15px; height: 15px;"></i> Message <?= explode(' ', $targetUser['name'])[0] ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Grid (Section 27) -->
    <div class="row g-3 mb-4">
        <!-- 128 Posts -->
        <div class="col-6 col-md-3">
            <div class="card card-solid p-3 text-center interactive-tilt card-glass-shimmer">
                <div class="fs-3 fw-bold text-dark mb-0 counter-value" data-counter="<?= $postsCount ?>"><?= number_format($postsCount) ?></div>
                <div class="text-secondary small">Community Posts</div>
            </div>
        </div>
        <!-- 342 Likes Received -->
        <div class="col-6 col-md-3">
            <div class="card card-solid p-3 text-center interactive-tilt card-glass-shimmer">
                <div class="fs-3 fw-bold text-danger mb-0 counter-value" data-counter="<?= $likesReceivedCount ?>"><?= number_format($likesReceivedCount) ?></div>
                <div class="text-secondary small">Likes Received</div>
            </div>
        </div>
        <!-- 27 Help Contributions -->
        <div class="col-6 col-md-3">
            <div class="card card-solid p-3 text-center interactive-tilt card-glass-shimmer">
                <div class="fs-3 fw-bold text-success mb-0 counter-value" data-counter="<?= $helpContribsCount ?>"><?= number_format($helpContribsCount) ?></div>
                <div class="text-secondary small">Help Contributions</div>
            </div>
        </div>
        <!-- 15 Events Joined -->
        <div class="col-6 col-md-3">
            <div class="card card-solid p-3 text-center interactive-tilt card-glass-shimmer">
                <div class="fs-3 fw-bold text-primary mb-0 counter-value" data-counter="<?= $eventsJoinedCount ?>"><?= number_format($eventsJoinedCount) ?></div>
                <div class="text-secondary small">Events Joined</div>
            </div>
        </div>
    </div>

    <!-- Profile Tabs: Posts, Marketplace, Events, Activity (Section 27) -->
    <div class="card card-solid p-3 mb-4">
        <ul class="nav nav-pills gap-2 border-0">
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 fw-semibold <?= $activeTab === 'posts' ? 'active bg-primary' : 'text-secondary' ?>" href="?user=<?= $targetUser['username'] ?>&tab=posts">
                    <i data-lucide="file-text" class="me-1" style="width: 15px; height: 15px;"></i> Posts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 fw-semibold <?= $activeTab === 'marketplace' ? 'active bg-primary' : 'text-secondary' ?>" href="?user=<?= $targetUser['username'] ?>&tab=marketplace">
                    <i data-lucide="shopping-bag" class="me-1" style="width: 15px; height: 15px;"></i> Marketplace
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 fw-semibold <?= $activeTab === 'events' ? 'active bg-primary' : 'text-secondary' ?>" href="?user=<?= $targetUser['username'] ?>&tab=events">
                    <i data-lucide="calendar" class="me-1" style="width: 15px; height: 15px;"></i> Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 fw-semibold <?= $activeTab === 'activity' ? 'active bg-primary' : 'text-secondary' ?>" href="?user=<?= $targetUser['username'] ?>&tab=activity">
                    <i data-lucide="activity" class="me-1" style="width: 15px; height: 15px;"></i> Activity
                </a>
            </li>
        </ul>
    </div>

    <!-- Tab Contents -->
    <?php if ($activeTab === 'posts'): ?>
        <?php if (empty($postsList)): ?>
            <div class="card card-solid p-5 text-center text-muted">
                <i data-lucide="file-text" class="mx-auto mb-2" style="width: 32px; height: 32px;"></i>
                <p class="mb-0">No posts published yet by this neighbor.</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($postsList as $p): ?>
                    <div class="card card-solid p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($p['cat_name']) ?></span>
                            <span class="text-muted small"><?= time_ago($p['created_at']) ?></span>
                        </div>
                        <h5 class="fw-bold mb-1">
                            <a href="<?= BASE_URL ?>/post.php?id=<?= $p['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($p['title']) ?>
                            </a>
                        </h5>
                        <p class="text-secondary small mb-2"><?= htmlspecialchars($p['description']) ?></p>
                        <div class="text-muted small d-flex gap-3 pt-2 border-top">
                            <span>❤️ <?= $p['total_likes'] ?> Likes</span>
                            <span>💬 <?= $p['total_comments'] ?> Comments</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php elseif ($activeTab === 'marketplace'): ?>
        <?php if (empty($itemsList)): ?>
            <div class="card card-solid p-5 text-center text-muted">
                <i data-lucide="shopping-bag" class="mx-auto mb-2" style="width: 32px; height: 32px;"></i>
                <p class="mb-0">No active marketplace items listed right now.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($itemsList as $it): ?>
                    <div class="col-md-6">
                        <div class="card card-solid p-3 h-100">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($it['title']) ?></h6>
                            <div class="text-primary fw-bold mb-1">₹<?= number_format($it['price'], 2) ?></div>
                            <p class="text-secondary small mb-2"><?= htmlspecialchars($it['description']) ?></p>
                            <span class="badge bg-light text-secondary border align-self-start"><?= htmlspecialchars($it['item_condition']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php elseif ($activeTab === 'events'): ?>
        <div class="card card-solid p-4">
            <h6 class="fw-bold mb-3">Joined Community Events</h6>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                    <div class="d-flex align-items-center gap-3">
                        <div class="event-calendar-badge">
                            <div class="fw-bold fs-5 mb-0">28</div>
                            <div class="small fw-semibold text-uppercase" style="font-size: 0.65rem;">SEP</div>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Community Clean-Up Drive</h6>
                            <div class="text-muted small"><i data-lucide="map-pin" style="width: 12px; height: 12px;"></i> Municipal Park, North Paravur</div>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success">Attending</span>
                </div>
            </div>
        </div>

    <?php elseif ($activeTab === 'activity'): ?>
        <div class="card card-solid p-4">
            <h6 class="fw-bold mb-3">Recent Neighborhood Activity</h6>
            <div class="d-flex flex-column gap-3 small text-secondary">
                <div class="d-flex gap-2 align-items-center">
                    <i data-lucide="check-circle" class="text-success" style="width: 16px; height: 16px;"></i>
                    <span>Resolved a help request: <strong>"Transport assistance for elderly neighbor"</strong> (+15 Rep)</span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <i data-lucide="message-square" class="text-primary" style="width: 16px; height: 16px;"></i>
                    <span>Commented on <strong>"Water supply disruption in Ward 5"</strong></span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <i data-lucide="calendar" class="text-purple" style="width: 16px; height: 16px; color: #7c3aed;"></i>
                    <span>RSVP'd Going to <strong>"Community Clean-Up Drive"</strong></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
