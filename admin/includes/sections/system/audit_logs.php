            <!-- ════════════════════════════ LOGS ════════════════════════════ -->
            <section id="sec-logs" class="admin-section">
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title">Audit Logs</div>
                            <div class="panel-subtitle">Full system event history</div>
                        </div>
                        <button class="btn btn-ghost btn-sm"><i class="fa-solid fa-file-arrow-down"></i> Export
                            CSV</button>
                    </div>
                    <div class="panel-body">
                        <div style="overflow-x:auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Detail</th>
                                        <th>Level</th>
                                    </tr>
                                </thead>
                                <tbody id="logs-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

