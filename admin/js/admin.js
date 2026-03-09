/* ─── Admin Panel Data Store & Logic ─── */

// ── Sample Data ──────────────────────────────────────────────
const DB = {
    users: [
        { id: 1, name: 'Maria Santos', username: 'msantos', email: 'maria@leasepro.ph', role: 'Admin', status: 'Active', lastLogin: '2026-03-06 08:30', avatar: null },
        { id: 2, name: 'Juan dela Cruz', username: 'jdelacruz', email: 'juan@leasepro.ph', role: 'Manager', status: 'Active', lastLogin: '2026-03-05 14:12', avatar: null },
        { id: 3, name: 'Ana Reyes', username: 'areyes', email: 'ana@leasepro.ph', role: 'Staff', status: 'Active', lastLogin: '2026-03-06 07:55', avatar: null },
        { id: 4, name: 'Pedro Lim', username: 'plim', email: 'pedro@leasepro.ph', role: 'Staff', status: 'Inactive', lastLogin: '2026-02-20 09:00', avatar: null },
        { id: 5, name: 'Rosa Cruz', username: 'rcruz', email: 'rosa@leasepro.ph', role: 'Viewer', status: 'Active', lastLogin: '2026-03-04 11:30', avatar: null },
        { id: 6, name: 'Carlos Tan', username: 'ctan', email: 'carlos@leasepro.ph', role: 'Manager', status: 'Suspended', lastLogin: '2026-02-28 16:45', avatar: null },
    ],
    activities: [
        { icon: 'fa-user-plus', color: 'rgba(99,102,241,0.2)', text: '#fff', desc: '<b>msantos</b> created a new lease contract for <b>Footwear Shop</b>', time: '2 min ago' },
        { icon: 'fa-pen-to-square', color: 'rgba(245,158,11,0.2)', text: '#fcd34d', desc: '<b>jdelacruz</b> updated stall config for <b>BLOCK4-11</b>', time: '18 min ago' },
        { icon: 'fa-user-xmark', color: 'rgba(239,68,68,0.2)', text: '#fca5a5', desc: '<b>ctan</b> account was suspended by administrator', time: '1 hr ago' },
        { icon: 'fa-right-to-bracket', color: 'rgba(34,197,94,0.2)', text: '#86efac', desc: '<b>areyes</b> logged in from <b>192.168.1.5</b>', time: '2 hr ago' },
        { icon: 'fa-file-invoice', color: 'rgba(56,189,248,0.2)', text: '#7dd3fc', desc: 'System generated monthly billing for <b>12 tenants</b>', time: '5 hr ago' },
        { icon: 'fa-key', color: 'rgba(139,92,246,0.2)', text: '#c4b5fd', desc: '<b>plim</b> reset password successfully', time: 'Yesterday' },
    ],
    pages: [
        { id: 1, name: 'Home / Dashboard', slug: 'user/index.html', visible: true, lastEdit: '2026-03-01', editedBy: 'msantos' },
        { id: 2, name: 'Login Page', slug: 'index.html', visible: true, lastEdit: '2026-03-06', editedBy: 'msantos' },
        { id: 3, name: 'Lessee Management', slug: '#lessee', visible: true, lastEdit: '2026-02-28', editedBy: 'jdelacruz' },
        { id: 4, name: 'Stall Configuration', slug: '#stall', visible: true, lastEdit: '2026-02-25', editedBy: 'areyes' },
        { id: 5, name: 'Lease Terms & Dates', slug: '#terms', visible: false, lastEdit: '2026-02-20', editedBy: 'jdelacruz' },
        { id: 6, name: 'Admin Panel', slug: 'admin/index.html', visible: true, lastEdit: '2026-03-06', editedBy: 'msantos' },
    ],
    sysInfo: {
        serverOS: 'Windows Server 2022',
        phpVersion: '8.2.4',
        mysqlVersion: '8.0.32',
        appVersion: 'v2.4.1',
        diskUsage: 48,
        memUsage: 62,
        cpuLoad: 23,
        uptime: '12d 4h 37m',
    },
    permissions: [
        { label: 'Dashboard Access', admin: 1, manager: 1, staff: 1, viewer: 1 },
        { label: 'View Contracts', admin: 1, manager: 1, staff: 1, viewer: 1 },
        { label: 'Create Contracts', admin: 1, manager: 1, staff: 1, viewer: 0 },
        { label: 'Edit Contracts', admin: 1, manager: 1, staff: 1, viewer: 0 },
        { label: 'Delete Contracts', admin: 1, manager: 0, staff: 0, viewer: 0 },
        { label: 'Manage Users', admin: 1, manager: 0, staff: 0, viewer: 0 },
        { label: 'Manage Roles', admin: 1, manager: 0, staff: 0, viewer: 0 },
        { label: 'View Audit Logs', admin: 1, manager: 1, staff: 0, viewer: 0 },
        { label: 'Export Reports', admin: 1, manager: 1, staff: 0, viewer: 0 },
        { label: 'App Settings', admin: 1, manager: 0, staff: 0, viewer: 0 },
        { label: 'SMTP Configuration', admin: 1, manager: 0, staff: 0, viewer: 0 },
        { label: 'Run Backups', admin: 1, manager: 0, staff: 0, viewer: 0 },
        { label: 'Publish Announcements', admin: 1, manager: 1, staff: 0, viewer: 0 },
        { label: 'Manage Frontend Pages', admin: 1, manager: 0, staff: 0, viewer: 0 },
        { label: 'Force Logout Sessions', admin: 1, manager: 0, staff: 0, viewer: 0 },
    ],
    sessions: [
        { id: 1, user: 'msantos', name: 'Maria Santos', ip: '192.168.1.10', browser: 'Chrome 122 / Windows', loginTime: '2026-03-06 08:30', duration: '32m', status: 'active' },
        { id: 2, user: 'areyes', name: 'Ana Reyes', ip: '192.168.1.5', browser: 'Firefox 124 / Windows', loginTime: '2026-03-06 07:55', duration: '1h 7m', status: 'active' },
        { id: 3, user: 'rcruz', name: 'Rosa Cruz', ip: '192.168.1.22', browser: 'Edge 121 / Windows', loginTime: '2026-03-06 08:50', duration: '12m', status: 'active' },
    ],
    announcements: [
        { id: 1, title: 'Scheduled System Maintenance', body: 'The system will be down for maintenance on March 10, 2026 from 12:00 AM to 3:00 AM. Please save your work beforehand.', type: 'warning', roles: 'all', publishedBy: 'msantos', publishedAt: '2026-03-05', expires: '2026-03-10' },
        { id: 2, title: 'New Feature: Lease Term Wizard', body: 'A new guided wizard for setting lease terms is now available. Access it from the Lease Terms & Dates section.', type: 'success', roles: 'Staff', publishedBy: 'msantos', publishedAt: '2026-03-01', expires: '' },
    ],
};

let nextUserId = DB.users.length + 1;
let editingUserId = null;

// ── Navigation ──────────────────────────────────────────────
function navigate(sectionId) {
    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

    const section = document.getElementById(sectionId);
    if (section) section.classList.add('active');

    const navItem = document.querySelector(`.nav-item[data-section="${sectionId}"]`);
    if (navItem) navItem.classList.add('active');

    const titles = {
        'sec-dashboard': ['Dashboard', 'Admin / Overview'],
        'sec-users': ['User Management', 'Admin / Users'],
        'sec-roles': ['Roles & Permissions', 'Admin / Security'],
        'sec-sessions': ['Active Sessions', 'Admin / Sessions'],
        'sec-announcements': ['Announcements', 'Admin / Broadcast'],
        'sec-pages': ['Frontend Pages', 'Admin / Content'],
        'sec-system': ['System Info', 'Admin / System'],
        'sec-logs': ['Audit Logs', 'Admin / Logs'],
        'sec-settings': ['App Settings', 'Admin / Config'],
    };
    const t = titles[sectionId] || ['Dashboard', 'Admin'];
    document.getElementById('page-title').textContent = t[0];
    document.getElementById('page-breadcrumb').textContent = t[1];
}

// ── Dashboard ────────────────────────────────────────────────
function renderDashboard() {
    document.getElementById('stat-users').textContent = DB.users.length;
    document.getElementById('stat-active').textContent = DB.users.filter(u => u.status === 'Active').length;
    document.getElementById('stat-pages').textContent = DB.pages.length;
    document.getElementById('stat-online').textContent = DB.sessions.length;

    // Activity feed
    const feed = document.getElementById('activity-feed');
    feed.innerHTML = DB.activities.map(a => `
        <div class="activity-item">
            <div class="activity-dot" style="background:${a.color}; color:${a.text}">
                <i class="fa-solid ${a.icon}"></i>
            </div>
            <div>
                <div class="activity-text">${a.desc}</div>
                <div class="activity-time"><i class="fa-regular fa-clock"></i> ${a.time}</div>
            </div>
        </div>`).join('');

    // Mini chart bars
    const heights = [55, 70, 45, 80, 65, 90, 72, 60, 85, 95, 75, 88];
    const chart = document.getElementById('monthly-chart');
    chart.innerHTML = heights.map((h, i) => `<div class="chart-bar" style="height:${h}%;animation-delay:${i * 0.05}s"></div>`).join('');

    // System gauges
    renderGauge('gauge-disk', DB.sysInfo.diskUsage, '#3b82f6');
    renderGauge('gauge-mem', DB.sysInfo.memUsage, '#2563eb');
    renderGauge('gauge-cpu', DB.sysInfo.cpuLoad, '#38bdf8');
    document.getElementById('dash-disk-val').textContent = DB.sysInfo.diskUsage + '%';
    document.getElementById('dash-mem-val').textContent = DB.sysInfo.memUsage + '%';
    document.getElementById('dash-cpu-val').textContent = DB.sysInfo.cpuLoad + '%';

    // Recent users widget
    const rul = document.getElementById('recent-users-list');
    if (rul) rul.innerHTML = DB.users.slice(0, 5).map(u => `
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(51,65,85,0.4);">
            <div class="avatar-initials" style="background:${avatarColor(u.name)};width:32px;height:32px;font-size:0.7rem">${initials(u.name)}</div>
            <div style="flex:1;overflow:hidden">
                <div style="font-weight:700;font-size:0.8125rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${u.name}</div>
                <div style="font-size:0.7rem;color:var(--muted)">${u.role} · ${u.status}</div>
            </div>
            <span class="chip ${u.status === 'Active' ? 'chip-green' : u.status === 'Suspended' ? 'chip-red' : 'chip-amber'}" style="font-size:0.6rem">${u.status}</span>
        </div>`).join('');
}

function renderGauge(id, val, color) {
    const el = document.getElementById(id);
    if (el) el.style.width = val + '%';
    if (el) el.style.background = color;
}

// ── User Management ──────────────────────────────────────────
function renderUsersTable(filter = '') {
    const tbody = document.getElementById('users-tbody');
    const roleColors = { Admin: 'chip-blue', Manager: 'chip-blue', Staff: 'chip-green', Viewer: 'chip-amber' };
    const statusColors = { Active: 'chip-green', Inactive: 'chip-amber', Suspended: 'chip-red' };

    const data = DB.users.filter(u =>
        !filter || u.name.toLowerCase().includes(filter) ||
        u.username.toLowerCase().includes(filter) ||
        u.email.toLowerCase().includes(filter) ||
        u.role.toLowerCase().includes(filter)
    );

    tbody.innerHTML = data.length ? data.map(u => `
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="avatar-initials" style="background:${avatarColor(u.name)}">${initials(u.name)}</div>
                    <div>
                        <div style="font-weight:700;font-size:0.875rem">${u.name}</div>
                        <div style="font-size:0.75rem;color:var(--muted)">@${u.username}</div>
                    </div>
                </div>
            </td>
            <td style="font-family:'Inter',sans-serif;font-size:0.8125rem;color:var(--muted)">${u.email}</td>
            <td><span class="chip ${roleColors[u.role] || 'chip-blue'}">${u.role}</span></td>
            <td><span class="chip ${statusColors[u.status] || 'chip-amber'}">${u.status}</span></td>
            <td style="font-size:0.75rem;color:var(--muted);font-family:'Inter',sans-serif">${u.lastLogin}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-ghost btn-xs" onclick="openEditUser(${u.id})"><i class="fa-solid fa-pen"></i> Edit</button>
                    <button class="btn btn-danger btn-xs" onclick="confirmDeleteUser(${u.id})""><i class="fa-solid fa-trash"></i></button>
                </div>
            </td>
        </tr>`).join('') :
        `<tr><td colspan="6" style="text-align:center;padding:32px;color:var(--muted)"><i class="fa-solid fa-users-slash" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:8px"></i>No users found</td></tr>`;
}

function initials(name) {
    return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
}
function avatarColor(name) {
    const colors = ['#3b82f6', '#2563eb', '#ec4899', '#10b981', '#f59e0b', '#3b82f6', '#14b8a6'];
    let h = 0; for (const c of name) h += c.charCodeAt(0);
    return colors[h % colors.length];
}

// ── User Modal ───────────────────────────────────────────────
function openAddUser() {
    editingUserId = null;
    document.getElementById('modal-user-title').textContent = 'Add New User';
    document.getElementById('user-form').reset();
    document.getElementById('user-modal').classList.add('open');
}
function openEditUser(id) {
    const u = DB.users.find(x => x.id === id);
    if (!u) return;
    editingUserId = id;
    document.getElementById('modal-user-title').textContent = 'Edit User';
    document.getElementById('uf-name').value = u.name;
    document.getElementById('uf-username').value = u.username;
    document.getElementById('uf-email').value = u.email;
    document.getElementById('uf-role').value = u.role;
    document.getElementById('uf-status').value = u.status;
    document.getElementById('uf-password').value = '';
    document.getElementById('user-modal').classList.add('open');
}
function closeUserModal() {
    document.getElementById('user-modal').classList.remove('open');
}
function confirmAction({ title, msg, icon, color, btnClass, btnIcon, btnText, action }) {
    document.getElementById('confirm-title').style.color = color;
    document.getElementById('confirm-title-text').textContent = title;
    document.getElementById('confirm-icon').className = `fa-solid ${icon}`;
    document.getElementById('confirm-msg').innerHTML = msg;

    const okBtn = document.getElementById('confirm-ok-btn');
    okBtn.className = `btn ${btnClass}`;
    okBtn.innerHTML = `<i class="fa-solid ${btnIcon}" style="margin-right:6px"></i> ${btnText}`;

    okBtn.onclick = () => {
        closeConfirm();
        action();
    };
    document.getElementById('confirm-modal').classList.add('open');
}
function closeConfirm() { document.getElementById('confirm-modal').classList.remove('open'); }

function saveUser() {
    const name = document.getElementById('uf-name').value.trim();
    const uname = document.getElementById('uf-username').value.trim();
    const email = document.getElementById('uf-email').value.trim();
    const role = document.getElementById('uf-role').value;
    const status = document.getElementById('uf-status').value;
    const pass = document.getElementById('uf-password').value;

    if (!name || !uname || !email) { showToast('Please fill all required fields.', 'warning'); return; }

    const isEdit = !!editingUserId;
    confirmAction({
        title: isEdit ? 'Save Changes' : 'Create User',
        msg: isEdit ? `Are you sure you want to update the profile for <b>${name}</b>?` : `Are you sure you want to create a new user account for <b>${name}</b>?`,
        icon: isEdit ? 'fa-pen' : 'fa-user-plus',
        color: isEdit ? '#7dd3fc' : '#86efac',
        btnClass: 'btn-primary',
        btnIcon: 'fa-floppy-disk',
        btnText: 'Confirm Save',
        action: () => {
            if (isEdit) {
                const u = DB.users.find(x => x.id === editingUserId);
                Object.assign(u, { name, username: uname, email, role, status });
                showToast(`User <b>${name}</b> updated successfully.`, 'success');
                logActivity('fa-pen-to-square', 'rgba(245,158,11,0.2)', '#fcd34d', `<b>Admin</b> updated user <b>${name}</b>`, 'Just now');
            } else {
                DB.users.push({ id: nextUserId++, name, username: uname, email, role, status, lastLogin: 'Never', avatar: null });
                showToast(`User <b>${name}</b> created.`, 'success');
                logActivity('fa-user-plus', 'rgba(99,102,241,0.2)', '#a5b4fc', `<b>Admin</b> created user <b>${name}</b>`, 'Just now');
            }
            closeUserModal();
            renderUsersTable();
            renderDashboard();
        }
    });
}

function confirmDeleteUser(id) {
    const u = DB.users.find(x => x.id === id);
    if (!u) return;
    confirmAction({
        title: 'Confirm Delete',
        msg: `Delete user "<b>${u.name}</b>"? This action cannot be undone.`,
        icon: 'fa-triangle-exclamation',
        color: '#fca5a5',
        btnClass: 'btn-danger btn-danger-solid',
        btnIcon: 'fa-trash',
        btnText: 'Delete User',
        action: () => {
            DB.users = DB.users.filter(x => x.id !== id);
            renderUsersTable();
            renderDashboard();
            showToast(`User <b>${u.name}</b> deleted.`, 'danger');
            logActivity('fa-user-xmark', 'rgba(239,68,68,0.2)', '#fca5a5', `<b>Admin</b> deleted user <b>${u.name}</b>`, 'Just now');
        }
    });
}

// ── Pages / Frontend ─────────────────────────────────────────
function renderPagesTable(filter = '') {
    const tbody = document.getElementById('pages-tbody');
    const data = DB.pages.filter(p => !filter || p.name.toLowerCase().includes(filter) || p.slug.toLowerCase().includes(filter));
    tbody.innerHTML = data.map(p => `
        <tr>
            <td style="font-weight:700">${p.name}</td>
            <td><code style="font-size:0.75rem;color:#60a5fa;background:rgba(99,102,241,0.1);padding:3px 8px;border-radius:6px">${p.slug}</code></td>
            <td>
                <label class="toggle-switch">
                    <input type="checkbox" ${p.visible ? 'checked' : ''} onchange="togglePage(${p.id}, this.checked)">
                    <span class="toggle-thumb"></span>
                </label>
            </td>
            <td style="font-size:0.8rem;color:var(--muted)">${p.lastEdit}</td>
            <td style="font-size:0.8rem;color:var(--muted)">@${p.editedBy}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-ghost btn-xs" onclick="openPageModal(${p.id})"><i class="fa-solid fa-pen"></i> Edit</button>
                    <a href="../${p.slug}" target="_blank" class="btn btn-ghost btn-xs"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                </div>
            </td>
        </tr>`).join('');
}
function togglePage(id, val) {
    const p = DB.pages.find(x => x.id === id);
    if (!p) return;
    confirmAction({
        title: val ? 'Show Page' : 'Hide Page',
        msg: `Are you sure you want to ${val ? 'show' : 'hide'} the page "<b>${p.name}</b>" from the public?`,
        icon: val ? 'fa-eye' : 'fa-eye-slash',
        color: val ? '#86efac' : '#fcd34d',
        btnClass: val ? 'btn-primary' : 'btn-danger',
        btnIcon: val ? 'fa-check' : 'fa-power-off',
        btnText: 'Confirm',
        action: () => {
            p.visible = val;
            renderPagesTable();
            showToast(`Page <b>${p.name}</b> ${val ? 'shown' : 'hidden'}.`, val ? 'success' : 'warning');
        }
    });
    // Revert visually to avoid desync if cancelled
    const cb = document.querySelector(`input[onchange*="${id}"]`);
    if (cb) cb.checked = !val;
}

let editingPageId = null;
function openPageModal(id) {
    const p = DB.pages.find(x => x.id === id);
    if (!p) return;
    editingPageId = id;
    document.getElementById('page-name').value = p.name;
    document.getElementById('page-slug').value = p.slug;
    document.getElementById('page-visible').checked = p.visible;
    document.getElementById('page-modal').classList.add('open');
}
function closePageModal() { document.getElementById('page-modal').classList.remove('open'); }
function savePage() {
    const p = DB.pages.find(x => x.id === editingPageId);
    if (!p) return;
    const name = document.getElementById('page-name').value.trim();
    const slug = document.getElementById('page-slug').value.trim();
    const visible = document.getElementById('page-visible').checked;

    if (!name || !slug) { showToast('Name and URL/Slug are required.', 'warning'); return; }

    confirmAction({
        title: 'Save Page Changes',
        msg: `Are you sure you want to update the properties for "<b>${name}</b>"?`,
        icon: 'fa-floppy-disk',
        color: '#7dd3fc',
        btnClass: 'btn-primary',
        btnIcon: 'fa-check',
        btnText: 'Save Changes',
        action: () => {
            p.name = name;
            p.slug = slug;
            p.visible = visible;
            p.lastEdit = new Date().toISOString().slice(0, 10);
            p.editedBy = 'Admin';
            showToast(`Page <b>${name}</b> updated successfully.`, 'success');
            logActivity('fa-layer-group', 'rgba(139,92,246,0.2)', '#c4b5fd', `<b>Admin</b> edited page details for <b>${name}</b>`, 'Just now');
            closePageModal();
            renderPagesTable();
        }
    });
}

// ── System Info ──────────────────────────────────────────────
function renderSystem() {
    const s = DB.sysInfo;
    document.getElementById('sys-os').textContent = s.serverOS;
    document.getElementById('sys-php').textContent = s.phpVersion;
    document.getElementById('sys-mysql').textContent = s.mysqlVersion;
    document.getElementById('sys-ver').textContent = s.appVersion;
    document.getElementById('sys-uptime').textContent = s.uptime;
    renderGauge('sys-disk-bar', s.diskUsage, '#3b82f6');
    renderGauge('sys-mem-bar', s.memUsage, '#2563eb');
    renderGauge('sys-cpu-bar', s.cpuLoad, '#38bdf8');
    document.getElementById('sys-disk-pct').textContent = s.diskUsage + '%';
    document.getElementById('sys-mem-pct').textContent = s.memUsage + '%';
    document.getElementById('sys-cpu-pct').textContent = s.cpuLoad + '%';
}

// ── Audit Logs ───────────────────────────────────────────────
function renderLogsTable() {
    const rows = [
        { time: '2026-03-06 08:30:11', user: 'msantos', action: 'LOGIN', detail: 'Successful login from 192.168.1.10', level: 'info' },
        { time: '2026-03-06 08:32:45', user: 'msantos', action: 'CREATE', detail: 'New lease contract for Footwear Shop', level: 'success' },
        { time: '2026-03-06 07:55:02', user: 'areyes', action: 'LOGIN', detail: 'Successful login from 192.168.1.5', level: 'info' },
        { time: '2026-03-05 16:45:00', user: 'ctan', action: 'SUSPEND', detail: 'Account suspended by administrator', level: 'danger' },
        { time: '2026-03-05 14:12:33', user: 'jdelacruz', action: 'UPDATE', detail: 'Updated stall BLOCK4-11 configuration', level: 'warning' },
        { time: '2026-03-04 09:00:44', user: 'plim', action: 'PASSWORD', detail: 'Password reset via recovery email', level: 'info' },
        { time: '2026-03-03 18:10:05', user: 'System', action: 'BACKUP', detail: 'Automated daily DB backup completed', level: 'success' },
        { time: '2026-03-02 11:25:59', user: 'msantos', action: 'DELETE', detail: 'Deleted test user account "test_user"', level: 'danger' },
    ];
    const levelMap = { info: 'chip-blue', success: 'chip-green', warning: 'chip-amber', danger: 'chip-red' };
    document.getElementById('logs-tbody').innerHTML = rows.map(r => `
        <tr>
            <td style="font-family:'Inter',sans-serif;font-size:0.75rem;color:var(--muted)">${r.time}</td>
            <td><span style="font-weight:700;color:#a5b4fc">@${r.user}</span></td>
            <td><span class="chip chip-blue" style="font-size:0.65rem;">${r.action}</span></td>
            <td style="font-size:0.8125rem">${r.detail}</td>
            <td><span class="chip ${levelMap[r.level]}">${r.level}</span></td>
        </tr>`).join('');
}

// ── Activity logger ──────────────────────────────────────────
function logActivity(icon, bg, color, desc, time) {
    DB.activities.unshift({ icon, color: bg, text: color, desc, time });
    if (DB.activities.length > 20) DB.activities.pop();
}

// ── Toast Notifications ──────────────────────────────────────
function showToast(msg, type = 'success') {
    const icons = { success: 'fa-circle-check', danger: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const colors = { success: '#86efac', danger: '#fca5a5', warning: '#fcd34d', info: '#7dd3fc' };
    const t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML = `<i class="fa-solid ${icons[type]} toast-icon" style="color:${colors[type]}"></i><span>${msg}</span>`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => t.style.animation = 'toastIn 0.3s reverse forwards', 2700);
    setTimeout(() => t.remove(), 3000);
}

// ── Roles & Permissions ──────────────────────────────────────
function renderPermissions() {
    const check = '<i class="fa-solid fa-circle-check perm-check"></i>';
    const cross = '<i class="fa-solid fa-circle-xmark perm-cross"></i>';
    document.getElementById('perms-tbody').innerHTML = DB.permissions.map(p => `
        <tr>
            <td style="font-weight:600;font-size:0.875rem">${p.label}</td>
            <td class="perm-cell">${p.admin ? check : cross}</td>
            <td class="perm-cell">${p.manager ? check : cross}</td>
            <td class="perm-cell">${p.staff ? check : cross}</td>
            <td class="perm-cell">${p.viewer ? check : cross}</td>
        </tr>`).join('');
}

// ── Sessions ─────────────────────────────────────────────────
function renderSessions() {
    const tbody = document.getElementById('sessions-tbody');
    tbody.innerHTML = DB.sessions.map(s => `
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="avatar-initials" style="background:${avatarColor(s.name)};width:32px;height:32px;font-size:0.7rem">${initials(s.name)}</div>
                    <div>
                        <div style="font-weight:700;font-size:0.875rem">${s.name}</div>
                        <div style="font-size:0.75rem;color:var(--muted)">@${s.user}</div>
                    </div>
                </div>
            </td>
            <td style="font-family:'Inter',sans-serif;font-size:0.8rem;color:var(--muted)">${s.ip}</td>
            <td style="font-size:0.8rem;color:var(--muted)">${s.browser}</td>
            <td style="font-family:'Inter',sans-serif;font-size:0.8rem;color:var(--muted)">${s.loginTime}</td>
            <td style="font-size:0.8rem;font-weight:700">${s.duration}</td>
            <td><span class="live-dot"></span> <span style="font-size:0.75rem;color:#86efac;font-weight:700;margin-left:6px">Active</span></td>
            <td><button class="btn btn-danger btn-xs" onclick="killSession(${s.id})"><i class="fa-solid fa-power-off"></i> Kick</button></td>
        </tr>`).join('');
    const cnt = document.getElementById('sess-active-count');
    if (cnt) cnt.textContent = DB.sessions.length;
}
function killSession(id) {
    const s = DB.sessions.find(x => x.id === id);
    if (!s) return;
    confirmAction({
        title: 'Terminate Session',
        msg: `Are you sure you want to forcefully disconnect <b>${s.name}</b>?`,
        icon: 'fa-power-off',
        color: '#fca5a5',
        btnClass: 'btn-danger btn-danger-solid',
        btnIcon: 'fa-bolt',
        btnText: 'Terminate',
        action: () => {
            DB.sessions = DB.sessions.filter(x => x.id !== id);
            renderSessions(); renderDashboard();
            showToast(`Session for <b>${s.name}</b> terminated.`, 'warning');
            logActivity('fa-power-off', 'rgba(239,68,68,0.2)', '#fca5a5', `<b>Admin</b> force-terminated session for <b>${s.name}</b>`, 'Just now');
        }
    });
}
function killAllSessions() {
    const count = DB.sessions.length;
    if (count === 0) return;
    confirmAction({
        title: 'Terminate All Sessions',
        msg: `Are you sure you want to forcefully disconnect ALL <b>${count}</b> active users? They will lose unsaved work.`,
        icon: 'fa-bolt',
        color: '#ef4444',
        btnClass: 'btn-danger btn-danger-solid',
        btnIcon: 'fa-skull',
        btnText: 'Terminate All',
        action: () => {
            DB.sessions = [];
            renderSessions(); renderDashboard();
            showToast(`All ${count} sessions terminated.`, 'danger');
            logActivity('fa-bolt', 'rgba(239,68,68,0.2)', '#fca5a5', `<b>Admin</b> forcefully terminated all active sessions`, 'Just now');
        }
    });
}

// ── Announcements ─────────────────────────────────────────────
let nextAnnId = 3;
let editingAnnId = null;

function renderAnnouncements() {
    const list = document.getElementById('announcements-list');
    const typeIcon = { info: 'fa-circle-info', warning: 'fa-triangle-exclamation', danger: 'fa-siren-on', success: 'fa-circle-check' };
    list.innerHTML = DB.announcements.length ? DB.announcements.map(a => `
        <div class="ann-card ${a.type}">
            <div style="flex:1">
                <div class="ann-card-title">
                    <i class="fa-solid ${typeIcon[a.type] || 'fa-circle-info'}" style="margin-right:7px"></i>${a.title}
                </div>
                <div class="ann-card-body">${a.body}</div>
                <div class="ann-card-meta">
                    <span><i class="fa-solid fa-users" style="margin-right:4px"></i>Target: <b>${a.roles}</b></span>
                    &nbsp;&middot;&nbsp;
                    <span><i class="fa-regular fa-calendar" style="margin-right:4px"></i>Published: ${a.publishedAt} by @${a.publishedBy}</span>
                    ${a.expires ? `&nbsp;&middot;&nbsp;<span><i class="fa-solid fa-clock" style="margin-right:4px"></i>Expires: ${a.expires}</span>` : ''}
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0">
                <button class="btn btn-ghost btn-xs" onclick="editAnnouncement(${a.id})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-danger btn-xs" onclick="deleteAnnouncement(${a.id})"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>`).join('') :
        '<div style="text-align:center;padding:40px;color:var(--muted)"><i class="fa-solid fa-bullhorn" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:10px"></i>No announcements yet</div>';
}
function openAddAnnouncement() {
    editingAnnId = null;
    document.getElementById('modal-ann-title').textContent = 'New Announcement';
    document.getElementById('ann-title').value = '';
    document.getElementById('ann-body').value = '';
    document.getElementById('ann-type').value = 'info';
    document.getElementById('ann-roles').value = 'all';
    document.getElementById('ann-expires').value = '';
    document.getElementById('announcement-modal').classList.add('open');
}
function editAnnouncement(id) {
    const a = DB.announcements.find(x => x.id === id);
    if (!a) return;
    editingAnnId = id;
    document.getElementById('modal-ann-title').textContent = 'Edit Announcement';
    document.getElementById('ann-title').value = a.title;
    document.getElementById('ann-body').value = a.body;
    document.getElementById('ann-type').value = a.type;
    document.getElementById('ann-roles').value = a.roles;
    document.getElementById('ann-expires').value = a.expires;
    document.getElementById('announcement-modal').classList.add('open');
}
function closeAnnModal() { document.getElementById('announcement-modal').classList.remove('open'); }
function saveAnnouncement() {
    const title = document.getElementById('ann-title').value.trim();
    const body = document.getElementById('ann-body').value.trim();
    const type = document.getElementById('ann-type').value;
    const roles = document.getElementById('ann-roles').value;
    const expires = document.getElementById('ann-expires').value;
    if (!title || !body) { showToast('Title and message are required.', 'warning'); return; }

    const isEdit = !!editingAnnId;
    confirmAction({
        title: isEdit ? 'Update Announcement' : 'Publish Announcement',
        msg: isEdit ? `Save changes to "<b>${title}</b>"?` : `Are you sure you want to broadcast "<b>${title}</b>" to ${roles === 'all' ? 'everyone' : roles}?`,
        icon: 'fa-paper-plane',
        color: '#60a5fa',
        btnClass: 'btn-primary',
        btnIcon: 'fa-check',
        btnText: isEdit ? 'Save Changes' : 'Publish',
        action: () => {
            if (isEdit) {
                const a = DB.announcements.find(x => x.id === editingAnnId);
                Object.assign(a, { title, body, type, roles, expires });
                showToast(`Announcement updated.`, 'success');
            } else {
                DB.announcements.unshift({ id: nextAnnId++, title, body, type, roles, expires, publishedBy: 'Admin', publishedAt: new Date().toISOString().slice(0, 10) });
                showToast(`Announcement published.`, 'success');
                logActivity('fa-bullhorn', 'rgba(99,102,241,0.2)', '#a5b4fc', `<b>Admin</b> published announcement: <b>${title}</b>`, 'Just now');
            }
            closeAnnModal();
            renderAnnouncements();
        }
    });
}
function deleteAnnouncement(id) {
    const a = DB.announcements.find(x => x.id === id);
    if (!a) return;
    confirmAction({
        title: 'Delete Announcement',
        msg: `Are you sure you want to delete the announcement "<b>${a.title}</b>"?`,
        icon: 'fa-trash',
        color: '#fca5a5',
        btnClass: 'btn-danger btn-danger-solid',
        btnIcon: 'fa-trash',
        btnText: 'Delete',
        action: () => {
            DB.announcements = DB.announcements.filter(x => x.id !== id);
            renderAnnouncements();
            showToast('Announcement deleted.', 'danger');
        }
    });
}

// ── Settings actions ──────────────────────────────────────────
function saveSetting(key, val) {
    showToast(`Setting ${key} ${val ? 'enabled' : 'disabled'}.`, val ? 'success' : 'warning');
    logActivity('fa-sliders', 'rgba(139,92,246,0.2)', '#c4b5fd', `<b>Admin</b> changed config: <b>${key}</b>`, 'Just now');
}
function saveSettings() {
    confirmAction({
        title: 'Save Application Settings',
        msg: 'Are you sure you want to apply these global settings changes to the system?',
        icon: 'fa-sliders',
        color: '#60a5fa',
        btnClass: 'btn-primary',
        btnIcon: 'fa-check',
        btnText: 'Apply Settings',
        action: () => {
            showToast('All settings saved successfully.', 'success');
            logActivity('fa-floppy-disk', 'rgba(34,197,94,0.2)', '#86efac', '<b>Admin</b> saved application settings', 'Just now');
        }
    });
}
function testSMTP() {
    showToast('Test email sent! Check your inbox.', 'info');
}
function runBackup() {
    confirmAction({
        title: 'Run Manual Backup',
        msg: 'Are you sure you want to trigger a manual backup of the database right now?',
        icon: 'fa-cloud-arrow-up',
        color: '#7dd3fc',
        btnClass: 'btn-primary',
        btnIcon: 'fa-play',
        btnText: 'Start Backup',
        action: () => {
            showToast('Backup started… this may take a moment.', 'info');
            setTimeout(() => showToast('Backup completed successfully!', 'success'), 2000);
            logActivity('fa-database', 'rgba(56,189,248,0.2)', '#7dd3fc', '<b>Admin</b> triggered manual database backup', 'Just now');
        }
    });
}

// ── Search handlers ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Initial render
    renderDashboard();
    renderUsersTable();
    renderPagesTable();
    renderSystem();
    renderLogsTable();
    renderPermissions();
    renderSessions();
    renderAnnouncements();

    // Navigation
    document.querySelectorAll('.nav-item[data-section]').forEach(item => {
        item.addEventListener('click', () => navigate(item.dataset.section));
    });

    // User search
    document.getElementById('user-search').addEventListener('input', e => {
        renderUsersTable(e.target.value.toLowerCase());
    });

    // Pages search
    document.getElementById('pages-search')?.addEventListener('input', e => {
        renderPagesTable(e.target.value.toLowerCase());
    });

    // Mobile sidebar toggle
    document.getElementById('menu-toggle')?.addEventListener('click', () => {
        document.getElementById('admin-sidebar').classList.toggle('open');
    });

    // Global header search
    document.getElementById('global-search')?.addEventListener('input', e => {
        const v = e.target.value.trim().toLowerCase();
        if (!v) return;
        const sec = v.includes('user') || v.includes('staff') ? 'sec-users'
            : v.includes('role') || v.includes('permission') ? 'sec-roles'
                : v.includes('session') ? 'sec-sessions'
                    : v.includes('announce') || v.includes('broadcast') ? 'sec-announcements'
                        : v.includes('page') || v.includes('frontend') ? 'sec-pages'
                            : v.includes('sys') || v.includes('server') ? 'sec-system'
                                : v.includes('log') || v.includes('audit') ? 'sec-logs'
                                    : v.includes('setting') || v.includes('smtp') ? 'sec-settings'
                                        : 'sec-dashboard';
        navigate(sec);
    });

    navigate('sec-dashboard');
});
