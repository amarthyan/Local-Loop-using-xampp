<?php
// includes/footer.php
// LocalLoop Unified SaaS Application Footer & Global Scripts

$isAuthPage = $isAuthPage ?? false;
?>

<?php if (!$isAuthPage): ?>
            </div><!-- /.app-content-body -->
        </main><!-- /.app-main -->
    </div><!-- /.app-layout -->

    <!-- Mobile Bottom Navigation -->
    <?php require_once __DIR__ . '/bottom_nav.php'; ?>
<?php else: ?>
        </div><!-- /.container-fluid -->
    </div><!-- /.auth-standalone-wrapper -->
<?php endif; ?>

<!-- Global Content Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-modal);">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fs-6 fw-bold text-danger d-flex align-items-center gap-2" id="reportModalLabel">
                    <i data-lucide="flag" style="width: 18px; height: 18px;"></i> Report Inappropriate Content
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="globalReportForm" method="POST" action="<?= BASE_URL ?>/ajax/report.php">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <input type="hidden" name="post_id" id="reportPostId" value="">
                    <input type="hidden" name="comment_id" id="reportCommentId" value="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Reason for reporting</label>
                        <select name="reason" class="form-select rounded-3 py-2" required>
                            <option value="">Select a reason...</option>
                            <option value="Spam">Spam or unwanted advertising</option>
                            <option value="Harassment">Harassment or abusive language</option>
                            <option value="False Information">Misleading or false local news</option>
                            <option value="Scam / Fraud">Scam, counterfeit item or financial fraud</option>
                            <option value="Off-topic">Irrelevant to this neighborhood</option>
                            <option value="Other">Other violation of guidelines</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small text-secondary">Additional details (Optional)</label>
                        <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Provide extra context to help our local moderators review this quickly..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-1.5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                        <i data-lucide="shield-alert" style="width: 16px; height: 16px;"></i> Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
<!-- Emergency Helplines Modal -->
<div class="modal fade" id="emergencyModal" tabindex="-1" aria-labelledby="emergencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom py-3 px-4" style="background: #fef2f2;">
                <div>
                    <h5 class="modal-title fs-5 fw-bold text-danger d-flex align-items-center gap-2" id="emergencyModalLabel">
                        <i data-lucide="phone-call" style="width: 20px; height: 20px;"></i> Essential Kerala Civic & Emergency Helplines
                    </h5>
                    <p class="text-secondary small mb-0 mt-1">Direct official emergency numbers across Kerala. Available 24x7 toll-free.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-4">
                        <a href="tel:112" class="helpline-box">
                            <div>
                                <span class="badge bg-danger-subtle text-danger small fw-bold mb-1">Police Emergency</span>
                                <div class="fw-bold text-dark fs-5">112 / 100</div>
                                <div class="text-muted small">Kerala Police Control</div>
                            </div>
                            <div class="btn btn-sm btn-danger rounded-circle p-2"><i data-lucide="phone" style="width: 16px; height: 16px;"></i></div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <a href="tel:101" class="helpline-box">
                            <div>
                                <span class="badge bg-danger-subtle text-danger small fw-bold mb-1">Fire & Rescue</span>
                                <div class="fw-bold text-dark fs-5">101</div>
                                <div class="text-muted small">Kerala Fire Station</div>
                            </div>
                            <div class="btn btn-sm btn-danger rounded-circle p-2"><i data-lucide="flame" style="width: 16px; height: 16px;"></i></div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <a href="tel:108" class="helpline-box">
                            <div>
                                <span class="badge bg-danger-subtle text-danger small fw-bold mb-1">Ambulance</span>
                                <div class="fw-bold text-dark fs-5">108 / 102</div>
                                <div class="text-muted small">Emergency Medical Aid</div>
                            </div>
                            <div class="btn btn-sm btn-danger rounded-circle p-2"><i data-lucide="heart-pulse" style="width: 16px; height: 16px;"></i></div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <a href="tel:1912" class="helpline-box" style="border-color: #fde68a;">
                            <div>
                                <span class="badge bg-warning-subtle text-warning small fw-bold mb-1">Electricity (KSEB)</span>
                                <div class="fw-bold text-dark fs-5">1912</div>
                                <div class="text-muted small">24x7 Power Breakdown</div>
                            </div>
                            <div class="btn btn-sm btn-warning text-dark rounded-circle p-2"><i data-lucide="zap" style="width: 16px; height: 16px;"></i></div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <a href="tel:1916" class="helpline-box" style="border-color: #bae6fd;">
                            <div>
                                <span class="badge bg-info-subtle text-info small fw-bold mb-1">Water Supply (KWA)</span>
                                <div class="fw-bold text-dark fs-5">1916</div>
                                <div class="text-muted small">Pipeline Leak & Supply</div>
                            </div>
                            <div class="btn btn-sm btn-info text-white rounded-circle p-2"><i data-lucide="droplet" style="width: 16px; height: 16px;"></i></div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <a href="tel:1098" class="helpline-box" style="border-color: #ddd6fe;">
                            <div>
                                <span class="badge bg-purple-subtle text-purple small fw-bold mb-1" style="color: #7c3aed;">Child Helpline</span>
                                <div class="fw-bold text-dark fs-5">1098</div>
                                <div class="text-muted small">Childline India Toll-Free</div>
                            </div>
                            <div class="btn btn-sm btn-primary rounded-circle p-2"><i data-lucide="smile" style="width: 16px; height: 16px;"></i></div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <a href="tel:181" class="helpline-box" style="border-color: #fbcfe8;">
                            <div>
                                <span class="badge bg-danger-subtle text-danger small fw-bold mb-1">Women Helpline</span>
                                <div class="fw-bold text-dark fs-5">181</div>
                                <div class="text-muted small">Mitra 24x7 Support</div>
                            </div>
                            <div class="btn btn-sm btn-danger rounded-circle p-2"><i data-lucide="shield" style="width: 16px; height: 16px;"></i></div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <a href="tel:04712529200" class="helpline-box" style="border-color: #a7f3d0;">
                            <div>
                                <span class="badge bg-success-subtle text-success small fw-bold mb-1">Snake Rescue</span>
                                <div class="fw-bold text-dark fs-5">0471-2529200</div>
                                <div class="text-muted small">Kerala Forest / SARPA</div>
                            </div>
                            <div class="btn btn-sm btn-success rounded-circle p-2"><i data-lucide="life-buoy" style="width: 16px; height: 16px;"></i></div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="helpline-box" style="border-color: #e5e7eb;">
                            <div>
                                <span class="badge bg-secondary-subtle text-secondary small fw-bold mb-1">Cleanliness Mission</span>
                                <div class="fw-bold text-dark fs-6">Suchitwa Mission</div>
                                <div class="text-muted small">Haritha Karma Sena Dry Waste</div>
                            </div>
                            <div class="btn btn-sm btn-secondary rounded-circle p-2"><i data-lucide="recycle" style="width: 16px; height: 16px;"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small">Verified by Kerala State Government Emergency Protocol.</span>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-1.5" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container for Interactive Actions -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1099;">
    <div id="appToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: var(--radius-md);">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="toastMessage">
                <i data-lucide="info" style="width: 18px; height: 18px;"></i>
                <span>Action completed</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- LocalLoop Interactive Frontend & Micro-Animations -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>

<!-- Global Layout Helpers -->
<script>
    // 1. Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // 2. Mobile Sidebar Drawer Toggle
    const mobileToggle = document.getElementById('mobileSidebarToggle');
    const appSidebar = document.getElementById('appSidebar');
    if (mobileToggle && appSidebar) {
        mobileToggle.addEventListener('click', () => {
            appSidebar.classList.toggle('show-mobile');
        });
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && appSidebar.classList.contains('show-mobile') && !appSidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                appSidebar.classList.remove('show-mobile');
            }
        });
    }

    // 3. Global Toast Helper
    window.showToast = function(message, isSuccess = true) {
        const toastEl = document.getElementById('appToast');
        const toastMsg = document.getElementById('toastMessage');
        if (toastEl && toastMsg) {
            toastMsg.innerHTML = isSuccess 
                ? '<i class="bi bi-check-circle-fill text-success fs-5"></i> <span>' + message + '</span>'
                : '<i class="bi bi-exclamation-circle-fill text-danger fs-5"></i> <span>' + message + '</span>';
            const bsToast = new bootstrap.Toast(toastEl, { delay: 3500 });
            bsToast.show();
        }
    };

    // 4. Report Modal Handler
    window.openReportModal = function(postId = null, commentId = null) {
        document.getElementById('reportPostId').value = postId || '';
        document.getElementById('reportCommentId').value = commentId || '';
        const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
        reportModal.show();
    };

    // 5. Global Like Interaction (AJAX with graceful UI fallback)
    document.querySelectorAll('.btn-like-trigger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            const countEl = this.querySelector('.like-count');
            const isLiked = this.classList.contains('liked');
            let count = parseInt(countEl.textContent || '0');
            
            if (isLiked) {
                this.classList.remove('liked');
                count = Math.max(0, count - 1);
            } else {
                this.classList.add('liked');
                count += 1;
            }
            countEl.textContent = count;
            
            // Send AJAX to server
            fetch('<?= BASE_URL ?>/ajax/like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'post_id=' + postId + '&csrf_token=<?= generate_csrf_token() ?>'
            }).then(r => r.json()).then(res => {
                if (res.status === 'liked') {
                    showToast('Post added to your liked loop!');
                }
            }).catch(() => {});
        });
    });

    // 6. Global Save Interaction
    document.querySelectorAll('.btn-save-trigger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            const isSaved = this.classList.contains('saved');
            
            if (isSaved) {
                this.classList.remove('saved');
                showToast('Removed from Saved items');
            } else {
                this.classList.add('saved');
                showToast('Post saved to your bookmarks!');
            }
            
            fetch('<?= BASE_URL ?>/ajax/save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'post_id=' + postId + '&csrf_token=<?= generate_csrf_token() ?>'
            }).catch(() => {});
        });
    });
</script>

</body>
</html>
