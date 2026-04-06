/* ── System Info & Settings Module ── */
const SYSTEM_PAGE = {
    apiBase: '',

    currentPage: 1,
    currentLimit: 25,
    currentSearch: '',

    init() {
        console.log('System Module Initializing...');
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/system_api.php';

        // Fix header titles
        if (document.getElementById('sys-info-grid')) {
            safeSetText('page-title', 'System Information');
            safeSetText('page-breadcrumb', 'Admin / System');
        } else if (document.getElementById('logs-tbody')) {
            safeSetText('page-title', 'Audit Logs');
            safeSetText('page-breadcrumb', 'Admin / System');

            // Sync initial limit from UI
            const limitSel = document.getElementById('log-limit');
            if (limitSel) this.currentLimit = parseInt(limitSel.value);
        } else if (document.getElementById('settings-list')) {
            safeSetText('page-title', 'App Settings');
            safeSetText('page-breadcrumb', 'Admin / System');
        }

        if (document.getElementById('sys-info-grid')) this.loadInfo();
        if (document.getElementById('logs-tbody')) this.loadLogs();
        if (document.getElementById('settings-list')) this.loadSettings();
    },

    async loadInfo() {
        safeSetHTML('sys-info-grid', '<div style="grid-column:1/-1; text-align:center; padding:40px"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>');
        try {
            const res = await fetch(`${this.apiBase}?action=info`);
            const data = await res.json();
            if (data.success) {
                const info = data.data;
                safeSetHTML('sys-info-grid', `
                    ${this.renderInfoItem('fa-server', 'Server Software', info.server_software)}
                    ${this.renderInfoItem('fa-code', 'PHP Version', info.php_version)}
                    ${this.renderInfoItem('fa-database', 'MySQL Version', info.db_version)}
                    ${this.renderInfoItem('fa-microchip', 'OS Platform', info.os)}
                    ${this.renderInfoItem('fa-upload', 'Max Upload Size', info.max_upload)}
                    ${this.renderInfoItem('fa-bolt', 'Max Exec Time', info.max_execution)}
                    ${this.renderInfoItem('fa-memory', 'PHP Memory Limit', info.memory_limit)}
                    ${this.renderInfoItem('fa-clock', 'Server Time', info.server_time)}
                `);
            }
        } catch (e) { console.error(e); }
    },

    renderInfoItem(icon, label, val) {
        return `
            <div class="panel" style="margin-bottom:0">
                <div class="panel-body" style="display:flex; align-items:center; gap:15px">
                    <div style="width:40px; height:40px; border-radius:10px; background:rgba(96, 165, 250, 0.1); display:flex; align-items:center; justify-content:center; color:#60a5fa">
                        <i class="fa-solid ${icon}"></i>
                    </div>
                    <div>
                        <div style="font-size:0.7rem; color:var(--muted); font-weight:700; text-transform:uppercase">${label}</div>
                        <div style="font-size:1rem; color:#fff; font-weight:700">${val}</div>
                    </div>
                </div>
            </div>
        `;
    },

    async loadLogs() {
        const tbody = document.getElementById('logs-tbody');
        const infoEl = document.getElementById('log-info');
        const pagEl = document.getElementById('log-pagination');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px"><i class="fa-solid fa-spinner fa-spin"></i> Fetching records...</td></tr>';

        try {
            const params = new URLSearchParams({
                action: 'logs',
                page: this.currentPage,
                limit: this.currentLimit,
                search: this.currentSearch
            });

            const res = await fetch(`${this.apiBase}?${params.toString()}`);
            const data = await res.json();

            if (data.success) {
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px">No logs found matching your criteria.</td></tr>';
                    infoEl.innerText = 'Showing 0 to 0 of 0 entries';
                    pagEl.innerHTML = '';
                    return;
                }

                tbody.innerHTML = data.data.map(l => `
                    <tr>
                        <td><span style="font-family:monospace; font-size:0.75rem">${GLOBAL_UI.formatDateTime(l.created_at)}</span></td>
                        <td><div style="font-weight:700; color:#fff">${this.esc(l.username)}</div></td>
                        <td><span class="chip" style="background:rgba(59, 130, 246, 0.1); color:#a5b4fc">${this.esc(l.action)}</span></td>
                        <td style="font-size:0.8rem">${this.esc(l.detail)}</td>
                        <td><span class="chip chip-${l.level}">${l.level}</span></td>
                        <td style="font-family:monospace; font-size:0.75rem; color:var(--muted)">${l.ip_address}</td>
                    </tr>
                `).join('');

                const p = data.pagination;
                const start = ((p.page - 1) * p.limit) + 1;
                const end = Math.min(p.page * p.limit, p.total);
                infoEl.innerText = `Showing ${start} to ${end} of ${p.total} entries`;

                this.renderPagination(p.page, p.pages);

            } else {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:40px;color:#fca5a5">${data.message || 'Error loading logs'}</td></tr>`;
            }
        } catch (e) {
            console.error(e);
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:#fca5a5">Connection Error. Check console for details.</td></tr>';
        }
    },

    renderPagination(current, total) {
        const el = document.getElementById('log-pagination');
        if (!el) return;
        let html = '';

        // Prev
        html += `<button class="page-btn" ${current === 1 ? 'disabled' : ''} onclick="SYSTEM_PAGE.changePage(${current - 1})"><i class="fa-solid fa-angle-left"></i></button>`;

        // Pages
        let start = Math.max(1, current - 2);
        let end = Math.min(total, start + 4);
        if (end - start < 4) start = Math.max(1, end - 4);

        for (let i = start; i <= end; i++) {
            html += `<button class="page-btn ${i === current ? 'active' : ''}" onclick="SYSTEM_PAGE.changePage(${i})">${i}</button>`;
        }

        // Next
        html += `<button class="page-btn" ${current === total ? 'disabled' : ''} onclick="SYSTEM_PAGE.changePage(${current + 1})"><i class="fa-solid fa-angle-right"></i></button>`;

        el.innerHTML = html;
    },

    changeLimit(val) {
        this.currentLimit = val;
        this.currentPage = 1;
        this.loadLogs();
    },

    changePage(page) {
        this.currentPage = page;
        this.loadLogs();
    },

    debounceSearch(val) {
        clearTimeout(this.searchTimer);
        this.searchTimer = setTimeout(() => {
            this.currentSearch = val;
            this.currentPage = 1;
            this.loadLogs();
        }, 400);
    },

    async loadSettings() {
        const list = document.getElementById('settings-list');
        if (!list) return;
        list.innerHTML = '<div style="text-align:center; padding:40px"><i class="fa-solid fa-spinner fa-spin"></i> Loading settings...</div>';

        try {
            const res = await fetch(`${this.apiBase}?action=settings_list`);
            const data = await res.json();
            if (data.success) {
                const order = ['app_name', 'app_tagline', 'maintenance_mode', 'session_timeout', 'date_format', 'time_format'];
                const sortedData = data.data
                    .filter(s => s.setting_key !== 'currency' && !s.setting_key.startsWith('leasing_')) // Hide currency and signatures
                    .sort((a, b) => {
                        let indexA = order.indexOf(a.setting_key);
                        let indexB = order.indexOf(b.setting_key);
                        if (indexA === -1) indexA = 99;
                        if (indexB === -1) indexB = 99;
                        return indexA - indexB;
                    });

                list.innerHTML = sortedData.map(s => `
                    <div class="settings-row">
                        <div class="settings-info">
                            <label class="settings-label">${this.formatKey(s.setting_key)}</label>
                            <p class="settings-desc">${this.esc(s.description)}</p>
                        </div>
                        <div class="settings-action">
                            ${this.renderInput(s)}
                        </div>
                    </div>
                `).join('');
            }
        } catch (e) { console.error(e); }
    },

    formatKey(key) {
        return key.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    },

    renderInput(s) {
        if (s.setting_value === 'true' || s.setting_value === 'false') {
            const checked = s.setting_value === 'true' ? 'checked' : '';
            return `<label class="switch">
                        <input type="checkbox" name="${s.setting_key}" ${checked} onchange="SYSTEM_PAGE.updateSingleSetting('${s.setting_key}', this.checked ? 'true' : 'false')">
                        <span class="slider round"></span>
                    </label>`;
        }

        if (s.setting_key === 'date_format') {
            const options = [
                { val: 'M j, Y', label: 'Mar 17, 2026' },
                { val: 'F j, Y', label: 'March 17, 2026' },
                { val: 'm/d/Y', label: '03/17/2026' },
                { val: 'd/m/Y', label: '17/03/2026' },
                { val: 'Y-m-d', label: '2026-03-17' }
            ];
            return `<select class="form-control" onchange="SYSTEM_PAGE.updateSingleSetting('${s.setting_key}', this.value)">
                        ${options.map(opt => `<option value="${opt.val}" ${s.setting_value === opt.val ? 'selected' : ''}>${opt.label}</option>`).join('')}
                    </select>`;
        }

        if (s.setting_key === 'time_format') {
            const options = [
                { val: 'h:i A', label: '12-Hour (02:30 PM)' },
                { val: 'g:i a', label: '12-Hour Short (2:30 pm)' },
                { val: 'H:i', label: '24-Hour (14:30)' },
                { val: 'H:i:s', label: '24-Hour with Seconds (14:30:05)' }
            ];
            return `<select class="form-control" onchange="SYSTEM_PAGE.updateSingleSetting('${s.setting_key}', this.value)">
                        ${options.map(opt => `<option value="${opt.val}" ${s.setting_value === opt.val ? 'selected' : ''}>${opt.label}</option>`).join('')}
                    </select>`;
        }

        return `<input type="text" class="form-control" value="${this.esc(s.setting_value)}" 
                onchange="SYSTEM_PAGE.updateSingleSetting('${s.setting_key}', this.value)">`;
    },

    async updateSingleSetting(key, val) {
        try {
            const res = await fetch(`${this.apiBase}?action=settings_update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ [key]: val })
            });
            const data = await res.json();
            if (data.success) {
                showToast(`Setting saved: ${this.formatKey(key)}`);
                if (typeof GLOBAL_UI !== 'undefined') {
                    GLOBAL_UI.settings[key] = val;
                    // Proactively refresh logs if we are on the logs page to show new format
                    if (document.getElementById('logs-tbody')) this.loadLogs();
                    // Or info grid if time changed
                    if (document.getElementById('sys-info-grid')) this.loadInfo();

                    // Refresh branding if relevant settings changed
                    if (key === 'app_name' || key === 'app_tagline') GLOBAL_UI.applyBrandSettings();
                }
            } else {
                showToast(data.message || 'Error saving', 'danger');
            }
        } catch (e) { showToast('Error saving', 'danger'); }
    },

    esc(s) {
        if (!s) return '—';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
};

document.addEventListener('DOMContentLoaded', () => SYSTEM_PAGE.init());
