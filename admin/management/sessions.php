<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — Active Sessions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sections.css">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include '../includes/layout/sidebar.php'; ?>

    <!-- MAIN -->
    <div id="admin-main">

        <!-- Header -->
        <?php include '../includes/layout/header.php'; ?>

        <div id="admin-content">

            <!-- Stats -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:24px">
                <div class="panel" style="margin-bottom:0">
                    <div class="panel-body" style="display:flex; align-items:center; gap:18px">
                        <div style="width:50px; height:50px; border-radius:12px; background:rgba(34,197,94,0.1); display:flex; align-items:center; justify-content:center; color:#22c55e; font-size:1.25rem">
                            <i class="fa-solid fa-users-viewfinder"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem; font-weight:800; color:#fff" id="sess-active-count">0</div>
                            <div style="font-size:0.75rem; color:var(--muted); font-weight:700; text-transform:uppercase">Online Now</div>
                        </div>
                    </div>
                </div>

                <div class="panel" style="margin-bottom:0">
                    <div class="panel-body" style="display:flex; align-items:center; gap:18px">
                        <div style="width:50px; height:50px; border-radius:12px; background:rgba(59, 130, 246, 0.1); display:flex; align-items:center; justify-content:center; color:#3b82f6; font-size:1.25rem">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem; font-weight:800; color:#fff" id="sess-today-count">0</div>
                            <div style="font-size:0.75rem; color:var(--muted); font-weight:700; text-transform:uppercase">Logins Today</div>
                        </div>
                    </div>
                </div>

                <div class="panel" style="margin-bottom:0">
                    <div class="panel-body" style="display:flex; align-items:center; gap:18px">
                        <div style="width:50px; height:50px; border-radius:12px; background:rgba(239, 68, 68, 0.1); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:1.25rem">
                            <i class="fa-solid fa-shield-virus"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem; font-weight:800; color:#fff" id="sess-susp-count">0</div>
                            <div style="font-size:0.75rem; color:var(--muted); font-weight:700; text-transform:uppercase">Suspicious</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title"><i class="fa-solid fa-server" style="color:var(--primary);margin-right:10px"></i>Real-time Sessions</div>
                        <div class="panel-subtitle">Monitor and terminate active user connections</div>
                    </div>
                    <button class="btn btn-danger btn-sm" onclick="SESSIONS_PAGE.terminateAll()">
                        <i class="fa-solid fa-bolt"></i> Terminate All
                    </button>
                </div>
                <div class="panel-body">
                    <div style="overflow-x:auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Device / Browser</th>
                                    <th>Login Time</th>
                                    <th>Active For</th>
                                    <th>Status</th>
                                    <th style="text-align:right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="sessions-tbody">
                                <tr><td colspan="7" style="text-align:center;padding:40px">Initializing session scan...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /admin-content -->
    </div><!-- /admin-main -->

    <?php include '../includes/layout/modals.php'; ?>

    <script src="../js/admin-core.js"></script>
    <script src="../js/sessions.js"></script>
</body>

</html>
