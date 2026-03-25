<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — Roles & Permissions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sections.css">
    <style>
        .perm-cell { text-align: center; }
        .perm-cell i { font-size: 1.1rem; transition: transform 0.2s; }
        .perm-cell button:hover i { transform: scale(1.25); }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include '../includes/layout/sidebar.php'; ?>

    <!-- MAIN -->
    <div id="admin-main">

        <!-- Header -->
        <?php include '../includes/layout/header.php'; ?>

        <div id="admin-content">
            <?php
            $slug = 'roles.php';
            $stmt = getPDO()->prepare("SELECT content FROM pages WHERE slug = ?");
            $stmt->execute([$slug]);
            $dbContent = $stmt->fetchColumn();

            if ($dbContent && trim($dbContent) !== '') {
                echo $dbContent;
            } else {
            ?>
                <div class="panel" style="margin-bottom:22px">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title"><i class="fa-solid fa-shield-halved" style="color:var(--primary);margin-right:10px"></i>Access Control Matrix</div>
                            <div class="panel-subtitle">Manage module-level permissions for each user role</div>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="ROLES_PAGE.openPermModal()">
                            <i class="fa-solid fa-plus"></i> Add Permission
                        </button>
                    </div>
                    <div class="panel-body">
                        <div style="overflow-x:auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width:35%">Permission Module</th>
                                        <th class="perm-cell">Administrator</th>
                                        <th class="perm-cell">Manager</th>
                                        <th class="perm-cell">Staff</th>
                                        <th class="perm-cell">Viewer</th>
                                        <th style="text-align:right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="roles-tbody">
                                    <tr><td colspan="6" style="text-align:center;padding:40px">Loading matrix...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Role Definitions Grid -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-top:24px">
                    <div class="panel" style="margin-bottom:0">
                        <div class="panel-body">
                            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px">
                                <div style="width:40px;height:40px;border-radius:10px;background:rgba(59, 130, 246, 0.1);display:flex;align-items:center;justify-content:center;color:#3b82f6">
                                    <i class="fa-solid fa-user-shield"></i>
                                </div>
                                <div style="font-weight:800; color:#fff">Administrator</div>
                            </div>
                            <p style="font-size:0.8125rem; color:var(--muted); line-height:1.6">
                                Full system ownership. Can access all financial data, system logs, and manage other administrators.
                            </p>
                        </div>
                    </div>

                    <div class="panel" style="margin-bottom:0">
                        <div class="panel-body">
                            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px">
                                <div style="width:40px;height:40px;border-radius:10px;background:rgba(56, 189, 248, 0.1);display:flex;align-items:center;justify-content:center;color:#38bdf8">
                                    <i class="fa-solid fa-briefcase"></i>
                                </div>
                                <div style="font-weight:800; color:#fff">Manager</div>
                            </div>
                            <p style="font-size:0.8125rem; color:var(--muted); line-height:1.6">
                                Operational control. Can manage lessees, contracts, and view reports. Limited system configuration access.
                            </p>
                        </div>
                    </div>

                    <div class="panel" style="margin-bottom:0">
                        <div class="panel-body">
                            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px">
                                <div style="width:40px;height:40px;border-radius:10px;background:rgba(34, 197, 94, 0.1);display:flex;align-items:center;justify-content:center;color:#22c55e">
                                    <i class="fa-solid fa-user-pen"></i>
                                </div>
                                <div style="font-weight:800; color:#fff">Staff</div>
                            </div>
                            <p style="font-size:0.8125rem; color:var(--muted); line-height:1.6">
                                Daily operations. Can create and read records. restricted from deleting data or viewing sensitive logs.
                            </p>
                        </div>
                    </div>

                    <div class="panel" style="margin-bottom:0">
                        <div class="panel-body">
                            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px">
                                <div style="width:40px;height:40px;border-radius:10px;background:rgba(245, 158, 11, 0.1);display:flex;align-items:center;justify-content:center;color:#f59e0b">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <div style="font-weight:800; color:#fff">Viewer</div>
                            </div>
                            <p style="font-size:0.8125rem; color:var(--muted); line-height:1.6">
                                Read-only access. Ideal for external auditors or executive overview where no data modification is allowed.
                            </p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div><!-- /admin-content -->
    </div><!-- /admin-main -->

    <div id="perm-overlay" class="modal-backdrop">
        <div class="modal" style="max-width:400px">
            <div class="modal-header">
                <div class="modal-title" id="perm-modal-title">Add New Permission</div>
                <button class="modal-close" onclick="ROLES_PAGE.closePermModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="form-add-perm" onsubmit="ROLES_PAGE.submitNewPermission(event)">
                    <input type="hidden" name="id" id="perm-edit-id" value="">
                    <div class="form-group">
                        <label class="form-label">Permission / Module Name</label>
                        <input type="text" name="name" id="perm-name-input" class="form-control" placeholder="e.g. Audit Logs" required autofocus>
                    </div>
                    
                    <div id="initial-access-section" style="margin-top:20px; border-top:1px solid var(--border); padding-top:15px">
                        <label class="form-label" style="margin-bottom:12px">Access Levels</label>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem">
                                <input type="checkbox" name="admin" checked style="width:16px; height:16px"> Administrator
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem">
                                <input type="checkbox" name="manager" style="width:16px; height:16px"> Manager
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem">
                                <input type="checkbox" name="staff" style="width:16px; height:16px"> Staff
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem">
                                <input type="checkbox" name="viewer" style="width:16px; height:16px"> Viewer
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer" style="margin-top:25px; padding-bottom:0">
                        <button type="button" class="btn btn-ghost" onclick="ROLES_PAGE.closePermModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-perm">Add Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/layout/modals.php'; ?>

    <script src="../js/admin-core.js"></script>
    <script src="../js/roles.js"></script>
</body>

</html>
