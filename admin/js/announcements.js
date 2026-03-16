const ANNOUNCEMENTS_PAGE = {
    apiBase: '',

    init() {
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/announcements_api.php';

        safeSetText('page-title', 'Announcements');
        safeSetText('page-breadcrumb', 'Admin / Global / Broadcasts');

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
        const list = document.getElementById('announcements-list');
        if (!list) return;

        if (!items.length) {
            list.innerHTML = '<div class="empty-state"><p>No announcements found.</p></div>';
            return;
        }

        list.innerHTML = items.map(a => `
            <div class="panel" style="margin-bottom:0; border-left:4px solid var(--${a.type || 'primary'})">
                <div class="panel-body" style="padding:15px">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start">
                        <div>
                            <div style="font-weight:700; color:#fff; font-size:1rem; margin-bottom:5px">${this.esc(a.title)}</div>
                            <div style="font-size:0.875rem; color:var(--muted); line-height:1.5">${this.esc(a.content)}</div>
                            <div style="font-size:0.75rem; color:var(--muted); margin-top:10px">
                                <i class="fa-solid fa-user-circle"></i> ${this.esc(a.creator_name)} • 
                                <i class="fa-solid fa-calendar"></i> ${new Date(a.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        <div style="display:flex; gap:8px">
                            <button class="btn btn-ghost btn-sm" onclick="ANNOUNCEMENTS_PAGE.openEditModal(${JSON.stringify(a).replace(/'/g, "&apos;")})">
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

    openAddModal() {
        document.getElementById('ann-id').value = '';
        document.getElementById('ann-form').reset();
        safeSetText('ann-modal-title', 'New Broadcast');
        document.getElementById('ann-overlay').classList.add('open');
    },

    openEditModal(a) {
        document.getElementById('ann-id').value = a.id;
        document.getElementById('ann-title').value = a.title;
        document.getElementById('ann-content').value = a.content;
        document.getElementById('ann-type').value = a.type;
        safeSetText('ann-modal-title', 'Edit Broadcast');
        document.getElementById('ann-overlay').classList.add('open');
    },

    closeModal() {
        document.getElementById('ann-overlay').classList.remove('open');
    },

    async saveAnnouncement(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const res = await fetch(`${this.apiBase}?action=save`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const resData = await res.json();
            if (resData.success) {
                showToast('Broadcast saved');
                this.closeModal();
                this.loadAnnouncements();
            }
        } catch (e) { showToast('Error saving', 'danger'); }
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
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
};

document.addEventListener('DOMContentLoaded', () => ANNOUNCEMENTS_PAGE.init());
