<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — System Info</title>
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
            <div style="margin-bottom:24px">
                <div class="panel-title" style="font-size:1.5rem"><i class="fa-solid fa-server" style="color:var(--primary);margin-right:12px"></i>Server Configuration</div>
                <div class="panel-subtitle">Technical details of your hosting environment</div>
            </div>
            <div id="sys-info-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px">
                <!-- Dynamic Content -->
            </div>
        </div>
    </div>
    <?php include '../includes/layout/modals.php'; ?>
    <script src="../js/admin-core.js"></script>
    <script src="../js/system.js"></script>
</body>
</html>
