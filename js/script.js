document.addEventListener('DOMContentLoaded', () => {
    // Top Navigation Links
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.page-section');
    const headerTitle = document.getElementById('header-title');

    // --- Unsaved Changes Protection State ---
    let isSaveClicked = false;

    // --- Unsaved Changes Protection Logic ---
    let initialFormState = "";

    const getFormSnapshot = () => {
        let snapshot = "";
        const sections = document.querySelectorAll('.page-section');
        sections.forEach(section => {
            const inputs = section.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]), textarea, select');
            inputs.forEach(inp => {
                snapshot += inp.value;
            });
        });
        return snapshot;
    };

    // Capture state once everything is loaded
    setTimeout(() => {
        initialFormState = getFormSnapshot();
    }, 1000);

    const monitorInputs = () => {
        if (!initialFormState) return false;
        return getFormSnapshot() !== initialFormState;
    };

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();

            // Navigation logic (Unsaved changes check removed for navigation tabs as requested)

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
            const targetEl = document.getElementById(targetId);
            if (targetEl) targetEl.classList.remove('hidden');

            // Save state
            localStorage.setItem('curr_active_section', targetId);

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
                    t.classList.remove('text-blue-600', 'border-blue-600', 'bg-blue-50/50', 'bg-gradient-primary', 'text-white', 'shadow-md');
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
                    tab.classList.add('text-blue-600', 'border-blue-600', 'bg-blue-50');
                }

                // Hide contents
                document.querySelectorAll(`.tab-content[data-group="${group}"]`).forEach(c => {
                    c.classList.add('hidden');
                });

                // Show target
                document.getElementById(target).classList.remove('hidden');

                // Save tab state
                localStorage.setItem(`curr_tab_${group}`, target);
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

    if (themeToggleBtn) {
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
    }

    // --- Profile Management Logic ---
    const fetchProfile = async () => {
        try {
            const res = await fetch('../api/admin_api.php?action=get_profile');
            const data = await res.json();
            if (data.success) {
                const user = data.data;
                const root = '../';
                const avatarUrl = user.avatar ? (root + user.avatar) : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=4f46e5&color=fff&rounded=true`;

                // Update Sidebar
                const sName = document.getElementById('user-name');
                const sRole = document.getElementById('user-role');
                const sAvatar = document.getElementById('user-avatar');
                if (sName) sName.textContent = user.name;
                if (sRole) sRole.textContent = user.role;
                if (sAvatar) sAvatar.src = avatarUrl;

                // Update Modal Displays
                const pName = document.getElementById('prof-display-name');
                const pRole = document.getElementById('prof-display-role');
                const pPos = document.getElementById('prof-display-position');
                const pDept = document.getElementById('prof-display-dept');
                const pAvatar = document.getElementById('prof-avatar-preview');
                if (pName) pName.textContent = user.name;
                if (pRole) pRole.textContent = user.role;
                if (pPos) pPos.textContent = user.position;
                if (pDept) pDept.textContent = user.department;
                if (pAvatar) pAvatar.src = avatarUrl;

                // Update Form Inputs
                const iName = document.getElementById('prof-input-name');
                const iEmail = document.getElementById('prof-input-email');
                const iPhone = document.getElementById('prof-input-phone');
                if (iName) iName.value = user.name;
                if (iEmail) iEmail.value = user.email;
                if (iPhone) iPhone.value = user.phone;
            }
        } catch (e) { console.error('Profile fetch failed', e); }
    };

    const setupProfileModal = () => {
        const chip = document.getElementById('user-profile-chip');
        const modal = document.getElementById('profile-modal');
        if (!chip || !modal) return;

        chip.addEventListener('click', () => {
            modal.classList.remove('hidden');
            fetchProfile();
        });

        // Tab Switching
        const tabBtns = document.querySelectorAll('.profile-tab-btn');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');

                // Toggle Buttons
                tabBtns.forEach(b => {
                    b.classList.remove('active', 'bg-white', 'dark:bg-slate-800', 'text-blue-600', 'shadow-sm', 'border', 'border-blue-100', 'dark:border-slate-700');
                    b.classList.add('text-slate-400', 'hover:text-slate-600', 'dark:hover:text-slate-200');
                });
                btn.classList.add('active', 'bg-white', 'dark:bg-slate-800', 'text-blue-600', 'shadow-sm', 'border', 'border-blue-100', 'dark:border-slate-700');
                btn.classList.remove('text-slate-400', 'hover:text-slate-600', 'dark:hover:text-slate-200');

                // Toggle Content
                document.querySelectorAll('.prof-modal-tab').forEach(c => c.classList.add('hidden'));
                document.getElementById(targetId).classList.remove('hidden');
            });
        });

        // Save Functionality
        const btnSave = document.getElementById('btn-save-profile');
        if (btnSave) {
            btnSave.addEventListener('click', async () => {
                const payload = {
                    name: document.getElementById('prof-input-name').value,
                    email: document.getElementById('prof-input-email').value,
                    phone: document.getElementById('prof-input-phone').value,
                    curr_password: document.getElementById('prof-input-curr-pass').value,
                    new_password: document.getElementById('prof-input-new-pass').value
                };

                if (payload.new_password && payload.new_password !== document.getElementById('prof-input-conf-pass').value) {
                    alert('New passwords do not match');
                    return;
                }

                try {
                    const res = await fetch('../api/admin_api.php?action=update_profile', {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert('Profile updated successfully!');
                        modal.classList.add('hidden');
                        fetchProfile();
                    } else {
                        alert(data.message || 'Update failed');
                    }
                } catch (e) { alert('An error occurred during update'); }
            });
        }

        // Avatar Upload
        const avInput = document.getElementById('prof-avatar-input');
        if (avInput) {
            avInput.addEventListener('change', async function () {
                if (!this.files || !this.files[0]) return;
                const fd = new FormData();
                fd.append('avatar', this.files[0]);

                try {
                    const res = await fetch('../api/admin_api.php?action=upload_avatar', {
                        method: 'POST',
                        body: fd
                    });
                    const data = await res.json();
                    if (data.success) {
                        fetchProfile(); // Refresh UI
                    } else {
                        alert(data.message || 'Upload failed');
                    }
                } catch (e) { alert('Avatar upload failed'); }
            });
        }
    };

    fetchProfile(); // Initial sync
    setupProfileModal();

    // Restore State from localStorage
    const savedSection = localStorage.getItem('curr_active_section');
    if (savedSection) {
        const targetLink = document.querySelector(`.nav-link[data-target="${savedSection}"]`);
        if (targetLink) targetLink.click();
    }

    ['biz', 'stall', 'term'].forEach(group => {
        const savedTab = localStorage.getItem(`curr_tab_${group}`);
        if (savedTab) {
            const targetTab = document.querySelector(`[data-group="${group}"][data-target="${savedTab}"]`);
            if (targetTab) targetTab.click();
        }
    });

    // --- Search Logic ---
    const searchInput = document.getElementById('global-search');
    const searchResults = document.getElementById('search-results');

    if (searchInput) {
        searchInput.addEventListener('input', async (e) => {
            const query = e.target.value.trim();
            if (query.length < 2) {
                searchResults.classList.remove('open');
                return;
            }

            try {
                const res = await fetch(`../api/admin_api.php?action=search&q=${encodeURIComponent(query)}`);
                const json = await res.json();
                if (json.success) {
                    renderSearchResults(json.data);
                }
            } catch (e) { console.error('Search failed', e); }
        });
    }

    const renderSearchResults = (items) => {
        if (!items.length) {
            searchResults.innerHTML = '<div class="p-4 text-center text-xs text-slate-400">No matches found</div>';
        } else {
            searchResults.innerHTML = items.map(item => `
                <div class="search-item" onclick="handleSearchClick('${item.type}', '${item.slug || item.id}')">
                    <div class="search-title">${item.title}</div>
                    <div class="search-category">${item.type}</div>
                </div>
            `).join('');
        }
        searchResults.classList.add('open');
    };

    window.handleSearchClick = async (type, val) => {
        searchResults.classList.remove('open');
        if (type === 'Page') {
            window.location.href = 'page.php?slug=' + val;
        }
    };

    // Close dropdowns on outside click
    document.addEventListener('click', (e) => {
        if (searchResults && !e.target.closest('#search-wrapper')) searchResults.classList.remove('open');
    });

    // Physical refresh is now handled by the native beforeunload event below for better compatibility

    window.addEventListener('beforeunload', (e) => {
        if (!isSaveClicked && monitorInputs()) {
            // Note: This still triggers the browser native dialog for tab closure/browser refresh button
            // It is impossible to use a custom HTML modal for total browser exit/closure.
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Mark as safe to exit when any save button is clicked
    document.querySelectorAll('button').forEach(btn => {
        if (btn.innerText.toLowerCase().includes('save')) {
            btn.addEventListener('click', () => {
                isSaveClicked = true;
            });
        }
    });
});

