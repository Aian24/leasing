document.addEventListener('DOMContentLoaded', () => {
    // Top Navigation Links
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.page-section');
    const headerTitle = document.getElementById('header-title');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();

            // Restyle existing
            navLinks.forEach(n => {
                n.classList.remove('active', 'font-semibold');
                n.classList.add('text-slate-500', 'font-medium', 'hover:bg-slate-50/50');
            });

            // Highlight current
            link.classList.add('active', 'font-semibold');
            link.classList.remove('text-slate-500', 'font-medium', 'hover:bg-slate-50/50');

            // Toggle sections
            sections.forEach(sec => sec.classList.add('hidden'));
            const targetId = link.getAttribute('data-target');
            document.getElementById(targetId).classList.remove('hidden');

            // Update Title
            const titleText = link.querySelector('.nav-text') ? link.querySelector('.nav-text').innerText : link.innerText.trim();
            headerTitle.innerText = titleText;

            // Close mobile sidebar if open
            if (window.innerWidth < 1024) {
                document.getElementById('sidebar').classList.add('-translate-x-[120%]');
            }
        });
    });

    // Mobile Sidebar controls
    const sidebar = document.getElementById('sidebar');
    const openBtn = document.getElementById('open-sidebar');
    const closeBtn = document.getElementById('close-sidebar');

    openBtn.addEventListener('click', () => {
        sidebar.classList.remove('-translate-x-[120%]');
    });

    closeBtn.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-[120%]');
    });

    // Tabs functionality
    const setupTabs = (tabGroupClass) => {
        const tabs = document.querySelectorAll(`.${tabGroupClass}`);
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const group = tab.getAttribute('data-group');
                const target = tab.getAttribute('data-target');

                // Clear active states
                document.querySelectorAll(`.${tabGroupClass}[data-group="${group}"]`).forEach(t => {
                    t.classList.remove('text-indigo-600', 'border-indigo-600', 'bg-indigo-50/50', 'bg-gradient-primary', 'text-white', 'shadow-md');
                    t.classList.add('text-slate-500', 'border-transparent', 'hover:text-slate-700', 'hover:border-slate-300');
                    if (tabGroupClass === 'term-tab') {
                        t.classList.add('hover:bg-slate-50');
                    }
                });

                // Set active based on style
                if (tabGroupClass === 'term-tab') {
                    tab.classList.remove('text-slate-500', 'hover:bg-slate-50', 'hover:text-slate-700', 'hover:border-slate-300');
                    tab.classList.add('bg-gradient-primary', 'text-white', 'shadow-md');
                } else {
                    tab.classList.remove('text-slate-500', 'border-transparent', 'hover:text-slate-700', 'hover:border-slate-300');
                    tab.classList.add('text-indigo-600', 'border-indigo-600', 'bg-indigo-50');
                }

                // Hide contents
                document.querySelectorAll(`.tab-content[data-group="${group}"]`).forEach(c => {
                    c.classList.add('hidden');
                });

                // Show target
                document.getElementById(target).classList.remove('hidden');
            });
        });
    };

    setupTabs('biz-tab');
    setupTabs('stall-tab');
    setupTabs('term-tab');

    // Quick Demo Functionality
    const yearsInput = document.querySelector('input[name="years"]');
    if (yearsInput) {
        yearsInput.addEventListener('input', () => {
            document.querySelector('#total-months').value = (parseInt(yearsInput.value) * 12 || 0).toFixed(2);
            document.querySelector('#total-days').value = (parseInt(yearsInput.value) * 365 || 0);
        });
    }

    // Dark Mode Toggle
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const html = document.documentElement;

    themeToggleBtn.addEventListener('click', () => {
        html.classList.toggle('dark');
        if (html.classList.contains('dark')) {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        } else {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
    });
});
