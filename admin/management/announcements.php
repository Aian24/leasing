<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — Announcements</title>
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
                        <div class="panel-title"><i class="fa-solid fa-bullhorn" style="color:var(--primary);margin-right:10px"></i>Announcements</div>
                        <div class="panel-subtitle">Broadcast messages to all system users</div>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="ANNOUNCEMENTS_PAGE.openAddModal()">
                        <i class="fa-solid fa-plus"></i> New Broadcast
                    </button>
                </div>
                <div class="panel-body">
                    <div id="announcements-list" style="display:flex; flex-direction:column; gap:16px">
                        <!-- Dynamic Content -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="ann-overlay" class="modal-backdrop">
        <div class="modal" style="max-width:500px">
            <div class="modal-header">
                <div class="modal-title" id="ann-modal-title">New Broadcast</div>
                <button class="modal-close" onclick="ANNOUNCEMENTS_PAGE.closeModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="ann-form" onsubmit="ANNOUNCEMENTS_PAGE.saveAnnouncement(event)">
                    <input type="hidden" name="id" id="ann-id">
                    <div class="form-group">
                        <label class="form-label">Subject Title</label>
                        <input type="text" name="title" id="ann-title" class="form-control" placeholder="e.g. Server Maintenance" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message Content</label>
                        <textarea name="content" id="ann-content" class="form-control" style="height:120px" placeholder="Write your announcement message here..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alert Type</label>
                        <select name="type" id="ann-type" class="btn" style="width:100%; text-align:left">
                            <option value="info">Information (Blue)</option>
                            <option value="warning">Warning (Yellow)</option>
                            <option value="success">Success (Green)</option>
                            <option value="danger">Urgent (Red)</option>
                        </select>
                    </div>
                    <div class="modal-footer" style="padding:0; margin-top:20px">
                        <button type="button" class="btn btn-ghost" onclick="ANNOUNCEMENTS_PAGE.closeModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Broadcast</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/layout/modals.php'; ?>
    <script src="../js/admin-core.js"></script>
    <script src="../js/announcements.js"></script>
</body>
</html>
