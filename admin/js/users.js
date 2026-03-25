/* ─── User Management Module ─── */
const USERS_PAGE = {
    data: [],
    editingId: null,
    apiBase: '',

    init() {
        console.log('Users Module Initializing...');
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/users_api.php';
        console.log('Users API Path:', this.apiBase);

        // Immediate Header Update
        safeSetText('page-title', 'User Management');
        safeSetText('page-breadcrumb', 'Admin / Management');

        this.bindEvents();
        this.refresh();
    },

    bindEvents() {
        const searchInput = document.getElementById('user-search');
        if (searchInput) {
            searchInput.addEventListener('input', e => this.render(e.target.value.toLowerCase()));
        }
    },

    async refresh() {
        try {
            console.log('Fetching Users List...');
            const res = await fetch(`${this.apiBase}?action=list`);
            const json = await res.json();
            console.log('Users received:', json);

            if (json.success) {
                this.data = json.data;
                this.render();
            }
        } catch (e) {
            console.error('Failed to load users:', e);
        }
    },

    render(filter = '') {
        const tbody = document.getElementById('users-tbody');
        if (!tbody) {
            console.warn('Tbody #users-tbody not found! Data won\'t show.');
            return;
        }

        const roleColors = { Admin: 'chip-blue', Manager: 'chip-blue', Staff: 'chip-green', Viewer: 'chip-amber' };
        const statusColors = { Active: 'chip-green', Inactive: 'chip-amber', Suspended: 'chip-red' };

        const filtered = this.data.filter(u =>
            !filter || u.name.toLowerCase().includes(filter) ||
            u.username.toLowerCase().includes(filter) ||
            u.email.toLowerCase().includes(filter)
        );

        tbody.innerHTML = filtered.length ? filtered.map(u => `
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="avatar-initials" style="background:${this.getAvatarColor(u.name)}">${this.getInitials(u.name)}</div>
                        <div><div style="font-weight:700;font-size:0.875rem">${u.name}</div><div style="font-size:0.75rem;color:var(--muted)">@${u.username}</div></div>
                    </div>
                </td>
                <td style="font-family:'Inter',sans-serif;font-size:0.8125rem;color:var(--muted)">${u.email}</td>
                <td><span class="chip ${roleColors[u.role] || 'chip-blue'}">${u.role}</span></td>
                <td><span class="chip ${statusColors[u.status] || 'chip-amber'}">${u.status}</span></td>
                <td style="font-size:0.75rem;color:var(--muted);font-family:'Inter',sans-serif">${u.lastLogin ? this.formatDate(u.lastLogin) : 'Never'}</td>
                <td>
                    <div style="display:flex;gap:6px">
                        <button class="btn btn-ghost btn-xs" onclick="USERS_PAGE.openEdit(${u.id})"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-danger btn-xs" onclick="USERS_PAGE.confirmDelete(${u.id})"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>`).join('') : `<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No users found</td></tr>`;
    },

    openAdd() {
        this.editingId = null;
        safeSetText('modal-user-title', 'Add New User');
        document.getElementById('user-form')?.reset();
        document.getElementById('user-modal')?.classList.add('open');
    },

    openEdit(id) {
        const u = this.data.find(x => x.id === id);
        if (!u) return;
        this.editingId = id;
        safeSetText('modal-user-title', 'Edit User');
        document.getElementById('uf-name').value = u.name;
        document.getElementById('uf-username').value = u.username;
        document.getElementById('uf-email').value = u.email;
        document.getElementById('uf-role').value = u.role;
        document.getElementById('uf-status').value = u.status;
        document.getElementById('uf-password').value = '';
        document.getElementById('user-modal')?.classList.add('open');
    },

    async save() {
        const payload = {
            id: this.editingId,
            name: document.getElementById('uf-name').value.trim(),
            username: document.getElementById('uf-username').value.trim(),
            email: document.getElementById('uf-email').value.trim(),
            role: document.getElementById('uf-role').value,
            status: document.getElementById('uf-status').value,
            password: document.getElementById('uf-password').value
        };

        if (!payload.name || !payload.username) return showToast('Name and Username are required', 'warning');

        const action = this.editingId ? 'update' : 'create';
        try {
            const res = await fetch(`${this.apiBase}?action=${action}`, {
                method: 'POST',
                body: JSON.stringify(payload),
                headers: { 'Content-Type': 'application/json' }
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message);
                document.getElementById('user-modal')?.classList.remove('open');
                this.refresh();
            } else showToast(json.message, 'danger');
        } catch (e) {
            showToast('Network error', 'danger');
        }
    },

    confirmDelete(id) {
        const u = this.data.find(x => x.id === id);
        if (!u) return;
        confirmAction({
            title: 'Delete User',
            msg: `Delete user <b>${u.name}</b>? This cannot be undone.`,
            icon: 'fa-user-minus',
            color: '#ef4444',
            btnClass: 'btn-danger',
            btnIcon: 'fa-trash',
            btnText: 'Delete User',
            action: async () => {
                try {
                    const res = await fetch(`${this.apiBase}?action=delete`, {
                        method: 'POST',
                        body: JSON.stringify({ id }),
                        headers: { 'Content-Type': 'application/json' }
                    });
                    const json = await res.json();
                    if (json.success) { showToast('User deleted'); this.refresh(); }
                    else showToast(json.message, 'danger');
                } catch (e) { showToast('Network error', 'danger'); }
            }
        });
    },

    getInitials(name) { return name ? name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '??'; },
    getAvatarColor(name) {
        const colors = ['#3b82f6', '#ec4899', '#10b981', '#f59e0b', '#14b8a6'];
        let h = 0; if (name) for (const c of name) h += c.charCodeAt(0);
        return colors[h % colors.length];
    },
    formatDate(dateStr) {
        return GLOBAL_UI.formatDateTime(dateStr);
    }
};

// Global hooks
window.openAddUser = () => USERS_PAGE.openAdd();
window.openEditUser = (id) => USERS_PAGE.openEdit(id);
window.saveUser = () => USERS_PAGE.save();
window.confirmDeleteUser = (id) => USERS_PAGE.confirmDelete(id);
window.closeUserModal = () => document.getElementById('user-modal')?.classList.remove('open');

// Initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => USERS_PAGE.init());
} else {
    USERS_PAGE.init();
}
