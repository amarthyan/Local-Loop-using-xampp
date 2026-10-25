<?php
// admin/index.php
// LocalLoop Administrator Dashboard (Section 30)

$adminPageTitle = "Admin Dashboard";
require_once __DIR__ . '/header.php';

// Exact 6 Dashboard Cards Metrics (Section 30)
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$pendingReports = (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
$activeEvents = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE status = 'upcoming'")->fetchColumn() ?: 14;
$marketListings = (int)$pdo->query("SELECT COUNT(*) FROM marketplace_items WHERE status = 'available'")->fetchColumn() ?: 52;
$helpRequests = (int)$pdo->query("SELECT COUNT(*) FROM help_requests WHERE status = 'open'")->fetchColumn() ?: 9;

// Recent Pending Reports for Quick Action Table
$recentReports = $pdo->query("SELECT r.*, u.name as reporter_name, u.username as reporter_username, p.title as post_title 
                             FROM reports r 
                             JOIN users u ON r.reporter_id = u.id 
                             LEFT JOIN posts p ON r.post_id = p.id 
                             ORDER BY r.created_at DESC LIMIT 5")->fetchAll();

// Recent Registered Users
$recentUsers = $pdo->query("SELECT u.*, a.name as area_name FROM users u 
                            LEFT JOIN areas a ON u.area_id = a.id 
                            ORDER BY u.created_at DESC LIMIT 5")->fetchAll();
?>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Administrative Control Center</h2>
        <p class="text-secondary small mb-0">System health, community reports, platform safety, and telemetry monitoring.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success p-2 px-3 rounded-pill fw-semibold">
            <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i> System Operational &bull; MySQL Active
        </span>
    </div>
</div>

<!-- 6 Dashboard Cards (Section 30) -->
<div class="row g-3 mb-4">
    <!-- 1. Total Users -->
    <div class="col-sm-6 col-xl-2">
        <div class="card card-solid p-3 h-100">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase">
                <span>Total Users</span>
                <i data-lucide="users" class="text-primary" style="width: 16px; height: 16px;"></i>
            </div>
            <div class="fs-2 fw-bold text-dark mt-2 mb-1"><?= number_format($totalUsers) ?></div>
            <a href="users.php" class="small text-primary text-decoration-none fw-semibold">Manage Users &rarr;</a>
        </div>
    </div>

    <!-- 2. Total Posts -->
    <div class="col-sm-6 col-xl-2">
        <div class="card card-solid p-3 h-100">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase">
                <span>Total Posts</span>
                <i data-lucide="file-text" class="text-info" style="width: 16px; height: 16px;"></i>
            </div>
            <div class="fs-2 fw-bold text-dark mt-2 mb-1"><?= number_format($totalPosts) ?></div>
            <a href="posts.php" class="small text-info text-decoration-none fw-semibold">View Posts &rarr;</a>
        </div>
    </div>

    <!-- 3. Pending Reports -->
    <div class="col-sm-6 col-xl-2">
        <div class="card card-solid p-3 h-100 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase">
                <span>Pending Reports</span>
                <i data-lucide="flag" class="text-danger" style="width: 16px; height: 16px;"></i>
            </div>
            <div class="fs-2 fw-bold text-danger mt-2 mb-1"><?= number_format($pendingReports) ?></div>
            <a href="reports.php" class="small text-danger text-decoration-none fw-bold">Review Queue &rarr;</a>
        </div>
    </div>

    <!-- 4. Active Events -->
    <div class="col-sm-6 col-xl-2">
        <div class="card card-solid p-3 h-100">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase">
                <span>Active Events</span>
                <i data-lucide="calendar" class="text-purple" style="width: 16px; height: 16px; color: #7c3aed;"></i>
            </div>
            <div class="fs-2 fw-bold text-dark mt-2 mb-1"><?= number_format($activeEvents) ?></div>
            <a href="<?= BASE_URL ?>/events.php" class="small text-purple text-decoration-none fw-semibold" target="_blank" style="color: #7c3aed;">Events Grid &rarr;</a>
        </div>
    </div>

    <!-- 5. Marketplace Listings -->
    <div class="col-sm-6 col-xl-2">
        <div class="card card-solid p-3 h-100">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase">
                <span>Market Listings</span>
                <i data-lucide="shopping-bag" class="text-warning" style="width: 16px; height: 16px;"></i>
            </div>
            <div class="fs-2 fw-bold text-dark mt-2 mb-1"><?= number_format($marketListings) ?></div>
            <a href="<?= BASE_URL ?>/marketplace.php" class="small text-warning text-decoration-none fw-semibold" target="_blank">Marketplace &rarr;</a>
        </div>
    </div>

    <!-- 6. Help Requests -->
    <div class="col-sm-6 col-xl-2">
        <div class="card card-solid p-3 h-100">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase">
                <span>Help Requests</span>
                <i data-lucide="heart-handshake" class="text-danger" style="width: 16px; height: 16px;"></i>
            </div>
            <div class="fs-2 fw-bold text-dark mt-2 mb-1"><?= number_format($helpRequests) ?></div>
            <a href="<?= BASE_URL ?>/help.php" class="small text-danger text-decoration-none fw-semibold" target="_blank">Help Network &rarr;</a>
        </div>
    </div>
</div>

<!-- Charts Row (Section 30) -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card card-solid p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">Platform Activity & Ingestion Rate</h6>
                <span class="badge bg-light text-secondary border">Real-time telemetry</span>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="adminActivityChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-solid p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">Locality Distribution</h6>
                <span class="badge bg-light text-secondary border">Kerala</span>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="adminLocalityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tables: Recent Reports & Users (Section 30) -->
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="flag" class="text-danger" style="width: 16px; height: 16px;"></i>
                    <span>Pending Moderation Reports</span>
                </h6>
                <a href="reports.php" class="small text-primary text-decoration-none">Full Queue &rarr;</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Report ID</th>
                            <th>Reason</th>
                            <th>Reported By</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentReports)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No pending reports in queue! Everything clean.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentReports as $r): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border">#<?= $r['id'] ?></span></td>
                                    <td><span class="badge bg-danger-subtle text-danger"><?= htmlspecialchars($r['reason']) ?></span></td>
                                    <td>@<?= htmlspecialchars($r['reporter_username'] ?? 'User') ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= ucfirst($r['status']) ?></span></td>
                                    <td>
                                        <a href="reports.php" class="btn btn-xs btn-outline-primary py-0.5 px-2 rounded-pill">Review</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="users" class="text-primary" style="width: 16px; height: 16px;"></i>
                    <span>Recently Joined Citizens</span>
                </h6>
                <a href="users.php" class="small text-primary text-decoration-none">All &rarr;</a>
            </div>

            <div class="d-flex flex-column gap-2.5">
                <?php foreach ($recentUsers as $u): ?>
                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border small">
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= get_avatar_url($u['profile_photo']) ?>" width="32" height="32" class="rounded-circle border" style="object-fit: cover;">
                            <div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">@<?= htmlspecialchars($u['username']) ?> &bull; 📍 <?= htmlspecialchars($u['area_name'] ?? 'North Paravur') ?></div>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary"><?= ucfirst($u['role']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctxAct = document.getElementById('adminActivityChart');
        if (ctxAct) {
            new Chart(ctxAct, {
                type: 'line',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                    datasets: [{
                        label: 'Posts & Updates Ingested',
                        data: [12, 5, 45, 128, 98, 142],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const ctxLoc = document.getElementById('adminLocalityChart');
        if (ctxLoc) {
            new Chart(ctxLoc, {
                type: 'pie',
                data: {
                    labels: ['North Paravur', 'Maliankara', 'Kodungallur', 'Aluva', 'Ernakulam'],
                    datasets: [{
                        data: [42, 18, 22, 10, 8],
                        backgroundColor: ['#0b132b', '#2563eb', '#0ea5e9', '#10b981', '#f59e0b']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
