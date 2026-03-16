/* ─── Global Admin UI Controller ─── */
const GLOBAL_UI = {
    apiBase: '',

    init() {
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/admin_api.php';

        this.initSearch();
        this.initNotifications();
        this.initProfile();
        this.syncSidebarUser();
        syncSidebarBadges();
    },

    async syncSidebarUser() {
        try {
            const res = await fetch(`${this.apiBase}?action=get_profile`);
            const data = await res.json();
            if (data.success) {
                const user = data.data;
                const path = window.location.pathname;
                const root = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';

                const nameEl = document.getElementById('sidebar-user-name');
                const roleEl = document.getElementById('sidebar-user-role');
                const avatarEl = document.getElementById('sidebar-user-avatar');

                if (nameEl) nameEl.textContent = user.name;
                if (roleEl) roleEl.textContent = user.role;
                if (avatarEl) {
                    avatarEl.src = user.avatar ? (root + '/' + user.avatar) : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=4f46e5&color=fff&rounded=true`;
                }
            }
        } catch (e) { console.error('Sidebar user sync failed', e); }
    },

    // 🔍 Global Search Logic
    initSearch() {
        const input = document.getElementById('global-search');
        if (!input) return;

        input.addEventListener('input', async (e) => {
            const query = e.target.value.trim();
            const results = document.getElementById('search-results');
            if (query.length < 2) {
                results.classList.remove('open');
                return;
            }

            try {
                const res = await fetch(`${this.apiBase}?action=search&q=${encodeURIComponent(query)}`);
                const data = await res.json();
                this.renderSearchResults(data.data);
            } catch (e) { console.error(e); }
        });

        // Hide search on click outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.header-search')) {
                document.getElementById('search-results')?.classList.remove('open');
            }
        });
    },

    renderSearchResults(items) {
        const container = document.getElementById('search-results');
        if (!container) return;

        if (!items.length) {
            container.innerHTML = '<div class="search-item empty">No matches found</div>';
        } else {
            container.innerHTML = items.map(item => `
                <div class="search-item" onclick="GLOBAL_UI.navigateTo('${item.type}', '${item.slug || item.id}')">
                    <div class="search-title">${this.esc(item.title)}</div>
                    <div class="search-category">${item.type}</div>
                </div>
            `).join('');
        }
        container.classList.add('open');
    },

    navigateTo(type, val) {
        const path = window.location.pathname;
        const root = path.includes('/overview/') || path.includes('/management/') || path.includes('/system/') || path.includes('/content/') ? '../' : './';

        if (type === 'Page') {
            window.location.href = root + (val.includes('/') ? val : val); // Simple for now
        } else if (type === 'Lessee') {
            window.location.href = root + 'management/lessees.php?id=' + val;
        }
    },

    // 🔔 Notifications Logic
    initNotifications() {
        const trigger = document.getElementById('notif-trigger');
        if (!trigger) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('notif-dropdown')?.classList.toggle('open');
            this.loadNotifications();
        });

        document.addEventListener('click', () => {
            document.getElementById('notif-dropdown')?.classList.remove('open');
        });

        // Initial unread check
        this.loadNotifications(true);
    },

    async loadNotifications(onlyCount = false) {
        try {
            const res = await fetch(`${this.apiBase}?action=get_notifications`);
            const data = await res.json();
            if (data.success) {
                const dot = document.getElementById('notif-dot');
                if (dot) dot.style.display = data.unread_count > 0 ? 'block' : 'none';

                if (!onlyCount) this.renderNotifications(data.data);
            }
        } catch (e) { console.error(e); }
    },

    renderNotifications(items) {
        const list = document.getElementById('notif-list');
        if (!list) return;

        if (!items.length) {
            list.innerHTML = '<div class="empty">No new messages</div>';
            return;
        }

        list.innerHTML = items.map(n => `
            <div class="notif-item ${n.is_read ? '' : 'unread'}">
                <div class="notif-icon ${n.type}"><i class="fa-solid ${this.getNotifIcon(n.type)}"></i></div>
                <div class="notif-body">
                    <div class="notif-title">${this.esc(n.title)}</div>
                    <div class="notif-text">${this.esc(n.message)}</div>
                    <div class="notif-time">${this.timeAgo(n.created_at)}</div>
                </div>
            </div>
        `).join('');
    },

    async markAllRead() {
        try {
            const res = await fetch(`${this.apiBase}?action=mark_as_read`);
            const data = await res.json();
            if (data.success) {
                this.loadNotifications();
                showToast('All notifications marked as read');
            }
        } catch (e) { console.error(e); }
    },

    getNotifIcon(type) {
        switch (type) {
            case 'success': return 'fa-circle-check';
            case 'warning': return 'fa-triangle-exclamation';
            case 'danger': return 'fa-circle-exclamation';
            default: return 'fa-circle-info';
        }
    },

    // 👤 Profile Settings Logic
    initProfile() {
        const userChip = document.querySelector('.user-chip');
        if (!userChip) return;

        userChip.style.cursor = 'pointer';
        userChip.addEventListener('click', () => this.openProfileModal());
    },

    async openProfileModal() {
        try {
            const res = await fetch(`${this.apiBase}?action=get_profile`);
            const data = await res.json();
            if (data.success) {
                const user = data.data;
                const path = window.location.pathname;
                const root = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
                const avatarUrl = user.avatar ? (root + '/' + user.avatar) : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=4f46e5&color=fff&rounded=true`;

                confirmAction({
                    title: 'My Account Settings',
                    msg: `
                        <div class="profile-settings-ui">
                            <!-- Avatar Section -->
                            <div class="profile-avatar-row">
                                <div class="avatar-edit-wrapper">
                                    <img src="${avatarUrl}" id="prof-avatar-preview" class="prof-large-avatar">
                                    <label class="avatar-upload-btn" title="Change Photo">
                                        <i class="fa-solid fa-camera"></i>
                                        <input type="file" id="prof-avatar-input" hidden accept="image/*" onchange="GLOBAL_UI.handleAvatarUpload(this)">
                                    </label>
                                </div>
                                <div class="prof-meta">
                                    <div class="prof-name-display">${this.esc(user.name)}</div>
                                    <div class="prof-role-badge">${user.role}</div>
                                </div>
                            </div>

                            <div class="prof-tabs-nav">
                                <button class="prof-tab active" onclick="GLOBAL_UI.switchProfTab(this, 'prof-tab-basic')">Basic Info</button>
                                <button class="prof-tab" onclick="GLOBAL_UI.switchProfTab(this, 'prof-tab-security')">Security</button>
                            </div>

                            <!-- Basic Info Tab -->
                            <div id="prof-tab-basic" class="prof-tab-content active">
                                <div class="grid-2">
                                    <div class="form-group">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" id="prof-name" class="form-control" value="${this.esc(user.name)}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" id="prof-phone" class="form-control" value="${this.esc(user.phone)}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" id="prof-email" class="form-control" value="${this.esc(user.email)}">
                                </div>
                                <div class="prof-info-box">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>Workplace: <b>${user.department}</b> — ${user.position}</span>
                                </div>
                            </div>

                            <!-- Security Tab -->
                            <div id="prof-tab-security" class="prof-tab-content">
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" id="prof-curr-pass" class="form-control" placeholder="Required for security changes">
                                </div>
                                <hr style="border:none; border-top:1px solid rgba(255,255,255,0.05); margin:15px 0">
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" id="prof-new-pass" class="form-control" placeholder="Min. 8 characters">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" id="prof-conf-pass" class="form-control" placeholder="Repeat new password">
                                </div>
                            </div>
                        </div>
                    `,
                    icon: 'fa-user-gear',
                    color: 'var(--primary)',
                    btnClass: 'btn-primary',
                    btnText: 'Update Profile',
                    action: () => this.saveProfile()
                });
            }
        } catch (e) { console.error(e); }
    },

    switchProfTab(btn, tabId) {
        document.querySelectorAll('.prof-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.prof-tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    },

    async handleAvatarUpload(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const formData = new FormData();
        formData.append('avatar', file);

        try {
            const res = await fetch(`${this.apiBase}?action=upload_avatar`, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                const path = window.location.pathname;
                const root = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
                document.getElementById('prof-avatar-preview').src = root + '/' + data.avatar;
                showToast('Avatar uploaded successfully');
                const sidebarImg = document.querySelector('.user-chip img');
                if (sidebarImg) sidebarImg.src = root + '/' + data.avatar;
            } else {
                showToast(data.message, 'danger');
            }
        } catch (e) { showToast('Upload failed', 'danger'); }
    },

    async saveProfile() {
        const newPass = document.getElementById('prof-new-pass').value;
        const confPass = document.getElementById('prof-conf-pass').value;
        const currPass = document.getElementById('prof-curr-pass').value;

        if (newPass && newPass !== confPass) {
            showToast('New passwords do not match', 'warning');
            return;
        }

        if (newPass && !currPass) {
            showToast('Valid current password is required for changes', 'warning');
            return;
        }

        const data = {
            name: document.getElementById('prof-name').value,
            email: document.getElementById('prof-email').value,
            phone: document.getElementById('prof-phone').value,
            curr_password: currPass,
            new_password: newPass
        };

        try {
            const res = await fetch(`${this.apiBase}?action=update_profile`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const resData = await res.json();
            if (resData.success) {
                showToast('Profile updated successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(resData.message, 'danger');
            }
        } catch (e) { showToast('Error updating profile', 'danger'); }
    },

    // Helpers
    timeAgo(date) {
        const seconds = Math.floor((new Date() - new Date(date)) / 1000);
        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + " years ago";
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + " months ago";
        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + " days ago";
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + " hours ago";
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + " mins ago";
        return "Just now";
    },

    esc(s) {
        if (!s) return '—';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
};

/* ─── Global Admin UI Helpers ─── */
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
function confirmAction({ title, msg, icon, color, btnClass, btnIcon, btnText, action }) {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return action();
    const t = document.getElementById('confirm-title');
    if (t) t.style.color = color;
    safeSetText('confirm-title-text', title);
    const i = document.getElementById('confirm-icon');
    if (i) i.className = `fa-solid ${icon}`;
    safeSetHTML('confirm-msg', msg);
    const okBtn = document.getElementById('confirm-ok-btn');
    if (okBtn) {
        okBtn.className = `btn ${btnClass}`;
        okBtn.innerHTML = `<i class="fa-solid ${btnIcon || 'fa-check'}" style="margin-right:6px"></i> ${btnText || 'Confirm'}`;
        okBtn.onclick = () => { closeConfirm(); action(); };
    }
    modal.classList.add('open');
}
function closeConfirm() { document.getElementById('confirm-modal')?.classList.remove('open'); }

async function syncSidebarBadges() {
    try {
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        const apiBase = base + '/api/dashboard_api.php';
        const res = await fetch(`${apiBase}?action=stats`);
        const json = await res.json();
        if (json.success) {
            safeSetText('nav-badge-users', json.users.total);
            safeSetText('nav-count', json.lessees.total);
            safeSetText('nav-badge-logs', json.logs.total);
            safeSetText('nav-badge-sessions', json.sessions.online);
        }
    } catch (e) { }
}

document.addEventListener('DOMContentLoaded', () => GLOBAL_UI.init());
