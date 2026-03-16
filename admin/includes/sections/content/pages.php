            <!-- ════════════════════════════ PAGES ════════════════════════════ -->
            <section id="sec-pages" class="admin-section">
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title">Frontend Pages</div>
                            <div class="panel-subtitle">Monitor and manage all application pages</div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-toolbar">
                            <div class="search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="pages-search" placeholder="Search pages…">
                            </div>
                        </div>
                        <div style="overflow-x:auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Page Name</th>
                                        <th>URL / Slug</th>
                                        <th>Visible</th>
                                        <th>Last Edited</th>
                                        <th>Edited By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pages-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Page health overview -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Page Health Overview</div>
                    </div>
                    <div class="panel-body" style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px">
                        <div style="text-align:center">
                            <div style="font-size:2rem;font-weight:800;color:#86efac">5</div>
                            <div style="font-size:0.75rem;color:var(--muted);margin-top:4px">Pages Live</div>
                        </div>
                        <div style="text-align:center">
                            <div style="font-size:2rem;font-weight:800;color:#fcd34d">1</div>
                            <div style="font-size:0.75rem;color:var(--muted);margin-top:4px">Hidden Pages</div>
                        </div>
                        <div style="text-align:center">
                            <div style="font-size:2rem;font-weight:800;color:#7dd3fc">6</div>
                            <div style="font-size:0.75rem;color:var(--muted);margin-top:4px">Total Registered</div>
                        </div>
                    </div>
                </div>
            </section>

