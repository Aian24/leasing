<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — Frontend Pages</title>
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
            
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:24px">
                <div class="panel" style="margin-bottom:0">
                    <div class="panel-body" style="display:flex; align-items:center; gap:18px">
                        <div style="width:50px; height:50px; border-radius:12px; background:rgba(34,197,94,0.1); display:flex; align-items:center; justify-content:center; color:#22c55e">
                            <i class="fa-solid fa-file-circle-check"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem; font-weight:800; color:#fff" id="pages-live-count">0</div>
                            <div style="font-size:0.75rem; color:var(--muted); font-weight:700">LIVE PAGES</div>
                        </div>
                    </div>
                </div>
                <div class="panel" style="margin-bottom:0">
                    <div class="panel-body" style="display:flex; align-items:center; gap:18px">
                        <div style="width:50px; height:50px; border-radius:12px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444">
                            <i class="fa-solid fa-eye-slash"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem; font-weight:800; color:#fff" id="pages-hidden-count">0</div>
                            <div style="font-size:0.75rem; color:var(--muted); font-weight:700">HIDDEN</div>
                        </div>
                    </div>
                </div>
                <div class="panel" style="margin-bottom:0">
                    <div class="panel-body" style="display:flex; align-items:center; gap:18px">
                        <div style="width:50px; height:50px; border-radius:12px; background:rgba(59,130,246,0.1); display:flex; align-items:center; justify-content:center; color:#3b82f6">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem; font-weight:800; color:#fff" id="pages-total-count">0</div>
                            <div style="font-size:0.75rem; color:var(--muted); font-weight:700">TOTAL PAGES</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title"><i class="fa-solid fa-laptop-code" style="color:var(--primary);margin-right:10px"></i>Page Management</div>
                        <div class="panel-subtitle">Manage visibility and status of application pages</div>
                    </div>
                </div>
                <div class="panel-body">
                    <div style="overflow-x:auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Page Name</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th>Last Edited</th>
                                    <th>Editor</th>
                                    <th style="text-align:right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pages-tbody">
                                <tr><td colspan="6" style="text-align:center;padding:40px">Loading pages...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/layout/modals.php'; ?>
    <script src="../js/admin-core.js"></script>
    <script src="../js/pages.js"></script>
</body>
</html>
