<?php 
require_once '../includes/auth.php'; 
require_once '../../database/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Signatures — LeasePro Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sections.css">
    <style>
        .settings-container { width: 100%; }
        .form-control {
            width: 100%;
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            color: #fff;
            padding: 12px 16px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.6);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body>
    <?php include '../includes/layout/sidebar.php'; ?>
    <div id="admin-main">
        <?php include '../includes/layout/header.php'; ?>
        <div id="admin-content" class="p-6">
            <div class="settings-container">
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title"><i class="fa-solid fa-signature" style="color:var(--primary);margin-right:10px"></i>Contract Signatories</div>
                            <div class="panel-subtitle">Manage the representative names printed on final contract proposals</div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="space-y-6">
                            
                            <!-- Assistant Settings -->
                            <div class="p-5" style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius: 15px;">
                                <h4 class="text-sm font-bold text-white mb-4"><i class="fa-solid fa-user-pen text-blue-400 mr-2"></i>Leasing Assistant Details</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Full Name</label>
                                        <input type="text" id="set-leasing-ass-name" class="form-control" value="<?php echo htmlspecialchars(getSetting('leasing_assistant_name', 'MS. SHEILA MARIE C. VALERIO')); ?>">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Job Title</label>
                                        <input type="text" id="set-leasing-ass-title" class="form-control" value="<?php echo htmlspecialchars(getSetting('leasing_assistant_title', 'Leasing Assistant')); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Manager Settings -->
                            <div class="p-5" style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius: 15px;">
                                <h4 class="text-sm font-bold text-white mb-4"><i class="fa-solid fa-user-tie text-emerald-400 mr-2"></i>Leasing Manager Details</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Full Name</label>
                                        <input type="text" id="set-leasing-mgr-name" class="form-control" value="<?php echo htmlspecialchars(getSetting('leasing_manager_name', 'MS. KRISTINA G. COMIA')); ?>">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Job Title</label>
                                        <input type="text" id="set-leasing-mgr-title" class="form-control" value="<?php echo htmlspecialchars(getSetting('leasing_manager_title', 'Leasing Manager')); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button onclick="saveSignatures()" class="btn btn-primary" style="padding: 10px 24px; font-weight: bold; border-radius: 12px; font-size: 14px;">
                                    <i class="fa-solid fa-floppy-disk mr-2"></i> Save Signatories
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include '../includes/layout/modals.php'; ?>
    
    <script src="../js/admin-core.js"></script>
    <script>
        async function saveSignatures() {
            const data = {
                leasing_assistant_name: document.getElementById('set-leasing-ass-name').value,
                leasing_assistant_title: document.getElementById('set-leasing-ass-title').value,
                leasing_manager_name: document.getElementById('set-leasing-mgr-name').value,
                leasing_manager_title: document.getElementById('set-leasing-mgr-title').value
            };

            const btn = document.querySelector('button[onclick="saveSignatures()"]');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';
            btn.disabled = true;

            try {
                const res = await fetch('../../api/system_api.php?action=settings_update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const json = await res.json();
                
                if (json.success) {
                    showToast('Contract signatories updated automatically.', 'success');
                } else {
                    showToast(json.message || 'Error updating records.', 'danger');
                }
            } catch (e) {
                console.error(e);
                showToast('Failed to connect to the server.', 'danger');
            } finally {
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-2"></i> Save Signatories';
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
