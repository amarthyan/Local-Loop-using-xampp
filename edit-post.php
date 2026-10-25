<?php
// edit-post.php
// LocalLoop Edit Existing Post

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();
$currentUser = current_user($pdo);

$postId = (int)($_GET['id'] ?? 0);
if (!$postId) {
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['flash_error'] = "Post not found.";
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

// Authorization check: Only author or admin
if ($post['user_id'] != $currentUser['id'] && $currentUser['role'] !== 'admin') {
    $_SESSION['flash_error'] = "You are not authorized to edit this post.";
    header("Location: " . BASE_URL . "/post.php?id=" . $postId);
    exit;
}

$errors = [];
$title = $post['title'];
$description = $post['description'];
$categoryId = $post['category_id'];
$areaId = $post['area_id'];
$location = $post['location'];
$postType = $post['post_type'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security token mismatch.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $areaId = (int)($_POST['area_id'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $postType = in_array($_POST['post_type'] ?? '', ['general', 'announcement', 'recommendation', 'alert']) ? $_POST['post_type'] : 'general';

        if (empty($title)) $errors[] = "Title is required.";
        if (empty($description)) $errors[] = "Description is required.";

        if (empty($errors)) {
            $update = $pdo->prepare("UPDATE posts SET title = ?, description = ?, category_id = ?, area_id = ?, location = ?, post_type = ? WHERE id = ?");
            $update->execute([$title, $description, $categoryId, $areaId, $location, $postType, $postId]);

            // Optional new image
            if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                $uploadRes = upload_image_file($_FILES['post_image'], 'posts');
                if ($uploadRes['success']) {
                    $imgStmt = $pdo->prepare("INSERT INTO post_images (post_id, image_path) VALUES (?, ?)");
                    $imgStmt->execute([$postId, $uploadRes['fileName']]);
                }
            }

            if ($currentUser['role'] === 'admin' && $post['user_id'] != $currentUser['id']) {
                log_admin_action($pdo, $currentUser['id'], 'edit_post', 'posts', $postId, "Admin edited post title: {$title}");
            }

            $_SESSION['flash_success'] = "Post updated successfully!";
            header("Location: " . BASE_URL . "/post.php?id=" . $postId);
            exit;
        }
    }
}

$pageTitle = "Edit Post";
require_once __DIR__ . '/includes/header.php';
$categories = get_active_categories($pdo);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0">Edit Community Post</h3>
                    <a href="<?= BASE_URL ?>/post.php?id=<?= $postId ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Cancel</a>
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

                <form method="POST" action="<?= BASE_URL ?>/edit-post.php?id=<?= $postId ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Post Title</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($title) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Locality</label>
                            <select name="area_id" class="form-select" required>
                                <?php foreach ($allAreas as $area): ?>
                                    <option value="<?= $area['id'] ?>" <?= $areaId == $area['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($area['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Location / Landmark</label>
                            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($location) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Post Type</label>
                            <select name="post_type" class="form-select">
                                <option value="general" <?= $postType === 'general' ? 'selected' : '' ?>>General Discussion</option>
                                <option value="announcement" <?= $postType === 'announcement' ? 'selected' : '' ?>>Announcement</option>
                                <option value="recommendation" <?= $postType === 'recommendation' ? 'selected' : '' ?>>Recommendation</option>
                                <option value="alert" <?= $postType === 'alert' ? 'selected' : '' ?>>Urgent Notice</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="description" class="form-control" rows="6" required><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Add New Image (Optional)</label>
                        <input type="file" name="post_image" class="form-control" accept="image/*">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/post.php?id=<?= $postId ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
