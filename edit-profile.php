<?php
// edit-profile.php
// LocalLoop Profile Settings

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();
$currentUser = current_user($pdo);

$errors = [];
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security token mismatch.";
    } else {
        $action = $_POST['action'] ?? 'update_profile';

        if ($action === 'update_profile') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $areaId = (int)($_POST['area_id'] ?? 0);

            if (empty($name)) {
                $errors[] = "Name cannot be empty.";
            }

            // Avatar Upload
            $newPhotoName = $currentUser['profile_photo'];
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadRes = upload_image_file($_FILES['avatar'], 'profiles');
                if ($uploadRes['success']) {
                    $newPhotoName = $uploadRes['fileName'];
                    $_SESSION['profile_photo'] = $newPhotoName;
                } else {
                    $errors[] = "Avatar error: " . $uploadRes['error'];
                }
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, bio = ?, area_id = ?, profile_photo = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $bio, $areaId, $newPhotoName, $currentUser['id']]);

                $_SESSION['user_name'] = $name;
                $_SESSION['user_area_id'] = $areaId;
                $_SESSION['flash_success'] = "Profile details updated successfully!";
                header("Location: " . BASE_URL . "/profile.php");
                exit;
            }
        } elseif ($action === 'change_password') {
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_new_password'] ?? '';

            if (!password_verify($currentPass, $currentUser['password_hash'])) {
                $errors[] = "Current password is incorrect.";
            } elseif (strlen($newPass) < 6) {
                $errors[] = "New password must be at least 6 characters.";
            } elseif ($newPass !== $confirmPass) {
                $errors[] = "New passwords do not match.";
            } else {
                $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $currentUser['id']]);
                $_SESSION['flash_success'] = "Password changed successfully!";
                header("Location: " . BASE_URL . "/profile.php");
                exit;
            }
        }
    }
}

$pageTitle = "Edit Profile";
require_once __DIR__ . '/includes/header.php';
$allAreas = get_active_areas($pdo);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0">Profile Settings</h3>
                <a href="<?= BASE_URL ?>/profile.php" class="btn btn-outline-secondary btn-sm rounded-pill">View Profile</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- General Info Form -->
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5 mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-gear text-primary me-2"></i> Personal Details</h5>
                
                <form method="POST" action="<?= BASE_URL ?>/edit-profile.php" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="<?= get_avatar_url($currentUser['profile_photo']) ?>" width="72" height="72" class="rounded-circle border" style="object-fit: cover;">
                        <div>
                            <label class="form-label fw-semibold small mb-1">Update Profile Photo</label>
                            <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*">
                            <div class="form-text small">Recommended square image (JPG, PNG, WebP).</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Full Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($currentUser['name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Username</label>
                            <input type="text" class="form-control bg-light" disabled value="<?= htmlspecialchars($currentUser['username']) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" class="form-control bg-light" disabled value="<?= htmlspecialchars($currentUser['email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Primary Locality / Town</label>
                        <select name="area_id" class="form-select">
                            <option value="">-- Choose Locality --</option>
                            <?php foreach ($allAreas as $area): ?>
                                <option value="<?= $area['id'] ?>" <?= $currentUser['area_id'] == $area['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($area['name']) ?> (<?= htmlspecialchars($area['district']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Bio / About You</label>
                        <textarea name="bio" class="form-control" rows="3" placeholder="Tell your neighborhood about your skills, interests, or background..."><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Save Profile</button>
                </form>
            </div>

            <!-- Password Change Form -->
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5">
                <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock text-danger me-2"></i> Security & Password</h5>

                <form method="POST" action="<?= BASE_URL ?>/edit-profile.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">New Password</label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Min 6 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Confirm New Password</label>
                            <input type="password" name="confirm_new_password" class="form-control" required placeholder="Repeat password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-danger rounded-pill px-4 fw-semibold">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
