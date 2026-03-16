<?php
// --- Dynamic Session Heartbeat ---
require_once __DIR__ . '/../../../database/config.php';
$heartbeat_pdo = getPDO();
$current_uid = 1; // Maria Santos / Admin

// Update or create active session for ID 1
$heartbeat_stmt = $heartbeat_pdo->prepare("
    UPDATE user_sessions 
    SET last_activity = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
    WHERE user_id = ? AND is_active = 1
");
$heartbeat_stmt->execute([$current_uid]);

if ($heartbeat_stmt->rowCount() == 0) {
    // If no active session, create one
    $heartbeat_pdo->prepare("
        INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, browser, platform, expires_at)
        VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
    ")->execute([
        $current_uid, 
        bin2hex(random_bytes(16)), 
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', 
        $_SERVER['HTTP_USER_AGENT'] ?? 'Admin Dashboard',
        'Chrome', 'System',
    ]);
}
?>
        <!-- Header -->
        <header id="admin-header">
            <div class="header-left">
                <button class="icon-btn" id="menu-toggle" style="display:none">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <div id="page-title">Dashboard</div>
                    <div id="page-breadcrumb">Admin / Overview</div>
                </div>
            </div>

            <div class="header-right">
                <div class="header-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="global-search" placeholder="Search sections…">
                    <div id="search-results" class="dropdown-menu search-dropdown"></div>
                </div>
                
                <div class="header-notif" id="notif-trigger">
                    <div class="icon-btn" title="Notifications">
                        <i class="fa-regular fa-bell"></i>
                        <span class="dot" id="notif-dot" style="display:none"></span>
                    </div>
                    <div id="notif-dropdown" class="dropdown-menu notif-dropdown">
                        <div class="dropdown-header">
                            <span>Notifications</span>
                            <button onclick="GLOBAL_UI.markAllRead()">Mark all as read</button>
                        </div>
                        <div id="notif-list" class="dropdown-list">
                            <div class="empty">No new notifications</div>
                        </div>
                    </div>
                </div>

                <div class="icon-btn" title="Refresh" onclick="location.reload()">
                    <i class="fa-solid fa-rotate"></i>
                </div>
                <a href="<?php 
                    $in_sub = strpos($_SERVER['PHP_SELF'], '/overview/') !== false || 
                             strpos($_SERVER['PHP_SELF'], '/management/') !== false || 
                             strpos($_SERVER['PHP_SELF'], '/system/') !== false || 
                             strpos($_SERVER['PHP_SELF'], '/content/') !== false;
                    echo $in_sub ? '../../index.html' : '../index.html'; 
                ?>" class="btn btn-ghost btn-sm" style="gap:7px">
                    <i class="fa-solid fa-power-off"></i> Logout
                </a>
            </div>
        </header>

