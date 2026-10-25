<?php
// config/config.php
// LocalLoop Global Configuration

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

// Compute Dynamic Base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Script directory relative to document root
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
// If inside admin or ajax, strip the trailing subfolder for base root
$baseFolder = preg_replace('#/(admin|ajax)$#', '', $scriptName);
$baseFolder = rtrim($baseFolder, '/');

// Properly encode path segments (e.g. spaces -> %20) so assets like CSS and JS never fail over HTTP
$encodedFolder = implode('/', array_map('rawurlencode', explode('/', $baseFolder)));

define('BASE_URL', $protocol . $host . $encodedFolder);
define('SITE_NAME', 'LocalLoop');
define('SITE_TAGLINE', 'Your Community. One Platform.');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads');

// Include Database
require_once __DIR__ . '/database.php';

// Auto-sync sample user passwords once so demo logins work reliably
try {
    static $synced = false;
    if (!$synced && isset($pdo)) {
        $check = $pdo->query("SELECT password_hash FROM users WHERE username = 'admin'")->fetchColumn();
        if ($check && !password_verify('admin123', $check)) {
            $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
            $userHash = password_hash('password123', PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'")->execute([$adminHash]);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE username != 'admin'")->execute([$userHash]);
        }
        $synced = true;
    }
} catch (Exception $e) {
    // Ignore if table not yet created
}
