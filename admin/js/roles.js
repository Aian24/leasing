/* ── Roles & Permissions Management Module ── */
const ROLES_PAGE = {
    apiBase: '',

    init() {
        console.log('Roles Module Initializing...');
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/roles_api.php';

        // Breadcrumbs & Title
        safeSetText('page-title', 'Roles & Permissions');
        safeSetText('page-breadcrumb', 'Admin / Security / Role Matrix');

        this.loadPermissions();
    },

    async loadPermissions() {
        const tbody = document.getElementById('roles-tbody');
        if (!tbody) return;

        tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading Matrix...</p></div></td></tr>`;

        try {
            const res = await fetch(`${this.apiBase}?action=list`);
            const data = await res.json();
            if (!data.success) { showToast(data.message, 'danger'); return; }

            this.renderMatrix(data.data);
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-circle-exclamation"></i><p>Failed to load: ${e.message}</p></div></td></tr>`;
        }
    },

    renderMatrix(rows) {
        const tbody = document.getElementById('roles-tbody');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><p>No permissions defined.</p></div></td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr>
                <td style="font-weight:600; color:var(--text)">${this.esc(r.permission_name)}</td>
                <td class="perm-cell">${this.renderCheck(r.id, 'admin', r.admin_access)}</td>
                <td class="perm-cell">${this.renderCheck(r.id, 'manager', r.manager_access)}</td>
                <td class="perm-cell">${this.renderCheck(r.id, 'staff', r.staff_access)}</td>
                <td class="perm-cell">${this.renderCheck(r.id, 'viewer', r.viewer_access)}</td>
                <td style="text-align:right">
                    <div style="display:flex; gap:8px; justify-content:flex-end">
                        <button class="btn btn-ghost btn-sm" style="color:#60a5fa" onclick='ROLES_PAGE.openEditModal(${JSON.stringify(r)})' title="Edit Full">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-ghost btn-sm" style="color:#fca5a5" onclick="ROLES_PAGE.confirmDelete(${r.id}, '${this.esc(r.permission_name).replace(/'/g, "\\'")}')" title="Delete">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderCheck(id, role, val) {
        const icon = val ? 'fa-check' : 'fa-xmark';
        const color = val ? '#86efac' : 'rgba(148, 163, 184, 0.3)';
        return `
            <button class="icon-btn btn-ghost" 
                    style="color:${color}; border:none; background:none; width:auto; height:auto; padding:8px"
                    onclick="ROLES_PAGE.toggle(${id}, '${role}', ${val})">
                <i class="fa-solid ${icon}"></i>
            </button>
        `;
    },

    async toggle(id, role, currentVal) {
        const newVal = currentVal ? 0 : 1;
        try {
            const res = await fetch(`${this.apiBase}?action=toggle`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, role, value: newVal })
            });
            const data = await res.json();
            if (data.success) {
                this.loadPermissions();
                showToast('Permission updated');
            } else {
                showToast(data.message, 'danger');
            }
        } catch (e) {
            showToast('Network Error', 'danger');
        }
    },

    esc(s) {
        if (!s) return '—';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    openPermModal() {
        safeSetText('perm-modal-title', 'Add New Permission');
        safeSetHTML('btn-submit-perm', 'Add Permission');
        const form = document.getElementById('form-add-perm');
        if (form) form.reset();
        document.getElementById('perm-edit-id').value = '';
        document.getElementById('perm-overlay')?.classList.add('open');
        setTimeout(() => document.getElementById('perm-name-input')?.focus(), 100);
    },

    openEditModal(item) {
        safeSetText('perm-modal-title', 'Edit Permission');
        safeSetHTML('btn-submit-perm', 'Save Changes');

        document.getElementById('perm-edit-id').value = item.id;
        document.getElementById('perm-name-input').value = item.permission_name;

        // Populate checkboxes
        const form = document.getElementById('form-add-perm');
        if (form) {
            form.querySelector('input[name="admin"]').checked = !!parseInt(item.admin_access);
            form.querySelector('input[name="manager"]').checked = !!parseInt(item.manager_access);
            form.querySelector('input[name="staff"]').checked = !!parseInt(item.staff_access);
            form.querySelector('input[name="viewer"]').checked = !!parseInt(item.viewer_access);
        }

        document.getElementById('perm-overlay')?.classList.add('open');
        setTimeout(() => document.getElementById('perm-name-input')?.focus(), 100);
    },

    closePermModal() {
        document.getElementById('perm-overlay')?.classList.remove('open');
    },

    async submitNewPermission(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-perm');
        const formData = new FormData(e.target);

        const isEdit = !!formData.get('id');
        const action = isEdit ? 'update' : 'add';

        const data = {
            id: formData.get('id'),
            name: formData.get('name'),
            admin: formData.get('admin') === 'on',
            manager: formData.get('manager') === 'on',
            staff: formData.get('staff') === 'on',
            viewer: formData.get('viewer') === 'on'
        };

        if (!data.name) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const res = await fetch(`${this.apiBase}?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message);
                this.closePermModal();
                this.loadPermissions();
            } else {
                showToast(json.message, 'danger');
            }
        } catch (e) {
            showToast('Network Error', 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = isEdit ? 'Save Changes' : 'Add Permission';
        }
    },

    confirmDelete(id, name) {
        confirmAction({
            title: 'Delete Permission?',
            msg: `This will permanently remove the "<b>${name}</b>" module from the matrix. This action cannot be undone.`,
            icon: 'fa-trash-can',
            color: '#ef4444',
            btnClass: 'btn-danger',
            btnIcon: 'fa-trash',
            btnText: 'Delete Module',
            action: async () => {
                try {
                    const res = await fetch(`${this.apiBase}?action=delete`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast('Permission removed');
                        this.loadPermissions();
                    } else showToast(data.message, 'danger');
                } catch (e) { showToast('Network Error', 'danger'); }
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => ROLES_PAGE.init());
