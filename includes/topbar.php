<?php
// includes/topbar.php
// LocalLoop Civic Glass Application Header (Stitch Design Specification)

$hour = (int)date('H');
if ($hour < 12) {
    $timeGreeting = "Good morning";
} elseif ($hour < 17) {
    $timeGreeting = "Good afternoon";
} else {
    $timeGreeting = "Good evening";
}

$displayName = $currentUser ? htmlspecialchars(explode(' ', $currentUser['name'])[0]) : "Neighbor";
$searchQuery = htmlspecialchars($_GET['q'] ?? '');
?>
<header class="app-topbar">
    <!-- Mobile Hamburger Toggle & Brand -->
    <div class="d-flex align-items-center gap-2 d-lg-none">
        <button class="btn btn-sm btn-light border p-1 rounded-lg" id="mobileSidebarToggle" type="button" aria-label="Toggle Navigation">
            <span class="material-symbols-outlined text-[20px]">menu</span>
        </button>
        <a href="<?= BASE_URL ?>/feed.php" class="brand-logo-text-mobile">Local<span>Loop</span></a>
    </div>

    <!-- Greeting & Locality Subtitle (Desktop) -->
    <div class="topbar-greeting-wrap">
        <div class="topbar-greeting-title"><?= $timeGreeting ?>, <?= $displayName ?> 👋</div>
        <div class="topbar-greeting-sub">What's happening in <?= htmlspecialchars($currentAreaName) ?>?</div>
    </div>

    <!-- Global Search Bar -->
    <form class="topbar-search-form" action="<?= BASE_URL ?>/search.php" method="GET">
        <div class="topbar-search-pill">
            <span class="material-symbols-outlined search-icon">search</span>
            <input type="text" name="q" placeholder="Search posts, events, jobs, items..." value="<?= $searchQuery ?>" autocomplete="off">
            <?php if (!empty($searchQuery)): ?>
                <a href="<?= BASE_URL ?>/feed.php" class="text-muted small ps-1 text-decoration-none">&times;</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Topbar Right Actions -->
    <div class="topbar-actions-wrap">
        <!-- Locality Selector Dropdown -->
        <div class="dropdown">
            <button class="btn-locality-pill dropdown-toggle border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-symbols-outlined text-secondary" style="font-size: 16px;">location_on</span>
                <span class="truncate" style="max-width: 125px;"><?= htmlspecialchars($currentAreaName) ?></span>
                <span class="material-symbols-outlined text-[16px]">arrow_drop_down</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2 dropdown-civic-menu" style="border-radius: var(--radius-md); min-width: 220px;">
                <li class="dropdown-header text-uppercase small fw-bold px-3 py-1 text-secondary">Choose Locality</li>
                <li>
                    <a class="dropdown-item rounded-xl py-2 <?= empty($browsingAreaId) ? 'active bg-secondary-fixed text-on-secondary-fixed' : '' ?>" href="?area=all">
                        <span class="material-symbols-outlined me-2 text-[18px]">public</span> All Localities
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <?php foreach ($allAreas as $area): ?>
                    <li>
                        <a class="dropdown-item rounded-xl py-2 <?= ($browsingAreaId == $area['id']) ? 'active bg-secondary-fixed text-on-secondary-fixed' : '' ?>" href="?area=<?= $area['id'] ?>">
                            <span class="material-symbols-outlined me-2 text-secondary text-[16px]">location_on</span>
                            <?= htmlspecialchars($area['name']) ?>
                            <span class="badge bg-light text-secondary ms-1 small" style="font-size: 0.7rem;"><?= htmlspecialchars($area['district']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item text-secondary fw-semibold small" href="<?= BASE_URL ?>/onboarding.php">
                        <span class="material-symbols-outlined me-2 text-[16px]">explore</span> Browse All Localities...
                    </a>
                </li>
            </ul>
        </div>

        <!-- Create Post Primary CTA (Desktop) -->
        <a href="<?= BASE_URL ?>/create-post.php" class="btn-topbar-create d-none d-md-flex align-items-center gap-1">
            <span class="material-symbols-outlined text-[18px]">add</span>
            <span>Create Post</span>
        </a>

        <!-- Emergency Helplines Trigger -->
        <button type="button" class="btn-emergency-pill border-0" data-bs-toggle="modal" data-bs-target="#emergencyModal" title="Emergency Numbers & Civic Helplines">
            <span class="material-symbols-outlined text-[15px]">call</span>
            <span class="d-none d-sm-inline">Helplines</span>
        </button>

        <!-- Notification Icon -->
        <a href="<?= BASE_URL ?>/notifications.php" class="topbar-icon-btn" title="Notifications">
            <span class="material-symbols-outlined text-[20px]">notifications</span>
            <?php if ($unreadNotifications > 0): ?>
                <span class="topbar-badge-dot bg-error"></span>
            <?php endif; ?>
        </a>

        <!-- Message Icon -->
        <a href="<?= BASE_URL ?>/messages.php" class="topbar-icon-btn" title="Messages">
            <span class="material-symbols-outlined text-[20px]">chat</span>
            <?php if (($unreadMsgCount ?? 0) > 0): ?>
                <span class="topbar-badge-dot bg-secondary"></span>
            <?php endif; ?>
        </a>

        <!-- User Profile Avatar & Dropdown -->
        <?php if ($currentUser): ?>
            <div class="dropdown">
                <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="d-inline-block">
                    <img src="<?= get_avatar_url($currentUser['profile_photo']) ?>" alt="Avatar" class="topbar-user-avatar">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2 dropdown-civic-menu" style="border-radius: var(--radius-md); min-width: 230px;">
                    <li class="px-3 py-2 border-bottom mb-1">
                        <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($currentUser['name']) ?></div>
                        <div class="text-secondary small">@<?= htmlspecialchars($currentUser['username']) ?> &bull; <span class="badge bg-secondary-fixed text-on-secondary-fixed"><?= ucfirst($currentUser['role']) ?></span></div>
                        <div class="text-success small mt-1"><span class="material-symbols-outlined text-[14px] align-middle">military_tech</span> <?= $currentUser['reputation_points'] ?> Rep Points</div>
                    </li>
                    <li><a class="dropdown-item rounded-xl py-2 small" href="<?= BASE_URL ?>/profile.php"><span class="material-symbols-outlined me-2 text-[16px] align-middle">person</span> My Profile</a></li>
                    <li><a class="dropdown-item rounded-xl py-2 small" href="<?= BASE_URL ?>/saved.php"><span class="material-symbols-outlined me-2 text-[16px] align-middle">bookmark</span> Saved Posts</a></li>
                    <li><a class="dropdown-item rounded-xl py-2 small" href="<?= BASE_URL ?>/settings.php"><span class="material-symbols-outlined me-2 text-[16px] align-middle">settings</span> Settings</a></li>
                    <li><a class="dropdown-item rounded-xl py-2 small" href="<?= BASE_URL ?>/community-stats.php"><span class="material-symbols-outlined me-2 text-[16px] align-middle">bar_chart</span> Community Stats</a></li>
                    
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item rounded-xl py-2 small text-danger fw-semibold" href="<?= BASE_URL ?>/admin/index.php"><span class="material-symbols-outlined me-2 text-[16px] align-middle">shield_person</span> Admin Dashboard</a></li>
                    <?php endif; ?>
                    
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item rounded-xl py-2 small text-danger" href="<?= BASE_URL ?>/logout.php"><span class="material-symbols-outlined me-2 text-[16px] align-middle">logout</span> Sign Out</a></li>
                </ul>
            </div>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-dark rounded-xl px-3 py-1.5 fw-semibold">Sign In</a>
        <?php endif; ?>
    </div>
</header>
