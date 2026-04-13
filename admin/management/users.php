<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — LeasePro Admin</title>
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
             <section id="sec-users" class="admin-section active">
            <?php
            $slug = 'users.php';
            $db = getPDO();
            
            // Check for preview mode
            $id_stmt = $db->prepare("SELECT id FROM pages WHERE slug = ?");
            $id_stmt->execute([$slug]);
            $page_id = $id_stmt->fetchColumn();
            
            $dbContent = '';
            if (isset($_GET['preview']) && $page_id) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $dbContent = $_SESSION['builder_preview'][$page_id] ?? '';
            }

            if (empty($dbContent)) {
                $stmt = $db->prepare("SELECT content FROM pages WHERE slug = ?");
                $stmt->execute([$slug]);
                $dbContent = $stmt->fetchColumn();
            }

            if ($dbContent && trim($dbContent) !== '') {
                echo $dbContent;
            } else {
            ?>
                <!-- ══ User Management Panel ══ -->
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title">User Management</div>
                            <div class="panel-subtitle">Create, edit, and manage system users</div>
                        </div>
                        <button class="btn btn-primary" onclick="openAddUser()">
                            <i class="fa-solid fa-plus"></i> Add User
                        </button>
                    </div>
                    <div class="panel-body">
                        <div class="table-toolbar">
                            <div class="search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="user-search" placeholder="Search users…">
                            </div>
                            <div style="display:flex;gap:8px">
                                <button class="btn btn-ghost btn-sm"><i class="fa-solid fa-filter"></i> Filter</button>
                                <button class="btn btn-ghost btn-sm"><i class="fa-solid fa-file-export"></i>
                                    Export</button>
                            </div>
                        </div>
                        <div style="overflow-x:auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ══ Role Summary Cards ══ -->
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
                    <div class="stat-card" style="padding:16px">
                        <div style="font-size:0.75rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.08em">Administrators</div>
                        <div style="font-size:1.5rem;font-weight:800;margin-top:6px;color:#a5b4fc">1</div>
                    </div>
                    <div class="stat-card" style="padding:16px">
                        <div style="font-size:0.75rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.08em">Managers</div>
                        <div style="font-size:1.5rem;font-weight:800;margin-top:6px;color:#7dd3fc">2</div>
                    </div>
                    <div class="stat-card" style="padding:16px">
                        <div style="font-size:0.75rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.08em">Staff</div>
                        <div style="font-size:1.5rem;font-weight:800;margin-top:6px;color:#86efac">2</div>
                    </div>
                    <div class="stat-card" style="padding:16px">
                        <div style="font-size:0.75rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.08em">Viewers</div>
                        <div style="font-size:1.5rem;font-weight:800;margin-top:6px;color:#fcd34d">1</div>
                    </div>
                </div>
            <?php } ?>
             </section>
        </div>
    </div>
    <?php include '../includes/layout/modals.php'; ?>
    <script src="../js/admin-core.js?v=1.1"></script>
    <script src="../js/users.js?v=1.1"></script>
</body>
</html>

