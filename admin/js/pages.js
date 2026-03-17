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
                this.pages = data.data;
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

        tbody.innerHTML = rows.map(p => {
            const isVisible = Number(p.is_visible) === 1;
            return `
                <tr>
                    <td>
                        <div style="font-weight:700; color:#fff">${this.esc(p.page_name)}</div>
                        <div style="font-size:0.65rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em; margin-top:2px">${p.type}</div>
                    </td>
                    <td style="color:var(--primary)">/${this.esc(p.slug)}</td>
                    <td>
                        <span class="chip ${isVisible ? 'chip-green' : 'chip-red'}">
                            ${isVisible ? 'Visible' : 'Hidden'}
                        </span>
                    </td>
                    <td style="font-size:0.8rem">${new Date(p.updated_at).toLocaleDateString()}</td>
                    <td style="font-size:0.8rem">${this.esc(p.editor_name || 'System')}</td>
                    <td style="text-align:right">
                        <div style="display:flex; gap:8px; justify-content:flex-end">
                            <button class="btn btn-ghost btn-sm" onclick="PAGES_PAGE.openEditModal(${p.id})" title="Edit Content">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-ghost btn-sm" onclick="PAGES_PAGE.toggleVisibility(${p.id})" title="Toggle Visibility">
                                <i class="fa-solid ${isVisible ? 'fa-eye-slash' : 'fa-eye'}"></i>
                            </button>
                            <button class="btn btn-ghost btn-sm" style="color:#ef4444" onclick="PAGES_PAGE.confirmDelete(${p.id})" title="Delete Page">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    },

    // ── Modal & Form ──
    openAddModal() {
        const form = document.getElementById('page-form');
        form.reset();
        document.getElementById('page-id').value = '';
        document.getElementById('page-modal-title').textContent = 'Create New Page';
        document.getElementById('page-overlay').classList.add('open');
    },

    openEditModal(id) {
        const p = this.pages.find(item => item.id == id);
        if (!p) return;

        document.getElementById('page-id').value = p.id;
        document.getElementById('page-name').value = p.page_name;
        document.getElementById('page-slug').value = p.slug;
        document.getElementById('page-type').value = p.type;
        document.getElementById('page-content').value = p.content || '';
        document.getElementById('page-visible').checked = Number(p.is_visible) === 1;

        document.getElementById('page-modal-title').textContent = 'Edit Page Content';
        document.getElementById('page-overlay').classList.add('open');
    },

    closeModal() {
        document.getElementById('page-overlay').classList.remove('open');
    },

    async savePage(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const res = await fetch(`${this.apiBase}?action=save`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message);
                this.closeModal();
                this.loadPages();
            } else {
                showToast(json.message, 'danger');
            }
        } catch (err) { showToast('Error saving page', 'danger'); }
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
                const statusText = data.new_status === 1 ? 'is now visible' : 'is now hidden';
                showToast(`Page ${statusText}`);
                this.loadPages();
            }
        } catch (e) {
            console.error(e);
            showToast('Error toggling visibility', 'danger');
        }
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
