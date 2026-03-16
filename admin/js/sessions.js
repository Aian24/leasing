/* ── Active Sessions Management Module ── */
const SESSIONS_PAGE = {
    apiBase: '',

    init() {
        console.log('Sessions Module Initializing...');
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/sessions_api.php';

        safeSetText('page-title', 'Active Sessions');
        safeSetText('page-breadcrumb', 'Admin / Security / Sessions');

        this.loadSessions();
    },

    async loadSessions() {
        const tbody = document.getElementById('sessions-tbody');
        if (!tbody) return;

        tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Scanning active users...</p></div></td></tr>`;

        try {
            const res = await fetch(`${this.apiBase}?action=list`);
            const data = await res.json();
            if (!data.success) {
                showToast(data.message, 'danger');
                return;
            }

            this.updateStats(data.stats);
            this.renderTable(data.data);
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-circle-exclamation"></i><p>Failed to scan: ${e.message}</p></div></td></tr>`;
        }
    },

    updateStats(stats) {
        safeSetText('sess-active-count', stats.active);
        safeSetText('sess-today-count', stats.today);
        safeSetText('sess-susp-count', stats.suspicious);
    },

    renderTable(sessions) {
        const tbody = document.getElementById('sessions-tbody');
        if (!tbody) return;

        const path = window.location.pathname;
        const root = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';

        if (!sessions.length) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-ghost"></i><p>No active sessions found.</p></div></td></tr>`;
            return;
        }

        tbody.innerHTML = sessions.map(s => {
            const avatar = s.avatar ? (root + '/' + s.avatar) : `https://ui-avatars.com/api/?name=${encodeURIComponent(s.name)}&background=4f46e5&color=fff&rounded=true`;
            const duration = this.getDuration(s.login_time);

            return `
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px">
                            <img src="${avatar}" style="width:32px; height:32px; border-radius:10px; border:1px solid rgba(59,130,246,0.3); object-fit:cover">
                            <div>
                                <div style="font-weight:700; font-size:0.875rem">${this.esc(s.name)}</div>
                                <div style="font-size:0.7rem; color:var(--muted)">${this.esc(s.username)} • <span style="color:var(--primary)">${s.role}</span></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-family:monospace; font-size:0.8rem; color:#a5b4fc">${s.ip_address || 'Unknown'}</td>
                    <td>
                        <div style="font-size:0.75rem" title="${this.esc(s.user_agent)}">
                            <i class="fa-solid ${this.getBrowserIcon(s.browser)}"></i> ${s.browser || 'Browser'}
                            <div style="font-size:0.65rem; color:var(--muted)">${s.platform || 'System'}</div>
                        </div>
                    </td>
                    <td style="font-size:0.78rem">${new Date(s.login_time).toLocaleString()}</td>
                    <td><span class="chip" style="background:rgba(59, 130, 246, 0.1); color:#94a3b8">${duration}</span></td>
                    <td>
                        <span class="chip chip-green">
                            <span class="live-dot" style="margin-right:5px"></span> Online
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-ghost btn-sm" style="color:#fca5a5" onclick="SESSIONS_PAGE.terminate('${s.id}', '${this.esc(s.name)}')">
                            <i class="fa-solid fa-right-from-bracket"></i> Kill
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    },

    getDuration(startTime) {
        const diff = Math.floor((new Date() - new Date(startTime)) / 60000); // in minutes
        if (diff < 1) return 'Just now';
        if (diff < 60) return `${diff}m`;
        const hrs = Math.floor(diff / 60);
        const mins = diff % 60;
        return `${hrs}h ${mins}m`;
    },

    getBrowserIcon(browser) {
        if (!browser) return 'fa-globe';
        browser = browser.toLowerCase();
        if (browser.includes('chrome')) return 'fa-chrome';
        if (browser.includes('firefox')) return 'fa-firefox-browser';
        if (browser.includes('safari')) return 'fa-safari';
        if (browser.includes('edge')) return 'fa-edge';
        return 'fa-globe';
    },

    async terminate(id, name) {
        confirmAction({
            title: 'Terminate Session?',
            msg: `This will instantly log out <b>${name}</b> and invalidate their current session.`,
            icon: 'fa-bolt',
            color: '#ef4444',
            btnClass: 'btn-danger',
            btnText: 'Terminate Now',
            action: async () => {
                try {
                    const res = await fetch(`${this.apiBase}?action=terminate`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast('Session terminated');
                        this.loadSessions();
                    } else showToast(data.message, 'danger');
                } catch (e) { showToast('Network Error', 'danger'); }
            }
        });
    },

    async terminateAll() {
        confirmAction({
            title: 'Kill ALL Sessions?',
            msg: 'This will instantly log out <b>EVERY</b> active user (including yourself). Are you absolutely sure?',
            icon: 'fa-triangle-exclamation',
            color: '#ef4444',
            btnClass: 'btn-danger',
            btnText: 'Terminate Everyone',
            action: async () => {
                try {
                    const res = await fetch(`${this.apiBase}?action=terminate_all`, { method: 'POST' });
                    const data = await res.json();
                    if (data.success) {
                        location.reload(); // Reload to trigger logout if self-killed
                    } else showToast(data.message, 'danger');
                } catch (e) { showToast('Network Error', 'danger'); }
            }
        });
    },

    esc(s) {
        if (!s) return '—';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
};

document.addEventListener('DOMContentLoaded', () => SESSIONS_PAGE.init());
