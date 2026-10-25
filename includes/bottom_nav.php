<?php
// includes/bottom_nav.php
// LocalLoop Mobile Civic Glass Bottom Dock

$currentFile = basename($_SERVER['PHP_SELF']);
?>
<nav class="app-bottom-nav">
    <div class="app-bottom-nav-inner">
        <!-- 1. Home Feed -->
        <a href="<?= BASE_URL ?>/feed.php" class="bottom-nav-item <?= in_array($currentFile, ['feed.php', 'index.php']) ? 'active' : '' ?>">
            <span class="material-symbols-outlined text-[22px]">home</span>
            <span>Home</span>
        </a>

        <!-- 2. Explore -->
        <a href="<?= BASE_URL ?>/explore.php" class="bottom-nav-item <?= ($currentFile == 'explore.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined text-[22px]">explore</span>
            <span>Explore</span>
        </a>

        <!-- 3. Central Floating Create Button -->
        <a href="<?= BASE_URL ?>/create-post.php" class="bottom-nav-create-btn" title="Create Post">
            <span class="material-symbols-outlined text-[26px]">add</span>
        </a>

        <!-- 4. Messages -->
        <a href="<?= BASE_URL ?>/messages.php" class="bottom-nav-item <?= ($currentFile == 'messages.php') ? 'active' : '' ?> position-relative">
            <span class="material-symbols-outlined text-[22px]">chat</span>
            <span>Chat</span>
            <?php if (($unreadMsgCount ?? 0) > 0): ?>
                <span class="position-absolute top-1 end-4 translate-middle p-1 bg-secondary rounded-circle"></span>
            <?php endif; ?>
        </a>

        <!-- 5. Profile -->
        <a href="<?= BASE_URL ?>/profile.php" class="bottom-nav-item <?= ($currentFile == 'profile.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined text-[22px]">person</span>
            <span>Profile</span>
        </a>
    </div>
</nav>
