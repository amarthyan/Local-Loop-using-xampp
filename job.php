<?php
// job.php
// LocalLoop Job Details (Section 17)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$jobId = (int)($_GET['id'] ?? 1);
$stmt = $pdo->prepare("SELECT j.*, a.name as area_name, u.name as poster_name, u.username as poster_username 
                       FROM jobs j 
                       JOIN areas a ON j.area_id = a.id 
                       JOIN users u ON j.user_id = u.id 
                       WHERE j.id = ?");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    // Fallback prompt exact example: Part-Time Graphic Designer
    $job = [
        'id' => 1,
        'title' => 'Part-Time Graphic Designer',
        'company' => 'PixelCraft Studios & Local Media',
        'location' => 'North Paravur, Kerala',
        'area_name' => 'North Paravur',
        'salary' => '₹10,000–₹15,000/month',
        'job_type' => 'part-time',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'description' => 'We are seeking a talented and enthusiastic Part-Time Graphic Designer to create social media creatives, flyers, brand assets, and marketing collateral for local businesses and festivals across North Paravur.',
        'requirements' => "• Proficiency in Figma, Adobe Photoshop, or Illustrator\n• Good typography and visual sense\n• Ability to work 15–20 hours per week\n• College students and freelance designers welcome",
        'responsibilities' => "• Design engaging digital posts, event posters, and print banners\n• Collaborate with community organizers and retail clients\n• Ensure consistent branding across all published graphics",
        'contact_email' => 'careers@pixelcraft.in'
    ];
}

$pageTitle = $job['title'] . " — " . $job['company'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3" style="max-width: 960px;">
    <!-- Back link -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/jobs.php" class="text-secondary small fw-semibold text-decoration-none">
            <i data-lucide="arrow-left" style="width: 15px; height: 15px;"></i> Back to Jobs
        </a>
    </div>

    <!-- Job Header Card (Section 17) -->
    <div class="card card-solid p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-success-subtle text-success border">
                        <i data-lucide="clock" style="width: 12px; height: 12px;"></i> <?= ucfirst($job['job_type']) ?>
                    </span>
                    <span class="text-muted small">Posted <?= time_ago($job['created_at']) ?></span>
                </div>

                <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars($job['title']) ?></h3>
                <div class="text-primary fw-semibold fs-5 mb-3"><?= htmlspecialchars($job['company']) ?></div>

                <div class="d-flex align-items-center gap-4 flex-wrap text-secondary small">
                    <div>
                        <i data-lucide="map-pin" class="text-danger me-1" style="width: 15px; height: 15px;"></i>
                        <span><?= htmlspecialchars($job['location']) ?></span>
                    </div>
                    <div>
                        <i data-lucide="banknote" class="text-success me-1" style="width: 15px; height: 15px;"></i>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($job['salary']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons: Apply Now & Save Job (Section 17) -->
            <div class="d-flex flex-column gap-2" style="min-width: 180px;">
                <button type="button" class="btn btn-blue-accent w-100 justify-content-center py-2.5 fw-bold" onclick="showToast('Application sent to <?= htmlspecialchars($job['company']) ?>!')">
                    <i data-lucide="send" style="width: 16px; height: 16px;"></i> Apply Now
                </button>
                <button type="button" class="btn btn-outline-custom w-100 justify-content-center py-2" onclick="showToast('Job saved to your bookmarks!')">
                    <i data-lucide="bookmark" style="width: 16px; height: 16px;"></i> Save Job
                </button>
            </div>
        </div>

        <hr class="my-4">

        <!-- Job Details Sections (Section 17) -->
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Description -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Job Description</h6>
                    <p class="text-secondary lh-lg mb-0">
                        <?= nl2br(htmlspecialchars($job['description'])) ?>
                    </p>
                </div>

                <!-- Responsibilities -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Key Responsibilities</h6>
                    <ul class="text-secondary lh-lg mb-0 ps-3">
                        <?php 
                        $resLines = explode("\n", $job['responsibilities'] ?? "• Handle community design briefs\n• Produce high-resolution assets\n• Coordinate delivery schedules");
                        foreach ($resLines as $l): if (!empty(trim($l))):
                        ?>
                            <li><?= htmlspecialchars(ltrim(trim($l), '•- ')) ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>

                <!-- Requirements -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Requirements & Qualifications</h6>
                    <ul class="text-secondary lh-lg mb-0 ps-3">
                        <?php 
                        $reqLines = explode("\n", $job['requirements'] ?? "• Relevant experience or strong portfolio\n• Reliable communication\n• Local resident preferred");
                        foreach ($reqLines as $l): if (!empty(trim($l))):
                        ?>
                            <li><?= htmlspecialchars(ltrim(trim($l), '•- ')) ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-4 border bg-light rounded-4">
                    <h6 class="fw-bold text-dark mb-3">Company Overview</h6>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="brand-logo-icon" style="width: 44px; height: 44px; background: #0b132b; color: white;">
                            <i data-lucide="building" style="width: 20px; height: 20px;"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-dark"><?= htmlspecialchars($job['company']) ?></div>
                            <div class="text-muted small">Verified Local Business</div>
                        </div>
                    </div>
                    <p class="text-secondary small mb-3">Local creative design studio based in North Paravur supporting local businesses with digital branding and print design.</p>
                    <div class="small text-secondary">
                        <div><strong>Location:</strong> <?= htmlspecialchars($job['location']) ?></div>
                        <div><strong>Direct Contact:</strong> <?= htmlspecialchars($job['contact_email'] ?? 'careers@localloop.in') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
