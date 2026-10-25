<?php
// includes/auth.php
// User Authentication State & Access Guards

require_once __DIR__ . '/../config/config.php';

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user($pdo) {
    if (!is_logged_in() || !$pdo) {
        return null;
    }
    static $userCache = null;
    if ($userCache !== null) {
        return $userCache;
    }
    try {
        $stmt = $pdo->prepare("SELECT u.*, a.name as area_name, a.district as area_district 
                               FROM users u 
                               LEFT JOIN areas a ON u.area_id = a.id 
                               WHERE u.id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userCache = $stmt->fetch();
        return $userCache;
    } catch (Exception $e) {
        return null;
    }
}

function is_admin($pdo = null) {
    if (!is_logged_in()) {
        return false;
    }
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = "Please log in to access this page.";
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? (BASE_URL . '/feed.php');
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        $_SESSION['flash_error'] = "Access denied. Administrator privileges required.";
        header("Location: " . BASE_URL . "/feed.php");
        exit;
    }
}

function login_user($user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_area_id'] = $user['area_id'];
    $_SESSION['profile_photo'] = $user['profile_photo'] ?? 'default-avatar.png';
}

function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
