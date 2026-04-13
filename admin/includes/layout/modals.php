    <!-- ════════════════ MODALS ════════════════ -->

    <!-- User Modal -->
    <div class="modal-backdrop" id="user-modal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="modal-user-title">Add New User</div>
                <button class="modal-close" onclick="closeUserModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="user-form" onsubmit="return false">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Full Name <span style="color:#ef4444">*</span></label>
                            <input type="text" id="uf-name" class="form-control" placeholder="e.g. Maria Santos">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Username <span style="color:#ef4444">*</span></label>
                            <input type="text" id="uf-username" class="form-control" placeholder="e.g. msantos">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span style="color:#ef4444">*</span></label>
                        <input type="email" id="uf-email" class="form-control" placeholder="user@leasepro.ph">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select id="uf-role" class="form-control">
                                <option value="" disabled>Select Role...</option>
                                <option value="Admin">Admin</option>
                                <option value="Manager">Manager</option>
                                <option value="Staff" selected>Staff</option>
                                <option value="Viewer">Viewer</option>
                                <option value="User">User</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select id="uf-status" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" id="uf-pass-label">Password <span style="font-size:0.7rem;color:var(--muted)">(leave
                                blank to keep)</span></label>
                        <input type="password" id="uf-password" class="form-control" placeholder="Min. 8 characters">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeUserModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveUser()"><i class="fa-solid fa-floppy-disk"></i> Save
                    User</button>
            </div>
        </div>
    </div>

    <!-- Generic Confirm Modal -->
    <div class="modal-backdrop" id="confirm-modal">
        <div class="modal" style="max-width:400px">
            <div class="modal-header">
                <div class="modal-title" id="confirm-title" style="color:#fca5a5">
                    <i id="confirm-icon" class="fa-solid fa-triangle-exclamation" style="margin-right:8px"></i>
                    <span id="confirm-title-text">Confirm Action</span>
                </div>
                <button class="modal-close" onclick="closeConfirm()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <p id="confirm-msg" style="font-size:0.9rem;color:var(--text);line-height:1.6"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeConfirm()">Cancel</button>
                <button class="btn btn-danger btn-danger-solid" id="confirm-ok-btn">Confirm</button>
            </div>
        </div>
    </div>


    <!-- Page Edit Modal -->
    <div class="modal-backdrop" id="page-modal">
        <div class="modal" style="max-width:500px">
            <div class="modal-header">
                <div class="modal-title"><i class="fa-solid fa-layer-group"
                        style="margin-right:8px;color:#60a5fa"></i>Edit Page</div>
                <button class="modal-close" onclick="closePageModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Page Name <span style="color:#ef4444">*</span></label>
                    <input type="text" id="page-name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">URL / Slug <span style="color:#ef4444">*</span></label>
                    <input type="text" id="page-slug" class="form-control" placeholder="e.g. index.html">
                </div>
                <div class="form-group" style="margin-top:20px">
                    <label
                        style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:12px 16px;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:10px">
                        <label class="toggle-switch"><input type="checkbox" id="page-visible"><span
                                class="toggle-thumb"></span></label>
                        <div>
                            <div style="font-weight:700;font-size:0.875rem">Page Visibility</div>
                            <div style="font-size:0.75rem;color:var(--muted)">Show or hide this page from the public
                            </div>
                        </div>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closePageModal()">Cancel</button>
                <button class="btn btn-primary" onclick="savePage()"><i class="fa-solid fa-floppy-disk"></i> Save
                    Changes</button>
            </div>
        </div>
    </div>

    <!-- Lessee Create/Edit Modal -->
    <div class="modal-backdrop" id="create-overlay">
        <div class="modal" style="max-width: 650px;">
            <div class="modal-header">
                <div class="modal-title" id="modal-form-title">
                    <i class="fa-solid fa-user-plus" style="color:var(--primary)"></i> Create New Lessee
                </div>
                <button class="modal-close" onclick="closeCreateLesseeModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div class="modal-body">
                <form id="form-create-lessee" onsubmit="submitCreateLesseeForm(event)">
                    <input type="hidden" name="id" id="form-lessee-id" value="">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Company Name <span style="color:red">*</span></label>
                            <input type="text" name="company_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Trade / Store Name</label>
                            <input type="text" name="trade_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Space Code / Stall</label>
                            <input type="text" name="space_code" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Owner / Rep Name</label>
                            <input type="text" name="owner_lessee_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Total Area (sq.m)</label>
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
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email_address" class="form-control">
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeCreateLesseeModal()">Cancel</button>
                <button type="submit" form="form-create-lessee" class="btn btn-primary" id="btn-submit-create">
                    <i class="fa-solid fa-check"></i> Create Record
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

