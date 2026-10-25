<?php
// login.php
// LocalLoop Premium Split-Screen Login Page (Section 5)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

if (is_logged_in()) {
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

$error = '';
$loginIdentifier = '';

// Handle Direct Quick Demo Switch
if (isset($_GET['demo'])) {
    $demoUsername = ($_GET['demo'] === 'admin') ? 'admin' : (($_GET['demo'] === 'rahul') ? 'rahulmenon' : 'amarthyan');
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$demoUsername]);
    $user = $stmt->fetch();
    if ($user) {
        login_user($user);
        header("Location: " . BASE_URL . "/feed.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security session expired. Please submit again.";
    } else {
        $loginIdentifier = trim($_POST['login_identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($loginIdentifier) || empty($password)) {
            $error = "Please enter your email, phone, or username, and password.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ? OR phone = ? LIMIT 1");
            $stmt->execute([$loginIdentifier, $loginIdentifier, $loginIdentifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['status'] === 'suspended') {
                    $error = "Your account has been suspended by community moderation.";
                } else {
                    login_user($user);
                    $redirect = $_SESSION['redirect_after_login'] ?? (BASE_URL . '/feed.php');
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $redirect);
                    exit;
                }
            } else {
                $error = "Invalid credentials. Please verify your email/phone and password.";
            }
        }
    }
}

$pageTitle = "Sign In";
$isAuthPage = true;
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-split-wrapper">
    <!-- Left Pane: Branding & Community Connectivity Illustration (Section 5) -->
    <div class="auth-branding-pane">
        <!-- Logo -->
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

        <!-- Main Branding Message -->
        <div class="auth-branding-content">
            <div class="badge bg-white bg-opacity-10 text-cyan px-3 py-1.5 rounded-pill mb-4 border border-white border-opacity-10 d-inline-flex align-items-center gap-2">
                <i data-lucide="sparkles" style="width: 14px; height: 14px; color: #38bdf8;"></i> Hyperlocal Social Network
            </div>
            <h1 class="auth-brand-headline">
                Everything happening around you, in one place.
            </h1>
            <p class="auth-brand-desc">
                Discover local announcements, connect with verified neighbors, join nearby events, find help, and buy & sell within your immediate locality.
            </p>

            <!-- Abstract Community Connectivity Illustration Card -->
            <div class="auth-illustration-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-circle bg-primary text-white"><i data-lucide="map-pin" style="width: 16px; height: 16px;"></i></span>
                        <div>
                            <div class="fw-bold small text-white">North Paravur Community Loop</div>
                            <div class="text-white-50" style="font-size: 0.75rem;">2,480 active neighbors connected</div>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success small">Live</span>
                </div>
                
                <div class="p-2.5 rounded-3 bg-white bg-opacity-5 border border-white border-opacity-10 small text-white-50 d-flex gap-2 align-items-center">
                    <i data-lucide="bell" style="width: 15px; height: 15px; color: #38bdf8;"></i>
                    <span>Ward 5 water pipeline repair announced 25m ago</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="small text-white-50">
            &copy; <?= date('Y') ?> LocalLoop Inc. Modern Hyperlocal Architecture.
        </div>
    </div>

    <!-- Right Pane: Login Card with Subtle Glassmorphism (Section 5) -->
    <div class="auth-form-pane">
        <div class="auth-card">
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">Welcome back</h3>
                <p class="text-secondary small">Enter your details to access your neighborhood hub.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2.5 px-3 rounded-3 small d-flex align-items-center gap-2 mb-4" role="alert">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="<?= BASE_URL ?>/login.php">
                <?= csrf_field() ?>

                <!-- Email or Phone -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Email or Phone</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i data-lucide="mail" style="width: 16px; height: 16px;"></i></span>
                        <input type="text" name="login_identifier" class="form-control border-start-0 ps-1" placeholder="name@example.com or phone" value="<?= htmlspecialchars($loginIdentifier) ?>" required autofocus>
                    </div>
                </div>

                <!-- Password & Forgot Password Link -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-secondary mb-0">Password</label>
                        <a href="<?= BASE_URL ?>/forgot-password.php" class="small text-primary text-decoration-none fw-semibold">Forgot Password?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i data-lucide="lock" style="width: 16px; height: 16px;"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-1" placeholder="Enter your password" required>
                    </div>
                </div>

                <!-- Primary Login Button -->
                <button type="submit" class="btn btn-primary-custom w-100 justify-content-center py-2.5 fw-bold mb-3">
                    <span>Login to LocalLoop</span>
                </button>

                <!-- Divider -->
                <div class="d-flex align-items-center my-3">
                    <hr class="flex-grow-1 border-secondary border-opacity-25 m-0">
                    <span class="px-3 text-muted small">or continue with</span>
                    <hr class="flex-grow-1 border-secondary border-opacity-25 m-0">
                </div>

                <!-- Continue with Google Button -->
                <a href="<?= BASE_URL ?>/feed.php?demo=amarthyan" class="btn btn-outline-custom w-100 justify-content-center py-2 mb-3 small">
                    <svg width="18" height="18" viewBox="0 0 24 24" class="me-2">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.65v3.03h3.88c2.27-2.09 3.665-5.17 3.665-9.12z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.03c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.13C3.26 21.36 7.33 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.29c-.25-.72-.38-1.49-.38-2.29s.13-1.57.38-2.29V6.57H1.26C.46 8.17 0 9.99 0 12s.46 3.83 1.26 5.43l4.02-3.14z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.26 6.57l4.02 3.14c.95-2.83 3.6-4.96 6.72-4.96z"/>
                    </svg>
                    Continue with Google
                </a>

                <!-- Create Account Link -->
                <div class="text-center small text-secondary mt-3">
                    Don't have an account? 
                    <a href="<?= BASE_URL ?>/register.php" class="text-primary fw-bold text-decoration-none">Create Account</a>
                </div>

                <!-- One-Click Instant Demo Credentials Switcher for Evaluation -->
                <div class="mt-4 pt-3 border-top text-center">
                    <span class="text-muted" style="font-size: 0.72rem;">Presentation Quick Demo Login:</span>
                    <div class="d-flex justify-content-center gap-2 mt-1 flex-wrap">
                        <a href="?demo=amarthyan" class="badge bg-light text-primary border text-decoration-none p-1.5">Amarthyan (User)</a>
                        <a href="?demo=rahul" class="badge bg-light text-secondary border text-decoration-none p-1.5">Rahul (Neighbor)</a>
                        <a href="?demo=admin" class="badge bg-light text-danger border text-decoration-none p-1.5">Admin Panel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
