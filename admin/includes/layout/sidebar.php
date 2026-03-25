<?php
$current_page = basename($_SERVER['PHP_SELF']);
// Determine if we are in a subfolder (overview, management, system, content)
$in_subfolder = strpos($_SERVER['PHP_SELF'], '/overview/') !== false || 
                strpos($_SERVER['PHP_SELF'], '/management/') !== false || 
                strpos($_SERVER['PHP_SELF'], '/system/') !== false || 
                strpos($_SERVER['PHP_SELF'], '/content/') !== false;

$root = $in_subfolder ? '../' : '';
?>
<!-- ═══════════════════════════════════ SIDEBAR ═══════════════════════════════════ -->
<aside id="admin-sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="fa-solid fa-building"></i></div>
        <div class="brand-name">Lease<span>Pro</span></div>
        <div style="margin-left:auto;font-size:10px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;padding:2px 8px;border-radius:100px;font-weight:700">ADMIN</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Overview</div>

        <div class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" 
             onclick="window.location.href='<?php echo $root; ?>overview/dashboard.php'">
            <i class="fa-solid fa-gauge-high"></i>
            Dashboard
        </div>

        <div class="nav-section-label">Management</div>

        <div class="nav-item <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" 
             onclick="window.location.href='<?php echo $root; ?>management/users.php'">
            <i class="fa-solid fa-users"></i>
            User Management
            <span class="badge" id="nav-badge-users">6</span>
        </div>

        <div class="nav-item <?php echo ($current_page == 'lessees.php') ? 'active' : ''; ?>"
             onclick="window.location.href='<?php echo $root; ?>management/lessees.php'">
            <i class="fa-solid fa-file-csv"></i>
            Lessee Management
            <span class="badge" id="nav-count" style="background:rgba(34,197,94,0.2);color:#86efac">—</span>
        </div>

        <div class="nav-item <?php echo ($current_page == 'contracts.php') ? 'active' : ''; ?>"
             onclick="window.location.href='<?php echo $root; ?>management/contracts.php'">
            <i class="fa-solid fa-file-signature"></i>
            Review Contracts
            <span class="badge" id="nav-contracts-count" style="background:rgba(59,130,246,0.2);color:#93c5fd">—</span>
        </div>

        <div class="nav-item <?php echo ($current_page == 'roles.php') ? 'active' : ''; ?>"
             onclick="window.location.href='<?php echo $root; ?>management/roles.php'">
            <i class="fa-solid fa-shield-halved"></i>
            Roles & Permissions
        </div>

        <div class="nav-item <?php echo ($current_page == 'sessions.php') ? 'active' : ''; ?>"
             onclick="window.location.href='<?php echo $root; ?>management/sessions.php'">
            <i class="fa-solid fa-tower-broadcast"></i>
            Active Sessions
            <span class="badge success" id="nav-badge-sessions">0</span>
        </div>

        <div class="nav-item <?php echo ($current_page == 'announcements.php') ? 'active' : ''; ?>"
             onclick="window.location.href='<?php echo $root; ?>management/announcements.php'">
            <i class="fa-solid fa-bullhorn"></i>
            Announcements
            <span class="badge">2</span>
        </div>

        <div class="nav-item <?php echo ($current_page == 'pages.php') ? 'active' : ''; ?>"
             onclick="window.location.href='<?php echo $root; ?>content/pages.php'">
            <i class="fa-solid fa-layer-group"></i>
            Frontend Pages
        </div>

        <div class="nav-section-label">System</div>

        <div class="nav-item <?php echo ($current_page == 'info.php') ? 'active' : ''; ?>"
             onclick="window.location.href='<?php echo $root; ?>system/info.php'">
            <i class="fa-solid fa-server"></i>
            System Info
        </div>

        <div class="nav-item <?php echo ($current_page == 'logs.php') ? 'active' : ''; ?>"
             onclick="window.location.href='<?php echo $root; ?>system/logs.php'">
            <i class="fa-solid fa-scroll"></i>
            Audit Logs
            <span class="badge danger" id="nav-badge-logs">0</span>
        </div>

        <div class="nav-item <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>"
             onclick="window.location.href='<?php echo $root; ?>system/settings.php'">
            <i class="fa-solid fa-sliders"></i>
            App Settings
        </div>

        <div class="nav-section-label">App</div>

        <a class="nav-item" href="../../user/index.php" target="_blank">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            View App
        </a>
        <a class="nav-item" href="../../index.php" target="_blank">
            <i class="fa-solid fa-right-to-bracket"></i>
            Login Page
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-chip">
            <img id="sidebar-user-avatar" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['name']); ?>&background=4f46e5&color=fff&rounded=true" alt="Admin">
            <div class="info">
                <div class="name" id="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
                <div class="role" id="sidebar-user-role"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
            </div>
            <i class="fa-solid fa-ellipsis" style="color:var(--muted);font-size:14px"></i>
        </div>
    </div>
</aside>

