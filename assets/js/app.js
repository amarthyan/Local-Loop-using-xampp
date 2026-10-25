/**
 * assets/js/app.js
 * LocalLoop Interactive Frontend Script & Micro-Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    initLikeButtons();
    initSaveButtons();
    initReportModal();
    initCommentForms();
    initEventRSVP();
    initButtonRipples();
    initCounters();
    initCopyShareLinks();
    initTiltEffect();
    initStaggerEntrance();
});

// Toast notification helper
function showToast(message, type = 'info') {
    const toastEl = document.getElementById('appToast');
    const toastMsg = document.getElementById('toastMessage');
    if (!toastEl || !toastMsg) return;

    let icon = 'bi-info-circle-fill text-info';
    if (type === 'success') icon = 'bi-check-circle-fill text-success';
    if (type === 'danger') icon = 'bi-exclamation-triangle-fill text-danger';

    toastMsg.innerHTML = `<i class="bi ${icon} fs-5"></i> <span>${message}</span>`;
    const bsToast = new bootstrap.Toast(toastEl, { delay: 3500 });
    bsToast.show();
}

// Spawns floating mini heart particles on like
function spawnHeartParticles(x, y) {
    const heartSymbols = ['❤️', '💖', '✨', '💕'];
    for (let i = 0; i < 4; i++) {
        const p = document.createElement('div');
        p.className = 'floating-heart-particle';
        p.textContent = heartSymbols[i % heartSymbols.length];
        const offsetX = (Math.random() - 0.5) * 40;
        const offsetY = (Math.random() - 0.5) * 20;
        p.style.left = (x + offsetX) + 'px';
        p.style.top = (y + offsetY) + 'px';
        p.style.animationDelay = (i * 0.08) + 's';
        document.body.appendChild(p);

        setTimeout(() => {
            if (p.parentNode) p.parentNode.removeChild(p);
        }, 1100);
    }
}

// Celebratory Confetti Burst
window.triggerConfetti = function() {
    const colors = ['#2563eb', '#0ea5e9', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'];
    const count = 48;
    for (let i = 0; i < count; i++) {
        const c = document.createElement('div');
        c.className = 'confetti-particle';
        c.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        c.style.left = (Math.random() * 100) + 'vw';
        c.style.top = '-20px';
        c.style.transform = `rotate(${Math.random() * 360}deg)`;
        c.style.animationDuration = (1.5 + Math.random() * 1.5) + 's';
        c.style.animationDelay = (Math.random() * 0.3) + 's';
        document.body.appendChild(c);

        setTimeout(() => {
            if (c.parentNode) c.parentNode.removeChild(c);
        }, 3200);
    }
};

// Like / Unlike AJAX with micro-animation & Material Symbols support
function initLikeButtons() {
    const likeButtons = document.querySelectorAll('.btn-like, .btn-like-trigger, .btn-post-action');
    likeButtons.forEach(button => {
        const heart = button.querySelector('.material-symbols-outlined, i, svg');
        if (!heart || (heart.textContent.trim() !== 'favorite' && !heart.classList.contains('bi-heart'))) return;

        button.addEventListener('click', async function (e) {
            e.preventDefault();

            // Trigger floating heart particles at button position
            const rect = this.getBoundingClientRect();
            spawnHeartParticles(rect.left + rect.width / 2, rect.top);

            const postId = this.dataset.postId || this.getAttribute('data-post-id');
            if (!postId) {
                // Interactive local toggle for showcase posts
                const isCurrentlyLiked = this.classList.contains('liked') || this.classList.contains('text-error');
                if (!isCurrentlyLiked) {
                    this.classList.add('liked', 'text-error');
                    heart.style.fontVariationSettings = "'FILL' 1";
                    const span = this.querySelector('span:not(.material-symbols-outlined)');
                    if (span && !isNaN(parseInt(span.textContent))) {
                        span.textContent = (parseInt(span.textContent) + 1) + ' Likes';
                    }
                    showToast('Post liked!', 'success');
                } else {
                    this.classList.remove('liked', 'text-error');
                    heart.style.fontVariationSettings = "'FILL' 0";
                    const span = this.querySelector('span:not(.material-symbols-outlined)');
                    if (span && !isNaN(parseInt(span.textContent))) {
                        span.textContent = Math.max(0, parseInt(span.textContent) - 1) + ' Likes';
                    }
                }
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}/ajax/like.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${encodeURIComponent(postId)}`
                });

                const data = await response.json();
                if (data.status === 'unauthorized') {
                    window.location.href = `${BASE_URL}/login.php`;
                    return;
                }

                if (data.success || data.status) {
                    const isLiked = data.action === 'liked' || data.status === 'liked';
                    const countSpan = this.querySelector('.like-count');
                    
                    if (countSpan) {
                        let count = parseInt(countSpan.textContent || '0');
                        countSpan.textContent = isLiked ? (count + 1) : Math.max(0, count - 1);
                    }

                    if (isLiked) {
                        this.classList.add('active-like', 'liked');
                        heart.style.fontVariationSettings = "'FILL' 1";
                        showToast('Post added to your liked loop!', 'success');
                    } else {
                        this.classList.remove('active-like', 'liked');
                        heart.style.fontVariationSettings = "'FILL' 0";
                    }
                } else {
                    showToast(data.message || 'Action failed', 'danger');
                }
            } catch (err) {
                console.error('Error liking post:', err);
            }
        });
    });
}

// Save / Bookmark AJAX with golden pop
function initSaveButtons() {
    const saveButtons = document.querySelectorAll('.btn-save, .btn-save-trigger');
    saveButtons.forEach(button => {
        button.addEventListener('click', async function (e) {
            e.preventDefault();
            const postId = this.dataset.postId || this.getAttribute('data-post-id');
            if (!postId) return;

            try {
                const response = await fetch(`${BASE_URL}/ajax/save.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${encodeURIComponent(postId)}`
                });

                const data = await response.json();
                if (data.status === 'unauthorized') {
                    window.location.href = `${BASE_URL}/login.php`;
                    return;
                }

                if (data.success || data.status) {
                    const isSaved = data.action === 'saved' || data.status === 'saved';
                    const icon = this.querySelector('i, svg');
                    const textSpan = this.querySelector('.save-text');

                    if (isSaved) {
                        this.classList.add('active-saved', 'saved');
                        if (icon) {
                            icon.classList.remove('bi-bookmark');
                            icon.classList.add('bi-bookmark-fill', 'text-warning');
                        }
                        if (textSpan) textSpan.textContent = 'Saved';
                        showToast('Post saved to your bookmarks!', 'success');
                    } else {
                        this.classList.remove('active-saved', 'saved');
                        if (icon) {
                            icon.classList.remove('bi-bookmark-fill', 'text-warning');
                            icon.classList.add('bi-bookmark');
                        }
                        if (textSpan) textSpan.textContent = 'Save';
                        showToast('Post removed from saved bookmarks.', 'info');
                    }
                } else {
                    showToast(data.message || 'Action failed', 'danger');
                }
            } catch (err) {
                console.error('Error saving post:', err);
            }
        });
    });
}

// Report Modal setup
function initReportModal() {
    const reportModal = document.getElementById('reportModal');
    if (!reportModal) return;

    reportModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (trigger) {
            const postId = trigger.getAttribute('data-post-id');
            const commentId = trigger.getAttribute('data-comment-id');
            const pInput = document.getElementById('reportPostId');
            const cInput = document.getElementById('reportCommentId');
            if (pInput) pInput.value = postId || '';
            if (cInput) cInput.value = commentId || '';
        }
    });

    const form = document.getElementById('globalReportForm');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const response = await fetch(`${BASE_URL}/ajax/report.php`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.status === 'unauthorized') {
                    window.location.href = `${BASE_URL}/login.php`;
                    return;
                }

                if (data.success) {
                    const modalInstance = bootstrap.Modal.getInstance(reportModal);
                    if (modalInstance) modalInstance.hide();
                    form.reset();
                    showToast(data.message || 'Report submitted to moderators. Thank you for keeping our community safe.', 'success');
                } else {
                    showToast(data.message || 'Failed to submit report', 'danger');
                }
            } catch (err) {
                console.error('Report submission error:', err);
                showToast('An error occurred while submitting report.', 'danger');
            }
        });
    }
}

// Inline comment submission
function initCommentForms() {
    document.querySelectorAll('.ajax-comment-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const input = this.querySelector('input[name="comment"]');
            const postId = this.querySelector('input[name="post_id"]').value;
            const commentText = input.value.trim();

            if (!commentText) return;

            try {
                const response = await fetch(`${BASE_URL}/ajax/comment.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${encodeURIComponent(postId)}&comment=${encodeURIComponent(commentText)}`
                });

                const data = await response.json();
                if (data.status === 'unauthorized') {
                    window.location.href = `${BASE_URL}/login.php`;
                    return;
                }

                if (data.success) {
                    input.value = '';
                    const listContainer = document.getElementById(`commentsList-${postId}`);
                    if (listContainer) {
                        const newCommentHtml = `
                            <div class="d-flex gap-2 mb-2 p-2 rounded bg-light border-start border-3 border-primary animate-fade-in">
                                <img src="${data.comment.avatar}" class="rounded-circle" width="30" height="30">
                                <div class="flex-grow-1 small">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">${data.comment.user_name}</span>
                                        <span class="text-muted" style="font-size: 0.75rem;">Just now</span>
                                    </div>
                                    <p class="mb-0 text-dark">${data.comment.comment}</p>
                                </div>
                            </div>
                        `;
                        listContainer.insertAdjacentHTML('beforeend', newCommentHtml);
                    }

                    // Update counter
                    const commentCountEl = document.querySelector(`.comment-count-${postId}`);
                    if (commentCountEl && data.total_comments) {
                        commentCountEl.textContent = data.total_comments;
                    }
                    showToast('Comment posted!', 'success');
                } else {
                    showToast(data.message || 'Failed to post comment', 'danger');
                }
            } catch (err) {
                console.error('Comment error:', err);
            }
        });
    });
}

// Event RSVP with celebratory confetti
function initEventRSVP() {
    document.querySelectorAll('.btn-event-rsvp').forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            const eventId = this.dataset.eventId;
            const rsvpStatus = this.dataset.status || 'going';

            try {
                const response = await fetch(`${BASE_URL}/ajax/event_rsvp.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `event_id=${encodeURIComponent(eventId)}&status=${encodeURIComponent(rsvpStatus)}`
                });

                const data = await response.json();
                if (data.status === 'unauthorized') {
                    window.location.href = `${BASE_URL}/login.php`;
                    return;
                }

                if (data.success) {
                    showToast(data.message, 'success');
                    if (data.action === 'registered') {
                        triggerConfetti();
                    }
                    const countEl = document.getElementById(`eventCount-${eventId}`);
                    if (countEl && data.total_participants !== undefined) {
                        countEl.textContent = `${data.total_participants} Attending`;
                    }
                    if (data.action === 'registered') {
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-success');
                        this.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Going';
                    } else {
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-primary');
                        this.innerHTML = '<i class="bi bi-calendar-check me-1"></i> RSVP / Attend';
                    }
                } else {
                    showToast(data.message || 'Action failed', 'danger');
                }
            } catch (err) {
                console.error('RSVP error:', err);
            }
        });
    });
}

// Material Button Ripple Effect
function initButtonRipples() {
    const targets = document.querySelectorAll('.btn, .btn-primary-custom, .btn-blue-accent, .category-card-pill, .quick-action-btn');
    targets.forEach(button => {
        button.classList.add('btn-ripple');
        button.addEventListener('click', function(e) {
            const circle = document.createElement('span');
            circle.classList.add('ripple-circle');
            const rect = this.getBoundingClientRect();
            const d = Math.max(rect.width, rect.height);
            circle.style.width = circle.style.height = `${d}px`;
            circle.style.left = `${e.clientX - rect.left - d / 2}px`;
            circle.style.top = `${e.clientY - rect.top - d / 2}px`;
            this.appendChild(circle);
            setTimeout(() => circle.remove(), 600);
        });
    });
}

// Smooth Numeric Counter Rolling Animation
function initCounters() {
    const counterElements = document.querySelectorAll('[data-counter], .counter-value');
    if (!counterElements.length) return;

    const animateNumber = (el) => {
        const rawTarget = el.getAttribute('data-counter') || el.textContent;
        const targetNumber = parseInt(rawTarget.replace(/[^0-9]/g, ''), 10);
        if (isNaN(targetNumber) || targetNumber === 0) return;

        const duration = 1400; // ms
        const frameDuration = 1000 / 60;
        const totalFrames = Math.round(duration / frameDuration);
        let frame = 0;

        const counterInterval = setInterval(() => {
            frame++;
            const progress = frame / totalFrames;
            // Ease out cubic
            const current = Math.round(targetNumber * (1 - Math.pow(1 - progress, 3)));
            el.textContent = current.toLocaleString();

            if (frame === totalFrames) {
                clearInterval(counterInterval);
                el.textContent = targetNumber.toLocaleString();
            }
        }, frameDuration);
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateNumber(entry.target);
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        counterElements.forEach(el => observer.observe(el));
    } else {
        counterElements.forEach(el => animateNumber(el));
    }
}

// Copy Share Links with Animated Feedback
function initCopyShareLinks() {
    document.querySelectorAll('.btn-copy-share').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-share-url') || window.location.href;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    showToast('Link copied to clipboard! Ready to share with neighbors.', 'success');
                });
            } else {
                showToast('Link copied to clipboard!', 'info');
            }
        });
    });
}

// Interactive 3D Card Perspective Tilt
function initTiltEffect() {
    const cards = document.querySelectorAll('.interactive-tilt');
    cards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const xPct = (x / rect.width - 0.5) * 8; // -4deg to 4deg
            const yPct = (0.5 - y / rect.height) * 8;
            this.style.transform = `perspective(800px) rotateX(${yPct}deg) rotateY(${xPct}deg) translateY(-4px)`;
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
}

// Stagger Entrance for Cards on page load
function initStaggerEntrance() {
    const items = document.querySelectorAll('.feed-stagger-item, .post-card, .event-card, .marketplace-item-card, .job-listing-card');
    items.forEach((item, index) => {
        if (!item.classList.contains('animate-fade-up')) {
            item.classList.add('animate-fade-up');
            const delayClass = `stagger-${Math.min(5, (index % 5) + 1)}`;
            item.classList.add(delayClass);
        }
    });
}
