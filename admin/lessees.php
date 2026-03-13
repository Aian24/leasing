<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — Lessees CSV Manager</title>
    <meta name="description" content="Import, browse, and manage lessee records via CSV upload.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* ── Drop Zone ────────────────────────────────────── */
        .drop-zone {
            border: 2px dashed rgba(99, 102, 241, 0.4);
            border-radius: 18px;
            background: rgba(99, 102, 241, 0.04);
            padding: 48px 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
        }
        .drop-zone:hover,
        .drop-zone.drag-over {
            border-color: rgba(99, 102, 241, 0.7);
            background: rgba(99, 102, 241, 0.1);
            transform: scale(1.01);
        }
        .drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .drop-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 1.75rem;
            color: #a5b4fc;
        }
        .drop-title   { font-size: 1.125rem; font-weight: 800; margin-bottom: 6px; color: var(--text); }
        .drop-sub     { font-size: 0.8125rem; color: var(--muted); }
        .drop-fname   { margin-top: 10px; font-size: 0.8125rem; color: #86efac; font-weight: 700; display: none; }

        /* ── Progress bar for upload ──────────────────────── */
        .upload-progress {
            display: none;
            margin-top: 18px;
            background: rgba(255,255,255,0.05);
            border-radius: 100px;
            height: 8px;
            overflow: hidden;
        }
        .upload-progress-fill {
            height: 100%;
            border-radius: 100px;
            background: linear-gradient(90deg, #6366f1, #a5b4fc);
            width: 0%;
            transition: width 0.3s ease;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0%   { filter: brightness(1); }
            50%  { filter: brightness(1.3); }
            100% { filter: brightness(1); }
        }

        /* ── Result toast ─────────────────────────────────── */
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            min-width: 320px;
            max-width: 420px;
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 0.875rem;
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
            transform: translateX(130%);
            transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1);
            border: 1px solid;
        }
        .toast.show { transform: translateX(0); }
        .toast.success { background: rgba(17,34,17,0.95); border-color: rgba(34,197,94,0.35); }
        .toast.error   { background: rgba(34,17,17,0.95); border-color: rgba(239,68,68,0.35); }
        .toast-icon { font-size: 1.2rem; margin-top: 2px; flex-shrink: 0; }
        .toast.success .toast-icon { color: #86efac; }
        .toast.error   .toast-icon { color: #fca5a5; }
        .toast-close { margin-left: auto; cursor: pointer; color: var(--muted); align-self: flex-start; }

        /* ── Stats strip ──────────────────────────────────── */
        .lessee-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 22px;
        }
        .ls-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
        }
        .ls-card .glow {
            position: absolute;
            top: -30px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            opacity: 0.15;
            filter: blur(20px);
        }
        .ls-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted); }
        .ls-val   { font-size: 1.8rem; font-weight: 900; margin-top: 4px; }

        /* ── Table toolbar ────────────────────────────────── */
        .tbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
        .search-box { display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.04); border: 1px solid var(--border);
            border-radius: 10px; padding: 0 12px; flex: 1; min-width: 200px; }
        .search-box input { background: transparent; border: none; outline: none;
            color: var(--text); font-size: 0.875rem; padding: 9px 0; flex: 1; }
        .search-box i { color: var(--muted); font-size: 0.75rem; }

        /* ── Confirm overlay ──────────────────────────────── */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .overlay.open { display: flex; }
        .confirm-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 24px 60px rgba(0,0,0,0.5);
            animation: popIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.85); }
            to   { opacity: 1; transform: scale(1); }
        }
        .confirm-icon { font-size: 2.5rem; margin-bottom: 14px; }
        .confirm-title { font-size: 1.125rem; font-weight: 800; margin-bottom: 8px; }
        .confirm-msg   { font-size: 0.875rem; color: var(--muted); margin-bottom: 24px; line-height: 1.6; }
        .confirm-btns  { display: flex; gap: 12px; justify-content: center; }
        .btn-danger-solid {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff; border: none;
            box-shadow: 0 4px 16px rgba(239,68,68,0.35);
        }
        .btn-danger-solid:hover { opacity: 0.88; transform: translateY(-1px); }

        /* ── Pagination ───────────────────────────────────── */
        .pagination { display: flex; align-items: center; gap: 6px; justify-content: center; margin-top: 20px; flex-wrap: wrap; }
        .page-btn {
            width: 34px; height: 34px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8125rem; font-weight: 700; cursor: pointer;
            border: 1px solid var(--border); background: transparent;
            color: var(--muted); transition: all 0.2s;
        }
        .page-btn:hover { border-color: rgba(99,102,241,0.5); color: #a5b4fc; background: rgba(99,102,241,0.1); }
        .page-btn.active { background: linear-gradient(135deg,#6366f1,#4f46e5); border-color: transparent; color: #fff; }
        .page-btn:disabled { opacity: 0.3; cursor: not-allowed; pointer-events: none; }

        /* ── Table column widths ──────────────────────────── */
        .data-table th, .data-table td { white-space: nowrap; }
        .data-table td.wrap { white-space: normal; min-width: 140px; }

        /* ── Empty state ──────────────────────────────────── */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
        .empty-state i { font-size: 3rem; margin-bottom: 16px; opacity: 0.25; }
        .empty-state p { font-size: 0.9375rem; }

        @media (max-width: 768px) {
            .lessee-stats { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 500px) {
            .lessee-stats { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>

<body>

    <!-- ═══════ TOAST ═══════ -->
    <div id="toast" class="toast">
        <i id="toast-icon" class="toast-icon fa-solid fa-circle-check"></i>
        <div>
            <div id="toast-title" style="font-weight:800;margin-bottom:2px"></div>
            <div id="toast-msg"   style="color:var(--muted);font-size:0.8125rem"></div>
        </div>
        <span class="toast-close" onclick="closeToast()"><i class="fa-solid fa-xmark"></i></span>
    </div>

    <!-- ═══════ CONFIRM OVERLAY ═══════ -->
    <div id="confirm-overlay" class="overlay">
        <div class="confirm-box">
            <div class="confirm-icon" id="confirm-icon">⚠️</div>
            <div class="confirm-title" id="confirm-title">Are you sure?</div>
            <div class="confirm-msg"   id="confirm-msg">This action cannot be undone.</div>
            <div class="confirm-btns">
                <button class="btn btn-ghost" onclick="closeConfirm()">Cancel</button>
                <button class="btn btn-danger-solid" id="confirm-ok" onclick="runConfirm()">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════ CREATE RECORD MODAL ═══════ -->
    <div id="create-overlay" class="overlay">
        <div class="confirm-box" style="max-width: 650px; text-align: left;">
            <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3" style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 20px;">
                <div class="confirm-title" id="modal-form-title" style="margin: 0; display:flex; gap:8px; align-items:center;">
                    <i class="fa-solid fa-user-plus" style="color:var(--primary)"></i> Create New Lessee
                </div>
                <button class="btn btn-ghost btn-sm" onclick="closeCreateModal()" style="padding: 4px 8px;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form id="form-create-lessee" onsubmit="submitCreateForm(event)">
                <input type="hidden" name="id" id="form-lessee-id" value="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                    <div>
                        <label style="display:block; font-size: 0.8rem; font-weight:700; color:var(--muted); margin-bottom: 4px;">Company Name <span style="color:red">*</span></label>
                        <input type="text" name="company_name" required style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block; font-size: 0.8rem; font-weight:700; color:var(--muted); margin-bottom: 4px;">Trade / Store Name</label>
                        <input type="text" name="trade_name" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block; font-size: 0.8rem; font-weight:700; color:var(--muted); margin-bottom: 4px;">Space Code / Stall</label>
                        <input type="text" name="space_code" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block; font-size: 0.8rem; font-weight:700; color:var(--muted); margin-bottom: 4px;">Owner / Rep Name</label>
                        <input type="text" name="owner_lessee_name" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block; font-size: 0.8rem; font-weight:700; color:var(--muted); margin-bottom: 4px;">Total Area (sq.m)</label>
                        <input type="number" step="0.01" name="total_area" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block; font-size: 0.8rem; font-weight:700; color:var(--muted); margin-bottom: 4px;">Basic Rent</label>
                        <input type="number" step="0.01" name="basic_rent" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block; font-size: 0.8rem; font-weight:700; color:var(--muted); margin-bottom: 4px;">Status</label>
                        <select name="status" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-family:inherit;">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Pending">Pending</option>
                            <option value="Terminated">Terminated</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size: 0.8rem; font-weight:700; color:var(--muted); margin-bottom: 4px;">Email</label>
                        <input type="email" name="email_address" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text); font-family:inherit;">
                    </div>
                </div>

                <div class="confirm-btns" style="justify-content: flex-end; margin-top:24px;">
                    <button type="button" class="btn btn-ghost" onclick="closeCreateModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-create">
                        <i class="fa-solid fa-check"></i> Create Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══════════════════════════════ SIDEBAR ═══════════════════════════════ -->
    <aside id="admin-sidebar">
        <div class="brand">
            <div class="brand-icon"><i class="fa-solid fa-building"></i></div>
            <div class="brand-name">Lease<span>Pro</span></div>
            <div style="margin-left:auto;font-size:10px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;padding:2px 8px;border-radius:100px;font-weight:700">ADMIN</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Overview</div>
            <a class="nav-item" href="index.html">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>

            <div class="nav-section-label">Lessees</div>
            <a class="nav-item active" href="lessees.php">
                <i class="fa-solid fa-file-csv"></i>
                Management
                <span class="badge" id="nav-count">—</span>
            </a>

            <div class="nav-section-label">Management</div>
            <a class="nav-item" href="index.html#sec-users">
                <i class="fa-solid fa-users"></i> User Management
            </a>
            <a class="nav-item" href="index.html#sec-logs">
                <i class="fa-solid fa-scroll"></i> Audit Logs
                <span class="badge danger">8</span>
            </a>
            <a class="nav-item" href="index.html#sec-settings">
                <i class="fa-solid fa-sliders"></i> App Settings
            </a>

            <div class="nav-section-label">App</div>
            <a class="nav-item" href="../user/index.html" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View App
            </a>
            <a class="nav-item" href="../index.html" target="_blank">
                <i class="fa-solid fa-right-to-bracket"></i> Login Page
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-chip">
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff&rounded=true" alt="Admin">
                <div class="info">
                    <div class="name">Admin User</div>
                    <div class="role">Super Administrator</div>
                </div>
                <i class="fa-solid fa-ellipsis" style="color:var(--muted);font-size:14px"></i>
            </div>
        </div>
    </aside>

    <!-- ═══════════════════════════════ MAIN CONTENT ═══════════════════════════════ -->
    <div id="admin-main">

        <!-- Header -->
        <header id="admin-header">
            <div class="header-left">
                <button class="icon-btn" id="menu-toggle" style="display:none">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <div id="page-title">Lessees Management</div>
                    <div id="page-breadcrumb">Admin / Lessees / Manage Records</div>
                </div>
            </div>
            <div class="header-right">
                <button class="btn btn-ghost btn-sm" id="btn-download-tpl" onclick="downloadTemplate()">
                    <i class="fa-solid fa-file-arrow-down"></i> CSV Template
                </button>
                <a href="../index.html" class="btn btn-ghost btn-sm" style="gap:7px">
                    <i class="fa-solid fa-power-off"></i> Logout
                </a>
            </div>
        </header>

        <div id="admin-content">

            <!-- ══ Stats Strip ══ -->
            <div class="lessee-stats">
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
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="tbl-search" placeholder="Search by company, space code, email…" oninput="debounceSearch(this.value)">
                        </div>
                        <select id="tbl-limit" class="btn btn-ghost btn-sm" onchange="changeLimit(this.value)" style="cursor:pointer">
                            <option value="10">10 / page</option>
                            <option value="25" selected>25 / page</option>
                            <option value="50">50 / page</option>
                            <option value="100">100 / page</option>
                        </select>
                        <button class="btn btn-ghost btn-sm" onclick="loadTable()" title="Refresh">
                            <i class="fa-solid fa-rotate"></i>
                        </button>
                    </div>

                    <!-- Table -->
                    <div style="overflow-x:auto">
                        <table class="data-table" id="lessee-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Company Name</th>
                                    <th>Trade / Store Name</th>
                                    <th>Nature of Business</th>
                                    <th>Owner / Lessee Rep</th>
                                    <th>Space Code</th>
                                    <th>Total Area</th>
                                    <th>Basic Rent</th>
                                    <th>Lease Period</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="lessee-tbody">
                                <tr>
                                    <td colspan="12">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-database"></i>
                                            <p>Loading records…</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination + info -->
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
                        <div class="panel-title"><i class="fa-solid fa-file-csv" style="color:#86efac;margin-right:8px"></i>Import Lessees via CSV</div>
                        <div class="panel-subtitle">Upload your CSV file — new records will be appended to the database</div>
                    </div>
                    <span id="upload-status-chip" class="chip" style="display:none"></span>
                </div>
                <div class="panel-body">
                    <div id="drop-zone" class="drop-zone" ondragover="onDragOver(event)" ondragleave="onDragLeave(event)" ondrop="onDrop(event)">
                        <input type="file" id="csv-input" accept=".csv" onchange="onFileSelected(this)">
                        <div class="drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                        <div class="drop-title">Drag &amp; drop your CSV here</div>
                        <div class="drop-sub">or <span style="color:#a5b4fc;font-weight:700">click to browse</span> — Max 10 MB · .csv only</div>
                        <div class="drop-fname" id="drop-fname"></div>
                    </div>

                    <div class="upload-progress" id="upload-progress">
                        <div class="upload-progress-fill" id="upload-progress-fill"></div>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:16px;flex-wrap:wrap;align-items:center">
                        <button class="btn btn-primary" id="btn-upload" onclick="uploadCSV()" disabled>
                            <i class="fa-solid fa-upload"></i> Upload &amp; Import
                        </button>
                        <button class="btn btn-ghost btn-sm" onclick="resetUpload()">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </button>
                        <span style="font-size:0.78rem;color:var(--muted);margin-left:4px">
                            <i class="fa-solid fa-circle-info" style="color:#60a5fa"></i>
                            New rows are appended — duplicate company names are allowed.
                            Download the <a href="#" onclick="downloadTemplate();return false" style="color:#a5b4fc">CSV template</a> to get the correct column order.
                        </span>
                    </div>
                </div>
            </div>

        </div><!-- /admin-content -->
    </div><!-- /admin-main -->

    <!-- ═══════ SIDEBAR TOGGLE (mobile) ═══════ -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>
    <script>
    // ── State ────────────────────────────────────────────────
    // Build absolute URL so fetch works from both http:// and when CSS/JS are local
    const _base = window.location.origin.startsWith('http')
        ? window.location.origin
        : 'http://localhost';
    const _path = window.location.pathname.replace(/\/admin\/.*$/, '');
    const API   = _base + _path + '/api/lessees_csv.php';

    // Guard: warn if running from file:// (fetch to PHP won't work)
    if (window.location.protocol === 'file:') {
        document.body.insertAdjacentHTML('afterbegin',
            `<div style="position:fixed;top:0;left:0;right:0;z-index:9999;background:#7f1d1d;color:#fca5a5;padding:12px 20px;font-weight:700;font-size:0.875rem;text-align:center">
                ⚠️ Open this page via <strong>http://localhost/Lease/admin/lessees.php</strong> — file:// URLs cannot reach the PHP API.
            </div>`);
    }
    let state = { page: 1, limit: 25, search: '', totalRows: 0, totalPages: 0 };
    let selectedFile = null;
    let confirmAction = null;
    let _searchTimer = null;

    // ── Toast ────────────────────────────────────────────────
    function showToast(type, title, msg) {
        const t = document.getElementById('toast');
        const ic = document.getElementById('toast-icon');
        document.getElementById('toast-title').textContent = title;
        document.getElementById('toast-msg').textContent   = msg;
        t.className = `toast ${type}`;
        ic.className = `toast-icon fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}`;
        setTimeout(() => t.classList.add('show'), 10);
        setTimeout(() => closeToast(), 5500);
    }
    function closeToast() {
        document.getElementById('toast').classList.remove('show');
    }

    // ── Confirm Modal ─────────────────────────────────────────
    function openConfirm(icon, title, msg, action) {
        document.getElementById('confirm-icon').textContent = icon;
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-msg').textContent   = msg;
        confirmAction = action;
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() {
        document.getElementById('confirm-overlay').classList.remove('open');
        confirmAction = null;
    }
    function runConfirm() {
        closeConfirm();
        if (typeof confirmAction === 'function') confirmAction();
    }

    // ── Create/Edit Modal ──────────────────────────────────────────
    function openCreateModal() {
        document.getElementById('form-create-lessee').reset();
        document.getElementById('form-lessee-id').value = '';
        document.getElementById('modal-form-title').innerHTML = '<i class="fa-solid fa-user-plus" style="color:var(--primary)"></i> Create New Lessee';
        document.getElementById('btn-submit-create').innerHTML = '<i class="fa-solid fa-check"></i> Create Record';
        document.getElementById('create-overlay').classList.add('open');
    }
    window.openEditModal = function(base64Str) {
        const item = JSON.parse(decodeURIComponent(atob(base64Str)));
        document.getElementById('form-create-lessee').reset();
        
        document.getElementById('form-lessee-id').value = item.id;
        document.querySelector('input[name="company_name"]').value = item.company_name || '';
        document.querySelector('input[name="trade_name"]').value = item.trade_name || '';
        document.querySelector('input[name="space_code"]').value = item.space_code || '';
        document.querySelector('input[name="owner_lessee_name"]').value = item.owner_lessee_name || '';
        document.querySelector('input[name="total_area"]').value = item.total_area || '';
        document.querySelector('input[name="basic_rent"]').value = item.basic_rent || '';
        document.querySelector('input[name="email_address"]').value = item.email_address || '';
        document.querySelector('select[name="status"]').value = item.status || 'Active';

        document.getElementById('modal-form-title').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color:var(--primary)"></i> Edit Lessee';
        document.getElementById('btn-submit-create').innerHTML = '<i class="fa-solid fa-save"></i> Save Changes';
        document.getElementById('create-overlay').classList.add('open');
    }

    function closeCreateModal() {
        document.getElementById('create-overlay').classList.remove('open');
    }
    
    async function submitCreateForm(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btn-submit-create');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const isEdit = !!data.id;
        const actionType = isEdit ? 'update' : 'create';

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const res = await fetch(API + '?action=' + actionType, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if (json.success) {
                showToast('success', 'Success', json.message);
                closeCreateModal();
                loadTable();
                loadStats();
            } else {
                showToast('error', 'Error', json.message);
            }
        } catch(err) {
            showToast('error', 'Network Error', err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = isEdit ? '<i class="fa-solid fa-save"></i> Save Changes' : '<i class="fa-solid fa-check"></i> Create Record';
        }
    }

    // ── Drop Zone ─────────────────────────────────────────────
    function onDragOver(e)  { e.preventDefault(); document.getElementById('drop-zone').classList.add('drag-over'); }
    function onDragLeave(e) { document.getElementById('drop-zone').classList.remove('drag-over'); }
    function onDrop(e) {
        e.preventDefault();
        document.getElementById('drop-zone').classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) setFile(file);
    }
    function onFileSelected(input) {
        if (input.files[0]) setFile(input.files[0]);
    }
    function setFile(file) {
        if (!file.name.endsWith('.csv')) { showToast('error','Invalid File','Only .csv files are accepted.'); return; }
        selectedFile = file;
        const fn = document.getElementById('drop-fname');
        fn.textContent = '📄 ' + file.name + '  (' + (file.size/1024).toFixed(1) + ' KB)';
        fn.style.display = 'block';
        document.getElementById('btn-upload').disabled = false;
    }
    function resetUpload() {
        selectedFile = null;
        document.getElementById('csv-input').value = '';
        document.getElementById('drop-fname').style.display = 'none';
        document.getElementById('drop-fname').textContent = '';
        document.getElementById('btn-upload').disabled = true;
        document.getElementById('upload-progress').style.display = 'none';
        document.getElementById('upload-progress-fill').style.width = '0%';
        const chip = document.getElementById('upload-status-chip');
        chip.style.display = 'none';
    }

    // ── Upload ────────────────────────────────────────────────
    async function uploadCSV() {
        if (!selectedFile) return;
        const btn = document.getElementById('btn-upload');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing…';

        const prog = document.getElementById('upload-progress');
        const fill = document.getElementById('upload-progress-fill');
        prog.style.display = 'block';
        fill.style.width   = '15%';

        const fd = new FormData();
        fd.append('csv_file', selectedFile);

        try {
            fill.style.width = '50%';
            const res  = await fetch(API + '?action=upload', { method: 'POST', body: fd });
            fill.style.width = '90%';
            const data = await res.json();
            fill.style.width = '100%';

            if (data.success) {
                showToast('success', 'Import Complete', data.message);
                setStatusChip('success', `✓ ${data.inserted} imported`);
                loadTable();
                loadStats();
            } else {
                showToast('error', 'Import Failed', data.message);
                setStatusChip('error', '✗ Failed');
            }
        } catch (e) {
            showToast('error', 'Network Error', e.message);
            setStatusChip('error', '✗ Error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-upload"></i> Upload &amp; Import';
            setTimeout(() => { prog.style.display = 'none'; fill.style.width = '0%'; }, 1200);
        }
    }
    function setStatusChip(type, text) {
        const chip = document.getElementById('upload-status-chip');
        chip.className  = `chip chip-${type === 'success' ? 'green' : 'red'}`;
        chip.textContent = text;
        chip.style.display = 'inline-flex';
    }

    // ── Download Template ─────────────────────────────────────
    function downloadTemplate() {
        window.location.href = API + '?action=download_template';
    }

    // ── Load Stats ────────────────────────────────────────────
    async function loadStats() {
        try {
            const res  = await fetch(API + '?action=list&limit=1&page=1');
            const data = await res.json();
            if (!data.success) return;
            document.getElementById('stat-total').textContent    = data.total;
            document.getElementById('nav-count').textContent     = data.total;
            if (data.stats) {
                document.getElementById('stat-active').textContent   = data.stats.active;
                document.getElementById('stat-expiring').textContent = data.stats.expiring;
                document.getElementById('stat-expired').textContent  = data.stats.expired;
            }
        } catch(e) { /* silent */ }
    }

    // ── Load Table ────────────────────────────────────────────
    async function loadTable() {
        const tbody = document.getElementById('lessee-tbody');
        tbody.innerHTML = `<tr><td colspan="12"><div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading…</p></div></td></tr>`;

        const params = new URLSearchParams({
            action: 'list',
            page:   state.page,
            limit:  state.limit,
            search: state.search,
        });

        try {
            const res  = await fetch(`${API}?${params}`);
            const data = await res.json();
            if (!data.success) { showToast('error', 'Error', data.message); return; }

            state.totalRows  = data.total;
            state.totalPages = data.pages;

            renderTable(data.data);
            renderPagination();
            updateInfo();
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="12"><div class="empty-state"><i class="fa-solid fa-circle-exclamation"></i><p>Failed to load: ${e.message}</p></div></td></tr>`;
        }
    }

    function renderTable(rows) {
        const tbody = document.getElementById('lessee-tbody');
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="12"><div class="empty-state"><i class="fa-solid fa-inbox"></i><p>No records found. Import a CSV to get started.</p></div></td></tr>`;
            return;
        }

        const today = new Date();
        tbody.innerHTML = rows.map(r => {
            let statusBadge = '<span class="chip" style="background:rgba(148,163,184,0.15);color:#94a3b8">—</span>';
            if (r.status === 'Active' && r.lease_period_end) {
                const end  = new Date(r.lease_period_end);
                const days = Math.ceil((end - today) / 86400000);
                if (days < 0)       statusBadge = '<span class="chip chip-red">Expired</span>';
                else if (days <= 30) statusBadge = '<span class="chip" style="background:rgba(245,158,11,.15);color:#fcd34d">Expiring Soon</span>';
                else                 statusBadge = '<span class="chip chip-green">Active</span>';
            } else {
                if (r.status === 'Active') statusBadge = '<span class="chip chip-green">Active</span>';
                else if (r.status === 'Pending') statusBadge = '<span class="chip" style="background:rgba(245,158,11,.15);color:#fcd34d">Pending</span>';
                else if (r.status === 'Inactive') statusBadge = '<span class="chip" style="background:rgba(148,163,184,0.15);color:#94a3b8">Inactive</span>';
                else if (r.status === 'Terminated') statusBadge = '<span class="chip chip-red">Terminated</span>';
            }

            const leasePeriod = [r.lease_period_start, r.lease_period_end].filter(Boolean).join(' → ') || '—';
            const rent = r.basic_rent ? '₱' + Number(r.basic_rent).toLocaleString('en-PH', {minimumFractionDigits:2}) : '—';

            return `<tr>
                <td style="color:var(--muted);font-size:0.75rem">${r.id}</td>
                <td><div style="font-weight:700">${esc(r.company_name)}</div></td>
                <td class="wrap">${esc(r.trade_name  || '—')}</td>
                <td>${esc(r.nature_of_business || '—')}</td>
                <td class="wrap">${esc(r.owner_lessee_name || '—')}</td>
                <td><span class="chip chip-blue">${esc(r.space_code || '—')}</span></td>
                <td>${r.total_area ? r.total_area + ' sqm' : '—'}</td>
                <td style="font-weight:700;color:#86efac">${rent}</td>
                <td style="font-size:0.78rem">${esc(leasePeriod)}</td>
                <td>${esc(r.email_address || '—')}</td>
                <td>${statusBadge}</td>
                <td style="display:flex; gap:6px;">
                    <button class="btn btn-ghost btn-sm btn-edit-one"
                        style="color:#60a5fa;border-color:rgba(96,165,250,0.25)"
                        onclick='openEditModal(\`${btoa(encodeURIComponent(JSON.stringify(r)))}\`)'
                        title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn btn-ghost btn-sm btn-del-one"
                        style="color:#fca5a5;border-color:rgba(239,68,68,0.25)"
                        data-id="${r.id}"
                        data-name="${esc(r.company_name)}"
                        title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');

        // (click delegation wired once at bottom of script)
    }

    // Persistent delegated click handler for delete buttons — wired once
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-del-one');
        if (!btn) return;
        const id   = parseInt(btn.dataset.id);
        const name = btn.dataset.name;
        confirmDeleteOne(id, name);
    });

    function esc(s) {
        if (s === null || s === undefined) return '—';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Pagination ────────────────────────────────────────────
    function renderPagination() {
        const cont = document.getElementById('tbl-pagination');
        const { page, totalPages } = state;
        if (totalPages <= 1) { cont.innerHTML = ''; return; }

        let html = `<button class="page-btn" onclick="goPage(${page-1})" ${page===1?'disabled':''}>
                        <i class="fa-solid fa-chevron-left"></i></button>`;

        const range = pageRange(page, totalPages);
        range.forEach(p => {
            if (p === '…') {
                html += `<span class="page-btn" style="pointer-events:none">…</span>`;
            } else {
                html += `<button class="page-btn ${p===page?'active':''}" onclick="goPage(${p})">${p}</button>`;
            }
        });

        html += `<button class="page-btn" onclick="goPage(${page+1})" ${page===totalPages?'disabled':''}>
                     <i class="fa-solid fa-chevron-right"></i></button>`;
        cont.innerHTML = html;
    }

    function pageRange(cur, total) {
        if (total <= 7) return Array.from({length:total},(_,i)=>i+1);
        if (cur <= 4)   return [1,2,3,4,5,'…',total];
        if (cur >= total-3) return [1,'…',total-4,total-3,total-2,total-1,total];
        return [1,'…',cur-1,cur,cur+1,'…',total];
    }

    function goPage(p) {
        if (p < 1 || p > state.totalPages) return;
        state.page = p;
        loadTable();
    }

    function updateInfo() {
        const { page, limit, totalRows } = state;
        const from = totalRows ? (page-1)*limit+1 : 0;
        const to   = Math.min(page*limit, totalRows);
        document.getElementById('tbl-info').textContent =
            totalRows ? `Showing ${from}–${to} of ${totalRows} records` : 'No records';
    }

    // ── Search ────────────────────────────────────────────────
    function debounceSearch(val) {
        clearTimeout(_searchTimer);
        _searchTimer = setTimeout(() => {
            state.search = val.trim();
            state.page   = 1;
            loadTable();
        }, 380);
    }

    function changeLimit(val) {
        state.limit = parseInt(val);
        state.page  = 1;
        loadTable();
    }

    // ── Delete actions ─────────────────────────────────────────
    function confirmDeleteOne(id, name) {
        openConfirm('🗑️', 'Delete Record?',
            `This will permanently remove "${name}" (ID #${id}) from the database.`,
            () => deleteOne(id));
    }
    async function deleteOne(id) {
        try {
            const res  = await fetch(`${API}?action=delete_one`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); }
            catch(e) { showToast('error','Server Error', text.slice(0,120)); return; }
            if (data.success) { showToast('success', 'Deleted', data.message); loadTable(); loadStats(); }
            else               showToast('error',   'Error',   data.message);
        } catch (e) { showToast('error', 'Network Error — make sure you opened this page via http://localhost', e.message); }
    }

    function confirmDeleteAll() {
        openConfirm('⚠️', 'Delete ALL Lessees?',
            'This will permanently erase every lessee record in the database. This action CANNOT be undone.',
            deleteAll);
    }
    async function deleteAll() {
        try {
            const res  = await fetch(`${API}?action=delete_all`, { method: 'POST' });
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); }
            catch(e) { showToast('error','Server Error', text.slice(0,120)); return; }
            if (data.success) { showToast('success', 'Cleared', data.message); loadTable(); loadStats(); }
            else               showToast('error',   'Error',   data.message);
        } catch (e) { showToast('error', 'Network Error — make sure you opened this page via http://localhost', e.message); }
    }

    // ── Export current view as CSV ─────────────────────────────
    function exportCurrentCSV() {
        const params = new URLSearchParams({
            action: 'list', page: 1, limit: 9999, search: state.search
        });
        fetch(`${API}?${params}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.data.length) {
                    showToast('error','No Data','Nothing to export.'); return;
                }
                const cols = Object.keys(data.data[0]).filter(c => c !== 'id');
                const header = cols.join(',');
                const rows = data.data.map(r =>
                    cols.map(c => `"${String(r[c]??'').replace(/"/g,'""')}"`).join(',')
                );
                const blob = new Blob([header+'\n'+rows.join('\n')], {type:'text/csv'});
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = `lessees_export_${new Date().toISOString().slice(0,10)}.csv`;
                a.click();
            });
    }

    // ── Mobile sidebar toggle ─────────────────────────────────
    const menuBtn = document.getElementById('menu-toggle');
    if (window.innerWidth < 768) menuBtn.style.display = 'flex';
    menuBtn.addEventListener('click', () => {
        document.getElementById('admin-sidebar').classList.toggle('mobile-open');
    });

    // ── Init ──────────────────────────────────────────────────
    loadTable();
    loadStats();
    </script>
</body>
</html>
