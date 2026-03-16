<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — App Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sections.css">
    <style>
        /* Modern Switch Styles */
        .switch { position: relative; display: inline-block; width: 46px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border: 1px solid var(--border); }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 3px; background-color: #94a3b8; transition: .4s; }
        input:checked + .slider { background-color: var(--primary); border-color: var(--primary); }
        input:focus + .slider { box-shadow: 0 0 1px var(--primary); }
        input:checked + .slider:before { transform: translateX(20px); background-color: white; }
        .slider.round { border-radius: 34px; }
        .slider.round:before { border-radius: 50%; }
    </style>
</head>
<body>
    <?php include '../includes/layout/sidebar.php'; ?>
    <div id="admin-main">
        <?php include '../includes/layout/header.php'; ?>
        <div id="admin-content" style="max-width:800px">
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title"><i class="fa-solid fa-gears" style="color:var(--primary);margin-right:10px"></i>Application Settings</div>
                        <div class="panel-subtitle">Global configuration for the LeasePro environment</div>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="settings-list">
                        <!-- Dynamic Content -->
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
