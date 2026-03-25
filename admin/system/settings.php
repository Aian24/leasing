<?php require_once '../includes/auth.php'; ?>
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
        .settings-container { width: 100%; }
        
        /* Modern Switch Styles */
        .switch { position: relative; display: inline-block; width: 42px; height: 22px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.08); transition: .3s; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: #94a3b8; transition: .3s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary); border-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(20px); background-color: white; }

        /* Settings Row Layout */
        .settings-row { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 24px 0; 
            border-bottom: 1px solid rgba(255,255,255,0.04); 
            gap: 40px;
        }
        .settings-row:last-child { border-bottom: none; }
        
        .settings-info { flex: 1; }
        .settings-label { display: block; font-size: 0.9375rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
        .settings-desc { font-size: 0.8125rem; color: var(--muted); line-height: 1.5; }
        
        .settings-action { width: 300px; display: flex; justify-content: flex-end; flex-shrink: 0; }
        
        select.form-control {
            appearance: auto;
            background-image: none;
            cursor: pointer;
        }

        .form-control {
            width: 100%;
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            color: #fff;
            padding: 10px 14px;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.6);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .settings-footer-info {
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px;
            background: rgba(59, 130, 246, 0.04);
            border: 1px solid rgba(59, 130, 246, 0.1);
            border-radius: 12px;
            color: #94a3b8;
            font-size: 0.8125rem;
        }
        .settings-footer-info i { color: var(--primary); }
    </style>
</head>
<body>
    <?php include '../includes/layout/sidebar.php'; ?>
    <div id="admin-main">
        <?php include '../includes/layout/header.php'; ?>
        <div id="admin-content">
            <div class="settings-container">
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
                
                <div class="settings-footer-info">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Changes are saved automatically upon modification.</span>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/layout/modals.php'; ?>
    <script src="../js/admin-core.js"></script>
    <script src="../js/system.js"></script>
</body>
</html>
