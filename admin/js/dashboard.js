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
        this.renderCharts();
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
                this.renderGauge('gauge-disk', 42, '#3b82f6');
                this.renderGauge('gauge-mem', 65, '#2563eb');
                this.renderGauge('gauge-cpu', 18, '#38bdf8');
                safeSetText('dash-disk-val', '42%');
                safeSetText('dash-mem-val', '65%');
                safeSetText('dash-cpu-val', '18%');
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

    renderCharts() {
        const heights = [55, 70, 45, 80, 65, 90, 72, 60, 85, 95, 75, 88];
        safeSetHTML('monthly-chart', heights.map((h, i) => `<div class="chart-bar" style="height:${h}%;animation-delay:${i * 0.05}s"></div>`).join(''));
    }
};

// Initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => DASHBOARD.init());
} else {
    DASHBOARD.init();
}
