<?php
// messages.php
// LocalLoop Direct Loop Messaging Interface
// Fully updated for Civic Glass Design System & Robust Database Compatibility

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

global $pdo;

// Check user authentication
$isLoggedIn = is_logged_in();
$currentUser = current_user($pdo);

// If guest access is attempted via POST, redirect to login
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !$currentUser) {
    $_SESSION['flash_error'] = "Please sign in to send messages.";
    $_SESSION['redirect_after_login'] = BASE_URL . '/messages.php';
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Target conversation user
$targetParam = $_GET['user'] ?? null;
$activeContact = null;
$conversations = [];
$messages = [];
$myId = $currentUser ? (int)$currentUser['id'] : 0;

if ($pdo) {
    try {
        // 1. Resolve target contact if provided
        if (!empty($targetParam)) {
            $targetStmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR id = ?) AND status = 'active' LIMIT 1");
            $targetStmt->execute([$targetParam, $targetParam]);
            $candidate = $targetStmt->fetch();
            if ($candidate && (!$currentUser || $candidate['id'] != $currentUser['id'])) {
                $activeContact = $candidate;
            }
        }

        // 2. If no target specified or target is self, find user's most recent chat partner
        if (!$activeContact && $currentUser) {
            $recentStmt = $pdo->prepare("
                SELECT u.* 
                FROM users u
                JOIN messages m ON (m.sender_id = u.id AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = u.id)
                WHERE u.id != ? AND u.status = 'active'
                ORDER BY m.created_at DESC
                LIMIT 1
            ");
            $recentStmt->execute([$myId, $myId, $myId]);
            $activeContact = $recentStmt->fetch();
        }

        // 3. Fallback to another active neighbor if still no contact
        if (!$activeContact) {
            $fallbackStmt = $pdo->prepare("SELECT * FROM users WHERE id != ? AND status = 'active' ORDER BY reputation_points DESC LIMIT 1");
            $fallbackStmt->execute([$myId]);
            $activeContact = $fallbackStmt->fetch();
        }

        // 4. Handle sending a new message
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['send_message']) && $currentUser) {
            if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $msgText = trim($_POST['message'] ?? '');
                $recipientId = (int)($_POST['recipient_id'] ?? ($activeContact['id'] ?? 0));

                if (!empty($msgText) && $recipientId > 0 && $recipientId != $currentUser['id']) {
                    $insertStmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
                    $insertStmt->execute([$currentUser['id'], $recipientId, $msgText]);

                    // Send notification to recipient
                    $snippet = function_exists('mb_substr') ? mb_substr($msgText, 0, 45) : substr($msgText, 0, 45);
                    create_notification(
                        $pdo,
                        $recipientId,
                        $currentUser['id'],
                        'message',
                        "{$currentUser['name']} sent you a message: " . $snippet . (strlen($msgText) > 45 ? '...' : ''),
                        BASE_URL . "/messages.php?user=" . urlencode($currentUser['username'] ?? $currentUser['id'])
                    );

                    $redirectUser = $activeContact['username'] ?? $recipientId;
                    header("Location: " . BASE_URL . "/messages.php?user=" . urlencode($redirectUser));
                    exit;
                }
            }
        }

        // 5. Mark messages from active contact as read
        if ($activeContact && $currentUser) {
            $markStmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
            $markStmt->execute([$activeContact['id'], $currentUser['id']]);
        }

        // 6. Fetch conversation contacts list (using unique positional parameters to prevent SQLSTATE[HY093])
        $convQuery = "SELECT u.id, u.name, u.username, u.profile_photo, u.role,
                             (SELECT message FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
                             (SELECT created_at FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message_time,
                             (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
                      FROM users u
                      WHERE u.id != ? AND u.status = 'active'
                      ORDER BY (last_message_time IS NOT NULL) DESC, last_message_time DESC, u.reputation_points DESC
                      LIMIT 25";
        $convStmt = $pdo->prepare($convQuery);
        $convStmt->execute([$myId, $myId, $myId, $myId, $myId, $myId]);
        $conversations = $convStmt->fetchAll();

        // 7. Fetch active message thread
        if ($activeContact) {
            $msgQuery = "SELECT m.*, u.name as sender_name, u.profile_photo as sender_avatar
                         FROM messages m
                         JOIN users u ON m.sender_id = u.id
                         WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                            OR (m.sender_id = ? AND m.receiver_id = ?)
                         ORDER BY m.created_at ASC";
            $msgStmt = $pdo->prepare($msgQuery);
            $msgStmt->execute([$myId, $activeContact['id'], $activeContact['id'], $myId]);
            $messages = $msgStmt->fetchAll();
        }

    } catch (Exception $e) {
        $chatError = $e->getMessage();
    }
}

$pageTitle = "Messages & Direct Loops";
require_once __DIR__ . '/includes/header.php';
?>

<div class="messages-page-wrapper">
    
    <?php if (!$currentUser): ?>
        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-3 d-flex align-items-center justify-content-between p-3" style="background: rgba(0, 99, 152, 0.08); border-left: 4px solid var(--secondary) !important;">
            <div class="d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary" style="font-size: 22px;">info</span>
                <span class="small text-dark font-medium">You are previewing Direct Loop as a guest. Sign in to chat directly with verified neighbors.</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-primary rounded-pill px-3">Sign In</a>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Join LocalLoop</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($chatError)): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-3 d-flex align-items-center gap-2 p-3">
            <span class="material-symbols-outlined text-warning">warning</span>
            <span class="small">Messaging service notification: <?= htmlspecialchars($chatError) ?></span>
        </div>
    <?php endif; ?>

    <div class="messages-card-container">
        
        <!-- Left Pane: Conversations List -->
        <div class="chat-conversations-pane <?= (isset($_GET['user']) && !empty($_GET['user'])) ? 'hide-mobile' : '' ?>" id="conversationsPane">
            <div class="chat-conversations-header">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary" style="font-size: 20px;">chat</span>
                        <h6 class="fw-bold mb-0 text-dark">Direct Loop</h6>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1" style="font-size: 0.7rem; font-weight: 600;">
                        Ward <?= htmlspecialchars($currentAreaName ?? 'Local Ward') ?>
                    </span>
                </div>
                
                <div class="chat-search-wrap">
                    <span class="material-symbols-outlined search-icon">search</span>
                    <input type="text" id="chatSearchInput" class="chat-search-input" placeholder="Search neighbors..." autocomplete="off">
                </div>
            </div>

            <div class="chat-conversations-list" id="conversationsList">
                <?php if (empty($conversations)): ?>
                    <div class="p-4 text-center my-auto">
                        <span class="material-symbols-outlined text-muted mb-2" style="font-size: 36px;">group_off</span>
                        <p class="text-secondary small mb-0">No other neighbors found in this loop yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $c): 
                        $isSelected = ($activeContact && $activeContact['id'] == $c['id']);
                        $avatarUrl = get_avatar_url($c['profile_photo']);
                    ?>
                        <a href="<?= BASE_URL ?>/messages.php?user=<?= urlencode($c['username']) ?>" 
                           class="chat-conv-item <?= $isSelected ? 'active' : '' ?>"
                           data-name="<?= strtolower(htmlspecialchars($c['name'])) ?>"
                           data-username="<?= strtolower(htmlspecialchars($c['username'])) ?>">
                            
                            <div class="chat-avatar-wrap">
                                <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($c['name']) ?>" class="chat-avatar-img">
                                <span class="chat-online-dot"></span>
                            </div>

                            <div class="chat-conv-info">
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="chat-conv-name"><?= htmlspecialchars($c['name']) ?></span>
                                    <?php if ($c['role'] === 'admin'): ?>
                                        <span class="badge bg-danger-subtle text-danger border" style="font-size: 0.6rem; padding: 1px 5px;">Admin</span>
                                    <?php elseif ($c['role'] === 'business'): ?>
                                        <span class="badge bg-warning-subtle text-warning border" style="font-size: 0.6rem; padding: 1px 5px;">Biz</span>
                                    <?php endif; ?>
                                </div>
                                <div class="chat-conv-preview">
                                    <?= htmlspecialchars($c['last_message'] ?: 'Say hello to start loop...') ?>
                                </div>
                            </div>

                            <div class="chat-conv-meta">
                                <div class="chat-conv-time">
                                    <?= $c['last_message_time'] ? date('h:i A', strtotime($c['last_message_time'])) : '' ?>
                                </div>
                                <?php if ($c['unread_count'] > 0): ?>
                                    <span class="chat-unread-badge"><?= $c['unread_count'] ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Pane: Active Chat Thread -->
        <div class="chat-active-pane <?= !isset($_GET['user']) ? 'd-none d-md-flex' : '' ?>" id="chatActivePane">
            <?php if ($activeContact): ?>
                
                <!-- Chat Header -->
                <div class="chat-active-header">
                    <div class="d-flex align-items-center gap-2.5">
                        <!-- Mobile back button to show conversation list -->
                        <a href="<?= BASE_URL ?>/messages.php" class="btn btn-sm btn-light border-0 rounded-circle p-2 d-md-none text-secondary me-1" title="Back to conversations">
                            <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span>
                        </a>

                        <div class="chat-avatar-wrap">
                            <img src="<?= get_avatar_url($activeContact['profile_photo']) ?>" width="44" height="44" class="rounded-circle border" style="object-fit: cover;" alt="<?= htmlspecialchars($activeContact['name']) ?>">
                            <span class="chat-online-dot"></span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars($activeContact['name']) ?></h6>
                                <?php if ($activeContact['role'] === 'admin'): ?>
                                    <span class="badge bg-danger-subtle text-danger border rounded-pill" style="font-size: 0.65rem;">Ward Admin</span>
                                <?php elseif ($activeContact['role'] === 'business'): ?>
                                    <span class="badge bg-warning-subtle text-dark border rounded-pill" style="font-size: 0.65rem;">Local Business</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-success small d-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                                <span class="d-inline-block rounded-circle bg-success" style="width: 6px; height: 6px;"></span>
                                <span>Active neighbor &bull; @<?= htmlspecialchars($activeContact['username']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= BASE_URL ?>/profile.php?user=<?= urlencode($activeContact['username']) ?>" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 text-secondary d-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">person</span>
                            <span class="d-none d-sm-inline">Profile</span>
                        </a>
                    </div>
                </div>

                <!-- Chat Messages Body -->
                <div class="chat-active-body" id="chatMessageThread">
                    <?php if (empty($messages)): ?>
                        <div class="text-center my-auto py-5">
                            <div class="rounded-circle bg-white shadow-sm border mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; color: var(--secondary);">
                                <span class="material-symbols-outlined" style="font-size: 28px;">chat_bubble</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Direct loop with <?= htmlspecialchars($activeContact['name']) ?></h6>
                            <p class="text-secondary small mb-3">Send a greeting or ask a question to start your neighbor loop!</p>
                            
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 small" onclick="quickMessage('Hello neighbor! How are things on your street?')">👋 "Hello neighbor!"</button>
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 small" onclick="quickMessage('Hey, wanted to ask about the ward community initiative!')">🌿 "Ward initiative info"</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Loop Security Assurance Notice -->
                        <div class="text-center my-2">
                            <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill" style="font-size: 0.7rem; font-weight: 500;">
                                <span class="material-symbols-outlined align-middle me-1" style="font-size: 13px;">lock</span>
                                End-to-end verified resident loop &bull; LocalLoop Direct
                            </span>
                        </div>

                        <?php foreach ($messages as $msg): 
                            $isSentByMe = ($currentUser && $msg['sender_id'] == $currentUser['id']);
                        ?>
                            <div class="d-flex flex-column <?= $isSentByMe ? 'align-items-end' : 'align-items-start' ?>">
                                <div class="<?= $isSentByMe ? 'chat-bubble-sent' : 'chat-bubble-received' ?>">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                </div>
                                <div class="chat-bubble-time px-1">
                                    <span><?= date('h:i A', strtotime($msg['created_at'])) ?></span>
                                    <?php if ($isSentByMe): ?>
                                        <span class="material-symbols-outlined text-primary" style="font-size: 13px;">done_all</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Chat Footer Composer -->
                <div class="chat-active-footer">
                    <?php if ($currentUser): ?>
                        <form method="POST" action="<?= BASE_URL ?>/messages.php?user=<?= urlencode($activeContact['username']) ?>" class="d-flex align-items-center gap-2" id="chatMessageForm">
                            <?= csrf_field() ?>
                            <input type="hidden" name="send_message" value="1">
                            <input type="hidden" name="recipient_id" value="<?= (int)$activeContact['id'] ?>">

                            <!-- Attachment Button -->
                            <button type="button" class="chat-icon-btn" title="Attach file or photo" onclick="alert('File attachment supported in verified ward loops.')">
                                <span class="material-symbols-outlined" style="font-size: 20px;">attach_file</span>
                            </button>

                            <!-- Quick Emoji Button -->
                            <button type="button" class="chat-icon-btn" title="Add emoji" onclick="addEmoji('😊')">
                                <span class="material-symbols-outlined" style="font-size: 20px;">sentiment_satisfied</span>
                            </button>

                            <!-- Message Input -->
                            <input type="text" name="message" id="chatMsgInput" class="chat-input-pill" placeholder="Type a message to <?= htmlspecialchars($activeContact['name']) ?>..." required autocomplete="off">

                            <!-- Send Button -->
                            <button type="submit" class="chat-btn-send">
                                <span>Send</span>
                                <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-pill bg-light border px-3">
                            <span class="small text-secondary">Sign in with your verified resident profile to send a reply.</span>
                            <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-primary rounded-pill px-3">Sign In</a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="text-center my-auto p-5">
                    <span class="material-symbols-outlined text-muted mb-3" style="font-size: 48px;">chat</span>
                    <h5 class="fw-bold">Select a conversation</h5>
                    <p class="text-secondary small">Pick a neighbor from the left list to begin a direct message loop.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    // 1. Auto-scroll chat thread to bottom
    const thread = document.getElementById('chatMessageThread');
    if (thread) {
        thread.scrollTop = thread.scrollHeight;
    }

    // 2. Client-side Search / Filter for Contacts
    const searchInput = document.getElementById('chatSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.chat-conv-item');
            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                const username = item.getAttribute('data-username') || '';
                if (name.includes(query) || username.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // 3. Quick Message helper
    function quickMessage(text) {
        const input = document.getElementById('chatMsgInput');
        if (input) {
            input.value = text;
            input.focus();
        }
    }

    // 4. Emoji helper
    function addEmoji(emoji) {
        const input = document.getElementById('chatMsgInput');
        if (input) {
            input.value += ' ' + emoji;
            input.focus();
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
