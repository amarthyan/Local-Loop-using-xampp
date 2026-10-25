<?php
// includes/header.php
// LocalLoop Unified SaaS Application Header & Layout Frame

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

$pageTitle = isset($pageTitle) ? $pageTitle . " — " . SITE_NAME : SITE_NAME . " — Hyperlocal Community Platform";
$currentUser = current_user($pdo);
$unreadNotifications = $currentUser ? get_unread_notifications_count($pdo, $currentUser['id']) : 0;
$unreadMsgCount = $currentUser ? get_unread_messages_count($pdo, $currentUser['id']) : 0;
$allAreas = get_active_areas($pdo);

// Determine active area for browsing
if (isset($_GET['area'])) {
    if ($_GET['area'] === 'all') {
        unset($_SESSION['browsing_area_id']);
    } else {
        $_SESSION['browsing_area_id'] = (int)$_GET['area'];
    }
}

// Default to North Paravur if no preference specified yet
if (!isset($_SESSION['browsing_area_id']) && !$currentUser) {
    foreach ($allAreas as $a) {
        if (stripos($a['name'], 'North Paravur') !== false) {
            $_SESSION['browsing_area_id'] = $a['id'];
            break;
        }
    }
}

$browsingAreaId = $_SESSION['browsing_area_id'] ?? ($currentUser['area_id'] ?? null);
$currentAreaName = "All Localities";
if ($browsingAreaId) {
    foreach ($allAreas as $a) {
        if ($a['id'] == $browsingAreaId) {
            $currentAreaName = $a['name'];
            break;
        }
    }
}

$isAuthPage = $isAuthPage ?? false;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="LocalLoop — Hyperlocal community platform connecting residents, students, local businesses, and events. Your community. Your locality. Your loop.">
    
    <!-- Google Fonts: Plus Jakarta Sans (Civic Glass Foundation) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Google Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Lucide Outline Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Bootstrap Icons (Fallback / Extra) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- LocalLoop Civic Glass Modern Community Design System -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
    <!-- Ambient Warmth Gradient Mesh (Sunlit Kerala Morning) -->
    <div class="ambient-glow-wrapper" aria-hidden="true">
        <div class="ambient-orb ambient-orb-1"></div>
        <div class="ambient-orb ambient-orb-2"></div>
        <div class="ambient-orb ambient-orb-3"></div>
    </div>
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>

<?php if (!$isAuthPage): ?>
    <div class="app-layout">
        <!-- Persistent Desktop Sidebar -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <!-- Main Workspace -->
        <main class="app-main">
            <!-- Main Application Topbar -->
            <?php require_once __DIR__ . '/topbar.php'; ?>

            <!-- Page Content Body Container -->
            <div class="app-content-body">
                <!-- Flash Messages Banner -->
                <?= display_flash() ?>
<?php else: ?>
    <!-- Standalone Auth Page Container -->
    <div class="auth-standalone-wrapper">
        <div class="container-fluid p-0">
            <?= display_flash() ?>
<?php endif; ?>
