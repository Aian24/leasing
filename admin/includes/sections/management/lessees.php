            <!-- ════════════════════════════ LESSEES ════════════════════════════ -->
            <section id="sec-lessees" class="admin-section">
                
                <!-- Lessee Stats -->
                <div class="ls-grid">
                    <div class="ls-card">
                        <div class="glow" style="background:#6366f1"></div>
                        <div class="ls-label">Total Lessees</div>
                        <div class="ls-val" id="stat-total-lessees" style="color:#a5b4fc">—</div>
                    </div>
                    <div class="ls-card">
                        <div class="glow" style="background:#22c55e"></div>
                        <div class="ls-label">Active Leases</div>
                        <div class="ls-val" id="stat-active-lessees" style="color:#86efac">—</div>
                    </div>
                    <div class="ls-card">
                        <div class="glow" style="background:#38bdf8"></div>
                        <div class="ls-label">Expiring (30 days)</div>
                        <div class="ls-val" id="stat-expiring-lessees" style="color:#7dd3fc">—</div>
                    </div>
                    <div class="ls-card">
                        <div class="glow" style="background:#f59e0b"></div>
                        <div class="ls-label">Expired</div>
                        <div class="ls-val" id="stat-expired-lessees" style="color:#fcd34d">—</div>
                    </div>
                </div>

                <!-- Data Table Panel -->
                <div class="panel" style="margin-bottom:22px">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title"><i class="fa-solid fa-table" style="color:#7dd3fc;margin-right:8px"></i>Lessee Records</div>
                            <div class="panel-subtitle">Create, browse, search, edit, and delete records from the database</div>
                        </div>
                        <div style="display:flex;gap:10px">
                            <button class="btn btn-sm" style="background:var(--primary);color:#fff;border:none" onclick="openCreateLesseeModal()">
                                <i class="fa-solid fa-plus"></i> Create New
                            </button>
                            <button class="btn btn-ghost btn-sm" onclick="exportLesseeCSV()">
                                <i class="fa-solid fa-file-export"></i> Export
                            </button>
                            <button class="btn btn-sm" style="background:rgba(239,68,68,0.15);color:#fca5a5;border:1px solid rgba(239,68,68,0.3)" onclick="confirmDeleteAllLessees()">
                                <i class="fa-solid fa-trash-can"></i> Delete All
                            </button>
                        </div>
                    </div>
                    <div class="panel-body">

                        <!-- Toolbar -->
                        <div class="tbar">
                            <div class="search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="tbl-search" placeholder="Search by company, space code, email…" oninput="debounceLesseeSearch(this.value)">
                            </div>
                            <select id="tbl-limit" class="btn btn-ghost btn-sm" onchange="changeLesseeLimit(this.value)" style="cursor:pointer">
                                <option value="10">10 / page</option>
                                <option value="25" selected>25 / page</option>
                                <option value="50">50 / page</option>
                                <option value="100">100 / page</option>
                            </select>
                            <button class="btn btn-ghost btn-sm" onclick="loadLesseeTable()" title="Refresh">
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

                <!-- Upload Panel -->
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title"><i class="fa-solid fa-file-csv" style="color:#86efac;margin-right:8px"></i>Import Lessees via CSV</div>
                            <div class="panel-subtitle">Upload your CSV file — new records will be appended to the database</div>
                        </div>
                        <span id="upload-status-chip" class="chip" style="display:none"></span>
                    </div>
                    <div class="panel-body">
                        <div id="drop-zone" class="drop-zone" ondragover="onLesseeDragOver(event)" ondragleave="onLesseeDragLeave(event)" ondrop="onLesseeDrop(event)">
                            <input type="file" id="csv-input" accept=".csv" onchange="onLesseeFileSelected(this)">
                            <div class="drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="drop-title">Drag &amp; drop your CSV here</div>
                            <div class="drop-sub">or <span style="color:#a5b4fc;font-weight:700">click to browse</span> — Max 10 MB · .csv only</div>
                            <div class="drop-fname" id="drop-fname"></div>
                        </div>

                        <div class="upload-progress" id="upload-progress">
                            <div class="upload-progress-fill" id="upload-progress-fill"></div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:16px;flex-wrap:wrap;align-items:center">
                            <button class="btn btn-primary" id="btn-upload" onclick="uploadLesseeCSV()" disabled>
                                <i class="fa-solid fa-upload"></i> Upload &amp; Import
                            </button>
                            <button class="btn btn-ghost btn-sm" onclick="resetLesseeUpload()">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </button>
                            <span style="font-size:0.78rem;color:var(--muted);margin-left:4px">
                                <i class="fa-solid fa-circle-info" style="color:#60a5fa"></i>
                                New rows are appended — duplicate company names are allowed.
                                Download the <a href="#" onclick="downloadLesseeTemplate();return false" style="color:#a5b4fc">CSV template</a> to get the correct column order.
                            </span>
                        </div>
                    </div>
                </div>

            </section>

