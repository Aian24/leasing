/* ─── Dashboard Module ─── */
const DASHBOARD = {
    apiBase: '',

    init() {
        console.log('Dashboard Module Initializing...');
        const path = window.location.pathname;
        const base = path.includes('/admin/') ? path.substring(0, path.indexOf('/admin/')) : '';
        this.apiBase = base + '/api/dashboard_api.php';
        console.log('Dashboard API Path:', this.apiBase);

        // Immediate Header Update
        safeSetText('page-title', 'Dashboard Overview');
        safeSetText('page-breadcrumb', 'Admin / Overview');

        this.refresh();
        this.loadAnnouncements();
    },

    async loadAnnouncements() {
        const feed = document.getElementById('announcements-feed');
        if (!feed) return;

        if (!window.ENABLE_ANNOUNCEMENTS) {
            feed.closest('.panel').style.display = 'none';
            return;
        }

        try {
            const res = await fetch(`../../api/announcements_api.php?action=list`);
            const data = await res.json();
            if (data.success && data.data.length > 0) {
                feed.innerHTML = data.data.map(ann => `
                    <div style="border-left: 4px solid var(--${ann.type || 'primary'}); padding: 12px; margin-bottom: 12px; background: rgba(255,255,255,0.03); border-radius: 8px;">
                        <div style="font-weight: 700; color: #fff; font-size: 0.9rem; margin-bottom: 4px;">${this.esc(ann.title)}</div>
                        <div style="font-size: 0.8rem; color: var(--muted); line-height: 1.4;">${this.esc(ann.content)}</div>
                        <div style="font-size: 0.65rem; color: #64748b; margin-top: 8px;">
                            ${new Date(ann.created_at).toLocaleDateString()} • Posted by ${this.esc(ann.creator_name)}
                        </div>
                    </div>
                `).join('');
            } else {
                feed.innerHTML = '<div style="text-align:center;color:var(--muted);font-size:0.8rem">No active announcements</div>';
            }
        } catch (e) { console.error('Failed to load announcements', e); }
    },

    esc(s) {
        if (!s) return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    async refresh() {
        try {
            console.log('Fetching Dashboard Stats...');
            const res = await fetch(`${this.apiBase}?action=stats`);
            if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);

            const json = await res.json();
            console.log('Dashboard Stats received:', json);

            if (json.success) {
                safeSetText('stat-users', json.users.total);
                safeSetText('stat-active', json.users.active);
                safeSetText('stat-pages', json.pages.total);
                safeSetText('stat-online', json.sessions.online);

                // Gauges & Status Values
                this.renderGauge('gauge-disk', json.health.disk, '#3b82f6');
                this.renderGauge('gauge-mem', json.health.memory, '#2563eb');
                this.renderGauge('gauge-cpu', json.health.cpu, '#38bdf8');
                safeSetText('dash-disk-val', `${json.health.disk}%`);
                safeSetText('dash-mem-val', `${json.health.memory}%`);
                safeSetText('dash-cpu-val', `${json.health.cpu}%`);

                // Chart and Labels
                this.renderCharts(json.chart.heights, json.chart.labels);

                // Recent Users & Activity
                this.renderRecentUsers(json.recent_users || []);
                this.renderActivity(json.activity || []);

                // Remove loading skeleton classes
                document.querySelectorAll('.wait-for-data.skeleton').forEach(el => {
                    el.classList.remove('skeleton', 'wait-for-data');
                });
            } else {
                console.error('API Error:', json.message);
            }
        } catch (e) {
            console.error('Dashboard Refresh failed:', e);
        }
    },

    renderGauge(id, val, color) {
        const el = document.getElementById(id);
        if (el) { el.style.width = val + '%'; el.style.background = color; }
    },

    renderCharts(heights, labels) {
        safeSetHTML('monthly-chart', heights.map((h, i) => `<div class="chart-bar" style="height:${h}%;animation-delay:${i * 0.05}s"></div>`).join(''));
        safeSetHTML('monthly-chart-labels', labels.map(l => `<span style="font-size:0.65rem;color:var(--muted)">${l}</span>`).join(''));
    },

    renderRecentUsers(users) {
        if (!users || users.length === 0) {
            safeSetHTML('recent-users-list', '<div style="text-align:center;padding:20px;color:var(--muted);font-size:0.85rem">No recent users.</div>');
            return;
        }

        const html = users.map(u => {
            const avatar = u.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=3b82f6&color=fff&rounded=true`;
            let timeStr = 'Never';
            if (u.last_login) {
                const date = new Date(u.last_login);
                timeStr = date.toLocaleString();
            }
            return `
                <div class="user-chip">
                    <img src="${this.esc(avatar)}" alt="${this.esc(u.name)}" style="width:34px;height:34px;border-radius:10px;object-fit:cover">
                    <div style="flex:1;overflow:hidden">
                        <div style="font-size:0.85rem;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${this.esc(u.name)}</div>
                        <div style="font-size:0.7rem;color:var(--muted);margin-top:2px"><span style="color:#60a5fa">${this.esc(u.role)}</span> &bull; ${this.esc(timeStr)}</div>
                    </div>
                </div>
            `;
        }).join('');
        safeSetHTML('recent-users-list', html);
    },

    renderActivity(activities) {
        if (!activities || activities.length === 0) {
            safeSetHTML('activity-feed', '<div style="text-align:center;padding:20px;color:var(--muted);font-size:0.85rem">No recent activity.</div>');
            return;
        }

        const html = activities.map(item => {
            let color = '#3b82f6';
            if (item.level === 'warning') color = '#f59e0b';
            if (item.level === 'error') color = '#ef4444';
            if (item.level === 'success') color = '#22c55e';

            const date = new Date(item.created_at);
            const timeStr = date.toLocaleString();

            return `
                <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;gap:14px;align-items:flex-start">
                    <div style="width:10px;height:10px;border-radius:50%;background:${color};margin-top:6px;flex-shrink:0"></div>
                    <div>
                        <div style="font-size:0.85rem;font-weight:700">${this.esc(item.action)}</div>
                        <div style="font-size:0.75rem;color:var(--muted);margin-top:4px">${this.esc(item.detail)}</div>
                        <div style="font-size:0.7rem;color:var(--muted);margin-top:6px"><strong style="color:var(--text)">${this.esc(item.username)}</strong> &bull; ${this.esc(timeStr)}</div>
                    </div>
                </div>
            `;
        }).join('');
        safeSetHTML('activity-feed', html);
    }
};

// Initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => DASHBOARD.init());
} else {
    DASHBOARD.init();
}
