            <!-- ══════════════════════ SETTINGS ══════════════════════ -->
            <section id="sec-settings" class="admin-section">
                <!-- General Settings -->
                <div class="panel" style="margin-bottom:22px">
                    <div class="panel-header">
                        <div class="panel-title"><i class="fa-solid fa-gear"
                                style="color:#60a5fa;margin-right:8px"></i>General Settings</div>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                            <div class="form-group">
                                <label class="form-label">Application Name</label>
                                <input type="text" class="form-control" value="LeasePro" id="set-appname">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Default Language</label>
                                <select class="form-control" id="set-lang">
                                    <option selected>English (en)</option>
                                    <option>Filipino (fil)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Timezone</label>
                                <select class="form-control" id="set-tz">
                                    <option selected>Asia/Manila (UTC+8)</option>
                                    <option>UTC</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date Format</label>
                                <select class="form-control" id="set-datefmt">
                                    <option selected>MM/DD/YYYY</option>
                                    <option>DD/MM/YYYY</option>
                                    <option>YYYY-MM-DD</option>
                                </select>
                            </div>
                        </div>
                        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:8px">
                            <label
                                style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:10px;flex:1;min-width:200px">
                                <label class="toggle-switch"><input type="checkbox" id="set-maint"
                                        onchange="saveSetting('maintenance',this.checked)"><span
                                        class="toggle-thumb"></span></label>
                                <div>
                                    <div style="font-weight:700;font-size:0.875rem">Maintenance Mode</div>
                                    <div style="font-size:0.75rem;color:var(--muted)">Lock frontend access for all
                                        non-admin users</div>
                                </div>
                            </label>
                            <label
                                style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:10px;flex:1;min-width:200px">
                                <label class="toggle-switch"><input type="checkbox" id="set-reg" checked
                                        onchange="saveSetting('registration',this.checked)"><span
                                        class="toggle-thumb"></span></label>
                                <div>
                                    <div style="font-weight:700;font-size:0.875rem">User Self-Registration</div>
                                    <div style="font-size:0.75rem;color:var(--muted)">Allow new users to register
                                        without admin invite</div>
                                </div>
                            </label>
                            <label
                                style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:10px;flex:1;min-width:200px">
                                <label class="toggle-switch"><input type="checkbox" id="set-2fa" checked
                                        onchange="saveSetting('2fa',this.checked)"><span
                                        class="toggle-thumb"></span></label>
                                <div>
                                    <div style="font-weight:700;font-size:0.875rem">Force 2FA</div>
                                    <div style="font-size:0.75rem;color:var(--muted)">Require two-factor auth for all
                                        users</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- SMTP Settings -->
                <div class="panel" style="margin-bottom:22px">
                    <div class="panel-header">
                        <div class="panel-title"><i class="fa-solid fa-envelope"
                                style="color:#60a5fa;margin-right:8px"></i>SMTP / Email Settings</div>
                        <button class="btn btn-ghost btn-sm" onclick="testSMTP()"><i
                                class="fa-solid fa-paper-plane"></i> Send Test Email</button>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                            <div class="form-group">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" value="smtp.mailtrap.io" id="set-smtp-host">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" value="587" id="set-smtp-port">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SMTP Username</label>
                                <input type="text" class="form-control" placeholder="your@email.com" id="set-smtp-user">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SMTP Password</label>
                                <input type="password" class="form-control" placeholder="••••••••" id="set-smtp-pass">
                            </div>
                            <div class="form-group">
                                <label class="form-label">From Name</label>
                                <input type="text" class="form-control" value="LeasePro System" id="set-smtp-name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Encryption</label>
                                <select class="form-control" id="set-smtp-enc">
                                    <option selected>TLS</option>
                                    <option>SSL</option>
                                    <option>None</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Settings -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title"><i class="fa-solid fa-database"
                                style="color:#60a5fa;margin-right:8px"></i>Backup & Recovery</div>
                        <button class="btn btn-primary btn-sm" onclick="runBackup()"><i
                                class="fa-solid fa-cloud-arrow-up"></i> Run Backup Now</button>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                            <div class="form-group">
                                <label class="form-label">Auto-Backup Frequency</label>
                                <select class="form-control" id="set-backup-freq">
                                    <option selected>Daily</option>
                                    <option>Weekly</option>
                                    <option>Monthly</option>
                                    <option>Disabled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Retain Backups For</label>
                                <select class="form-control" id="set-backup-ret">
                                    <option>7 days</option>
                                    <option selected>30 days</option>
                                    <option>90 days</option>
                                </select>
                            </div>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Backup File</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-family:'Inter',sans-serif;font-size:0.8rem">
                                        leasepro_backup_20260303.sql</td>
                                    <td style="font-size:0.8rem;color:var(--muted)">4.2 MB</td>
                                    <td style="font-size:0.8rem;color:var(--muted)">2026-03-03 03:00</td>
                                    <td><span class="chip chip-green">Auto</span></td>
                                    <td><button class="btn btn-ghost btn-xs"><i class="fa-solid fa-download"></i>
                                            Download</button></td>
                                </tr>
                                <tr>
                                    <td style="font-family:'Inter',sans-serif;font-size:0.8rem">
                                        leasepro_backup_20260302.sql</td>
                                    <td style="font-size:0.8rem;color:var(--muted)">4.1 MB</td>
                                    <td style="font-size:0.8rem;color:var(--muted)">2026-03-02 03:00</td>
                                    <td><span class="chip chip-green">Auto</span></td>
                                    <td><button class="btn btn-ghost btn-xs"><i class="fa-solid fa-download"></i>
                                            Download</button></td>
                                </tr>
                                <tr>
                                    <td style="font-family:'Inter',sans-serif;font-size:0.8rem">
                                        leasepro_manual_20260301.sql</td>
                                    <td style="font-size:0.8rem;color:var(--muted)">4.0 MB</td>
                                    <td style="font-size:0.8rem;color:var(--muted)">2026-03-01 11:22</td>
                                    <td><span class="chip chip-blue">Manual</span></td>
                                    <td><button class="btn btn-ghost btn-xs"><i class="fa-solid fa-download"></i>
                                            Download</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="margin-top:16px">
                            <button class="btn btn-ghost btn-sm" onclick="saveSettings()"><i
                                    class="fa-solid fa-floppy-disk"></i> Save All Settings</button>
                        </div>
                    </div>
                </div>
            </section>

