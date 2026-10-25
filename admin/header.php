<?php
// admin/header.php
// LocalLoop Admin Header & Sidebar (Section 30)

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

require_admin();
$currentUser = current_user($pdo);

$adminPageTitle = isset($adminPageTitle) ? $adminPageTitle . " — Admin | " . SITE_NAME : "Admin Dashboard — " . SITE_NAME;
$pendingReportsCount = (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminPageTitle) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <style>
        .admin-sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.55rem 0.9rem;
            color: #cbd5e1;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.88rem;
            transition: all 0.15s;
        }
        .admin-sidebar-link:hover, .admin-sidebar-link.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }
        .admin-sidebar-link.active {
            background: #2563eb;
        }
    </style>
</head>
<body class="bg-surface">
    <!-- Admin Top Navbar -->
    <nav class="navbar navbar-dark bg-dark sticky-top py-2 px-3 border-bottom border-secondary" style="background-color: #0b132b !important;">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= BASE_URL ?>/admin/index.php">
                <span class="badge bg-danger p-1.5"><i data-lucide="shield-alert" style="width: 16px; height: 16px;"></i></span>
                <span>LocalLoop <span class="badge bg-danger ms-1 small" style="font-size: 0.7rem;">Admin Console</span></span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>/feed.php" class="btn btn-sm btn-outline-light rounded-pill px-3" target="_blank">
                    <i data-lucide="external-link" style="width: 14px; height: 14px;" class="me-1"></i> Live Community Feed
                </a>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle gap-2" data-bs-toggle="dropdown">
                        <img src="<?= get_avatar_url($currentUser['profile_photo']) ?>" width="32" height="32" class="rounded-circle border">
                        <span class="small fw-semibold"><?= htmlspecialchars($currentUser['name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php">My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/settings.php">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Admin Wrapper -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar: Exact 12 items from Section 30 -->
            <nav class="col-md-3 col-lg-2 d-md-block p-3 text-white collapse navbar-collapse" style="background-color: #0b132b; min-height: calc(100vh - 56px);" id="adminNavSidebar">
                <div class="d-flex flex-column gap-1 sticky-top" style="top: 75px;">
                    <span class="text-uppercase small text-white-50 px-2 pt-1 pb-1 fw-bold" style="font-size: 0.68rem;">Management</span>

                    <!-- 1. Dashboard -->
                    <a href="<?= BASE_URL ?>/admin/index.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                        <i data-lucide="layout-dashboard" style="width: 16px; height: 16px;"></i> Dashboard
                    </a>

                    <!-- 2. Users -->
                    <a href="<?= BASE_URL ?>/admin/users.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                        <i data-lucide="users" style="width: 16px; height: 16px;"></i> Users
                    </a>

                    <!-- 3. Posts -->
                    <a href="<?= BASE_URL ?>/admin/posts.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : '' ?>">
                        <i data-lucide="file-text" style="width: 16px; height: 16px;"></i> Posts
                    </a>

                    <!-- 4. Reports -->
                    <a href="<?= BASE_URL ?>/admin/reports.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?> d-flex justify-content-between">
                        <span><i data-lucide="flag" class="text-danger me-1" style="width: 16px; height: 16px;"></i> Reports</span>
                        <?php if ($pendingReportsCount > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= $pendingReportsCount ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- 5. Events -->
                    <a href="<?= BASE_URL ?>/events.php" class="admin-sidebar-link" target="_blank">
                        <i data-lucide="calendar" style="width: 16px; height: 16px;"></i> Events
                    </a>

                    <!-- 6. Jobs -->
                    <a href="<?= BASE_URL ?>/jobs.php" class="admin-sidebar-link" target="_blank">
                        <i data-lucide="briefcase" style="width: 16px; height: 16px;"></i> Jobs
                    </a>

                    <!-- 7. Marketplace -->
                    <a href="<?= BASE_URL ?>/marketplace.php" class="admin-sidebar-link" target="_blank">
                        <i data-lucide="shopping-bag" style="width: 16px; height: 16px;"></i> Marketplace
                    </a>

                    <!-- 8. Help Requests -->
                    <a href="<?= BASE_URL ?>/help.php" class="admin-sidebar-link" target="_blank">
                        <i data-lucide="heart-handshake" style="width: 16px; height: 16px;"></i> Help Requests
                    </a>

                    <!-- 9. Categories -->
                    <a href="<?= BASE_URL ?>/admin/categories.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                        <i data-lucide="tag" style="width: 16px; height: 16px;"></i> Categories
                    </a>

                    <!-- 10. Areas -->
                    <a href="<?= BASE_URL ?>/admin/areas.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'areas.php' ? 'active' : '' ?>">
                        <i data-lucide="map-pin" style="width: 16px; height: 16px;"></i> Areas
                    </a>

                    <!-- 11. Admin Actions -->
                    <a href="<?= BASE_URL ?>/admin/actions.php" class="admin-sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'actions.php' ? 'active' : '' ?>">
                        <i data-lucide="clipboard-list" style="width: 16px; height: 16px;"></i> Admin Actions
                    </a>

                    <!-- 12. Settings -->
                    <a href="<?= BASE_URL ?>/settings.php" class="admin-sidebar-link">
                        <i data-lucide="settings" style="width: 16px; height: 16px;"></i> Settings
                    </a>
                </div>
            </nav>

            <!-- Main Admin Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?= display_flash() ?>
