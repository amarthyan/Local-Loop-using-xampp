<?php
// index.php
// LocalLoop — Hyperlocal Kerala Neighborhood Platform (Public Community Noticeboard)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If logged in, redirect to active feed
if (is_logged_in()) {
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

$pageTitle = "Neighborhood Noticeboard & Civic Hub";
require_once __DIR__ . '/includes/header.php';

// 1. Fetch Real Active Localities from Database safely
$allAreas = [];
$recentPosts = [];
$urgentHelpRequests = [];
$upcomingEvents = [];
$totalPostsCount = 5;
$totalAreasCount = 7;
$totalJobsCount = 3;
$totalMarketCount = 3;

if (isset($pdo)) {
    try {
        $allAreas = get_active_areas($pdo);
        $recentPostsStmt = $pdo->query("SELECT p.*, u.name as author_name, u.username as author_username, 
                                               u.profile_photo as author_avatar, u.role as author_role, u.reputation_points as author_rep,
                                               a.name as area_name, c.name as cat_name, c.color as cat_color, c.slug as cat_slug,
                                               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
                                               (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'active') as total_comments
                                        FROM posts p
                                        JOIN users u ON p.user_id = u.id
                                        JOIN areas a ON p.area_id = a.id
                                        JOIN categories c ON p.category_id = c.id
                                        WHERE p.status = 'approved'
                                        ORDER BY p.created_at DESC
                                        LIMIT 6");
        $recentPosts = $recentPostsStmt ? $recentPostsStmt->fetchAll() : [];

        $urgentHelpStmt = $pdo->query("SELECT h.*, a.name as area_name, u.name as author_name,
                                              (SELECT COUNT(*) FROM help_offers WHERE request_id = h.id) as offer_count
                                       FROM help_requests h
                                       JOIN areas a ON h.area_id = a.id
                                       JOIN users u ON h.user_id = u.id
                                       WHERE h.status = 'open'
                                       ORDER BY h.urgency = 'Critical' DESC, h.created_at DESC
                                       LIMIT 2");
        $urgentHelpRequests = $urgentHelpStmt ? $urgentHelpStmt->fetchAll() : [];

        $eventsStmt = $pdo->query("SELECT e.*, a.name as area_name,
                                          (SELECT COUNT(*) FROM event_participants WHERE event_id = e.id) as attendees
                                   FROM events e
                                   JOIN areas a ON e.area_id = a.id
                                   WHERE e.status = 'upcoming'
                                   ORDER BY e.event_date ASC
                                   LIMIT 3");
        $upcomingEvents = $eventsStmt ? $eventsStmt->fetchAll() : [];

        $totalPostsCount = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'approved'")->fetchColumn() ?: 5;
        $totalAreasCount = count($allAreas) ?: 7;
        $totalJobsCount = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'open'")->fetchColumn() ?: 3;
        $totalMarketCount = $pdo->query("SELECT COUNT(*) FROM marketplace_items WHERE status = 'available'")->fetchColumn() ?: 3;
    } catch (Exception $e) {}
}
?>

<div class="container-fluid p-0">

    <!-- 1. Civic Glass Welcome Banner & Spotlight -->
    <div class="civic-pulse-ribbon p-4 p-lg-5 mb-4" style="background: linear-gradient(135deg, var(--surface-container-low) 0%, rgba(204, 229, 255, 0.4) 100%);">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-full bg-white shadow-sm mb-3">
                    <span class="badge bg-secondary-fixed text-on-secondary-fixed font-label-sm">LIVE</span>
                    <span class="fw-bold font-label-md text-dark">Civic Glass • Hyperlocal Community Platform</span>
                </div>

                <h1 class="display-5 fw-extrabold text-dark mb-3" style="line-height: 1.15; letter-spacing: -0.03em;">
                    Your Community. <br>
                    <span class="text-secondary">One Unified Hyperlocal Platform.</span>
                </h1>

                <p class="font-body-md text-muted mb-4" style="line-height: 1.7; max-width: 600px;">
                    LocalLoop connects residents, local businesses, volunteers, and municipal ward coordinators across <strong>North Paravur, Kodungallur, Kakkanad, Aluva</strong>, and neighboring areas. Real town news, drinking water alerts, mutual aid, and pre-owned bazaar with zero algorithm noise.
                </p>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <a href="<?= BASE_URL ?>/feed.php" class="btn btn-dark rounded-xl py-2.5 px-4 font-label-md text-white shadow-sm d-inline-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">apps</span>
                        <span>Browse Community Noticeboard</span>
                    </a>
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-secondary rounded-xl py-2.5 px-4 font-label-md text-white shadow-sm d-inline-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                        <span>Join Your Locality (Free)</span>
                    </a>
                    <button type="button" class="btn-emergency-pill py-2 px-3 border-0" data-bs-toggle="modal" data-bs-target="#emergencyModal">
                        <span class="material-symbols-outlined text-[16px]">call</span>
                        <span>Civic Helplines (112, 101, 1912)</span>
                    </button>
                </div>
            </div>

            <!-- Right Spotlight: Civic Glass Pinned Notice Card -->
            <div class="col-lg-5">
                <div class="rail-card p-4 position-relative">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary text-[24px]">push_pin</span>
                            <div>
                                <h6 class="fw-bold text-dark mb-0 font-label-lg">Today's Ward Bulletins</h6>
                                <small class="text-muted font-label-sm">Verified Civic Notices</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary-fixed text-on-secondary-fixed font-label-sm">Live Broadcast</span>
                    </div>

                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="p-2.5 rounded-xl bg-surface-container-low border-start border-3 border-info">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-bold text-dark font-label-md">💧 KWA Water Supply Notice</span>
                                <span class="text-muted font-label-sm">Ward 5</span>
                            </div>
                            <p class="font-body-sm text-muted mb-0 mt-1">
                                Pipeline maintenance near Market Road tomorrow 10:00 AM - 2:00 PM. Store water beforehand.
                            </p>
                        </div>

                        <div class="p-2.5 rounded-xl bg-surface-container-low border-start border-3 border-warning">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-bold text-dark font-label-md">⚡ KSEB Scheduled Line Works</span>
                                <span class="text-muted font-label-sm">Dial 1912</span>
                            </div>
                            <p class="font-body-sm text-muted mb-0 mt-1">
                                11kV line clearance along Pulimoodu Jn. Temporary shutdown 9 AM to 1 PM.
                            </p>
                        </div>

                        <div class="p-2.5 rounded-xl bg-surface-container-low border-start border-3 border-danger">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-bold text-dark font-label-md">🩸 Urgent Blood Donor Appeal</span>
                                <span class="badge bg-error-container text-on-error-container font-label-sm">Critical</span>
                            </div>
                            <p class="font-body-sm text-muted mb-0 mt-1">
                                A+ve donor needed at Don Bosco Hospital Paravur. Emergency surgery.
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 text-center border-top">
                        <a href="<?= BASE_URL ?>/feed.php" class="text-decoration-none fw-bold font-label-md text-secondary d-inline-flex align-items-center gap-1">
                            <span>View All <?= (int)$totalPostsCount ?> Neighborhood Notices</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Live Civic Schedule Ticker (Informative Bulletins) -->
    <div class="locality-ticker-strip mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold">SCHEDULE</span>
                <span class="fw-bold text-dark small">Haritha Karma Sena Dry-Waste Collection:</span>
                <span class="text-secondary small">Wards 4, 5, 6 & 8 plastic collection this Saturday 8:30 AM to 12:00 PM</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">KSEB Central Breakdown:</span>
                <a href="tel:1912" class="badge bg-warning text-dark text-decoration-none fw-bold">Dial 1912</a>
                <span class="text-muted small">|</span>
                <a href="tel:1916" class="badge bg-info text-white text-decoration-none fw-bold">KWA 1916</a>
            </div>
        </div>
    </div>

    <!-- 3. Urgent Neighbor Mutual Aid Spotlight (Direct From DB) -->
    <?php if (!empty($urgentHelpRequests)): ?>
        <div class="urgent-help-banner mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">🤝</span>
                    <div>
                        <h5 class="fw-bold text-danger mb-0">Urgent Neighborhood Mutual Aid</h5>
                        <small class="text-secondary">Community members nearby who need emergency help or equipment right now</small>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/help.php" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">
                    View Help Desk &rarr;
                </a>
            </div>

            <div class="row g-3">
                <?php foreach ($urgentHelpRequests as $help): ?>
                    <div class="col-md-6">
                        <div class="card p-3 rounded-3 border bg-white h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-danger text-white small fw-bold">
                                    <i data-lucide="alert-circle" style="width: 12px; height: 12px;" class="me-1"></i>
                                    <?= htmlspecialchars($help['urgency']) ?> Urgency
                                </span>
                                <span class="badge bg-light text-muted border small">
                                    📍 <?= htmlspecialchars($help['area_name']) ?>
                                </span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($help['title']) ?></h6>
                            <p class="text-secondary small mb-3 text-truncate-2" style="line-height: 1.5;">
                                <?= htmlspecialchars(mb_strimwidth($help['description'], 0, 140, '...')) ?>
                            </p>
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
                                <small class="text-muted">By <?= htmlspecialchars($help['author_name']) ?> &bull; <?= time_ago($help['created_at']) ?></small>
                                <a href="<?= BASE_URL ?>/help-request.php?id=<?= $help['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3">
                                    Offer Help
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 4. Interactive Locality Switcher (7 Real Kerala Localities) -->
    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <span class="text-danger">📍</span> Active Localities Across Kerala
                </h4>
                <p class="text-secondary small mb-0">Select your town to instantly view hyper-local announcements, water schedules, and local buy & sell listings.</p>
            </div>
            <a href="<?= BASE_URL ?>/feed.php?area=all" class="text-decoration-none fw-bold small text-primary">
                View All Localities &rarr;
            </a>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/feed.php?area=all" class="locality-filter-pill <?= empty($browsingAreaId) ? 'active' : '' ?>">
                <i data-lucide="globe" style="width: 15px; height: 15px;"></i>
                <span>All Kerala Localities</span>
            </a>
            <?php foreach ($allAreas as $area): ?>
                <a href="<?= BASE_URL ?>/feed.php?area=<?= $area['id'] ?>" 
                   class="locality-filter-pill <?= ($browsingAreaId == $area['id']) ? 'active' : '' ?>">
                    <i data-lucide="map-pin" style="width: 15px; height: 15px; color: #ea580c;"></i>
                    <span><?= htmlspecialchars($area['name']) ?></span>
                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;"><?= htmlspecialchars($area['pincode']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 5. Six Colorful Channel Cards (Tactile Noticeboard Explorer) -->
    <div class="mb-5">
        <div class="mb-3">
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold px-3 py-1 rounded-pill small mb-2">
                COMMUNITY DIRECTORY
            </span>
            <h3 class="fw-bold text-dark mb-1">What would you like to explore today?</h3>
            <p class="text-secondary small mb-0">Six dedicated notice channels connecting residents, students, and local businesses.</p>
        </div>

        <div class="row g-4">
            <!-- Channel 1: Civic Announcements -->
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/feed.php?category=announcements" class="channel-card channel-announcements">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="channel-icon-wrap" style="background: #e0f2fe; color: #0284c7;">
                            <i data-lucide="megaphone" style="width: 26px; height: 26px;"></i>
                        </div>
                        <span class="badge bg-info-subtle text-info border border-info-subtle fw-bold small">Official Updates</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Civic & Ward Notices</h5>
                    <p class="text-secondary small mb-3">
                        Scheduled KWA drinking water cuts, KSEB line shutdowns, municipality health camps, and local traffic diversions.
                    </p>
                    <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between text-info fw-bold small">
                        <span>Read civic alerts</span>
                        <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                    </div>
                </a>
            </div>

            <!-- Channel 2: Community Help -->
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/help.php" class="channel-card channel-help">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="channel-icon-wrap" style="background: #fef2f2; color: #dc2626;">
                            <i data-lucide="heart-handshake" style="width: 26px; height: 26px;"></i>
                        </div>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold small">100% Free Aid</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Mutual Assistance</h5>
                    <p class="text-secondary small mb-3">
                        Borrow wheelchairs or tools for home recovery, request emergency blood donors, and support elderly neighbors with medicine runs.
                    </p>
                    <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between text-danger fw-bold small">
                        <span>Open help requests</span>
                        <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                    </div>
                </a>
            </div>

            <!-- Channel 3: Local Bazaar -->
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/marketplace.php" class="channel-card channel-bazaar">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="channel-icon-wrap" style="background: #fffbeb; color: #d97706;">
                            <i data-lucide="shopping-bag" style="width: 26px; height: 26px;"></i>
                        </div>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold small"><?= (int)$totalMarketCount ?> Items</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Neighborhood Bazaar</h5>
                    <p class="text-secondary small mb-3">
                        Buy & sell pre-owned bicycles, study tables, electronics, and books directly from neighbors. Safe face-to-face pickup with no courier scams.
                    </p>
                    <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between text-warning fw-bold small">
                        <span>Browse local items</span>
                        <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                    </div>
                </a>
            </div>

            <!-- Channel 4: Local Events -->
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/events.php" class="channel-card channel-events">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="channel-icon-wrap" style="background: #f5f3ff; color: #7c3aed;">
                            <i data-lucide="calendar" style="width: 26px; height: 26px;"></i>
                        </div>
                        <span class="badge bg-purple-subtle text-purple border small fw-bold" style="color: #7c3aed;">Cultural & Sports</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Utsavams & Tournaments</h5>
                    <p class="text-secondary small mb-3">
                        Monsoon floodlight 7s football, temple festival programs, lake & maidan clean-up drives, and student tech meetups.
                    </p>
                    <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between fw-bold small" style="color: #7c3aed;">
                        <span>View upcoming events</span>
                        <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                    </div>
                </a>
            </div>

            <!-- Channel 5: Local Jobs -->
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/jobs.php" class="channel-card channel-jobs">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="channel-icon-wrap" style="background: #ecfdf5; color: #059669;">
                            <i data-lucide="briefcase" style="width: 26px; height: 26px;"></i>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold small"><?= (int)$totalJobsCount ?> Vacancies</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Local Jobs & Gigs</h5>
                    <p class="text-secondary small mb-3">
                        Cafe baristas, bakery counter staff, school tutors, delivery assistants, and junior software openings in nearby commercial centers.
                    </p>
                    <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between text-success fw-bold small">
                        <span>Explore jobs</span>
                        <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                    </div>
                </a>
            </div>

            <!-- Channel 6: Lost & Found -->
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/lost-found.php" class="channel-card channel-lost">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="channel-icon-wrap" style="background: #fff7ed; color: #ea580c;">
                            <i data-lucide="search" style="width: 26px; height: 26px;"></i>
                        </div>
                        <span class="badge bg-warning-subtle text-dark border small fw-bold">Active Desk</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Lost & Found Desk</h5>
                    <p class="text-secondary small mb-3">
                        Misplaced bike keys at bus stands, lost wallets with driving licenses, college ID cards, and pet rescue notifications.
                    </p>
                    <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between text-warning fw-bold small">
                        <span>Report or search item</span>
                        <i data-lucide="arrow-right" style="width: 15px; height: 15px;"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- 6. Real Community Notices (Live from DB) -->
    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <span class="text-primary">📋</span> Recent Notices Pinned by Neighbors
                </h4>
                <p class="text-secondary small mb-0">Live community feed showing verified notices, recommendations, and local announcements.</p>
            </div>
            <a href="<?= BASE_URL ?>/feed.php" class="btn btn-sm btn-outline-dark rounded-pill fw-bold px-3">
                Open Full Feed &rarr;
            </a>
        </div>

        <div class="row g-4">
            <?php if (!empty($recentPosts)): ?>
                <?php foreach ($recentPosts as $post): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="community-notice-card">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge rounded-pill px-2.5 py-1" style="background: <?= htmlspecialchars($post['cat_color']) ?>18; color: <?= htmlspecialchars($post['cat_color']) ?>; border: 1px solid <?= htmlspecialchars($post['cat_color']) ?>30; font-size: 0.72rem; font-weight: 700;">
                                    <?= htmlspecialchars($post['cat_name']) ?>
                                </span>
                                <span class="text-muted small" style="font-size: 0.75rem;">
                                    📍 <?= htmlspecialchars($post['area_name']) ?>
                                </span>
                            </div>

                            <h6 class="fw-bold text-dark mb-2" style="line-height: 1.4;">
                                <a href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>" class="text-dark text-decoration-none hover-primary">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h6>

                            <p class="text-secondary small mb-3" style="line-height: 1.6; flex: 1;">
                                <?= htmlspecialchars(mb_strimwidth($post['description'], 0, 130, '...')) ?>
                            </p>

                            <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= get_avatar_url($post['author_avatar']) ?>" alt="Author" class="rounded-circle" style="width: 24px; height: 24px; object-fit: cover;">
                                    <div class="overflow-hidden">
                                        <div class="fw-semibold text-dark small text-truncate" style="max-width: 110px; font-size: 0.78rem;">
                                            <?= htmlspecialchars($post['author_name']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 0.75rem;">
                                    <span>❤️ <?= (int)$post['total_likes'] ?></span>
                                    <span>💬 <?= (int)$post['total_comments'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4 bg-white rounded-3 border">
                    <p class="text-muted mb-0">No posts currently active in this locality.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 7. Essential Civic Directory & Emergency Helplines -->
    <div class="mb-5 p-4 rounded-4 bg-white border border-2 border-danger-subtle shadow-sm">
        <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 mb-3 border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">🚨</span>
                    <h5 class="fw-bold text-dark mb-0">Kerala Essential Civic & Emergency Helplines</h5>
                </div>
                <p class="text-secondary small mb-0 mt-1">Official state government & public utility emergency helplines. Save these numbers or click to dial directly.</p>
            </div>
            <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-bold">24x7 TOLL-FREE</span>
        </div>

        <div class="row g-3">
            <div class="col-sm-6 col-md-3">
                <a href="tel:112" class="helpline-box">
                    <div>
                        <span class="badge bg-danger-subtle text-danger small fw-bold mb-1">Police Emergency</span>
                        <div class="fw-bold text-dark fs-5">112 / 100</div>
                        <div class="text-muted small">Kerala Police</div>
                    </div>
                    <div class="btn btn-sm btn-danger rounded-circle p-1.5"><i data-lucide="phone" style="width: 14px; height: 14px;"></i></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="tel:101" class="helpline-box">
                    <div>
                        <span class="badge bg-danger-subtle text-danger small fw-bold mb-1">Fire & Rescue</span>
                        <div class="fw-bold text-dark fs-5">101</div>
                        <div class="text-muted small">Kerala Fire Station</div>
                    </div>
                    <div class="btn btn-sm btn-danger rounded-circle p-1.5"><i data-lucide="flame" style="width: 14px; height: 14px;"></i></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="tel:108" class="helpline-box">
                    <div>
                        <span class="badge bg-danger-subtle text-danger small fw-bold mb-1">Ambulance</span>
                        <div class="fw-bold text-dark fs-5">108 / 102</div>
                        <div class="text-muted small">Medical Emergency</div>
                    </div>
                    <div class="btn btn-sm btn-danger rounded-circle p-1.5"><i data-lucide="heart-pulse" style="width: 14px; height: 14px;"></i></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="tel:1912" class="helpline-box" style="border-color: #fde68a;">
                    <div>
                        <span class="badge bg-warning-subtle text-warning small fw-bold mb-1">Electricity (KSEB)</span>
                        <div class="fw-bold text-dark fs-5">1912</div>
                        <div class="text-muted small">Power Breakdown</div>
                    </div>
                    <div class="btn btn-sm btn-warning text-dark rounded-circle p-1.5"><i data-lucide="zap" style="width: 14px; height: 14px;"></i></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="tel:1916" class="helpline-box" style="border-color: #bae6fd;">
                    <div>
                        <span class="badge bg-info-subtle text-info small fw-bold mb-1">Water Supply (KWA)</span>
                        <div class="fw-bold text-dark fs-5">1916</div>
                        <div class="text-muted small">Supply Complaints</div>
                    </div>
                    <div class="btn btn-sm btn-info text-white rounded-circle p-1.5"><i data-lucide="droplet" style="width: 14px; height: 14px;"></i></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="tel:1098" class="helpline-box" style="border-color: #ddd6fe;">
                    <div>
                        <span class="badge bg-purple-subtle text-purple small fw-bold mb-1" style="color: #7c3aed;">Childline India</span>
                        <div class="fw-bold text-dark fs-5">1098</div>
                        <div class="text-muted small">Child Care & Safety</div>
                    </div>
                    <div class="btn btn-sm btn-primary rounded-circle p-1.5"><i data-lucide="smile" style="width: 14px; height: 14px;"></i></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="tel:181" class="helpline-box" style="border-color: #fbcfe8;">
                    <div>
                        <span class="badge bg-danger-subtle text-danger small fw-bold mb-1">Women Helpline</span>
                        <div class="fw-bold text-dark fs-5">181</div>
                        <div class="text-muted small">Mitra 24x7 Help</div>
                    </div>
                    <div class="btn btn-sm btn-danger rounded-circle p-1.5"><i data-lucide="shield" style="width: 14px; height: 14px;"></i></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="tel:04712529200" class="helpline-box" style="border-color: #a7f3d0;">
                    <div>
                        <span class="badge bg-success-subtle text-success small fw-bold mb-1">Snake Rescue</span>
                        <div class="fw-bold text-dark fs-5">0471-2529200</div>
                        <div class="text-muted small">Forest Dept (SARPA)</div>
                    </div>
                    <div class="btn btn-sm btn-success rounded-circle p-1.5"><i data-lucide="life-buoy" style="width: 14px; height: 14px;"></i></div>
                </a>
            </div>
        </div>
    </div>

    <!-- 8. How LocalLoop Works (Authentic & Informative Community Principles) -->
    <div class="p-4 rounded-4 bg-white border mb-5 shadow-sm">
        <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <span class="text-success">🛡️</span> How Our Neighborhood Noticeboard Stays Safe & Trusted
        </h4>
        <p class="text-secondary small mb-4">Unlike global social platforms filled with clickbait and distant bots, LocalLoop operates on strict neighborhood principles:</p>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="p-3 rounded-3 bg-light border-start border-3 border-primary h-100">
                    <h6 class="fw-bold text-dark mb-1">1. Locality & Ward Pinning</h6>
                    <p class="text-secondary small mb-0">Every post belongs to a verified Kerala locality so you only see news, traffic, and notices that physically impact your street.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded-3 bg-light border-start border-3 border-warning h-100">
                    <h6 class="fw-bold text-dark mb-1">2. Local Handover Only</h6>
                    <p class="text-secondary small mb-0">Marketplace trades happen in person at familiar town spots. No advance deposit demands and no courier delivery scams.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded-3 bg-light border-start border-3 border-danger h-100">
                    <h6 class="fw-bold text-dark mb-1">3. Priority Emergency Aid</h6>
                    <p class="text-secondary small mb-0">Urgent medical requirements, equipment borrowing, and blood appeals are pinned at the top completely free of charge.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded-3 bg-light border-start border-3 border-success h-100">
                    <h6 class="fw-bold text-dark mb-1">4. Zero Commercial Spam</h6>
                    <p class="text-secondary small mb-0">Strict community moderation removes political fights, clickbait gossip, and multi-level marketing solicitations.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 9. Quick Official Kerala Civic Portals Strip -->
    <div class="card p-4 rounded-4 border-0 mb-5 text-center text-md-start" style="background: linear-gradient(135deg, #fef3c7 0%, #e0f2fe 100%);">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <h5 class="fw-bold text-dark mb-1">Need official Kerala State Government Online Services?</h5>
                <p class="text-secondary small mb-0">Quick access to official utility payment, waste disposal regulations, and citizen safety portals.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <a href="https://wss.kseb.in/selfservices/quickpay" target="_blank" rel="noopener" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill">
                        ⚡ KSEB Bill Pay
                    </a>
                    <a href="https://thuna.keralapolice.gov.in/" target="_blank" rel="noopener" class="btn btn-sm btn-primary fw-bold rounded-pill">
                        🚓 Police Thuna Portal
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
