/* ── System Info & Settings Module ── */
const SYSTEM_PAGE = {
    apiBase: '',

    init() {
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/system_api.php';

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
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px">Loading logs...</td></tr>';

        try {
            const res = await fetch(`${this.apiBase}?action=logs`);
            const data = await res.json();
            if (data.success) {
                tbody.innerHTML = data.data.map(l => `
                    <tr>
                        <td><span style="font-family:monospace; font-size:0.75rem">${new Date(l.created_at).toLocaleString()}</span></td>
                        <td><div style="font-weight:700; color:#fff">${this.esc(l.username)}</div></td>
                        <td><span class="chip" style="background:rgba(59, 130, 246, 0.1); color:#a5b4fc">${this.esc(l.action)}</span></td>
                        <td style="font-size:0.8rem">${this.esc(l.detail)}</td>
                        <td><span class="chip chip-${l.level}">${l.level}</span></td>
                        <td style="font-family:monospace; font-size:0.75rem; color:var(--muted)">${l.ip_address}</td>
                    </tr>
                `).join('');
            }
        } catch (e) { console.error(e); }
    },

    async loadSettings() {
        const list = document.getElementById('settings-list');
        if (!list) return;
        list.innerHTML = '<div style="text-align:center; padding:40px"><i class="fa-solid fa-spinner fa-spin"></i> Loading settings...</div>';

        try {
            const res = await fetch(`${this.apiBase}?action=settings_list`);
            const data = await res.json();
            if (data.success) {
                list.innerHTML = data.data.map(s => `
                    <div class="form-group" style="margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid var(--border)">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px">
                            <label class="form-label" style="margin-bottom:0">${this.formatKey(s.setting_key)}</label>
                            <span style="font-size:0.7rem; color:var(--muted); font-family:monospace">${s.setting_key}</span>
                        </div>
                        <p style="font-size:0.75rem; color:var(--muted); margin-bottom:10px">${this.esc(s.description)}</p>
                        ${this.renderInput(s)}
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
            if (data.success) showToast(`Setting saved: ${this.formatKey(key)}`);
        } catch (e) { showToast('Error saving', 'danger'); }
    },

    esc(s) {
        if (!s) return '—';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
};

document.addEventListener('DOMContentLoaded', () => SYSTEM_PAGE.init());
