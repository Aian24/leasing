/* ─── Lessees Management Module ─── */
const LESSEES_PAGE = {
    apiBase: '',
    state: { page: 1, limit: 25, search: '', totalRows: 0, totalPages: 0 },
    selectedFile: null,
    confirmAction: null,
    searchTimer: null,

    init() {
        console.log('Lessees Module Initializing...');
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/lessees_csv.php';

        // Breadcrumbs & Title
        safeSetText('page-title', 'Lessees Management');
        safeSetText('page-breadcrumb', 'Admin / Lessees / Manage Records');

        this.bindEvents();
        this.loadTable();
        this.loadStats();
    },

    bindEvents() {
        // Mobile menu toggle
        const menuBtn = document.getElementById('menu-toggle');
        if (menuBtn) {
            if (window.innerWidth < 900) menuBtn.style.display = 'flex';
            menuBtn.addEventListener('click', () => {
                document.getElementById('admin-sidebar')?.classList.toggle('open');
            });
        }
    },

    async loadStats() {
        try {
            const res = await fetch(`${this.apiBase}?action=list&limit=1&page=1`);
            const data = await res.json();
            if (!data.success) return;
            safeSetText('stat-total', data.total);
            if (data.stats) {
                safeSetText('stat-active', data.stats.active);
                safeSetText('stat-expiring', data.stats.expiring);
                safeSetText('stat-expired', data.stats.expired);
            }
        } catch (e) {
            console.error('Lessees Stats Error:', e);
        }
    },

    async loadTable() {
        const tbody = document.getElementById('lessee-tbody');
        if (!tbody) return;
        tbody.innerHTML = `<tr><td colspan="12"><div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading…</p></div></td></tr>`;

        const params = new URLSearchParams({
            action: 'list',
            page: this.state.page,
            limit: this.state.limit,
            search: this.state.search,
        });

        try {
            const res = await fetch(`${this.apiBase}?${params}`);
            const data = await res.json();
            if (!data.success) { showToast(data.message, 'danger'); return; }

            this.state.totalRows = data.total;
            this.state.totalPages = data.pages;

            this.renderTable(data.data);
            this.renderPagination();
            this.updateInfo();
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="12"><div class="empty-state"><i class="fa-solid fa-circle-exclamation"></i><p>Failed to load: ${e.message}</p></div></td></tr>`;
        }
    },

    renderTable(rows) {
        const tbody = document.getElementById('lessee-tbody');
        if (!tbody) return;
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="12"><div class="empty-state"><i class="fa-solid fa-inbox"></i><p>No records found. Import a CSV to get started.</p></div></td></tr>`;
            return;
        }

        const today = new Date();
        tbody.innerHTML = rows.map(r => {
            let statusBadge = '<span class="chip" style="background:rgba(148,163,184,0.15);color:#94a3b8">—</span>';
            if (r.status === 'Active' && r.lease_period_end) {
                const end = new Date(r.lease_period_end);
                const days = Math.ceil((end - today) / 86400000);
                if (days < 0) statusBadge = '<span class="chip chip-red">Expired</span>';
                else if (days <= 30) statusBadge = '<span class="chip" style="background:rgba(245,158,11,.15);color:#fcd34d">Expiring Soon</span>';
                else statusBadge = '<span class="chip chip-green">Active</span>';
            } else {
                if (r.status === 'Active') statusBadge = '<span class="chip chip-green">Active</span>';
                else if (r.status === 'Pending') statusBadge = '<span class="chip" style="background:rgba(245,158,11,.15);color:#fcd34d">Pending</span>';
                else if (r.status === 'Inactive') statusBadge = '<span class="chip" style="background:rgba(148,163,184,0.15);color:#94a3b8">Inactive</span>';
                else if (r.status === 'Terminated') statusBadge = '<span class="chip chip-red">Terminated</span>';
            }

            const leasePeriod = [r.lease_period_start, r.lease_period_end].filter(Boolean).join(' → ') || '—';
            const cleanRent = r.basic_rent ? String(r.basic_rent).replace(/[^0-9.-]+/g, "") : "";
            const rentVal = parseFloat(cleanRent);
            const rent = !isNaN(rentVal)
                ? '₱' + rentVal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                : '—';

            return `<tr>
                <td style="color:var(--muted);font-size:0.75rem">${r.id}</td>
                <td><div style="font-weight:700">${this.esc(r.company_name)}</div></td>
                <td class="wrap">${this.esc(r.trade_name || '—')}</td>
                <td>${this.esc(r.nature_of_business || '—')}</td>
                <td class="wrap">${this.esc(r.owner_lessee_name || '—')}</td>
                <td><span class="chip chip-blue">${this.esc(r.space_code || '—')}</span></td>
                <td>${r.total_area ? r.total_area + ' sqm' : '—'}</td>
                <td style="font-weight:700;color:#86efac">${rent}</td>
                <td style="font-size:0.78rem">${this.esc(leasePeriod)}</td>
                <td>${this.esc(r.email_address || '—')}</td>
                <td>${statusBadge}</td>
                <td style="display:flex; gap:6px;">
                    <button class="btn btn-ghost btn-sm"
                        style="color:#60a5fa;border-color:rgba(96,165,250,0.25)"
                        onclick='LESSEES_PAGE.openEditModal(\`${btoa(encodeURIComponent(JSON.stringify(r)))}\`)'
                        title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn btn-ghost btn-sm"
                        style="color:#fca5a5;border-color:rgba(239,68,68,0.25)"
                        onclick="LESSEES_PAGE.confirmDeleteOne(${r.id}, '${this.esc(r.company_name).replace(/'/g, "\\'")}')"
                        title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');
    },

    renderPagination() {
        const cont = document.getElementById('tbl-pagination');
        if (!cont) return;
        const { page, totalPages } = this.state;
        if (totalPages <= 1) { cont.innerHTML = ''; return; }

        let html = `<button class="page-btn" onclick="LESSEES_PAGE.goPage(${page - 1})" ${page === 1 ? 'disabled' : ''}>
                        <i class="fa-solid fa-chevron-left"></i></button>`;

        const range = this.pageRange(page, totalPages);
        range.forEach(p => {
            if (p === '…') {
                html += `<span class="page-btn" style="pointer-events:none">…</span>`;
            } else {
                html += `<button class="page-btn ${p === page ? 'active' : ''}" onclick="LESSEES_PAGE.goPage(${p})">${p}</button>`;
            }
        });

        html += `<button class="page-btn" onclick="LESSEES_PAGE.goPage(${page + 1})" ${page === totalPages ? 'disabled' : ''}>
                     <i class="fa-solid fa-chevron-right"></i></button>`;
        cont.innerHTML = html;
    },

    pageRange(cur, total) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        if (cur <= 4) return [1, 2, 3, 4, 5, '…', total];
        if (cur >= total - 3) return [1, '…', total - 4, total - 3, total - 2, total - 1, total];
        return [1, '…', cur - 1, cur, cur + 1, '…', total];
    },

    goPage(p) {
        if (p < 1 || p > this.state.totalPages) return;
        this.state.page = p;
        this.loadTable();
    },

    updateInfo() {
        const info = document.getElementById('tbl-info');
        if (!info) return;
        const { page, limit, totalRows } = this.state;
        const from = totalRows ? (page - 1) * limit + 1 : 0;
        const to = Math.min(page * limit, totalRows);
        info.textContent = totalRows ? `Showing ${from}–${to} of ${totalRows} records` : 'No records';
    },

    debounceSearch(val) {
        clearTimeout(this.searchTimer);
        this.searchTimer = setTimeout(() => {
            this.state.search = val.trim();
            this.state.page = 1;
            this.loadTable();
        }, 380);
    },

    changeLimit(val) {
        this.state.limit = parseInt(val);
        this.state.page = 1;
        this.loadTable();
    },

    esc(s) {
        if (s === null || s === undefined) return '—';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    // ── Forms & Modals ─────────────────────────────────────────
    openCreateModal() {
        document.getElementById('form-create-lessee')?.reset();
        const idField = document.getElementById('form-lessee-id');
        if (idField) idField.value = '';
        safeSetHTML('modal-form-title', '<i class="fa-solid fa-user-plus" style="color:var(--primary)"></i> Create New Lessee');
        safeSetHTML('btn-submit-create', '<i class="fa-solid fa-check"></i> Create Record');
        document.getElementById('create-overlay')?.classList.add('open');
    },

    openEditModal(base64Str) {
        try {
            const item = JSON.parse(decodeURIComponent(atob(base64Str)));
            const form = document.getElementById('form-create-lessee');
            if (!form) return;
            form.reset();

            document.getElementById('form-lessee-id').value = item.id;
            form.querySelector('input[name="company_name"]').value = item.company_name || '';
            form.querySelector('input[name="trade_name"]').value = item.trade_name || '';
            form.querySelector('input[name="space_code"]').value = item.space_code || '';
            form.querySelector('input[name="owner_lessee_name"]').value = item.owner_lessee_name || '';
            form.querySelector('input[name="total_area"]').value = item.total_area || '';
            form.querySelector('input[name="basic_rent"]').value = item.basic_rent || '';
            form.querySelector('input[name="email_address"]').value = item.email_address || '';
            form.querySelector('select[name="status"]').value = item.status || 'Active';

            safeSetHTML('modal-form-title', '<i class="fa-solid fa-pen-to-square" style="color:var(--primary)"></i> Edit Lessee');
            safeSetHTML('btn-submit-create', '<i class="fa-solid fa-save"></i> Save Changes');
            document.getElementById('create-overlay')?.classList.add('open');
        } catch (e) {
            console.error('Edit modal error:', e);
        }
    },

    closeCreateModal() {
        document.getElementById('create-overlay')?.classList.remove('open');
    },

    async submitCreateForm(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btn-submit-create');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const isEdit = !!data.id;
        const actionType = isEdit ? 'update' : 'create';

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const res = await fetch(this.apiBase + '?action=' + actionType, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message);
                this.closeCreateModal();
                this.loadTable();
                this.loadStats();
                syncSidebarBadges(); // Sync sidebar after changes
            } else {
                showToast(json.message, 'danger');
            }
        } catch (err) {
            showToast('Network Error', 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = isEdit ? '<i class="fa-solid fa-save"></i> Save Changes' : '<i class="fa-solid fa-check"></i> Create Record';
        }
    },

    confirmDeleteOne(id, name) {
        confirmAction({
            title: 'Delete Record?',
            msg: `This will permanently remove "<b>${name}</b>" (ID #${id}) from the database.`,
            icon: 'fa-trash-can',
            color: '#ef4444',
            btnClass: 'btn-danger',
            btnIcon: 'fa-trash',
            btnText: 'Delete Record',
            action: async () => {
                try {
                    const res = await fetch(`${this.apiBase}?action=delete_one`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast('Record deleted');
                        this.loadTable();
                        this.loadStats();
                        syncSidebarBadges();
                    } else showToast(data.message, 'danger');
                } catch (e) { showToast('Network Error', 'danger'); }
            }
        });
    },

    confirmDeleteAll() {
        confirmAction({
            title: 'Delete ALL Lessees?',
            msg: 'This will permanently erase <b>every</b> lessee record in the database. This action CANNOT be undone.',
            icon: 'fa-triangle-exclamation',
            color: '#ef4444',
            btnClass: 'btn-danger',
            btnIcon: 'fa-trash-can',
            btnText: 'Erase All Data',
            action: async () => {
                try {
                    const res = await fetch(`${this.apiBase}?action=delete_all`, { method: 'POST' });
                    const data = await res.json();
                    if (data.success) {
                        showToast('Database cleared');
                        this.loadTable();
                        this.loadStats();
                        syncSidebarBadges();
                    } else showToast(data.message, 'danger');
                } catch (e) { showToast('Network Error', 'danger'); }
            }
        });
    },

    // ── CSV Features ──────────────────────────────────────────
    downloadTemplate() {
        window.location.href = this.apiBase + '?action=download_template';
    },

    onFileSelected(input) {
        if (input.files[0]) this.setFile(input.files[0]);
    },

    setFile(file) {
        if (!file.name.endsWith('.csv')) { showToast('Only .csv files are accepted.', 'warning'); return; }
        this.selectedFile = file;
        const fn = document.getElementById('drop-fname');
        if (fn) {
            fn.textContent = '📄 ' + file.name + '  (' + (file.size / 1024).toFixed(1) + ' KB)';
            fn.style.display = 'block';
        }
        const upBtn = document.getElementById('btn-upload');
        if (upBtn) upBtn.disabled = false;
    },

    resetUpload() {
        this.selectedFile = null;
        const input = document.getElementById('csv-input');
        if (input) input.value = '';
        const fn = document.getElementById('drop-fname');
        if (fn) { fn.style.display = 'none'; fn.textContent = ''; }
        const upBtn = document.getElementById('btn-upload');
        if (upBtn) upBtn.disabled = true;
        const prog = document.getElementById('upload-progress');
        if (prog) prog.style.display = 'none';
        const fill = document.getElementById('upload-progress-fill');
        if (fill) fill.style.width = '0%';
        const chip = document.getElementById('upload-status-chip');
        if (chip) chip.style.display = 'none';
    },

    async uploadCSV() {
        if (!this.selectedFile) return;
        const btn = document.getElementById('btn-upload');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing…';

        const prog = document.getElementById('upload-progress');
        const fill = document.getElementById('upload-progress-fill');
        if (prog) prog.style.display = 'block';
        if (fill) fill.style.width = '15%';

        const fd = new FormData();
        fd.append('csv_file', this.selectedFile);

        try {
            if (fill) fill.style.width = '50%';
            const res = await fetch(this.apiBase + '?action=upload', { method: 'POST', body: fd });
            if (fill) fill.style.width = '90%';
            const data = await res.json();
            if (fill) fill.style.width = '100%';

            if (data.success) {
                showToast(`Success: ${data.inserted} imported`);
                this.loadTable();
                this.loadStats();
                syncSidebarBadges();
            } else showToast(data.message, 'danger');
        } catch (e) {
            showToast('Network Error', 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-upload"></i> Upload &amp; Import';
            setTimeout(() => { if (prog) prog.style.display = 'none'; if (fill) fill.style.width = '0%'; }, 1200);
        }
    },

    exportCurrentCSV() {
        const params = new URLSearchParams({ action: 'list', page: 1, limit: 9999, search: this.state.search });
        fetch(`${this.apiBase}?${params}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.data.length) { showToast('Nothing to export.', 'warning'); return; }
                const cols = Object.keys(data.data[0]).filter(c => c !== 'id');
                const header = cols.join(',');
                const rows = data.data.map(r => cols.map(c => `"${String(r[c] ?? '').replace(/"/g, '""')}"`).join(','));
                const blob = new Blob([header + '\n' + rows.join('\n')], { type: 'text/csv' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = `lessees_export_${new Date().toISOString().slice(0, 10)}.csv`;
                a.click();
            });
    }
};

// Global hooks
window.openCreateModal = () => LESSEES_PAGE.openCreateModal();
window.closeCreateModal = () => LESSEES_PAGE.closeCreateModal();
window.submitCreateForm = (e) => LESSEES_PAGE.submitCreateForm(e);
window.uploadCSV = () => LESSEES_PAGE.uploadCSV();
window.resetUpload = () => LESSEES_PAGE.resetUpload();
window.downloadTemplate = () => LESSEES_PAGE.downloadTemplate();
window.exportCurrentCSV = () => LESSEES_PAGE.exportCurrentCSV();
window.confirmDeleteAll = () => LESSEES_PAGE.confirmDeleteAll();
window.loadTable = () => LESSEES_PAGE.loadTable();
window.onFileSelected = (input) => LESSEES_PAGE.onFileSelected(input);
window.debounceSearch = (val) => LESSEES_PAGE.debounceSearch(val);
window.changeLimit = (val) => LESSEES_PAGE.changeLimit(val);
window.goPage = (p) => LESSEES_PAGE.goPage(p);

document.addEventListener('DOMContentLoaded', () => LESSEES_PAGE.init());
