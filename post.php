<?php
// post.php
// LocalLoop Single Post Details & Discussion

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$postId = (int)($_GET['id'] ?? 0);
if (!$postId) {
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

// Increment view count
$pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$postId]);

// Fetch post
$userId = current_user_id();
$sql = "SELECT p.*, u.name as author_name, u.username as author_username, u.profile_photo as author_avatar, 
               u.role as author_role, u.reputation_points as author_rep,
               a.name as area_name, a.district as area_district,
               c.name as cat_name, c.color as cat_color, c.icon as cat_icon,
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'active') as total_comments,
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = " . ($userId ? (int)$userId : 0) . ") as user_liked,
               (SELECT COUNT(*) FROM saved_posts WHERE post_id = p.id AND user_id = " . ($userId ? (int)$userId : 0) . ") as user_saved
        FROM posts p
        JOIN users u ON p.user_id = u.id
        JOIN areas a ON p.area_id = a.id
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.status != 'rejected'";

$stmt = $pdo->prepare($sql);
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['flash_error'] = "Post not found or has been removed.";
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

// Fetch images
$imgStmt = $pdo->prepare("SELECT * FROM post_images WHERE post_id = ?");
$imgStmt->execute([$postId]);
$images = $imgStmt->fetchAll();

// Handle standard form comment submission (fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    require_login();
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $comment = trim($_POST['comment'] ?? '');
        if (!empty($comment)) {
            $pdo->prepare("INSERT INTO comments (post_id, user_id, comment, status) VALUES (?, ?, ?, 'active')")
                ->execute([$postId, $userId, $comment]);

            if ($post['user_id'] != $userId) {
                create_notification(
                    $pdo,
                    $post['user_id'],
                    $userId,
                    'comment',
                    $_SESSION['user_name'] . " commented on your post: \"" . substr($post['title'], 0, 30) . "...\"",
                    "post.php?id={$postId}"
                );
                award_reputation($pdo, $userId, 3, 'Community comment');
            }

            $_SESSION['flash_success'] = "Your comment has been posted!";
            header("Location: " . BASE_URL . "/post.php?id=" . $postId . "#comments");
            exit;
        }
    }
}

// Fetch comments
$cmtStmt = $pdo->prepare("SELECT c.*, u.name as user_name, u.username as user_username, u.profile_photo as user_avatar, u.role as user_role 
                         FROM comments c 
                         JOIN users u ON c.user_id = u.id 
                         WHERE c.post_id = ? AND c.status = 'active' 
                         ORDER BY c.created_at ASC");
$cmtStmt->execute([$postId]);
$comments = $cmtStmt->fetchAll();

$pageTitle = $post['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Back button -->
            <div class="mb-3">
                <a href="<?= BASE_URL ?>/feed.php" class="text-decoration-none text-muted small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Back to Community Feed
                </a>
            </div>

            <!-- Post Card -->
            <article class="card card-clean border-0 shadow-sm p-4 p-md-5 mb-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <a href="<?= BASE_URL ?>/profile.php?user=<?= $post['author_username'] ?>">
                            <img src="<?= get_avatar_url($post['author_avatar']) ?>" width="52" height="52" class="rounded-circle border" style="object-fit: cover;">
                        </a>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?= BASE_URL ?>/profile.php?user=<?= $post['author_username'] ?>" class="fw-bold text-dark text-decoration-none fs-5">
                                    <?= htmlspecialchars($post['author_name']) ?>
                                </a>
                                <?php if ($post['author_role'] === 'admin'): ?>
                                    <span class="badge bg-danger-subtle text-danger small"><i class="bi bi-shield-check"></i> Admin</span>
                                <?php elseif ($post['author_role'] === 'business'): ?>
                                    <span class="badge bg-success-subtle text-success small"><i class="bi bi-shop"></i> Business</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted small d-flex align-items-center gap-2 mt-0.5">
                                <span><i class="bi bi-geo-alt-fill text-danger me-0.5"></i> <?= htmlspecialchars($post['area_name']) ?>, <?= htmlspecialchars($post['area_district']) ?></span>
                                <span>&bull;</span>
                                <span><?= time_ago($post['created_at']) ?></span>
                                <span>&bull;</span>
                                <span><i class="bi bi-eye"></i> <?= $post['views'] ?> views</span>
                            </div>
                        </div>
                    </div>

                    <!-- Category Badge & Manage dropdown -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="category-pill" style="background-color: <?= htmlspecialchars($post['cat_color']) ?>18; color: <?= htmlspecialchars($post['cat_color']) ?>;">
                            <i class="bi <?= htmlspecialchars($post['cat_icon']) ?>"></i>
                            <?= htmlspecialchars($post['cat_name']) ?>
                        </span>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted p-1" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <?php if ($currentUser && ($currentUser['id'] == $post['user_id'] || $currentUser['role'] === 'admin')): ?>
                                    <li><a class="dropdown-item small" href="<?= BASE_URL ?>/edit-post.php?id=<?= $post['id'] ?>"><i class="bi bi-pencil me-2"></i> Edit Post</a></li>
                                    <li><a class="dropdown-item small text-danger" href="<?= BASE_URL ?>/delete-post.php?id=<?= $post['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?');"><i class="bi bi-trash me-2"></i> Delete Post</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li>
                                    <button class="dropdown-item small text-muted" data-bs-toggle="modal" data-bs-target="#reportModal" data-post-id="<?= $post['id'] ?>">
                                        <i class="bi bi-flag me-2 text-danger"></i> Report Content
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Title & Content -->
                <h3 class="fw-bold text-dark mb-3"><?= htmlspecialchars($post['title']) ?></h3>

                <?php if (!empty($post['location'])): ?>
                    <div class="mb-3">
                        <span class="badge bg-light text-muted border py-1.5 px-3 rounded-pill">
                            <i class="bi bi-pin-map-fill text-danger me-1"></i> <?= htmlspecialchars($post['location']) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="text-secondary fs-6 mb-4" style="line-height: 1.8; white-space: pre-line;">
                    <?= nl2br(htmlspecialchars($post['description'])) ?>
                </div>

                <!-- Attached Images -->
                <?php if (!empty($images)): ?>
                    <div class="row g-2 mb-4">
                        <?php foreach ($images as $img): ?>
                            <div class="col-12">
                                <img src="<?= BASE_URL ?>/uploads/posts/<?= htmlspecialchars($img['image_path']) ?>" class="post-img-cover border" alt="Attachment">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Post Actions Bar -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-4">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn-action-pill btn-like <?= $post['user_liked'] ? 'active-like' : '' ?>" data-post-id="<?= $post['id'] ?>">
                            <i class="bi bi-heart<?= $post['user_liked'] ? '-fill text-danger' : '' ?>"></i>
                            <span class="like-count"><?= $post['total_likes'] ?></span> Likes
                        </button>

                        <button class="btn-action-pill btn-save <?= $post['user_saved'] ? 'active-saved' : '' ?>" data-post-id="<?= $post['id'] ?>">
                            <i class="bi bi-bookmark<?= $post['user_saved'] ? '-fill text-warning' : '' ?>"></i>
                            <span class="save-text"><?= $post['user_saved'] ? 'Saved' : 'Save' ?></span>
                        </button>
                    </div>

                    <div>
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="navigator.clipboard.writeText(window.location.href); showToast('Link copied to clipboard!', 'info');">
                            <i class="bi bi-share-fill me-1"></i> Share
                        </button>
                    </div>
                </div>
            </article>

            <!-- Discussion & Comments Section -->
            <section class="card card-clean border-0 shadow-sm p-4 p-md-5 mb-5" id="comments">
                <h5 class="fw-bold mb-4">
                    <i class="bi bi-chat-dots-fill text-primary me-2"></i>
                    Community Discussion (<span class="comment-count-<?= $post['id'] ?>"><?= count($comments) ?></span>)
                </h5>

                <!-- Add Comment Form -->
                <?php if ($currentUser): ?>
                    <form method="POST" action="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>" class="mb-4">
                        <?= csrf_field() ?>
                        <div class="d-flex gap-3">
                            <img src="<?= get_avatar_url($currentUser['profile_photo']) ?>" width="40" height="40" class="rounded-circle border" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <textarea name="comment" class="form-control" rows="3" placeholder="Share your perspective, helpful info, or neighborly thoughts..." required></textarea>
                                <div class="text-end mt-2">
                                    <button type="submit" name="submit_comment" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold">
                                        Post Comment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-light border text-center p-3 mb-4 rounded-3">
                        <span class="text-muted">Want to join the conversation?</span>
                        <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-primary rounded-pill ms-2 px-3">Sign in to comment</a>
                    </div>
                <?php endif; ?>

                <!-- Comments Thread -->
                <div class="d-flex flex-column gap-3" id="commentsList-<?= $post['id'] ?>">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted small text-center py-3">No comments yet. Start the conversation!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="d-flex gap-3 p-3 rounded-3 bg-light border" id="comment-<?= $comment['id'] ?>">
                                <a href="<?= BASE_URL ?>/profile.php?user=<?= $comment['user_username'] ?>">
                                    <img src="<?= get_avatar_url($comment['user_avatar']) ?>" width="38" height="38" class="rounded-circle border" style="object-fit: cover;">
                                </a>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                            <a href="<?= BASE_URL ?>/profile.php?user=<?= $comment['user_username'] ?>" class="fw-bold text-dark text-decoration-none small">
                                                <?= htmlspecialchars($comment['user_name']) ?>
                                            </a>
                                            <?php if ($comment['user_role'] === 'admin'): ?>
                                                <span class="badge bg-danger-subtle text-danger" style="font-size: 0.65rem;">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted" style="font-size: 0.75rem;"><?= time_ago($comment['created_at']) ?></span>
                                            <?php if ($currentUser && ($currentUser['id'] == $comment['user_id'] || $currentUser['role'] === 'admin')): ?>
                                                <a href="<?= BASE_URL ?>/delete-comment.php?id=<?= $comment['id'] ?>&post_id=<?= $post['id'] ?>" class="text-danger small" title="Delete Comment" onclick="return confirm('Delete this comment?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-secondary small" style="white-space: pre-line;">
                                        <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
