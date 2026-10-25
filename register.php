<?php
// register.php
// LocalLoop User Registration Page (Section 6)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

if (is_logged_in()) {
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

$error = '';
$name = '';
$email = '';
$phone = '';
$areaId = '';

$areas = get_active_areas($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Session expired. Please try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $areaId = (int)($_POST['area_id'] ?? 0);

        if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword) || empty($areaId)) {
            $error = "Please fill in all required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            // Check if email already registered
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = "An account with this email already exists.";
            } else {
                // Auto-generate username from name
                $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $name)[0]));
                $username = $baseUsername . rand(100, 999);
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                $insStmt = $pdo->prepare("INSERT INTO users (name, username, email, phone, password_hash, area_id, role, status, reputation_points) 
                                          VALUES (?, ?, ?, ?, ?, ?, 'user', 'active', 50)");
                if ($insStmt->execute([$name, $username, $email, $phone, $passwordHash, $areaId])) {
                    $newUserId = $pdo->lastInsertId();
                    
                    // Fetch created user and log them in
                    $uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $uStmt->execute([$newUserId]);
                    $newUser = $uStmt->fetch();
                    login_user($newUser);

                    $_SESSION['browsing_area_id'] = $areaId;
                    
                    // Redirect to Onboarding Step (Section 6)
                    header("Location: " . BASE_URL . "/onboarding.php");
                    exit;
                } else {
                    $error = "Failed to create account. Please try again.";
                }
            }
        }
    }
}

$pageTitle = "Create Account";
$isAuthPage = true;
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-split-wrapper">
    <!-- Left Pane: Branding & Community Context -->
    <div class="auth-branding-pane">
        <a href="<?= BASE_URL ?>/index.php" class="brand-logo-link">
            <div class="brand-logo-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 3"/>
                    <circle cx="12" cy="12" r="2" fill="currentColor"/>
                </svg>
            </div>
            <div>
                <div class="brand-logo-text">Local<span>Loop</span></div>
                <span class="brand-tagline-sub text-white-50">Your locality. Your loop.</span>
            </div>
        </a>

        <div class="auth-branding-content">
            <h1 class="auth-brand-headline">
                Connect with the people right around your corner.
            </h1>
            <p class="auth-brand-desc">
                Join thousands of verified neighbors in Kerala. Share updates, find trusted local services, borrow tools, and build a stronger community together.
            </p>

            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3 text-white-50 small">
                    <span class="p-2 rounded-circle bg-white bg-opacity-10 text-cyan"><i data-lucide="check" style="width: 14px; height: 14px;"></i></span>
                    <span>Hyperlocal ward-based news and emergency alerts</span>
                </div>
                <div class="d-flex align-items-center gap-3 text-white-50 small">
                    <span class="p-2 rounded-circle bg-white bg-opacity-10 text-cyan"><i data-lucide="check" style="width: 14px; height: 14px;"></i></span>
                    <span>Zero spam with transparent neighbor reputation system</span>
                </div>
                <div class="d-flex align-items-center gap-3 text-white-50 small">
                    <span class="p-2 rounded-circle bg-white bg-opacity-10 text-cyan"><i data-lucide="check" style="width: 14px; height: 14px;"></i></span>
                    <span>Direct buy, sell, and community help exchanges</span>
                </div>
            </div>
        </div>

        <div class="small text-white-50">
            &copy; <?= date('Y') ?> LocalLoop Inc. All rights reserved.
        </div>
    </div>

    <!-- Right Pane: Clean Registration Form (Section 6) -->
    <div class="auth-form-pane">
        <div class="auth-card" style="max-width: 480px;">
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">Create Account</h3>
                <p class="text-secondary small">Join your local neighborhood network in under a minute.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2.5 px-3 rounded-3 small d-flex align-items-center gap-2 mb-4" role="alert">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/register.php">
                <?= csrf_field() ?>

                <!-- Full Name -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Full Name</label>
                    <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Amarthyan S S" value="<?= htmlspecialchars($name) ?>" required autofocus>
                </div>

                <!-- Email & Phone in 2 Columns -->
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-3" placeholder="name@domain.com" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Phone Number</label>
                        <input type="tel" name="phone" class="form-control rounded-3" placeholder="9847000000" value="<?= htmlspecialchars($phone) ?>" required>
                    </div>
                </div>

                <!-- Password & Confirm Password in 2 Columns -->
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Password</label>
                        <input type="password" name="password" class="form-control rounded-3" placeholder="Min 6 characters" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Repeat password" required>
                    </div>
                </div>

                <!-- Select Local Area (Section 6) -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary d-flex justify-content-between">
                        <span>Select Local Area</span>
                        <span class="text-muted fw-normal" style="font-size: 0.72rem;">Your primary loop</span>
                    </label>
                    <select name="area_id" class="form-select rounded-3 py-2" required>
                        <option value="">Choose your neighborhood...</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= ($areaId == $a['id'] || stripos($a['name'], 'North Paravur') !== false) ? 'selected' : '' ?>>
                                📍 <?= htmlspecialchars($a['name']) ?> (<?= htmlspecialchars($a['district']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Primary Submit Button -->
                <button type="submit" class="btn btn-primary-custom w-100 justify-content-center py-2.5 fw-bold mb-3">
                    <span>Create Account</span>
                </button>

                <div class="text-center small text-secondary mt-3">
                    Already have an account? 
                    <a href="<?= BASE_URL ?>/login.php" class="text-primary fw-bold text-decoration-none">Sign In</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
