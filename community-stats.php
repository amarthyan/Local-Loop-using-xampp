<?php
// community-stats.php
// LocalLoop Community Overview & Statistics (Section 29)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Community Overview & Statistics";
require_once __DIR__ . '/includes/header.php';

// Exact statistics from specification (Section 29)
$membersCount = 2480;
$postsCount = 1284;
$eventsCount = 326;
$helpRequestsCount = 184;
?>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<div class="container-fluid p-0">
    <!-- Header: Community Overview (Section 29) -->
    <div class="mb-5">
        <div class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill mb-2 fw-semibold">
            <i data-lucide="bar-chart-2" class="me-1" style="width: 14px; height: 14px;"></i> Neighborhood Telemetry
        </div>
        <h2 class="display-6 fw-bold text-dark mb-1">Community Overview</h2>
        <p class="text-secondary" style="max-width: 600px;">
            Real-time analytics and transparency metrics for <?= htmlspecialchars($currentAreaName) ?> and connected loops.
        </p>
    </div>

    <!-- 4 Statistics Cards (Section 29) -->
    <div class="row g-3 mb-5">
        <!-- 2,480 Members -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-solid p-4 text-center interactive-tilt card-glass-shimmer">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-bold text-uppercase">Neighbors</span>
                    <span class="p-2 rounded-circle bg-primary-subtle text-primary"><i data-lucide="users" style="width: 18px; height: 18px;"></i></span>
                </div>
                <div class="display-6 fw-bold text-dark mb-1 counter-value" data-counter="<?= $membersCount ?>"><?= number_format($membersCount) ?></div>
                <div class="text-secondary small">Registered Members</div>
                <div class="text-success small mt-2 fw-semibold"><i data-lucide="trending-up" style="width: 13px; height: 13px;"></i> +18% this month</div>
            </div>
        </div>

        <!-- 1,284 Posts -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-solid p-4 text-center interactive-tilt card-glass-shimmer">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-bold text-uppercase">Civic Updates</span>
                    <span class="p-2 rounded-circle bg-info-subtle text-info"><i data-lucide="message-square" style="width: 18px; height: 18px;"></i></span>
                </div>
                <div class="display-6 fw-bold text-dark mb-1 counter-value" data-counter="<?= $postsCount ?>"><?= number_format($postsCount) ?></div>
                <div class="text-secondary small">Community Posts</div>
                <div class="text-success small mt-2 fw-semibold"><i data-lucide="trending-up" style="width: 13px; height: 13px;"></i> 128 posts today</div>
            </div>
        </div>

        <!-- 326 Events -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-solid p-4 text-center interactive-tilt card-glass-shimmer">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-bold text-uppercase">Events Organized</span>
                    <span class="p-2 rounded-circle bg-purple-subtle text-purple" style="color: #7c3aed;"><i data-lucide="calendar" style="width: 18px; height: 18px;"></i></span>
                </div>
                <div class="display-6 fw-bold text-dark mb-1 counter-value" data-counter="<?= $eventsCount ?>"><?= number_format($eventsCount) ?></div>
                <div class="text-secondary small">Local Events</div>
                <div class="text-primary small mt-2 fw-semibold"><i data-lucide="calendar-check" style="width: 13px; height: 13px;"></i> 14 upcoming</div>
            </div>
        </div>

        <!-- 184 Help Requests -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-solid p-4 text-center interactive-tilt card-glass-shimmer">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-bold text-uppercase">Mutual Aid</span>
                    <span class="p-2 rounded-circle bg-danger-subtle text-danger"><i data-lucide="heart-handshake" style="width: 18px; height: 18px;"></i></span>
                </div>
                <div class="display-6 fw-bold text-dark mb-1 counter-value" data-counter="<?= $helpRequestsCount ?>"><?= number_format($helpRequestsCount) ?></div>
                <div class="text-secondary small">Help Requests</div>
                <div class="text-success small mt-2 fw-semibold"><i data-lucide="check-circle" style="width: 13px; height: 13px;"></i> 91% resolved</div>
            </div>
        </div>
    </div>

    <!-- Clean Dashboard Charts (Section 29) -->
    <div class="row g-4 mb-4">
        <!-- Chart 1: Community Activity Over Time -->
        <div class="col-lg-8">
            <div class="card card-solid p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Community Activity & Engagement</h6>
                        <span class="text-secondary small">Daily posts, comments, and interactions across the past 30 days</span>
                    </div>
                    <span class="badge bg-light text-secondary border">30 Days</span>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 2: Posts by Category (Doughnut) -->
        <div class="col-lg-4">
            <div class="card card-solid p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark">Posts by Category</h6>
                    <span class="badge bg-light text-secondary border">Distribution</span>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 3: New Members Growth (Bar) -->
        <div class="col-lg-6">
            <div class="card card-solid p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">New Members Growth</h6>
                        <span class="text-secondary small">Weekly neighborhood onboarding</span>
                    </div>
                    <span class="badge bg-success-subtle text-success">+142 this week</span>
                </div>
                <div style="height: 240px; position: relative;">
                    <canvas id="membersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 4: Events & Help Requests -->
        <div class="col-lg-6">
            <div class="card card-solid p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Events & Help Requests Resolved</h6>
                        <span class="text-secondary small">Civic impact & mutual aid delivery</span>
                    </div>
                    <span class="badge bg-info-subtle text-info">Civic Impact</span>
                </div>
                <div style="height: 240px; position: relative;">
                    <canvas id="impactChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize Charts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart 1: Activity Line Chart
        const ctxActivity = document.getElementById('activityChart');
        if (ctxActivity) {
            new Chart(ctxActivity, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'This Week'],
                    datasets: [
                        {
                            label: 'Community Posts',
                            data: [180, 240, 210, 310, 344],
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.08)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4
                        },
                        {
                            label: 'Interactions & Comments',
                            data: [320, 480, 410, 580, 680],
                            borderColor: '#0ea5e9',
                            backgroundColor: 'transparent',
                            borderDash: [4, 4],
                            tension: 0.35,
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
                }
            });
        }

        // Chart 2: Category Doughnut Chart
        const ctxCat = document.getElementById('categoryChart');
        if (ctxCat) {
            new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: ['Announcements', 'Events', 'Jobs', 'Marketplace', 'Help', 'Recommendations'],
                    datasets: [{
                        data: [340, 220, 180, 280, 140, 124],
                        backgroundColor: ['#0284c7', '#8b5cf6', '#059669', '#d97706', '#dc2626', '#ca8a04'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '68%'
                }
            });
        }

        // Chart 3: Members Bar Chart
        const ctxMem = document.getElementById('membersChart');
        if (ctxMem) {
            new Chart(ctxMem, {
                type: 'bar',
                data: {
                    labels: ['North Paravur', 'Maliankara', 'Kodungallur', 'Aluva', 'Ernakulam'],
                    datasets: [{
                        label: 'Active Members',
                        data: [2480, 1120, 3140, 4200, 6800],
                        backgroundColor: '#0b132b',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
                }
            });
        }

        // Chart 4: Events & Help Requests
        const ctxImpact = document.getElementById('impactChart');
        if (ctxImpact) {
            new Chart(ctxImpact, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Help Resolved',
                            data: [24, 32, 28, 41, 38, 52],
                            backgroundColor: '#10b981',
                            borderRadius: 4
                        },
                        {
                            label: 'Events Held',
                            data: [12, 18, 15, 22, 26, 31],
                            backgroundColor: '#8b5cf6',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
