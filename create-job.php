<?php
// create-job.php
// LocalLoop Post Job Opening

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();
$currentUser = current_user($pdo);

$errors = [];
$title = '';
$company = $currentUser['name'];
$jobType = 'full-time';
$salary = '';
$location = '';
$areaId = $currentUser['area_id'] ?? '';
$description = '';
$requirements = '';
$contactEmail = $currentUser['email'];
$contactPhone = $currentUser['phone'] ?? '';
$deadline = date('Y-m-d', strtotime('+30 days'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security token mismatch.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $jobType = in_array($_POST['job_type'] ?? '', ['full-time', 'part-time', 'freelance', 'internship']) ? $_POST['job_type'] : 'full-time';
        $salary = trim($_POST['salary'] ?? 'Negotiable');
        $location = trim($_POST['location'] ?? '');
        $areaId = (int)($_POST['area_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');

        if (empty($title)) $errors[] = "Job title is required.";
        if (empty($company)) $errors[] = "Company or Business name is required.";
        if (empty($description)) $errors[] = "Job description is required.";
        if (empty($contactEmail)) $errors[] = "Contact email is required.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO jobs (user_id, area_id, title, company, job_type, salary, location, description, requirements, contact_email, contact_phone, deadline, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')");
            $stmt->execute([$currentUser['id'], $areaId, $title, $company, $jobType, $salary, $location, $description, $requirements, $contactEmail, $contactPhone, $deadline]);
            $jobId = $pdo->lastInsertId();

            award_reputation($pdo, $currentUser['id'], 8, 'Posted local job opening');

            $_SESSION['flash_success'] = "Job opening published to local candidates!";
            header("Location: " . BASE_URL . "/job.php?id=" . $jobId);
            exit;
        }
    }
}

$pageTitle = "Post a Local Job Opening";
require_once __DIR__ . '/includes/header.php';
$allAreas = get_active_areas($pdo);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="brand-icon" style="width: 44px; height: 44px; background: linear-gradient(135deg, #059669, #34d399);">
                        <i class="bi bi-briefcase-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">Post a Job Opening</h3>
                        <p class="text-muted small mb-0">Connect directly with qualified local talent, students, and workers.</p>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/create-job.php">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Job Title / Role</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Cafe Barista & Billing Staff" value="<?= htmlspecialchars($title) ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Business / Organization Name</label>
                            <input type="text" name="company" class="form-control" required value="<?= htmlspecialchars($company) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Job Type</label>
                            <select name="job_type" class="form-select">
                                <option value="full-time" <?= $jobType === 'full-time' ? 'selected' : '' ?>>Full-Time</option>
                                <option value="part-time" <?= $jobType === 'part-time' ? 'selected' : '' ?>>Part-Time</option>
                                <option value="freelance" <?= $jobType === 'freelance' ? 'selected' : '' ?>>Freelance / Gig</option>
                                <option value="internship" <?= $jobType === 'internship' ? 'selected' : '' ?>>Internship</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Offered Salary / Stipend</label>
                            <input type="text" name="salary" class="form-control" placeholder="e.g. ₹18,000 - ₹22,000 / month" value="<?= htmlspecialchars($salary) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Application Deadline</label>
                            <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($deadline) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Locality / Town</label>
                            <select name="area_id" class="form-select" required>
                                <?php foreach ($allAreas as $area): ?>
                                    <option value="<?= $area['id'] ?>" <?= $areaId == $area['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($area['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Precise Workplace Location</label>
                            <input type="text" name="location" class="form-control" required placeholder="e.g. Near Bus Stand, South Nada" value="<?= htmlspecialchars($location) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Job Description & Responsibilities</label>
                        <textarea name="description" class="form-control" rows="5" required placeholder="Outline daily duties, working hours, and benefits..."><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Candidate Requirements / Skills (Optional)</label>
                        <textarea name="requirements" class="form-control" rows="3" placeholder="Education, languages, experience, or equipment required..."><?= htmlspecialchars($requirements) ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" required value="<?= htmlspecialchars($contactEmail) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Phone (Optional)</label>
                            <input type="tel" name="contact_phone" class="form-control" value="<?= htmlspecialchars($contactPhone) ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/jobs.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">Publish Job Listing</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
