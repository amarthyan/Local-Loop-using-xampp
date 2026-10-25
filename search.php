<?php
// search.php
// LocalLoop Universal Search (Section 23)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$query = trim($_GET['q'] ?? 'laptop');
$activeTab = $_GET['tab'] ?? 'all';

$areaFilter = (int)($_GET['area'] ?? 0);
$catFilter = (int)($_GET['cat'] ?? 0);
$distanceFilter = $_GET['distance'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$sortFilter = $_GET['sort'] ?? 'relevance';

$categories = get_active_categories($pdo);
$areas = get_active_areas($pdo);

// Fetch Search Results across channels
$wild = "%{$query}%";

// 1. Posts
$postStmt = $pdo->prepare("SELECT p.*, u.name as author_name, a.name as area_name, c.name as cat_name 
                           FROM posts p
                           JOIN users u ON p.user_id = u.id
                           JOIN areas a ON p.area_id = a.id
                           JOIN categories c ON p.category_id = c.id
                           WHERE p.status = 'approved' AND (p.title LIKE ? OR p.description LIKE ?)");
$postStmt->execute([$wild, $wild]);
$postResults = $postStmt->fetchAll();

// 2. Marketplace
$itemStmt = $pdo->prepare("SELECT m.*, a.name as area_name, u.name as seller_name 
                           FROM marketplace_items m
                           JOIN areas a ON m.area_id = a.id
                           JOIN users u ON m.user_id = u.id
                           WHERE m.status != 'sold' AND (m.title LIKE ? OR m.description LIKE ?)");
$itemStmt->execute([$wild, $wild]);
$itemResults = $itemStmt->fetchAll();

// 3. Jobs
$jobStmt = $pdo->prepare("SELECT j.*, a.name as area_name 
                          FROM jobs j
                          JOIN areas a ON j.area_id = a.id
                          WHERE j.status = 'open' AND (j.title LIKE ? OR j.description LIKE ? OR j.company LIKE ?)");
$jobStmt->execute([$wild, $wild, $wild]);
$jobResults = $jobStmt->fetchAll();

// 4. Events
$eventStmt = $pdo->prepare("SELECT e.*, a.name as area_name 
                            FROM events e
                            JOIN areas a ON e.area_id = a.id
                            WHERE e.status = 'upcoming' AND (e.title LIKE ? OR e.description LIKE ?)");
$eventStmt->execute([$wild, $wild]);
$eventResults = $eventStmt->fetchAll();

$totalCount = count($postResults) + count($itemResults) + count($jobResults) + count($eventResults);

$pageTitle = 'Search: "' . htmlspecialchars($query) . '"';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid p-0">
    <!-- Header: Search results for "..." (Section 23) -->
    <div class="mb-4">
        <h2 class="display-6 fw-bold text-dark mb-1">
            Search results for "<span class="text-primary"><?= htmlspecialchars($query) ?></span>"
        </h2>
        <p class="text-secondary small mb-0">Found <?= $totalCount ?> matching results in <?= htmlspecialchars($currentAreaName) ?> and nearby areas.</p>
    </div>

    <!-- Tabs: All, Posts, Marketplace, Jobs, Events (Section 23) -->
    <div class="card card-solid p-2 mb-4">
        <ul class="nav nav-pills gap-1">
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'all' ? 'active bg-primary' : 'text-secondary' ?>" href="?q=<?= urlencode($query) ?>&tab=all">
                    All (<?= $totalCount ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'posts' ? 'active bg-primary' : 'text-secondary' ?>" href="?q=<?= urlencode($query) ?>&tab=posts">
                    Posts (<?= count($postResults) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'marketplace' ? 'active bg-primary' : 'text-secondary' ?>" href="?q=<?= urlencode($query) ?>&tab=marketplace">
                    Marketplace (<?= count($itemResults) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'jobs' ? 'active bg-primary' : 'text-secondary' ?>" href="?q=<?= urlencode($query) ?>&tab=jobs">
                    Jobs (<?= count($jobResults) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'events' ? 'active bg-primary' : 'text-secondary' ?>" href="?q=<?= urlencode($query) ?>&tab=events">
                    Events (<?= count($eventResults) ?>)
                </a>
            </li>
        </ul>
    </div>

    <!-- Filters Row: Area, Category, Distance, Date, Sort (Section 23) -->
    <div class="card card-solid p-3 mb-4">
        <form method="GET" action="<?= BASE_URL ?>/search.php" class="row g-2 align-items-center">
            <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab) ?>">

            <!-- 1. Area -->
            <div class="col-sm-6 col-lg">
                <select name="area" class="form-select form-select-sm rounded-pill border py-1.5 px-3 small">
                    <option value="">📍 Area: All</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($areaFilter == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 2. Category -->
            <div class="col-sm-6 col-lg">
                <select name="cat" class="form-select form-select-sm rounded-pill border py-1.5 px-3 small">
                    <option value="">🏷️ Category: All</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($catFilter == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 3. Distance -->
            <div class="col-sm-6 col-lg">
                <select name="distance" class="form-select form-select-sm rounded-pill border py-1.5 px-3 small">
                    <option value="">📏 Distance: Any</option>
                    <option value="1">Within 1 km</option>
                    <option value="5">Within 5 km</option>
                    <option value="10">Within 10 km</option>
                </select>
            </div>

            <!-- 4. Date -->
            <div class="col-sm-6 col-lg">
                <select name="date" class="form-select form-select-sm rounded-pill border py-1.5 px-3 small">
                    <option value="">📅 Date: Any time</option>
                    <option value="today">Past 24 hours</option>
                    <option value="week">Past week</option>
                    <option value="month">Past month</option>
                </select>
            </div>

            <!-- 5. Sort -->
            <div class="col-sm-6 col-lg">
                <select name="sort" class="form-select form-select-sm rounded-pill border py-1.5 px-3 small">
                    <option value="relevance">⚡ Sort: Most Relevant</option>
                    <option value="latest">Latest First</option>
                    <option value="popular">Most Popular</option>
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary-custom rounded-pill px-3">Filter</button>
            </div>
        </form>
    </div>

    <!-- Results as Clean Cards (Section 23) -->
    <?php if ($totalCount === 0): ?>
        <div class="card card-solid p-5 text-center my-4">
            <i data-lucide="search-x" class="mx-auto text-muted mb-2" style="width: 44px; height: 44px;"></i>
            <h5 class="fw-bold">No results found for "<?= htmlspecialchars($query) ?>"</h5>
            <p class="text-secondary small">Try searching with a broader keyword or exploring category channels.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Marketplace Matches -->
            <?php if ($activeTab === 'all' || $activeTab === 'marketplace'): ?>
                <?php foreach ($itemResults as $it): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-solid p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-warning-subtle text-warning border">Marketplace</span>
                                    <span class="text-muted small">📍 <?= htmlspecialchars($it['area_name']) ?></span>
                                </div>
                                <h5 class="fw-bold mb-1">
                                    <a href="<?= BASE_URL ?>/item.php?id=<?= $it['id'] ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($it['title']) ?>
                                    </a>
                                </h5>
                                <div class="fs-5 fw-bold text-dark mb-2">₹<?= number_format($it['price']) ?></div>
                                <p class="text-secondary small mb-3"><?= htmlspecialchars(substr($it['description'], 0, 90)) ?>...</p>
                            </div>
                            <div class="pt-2 border-top text-end">
                                <a href="<?= BASE_URL ?>/item.php?id=<?= $it['id'] ?>" class="btn btn-sm btn-outline-custom">View Item &rarr;</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Posts Matches -->
            <?php if ($activeTab === 'all' || $activeTab === 'posts'): ?>
                <?php foreach ($postResults as $p): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-solid p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($p['cat_name']) ?></span>
                                    <span class="text-muted small">📍 <?= htmlspecialchars($p['area_name']) ?></span>
                                </div>
                                <h5 class="fw-bold mb-1">
                                    <a href="<?= BASE_URL ?>/post.php?id=<?= $p['id'] ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($p['title']) ?>
                                    </a>
                                </h5>
                                <p class="text-secondary small mb-3"><?= htmlspecialchars(substr($p['description'], 0, 110)) ?>...</p>
                            </div>
                            <div class="pt-2 border-top text-end">
                                <a href="<?= BASE_URL ?>/post.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-custom">View Discussion &rarr;</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Jobs Matches -->
            <?php if ($activeTab === 'all' || $activeTab === 'jobs'): ?>
                <?php foreach ($jobResults as $j): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-solid p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success-subtle text-success border"><?= ucfirst($j['job_type']) ?></span>
                                    <span class="text-muted small">📍 <?= htmlspecialchars($j['area_name']) ?></span>
                                </div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($j['title']) ?></h5>
                                <div class="text-primary fw-semibold small mb-2"><?= htmlspecialchars($j['company']) ?></div>
                                <div class="text-dark fw-bold small mb-2">💰 <?= htmlspecialchars($j['salary']) ?></div>
                            </div>
                            <div class="pt-2 border-top text-end">
                                <a href="<?= BASE_URL ?>/job.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-primary-custom">View Job &rarr;</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Events Matches -->
            <?php if ($activeTab === 'all' || $activeTab === 'events'): ?>
                <?php foreach ($eventResults as $ev): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-solid p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-purple-subtle text-purple border" style="color: #7c3aed;">Event</span>
                                    <span class="text-muted small">📍 <?= htmlspecialchars($ev['area_name']) ?></span>
                                </div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($ev['title']) ?></h5>
                                <div class="text-muted small mb-2">📅 <?= date('M d', strtotime($ev['event_date'])) ?> &bull; <?= htmlspecialchars($ev['venue']) ?></div>
                            </div>
                            <div class="pt-2 border-top text-end">
                                <a href="<?= BASE_URL ?>/event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-custom">View Event &rarr;</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
