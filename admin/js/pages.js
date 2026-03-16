const PAGES_PAGE = {
    apiBase: '',

    init() {
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/pages_api.php';

        safeSetText('page-title', 'Frontend Pages');
        safeSetText('page-breadcrumb', 'Admin / Content / Pages');

        this.loadPages();
    },

    async loadPages() {
        const tbody = document.getElementById('pages-tbody');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px">Checking registry...</td></tr>';

        try {
            const res = await fetch(`${this.apiBase}?action=list`);
            const data = await res.json();
            if (data.success) {
                this.renderTable(data.data);
                this.updateStats(data.stats);
            }
        } catch (e) { console.error(e); }
    },

    updateStats(stats) {
        safeSetText('pages-live-count', stats.live);
        safeSetText('pages-hidden-count', stats.hidden);
        safeSetText('pages-total-count', stats.total);
    },

    renderTable(rows) {
        const tbody = document.getElementById('pages-tbody');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px">No pages registered.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(p => `
            <tr>
                <td style="font-weight:700; color:#fff">${this.esc(p.page_name)}</td>
                <td style="color:var(--primary)">/${this.esc(p.slug)}</td>
                <td>
                    <span class="chip ${p.is_visible ? 'chip-green' : 'chip-red'}">
                        ${p.is_visible ? 'Visible' : 'Hidden'}
                    </span>
                </td>
                <td style="font-size:0.8rem">${new Date(p.updated_at).toLocaleDateString()}</td>
                <td style="font-size:0.8rem">${this.esc(p.editor_name || 'System')}</td>
                <td style="text-align:right">
                    <div style="display:flex; gap:8px; justify-content:flex-end">
                        <button class="btn btn-ghost btn-sm" onclick="PAGES_PAGE.toggleVisibility(${p.id})" title="Toggle Visibility">
                            <i class="fa-solid ${p.is_visible ? 'fa-eye-slash' : 'fa-eye'}"></i>
                        </button>
                        <button class="btn btn-ghost btn-sm" style="color:#ef4444" onclick="PAGES_PAGE.confirmDelete(${p.id})" title="Delete Registry">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    async toggleVisibility(id) {
        try {
            const res = await fetch(`${this.apiBase}?action=toggle`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Visibility toggled');
                this.loadPages();
            }
        } catch (e) { showToast('Error toggling', 'danger'); }
    },

    confirmDelete(id) {
        confirmAction({
            title: 'Delete Registry?',
            msg: 'This will remove the page from the management list. The actual file will not be deleted.',
            icon: 'fa-trash',
            btnText: 'Remove Registry',
            btnClass: 'btn-danger',
            action: async () => {
                await fetch(`${this.apiBase}?action=delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                showToast('Page removed');
                this.loadPages();
            }
        });
    },

    esc(s) {
        if (!s) return '—';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
};

document.addEventListener('DOMContentLoaded', () => PAGES_PAGE.init());
