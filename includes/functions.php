<?php
// includes/functions.php
// LocalLoop Helper Functions & Business Logic

require_once __DIR__ . '/../config/config.php';

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function flash($name, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION['flash'][$name] = [
            'message' => $message,
            'type' => $type
        ];
    } elseif (!empty($_SESSION['flash'][$name])) {
        $msg = $_SESSION['flash'][$name];
        unset($_SESSION['flash'][$name]);
        return $msg;
    }
    return null;
}

function display_flash() {
    $html = '';
    
    // Check old style flash keys
    if (!empty($_SESSION['flash_success'])) {
        $html .= '<div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>' . htmlspecialchars($_SESSION['flash_success']) . '</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        unset($_SESSION['flash_success']);
    }
    if (!empty($_SESSION['flash_error'])) {
        $html .= '<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>' . htmlspecialchars($_SESSION['flash_error']) . '</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        unset($_SESSION['flash_error']);
    }
    if (!empty($_SESSION['flash_info'])) {
        $html .= '<div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>' . htmlspecialchars($_SESSION['flash_info']) . '</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        unset($_SESSION['flash_info']);
    }

    if (!empty($_SESSION['flash']) && is_array($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $key => $data) {
            $type = $data['type'] ?? 'info';
            $icon = $type === 'success' ? 'bi-check-circle-fill' : ($type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');
            $html .= '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                        <i class="bi ' . $icon . ' me-2 fs-5"></i>
                        <div>' . htmlspecialchars($data['message']) . '</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
        }
        unset($_SESSION['flash']);
    }
    return $html;
}

function time_ago($timestamp) {
    if (!$timestamp) return 'Unknown';
    $time = strtotime($timestamp);
    if (!$time) return $timestamp;
    
    $diff = time() - $time;
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

function get_active_areas($pdo) {
    if (!$pdo) {
        return [
            ['id' => 1, 'name' => 'North Paravur', 'district' => 'Ernakulam', 'state' => 'Kerala', 'pincode' => '683513'],
            ['id' => 2, 'name' => 'Kodungallur Town', 'district' => 'Thrissur', 'state' => 'Kerala', 'pincode' => '680664'],
            ['id' => 3, 'name' => 'Kakkanad / Infopark', 'district' => 'Ernakulam', 'state' => 'Kerala', 'pincode' => '682030'],
            ['id' => 4, 'name' => 'Aluva Junction', 'district' => 'Ernakulam', 'state' => 'Kerala', 'pincode' => '683101'],
            ['id' => 5, 'name' => 'Thrissur City (Round)', 'district' => 'Thrissur', 'state' => 'Kerala', 'pincode' => '680001'],
            ['id' => 6, 'name' => 'Irinjalakuda', 'district' => 'Thrissur', 'state' => 'Kerala', 'pincode' => '680121'],
            ['id' => 7, 'name' => 'Fort Kochi', 'district' => 'Ernakulam', 'state' => 'Kerala', 'pincode' => '682001'],
        ];
    }
    try {
        $stmt = $pdo->query("SELECT * FROM areas WHERE status = 'active' ORDER BY name ASC");
        $results = $stmt->fetchAll();
        return !empty($results) ? $results : [
            ['id' => 1, 'name' => 'North Paravur', 'district' => 'Ernakulam', 'state' => 'Kerala', 'pincode' => '683513'],
            ['id' => 2, 'name' => 'Kodungallur Town', 'district' => 'Thrissur', 'state' => 'Kerala', 'pincode' => '680664'],
            ['id' => 3, 'name' => 'Kakkanad / Infopark', 'district' => 'Ernakulam', 'state' => 'Kerala', 'pincode' => '682030']
        ];
    } catch (Exception $e) {
        return [
            ['id' => 1, 'name' => 'North Paravur', 'district' => 'Ernakulam', 'state' => 'Kerala', 'pincode' => '683513'],
            ['id' => 2, 'name' => 'Kodungallur Town', 'district' => 'Thrissur', 'state' => 'Kerala', 'pincode' => '680664'],
            ['id' => 3, 'name' => 'Kakkanad / Infopark', 'district' => 'Ernakulam', 'state' => 'Kerala', 'pincode' => '682030']
        ];
    }
}

function get_active_categories($pdo) {
    if (!$pdo) {
        return [
            ['id' => 1, 'name' => 'Announcements', 'slug' => 'announcements', 'color' => '#006398', 'icon' => 'campaign'],
            ['id' => 2, 'name' => 'Events', 'slug' => 'events', 'color' => '#8b5cf6', 'icon' => 'calendar_month'],
            ['id' => 3, 'name' => 'Lost & Found', 'slug' => 'lost-found', 'color' => '#ea580c', 'icon' => 'travel_explore'],
            ['id' => 4, 'name' => 'Jobs', 'slug' => 'jobs', 'color' => '#059669', 'icon' => 'work'],
            ['id' => 5, 'name' => 'Marketplace', 'slug' => 'marketplace', 'color' => '#d97706', 'icon' => 'storefront'],
            ['id' => 6, 'name' => 'Community Help', 'slug' => 'help', 'color' => '#dc2626', 'icon' => 'volunteer_activism'],
            ['id' => 7, 'name' => 'Recommendations', 'slug' => 'recommendations', 'color' => '#ca8a04', 'icon' => 'thumb_up']
        ];
    }
    try {
        $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY id ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [
            ['id' => 1, 'name' => 'Announcements', 'slug' => 'announcements', 'color' => '#006398', 'icon' => 'campaign'],
            ['id' => 2, 'name' => 'Events', 'slug' => 'events', 'color' => '#8b5cf6', 'icon' => 'calendar_month'],
            ['id' => 3, 'name' => 'Lost & Found', 'slug' => 'lost-found', 'color' => '#ea580c', 'icon' => 'travel_explore'],
            ['id' => 4, 'name' => 'Jobs', 'slug' => 'jobs', 'color' => '#059669', 'icon' => 'work'],
            ['id' => 5, 'name' => 'Marketplace', 'slug' => 'marketplace', 'color' => '#d97706', 'icon' => 'storefront'],
            ['id' => 6, 'name' => 'Community Help', 'slug' => 'help', 'color' => '#dc2626', 'icon' => 'volunteer_activism'],
            ['id' => 7, 'name' => 'Recommendations', 'slug' => 'recommendations', 'color' => '#ca8a04', 'icon' => 'thumb_up']
        ];
    }
}

function get_unread_notifications_count($pdo, $user_id) {
    if (!$user_id) return 0;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

function create_notification($pdo, $user_id, $sender_id, $type, $message, $link = '#') {
    if ($user_id == $sender_id) return; // Don't notify self
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, sender_id, type, message, link, is_read) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$user_id, $sender_id, $type, $message, $link]);
}

function get_unread_messages_count($pdo, $user_id) {
    if (!$user_id) return 0;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function award_reputation($pdo, $user_id, $points, $reason = '') {
    if (!$user_id || $points == 0) return;
    $stmt = $pdo->prepare("UPDATE users SET reputation_points = reputation_points + ? WHERE id = ?");
    $stmt->execute([$points, $user_id]);
    
    // Check for automatic badges based on points
    $userPts = $pdo->prepare("SELECT reputation_points FROM users WHERE id = ?");
    $userPts->execute([$user_id]);
    $pts = (int)$userPts->fetchColumn();
    
    // Check if eligible for badges
    $badges = $pdo->query("SELECT * FROM badges WHERE points_required <= {$pts}")->fetchAll();
    foreach ($badges as $badge) {
        $stmtBadge = $pdo->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        $stmtBadge->execute([$user_id, $badge['id']]);
    }
}

function get_user_badges($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT b.* FROM badges b 
                           JOIN user_badges ub ON b.id = ub.badge_id 
                           WHERE ub.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function log_admin_action($pdo, $admin_id, $action, $target_type, $target_id, $notes = '') {
    $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action, target_type, target_id, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$admin_id, $action, $target_type, $target_id, $notes]);
}

function get_avatar_url($photoName) {
    if (empty($photoName) || $photoName === 'default-avatar.png') {
        return BASE_URL . '/assets/images/default-avatar.svg';
    }
    // Check if uploaded file exists
    $fullPath = UPLOAD_DIR . '/profiles/' . $photoName;
    if (file_exists($fullPath)) {
        return BASE_URL . '/uploads/profiles/' . $photoName;
    }
    return BASE_URL . '/assets/images/default-avatar.svg';
}

function upload_image_file($file, $destinationFolder, $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'], $maxBytes = 5242880) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file upload parameters.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'No file uploaded.'];
        }
        return ['success' => false, 'error' => 'Upload error code: ' . $file['error']];
    }

    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'File exceeds 5MB limit.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExts)];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $validMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'];
    if (!in_array($mime, $validMimes)) {
        return ['success' => false, 'error' => 'MIME type rejected: ' . $mime];
    }

    $targetDir = UPLOAD_DIR . '/' . trim($destinationFolder, '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = uniqid('ll_', true) . '.' . $ext;
    $targetPath = $targetDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Failed to save file to disk.'];
    }

    return ['success' => true, 'fileName' => $fileName];
}
