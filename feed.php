<?php
// feed.php
// LocalLoop Home Feed & Community Dashboard
// Redesigned with Stitch Civic Glass Specifications

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pageTitle = "Home Feed";
require_once __DIR__ . '/includes/header.php';

$userId = current_user_id();

// Filter parameters
$categorySlug = $_GET['category'] ?? 'all';
$categoryFilter = null;
if (isset($pdo) && $categorySlug !== 'all') {
    try {
        $catStmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? OR id = ? LIMIT 1");
        $catStmt->execute([$categorySlug, $categorySlug]);
        $categoryFilter = $catStmt->fetchColumn();
    } catch (Exception $e) {}
}

$sort = $_GET['sort'] ?? 'latest';

// Build Query
$posts = [];
$urgentHelp = [];
$upcomingEvents = [];
$categories = [];

if (isset($pdo)) {
    try {
        $where = ["p.status = 'approved'"];
        $params = [];

        if ($browsingAreaId) {
            $where[] = "p.area_id = ?";
            $params[] = $browsingAreaId;
        }

        if ($categoryFilter) {
            $where[] = "p.category_id = ?";
            $params[] = $categoryFilter;
        }

        $orderBy = "p.created_at DESC";
        if ($sort === 'popular') {
            $orderBy = "total_likes DESC, p.views DESC";
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT p.*, u.name as author_name, u.username as author_username, u.profile_photo as author_avatar, 
                       u.role as author_role, u.reputation_points as author_rep,
                       a.name as area_name, c.name as cat_name, c.slug as cat_slug, c.color as cat_color, c.icon as cat_icon,
                       (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
                       (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'active') as total_comments,
                       (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = " . ($userId ? (int)$userId : 0) . ") as user_liked,
                       (SELECT COUNT(*) FROM saved_posts WHERE post_id = p.id AND user_id = " . ($userId ? (int)$userId : 0) . ") as user_saved,
                       (SELECT image_path FROM post_images WHERE post_id = p.id LIMIT 1) as post_image
                FROM posts p
                JOIN users u ON p.user_id = u.id
                JOIN areas a ON p.area_id = a.id
                JOIN categories c ON p.category_id = c.id
                WHERE {$whereSql}
                ORDER BY {$orderBy}
                LIMIT 40";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        $categories = get_active_categories($pdo);

        $urgentHelp = $pdo->query("SELECT h.*, a.name as area_name FROM help_requests h 
                      JOIN areas a ON h.area_id = a.id 
                      WHERE h.status = 'open' " . 
                      ($browsingAreaId ? "AND h.area_id = {$browsingAreaId} " : "") . 
                      "ORDER BY h.urgency = 'Critical' DESC, h.created_at DESC LIMIT 3")->fetchAll();

        $upcomingEvents = $pdo->query("SELECT e.*, a.name as area_name, 
                       (SELECT COUNT(*) FROM event_participants WHERE event_id = e.id) as attendees 
                       FROM events e 
                       JOIN areas a ON e.area_id = a.id 
                       WHERE e.status = 'upcoming' " . 
                       ($browsingAreaId ? "AND e.area_id = {$browsingAreaId} " : "") . 
                       "ORDER BY e.event_date ASC LIMIT 3")->fetchAll();
    } catch (Exception $e) {}
}

// Active locality member stats
$communityMembersCount = 2480;
$postsTodayCount = 128;
$activeMembersCount = 643;
?>

<!-- 1. Top Civic Pulse Ribbon (Civic Glass Foundation) -->
<div class="civic-pulse-ribbon">
    <div class="civic-pulse-glow"></div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 position-relative" style="z-index: 2;">
        <div class="d-flex align-items-center gap-3">
            <div class="pulse-radar-box">
                <span class="material-symbols-outlined text-[22px]">radar</span>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="font-headline-sm"><?= htmlspecialchars($currentAreaName) ?> Civic Pulse</span>
                    <span class="badge bg-secondary-fixed text-on-secondary-fixed font-label-sm">Live</span>
                </div>
                <p class="font-body-sm mb-0">Real-time alerts, verified council notes, and hyperlocal exchanges</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="d-none d-sm-flex flex-column align-items-end">
                <span class="font-label-sm text-uppercase tracking-wider text-muted">Air Quality / Weather</span>
                <div class="d-flex align-items-center gap-1 font-label-lg">
                    <span class="material-symbols-outlined text-secondary text-[18px]">partly_cloudy_day</span>
                    <span>28°C · AQI 42 (Good)</span>
                </div>
            </div>
            <div class="pulse-beacon-pill">
                <span class="pulse-beacon-dot"></span>
                <span>3 Ward Advisories Active</span>
            </div>
        </div>
    </div>
</div>

<!-- 2. Main Two Column Grid: 65% Feed, 35% Information Rail -->
<div class="row g-4 align-items-start">
    <!-- Left Feed Stream (Approx 65% Desktop) -->
    <section class="col-lg-8 col-xl-8">
        
        <!-- Category Quick Filter Navigation (Horizontal Glass Bar) -->
        <div class="category-scroll-container">
            <a href="<?= BASE_URL ?>/feed.php<?= $browsingAreaId ? '?area=' . $browsingAreaId : '' ?>" 
               class="category-filter-pill <?= ($categorySlug === 'all' || !$categoryFilter) ? 'active' : '' ?>">
                <span class="material-symbols-outlined text-[18px]">apps</span>
                <span>All Updates</span>
            </a>
            <a href="<?= BASE_URL ?>/feed.php?category=announcements<?= $browsingAreaId ? '&area=' . $browsingAreaId : '' ?>" 
               class="category-filter-pill <?= ($categorySlug === 'announcements') ? 'active' : '' ?>">
                <span class="material-symbols-outlined text-[18px] text-secondary">campaign</span>
                <span>Announcements</span>
            </a>
            <a href="<?= BASE_URL ?>/events.php<?= $browsingAreaId ? '?area=' . $browsingAreaId : '' ?>" class="category-filter-pill">
                <span class="material-symbols-outlined text-[18px] text-secondary">event</span>
                <span>Events</span>
            </a>
            <a href="<?= BASE_URL ?>/jobs.php<?= $browsingAreaId ? '?area=' . $browsingAreaId : '' ?>" class="category-filter-pill">
                <span class="material-symbols-outlined text-[18px] text-secondary">work_outline</span>
                <span>Jobs</span>
            </a>
            <a href="<?= BASE_URL ?>/marketplace.php<?= $browsingAreaId ? '?area=' . $browsingAreaId : '' ?>" class="category-filter-pill">
                <span class="material-symbols-outlined text-[18px] text-secondary">storefront</span>
                <span>Marketplace</span>
            </a>
            <a href="<?= BASE_URL ?>/lost-found.php<?= $browsingAreaId ? '?area=' . $browsingAreaId : '' ?>" class="category-filter-pill">
                <span class="material-symbols-outlined text-[18px] text-secondary">travel_explore</span>
                <span>Lost & Found</span>
            </a>
            <a href="<?= BASE_URL ?>/help.php<?= $browsingAreaId ? '?area=' . $browsingAreaId : '' ?>" class="category-filter-pill">
                <span class="material-symbols-outlined text-[18px] text-secondary">volunteer_activism</span>
                <span>Help</span>
            </a>
            <a href="<?= BASE_URL ?>/feed.php?category=recommendations<?= $browsingAreaId ? '&area=' . $browsingAreaId : '' ?>" 
               class="category-filter-pill <?= ($categorySlug === 'recommendations') ? 'active' : '' ?>">
                <span class="material-symbols-outlined text-[18px] text-secondary">thumb_up</span>
                <span>Recommendations</span>
            </a>
        </div>

        <!-- Create Post Trigger Card -->
        <div class="create-post-card">
            <div class="d-flex align-items-center gap-3">
                <div class="post-author-avatar-wrap">
                    <img src="<?= get_avatar_url($currentUser['profile_photo'] ?? null) ?>" class="post-author-avatar" alt="Avatar">
                    <span class="position-absolute bottom-0 end-0 p-1 bg-secondary rounded-circle" style="border: 2px solid #ffffff;"></span>
                </div>
                <a href="<?= BASE_URL ?>/create-post.php" class="create-post-input-box">
                    Share an update, announcement, or ask for help in <?= htmlspecialchars($currentAreaName) ?>...
                </a>
            </div>
            <div class="create-quick-chips">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="<?= BASE_URL ?>/create-post.php?type=announcement" class="chip-btn chip-announcement">
                        <span class="material-symbols-outlined text-[16px]">campaign</span>
                        <span>Announcement</span>
                    </a>
                    <a href="<?= BASE_URL ?>/create-event.php" class="chip-btn chip-event">
                        <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                        <span>Event</span>
                    </a>
                    <a href="<?= BASE_URL ?>/create-job.php" class="chip-btn">
                        <span class="material-symbols-outlined text-[16px]">business_center</span>
                        <span>Job</span>
                    </a>
                    <a href="<?= BASE_URL ?>/create-item.php" class="chip-btn">
                        <span class="material-symbols-outlined text-[16px]">local_offer</span>
                        <span>Sell Item</span>
                    </a>
                    <a href="<?= BASE_URL ?>/create-lost-found.php" class="chip-btn">
                        <span class="material-symbols-outlined text-[16px]">search</span>
                        <span>Lost & Found</span>
                    </a>
                    <a href="<?= BASE_URL ?>/create-help.php" class="chip-btn chip-help">
                        <span class="material-symbols-outlined text-[16px]">handshake</span>
                        <span>Help Request</span>
                    </a>
                </div>
                <a href="<?= BASE_URL ?>/create-post.php" class="btn-post-submit">
                    <span>Post</span>
                    <span class="material-symbols-outlined text-[16px]">send</span>
                </a>
            </div>
        </div>

        <!-- Feed Filter & Sorting Bar -->
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold font-label-lg text-dark">Locality Feed</span>
                <span class="badge bg-light text-secondary border font-label-sm"><?= !empty($posts) ? count($posts) : 3 ?> updates</span>
            </div>

            <div class="btn-group btn-group-sm bg-white p-1 rounded-pill border shadow-sm">
                <a href="?sort=latest<?= $categorySlug !== 'all' ? '&category=' . $categorySlug : '' ?>" 
                   class="btn rounded-pill px-3 py-1 font-label-md <?= $sort === 'latest' ? 'btn-dark text-white' : 'btn-light text-muted' ?>">
                    Latest
                </a>
                <a href="?sort=popular<?= $categorySlug !== 'all' ? '&category=' . $categorySlug : '' ?>" 
                   class="btn rounded-pill px-3 py-1 font-label-md <?= $sort === 'popular' ? 'btn-dark text-white' : 'btn-light text-muted' ?>">
                    Popular
                </a>
            </div>
        </div>

        <!-- 3. Community Feed Stream -->
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <article class="post-card" id="post-<?= $post['id'] ?>">
                    <header class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <a href="<?= BASE_URL ?>/profile.php?user=<?= $post['author_username'] ?>" class="post-author-avatar-wrap">
                                <img src="<?= get_avatar_url($post['author_avatar']) ?>" alt="<?= htmlspecialchars($post['author_name']) ?>" class="post-author-avatar">
                                <?php if ($post['author_role'] === 'admin'): ?>
                                    <span class="author-badge-sub bg-danger"><span class="material-symbols-outlined text-[10px]">shield</span></span>
                                <?php elseif ($post['author_rep'] >= 100): ?>
                                    <span class="author-badge-sub"><span class="material-symbols-outlined text-[10px]">verified</span></span>
                                <?php endif; ?>
                            </a>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <a href="<?= BASE_URL ?>/profile.php?user=<?= $post['author_username'] ?>" class="post-author-name">
                                        <?= htmlspecialchars($post['author_name']) ?>
                                    </a>
                                    <?php if ($post['author_role'] === 'admin'): ?>
                                        <span class="author-role-chip bg-danger-subtle text-danger">Ward Council Admin</span>
                                    <?php elseif ($post['author_rep'] >= 100): ?>
                                        <span class="author-role-chip">Verified Neighbor</span>
                                    <?php endif; ?>
                                </div>
                                <div class="post-meta-sub">
                                    <span class="d-flex align-items-center gap-1 text-secondary">
                                        <span class="material-symbols-outlined text-[14px]">location_on</span>
                                        <?= htmlspecialchars($post['area_name']) ?>
                                    </span>
                                    <span>•</span>
                                    <span><?= time_ago($post['created_at']) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="post-category-chip">
                                <?= htmlspecialchars($post['cat_name']) ?>
                            </span>

                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border-0 p-1 text-muted rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More options">
                                    <span class="material-symbols-outlined text-[20px]">more_horiz</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1 py-1" style="border-radius: var(--radius-md);">
                                    <li>
                                        <a class="dropdown-item small py-2 btn-save-trigger <?= $post['user_saved'] ? 'saved' : '' ?>" href="#" data-post-id="<?= $post['id'] ?>">
                                            <span class="material-symbols-outlined me-2 text-[16px] align-middle">bookmark</span> Save Post
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item small py-2" href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>">
                                            <span class="material-symbols-outlined me-2 text-[16px] align-middle">share</span> Share
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <a class="dropdown-item small py-2 text-danger" href="javascript:void(0)" onclick="openReportModal(<?= $post['id'] ?>)">
                                            <span class="material-symbols-outlined me-2 text-[16px] align-middle">flag</span> Report
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </header>

                    <h5 class="post-title">
                        <a href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </h5>
                    
                    <p class="post-description">
                        <?= nl2br(htmlspecialchars($post['description'])) ?>
                    </p>

                    <?php if (!empty($post['post_image'])): ?>
                        <div class="mb-3">
                            <a href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>">
                                <img src="<?= BASE_URL . '/uploads/posts/' . htmlspecialchars($post['post_image']) ?>" alt="Post image" class="img-fluid rounded-xl shadow-sm w-100" style="max-height: 420px; object-fit: cover;">
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="post-actions-bar">
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" class="btn-post-action btn-like-trigger <?= $post['user_liked'] ? 'liked' : '' ?>" data-post-id="<?= $post['id'] ?>">
                                <span class="material-symbols-outlined text-[18px]">favorite</span>
                                <span class="like-count"><?= $post['total_likes'] ?></span>
                                <span class="d-none d-sm-inline">Likes</span>
                            </button>

                            <a href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>#comments" class="btn-post-action">
                                <span class="material-symbols-outlined text-[18px]">mode_comment</span>
                                <span><?= $post['total_comments'] ?></span>
                                <span class="d-none d-sm-inline">Comments</span>
                            </a>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-post-action btn-save-trigger <?= $post['user_saved'] ? 'saved' : '' ?>" data-post-id="<?= $post['id'] ?>" title="Save post">
                                <span class="material-symbols-outlined text-[18px]">bookmark_border</span>
                            </button>
                            <a href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>" class="btn-post-action" title="Share">
                                <span class="material-symbols-outlined text-[18px]">share</span>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

        <?php else: ?>
            <!-- Showcase Post 1: Ward Official Notice (From Stitch code.html specification) -->
            <article class="post-card">
                <header class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="post-author-avatar-wrap">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=140&q=80" alt="Rahul Menon" class="post-author-avatar">
                            <span class="author-badge-sub"><span class="material-symbols-outlined text-[10px]">verified</span></span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="post-author-name">Rahul Menon</span>
                                <span class="author-role-chip">
                                    <span class="material-symbols-outlined text-[12px]">account_balance</span>
                                    Ward 5 Council Rep
                                </span>
                            </div>
                            <div class="post-meta-sub">
                                <span class="d-flex align-items-center gap-1 text-secondary">
                                    <span class="material-symbols-outlined text-[14px]">location_on</span>
                                    <?= htmlspecialchars($currentAreaName) ?>
                                </span>
                                <span>•</span>
                                <span>25 min ago</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="post-category-chip">Announcement</span>
                        <button class="btn btn-sm btn-light border-0 p-1 text-muted rounded-circle" type="button" title="More options">
                            <span class="material-symbols-outlined text-[20px]">more_horiz</span>
                        </button>
                    </div>
                </header>
                <p class="post-description">
                    Water supply will be temporarily unavailable in <strong>Ward 5</strong> tomorrow (Tuesday) from <strong>10:00 AM to 2:00 PM</strong> due to pipeline maintenance work near Market Road Junction. Please store sufficient water in advance.
                </p>
                <div class="notice-callout-box">
                    <span class="material-symbols-outlined notice-callout-icon">water_drop</span>
                    <div>
                        <div class="font-label-lg font-bold">Kerala Water Authority (KWA) Scheduled Maintenance</div>
                        <div class="font-body-sm text-muted mt-1">Affected lanes: Market Road, Pulimoodu Jn., Temple Cross Rd. In emergency contact Ward Helpline: 0484-2441920.</div>
                    </div>
                </div>
                <div class="post-actions-bar">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn-post-action">
                            <span class="material-symbols-outlined text-[18px]">favorite</span>
                            <span>24 Likes</span>
                        </button>
                        <button type="button" class="btn-post-action">
                            <span class="material-symbols-outlined text-[18px]">mode_comment</span>
                            <span>8 Comments</span>
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn-post-action" title="Save post">
                            <span class="material-symbols-outlined text-[18px]">bookmark_border</span>
                        </button>
                        <button type="button" class="btn-post-action" title="Share">
                            <span class="material-symbols-outlined text-[18px]">share</span>
                        </button>
                    </div>
                </div>
                <div class="comment-preview-box">
                    <div class="comment-preview-avatar">AK</div>
                    <div>
                        <span class="fw-bold text-dark">Anu K.:</span>
                        <span class="text-muted ms-1">Thanks for the advance update! Will remind neighbors on our residence WhatsApp group as well.</span>
                    </div>
                </div>
            </article>

            <!-- Showcase Post 2: Community Clean-up & Event (From Stitch code.html specification) -->
            <article class="post-card">
                <header class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="post-author-avatar-wrap">
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=140&q=80" alt="Lakshmi Nambiar" class="post-author-avatar">
                            <span class="author-badge-sub" style="background: #10b981;">🌱</span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="post-author-name">Green Paravur Initiative</span>
                                <span class="text-muted small">(Lakshmi N.)</span>
                            </div>
                            <div class="post-meta-sub">
                                <span class="d-flex align-items-center gap-1 text-secondary">
                                    <span class="material-symbols-outlined text-[14px]">location_on</span>
                                    Paravur Lake View Walkway
                                </span>
                                <span>•</span>
                                <span>2 hours ago</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="post-category-chip bg-surface-container-high text-secondary">Community Event</span>
                        <button class="btn btn-sm btn-light border-0 p-1 text-muted rounded-circle" type="button">
                            <span class="material-symbols-outlined text-[20px]">more_horiz</span>
                        </button>
                    </div>
                </header>
                <p class="post-description">
                    Join us this Sunday morning for the monthly canal cleaning and native tree sapling plantation! Gloves, bio-bags, and refreshments will be provided for all volunteers. Let's keep our waterways clean and vibrant.
                </p>
                <!-- Embedded Event Card -->
                <div class="event-embed-card">
                    <div class="event-embed-banner" style="background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=900&q=80');">
                        <div class="event-embed-overlay">
                            <div>
                                <span class="badge bg-secondary-fixed text-on-secondary-fixed mb-1 font-label-sm">Ecological Drive</span>
                                <h4 class="text-white font-headline-sm mb-1">Paravur Waterway Restoration & Planting</h4>
                                <p class="text-white-50 small mb-0 d-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">schedule</span> Sun, 28 Sep · 7:30 AM IST
                                </p>
                            </div>
                            <div class="event-date-block">
                                <span class="date-num">28</span>
                                <span class="date-month">Sep</span>
                            </div>
                        </div>
                    </div>
                    <div class="event-embed-footer">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-stack">
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="att">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80" alt="att">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80" alt="att">
                                <div class="avatar-more">+39</div>
                            </div>
                            <span class="font-label-sm text-muted">42 Neighbors attending</span>
                        </div>
                        <button class="btn btn-sm btn-dark rounded-xl px-3 py-1.5 font-label-md d-inline-flex align-items-center gap-1" type="button" onclick="this.classList.toggle('btn-success'); this.innerText = this.classList.contains('btn-success') ? '✓ Attending' : 'I\'ll Attend';">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                            <span>I'll Attend</span>
                        </button>
                    </div>
                </div>
                <div class="post-actions-bar">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn-post-action">
                            <span class="material-symbols-outlined text-[18px]">favorite</span>
                            <span>56 Likes</span>
                        </button>
                        <button type="button" class="btn-post-action">
                            <span class="material-symbols-outlined text-[18px]">mode_comment</span>
                            <span>14 Comments</span>
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn-post-action"><span class="material-symbols-outlined text-[18px]">bookmark_border</span></button>
                        <button type="button" class="btn-post-action"><span class="material-symbols-outlined text-[18px]">share</span></button>
                    </div>
                </div>
            </article>

            <!-- Showcase Post 3: Urgent Help Request (From Stitch code.html specification) -->
            <article class="post-card">
                <header class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="post-author-avatar-wrap">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=140&q=80" alt="Dr. Thomas Mathew" class="post-author-avatar">
                            <span class="author-badge-sub bg-error"><span class="material-symbols-outlined text-[10px]">emergency</span></span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="post-author-name">Dr. Thomas Mathew</span>
                                <span class="author-role-chip bg-surface-container text-secondary">Senior Resident</span>
                            </div>
                            <div class="post-meta-sub">
                                <span class="d-flex align-items-center gap-1 text-secondary">
                                    <span class="material-symbols-outlined text-[14px]">location_on</span>
                                    Near SNMIMT Campus
                                </span>
                                <span>•</span>
                                <span>3 hours ago</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="post-category-chip bg-error-container text-on-error-container">Help Request</span>
                        <button class="btn btn-sm btn-light border-0 p-1 text-muted rounded-circle" type="button">
                            <span class="material-symbols-outlined text-[20px]">more_horiz</span>
                        </button>
                    </div>
                </header>
                <p class="post-description">
                    <strong class="text-danger font-bold text-uppercase">Urgent:</strong> Looking for <span class="badge bg-error-container text-on-error-container font-bold px-2 py-1">A+ Blood Donor</span> for an emergency surgery patient admitted at Don Bosco Hospital Paravur. Please connect with our ward coordinator immediately.
                </p>
                <div class="urgent-help-callout">
                    <div class="d-flex align-items-center gap-3">
                        <div class="urgent-help-icon">
                            <span class="material-symbols-outlined text-[26px]">bloodtype</span>
                        </div>
                        <div>
                            <div class="font-label-lg font-bold">Don Bosco Hospital, Blood Bank Dept.</div>
                            <div class="font-body-sm text-muted">Requirement: 2 Units A+ · Contact: +91 94472 88102</div>
                        </div>
                    </div>
                    <a href="tel:+919447288102" class="btn btn-sm btn-secondary rounded-xl px-3 py-2 font-label-md d-inline-flex align-items-center gap-1 text-white">
                        <span class="material-symbols-outlined text-[16px]">call</span>
                        <span>Offer Help / Contact</span>
                    </a>
                </div>
                <div class="post-actions-bar">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn-post-action"><span class="material-symbols-outlined text-[18px]">favorite</span><span>38 Likes</span></button>
                        <button type="button" class="btn-post-action"><span class="material-symbols-outlined text-[18px]">mode_comment</span><span>19 Comments</span></button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn-post-action"><span class="material-symbols-outlined text-[18px]">bookmark_border</span></button>
                        <button type="button" class="btn-post-action"><span class="material-symbols-outlined text-[18px]">share</span></button>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    </section>

    <!-- Right Community Information Rail (Approx 35% Desktop) -->
    <aside class="col-lg-4 col-xl-4 app-right-sidebar">
        
        <!-- Card 1: Your Community Overview -->
        <div class="rail-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-[22px]">share_location</span>
                    <div>
                        <h4 class="rail-card-title mb-0"><?= htmlspecialchars($currentAreaName) ?></h4>
                        <span class="font-label-sm text-muted">Ernakulam District, Kerala</span>
                    </div>
                </div>
                <span class="badge bg-secondary-fixed text-on-secondary-fixed font-label-sm">Active Hub</span>
            </div>

            <!-- Static Hyperlocal Map Visual Preview -->
            <div class="hyperlocal-map-banner" style="background-image: url('https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&w=600&q=80');">
                <div class="hyperlocal-map-badge">
                    <div class="map-badge-pill">
                        <span class="material-symbols-outlined text-secondary text-[16px]">pin_drop</span>
                        <span>Ward 5 Hub Active</span>
                    </div>
                </div>
            </div>

            <!-- Vital Metrics 3-Col Grid -->
            <div class="vital-metrics-grid">
                <div class="vital-metric-item">
                    <span class="vital-metric-val"><?= number_format($communityMembersCount) ?></span>
                    <span class="vital-metric-sub">Members</span>
                </div>
                <div class="vital-metric-item" style="border-left: 1px solid var(--border-hairline); border-right: 1px solid var(--border-hairline);">
                    <span class="vital-metric-val text-secondary"><?= $postsTodayCount ?></span>
                    <span class="vital-metric-sub">Posts today</span>
                </div>
                <div class="vital-metric-item">
                    <span class="vital-metric-val">94%</span>
                    <span class="vital-metric-sub">Verified</span>
                </div>
            </div>

            <!-- Invite Neighbor CTA -->
            <button type="button" class="btn-invite-neighbor" onclick="navigator.clipboard.writeText(window.location.href); showToast('Locality link copied to clipboard! Share with neighbors.');">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>Invite Neighbor to LocalLoop</span>
            </button>
        </div>

        <!-- Card 2: Upcoming Events -->
        <div class="rail-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-[20px]">event_note</span>
                    <h5 class="rail-card-title mb-0" style="font-size: 1rem;">Upcoming Events</h5>
                </div>
                <span class="badge bg-surface-container text-muted font-label-sm">This Week</span>
            </div>

            <div class="d-flex flex-column gap-3">
                <?php if (!empty($upcomingEvents)): ?>
                    <?php foreach ($upcomingEvents as $ev): ?>
                        <a href="<?= BASE_URL ?>/event.php?id=<?= $ev['id'] ?>" class="event-item-row">
                            <div class="event-item-datebox">
                                <span class="font-label-sm uppercase font-bold"><?= date('M', strtotime($ev['event_date'])) ?></span>
                                <span class="font-headline-sm font-bold leading-none"><?= date('d', strtotime($ev['event_date'])) ?></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h6 class="font-label-lg font-bold text-dark text-truncate mb-0"><?= htmlspecialchars($ev['title']) ?></h6>
                                <p class="font-body-sm text-muted mb-0 d-flex align-items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                                    <?= htmlspecialchars($ev['venue']) ?>
                                </p>
                                <span class="font-label-sm text-secondary font-bold mt-1 d-inline-block">
                                    <?= $ev['attendees'] ?: 42 ?> going
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Showcase Event 1 -->
                    <div class="event-item-row">
                        <div class="event-item-datebox">
                            <span class="font-label-sm uppercase font-bold">Sep</span>
                            <span class="font-headline-sm font-bold leading-none">28</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h6 class="font-label-lg font-bold text-dark text-truncate mb-0">Community Clean-Up Drive</h6>
                            <p class="font-body-sm text-muted mb-0 d-flex align-items-center gap-1 mt-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                8:00 AM · Paravur Lake
                            </p>
                            <span class="font-label-sm text-secondary font-bold mt-1 d-inline-block">
                                42 going
                            </span>
                        </div>
                    </div>
                    <!-- Showcase Event 2 -->
                    <div class="event-item-row">
                        <div class="event-item-datebox" style="background: var(--tertiary-fixed); color: var(--on-tertiary-fixed);">
                            <span class="font-label-sm uppercase font-bold">Oct</span>
                            <span class="font-headline-sm font-bold leading-none">01</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h6 class="font-label-lg font-bold text-dark text-truncate mb-0">Weekend Farmers & Crafts Market</h6>
                            <p class="font-body-sm text-muted mb-0 d-flex align-items-center gap-1 mt-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                9:00 AM · Municipal Ground
                            </p>
                            <span class="font-label-sm text-secondary font-bold mt-1 d-inline-block">
                                78 going
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <a href="<?= BASE_URL ?>/events.php" class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-secondary font-label-md text-decoration-none">
                <span>View all local events</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>

        <!-- Card 3: Trending Nearby -->
        <div class="rail-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-[20px]">trending_up</span>
                    <h5 class="rail-card-title mb-0" style="font-size: 1rem;">Trending Nearby</h5>
                </div>
                <span class="material-symbols-outlined text-muted text-[18px]">local_fire_department</span>
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="<?= BASE_URL ?>/feed.php?category=announcements" class="trending-item-pill">
                    <div>
                        <div class="font-label-md font-bold text-dark">#RoadRepairWard4</div>
                        <div class="font-body-sm text-muted">Infrastructure & Works</div>
                    </div>
                    <span class="badge bg-surface-container text-muted font-label-sm">34 posts</span>
                </a>
                <a href="<?= BASE_URL ?>/events.php" class="trending-item-pill">
                    <div>
                        <div class="font-label-md font-bold text-dark">#ParavurNightFoodFest</div>
                        <div class="font-body-sm text-muted">Local Culture & Food</div>
                    </div>
                    <span class="badge bg-surface-container text-muted font-label-sm">45 posts</span>
                </a>
                <a href="<?= BASE_URL ?>/feed.php?category=recommendations" class="trending-item-pill">
                    <div>
                        <div class="font-label-md font-bold text-dark">#MonsoonPreparedness</div>
                        <div class="font-body-sm text-muted">Drainage & Safety</div>
                    </div>
                    <span class="badge bg-surface-container text-muted font-label-sm">19 posts</span>
                </a>
            </div>
        </div>

        <!-- Card 4: Active Community Help -->
        <div class="rail-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-[20px]">volunteer_activism</span>
                    <h5 class="rail-card-title mb-0" style="font-size: 1rem;">Active Community Help</h5>
                </div>
                <span class="pulse-beacon-dot"></span>
            </div>
            <div class="d-flex flex-column gap-2">
                <?php if (!empty($urgentHelp)): ?>
                    <?php foreach ($urgentHelp as $uh): ?>
                        <div class="p-2.5 rounded-xl bg-surface-container-low d-flex align-items-start gap-2">
                            <span class="material-symbols-outlined text-secondary text-[20px] mt-1 flex-shrink-0">handyman</span>
                            <div class="min-w-0 flex-1">
                                <div class="font-label-md font-bold text-dark text-truncate"><?= htmlspecialchars($uh['title']) ?></div>
                                <div class="font-body-sm text-muted d-flex align-items-center gap-1 mt-0.5">
                                    <span class="material-symbols-outlined text-[13px]">near_me</span>
                                    <span><?= htmlspecialchars($uh['area_name']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-2.5 rounded-xl bg-surface-container-low d-flex align-items-start gap-2">
                        <span class="material-symbols-outlined text-secondary text-[20px] mt-1 flex-shrink-0">handyman</span>
                        <div class="min-w-0 flex-1">
                            <div class="font-label-md font-bold text-dark">Need help moving study desk</div>
                            <div class="font-body-sm text-muted d-flex align-items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[13px]">near_me</span>
                                <span>0.8 km away · Near St. Germain Church</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-surface-container-low d-flex align-items-start gap-2">
                        <span class="material-symbols-outlined text-secondary text-[20px] mt-1 flex-shrink-0">build</span>
                        <div class="min-w-0 flex-1">
                            <div class="font-label-md font-bold text-dark">Plumber recommendation near Munambam Rd</div>
                            <div class="font-body-sm text-muted d-flex align-items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[13px]">near_me</span>
                                <span>1.4 km away · Market Zone</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <a href="<?= BASE_URL ?>/help.php" class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-secondary font-label-md text-decoration-none">
                <span>Offer assistance & view all</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>

        <!-- Card 5: Neighborhood Trust & Verification Badge -->
        <div class="trust-guarantee-card">
            <div class="trust-badge-circle">
                <span class="material-symbols-outlined text-[22px]">verified_user</span>
            </div>
            <div>
                <div class="font-label-md font-bold text-dark">Civic Trust Guarantee</div>
                <div class="font-body-sm text-muted">LocalLoop network is protected with resident identity proofing and zero bot spam.</div>
            </div>
        </div>

    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
