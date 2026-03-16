            <!-- ════════════════════════════ SYSTEM ════════════════════════════ -->
            <section id="sec-system" class="admin-section">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-bottom:22px">
                    <div class="panel">
                        <div class="panel-header">
                            <div class="panel-title"><i class="fa-solid fa-circle-info"
                                    style="color:#60a5fa;margin-right:8px"></i>Server Details</div>
                        </div>
                        <div class="panel-body">
                            <table style="width:100%;font-size:0.875rem;border-collapse:collapse">
                                <tr style="border-bottom:1px solid rgba(51,65,85,0.4)">
                                    <td style="padding:10px 0;color:var(--muted);font-weight:600">Operating System</td>
                                    <td id="sys-os"
                                        style="padding:10px 0;text-align:right;font-family:'Inter',sans-serif;font-weight:700">
                                    </td>
                                </tr>
                                <tr style="border-bottom:1px solid rgba(51,65,85,0.4)">
                                    <td style="padding:10px 0;color:var(--muted);font-weight:600">PHP Version</td>
                                    <td id="sys-php"
                                        style="padding:10px 0;text-align:right;font-family:'Inter',sans-serif;font-weight:700;color:#a5b4fc">
                                    </td>
                                </tr>
                                <tr style="border-bottom:1px solid rgba(51,65,85,0.4)">
                                    <td style="padding:10px 0;color:var(--muted);font-weight:600">MySQL Version</td>
                                    <td id="sys-mysql"
                                        style="padding:10px 0;text-align:right;font-family:'Inter',sans-serif;font-weight:700;color:#a5b4fc">
                                    </td>
                                </tr>
                                <tr style="border-bottom:1px solid rgba(51,65,85,0.4)">
                                    <td style="padding:10px 0;color:var(--muted);font-weight:600">App Version</td>
                                    <td id="sys-ver"
                                        style="padding:10px 0;text-align:right;font-family:'Inter',sans-serif;font-weight:700;color:#86efac">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;color:var(--muted);font-weight:600">System Uptime</td>
                                    <td id="sys-uptime"
                                        style="padding:10px 0;text-align:right;font-family:'Inter',sans-serif;font-weight:700;color:#fcd34d">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <div class="panel-title"><i class="fa-solid fa-microchip"
                                    style="color:#60a5fa;margin-right:8px"></i>Resource Usage</div>
                        </div>
                        <div class="panel-body">
                            <div style="margin-bottom:22px">
                                <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                                    <span style="font-size:0.875rem;font-weight:700">Disk Storage</span>
                                    <span id="sys-disk-pct"
                                        style="font-size:0.875rem;font-weight:700;color:#a5b4fc"></span>
                                </div>
                                <div class="progress-bar-wrap" style="height:10px">
                                    <div class="progress-bar-fill" id="sys-disk-bar"></div>
                                </div>
                                <div style="font-size:0.7rem;color:var(--muted);margin-top:4px">48 GB used of 100 GB
                                </div>
                            </div>
                            <div style="margin-bottom:22px">
                                <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                                    <span style="font-size:0.875rem;font-weight:700">Memory (RAM)</span>
                                    <span id="sys-mem-pct"
                                        style="font-size:0.875rem;font-weight:700;color:#c4b5fd"></span>
                                </div>
                                <div class="progress-bar-wrap" style="height:10px">
                                    <div class="progress-bar-fill" id="sys-mem-bar"></div>
                                </div>
                                <div style="font-size:0.7rem;color:var(--muted);margin-top:4px">12.4 GB used of 32 GB
                                </div>
                            </div>
                            <div>
                                <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                                    <span style="font-size:0.875rem;font-weight:700">CPU Status</span>
                                    <span id="sys-cpu-pct"
                                        style="font-size:0.875rem;font-weight:700;color:#7dd3fc"></span>
                                </div>
                                <div class="progress-bar-wrap" style="height:10px">
                                    <div class="progress-bar-fill" id="sys-cpu-bar"></div>
                                </div>
                                <div style="font-size:0.7rem;color:var(--muted);margin-top:4px">Running optimally at
                                    <span id="sys-cpu-load">...</span> Load Average</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security status grid -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title"><i class="fa-solid fa-shield-halved"
                                style="color:#86efac;margin-right:8px"></i>Security Status</div>
                    </div>
                    <div class="panel-body"
                        style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;text-align:center">
                        <div>
                            <div style="font-size:1.5rem;color:#86efac"><i class="fa-solid fa-shield-check"></i></div>
                            <div style="font-size:0.75rem;font-weight:700;margin-top:6px">Firewall</div>
                            <div style="font-size:0.7rem;color:#86efac;margin-top:2px">Active</div>
                        </div>
                        <div>
                            <div style="font-size:1.5rem;color:#86efac"><i class="fa-solid fa-lock"></i></div>
                            <div style="font-size:0.75rem;font-weight:700;margin-top:6px">SSL/TLS</div>
                            <div style="font-size:0.7rem;color:#86efac;margin-top:2px">Enabled</div>
                        </div>
                        <div>
                            <div style="font-size:1.5rem;color:#fcd34d"><i class="fa-solid fa-database"></i></div>
                            <div style="font-size:0.75rem;font-weight:700;margin-top:6px">Last Backup</div>
                            <div style="font-size:0.7rem;color:#fcd34d;margin-top:2px">3 Mar 2026</div>
                        </div>
                        <div>
                            <div style="font-size:1.5rem;color:#86efac"><i class="fa-solid fa-circle-check"></i></div>
                            <div style="font-size:0.75rem;font-weight:700;margin-top:6px">2FA Policy</div>
                            <div style="font-size:0.7rem;color:#86efac;margin-top:2px">Enforced</div>
                        </div>
                    </div>
                </div>
            </section>

