<?php
// create-post.php
// LocalLoop Multi-Step Post Creation Experience (Section 22)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();
$currentUser = current_user($pdo);

$errors = [];
$title = '';
$description = '';
$categoryId = '';
$areaId = $currentUser['area_id'] ?? $browsingAreaId;
$location = '';
$postType = $_GET['type'] ?? 'announcement';

$categories = get_active_categories($pdo);
$areas = get_active_areas($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security token mismatch. Please try again.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $areaId = (int)($_POST['area_id'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $postType = trim($_POST['post_type'] ?? 'announcement');

        if (empty($title)) $errors[] = "Post title is required.";
        if (empty($description)) $errors[] = "Please provide post details/description.";
        if (!$categoryId) $errors[] = "Please select a category.";
        if (!$areaId) $errors[] = "Please select an area / locality.";

        if (empty($errors)) {
            // Map post type
            $dbType = 'general';
            if ($postType === 'announcement') $dbType = 'announcement';
            elseif ($postType === 'recommendation') $dbType = 'recommendation';
            elseif ($postType === 'alert') $dbType = 'alert';

            $stmt = $pdo->prepare("INSERT INTO posts (user_id, category_id, area_id, title, description, location, post_type, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')");
            $stmt->execute([$currentUser['id'], $categoryId, $areaId, $title, $description, $location, $dbType]);
            $newPostId = $pdo->lastInsertId();

            // Handle optional image upload
            if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                $uploadRes = upload_image_file($_FILES['post_image'], 'posts');
                if ($uploadRes['success']) {
                    $imgStmt = $pdo->prepare("INSERT INTO post_images (post_id, image_path) VALUES (?, ?)");
                    $imgStmt->execute([$newPostId, $uploadRes['fileName']]);
                }
            }

            // Award reputation points
            award_reputation($pdo, $currentUser['id'], 5, 'Published community post');

            flash('success', 'Your update has been published to your neighborhood feed!', 'success');
            header("Location: " . BASE_URL . "/post.php?id=" . $newPostId);
            exit;
        }
    }
}

$pageTitle = "Create Post";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3" style="max-width: 860px;">
    
    <!-- Multi-Step Wizard Progress (Section 22) -->
    <div class="card card-solid p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Create Community Post</h4>
                <p class="text-secondary small mb-0">Share information, alerts, or questions with neighbors in <?= htmlspecialchars($currentAreaName) ?>.</p>
            </div>
            <a href="<?= BASE_URL ?>/feed.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                &times; Cancel
            </a>
        </div>

        <!-- Step Indicator -->
        <div class="d-flex align-items-center justify-content-between position-relative mb-2">
            <div class="text-center flex-1" id="stepIndicator1">
                <div class="badge rounded-circle bg-primary text-white p-2 mb-1" style="width: 32px; height: 32px;">1</div>
                <div class="small fw-bold text-primary">Post Type</div>
            </div>
            <div class="flex-grow-1 bg-secondary bg-opacity-25" style="height: 2px;"></div>
            <div class="text-center flex-1" id="stepIndicator2">
                <div class="badge rounded-circle bg-light text-secondary border p-2 mb-1" style="width: 32px; height: 32px;">2</div>
                <div class="small fw-semibold text-secondary">Details</div>
            </div>
            <div class="flex-grow-1 bg-secondary bg-opacity-25" style="height: 2px;"></div>
            <div class="text-center flex-1" id="stepIndicator3">
                <div class="badge rounded-circle bg-light text-secondary border p-2 mb-1" style="width: 32px; height: 32px;">3</div>
                <div class="small fw-semibold text-secondary">Preview & Publish</div>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-4">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/create-post.php" enctype="multipart/form-data" id="postCreationForm">
        <?= csrf_field() ?>

        <!-- ==============================================
             STEP 1: CHOOSE POST TYPE (Section 22)
             ============================================== -->
        <div class="wizard-step" id="step1Container">
            <div class="card card-solid p-4 mb-4">
                <h5 class="fw-bold text-dark mb-2">Step 1: Choose Post Type</h5>
                <p class="text-secondary small mb-4">Select the format that best fits what you are sharing today:</p>

                <div class="row g-3">
                    <!-- Announcement -->
                    <div class="col-md-6 col-lg-4">
                        <label class="card h-100 p-3 border rounded-3 cursor-pointer post-type-option <?= $postType === 'announcement' ? 'border-primary bg-light' : '' ?>" style="cursor: pointer;">
                            <input type="radio" name="post_type" value="announcement" class="d-none" <?= $postType === 'announcement' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded bg-primary-subtle text-primary"><i data-lucide="megaphone" style="width: 22px; height: 22px;"></i></div>
                                <div>
                                    <div class="fw-bold small text-dark">Announcement</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Civic updates, news & alerts</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Event -->
                    <div class="col-md-6 col-lg-4">
                        <label class="card h-100 p-3 border rounded-3 cursor-pointer post-type-option <?= $postType === 'event' ? 'border-primary bg-light' : '' ?>" style="cursor: pointer;">
                            <input type="radio" name="post_type" value="event" class="d-none" <?= $postType === 'event' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded bg-purple-subtle text-purple" style="color: #7c3aed;"><i data-lucide="calendar" style="width: 22px; height: 22px;"></i></div>
                                <div>
                                    <div class="fw-bold small text-dark">Event</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Local meetups, drives & sports</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Job -->
                    <div class="col-md-6 col-lg-4">
                        <label class="card h-100 p-3 border rounded-3 cursor-pointer post-type-option <?= $postType === 'job' ? 'border-primary bg-light' : '' ?>" style="cursor: pointer;">
                            <input type="radio" name="post_type" value="job" class="d-none" <?= $postType === 'job' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded bg-success-subtle text-success"><i data-lucide="briefcase" style="width: 22px; height: 22px;"></i></div>
                                <div>
                                    <div class="fw-bold small text-dark">Job Opportunity</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Hire local talent or offer work</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Marketplace -->
                    <div class="col-md-6 col-lg-4">
                        <label class="card h-100 p-3 border rounded-3 cursor-pointer post-type-option <?= $postType === 'marketplace' ? 'border-primary bg-light' : '' ?>" style="cursor: pointer;">
                            <input type="radio" name="post_type" value="marketplace" class="d-none" <?= $postType === 'marketplace' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded bg-warning-subtle text-warning"><i data-lucide="tag" style="width: 22px; height: 22px;"></i></div>
                                <div>
                                    <div class="fw-bold small text-dark">Sell Item</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Used furniture, electronics, etc.</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Lost & Found -->
                    <div class="col-md-6 col-lg-4">
                        <label class="card h-100 p-3 border rounded-3 cursor-pointer post-type-option <?= $postType === 'lost_found' ? 'border-primary bg-light' : '' ?>" style="cursor: pointer;">
                            <input type="radio" name="post_type" value="lost_found" class="d-none" <?= $postType === 'lost_found' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded bg-orange-subtle text-orange" style="color: #ea580c;"><i data-lucide="search" style="width: 22px; height: 22px;"></i></div>
                                <div>
                                    <div class="fw-bold small text-dark">Lost & Found</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Misplaced wallet, pet, keys</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Community Help -->
                    <div class="col-md-6 col-lg-4">
                        <label class="card h-100 p-3 border rounded-3 cursor-pointer post-type-option <?= $postType === 'help' ? 'border-primary bg-light' : '' ?>" style="cursor: pointer;">
                            <input type="radio" name="post_type" value="help" class="d-none" <?= $postType === 'help' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded bg-danger-subtle text-danger"><i data-lucide="heart-handshake" style="width: 22px; height: 22px;"></i></div>
                                <div>
                                    <div class="fw-bold small text-dark">Help Request</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Assistance, shifting, donation</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Recommendation -->
                    <div class="col-md-6 col-lg-4">
                        <label class="card h-100 p-3 border rounded-3 cursor-pointer post-type-option <?= $postType === 'recommendation' ? 'border-primary bg-light' : '' ?>" style="cursor: pointer;">
                            <input type="radio" name="post_type" value="recommendation" class="d-none" <?= $postType === 'recommendation' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded bg-warning-subtle text-warning"><i data-lucide="star" style="width: 22px; height: 22px;"></i></div>
                                <div>
                                    <div class="fw-bold small text-dark">Recommendation</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Trusted plumbers, clinics, food</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="button" class="btn btn-primary-custom px-4" onclick="goToStep(2)">
                        <span>Continue to Details</span>
                        <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ==============================================
             STEP 2: FORM DETAILS (Section 22)
             ============================================== -->
        <div class="wizard-step d-none" id="step2Container">
            <div class="card card-solid p-4 mb-4">
                <h5 class="fw-bold text-dark mb-2">Step 2: Post Details</h5>
                <p class="text-secondary small mb-4">Provide title, description, location, and optional media for your neighbors:</p>

                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Post Title *</label>
                    <input type="text" name="title" id="inputPostTitle" class="form-control rounded-3 py-2" placeholder="e.g. Water supply will be temporarily unavailable tomorrow" value="<?= htmlspecialchars($title) ?>" required>
                </div>

                <!-- Category & Locality -->
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Channel / Category *</label>
                        <select name="category_id" id="inputPostCategory" class="form-select rounded-3 py-2" required>
                            <option value="">Select Category...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($categoryId == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Target Locality *</label>
                        <select name="area_id" id="inputPostArea" class="form-select rounded-3 py-2" required>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?= $a['id'] ?>" <?= ($areaId == $a['id']) ? 'selected' : '' ?>>
                                    📍 <?= htmlspecialchars($a['name']) ?> (<?= htmlspecialchars($a['district']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Specific Location / Landmark -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Specific Landmark / Location</label>
                    <input type="text" name="location" id="inputPostLocation" class="form-control rounded-3" placeholder="e.g. Ward 5 near Municipal Tank" value="<?= htmlspecialchars($location) ?>">
                </div>

                <!-- Description / Body -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Description *</label>
                    <textarea name="description" id="inputPostDescription" rows="5" class="form-control rounded-3" placeholder="Describe the details clearly so neighbors have all required information..." required><?= htmlspecialchars($description) ?></textarea>
                </div>

                <!-- Image Attachment -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Attach Photo / Banner (Optional)</label>
                    <input type="file" name="post_image" id="inputPostImage" class="form-control rounded-3" accept="image/*">
                    <div class="text-muted" style="font-size: 0.72rem; margin-top: 4px;">Supported: JPG, PNG, WEBP up to 5MB.</div>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-custom" onclick="goToStep(1)">
                        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                        <span>Back</span>
                    </button>
                    <button type="button" class="btn btn-primary-custom px-4" onclick="goToStep(3)">
                        <span>Review Post</span>
                        <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ==============================================
             STEP 3: LIVE PREVIEW & PUBLISH (Section 22)
             ============================================== -->
        <div class="wizard-step d-none" id="step3Container">
            <div class="card card-solid p-4 mb-4">
                <h5 class="fw-bold text-dark mb-2">Step 3: Preview</h5>
                <p class="text-secondary small mb-4">This is how your update will appear to everyone in <?= htmlspecialchars($currentAreaName) ?>:</p>

                <!-- Post Preview Mock Card -->
                <div class="post-card border p-4 bg-white mb-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="<?= get_avatar_url($currentUser['profile_photo'] ?? null) ?>" width="44" height="44" class="rounded-circle border" style="object-fit: cover;">
                        <div>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($currentUser['name']) ?></div>
                            <div class="text-muted small">
                                <span id="previewLocality">📍 <?= htmlspecialchars($currentAreaName) ?></span> &bull; <span>Just now</span>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary ms-auto" id="previewCategoryBadge">Announcement</span>
                    </div>

                    <h5 class="fw-bold text-dark mb-2" id="previewTitle">Post Title</h5>
                    <p class="text-secondary mb-3" id="previewDesc">Description will appear here...</p>

                    <div class="p-2.5 bg-light rounded text-secondary small d-flex align-items-center gap-2 mb-2">
                        <i data-lucide="map-pin" class="text-danger" style="width: 14px; height: 14px;"></i>
                        <span id="previewLocation">Near North Paravur</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-2 border-top text-muted small">
                        <span>❤️ 0 Likes</span> &bull; <span>💬 0 Comments</span> &bull; <span>🔖 Save</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between pt-3 border-top">
                    <button type="button" class="btn btn-outline-custom" onclick="goToStep(2)">
                        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                        <span>Back</span>
                    </button>
                    <button type="submit" class="btn btn-blue-accent px-4 py-2 fw-bold">
                        <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                        <span>Publish to Community</span>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
    // Step navigation script
    function goToStep(stepNum) {
        document.querySelectorAll('.wizard-step').forEach(step => step.classList.add('d-none'));
        
        // Reset indicator badges
        [1, 2, 3].forEach(num => {
            const ind = document.getElementById('stepIndicator' + num);
            const badge = ind.querySelector('.badge');
            if (num === stepNum) {
                badge.className = 'badge rounded-circle bg-primary text-white p-2 mb-1';
            } else if (num < stepNum) {
                badge.className = 'badge rounded-circle bg-success text-white p-2 mb-1';
            } else {
                badge.className = 'badge rounded-circle bg-light text-secondary border p-2 mb-1';
            }
        });

        if (stepNum === 1) {
            document.getElementById('step1Container').classList.remove('d-none');
        } else if (stepNum === 2) {
            document.getElementById('step2Container').classList.remove('d-none');
        } else if (stepNum === 3) {
            // Fill preview
            const titleVal = document.getElementById('inputPostTitle').value || 'Untitled Post';
            const descVal = document.getElementById('inputPostDescription').value || 'No description provided';
            const catSelect = document.getElementById('inputPostCategory');
            const catVal = catSelect.options[catSelect.selectedIndex]?.text || 'General';
            const locVal = document.getElementById('inputPostLocation').value || 'Locality area';

            document.getElementById('previewTitle').textContent = titleVal;
            document.getElementById('previewDesc').textContent = descVal;
            document.getElementById('previewCategoryBadge').textContent = catVal;
            document.getElementById('previewLocation').textContent = locVal;

            document.getElementById('step3Container').classList.remove('d-none');
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Post type radio selection styling
    document.querySelectorAll('.post-type-option input').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.post-type-option').forEach(l => l.classList.remove('border-primary', 'bg-light'));
            if (this.checked) {
                this.closest('.post-type-option').classList.add('border-primary', 'bg-light');
            }
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
