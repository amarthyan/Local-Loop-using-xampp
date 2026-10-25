<?php
// onboarding.php
// LocalLoop Onboarding: Choose Your Community (Section 6)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Choose Your Community";
$areas = get_active_areas($pdo);

// Handle locality selection
if (isset($_GET['select_area'])) {
    $selId = (int)$_GET['select_area'];
    $_SESSION['browsing_area_id'] = $selId;
    if ($currentUser) {
        $pdo->prepare("UPDATE users SET area_id = ? WHERE id = ?")->execute([$selId, $currentUser['id']]);
    }
    flash('success', 'Welcome to your neighborhood loop! Your feed is now customized.', 'success');
    header("Location: " . BASE_URL . "/feed.php");
    exit;
}

$isAuthPage = false;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4" style="max-width: 960px;">
    <!-- Onboarding Header -->
    <div class="text-center mb-5">
        <div class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill mb-2 fw-semibold">
            <i data-lucide="compass" class="me-1" style="width: 14px; height: 14px;"></i> Onboarding Step
        </div>
        <h2 class="display-6 fw-bold text-dark mb-2">Choose your community</h2>
        <p class="text-secondary mx-auto" style="max-width: 560px;">
            LocalLoop is locality-focused. Select where you live, study, or work to see verified neighborhood news, items, jobs, and events.
        </p>

        <!-- Searchable locality filter -->
        <div class="mx-auto mt-4" style="max-width: 440px;">
            <div class="input-group search-pill shadow-subtle bg-white border p-1 rounded-pill">
                <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                    <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                </span>
                <input type="text" id="localitySearchInput" class="form-control border-0 bg-transparent text-dark" placeholder="Search neighborhood or town (e.g. North Paravur)...">
            </div>
        </div>
    </div>

    <!-- Locality Cards Grid -->
    <div class="row g-4" id="localityCardsContainer">
        <?php foreach ($areas as $area): 
            $isCurrent = ($browsingAreaId == $area['id']);
            $members = (stripos($area['name'], 'North Paravur') !== false) ? 2480 : rand(800, 3500);
            $postsToday = rand(15, 65);
        ?>
            <div class="col-md-6 col-lg-4 locality-card-item" data-area-name="<?= strtolower(htmlspecialchars($area['name'] . ' ' . $area['district'])) ?>">
                <div class="card card-solid h-100 p-4 d-flex flex-column justify-content-between position-relative <?= $isCurrent ? 'border-primary shadow-subtle' : '' ?>" style="border-radius: var(--radius-lg); transition: var(--transition-normal);">
                    <?php if ($isCurrent): ?>
                        <span class="position-absolute top-0 end-0 m-3 badge bg-primary rounded-pill">Your Primary Loop</span>
                    <?php endif; ?>
                    
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="brand-logo-icon" style="width: 44px; height: 44px; background: <?= $isCurrent ? 'linear-gradient(135deg, #2563eb, #0ea5e9)' : '#f1f5f9' ?>; color: <?= $isCurrent ? '#ffffff' : '#0b132b' ?>;">
                                <i data-lucide="map-pin" style="width: 22px; height: 22px;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($area['name']) ?></h5>
                                <div class="text-secondary small"><?= htmlspecialchars($area['district']) ?>, <?= htmlspecialchars($area['state']) ?></div>
                            </div>
                        </div>

                        <p class="text-secondary small mb-3">
                            Connected neighborhood network with active community notice board, marketplace, and civic updates.
                        </p>

                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border mb-4 small text-secondary">
                            <div><i data-lucide="users" class="me-1 text-primary" style="width: 14px; height: 14px;"></i> <strong><?= number_format($members) ?></strong> members</div>
                            <div><i data-lucide="message-square" class="me-1 text-success" style="width: 14px; height: 14px;"></i> <strong><?= $postsToday ?></strong> updates today</div>
                        </div>
                    </div>

                    <a href="?select_area=<?= $area['id'] ?>" class="btn <?= $isCurrent ? 'btn-primary-custom' : 'btn-outline-custom' ?> w-100 justify-content-center fw-semibold">
                        <span>Select this Community</span>
                        <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick All Localities Option -->
    <div class="text-center mt-5 pt-3 border-top">
        <a href="?select_area=all" class="text-muted small text-decoration-none hover-blue">
            <i data-lucide="globe" style="width: 14px; height: 14px;"></i> Browse across all localities instead
        </a>
    </div>
</div>

<script>
    // Live filter search for locality cards
    document.getElementById('localitySearchInput').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.locality-card-item').forEach(card => {
            const areaText = card.getAttribute('data-area-name') || '';
            if (areaText.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
