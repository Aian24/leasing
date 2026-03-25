const ANNOUNCEMENTS_PAGE = {
    apiBase: '',

    init() {
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/announcements_api.php';

        safeSetText('page-title', 'Announcements');
        safeSetText('page-breadcrumb', 'Admin / Global / Broadcasts');
        window.ENABLE_ANNOUNCEMENTS = true;
        this.loadAnnouncements();
    },

    async loadAnnouncements() {
        const list = document.getElementById('announcements-list');
        if (!list) return;

        list.innerHTML = '<div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading broadcasts...</p></div>';

        try {
            const res = await fetch(`${this.apiBase}?action=list`);
            const data = await res.json();
            if (data.success) {
                this.renderList(data.data);
            }
        } catch (e) { console.error(e); }
    },

    renderList(items) {
        this.cache = items; // Store for editing
        const list = document.getElementById('announcements-list');
        if (!list) return;

        if (!items.length) {
            list.innerHTML = '<div class="empty-state"><p>No announcements found.</p></div>';
            return;
        }

        list.innerHTML = items.map(a => `
            <div class="panel" style="margin-bottom:0; border-left:4px solid var(--${a.type || 'primary'}); ${a.is_active ? '' : 'opacity:0.6; filter:grayscale(0.5)'}">
                <div class="panel-body" style="padding:15px">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start">
                        <div style="flex:1">
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:5px">
                                <div style="font-weight:700; color:#fff; font-size:1rem">${this.esc(a.title)}</div>
                                ${a.is_active ? '' : '<span style="font-size:10px; background:rgba(255,255,255,0.1); padding:2px 6px; border-radius:4px; color:var(--muted)">INACTIVE</span>'}
                            </div>
                            <div style="font-size:0.875rem; color:var(--muted); line-height:1.5">${this.esc(a.content)}</div>
                            <div style="font-size:0.75rem; color:var(--muted); margin-top:10px">
                                <i class="fa-solid fa-user-circle"></i> ${this.esc(a.creator_name)} • 
                                <i class="fa-solid fa-calendar"></i> ${new Date(a.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px">
                            <label class="switch">
                                <input type="checkbox" ${a.is_active ? 'checked' : ''} onchange="ANNOUNCEMENTS_PAGE.toggleAnnouncement(${a.id}, this.checked)">
                                <span class="slider round"></span>
                            </label>
                            <div style="width:1px; height:24px; background:rgba(255,255,255,0.1)"></div>
                            <button class="btn btn-ghost btn-sm" onclick="ANNOUNCEMENTS_PAGE.openEditModal(${a.id})">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn btn-ghost btn-sm" style="color:#ef4444" onclick="ANNOUNCEMENTS_PAGE.confirmDelete(${a.id})">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    },

    async toggleAnnouncement(id, state) {
        try {
            const res = await fetch(`${this.apiBase}?action=toggle_status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, is_active: state ? 1 : 0 })
            });
            const data = await res.json();
            if (data.success) {
                showToast(state ? 'Announcement activated' : 'Announcement deactivated');
                this.loadAnnouncements(); // Refresh to update dimming
            }
        } catch (e) { showToast('Error toggling', 'danger'); }
    },

    openEditModal(id) {
        const a = this.cache.find(item => item.id == id);
        if (!a) return;
        document.getElementById('ann-id').value = a.id;
        document.getElementById('ann-title').value = a.title;
        document.getElementById('ann-content').value = a.content;
        document.getElementById('ann-type').value = a.type;
        document.getElementById('ann-is-active').checked = a.is_active == 1;
        safeSetText('ann-modal-title', 'Edit Broadcast');
        document.getElementById('ann-overlay').classList.add('open');
    },

    openAddModal() {
        document.getElementById('ann-id').value = '';
        document.getElementById('ann-form').reset();
        document.getElementById('ann-is-active').checked = true;
        safeSetText('ann-modal-title', 'New Broadcast');
        document.getElementById('ann-overlay').classList.add('open');
    },

    closeModal() {
        document.getElementById('ann-overlay').classList.remove('open');
    },

    async saveAnnouncement(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        // Handle checkbox since it's not in FormData if unchecked
        data.is_active = document.getElementById('ann-is-active').checked ? 1 : 0;

        // Ensure ID is null if empty for new broadcasts
        if (!data.id) delete data.id;

        try {
            const res = await fetch(`${this.apiBase}?action=save`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const resData = await res.json();
            if (resData.success) {
                showToast(data.id ? 'Broadcast updated' : 'Broadcast published');
                this.closeModal();
                this.loadAnnouncements();
            }
        } catch (e) { showToast('Error saving', 'danger'); }
    },

    async toggleSystem(enabled) {
        try {
            const systemApi = this.apiBase.replace('announcements_api.php', 'system_api.php');
            const data = { enable_announcements: enabled ? 'true' : 'false' };
            const res = await fetch(`${systemApi}?action=settings_update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const resData = await res.json();
            if (resData.success) {
                showToast(`Announcements ${enabled ? 'enabled' : 'disabled'}`);
            }
        } catch (e) { showToast('Error updating setting', 'danger'); }
    },

    confirmDelete(id) {
        confirmAction({
            title: 'Delete Announcement?',
            msg: 'This message will be removed from all user dashboards.',
            icon: 'fa-trash',
            btnText: 'Delete Now',
            btnClass: 'btn-danger',
            action: async () => {
                await fetch(`${this.apiBase}?action=delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                showToast('Announcement deleted');
                this.loadAnnouncements();
            }
        });
    },

    esc(s) {
        if (!s) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/`/g, '&#96;');
    }
};

document.addEventListener('DOMContentLoaded', () => ANNOUNCEMENTS_PAGE.init());
