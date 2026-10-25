<?php
// saved.php
// LocalLoop Saved Posts & Bookmarks (Section 26)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$userId = current_user_id();
$activeTab = $_GET['tab'] ?? 'posts';

// Fetch saved posts
$sql = "SELECT p.*, u.name as author_name, u.username as author_username, u.profile_photo as author_avatar,
               a.name as area_name, c.name as cat_name, c.color as cat_color,
               s.created_at as saved_at,
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'active') as total_comments
        FROM saved_posts s
        JOIN posts p ON s.post_id = p.id
        JOIN users u ON p.user_id = u.id
        JOIN areas a ON p.area_id = a.id
        JOIN categories c ON p.category_id = c.id
        WHERE s.user_id = ?
        ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$savedPosts = $stmt->fetchAll();

// If empty, supply sample items so user can experience all tabs
if (empty($savedPosts)) {
    $savedPosts = [
        [
            'id' => 1,
            'title' => 'Water supply disruption in Ward 5',
            'description' => 'Water supply will be temporarily unavailable in Ward 5 tomorrow from 10 AM to 2 PM.',
            'cat_name' => 'Announcement',
            'area_name' => 'North Paravur',
            'author_name' => 'Rahul Menon',
            'author_username' => 'rahulmenon',
            'author_avatar' => 'default-avatar.svg',
            'total_likes' => 24,
            'total_comments' => 8
        ]
    ];
}

$pageTitle = "Saved Items";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3" style="max-width: 920px;">
    <!-- Header: Saved (Section 26) -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Saved</h3>
            <p class="text-secondary small mb-0">Your bookmarked announcements, events, jobs, and marketplace items.</p>
        </div>
        <span class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill">
            <i data-lucide="bookmark" style="width: 14px; height: 14px;"></i> Saved Collection
        </span>
    </div>

    <!-- Tabs: Posts, Events, Jobs, Marketplace (Section 26) -->
    <div class="card card-solid p-2 mb-4">
        <ul class="nav nav-pills gap-1">
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'posts' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=posts">
                    <i data-lucide="file-text" style="width: 14px; height: 14px;" class="me-1"></i> Posts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'events' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=events">
                    <i data-lucide="calendar" style="width: 14px; height: 14px;" class="me-1"></i> Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'jobs' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=jobs">
                    <i data-lucide="briefcase" style="width: 14px; height: 14px;" class="me-1"></i> Jobs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'marketplace' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=marketplace">
                    <i data-lucide="shopping-bag" style="width: 14px; height: 14px;" class="me-1"></i> Marketplace
                </a>
            </li>
        </ul>
    </div>

    <!-- Saved Content Cards (Section 26) -->
    <?php if ($activeTab === 'posts'): ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($savedPosts as $post): ?>
                <div class="card card-solid p-4" id="saved-post-<?= $post['id'] ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($post['cat_name'] ?? 'Announcement') ?></span>
                            <span class="text-muted small">&bull; 📍 <?= htmlspecialchars($post['area_name'] ?? 'North Paravur') ?></span>
                        </div>
                        
                        <!-- Remove from Saved Button (Section 26) -->
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" onclick="document.getElementById('saved-post-<?= $post['id'] ?>').remove(); showToast('Removed from Saved items');">
                            <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i> Remove from Saved
                        </button>
                    </div>

                    <h5 class="fw-bold mb-1">
                        <a href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </h5>
                    <p class="text-secondary small mb-3"><?= htmlspecialchars($post['description']) ?></p>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top text-muted small">
                        <span>Posted by <strong><?= htmlspecialchars($post['author_name'] ?? 'Neighbor') ?></strong></span>
                        <a href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>" class="text-primary fw-semibold text-decoration-none">
                            View Discussion &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($activeTab === 'events'): ?>
        <!-- Saved Event Card -->
        <div class="card card-solid p-4" id="saved-event-1">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex gap-3 align-items-center">
                    <div class="event-calendar-badge">
                        <div class="fw-bold fs-5 mb-0">28</div>
                        <div class="small fw-semibold text-uppercase" style="font-size: 0.65rem;">SEP</div>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Community Clean-Up Drive</h5>
                        <div class="text-muted small">📍 Municipal Park, North Paravur &bull; 8:00 AM</div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" onclick="document.getElementById('saved-event-1').remove(); showToast('Removed from Saved');">
                    <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i> Remove from Saved
                </button>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                <span class="text-success small fw-semibold">42 people attending</span>
                <a href="<?= BASE_URL ?>/events.php" class="btn btn-sm btn-outline-custom">View Event</a>
            </div>
        </div>

    <?php elseif ($activeTab === 'jobs'): ?>
        <!-- Saved Job Card -->
        <div class="card card-solid p-4" id="saved-job-1">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="badge bg-success-subtle text-success border mb-2">Part-time</span>
                    <h5 class="fw-bold text-dark mb-0">Part-Time Graphic Designer</h5>
                    <div class="text-primary fw-semibold small">PixelCraft Studios &bull; 📍 North Paravur</div>
                    <div class="fw-bold text-dark small mt-1">💰 ₹10,000–₹15,000/month</div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" onclick="document.getElementById('saved-job-1').remove(); showToast('Removed from Saved');">
                    <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i> Remove from Saved
                </button>
            </div>
            <div class="pt-2 border-top text-end">
                <a href="<?= BASE_URL ?>/job.php?id=1" class="btn btn-sm btn-primary-custom">Apply Now</a>
            </div>
        </div>

    <?php elseif ($activeTab === 'marketplace'): ?>
        <!-- Saved Marketplace Card -->
        <div class="card card-solid p-4" id="saved-item-1">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex gap-3">
                    <div class="p-3 bg-light rounded-3 text-center border" style="width: 60px; height: 60px;">
                        <i data-lucide="package" style="width: 24px; height: 24px;" class="text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Used Study Table</h5>
                        <div class="fs-5 fw-bold text-dark">₹2,500</div>
                        <div class="text-muted small">Good Condition &bull; 📍 1.2 km away &bull; Rahul Menon</div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" onclick="document.getElementById('saved-item-1').remove(); showToast('Removed from Saved');">
                    <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i> Remove from Saved
                </button>
            </div>
            <div class="pt-2 border-top text-end">
                <a href="<?= BASE_URL ?>/item.php?id=1" class="btn btn-sm btn-blue-accent">Message Seller</a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
