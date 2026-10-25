<?php
// includes/navbar.php
// LocalLoop Navigation Bar
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-brand-dark sticky-top glass-nav py-2.5">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-white fs-4" href="<?= BASE_URL ?>/index.php">
            <span class="brand-icon">
                <i class="bi bi-broadcast-pin"></i>
            </span>
            <span>Local<span class="text-brand-accent">Loop</span></span>
        </a>

        <!-- Area Selector Dropdown -->
        <div class="dropdown me-auto ms-2 d-none d-md-block">
            <button class="btn btn-locality dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-geo-alt-fill text-danger"></i>
                <span class="locality-text text-truncate" style="max-width: 140px;"><?= htmlspecialchars($currentAreaName) ?></span>
            </button>
            <ul class="dropdown-menu shadow border-0 mt-2 dropdown-menu-start">
                <li class="dropdown-header text-uppercase small fw-bold">Select Locality</li>
                <li><a class="dropdown-item <?= empty($browsingAreaId) ? 'active' : '' ?>" href="?area=all"><i class="bi bi-globe me-2"></i> All Localities</a></li>
                <li><hr class="dropdown-divider"></li>
                <?php foreach ($allAreas as $area): ?>
                    <li>
                        <a class="dropdown-item <?= ($browsingAreaId == $area['id']) ? 'active' : '' ?>" href="?area=<?= $area['id'] ?>">
                            <i class="bi bi-geo-alt me-2 text-primary"></i> <?= htmlspecialchars($area['name']) ?> 
                            <span class="badge bg-light text-muted ms-1 small"><?= htmlspecialchars($area['district']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Search Bar (Desktop) -->
        <form class="d-none d-lg-flex mx-3 flex-grow-1" style="max-width: 300px;" action="<?= BASE_URL ?>/search.php" method="GET">
            <div class="input-group search-pill">
                <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control border-0 bg-transparent text-white" placeholder="Search local posts, items..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
        </form>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2"></i>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 my-2 my-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'feed.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/feed.php">
                        <i class="bi bi-chat-square-text me-1"></i> Feed
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'events.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/events.php">
                        <i class="bi bi-calendar3 me-1"></i> Events
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'jobs.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/jobs.php">
                        <i class="bi bi-briefcase me-1"></i> Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'marketplace.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/marketplace.php">
                        <i class="bi bi-cart3 me-1"></i> Market
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'help.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/help.php">
                        <i class="bi bi-heart-pulse me-1 text-danger"></i> Help
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'lost-found.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/lost-found.php">
                        <i class="bi bi-binoculars me-1"></i> Lost & Found
                    </a>
                </li>

                <!-- Mobile Locality Switcher -->
                <li class="nav-item dropdown d-md-none my-2">
                    <a class="nav-link dropdown-toggle text-warning" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-geo-alt-fill me-1"></i> Locality: <?= htmlspecialchars($currentAreaName) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?area=all">All Localities</a></li>
                        <?php foreach ($allAreas as $area): ?>
                            <li><a class="dropdown-item" href="?area=<?= $area['id'] ?>"><?= htmlspecialchars($area['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <?php if ($currentUser): ?>
                    <!-- Create Post Button -->
                    <li class="nav-item ms-lg-2">
                        <a href="<?= BASE_URL ?>/create-post.php" class="btn btn-accent-gradient rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>Create Post</span>
                        </a>
                    </li>

                    <!-- Notifications Dropdown -->
                    <li class="nav-item position-relative ms-lg-2">
                        <a class="nav-link position-relative px-2" href="<?= BASE_URL ?>/notifications.php" title="Notifications">
                            <i class="bi bi-bell-fill fs-5"></i>
                            <?php if ($unreadNotifications > 0): ?>
                                <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" id="navNotificationBadge">
                                    <?= $unreadNotifications ?>
                                    <span class="visually-hidden">unread messages</span>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- User Avatar & Dropdown -->
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-1 rounded-pill" href="#" id="userMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= get_avatar_url($currentUser['profile_photo']) ?>" alt="Avatar" class="rounded-circle border border-2 border-primary" width="34" height="34" style="object-fit: cover;">
                            <span class="d-none d-lg-inline fw-semibold text-white"><?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 py-2" aria-labelledby="userMenuDropdown">
                            <li class="px-3 py-1 border-bottom mb-2">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($currentUser['name']) ?></div>
                                <div class="text-muted small">@<?= htmlspecialchars($currentUser['username']) ?> &bull; <span class="badge bg-primary-subtle text-primary"><?= ucfirst($currentUser['role']) ?></span></div>
                                <div class="text-success small mt-1"><i class="bi bi-award-fill"></i> <?= $currentUser['reputation_points'] ?> Rep Points</div>
                            </li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php"><i class="bi bi-person me-2"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/saved.php"><i class="bi bi-bookmark me-2"></i> Saved Posts</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/community-stats.php"><i class="bi bi-graph-up me-2"></i> Community Stats</a></li>
                            
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-semibold" href="<?= BASE_URL ?>/admin/index.php"><i class="bi bi-shield-lock-fill me-2"></i> Admin Panel</a></li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Guest Options -->
                    <li class="nav-item ms-lg-3">
                        <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-light rounded-pill px-3 py-1 text-sm">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="<?= BASE_URL ?>/register.php" class="btn btn-accent-gradient rounded-pill px-3 py-1 text-sm fw-semibold">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
