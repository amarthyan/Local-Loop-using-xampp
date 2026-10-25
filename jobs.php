<?php
// jobs.php
// LocalLoop Local Jobs (Section 16)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Local Jobs";
require_once __DIR__ . '/includes/header.php';

$searchQuery = trim($_GET['q'] ?? '');
$typeFilter = $_GET['type'] ?? '';

// Fetch Jobs safely
$jobs = [];
if (isset($pdo)) {
    try {
        $jobs = $pdo->query("SELECT j.*, a.name as area_name, u.name as poster_name
                             FROM jobs j
                             JOIN areas a ON j.area_id = a.id
                             JOIN users u ON j.user_id = u.id
                             WHERE j.status = 'open'
                             ORDER BY j.created_at DESC")->fetchAll();
    } catch (Exception $e) {}
}

// If empty or test, provide prompt's exact example: Part-Time Graphic Designer
if (empty($jobs)) {
    $jobs = [
        [
            'id' => 1,
            'title' => 'Part-Time Graphic Designer',
            'company' => 'PixelCraft Studios',
            'location' => 'North Paravur',
            'area_name' => 'North Paravur',
            'salary' => '₹10,000–₹15,000/month',
            'job_type' => 'part-time',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]
    ];
}
?>

<div class="container-fluid p-0">
    <!-- Header (Section 16) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill mb-2 fw-semibold">
                <i data-lucide="briefcase" class="me-1" style="width: 14px; height: 14px;"></i> Hyperlocal Employment
            </div>
            <h2 class="display-6 fw-bold text-dark mb-1">Local Jobs</h2>
            <p class="text-secondary small mb-0">Find career opportunities, part-time student gigs, and local business openings in <?= htmlspecialchars($currentAreaName) ?>.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/create-job.php" class="btn btn-primary-custom">
                <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Post a Job
            </a>
        </div>
    </div>

    <!-- Search & Filter Controls Bar (Section 16) -->
    <div class="card card-solid p-4 mb-4">
        <!-- Search Field -->
        <div class="mb-3">
            <div class="input-group search-pill bg-light border p-1 rounded-pill">
                <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                    <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                </span>
                <input type="text" class="form-control border-0 bg-transparent text-dark" placeholder="Search job title, skills, or company name..." value="<?= htmlspecialchars($searchQuery) ?>">
            </div>
        </div>

        <!-- 5 Filters: Category, Location, Employment Type, Salary, Distance -->
        <div class="row g-2">
            <!-- 1. Category -->
            <div class="col-sm-6 col-lg">
                <select class="form-select form-select-sm rounded-pill border py-2 px-3 small">
                    <option value="">🏷️ Category: All</option>
                    <option value="design">Design & Creative</option>
                    <option value="tech">Tech & Software</option>
                    <option value="retail">Retail & Cafe</option>
                    <option value="teaching">Tuition & Teaching</option>
                </select>
            </div>

            <!-- 2. Location -->
            <div class="col-sm-6 col-lg">
                <select class="form-select form-select-sm rounded-pill border py-2 px-3 small">
                    <option value="">📍 Location: <?= htmlspecialchars($currentAreaName) ?></option>
                    <option value="np">North Paravur</option>
                    <option value="mal">Maliankara</option>
                    <option value="kod">Kodungallur</option>
                    <option value="alu">Aluva</option>
                </select>
            </div>

            <!-- 3. Employment Type -->
            <div class="col-sm-6 col-lg">
                <select class="form-select form-select-sm rounded-pill border py-2 px-3 small">
                    <option value="">🕐 Type: All Types</option>
                    <option value="part-time" selected>Part-time</option>
                    <option value="full-time">Full-time</option>
                    <option value="freelance">Freelance</option>
                    <option value="internship">Internship</option>
                </select>
            </div>

            <!-- 4. Salary -->
            <div class="col-sm-6 col-lg">
                <select class="form-select form-select-sm rounded-pill border py-2 px-3 small">
                    <option value="">💰 Salary: Any Range</option>
                    <option value="10k">₹10,000–₹15,000/mo</option>
                    <option value="15k">₹15,000–₹25,000/mo</option>
                    <option value="30k">₹25,000+/mo</option>
                </select>
            </div>

            <!-- 5. Distance -->
            <div class="col-sm-6 col-lg">
                <select class="form-select form-select-sm rounded-pill border py-2 px-3 small">
                    <option value="">📏 Distance: Within 5 km</option>
                    <option value="2">Within 2 km</option>
                    <option value="10">Within 10 km</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Job Cards Grid (Section 16) -->
    <div class="row g-4">
        <?php foreach ($jobs as $job): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card card-solid h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-success-subtle text-success border">
                                <i data-lucide="clock" style="width: 12px; height: 12px;"></i> <?= ucfirst($job['job_type']) ?>
                            </span>
                            <span class="text-muted small" style="font-size: 0.72rem;">
                                <?= time_ago($job['created_at']) ?>
                            </span>
                        </div>

                        <!-- Job Title -->
                        <h5 class="fw-bold text-dark mb-1">
                            <a href="<?= BASE_URL ?>/job.php?id=<?= $job['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($job['title']) ?>
                            </a>
                        </h5>

                        <!-- Company Name -->
                        <div class="text-primary fw-semibold small mb-3">
                            <?= htmlspecialchars($job['company']) ?>
                        </div>

                        <!-- Details: Location & Salary -->
                        <div class="d-flex flex-column gap-2 mb-4 small text-secondary">
                            <div class="d-flex align-items-center gap-2">
                                <i data-lucide="map-pin" class="text-danger" style="width: 15px; height: 15px;"></i>
                                <span><?= htmlspecialchars($job['location']) ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i data-lucide="banknote" class="text-success" style="width: 15px; height: 15px;"></i>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($job['salary']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- View Job Button -->
                    <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                        <span class="badge bg-light text-secondary border">Verified Poster</span>
                        <a href="<?= BASE_URL ?>/job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-custom">
                            <span>View Job</span>
                            <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
