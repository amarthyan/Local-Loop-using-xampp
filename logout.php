<?php
// logout.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
session_start();
$_SESSION['flash_info'] = "You have been logged out successfully.";
header("Location: " . BASE_URL . "/login.php");
exit;
