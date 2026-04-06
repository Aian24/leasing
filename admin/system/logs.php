<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — Audit Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sections.css">
</head>
<body>
    <?php include '../includes/layout/sidebar.php'; ?>
    <div id="admin-main">
        <?php include '../includes/layout/header.php'; ?>
        <div id="admin-content">
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title"><i class="fa-solid fa-list-check" style="color:var(--primary);margin-right:10px"></i>System Audit Logs</div>
                        <div class="panel-subtitle">Transparent record of all administrative actions</div>
                    </div>
                </div>
                <div class="panel-body">
                    <!-- Toolbar -->
                    <div class="tbar" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px">
                        <div class="search-box" style="max-width:320px; flex:1">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="log-search" placeholder="Search logs..." oninput="SYSTEM_PAGE.debounceSearch(this.value)">
                        </div>
                        <div style="display:flex; gap:10px; align-items:center">
                            <span style="font-size:0.75rem; color:var(--muted); font-weight:600">Show:</span>
                            <select id="log-limit" class="btn btn-ghost btn-sm" onchange="SYSTEM_PAGE.changeLimit(this.value)" style="cursor:pointer; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1)">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <button class="btn btn-ghost btn-sm" onclick="SYSTEM_PAGE.loadLogs()" title="Refresh Logs">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>

                    <div style="overflow-x:auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Level</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody id="logs-tbody">
                                <tr><td colspan="6" style="text-align:center;padding:40px">Analyzing logs...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:20px; flex-wrap:wrap; gap:15px">
                        <div id="log-info" style="font-size:0.8rem; color:var(--muted)"></div>
                        <div class="pagination" id="log-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/layout/modals.php'; ?>
    <script src="../js/admin-core.js"></script>
    <script src="../js/system.js"></script>
</body>
</html>
