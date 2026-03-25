<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — Lessees Management</title>
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
            <?php
            $slug = 'lessees.php';
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
                <!-- ══ Stats Strip ══ -->
                <div class="ls-grid">
                    <div class="ls-card">
                        <div class="glow" style="background:#6366f1"></div>
                        <div class="ls-label">Total Lessees</div>
                        <div class="ls-val" id="stat-total" style="color:#a5b4fc">—</div>
                    </div>
                    <div class="ls-card">
                        <div class="glow" style="background:#22c55e"></div>
                        <div class="ls-label">Active Leases</div>
                        <div class="ls-val" id="stat-active" style="color:#86efac">—</div>
                    </div>
                    <div class="ls-card">
                        <div class="glow" style="background:#38bdf8"></div>
                        <div class="ls-label">Expiring (30 days)</div>
                        <div class="ls-val" id="stat-expiring" style="color:#7dd3fc">—</div>
                    </div>
                    <div class="ls-card">
                        <div class="glow" style="background:#f59e0b"></div>
                        <div class="ls-label">Expired</div>
                        <div class="ls-val" id="stat-expired" style="color:#fcd34d">—</div>
                    </div>
                </div>

                <!-- ══ Data Table Panel ══ -->
                <div class="panel" style="margin-bottom:22px">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title"><i class="fa-solid fa-table" style="color:#7dd3fc;margin-right:8px"></i>Lessee Records</div>
                            <div class="panel-subtitle">Create, browse, search, edit, and delete records from the database</div>
                        </div>
                        <div style="display:flex;gap:10px">
                            <button class="btn btn-sm" style="background:var(--primary);color:#fff;border:none" onclick="openCreateModal()">
                                <i class="fa-solid fa-plus"></i> Create New
                            </button>
                            <button class="btn btn-ghost btn-sm" onclick="exportCurrentCSV()">
                                <i class="fa-solid fa-file-export"></i> Export
                            </button>
                            <button class="btn btn-sm" style="background:rgba(239,68,68,0.15);color:#fca5a5;border:1px solid rgba(239,68,68,0.3)" onclick="confirmDeleteAll()">
                                <i class="fa-solid fa-trash-can"></i> Delete All
                            </button>
                        </div>
                    </div>
                    <div class="panel-body">

                        <!-- Toolbar -->
                        <div class="tbar">
                            <div class="search-box" style="max-width:320px">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="tbl-search" placeholder="Search by company, space code…" oninput="debounceSearch(this.value)">
                            </div>
                            <div style="display:flex; gap:8px; align-items:center">
                                <span style="font-size:0.75rem; color:var(--muted); font-weight:600">Show:</span>
                                <select id="tbl-limit" class="btn btn-ghost btn-sm" onchange="changeLimit(this.value)" style="cursor:pointer">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <button class="btn btn-ghost btn-sm" onclick="loadTable()" title="Refresh Data">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div style="overflow-x:auto">
                            <table class="data-table" id="lessee-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Company Name</th>
                                        <th>Store Name</th>
                                        <th>Business</th>
                                        <th>Owner / Rep</th>
                                        <th>Space</th>
                                        <th>Area</th>
                                        <th>Rent</th>
                                        <th>Period</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="lessee-tbody">
                                    <tr><td colspan="12" style="text-align:center;padding:40px">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;flex-wrap:wrap;gap:12px">
                            <div id="tbl-info" style="font-size:0.8rem;color:var(--muted)"></div>
                            <div class="pagination" id="tbl-pagination"></div>
                        </div>
                    </div>
                </div>

                <!-- ══ Upload Panel ══ -->
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title"><i class="fa-solid fa-file-csv" style="color:#86efac;margin-right:8px"></i>Import via CSV</div>
                            <div class="panel-subtitle">Upload CSV to append new records</div>
                        </div>
                        <span id="upload-status-chip" class="chip" style="display:none"></span>
                    </div>
                    <div class="panel-body">
                        <div id="drop-zone" class="drop-zone" ondragover="this.classList.add('drag-over');event.preventDefault()" ondragleave="this.classList.remove('drag-over')" ondrop="this.classList.remove('drag-over');event.preventDefault();onFileSelected(event.dataTransfer)">
                            <input type="file" id="csv-input" accept=".csv" onchange="onFileSelected(this)" style="position:absolute;inset:0;opacity:0;cursor:pointer;">
                            <div class="drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="drop-title">Drag & drop CSV here</div>
                            <div class="drop-sub" or click to browse</div>
                            <div class="drop-fname" id="drop-fname"></div>
                        </div>

                        <div class="upload-progress" id="upload-progress"><div class="upload-progress-fill" id="upload-progress-fill"></div></div>

                        <div style="display:flex;gap:12px;margin-top:16px;align-items:center">
                            <button class="btn btn-primary" id="btn-upload" onclick="uploadCSV()" disabled><i class="fa-solid fa-upload"></i> Import</button>
                            <button class="btn btn-ghost btn-sm" onclick="resetUpload()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div><!-- /admin-content -->
    </div><!-- /admin-main -->

    <!-- MODALS -->
    <!-- Using specialized lessee modal structure (inline or from modals include) -->
    <div id="create-overlay" class="modal-backdrop">
        <div class="modal" style="max-width:650px">
            <div class="modal-header">
                <div class="modal-title" id="modal-form-title">Create New Lessee</div>
                <button class="modal-close" onclick="closeCreateModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="form-create-lessee" onsubmit="submitCreateForm(event)">
                    <input type="hidden" name="id" id="form-lessee-id" value="">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Company Name *</label>
                            <input type="text" name="company_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Trade / Store Name</label>
                            <input type="text" name="trade_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Space Code</label>
                            <input type="text" name="space_code" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Owner / Rep Name</label>
                            <input type="text" name="owner_lessee_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Area (sqm)</label>
                            <input type="number" step="0.01" name="total_area" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Basic Rent</label>
                            <input type="number" step="0.01" name="basic_rent" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Pending">Pending</option>
                                <option value="Terminated">Terminated</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email_address" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" onclick="closeCreateModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-create">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- GLOBAL CONFIRM -->
    <?php include '../includes/layout/modals.php'; ?>

    <script src="../js/admin-core.js"></script>
    <script src="../js/lessees.js"></script>
</body>

</html>
