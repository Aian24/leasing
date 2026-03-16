            <!-- ══════════════════════ ROLES & PERMISSIONS ══════════════════════ -->
            <section id="sec-roles" class="admin-section">
                <div class="panel" style="margin-bottom:22px">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title">Roles & Permissions</div>
                            <div class="panel-subtitle">Manage user access levels (Matrix)</div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Permission</th>
                                    <th>Admin</th>
                                    <th>Manager</th>
                                    <th>Staff</th>
                                    <th>Viewer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Manage Users</td>
                                    <td style="color:#86efac"><i class="fa-solid fa-check"></i></td>
                                    <td style="color:#ef4444"><i class="fa-solid fa-xmark"></i></td>
                                    <td style="color:#ef4444"><i class="fa-solid fa-xmark"></i></td>
                                    <td style="color:#ef4444"><i class="fa-solid fa-xmark"></i></td>
                                </tr>
                                <tr>
                                    <td>Manage Contracts</td>
                                    <td style="color:#86efac"><i class="fa-solid fa-check"></i></td>
                                    <td style="color:#86efac"><i class="fa-solid fa-check"></i></td>
                                    <td style="color:#86efac"><i class="fa-solid fa-check"></i></td>
                                    <td style="color:#ef4444"><i class="fa-solid fa-xmark"></i></td>
                                </tr>
                                <tr>
                                    <td>System Config</td>
                                    <td style="color:#86efac"><i class="fa-solid fa-check"></i></td>
                                    <td style="color:#ef4444"><i class="fa-solid fa-xmark"></i></td>
                                    <td style="color:#ef4444"><i class="fa-solid fa-xmark"></i></td>
                                    <td style="color:#ef4444"><i class="fa-solid fa-xmark"></i></td>
                                </tr>
                                <tr>
                                    <td>View Audit Logs</td>
                                    <td style="color:#86efac"><i class="fa-solid fa-check"></i></td>
                                    <td style="color:#86efac"><i class="fa-solid fa-check"></i></td>
                                    <td style="color:#ef4444"><i class="fa-solid fa-xmark"></i></td>
                                    <td style="color:#ef4444"><i class="fa-solid fa-xmark"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title">Role Matrix Details</div>
                    </div>
                    <div class="panel-body" style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px">
                        <div
                            style="padding:16px;background:rgba(59, 130, 246,0.08);border:1px solid rgba(59, 130, 246,0.2);border-radius:12px">
                            <div style="font-weight:800;color:#a5b4fc;margin-bottom:6px"><i
                                    class="fa-solid fa-user-shield" style="margin-right:6px"></i>Administrator</div>
                            <div style="font-size:0.8rem;color:var(--muted);line-height:1.6">Full system access. Can
                                modify all data, settings, and manage other admin accounts.</div>
                        </div>
                        <div
                            style="padding:16px;background:rgba(56, 189, 248,0.08);border:1px solid rgba(56, 189, 248,0.2);border-radius:12px">
                            <div style="font-weight:800;color:#7dd3fc;margin-bottom:6px"><i
                                    class="fa-solid fa-briefcase" style="margin-right:6px"></i>Manager</div>
                            <div style="font-size:0.8rem;color:var(--muted);line-height:1.6">Can manage contracts,
                                lessees, and view reports. Cannot change system config.</div>
                        </div>
                        <div
                            style="padding:16px;background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);border-radius:12px">
                            <div style="font-weight:800;color:#86efac;margin-bottom:6px"><i class="fa-solid fa-user"
                                    style="margin-right:6px"></i>Staff</div>
                            <div style="font-size:0.8rem;color:var(--muted);line-height:1.6">Can create and edit lease
                                entries. Cannot delete or access admin tools.</div>
                        </div>
                        <div
                            style="padding:16px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:12px">
                            <div style="font-weight:800;color:#fcd34d;margin-bottom:6px"><i class="fa-solid fa-eye"
                                    style="margin-right:6px"></i>Viewer</div>
                            <div style="font-size:0.8rem;color:var(--muted);line-height:1.6">Read-only access. Can view
                                contracts and reports but cannot modify anything.</div>
                        </div>
                    </div>
                </div>
            </section>

