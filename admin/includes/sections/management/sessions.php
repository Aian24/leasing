            <!-- ══════════════════════ ACTIVE SESSIONS ══════════════════════ -->
            <section id="sec-sessions" class="admin-section">
                <div class="panel" style="margin-bottom:22px">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title">Active Sessions</div>
                            <div class="panel-subtitle">Currently logged-in users and their session details</div>
                        </div>
                        <button class="btn btn-danger btn-sm" onclick="killAllSessions()"><i
                                class="fa-solid fa-bolt"></i> Terminate All</button>
                    </div>
                    <div class="panel-body" style="overflow-x:auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Browser / Device</th>
                                    <th>Login Time</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="sessions-tbody"></tbody>
                        </table>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px">
                    <div class="stat-card" style="padding:18px">
                        <div class="sc-icon"
                            style="background:rgba(34,197,94,0.15);color:#86efac;width:38px;height:38px;font-size:14px;margin-bottom:10px">
                            <i class="fa-solid fa-circle-dot"></i>
                        </div>
                        <div class="sc-val" style="font-size:1.5rem" id="sess-active-count">3</div>
                        <div class="sc-label">Active Now</div>
                    </div>
                    <div class="stat-card" style="padding:18px">
                        <div class="sc-icon"
                            style="background:rgba(59, 130, 246,0.15);color:#a5b4fc;width:38px;height:38px;font-size:14px;margin-bottom:10px">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div class="sc-val" style="font-size:1.5rem">12</div>
                        <div class="sc-label">Logins Today</div>
                    </div>
                    <div class="stat-card" style="padding:18px">
                        <div class="sc-icon"
                            style="background:rgba(239,68,68,0.15);color:#fca5a5;width:38px;height:38px;font-size:14px;margin-bottom:10px">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="sc-val" style="font-size:1.5rem">1</div>
                        <div class="sc-label">Suspicious Attempts</div>
                    </div>
                </div>
            </section>

