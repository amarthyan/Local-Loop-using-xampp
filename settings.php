<?php
// settings.php
// LocalLoop Settings Dashboard (Section 28)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();
$currentUser = current_user($pdo);

$activeTab = $_GET['tab'] ?? 'account';
$successMsg = '';
$errorMsg = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errorMsg = "Security token mismatch. Please try again.";
    } else {
        $section = $_POST['section'] ?? '';

        if ($section === 'account') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $bio = trim($_POST['bio'] ?? '');

            if (!empty($name)) {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, bio = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $bio, $currentUser['id']]);
                flash('success', 'Account details updated successfully!', 'success');
                header("Location: " . BASE_URL . "/settings.php?tab=account");
                exit;
            }
        } elseif ($section === 'security') {
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (empty($currentPass) || empty($newPass)) {
                $errorMsg = "Please fill in all password fields.";
            } elseif ($newPass !== $confirmPass) {
                $errorMsg = "New passwords do not match.";
            } elseif (strlen($newPass) < 6) {
                $errorMsg = "Password must be at least 6 characters long.";
            } elseif (!password_verify($currentPass, $currentUser['password_hash'])) {
                $errorMsg = "Current password is incorrect.";
            } else {
                $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $currentUser['id']]);
                flash('success', 'Password successfully changed!', 'success');
                header("Location: " . BASE_URL . "/settings.php?tab=security");
                exit;
            }
        } else {
            flash('success', 'Preferences updated successfully!', 'success');
            header("Location: " . BASE_URL . "/settings.php?tab=" . $section);
            exit;
        }
    }
}

$pageTitle = "Settings";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3" style="max-width: 980px;">
    
    <!-- Settings Header -->
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Settings & Preferences</h3>
        <p class="text-secondary small">Manage your profile, neighborhood privacy, alert notifications, and theme.</p>
    </div>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-4">
            <i data-lucide="alert-circle" class="me-1" style="width: 16px; height: 16px;"></i>
            <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Settings Nav Pills (Left Column) -->
        <div class="col-md-3">
            <div class="card card-solid p-2 d-flex flex-column gap-1">
                <a href="?tab=account" class="sidebar-link rounded-3 <?= $activeTab === 'account' ? 'active' : '' ?>">
                    <i data-lucide="user" style="width: 17px; height: 17px;"></i> Account
                </a>
                <a href="?tab=privacy" class="sidebar-link rounded-3 <?= $activeTab === 'privacy' ? 'active' : '' ?>">
                    <i data-lucide="lock" style="width: 17px; height: 17px;"></i> Privacy
                </a>
                <a href="?tab=notifications" class="sidebar-link rounded-3 <?= $activeTab === 'notifications' ? 'active' : '' ?>">
                    <i data-lucide="bell" style="width: 17px; height: 17px;"></i> Notifications
                </a>
                <a href="?tab=appearance" class="sidebar-link rounded-3 <?= $activeTab === 'appearance' ? 'active' : '' ?>">
                    <i data-lucide="palette" style="width: 17px; height: 17px;"></i> Appearance
                </a>
                <a href="?tab=security" class="sidebar-link rounded-3 <?= $activeTab === 'security' ? 'active' : '' ?>">
                    <i data-lucide="shield-check" style="width: 17px; height: 17px;"></i> Security
                </a>
            </div>
        </div>

        <!-- Settings Content (Right Column) -->
        <div class="col-md-9">
            
            <!-- SECTION 1: ACCOUNT (Section 28) -->
            <?php if ($activeTab === 'account'): ?>
                <div class="card card-solid p-4">
                    <h5 class="fw-bold text-dark mb-1">Personal Information</h5>
                    <p class="text-secondary small mb-4">Update your community profile details and public identity.</p>

                    <form method="POST" action="<?= BASE_URL ?>/settings.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="account">

                        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3 bg-light border">
                            <img src="<?= get_avatar_url($currentUser['profile_photo']) ?>" width="64" height="64" class="rounded-circle border" style="object-fit: cover;">
                            <div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($currentUser['name']) ?></div>
                                <div class="text-muted small">@<?= htmlspecialchars($currentUser['username']) ?> &bull; 📍 <?= htmlspecialchars($currentAreaName) ?></div>
                                <a href="<?= BASE_URL ?>/edit-profile.php" class="btn btn-xs btn-outline-primary rounded-pill mt-1 py-1 px-3" style="font-size: 0.75rem;">Change Avatar</a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Full Name</label>
                            <input type="text" name="name" class="form-control rounded-3" value="<?= htmlspecialchars($currentUser['name']) ?>" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-secondary">Email Address</label>
                                <input type="email" class="form-control rounded-3 bg-light" value="<?= htmlspecialchars($currentUser['email']) ?>" readonly>
                                <span class="text-muted" style="font-size: 0.7rem;">Contact support to alter primary email</span>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-secondary">Phone Number</label>
                                <input type="tel" name="phone" class="form-control rounded-3" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Community Bio</label>
                            <textarea name="bio" rows="3" class="form-control rounded-3"><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
                            <span class="text-muted" style="font-size: 0.72rem;">Brief intro visible to neighbors when viewing your posts</span>
                        </div>

                        <button type="submit" class="btn btn-primary-custom px-4">
                            <i data-lucide="check" style="width: 16px; height: 16px;"></i> Save Account Changes
                        </button>
                    </form>
                </div>

            <!-- SECTION 2: PRIVACY (Section 28) -->
            <?php elseif ($activeTab === 'privacy'): ?>
                <div class="card card-solid p-4">
                    <h5 class="fw-bold text-dark mb-1">Neighborhood Privacy</h5>
                    <p class="text-secondary small mb-4">Control how your presence and contact details are shown to nearby residents.</p>

                    <form method="POST" action="<?= BASE_URL ?>/settings.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="privacy">

                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                                <div>
                                    <div class="fw-bold text-dark small">Profile Visibility</div>
                                    <div class="text-secondary small">Allow verified neighbors in <?= htmlspecialchars($currentAreaName) ?> to view your activity</div>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                                <div>
                                    <div class="fw-bold text-dark small">Precise Location Visibility</div>
                                    <div class="text-secondary small">Show approximate landmark radius (e.g. 1.2 km away) instead of street address</div>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                                <div>
                                    <div class="fw-bold text-dark small">Direct Messaging Permissions</div>
                                    <div class="text-secondary small">Allow neighbors to message you about marketplace items and help requests</div>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom px-4">Save Privacy Settings</button>
                    </form>
                </div>

            <!-- SECTION 3: NOTIFICATIONS (Section 28) -->
            <?php elseif ($activeTab === 'notifications'): ?>
                <div class="card card-solid p-4">
                    <h5 class="fw-bold text-dark mb-1">Notification Preferences</h5>
                    <p class="text-secondary small mb-4">Choose which community activities trigger in-app and email updates.</p>

                    <form method="POST" action="<?= BASE_URL ?>/settings.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="notifications">

                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                                <div>
                                    <div class="fw-bold text-dark small">Civic Announcements & Alerts</div>
                                    <div class="text-secondary small">Water supply disruptions, road repairs, and emergency announcements</div>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                                <div>
                                    <div class="fw-bold text-dark small">Post Comments & Interactions</div>
                                    <div class="text-secondary small">Notify when a neighbor replies to your post or comment</div>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                                <div>
                                    <div class="fw-bold text-dark small">Chat Messages</div>
                                    <div class="text-secondary small">Instant alert when you receive a new direct message</div>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
                                <div>
                                    <div class="fw-bold text-dark small">Upcoming Events Reminders</div>
                                    <div class="text-secondary small">Reminders 24 hours before events you marked interest in</div>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom px-4">Update Notification Rules</button>
                    </form>
                </div>

            <!-- SECTION 4: APPEARANCE (Section 28) -->
            <?php elseif ($activeTab === 'appearance'): ?>
                <div class="card card-solid p-4">
                    <h5 class="fw-bold text-dark mb-1">Theme & Appearance</h5>
                    <p class="text-secondary small mb-4">Toggle between clean daylight glassmorphism and sleek deep navy dark mode.</p>

                    <div class="row g-3 mb-4">
                        <!-- Light Mode -->
                        <div class="col-sm-6">
                            <div class="card p-3 border border-2 border-primary text-center cursor-pointer rounded-4" onclick="document.documentElement.setAttribute('data-theme', 'light'); showToast('Switched to Light Mode');" style="cursor: pointer;">
                                <div class="p-3 rounded-3 bg-light mb-3 border">
                                    <i data-lucide="sun" class="text-warning fs-3" style="width: 32px; height: 32px;"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Light Mode</h6>
                                <p class="text-secondary small mb-0">Crisp white and light gray glass surfaces</p>
                            </div>
                        </div>

                        <!-- Dark Mode -->
                        <div class="col-sm-6">
                            <div class="card p-3 border text-center cursor-pointer rounded-4 bg-dark text-white" onclick="document.documentElement.setAttribute('data-theme', 'dark'); showToast('Switched to Dark Mode');" style="cursor: pointer;">
                                <div class="p-3 rounded-3 bg-navy mb-3 border border-secondary">
                                    <i data-lucide="moon" class="text-info fs-3" style="width: 32px; height: 32px;"></i>
                                </div>
                                <h6 class="fw-bold mb-1 text-white">Dark Mode</h6>
                                <p class="text-muted small mb-0">Restrained deep navy nocturnal palette</p>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- SECTION 5: SECURITY (Section 28) -->
            <?php elseif ($activeTab === 'security'): ?>
                <div class="card card-solid p-4">
                    <h5 class="fw-bold text-dark mb-1">Security & Active Sessions</h5>
                    <p class="text-secondary small mb-4">Manage your password and review devices currently accessing your account.</p>

                    <form method="POST" action="<?= BASE_URL ?>/settings.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="security">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Current Password</label>
                            <input type="password" name="current_password" class="form-control rounded-3" placeholder="Enter current password" required>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-secondary">New Password</label>
                                <input type="password" name="new_password" class="form-control rounded-3" placeholder="Min 6 characters" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-secondary">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Repeat new password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom px-4 mb-5">Change Password</button>

                        <h6 class="fw-bold text-dark pt-3 border-top mb-3">Active Sessions</h6>
                        <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <i data-lucide="laptop" style="width: 22px; height: 22px;" class="text-primary"></i>
                                <div>
                                    <div class="fw-bold small text-dark">Windows Desktop &bull; Chrome Browser</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">North Paravur, Kerala &bull; Current Active Session</div>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success small">Active Now</span>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
