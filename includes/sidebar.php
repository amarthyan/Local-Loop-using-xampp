<?php
// includes/sidebar.php
// LocalLoop Civic Glass Navigation Sidebar (Stitch Design Specification)

$currentFile = basename($_SERVER['PHP_SELF']);
$unreadMsgCount = $currentUser ? get_unread_messages_count($pdo, $currentUser['id']) : 0;
$unreadNotifications = $currentUser ? get_unread_notifications_count($pdo, $currentUser['id']) : 0;
?>
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-top-section">
        <!-- 1. Header with Brand Logo & Typography -->
        <div class="sidebar-header">
            <a href="<?= BASE_URL ?>/feed.php" class="brand-logo-link">
                <div class="brand-logo-icon">
                    <span class="material-symbols-outlined" style="font-size: 22px;">radar</span>
                </div>
                <div class="brand-text-wrap">
                    <span class="brand-logo-title">LocalLoop</span>
                    <span class="brand-tagline-sub">Civic Community Hub</span>
                </div>
            </a>
        </div>

        <!-- 2. Main Navigation Rail -->
        <nav class="sidebar-nav-container">
            <!-- Home Noticeboard -->
            <a href="<?= BASE_URL ?>/feed.php" class="sidebar-nav-item <?= in_array($currentFile, ['feed.php', 'index.php']) ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon">home</span>
                    <span class="nav-label">Home</span>
                </div>
            </a>

            <!-- Explore -->
            <a href="<?= BASE_URL ?>/explore.php" class="sidebar-nav-item <?= ($currentFile == 'explore.php') ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon">explore</span>
                    <span class="nav-label">Explore</span>
                </div>
            </a>

            <!-- Create Post -->
            <a href="<?= BASE_URL ?>/create-post.php" class="sidebar-nav-item <?= ($currentFile == 'create-post.php') ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon text-secondary">add_circle</span>
                    <span class="nav-label">Create Post</span>
                </div>
                <span class="nav-chip-badge bg-primary text-white">+ Free</span>
            </a>

            <!-- Events -->
            <a href="<?= BASE_URL ?>/events.php" class="sidebar-nav-item <?= in_array($currentFile, ['events.php', 'event.php', 'create-event.php']) ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon">calendar_month</span>
                    <span class="nav-label">Events</span>
                </div>
            </a>

            <!-- Jobs -->
            <a href="<?= BASE_URL ?>/jobs.php" class="sidebar-nav-item <?= in_array($currentFile, ['jobs.php', 'job.php', 'create-job.php']) ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon">work</span>
                    <span class="nav-label">Jobs</span>
                </div>
            </a>

            <!-- Marketplace -->
            <a href="<?= BASE_URL ?>/marketplace.php" class="sidebar-nav-item <?= in_array($currentFile, ['marketplace.php', 'item.php', 'create-item.php']) ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon">storefront</span>
                    <span class="nav-label">Marketplace</span>
                </div>
            </a>

            <!-- Community Help -->
            <a href="<?= BASE_URL ?>/help.php" class="sidebar-nav-item <?= in_array($currentFile, ['help.php', 'help-request.php', 'create-help.php']) ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon text-danger">volunteer_activism</span>
                    <span class="nav-label">Community Help</span>
                </div>
                <span class="nav-chip-badge bg-error-container text-on-error-container">Urgent</span>
            </a>

            <!-- Lost & Found -->
            <a href="<?= BASE_URL ?>/lost-found.php" class="sidebar-nav-item <?= in_array($currentFile, ['lost-found.php', 'create-lost-found.php']) ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon">travel_explore</span>
                    <span class="nav-label">Lost & Found</span>
                </div>
            </a>

            <!-- Notifications -->
            <a href="<?= BASE_URL ?>/notifications.php" class="sidebar-nav-item <?= ($currentFile == 'notifications.php') ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon">notifications</span>
                    <span class="nav-label">Notifications</span>
                </div>
                <?php if ($unreadNotifications > 0): ?>
                    <span class="nav-counter-badge bg-error-container text-on-error-container"><?= $unreadNotifications ?></span>
                <?php endif; ?>
            </a>

            <!-- Messages -->
            <a href="<?= BASE_URL ?>/messages.php" class="sidebar-nav-item <?= ($currentFile == 'messages.php') ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon">chat</span>
                    <span class="nav-label">Messages</span>
                </div>
                <?php if ($unreadMsgCount > 0): ?>
                    <span class="nav-counter-badge bg-secondary-container text-on-secondary-container"><?= $unreadMsgCount ?></span>
                <?php endif; ?>
            </a>

            <!-- Saved -->
            <a href="<?= BASE_URL ?>/saved.php" class="sidebar-nav-item <?= ($currentFile == 'saved.php') ? 'active' : '' ?>">
                <div class="nav-item-left">
                    <span class="material-symbols-outlined nav-icon">bookmark</span>
                    <span class="nav-label">Saved</span>
                </div>
            </a>

            <!-- 3. Community Sub-section -->
            <div class="sidebar-community-block">
                <div class="sidebar-section-title">Community</div>
                <div class="sidebar-community-links">
                    <a href="<?= BASE_URL ?>/feed.php?area=<?= $browsingAreaId ?: 'all' ?>" class="community-sub-link">
                        <span class="material-symbols-outlined text-[18px] text-secondary">location_on</span>
                        <span class="truncate"><?= htmlspecialchars($currentAreaName) ?></span>
                    </a>
                    <a href="<?= BASE_URL ?>/onboarding.php" class="community-sub-link">
                        <span class="material-symbols-outlined text-[18px]">explore</span>
                        <span>Nearby Areas</span>
                    </a>
                    <a href="<?= BASE_URL ?>/community-stats.php" class="community-sub-link <?= ($currentFile == 'community-stats.php') ? 'active-sub' : '' ?>">
                        <span class="material-symbols-outlined text-[18px]">bar_chart</span>
                        <span>Community Stats</span>
                    </a>
                    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/admin/index.php" class="community-sub-link text-danger">
                            <span class="material-symbols-outlined text-[18px] text-danger">shield_person</span>
                            <span>Admin Panel</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>

    <!-- 4. Bottom User Profile Card -->
    <div class="sidebar-user-panel">
        <?php if ($currentUser): ?>
            <div class="sidebar-user-brief">
                <img src="<?= get_avatar_url($currentUser['profile_photo']) ?>" alt="<?= htmlspecialchars($currentUser['name']) ?>" class="sidebar-user-avatar">
                <div class="sidebar-user-info">
                    <span class="sidebar-user-name truncate"><?= htmlspecialchars($currentUser['name']) ?></span>
                    <span class="sidebar-user-role truncate">
                        <?= $currentUser['role'] === 'admin' ? 'Administrator' : ($currentUser['reputation_points'] >= 100 ? 'Verified Neighbor' : 'Resident') ?>
                    </span>
                </div>
            </div>
            <div class="sidebar-user-actions">
                <a href="<?= BASE_URL ?>/profile.php" class="user-action-pill">
                    <span class="material-symbols-outlined text-[18px]">person</span>
                    <span>Profile</span>
                </a>
                <a href="<?= BASE_URL ?>/settings.php" class="user-action-pill">
                    <span class="material-symbols-outlined text-[18px]">settings</span>
                    <span>Settings</span>
                </a>
                <a href="<?= BASE_URL ?>/about.php" class="user-action-pill">
                    <span class="material-symbols-outlined text-[18px]">help</span>
                    <span>Help & Support</span>
                </a>
                <a href="<?= BASE_URL ?>/logout.php" class="user-action-pill text-error hover-error">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>Logout</span>
                </a>
            </div>
        <?php else: ?>
            <div class="sidebar-guest-box">
                <p class="small text-secondary mb-2">Connect with neighbors in <?= htmlspecialchars($currentAreaName) ?></p>
                <div class="d-flex flex-column gap-2">
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-outline-dark rounded-xl fw-semibold py-1.5">Sign In</a>
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-sm btn-dark text-white rounded-xl fw-semibold py-1.5">Join LocalLoop</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</aside>
