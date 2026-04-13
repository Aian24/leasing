/* ─── Admin Panel Data Store & Logic ─── */

// ── Shared State ──────────────────────────────────────────────
let DB = {
    users: [],
    activities: [],
    pages: [],
    sysInfo: { diskUsage: 45, memUsage: 58, cpuLoad: 12 },
    sessions: [],
};

let editingUserId = null;

// ── Helpers ────────────────────────────────────────────────
function safeSetText(id, txt) {
    const el = document.getElementById(id);
    if (el) el.textContent = txt;
}
function safeSetHTML(id, html) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = html;
}
function showToast(msg, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> <span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.classList.add('out'); setTimeout(() => toast.remove(), 400); }, 3000);
}

// ── API Core ──────────────────────────────────────────────
const APP_API = {
    base: '',
    init() {
        const path = window.location.pathname;
        this.base = path.substring(0, path.indexOf('/admin/')) + '/api';
    },
    async fetch(action, params = {}) {
        try {
            const query = new URLSearchParams({ action, ...params }).toString();
            const res = await fetch(`${this.base}/admin_api.php?${query}`);
            return await res.json();
        } catch (e) {
            console.error(`API Error [${action}]:`, e);
            return { success: false };
        }
    },
    async post(action, data) {
        try {
            const res = await fetch(`${this.base}/admin_api.php?action=${action}`, {
                method: 'POST',
                body: JSON.stringify(data),
                headers: { 'Content-Type': 'application/json' }
            });
            return await res.json();
        } catch (e) {
            console.error(`POST Error [${action}]:`, e);
            return { success: false };
        }
    }
};

// ── Navigation ──────────────────────────────────────────────
function navigate(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;

    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

    section.classList.add('active');

    const navItem = document.querySelector(`.nav-item[data-section="${sectionId}"]`);
    if (navItem) navItem.classList.add('active');

    const titles = {
        'sec-dashboard': ['Dashboard', 'Admin / Overview'],
        'sec-users': ['User Management', 'Admin / Users'],
        'sec-lessees': ['Lessee Management', 'Admin / Management'],
    };
    const t = titles[sectionId] || ['Dashboard', 'Admin'];
    safeSetText('page-title', t[0]);
    safeSetText('page-breadcrumb', t[1]);

    if (sectionId === 'sec-dashboard') refreshDashboard();
    if (sectionId === 'sec-users') refreshUsers();
}

// ── Dashboard Logic ─────────────────────────────────────────
async function refreshDashboard() {
    const res = await APP_API.fetch('dashboard_stats');
    if (res.success) {
        safeSetText('stat-users', res.users.total);
        safeSetText('stat-active', res.users.active);
        safeSetText('stat-pages', res.pages.total);
        safeSetText('stat-online', res.sessions.online);
        renderGauge('gauge-disk', 42, '#3b82f6');
        renderGauge('gauge-mem', 65, '#2563eb');
        renderGauge('gauge-cpu', 18, '#38bdf8');
    }
    const heights = [55, 70, 45, 80, 65, 90, 72, 60, 85, 95, 75, 88];
    safeSetHTML('monthly-chart', heights.map((h, i) => `<div class="chart-bar" style="height:${h}%;animation-delay:${i * 0.05}s"></div>`).join(''));
}

// ── User Management Logic ────────────────────────────────────
async function refreshUsers() {
    const res = await APP_API.fetch('list_users');
    if (res.success) {
        DB.users = res.data;
        renderUsersTable();
    }
}

function renderUsersTable(filter = '') {
    const tbody = document.getElementById('users-tbody');
    if (!tbody) return;
    const roleColors = { Admin: 'chip-blue', Manager: 'chip-blue', Staff: 'chip-green', Viewer: 'chip-amber' };
    const statusColors = { Active: 'chip-green', Inactive: 'chip-amber', Suspended: 'chip-red' };

    const data = DB.users.filter(u =>
        !filter || u.name.toLowerCase().includes(filter) ||
        u.username.toLowerCase().includes(filter) ||
        u.email.toLowerCase().includes(filter)
    );

    tbody.innerHTML = data.length ? data.map(u => `
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="avatar-initials" style="background:${avatarColor(u.name)}">${initials(u.name)}</div>
                    <div><div style="font-weight:700;font-size:0.875rem">${u.name}</div><div style="font-size:0.75rem;color:var(--muted)">@${u.username}</div></div>
                </div>
            </td>
            <td style="font-family:'Inter',sans-serif;font-size:0.8125rem;color:var(--muted)">${u.email}</td>
            <td><span class="chip ${roleColors[u.role] || 'chip-blue'}">${u.role}</span></td>
            <td><span class="chip ${statusColors[u.status] || 'chip-amber'}">${u.status}</span></td>
            <td style="font-size:0.75rem;color:var(--muted);font-family:'Inter',sans-serif">${u.lastLogin || 'Never'}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-ghost btn-xs" onclick="openEditUser(${u.id})"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-danger btn-xs" onclick="confirmDeleteUser(${u.id})"><i class="fa-solid fa-trash"></i></button>
                </div>
            </td>
        </tr>`).join('') : `<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No users found in database</td></tr>`;
}

function openAddUser() {
    editingUserId = null;
    safeSetText('modal-user-title', 'Add New User');
    document.getElementById('user-form')?.reset();
    document.getElementById('user-modal')?.classList.add('open');
}

function openEditUser(id) {
    const u = DB.users.find(x => x.id === id);
    if (!u) return;
    editingUserId = id;
    safeSetText('modal-user-title', 'Edit User');
    document.getElementById('uf-name').value = u.name;
    document.getElementById('uf-username').value = u.username;
    document.getElementById('uf-email').value = u.email;
    document.getElementById('uf-role').value = u.role;
    document.getElementById('uf-status').value = u.status;
    document.getElementById('uf-password').value = '';
    document.getElementById('user-modal')?.classList.add('open');
}

function closeUserModal() { document.getElementById('user-modal')?.classList.remove('open'); }

async function saveUser() {
    const payload = {
        id: editingUserId,
        name: document.getElementById('uf-name').value.trim(),
        username: document.getElementById('uf-username').value.trim(),
        email: document.getElementById('uf-email').value.trim(),
        role: document.getElementById('uf-role').value,
        status: document.getElementById('uf-status').value,
        password: document.getElementById('uf-password').value
    };

    if (!payload.name || !payload.username) return showToast('Name and Username are required', 'warning');
    if (!editingUserId && !payload.password) return showToast('Password is required', 'warning');

    const res = await APP_API.post(editingUserId ? 'update_user' : 'create_user', payload);
    if (res.success) {
        showToast(res.message, 'success');
        closeUserModal();
        refreshUsers();
        refreshDashboard();
    } else {
        showToast(res.message, 'danger');
    }
}

function confirmDeleteUser(id) {
    const u = DB.users.find(x => x.id === id);
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
            const res = await APP_API.post('delete_user', { id });
            if (res.success) {
                showToast('User deleted', 'success');
                refreshUsers();
                refreshDashboard();
            } else showToast(res.message, 'danger');
        }
    });
}

function confirmAction({ title, msg, icon, color, btnClass, btnIcon, btnText, action }) {
    const t = document.getElementById('confirm-title');
    if (t) t.style.color = color;
    safeSetText('confirm-title-text', title);
    const i = document.getElementById('confirm-icon');
    if (i) i.className = `fa-solid ${icon}`;
    safeSetHTML('confirm-msg', msg);
    const okBtn = document.getElementById('confirm-ok-btn');
    if (okBtn) {
        okBtn.className = `btn ${btnClass}`;
        okBtn.innerHTML = `<i class="fa-solid ${btnIcon}" style="margin-right:6px"></i> ${btnText}`;
        okBtn.onclick = () => { closeConfirm(); action(); };
    }
    document.getElementById('confirm-modal')?.classList.add('open');
}
function closeConfirm() { document.getElementById('confirm-modal')?.classList.remove('open'); }

function renderGauge(id, val, color) {
    const el = document.getElementById(id);
    if (el) { el.style.width = val + '%'; el.style.background = color; }
}
function initials(name) { return name ? name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '??'; }
function avatarColor(name) {
    const colors = ['#3b82f6', '#ec4899', '#10b981', '#f59e0b', '#14b8a6'];
    let h = 0; if (name) for (const c of name) h += c.charCodeAt(0);
    return colors[h % colors.length];
}

// ── Initialization ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    APP_API.init();
    const p = window.location.pathname;
    let sec = 'sec-dashboard';
    if (p.includes('users.php')) sec = 'sec-users';
    else if (p.includes('lessees.php')) sec = 'sec-lessees';
    document.querySelectorAll('.nav-item[data-section]').forEach(item => {
        item.addEventListener('click', () => navigate(item.dataset.section));
    });

    // User Search Logic
    document.getElementById('user-search')?.addEventListener('input', e => {
        renderUsersTable(e.target.value.toLowerCase());
    });

    navigate(sec);
});
