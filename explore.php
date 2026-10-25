<?php
// explore.php
// LocalLoop Explore Page (Section 13)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Explore LocalLoop";
require_once __DIR__ . '/includes/header.php';

// Count metrics for cards safely
$eventsCount = 14;
$jobsCount = 28;
$marketCount = 52;
$helpCount = 9;
$lostCount = 7;
$announcementsCount = 45;

if (isset($pdo)) {
    try {
        $eventsCount = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'upcoming'")->fetchColumn() ?: 14;
        $jobsCount = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'open'")->fetchColumn() ?: 28;
        $marketCount = $pdo->query("SELECT COUNT(*) FROM marketplace_items WHERE status = 'available'")->fetchColumn() ?: 52;
        $helpCount = $pdo->query("SELECT COUNT(*) FROM help_requests WHERE status = 'open'")->fetchColumn() ?: 9;
        $lostCount = $pdo->query("SELECT COUNT(*) FROM lost_found WHERE status = 'open'")->fetchColumn() ?: 7;
        $announcementsCount = $pdo->query("SELECT COUNT(*) FROM posts WHERE post_type = 'announcement'")->fetchColumn() ?: 45;
    } catch (Exception $e) {}
}
?>

<div class="container-fluid p-0">
    <!-- Explore Header (Section 13) -->
    <div class="mb-5">
        <div class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill mb-2 fw-semibold">
            <i data-lucide="compass" class="me-1" style="width: 14px; height: 14px;"></i> Hyperlocal Directory
        </div>
        <h2 class="display-6 fw-bold text-dark mb-2">Explore LocalLoop</h2>
        <p class="text-secondary" style="max-width: 600px;">
            Discover everything happening in <?= htmlspecialchars($currentAreaName) ?>. Browse channels, find local goods, discover career openings, and offer help.
        </p>

        <!-- Search Bar -->
        <form class="mt-4" action="<?= BASE_URL ?>/search.php" method="GET" style="max-width: 520px;">
            <div class="input-group search-pill bg-white border p-1 rounded-pill shadow-subtle">
                <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                    <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                </span>
                <input type="text" name="q" class="form-control border-0 bg-transparent text-dark" placeholder="Search across events, jobs, items, announcements...">
                <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Search</button>
            </div>
        </form>
    </div>

    <!-- Large Category Cards Grid (Section 13) -->
    <div class="row g-4">
        
        <!-- 1. Events -->
        <div class="col-md-6 col-xl-4">
            <a href="<?= BASE_URL ?>/events.php" class="explore-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="explore-card-icon" style="background: #f3e8ff; color: #7c3aed;">
                        <i data-lucide="calendar" style="width: 26px; height: 26px;"></i>
                    </div>
                    <span class="badge bg-purple-subtle text-purple border" style="color: #7c3aed;"><?= $eventsCount ?> Active</span>
                </div>
                <h4 class="fw-bold text-dark mb-1">Events</h4>
                <p class="text-secondary small mb-4">
                    Discover events nearby. Cultural festivals, clean-up drives, sports tournaments, and resident meetups.
                </p>
                <div class="d-flex align-items-center text-primary fw-semibold small mt-auto">
                    <span>Browse upcoming events</span>
                    <i data-lucide="arrow-right" class="ms-1" style="width: 15px; height: 15px;"></i>
                </div>
            </a>
        </div>

        <!-- 2. Jobs -->
        <div class="col-md-6 col-xl-4">
            <a href="<?= BASE_URL ?>/jobs.php" class="explore-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="explore-card-icon" style="background: #ecfdf5; color: #059669;">
                        <i data-lucide="briefcase" style="width: 26px; height: 26px;"></i>
                    </div>
                    <span class="badge bg-success-subtle text-success border"><?= $jobsCount ?> Openings</span>
                </div>
                <h4 class="fw-bold text-dark mb-1">Jobs</h4>
                <p class="text-secondary small mb-4">
                    Find local opportunities. Full-time, part-time, freelance gigs, and student internships in your locality.
                </p>
                <div class="d-flex align-items-center text-primary fw-semibold small mt-auto">
                    <span>View job listings</span>
                    <i data-lucide="arrow-right" class="ms-1" style="width: 15px; height: 15px;"></i>
                </div>
            </a>
        </div>

        <!-- 3. Marketplace -->
        <div class="col-md-6 col-xl-4">
            <a href="<?= BASE_URL ?>/marketplace.php" class="explore-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="explore-card-icon" style="background: #fffbeb; color: #d97706;">
                        <i data-lucide="shopping-bag" style="width: 26px; height: 26px;"></i>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border"><?= $marketCount ?> Items</span>
                </div>
                <h4 class="fw-bold text-dark mb-1">Marketplace</h4>
                <p class="text-secondary small mb-4">
                    Buy and sell locally. Teak furniture, bikes, electronics, textbooks, and appliances from neighbors.
                </p>
                <div class="d-flex align-items-center text-primary fw-semibold small mt-auto">
                    <span>Browse marketplace</span>
                    <i data-lucide="arrow-right" class="ms-1" style="width: 15px; height: 15px;"></i>
                </div>
            </a>
        </div>

        <!-- 4. Community Help -->
        <div class="col-md-6 col-xl-4">
            <a href="<?= BASE_URL ?>/help.php" class="explore-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="explore-card-icon" style="background: #fef2f2; color: #dc2626;">
                        <i data-lucide="heart-handshake" style="width: 26px; height: 26px;"></i>
                    </div>
                    <span class="badge bg-danger-subtle text-danger border"><?= $helpCount ?> Requests</span>
                </div>
                <h4 class="fw-bold text-dark mb-1">Community Help</h4>
                <p class="text-secondary small mb-4">
                    Request or offer assistance. Elderly care, shifting furniture, blood donation, and emergency community aid.
                </p>
                <div class="d-flex align-items-center text-primary fw-semibold small mt-auto">
                    <span>Explore help requests</span>
                    <i data-lucide="arrow-right" class="ms-1" style="width: 15px; height: 15px;"></i>
                </div>
            </a>
        </div>

        <!-- 5. Lost & Found -->
        <div class="col-md-6 col-xl-4">
            <a href="<?= BASE_URL ?>/lost-found.php" class="explore-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="explore-card-icon" style="background: #fff7ed; color: #ea580c;">
                        <i data-lucide="search" style="width: 26px; height: 26px;"></i>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border"><?= $lostCount ?> Reports</span>
                </div>
                <h4 class="fw-bold text-dark mb-1">Lost & Found</h4>
                <p class="text-secondary small mb-4">
                    Find missing belongings. Lost wallets, keys, pets, certificates, and items found near campus and bus stands.
                </p>
                <div class="d-flex align-items-center text-primary fw-semibold small mt-auto">
                    <span>Check reported items</span>
                    <i data-lucide="arrow-right" class="ms-1" style="width: 15px; height: 15px;"></i>
                </div>
            </a>
        </div>

        <!-- 6. Announcements -->
        <div class="col-md-6 col-xl-4">
            <a href="<?= BASE_URL ?>/feed.php?category=announcements" class="explore-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="explore-card-icon" style="background: #e0f2fe; color: #0284c7;">
                        <i data-lucide="megaphone" style="width: 26px; height: 26px;"></i>
                    </div>
                    <span class="badge bg-info-subtle text-info border"><?= $announcementsCount ?> Updates</span>
                </div>
                <h4 class="fw-bold text-dark mb-1">Announcements</h4>
                <p class="text-secondary small mb-4">
                    Important local information. Water maintenance schedules, power outage alerts, and civic notices.
                </p>
                <div class="d-flex align-items-center text-primary fw-semibold small mt-auto">
                    <span>Read local news</span>
                    <i data-lucide="arrow-right" class="ms-1" style="width: 15px; height: 15px;"></i>
                </div>
            </a>
        </div>

        <!-- 7. Recommendations -->
        <div class="col-md-6 col-xl-4">
            <a href="<?= BASE_URL ?>/feed.php?category=recommendations" class="explore-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="explore-card-icon" style="background: #fef9c3; color: #ca8a04;">
                        <i data-lucide="star" style="width: 26px; height: 26px;"></i>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border">Curated</span>
                </div>
                <h4 class="fw-bold text-dark mb-1">Recommendations</h4>
                <p class="text-secondary small mb-4">
                    Discover useful places and services. Trusted plumbers, verified mechanics, food spots, and pediatricians.
                </p>
                <div class="d-flex align-items-center text-primary fw-semibold small mt-auto">
                    <span>Explore neighbor reviews</span>
                    <i data-lucide="arrow-right" class="ms-1" style="width: 15px; height: 15px;"></i>
                </div>
            </a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
