<?php
// forgot-password.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$message = '';
$isResetSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Security token mismatch.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $isResetSuccess = true;
            $message = "Password reset instructions simulated: In production, a secure verification token will be dispatched to " . htmlspecialchars($email) . ". Demo password for standard accounts is 'password123'.";
        } else {
            $message = "No account found associated with that email address.";
        }
    }
}

$pageTitle = "Reset Password";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card card-clean border-0 shadow-lg p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-icon mx-auto mb-2" style="width: 48px; height: 48px;">
                        <i class="bi bi-key-fill fs-3 text-white"></i>
                    </div>
                    <h3 class="fw-bold">Password Recovery</h3>
                    <p class="text-muted small">Enter your verified email to reset your account password.</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $isResetSuccess ? 'success' : 'danger' ?> mb-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/forgot-password.php">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                    </div>
                    <button type="submit" class="btn btn-accent-gradient w-100 py-2 fw-semibold rounded-pill mb-3">
                        Send Reset Link
                    </button>
                    <div class="text-center small">
                        <a href="<?= BASE_URL ?>/login.php" class="text-primary text-decoration-none"><i class="bi bi-arrow-left"></i> Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
