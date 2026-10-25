<?php
// help-request.php
// LocalLoop Help Request Details & Offer Assistance

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) {
    header("Location: " . BASE_URL . "/help.php");
    exit;
}

$stmt = $pdo->prepare("SELECT h.*, a.name as area_name, u.name as requester_name, u.username as requester_username, u.profile_photo as requester_avatar, u.phone as requester_phone,
                              r.name as resolved_by_name, r.username as resolved_by_username 
                       FROM help_requests h 
                       JOIN areas a ON h.area_id = a.id 
                       JOIN users u ON h.user_id = u.id 
                       LEFT JOIN users r ON h.resolved_by_user_id = r.id 
                       WHERE h.id = ?");
$stmt->execute([$requestId]);
$req = $stmt->fetch();

if (!$req) {
    $_SESSION['flash_error'] = "Help request not found.";
    header("Location: " . BASE_URL . "/help.php");
    exit;
}

$currentUser = current_user($pdo);
$isRequester = $currentUser && ($currentUser['id'] == $req['user_id'] || $currentUser['role'] === 'admin');

// Handle Mark as Resolved
if ($isRequester && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_request'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $helperUserId = !empty($_POST['helper_user_id']) ? (int)$_POST['helper_user_id'] : null;

        $pdo->prepare("UPDATE help_requests SET status = 'resolved', resolved_by_user_id = ? WHERE id = ?")
            ->execute([$helperUserId, $requestId]);

        if ($helperUserId) {
            // Award reputation points for solving help
            award_reputation($pdo, $helperUserId, 15, 'Resolved community help request');
            create_notification(
                $pdo,
                $helperUserId,
                $currentUser['id'],
                'help_resolved',
                "{$currentUser['name']} marked their help request as resolved and recognized you as the helper! +15 Reputation awarded.",
                "help-request.php?id={$requestId}"
            );
        }

        $_SESSION['flash_success'] = "Thank you! This community help request is now marked as resolved.";
        header("Location: " . BASE_URL . "/help-request.php?id=" . $requestId);
        exit;
    }
}

// Fetch Help Offers
$offersStmt = $pdo->prepare("SELECT ho.*, u.name, u.username, u.profile_photo, u.reputation_points 
                            FROM help_offers ho 
                            JOIN users u ON ho.user_id = u.id 
                            WHERE ho.request_id = ? 
                            ORDER BY ho.created_at ASC");
$offersStmt->execute([$requestId]);
$offers = $offersStmt->fetchAll();

$pageTitle = $req['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <a href="<?= BASE_URL ?>/help.php" class="text-decoration-none text-muted small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Back to Help Network
                </a>
            </div>

            <!-- Help Request Card -->
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5 mb-4 border-start border-4 <?= $req['status'] === 'resolved' ? 'border-success' : 'border-danger' ?>">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-<?= $req['urgency'] === 'Critical' ? 'danger' : 'warning-subtle text-warning-emphasis' ?> px-3 py-1.5 fs-6 rounded-pill">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($req['urgency']) ?> Urgency
                    </span>
                    <span class="badge bg-<?= $req['status'] === 'resolved' ? 'success' : 'danger' ?>">
                        <?= $req['status'] === 'resolved' ? 'Resolved' : 'Needs Help' ?>
                    </span>
                </div>

                <h2 class="fw-bold text-dark mb-3"><?= htmlspecialchars($req['title']) ?></h2>

                <div class="row g-2 p-3 bg-light rounded-3 mb-4 small">
                    <div class="col-sm-6">
                        <span class="text-muted">Target Locality:</span>
                        <span class="fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($req['area_name']) ?></span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted">Exact Location / Hospital:</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($req['location']) ?></span>
                    </div>
                </div>

                <h5 class="fw-bold mb-2">Request Overview</h5>
                <div class="text-secondary fs-6 mb-4" style="line-height: 1.8; white-space: pre-line;">
                    <?= nl2br(htmlspecialchars($req['description'])) ?>
                </div>

                <?php if ($req['status'] === 'resolved'): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4">
                        <i class="bi bi-patch-check-fill fs-3 me-3 text-success"></i>
                        <div>
                            <strong>This request was successfully resolved!</strong>
                            <?php if (!empty($req['resolved_by_name'])): ?>
                                <div>Special thanks to neighbor <a href="<?= BASE_URL ?>/profile.php?user=<?= $req['resolved_by_username'] ?>" class="fw-bold text-success text-decoration-none"><?= htmlspecialchars($req['resolved_by_name']) ?></a> for stepping up!</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                    <div class="d-flex align-items-center gap-2">
                        <img src="<?= get_avatar_url($req['requester_avatar']) ?>" width="36" height="36" class="rounded-circle border">
                        <div>
                            <div class="fw-bold text-dark small leading-tight"><?= htmlspecialchars($req['requester_name']) ?></div>
                            <span class="text-muted small" style="font-size: 0.72rem;">Requested <?= time_ago($req['created_at']) ?></span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <?php if ($req['status'] === 'open'): ?>
                            <?php if (!$isRequester): ?>
                                <button class="btn btn-danger rounded-pill px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#offerHelpModal">
                                    <i class="bi bi-hand-thumbs-up-fill me-1"></i> Offer to Help
                                </button>
                            <?php else: ?>
                                <button class="btn btn-success rounded-pill px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#resolveModal">
                                    <i class="bi bi-check-circle-fill me-1"></i> Mark Resolved
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Helper Offers Section -->
            <div class="card card-clean border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-people-fill text-danger me-2"></i> Neighborly Offers to Help (<?= count($offers) ?>)</h5>

                <?php if (empty($offers)): ?>
                    <p class="text-muted small mb-0">No offers received yet. If you can assist, click "Offer to Help" above!</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($offers as $offer): ?>
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= get_avatar_url($offer['profile_photo']) ?>" width="34" height="34" class="rounded-circle border">
                                        <div>
                                            <a href="<?= BASE_URL ?>/profile.php?user=<?= $offer['username'] ?>" class="fw-bold text-dark text-decoration-none small">
                                                <?= htmlspecialchars($offer['name']) ?>
                                            </a>
                                            <span class="badge bg-secondary-subtle text-secondary small ms-1"><?= $offer['reputation_points'] ?> pts</span>
                                        </div>
                                    </div>
                                    <span class="text-muted small"><?= time_ago($offer['created_at']) ?></span>
                                </div>
                                <p class="text-dark small mb-2" style="white-space: pre-line;"><?= nl2br(htmlspecialchars($offer['message'])) ?></p>
                                <?php if (!empty($offer['phone'])): ?>
                                    <div class="text-success small fw-semibold">
                                        <i class="bi bi-telephone-fill me-1"></i> Phone: <a href="tel:<?= htmlspecialchars($offer['phone']) ?>" class="text-success"><?= htmlspecialchars($offer['phone']) ?></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Offer Help Modal -->
<div class="modal fade" id="offerHelpModal" tabindex="-1" aria-labelledby="offerHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fs-6 fw-bold" id="offerHelpModalLabel"><i class="bi bi-heart-fill text-danger me-2"></i> Offer Assistance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="offerHelpForm">
                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                <div class="modal-body">
                    <p class="small text-muted mb-3">Thank you for stepping up! Let the neighbor know what you can provide and how to reach you.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Your Offer / Message</label>
                        <textarea name="message" class="form-control" rows="3" required placeholder="e.g. I have a spare wheelchair at our clinic you can borrow, or I am available with a car at 3 PM..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Your Contact Phone</label>
                        <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" placeholder="9847XXXXXX">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Submit Offer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1" aria-labelledby="resolveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fs-6 fw-bold" id="resolveModalLabel"><i class="bi bi-check-circle-fill text-success me-2"></i> Resolve & Award Reputation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/help-request.php?id=<?= $req['id'] ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="resolve_request" value="1">
                <div class="modal-body">
                    <p class="small text-muted mb-3">Did a neighbor assist you? Selecting them will award them community reputation points and helper badges!</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Select the Neighbor Who Helped</label>
                        <select name="helper_user_id" class="form-select">
                            <option value="">-- Choose helper from offers --</option>
                            <?php foreach ($offers as $off): ?>
                                <option value="<?= $off['user_id'] ?>"><?= htmlspecialchars($off['name']) ?> (@<?= htmlspecialchars($off['username']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">Confirm & Mark Resolved</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const offerForm = document.getElementById('offerHelpForm');
    if (offerForm) {
        offerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            try {
                const res = await fetch(`${BASE_URL}/ajax/help_offer.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    showToast(data.message || 'Error sending offer', 'danger');
                }
            } catch (err) {
                console.error(err);
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
