<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card wait-for-data skeleton">
        <div class="sc-glow" style="background:#3b82f6"></div>
        <div class="sc-icon" style="background:rgba(59, 130, 246,0.15);color:#a5b4fc"><i
                class="fa-solid fa-users"></i></div>
        <div class="sc-val" id="stat-users">—</div>
        <div class="sc-label">Total Users</div>
        <div class="sc-trend" style="background:rgba(34,197,94,0.15);color:#86efac">↑ Active</div>
    </div>
    <div class="stat-card wait-for-data skeleton">
        <div class="sc-glow" style="background:#22c55e"></div>
        <div class="sc-icon" style="background:rgba(34,197,94,0.15);color:#86efac"><i
                class="fa-solid fa-user-check"></i></div>
        <div class="sc-val" id="stat-active">—</div>
        <div class="sc-label">Active Accounts</div>
        <div class="sc-trend" style="background:rgba(34,197,94,0.15);color:#86efac">Online</div>
    </div>
    <div class="stat-card wait-for-data skeleton">
        <div class="sc-glow" style="background:#2563eb"></div>
        <div class="sc-icon" style="background:rgba(37, 99, 235,0.15);color:#c4b5fd"><i
                class="fa-solid fa-layer-group"></i></div>
        <div class="sc-val" id="stat-pages">—</div>
        <div class="sc-label">Frontend Pages</div>
        <div class="sc-trend" style="background:rgba(37, 99, 235,0.15);color:#c4b5fd">Live</div>
    </div>
    <div class="stat-card wait-for-data skeleton">
        <div class="sc-glow" style="background:#38bdf8"></div>
        <div class="sc-icon" style="background:rgba(56,189,248,0.15);color:#7dd3fc"><i
                class="fa-solid fa-circle-dot"></i></div>
        <div class="sc-val" id="stat-online">—</div>
        <div class="sc-label">Users Online Now</div>
        <div class="sc-trend" style="background:rgba(56,189,248,0.15);color:#7dd3fc">Live</div>
    </div>
</div>

<!-- Chart + Activity -->
<div style="display:grid;grid-template-columns:1fr 340px;gap:22px;margin-bottom:22px">
    <div class="panel wait-for-data skeleton">
        <div class="panel-header">
            <div>
                <div class="panel-title">Monthly Activity</div>
                <div class="panel-subtitle">Login & action volume over 12 months</div>
            </div>
            <span class="chip chip-blue"><?php echo date('Y'); ?></span>
        </div>
        <div class="panel-body">
            <div class="chart-placeholder" id="monthly-chart"></div>
            <div id="monthly-chart-labels" style="display:flex;justify-content:space-between;margin-top:8px;padding:0 16px">
                <!-- Javascript will load the last 12 months here -->
            </div>
        </div>
    </div>

    <div class="panel wait-for-data skeleton">
        <div class="panel-header">
            <div>
                <div class="panel-title">System Health</div>
                <div class="panel-subtitle">Resource usage</div>
            </div>
            <span class="chip chip-green"><i class="fa-solid fa-circle" style="font-size:7px"></i>
                Healthy</span>
        </div>
        <div class="panel-body">
            <div style="margin-bottom:18px">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                    <span style="font-size:0.8125rem;color:var(--muted)">Disk Usage</span>
                    <span id="dash-disk-val"
                        style="font-size:0.8125rem;font-weight:700;color:var(--text)"></span>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="gauge-disk"></div>
                </div>
            </div>
            <div style="margin-bottom:18px">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                    <span style="font-size:0.8125rem;color:var(--muted)">Memory</span>
                    <span id="dash-mem-val"
                        style="font-size:0.8125rem;font-weight:700;color:var(--text)"></span>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="gauge-mem"></div>
                </div>
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                    <span style="font-size:0.8125rem;color:var(--muted)">CPU Load</span>
                    <span id="dash-cpu-val"
                        style="font-size:0.8125rem;font-weight:700;color:var(--text)"></span>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="gauge-cpu"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions + Recent Users -->
<div style="display:grid;grid-template-columns:1fr 320px;gap:22px;margin-bottom:22px">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Quick Actions</div>
            <div class="panel-subtitle">Common admin shortcuts</div>
        </div>
        <div class="panel-body" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
            <button class="quick-action-btn" onclick="window.location.href='../management/users.php';">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add User</span>
            </button>
            <button class="quick-action-btn"
                onclick="window.location.href='../management/announcements.php';">
                <i class="fa-solid fa-bullhorn"></i>
                <span>Announce</span>
            </button>
            <button class="quick-action-btn" onclick="window.location.href='../system/logs.php';">
                <i class="fa-solid fa-scroll"></i>
                <span>View Logs</span>
            </button>
            <button class="quick-action-btn" onclick="window.location.href='../management/sessions.php';">
                <i class="fa-solid fa-tower-broadcast"></i>
                <span>Sessions</span>
            </button>
            <button class="quick-action-btn" onclick="window.location.href='../system/settings.php';">
                <i class="fa-solid fa-sliders"></i>
                <span>Settings</span>
            </button>
            <button class="quick-action-btn" onclick="window.location.href='../system/info.php';">
                <i class="fa-solid fa-server"></i>
                <span>System</span>
            </button>
        </div>
    </div>
    <div class="panel wait-for-data skeleton">
        <div class="panel-header">
            <div class="panel-title">Recent Users</div>
        </div>
        <div class="panel-body" id="recent-users-list"
            style="display:flex;flex-direction:column;gap:10px"></div>
    </div>
</div>

<!-- Activity Feed -->
<div class="panel wait-for-data skeleton">
    <div class="panel-header">
        <div>
            <div class="panel-title">Recent Activity</div>
            <div class="panel-subtitle">Latest system events</div>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="window.location.href='../system/logs.php';"><i
                class="fa-solid fa-list"></i> View All Logs</button>
    </div>
    <div class="panel-body" id="activity-feed"></div>
</div>

<!-- Announcements Feed -->
<div class="panel">
    <div class="panel-header">
        <div>
            <div class="panel-title"><i class="fa-solid fa-bullhorn" style="color:var(--warning);margin-right:10px"></i>System Announcements</div>
            <div class="panel-subtitle">Global broadcast messages</div>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="window.location.href='../management/announcements.php';"><i class="fa-solid fa-plus"></i></button>
    </div>
    <div class="panel-body" id="announcements-feed">
        <div class="empty-state" style="text-align:center;padding:20px;color:var(--muted)">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading...
        </div>
    </div>
</div>
