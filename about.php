<?php
// about.php
// LocalLoop Platform Mission & Guidelines

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "About LocalLoop & Guidelines";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-clean border-0 shadow-sm p-4 p-md-5 mb-4">
                <div class="text-center mb-4">
                    <div class="brand-icon mx-auto mb-2" style="width: 52px; height: 52px;">
                        <i class="bi bi-broadcast-pin fs-3 text-white"></i>
                    </div>
                    <h2 class="fw-bold mb-1">About <span class="text-primary">LocalLoop</span></h2>
                    <p class="text-muted">Your Community. One Platform.</p>
                </div>

                <h5 class="fw-bold text-dark mb-2">1. The Hyperlocal Vision</h5>
                <p class="text-secondary" style="line-height: 1.8;">
                    Global social networks bombard citizens with algorithms, worldwide drama, and distant news. But the information that most deeply impacts daily life happens within a <strong>5-kilometer radius</strong>: a burst water pipe on your street, an elderly neighbor needing a wheelchair, a local shop offering student jobs, or a misplaced wallet at the bus stand.
                </p>
                <p class="text-secondary" style="line-height: 1.8;">
                    <strong>LocalLoop</strong> provides a verified, locality-centered digital hub designed to make neighborhoods cleaner, friendlier, and more resilient through mutual aid.
                </p>

                <hr class="my-4">

                <h5 class="fw-bold text-dark mb-3" id="guidelines"><i class="bi bi-shield-check text-success me-2"></i> Community Guidelines</h5>
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="p-3 rounded bg-light border-start border-3 border-primary">
                        <h6 class="fw-bold mb-1">1. Respect Neighborly Decorum</h6>
                        <p class="small text-muted mb-0">Treat every member with kindness and respect. Abusive language, harassment, hate speech, and personal attacks are strictly prohibited.</p>
                    </div>
                    <div class="p-3 rounded bg-light border-start border-3 border-danger">
                        <h6 class="fw-bold mb-1">2. No False Civic Alerts or Misinformation</h6>
                        <p class="small text-muted mb-0">Do not publish unverified disaster warnings or fabricated panic news. Ensure civic announcements cite real sources.</p>
                    </div>
                    <div class="p-3 rounded bg-light border-start border-3 border-warning">
                        <h6 class="fw-bold mb-1">3. Transparent Marketplace & Jobs</h6>
                        <p class="small text-muted mb-0">Sell accurately described goods. Job listings must represent legitimate local employment with fair terms.</p>
                    </div>
                    <div class="p-3 rounded bg-light border-start border-3 border-success">
                        <h6 class="fw-bold mb-1">4. Safeguard Community Help Network</h6>
                        <p class="small text-muted mb-0">Help requests are reserved for genuine urgent needs (medical, blood, transport). Exploitation will lead to immediate account suspension.</p>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold text-dark mb-2">Technical Implementation</h5>
                <p class="small text-muted mb-3">
                    Developed following modern standards:
                </p>
                <div class="row g-2 text-center small">
                    <div class="col-4">
                        <div class="p-2.5 rounded bg-light border fw-semibold">PHP 8+ (PDO & Prepared Statements)</div>
                    </div>
                    <div class="col-4">
                        <div class="p-2.5 rounded bg-light border fw-semibold">MySQL Relational Database</div>
                    </div>
                    <div class="col-4">
                        <div class="p-2.5 rounded bg-light border fw-semibold">Bootstrap 5 + AJAX / Fetch</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
