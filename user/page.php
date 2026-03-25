<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../database/config.php';
$pdo = getPDO();

if (getSetting('maintenance_mode') === 'true' && $_SESSION['role'] !== 'Admin') {
    header('Location: ../maintenance.php');
    exit;
}

$appName = getSetting('app_name', 'LeasePro');
$appTagline = getSetting('app_tagline', 'Lease Management System');

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_visible = 1 AND type = 'frontend'");
$stmt->execute([$slug]);
$page = $stmt->fetch();

$pageName = $page ? $page['page_name'] : 'Page Not Found';
$pageContent = $page ? $page['content'] : '<div class="flex flex-col items-center justify-center p-20 text-slate-500"><i class="fa-solid fa-file-circle-xmark text-4xl mb-4 text-slate-300"></i><p class="text-lg font-bold">The page you are looking for does not exist or is currently hidden.</p></div>';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageName); ?> — <?php echo htmlspecialchars($appName); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        window.ENABLE_ANNOUNCEMENTS = true;
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff', 100: '#e0e7ff', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                        },
                        dark: '#0f172a', /* slate-900 */
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>

<body class="bg-slate-50 dark:bg-slate-900 border-none text-slate-800 dark:text-slate-200 antialiased overflow-hidden h-screen flex transition-colors duration-300">

    <!-- Floating Minimal Sidebar -->
    <aside id="sidebar"
        class="w-[280px] bg-white/80 dark:bg-slate-800/90 backdrop-blur-xl border border-white/40 dark:border-slate-700/50 flex flex-col fixed inset-y-4 left-4 z-50 transform -translate-x-[120%] lg:translate-x-0 lg:relative transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] shadow-[0_8px_32px_0_rgba(31,38,135,0.05)] rounded-3xl overflow-hidden">
        <div class="h-20 flex items-center justify-between px-8 border-b border-transparent shrink-0">
            <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-primary flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-building"></i>
                </div>
                <?php 
                if ($appName === 'LeasePro') {
                    echo 'Lease<span class="text-gradient">Pro</span>';
                } else {
                    $words = explode(' ', $appName);
                    if (count($words) > 1) {
                        $lastWord = array_pop($words);
                        echo htmlspecialchars(implode(' ', $words)) . ' <span class="text-gradient">' . htmlspecialchars($lastWord) . '</span>';
                    } else {
                        echo '<span class="text-gradient">' . htmlspecialchars($appName) . '</span>';
                    }
                }
                ?>
            </h1>
            <button id="close-sidebar" class="lg:hidden text-slate-400 hover:text-slate-700 dark:text-slate-200 transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <div class="p-6 flex-1 overflow-y-auto w-full">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4 px-2">Contract Mgt</p>
            <nav class="space-y-2">
                <a href="index.php" class="nav-link text-slate-500 dark:text-slate-400 font-medium hover:bg-slate-50 dark:bg-slate-800/50 flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <span class="nav-text text-[15px]">Lessee Account</span>
                </a>
            </nav>

            <div class="mt-10">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4 px-2">Other Pages</p>
                <nav class="space-y-2">
                    <?php
                    $navStmt = $pdo->query("SELECT * FROM pages WHERE type = 'frontend' AND is_visible = 1 ORDER BY page_name ASC");
                    $navPages = $navStmt->fetchAll();
                    foreach($navPages as $p):
                        $isActive = ($slug === $p['slug']) ? 'active font-semibold' : 'text-slate-500 dark:text-slate-400 font-medium hover:bg-slate-50 dark:bg-slate-800/50';
                    ?>
                    <a href="page.php?slug=<?php echo htmlspecialchars($p['slug']); ?>"
                        class="nav-link flex items-center gap-4 px-4 py-3 rounded-2xl transition-all <?php echo $isActive; ?>">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <span class="text-[15px]"><?php echo htmlspecialchars($p['page_name']); ?></span>
                    </a>
                    <?php endforeach; ?>
                    <a href="../logout.php"
                        class="text-rose-500 hover:text-rose-600 font-bold hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center gap-4 px-4 py-3 rounded-2xl transition-all">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-power-off"></i>
                        </div>
                        <span class="text-[15px]">Sign Out</span>
                    </a>
                </nav>
            </div>
        </div>

        <div class="p-6">
            <div id="user-profile-chip"
                class="bg-blue-50 dark:bg-blue-900/40 rounded-2xl p-4 flex items-center gap-4 border border-blue-100/50 transition hover:bg-blue-100 dark:hover:bg-blue-800/50 cursor-pointer group">
                <img id="user-avatar" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['name']); ?>&background=4f46e5&color=fff&rounded=true"
                    class="w-10 h-10 rounded-full shadow-sm group-hover:ring-2 group-hover:ring-blue-400 transition-all" alt="User">
                <div class="flex-1 overflow-hidden">
                    <p id="user-name" class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                    <p id="user-role" class="text-xs text-blue-500 font-medium italic"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
                </div>
                <i class="fa-solid fa-gear text-slate-400 group-hover:text-blue-500 transition-colors"></i>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden w-full relative">
        <div id="ann-notifications" class="fixed top-24 right-8 z-[100] w-80 space-y-3 pointer-events-none"></div>

        <header class="h-20 flex items-center justify-between px-6 lg:px-10 shrink-0 z-[60] mt-4 mx-4 lg:mx-8 premium-card !overflow-visible border-none bg-white/60 dark:bg-slate-800/60">
            <div class="flex items-center gap-5">
                <button id="open-sidebar" class="lg:hidden text-slate-500 dark:text-slate-400 hover:text-blue-600 bg-white dark:bg-slate-700 shadow-sm p-3 rounded-xl transition">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="hidden md:flex items-center gap-2 text-sm font-medium text-slate-400 dark:text-slate-500 dark:text-slate-400">
                    <span>Contracts</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    <span class="text-blue-600" id="header-breadcrumb"><?php echo htmlspecialchars($pageName); ?></span>
                </div>
                <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 hidden md:block mx-2"></div>
                <h2 id="header-title" class="text-xl md:text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight"><?php echo htmlspecialchars($pageName); ?></h2>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="relative">
                    <button id="noti-toggle" class="relative flex items-center justify-center w-11 h-11 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-400 dark:text-slate-500 rounded-xl hover:text-blue-600 dark:hover:text-blue-400 hover:shadow-md transition-all shadow-sm">
                        <i class="fa-solid fa-bell text-[1.1rem]"></i>
                        <span id="noti-badge" class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center hidden">0</span>
                    </button>
                    <!-- Noti Panel -->
                    <div id="noti-panel" class="absolute right-0 top-full mt-4 w-80 bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border border-slate-200 dark:border-slate-700 rounded-3xl shadow-2xl z-[110] hidden overflow-hidden scale-95 opacity-0 transition-all origin-top-right">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">Broadcasts</h4>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Recent</span>
                        </div>
                        <div id="noti-list" class="max-h-80 overflow-y-auto p-4 space-y-3">
                            <div class="text-center py-6 text-slate-400 text-xs">No notifications</div>
                        </div>
                    </div>
                </div>
                <button id="theme-toggle" class="relative flex items-center justify-center w-11 h-11 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl hover:text-blue-600 dark:hover:text-blue-400 hover:shadow-md transition-all shadow-sm">
                    <i id="theme-icon" class="fa-solid fa-moon text-[1.1rem]"></i>
                </button>
            </div>
        </header>

        <!-- Dynamic Page Content -->
        <div class="flex-1 overflow-auto px-4 md:px-8 lg:px-10 pt-6 pb-24">
            <div class="w-full bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/50 rounded-2xl p-8 shadow-sm max-w-none gjs-rendered-content">
                <?php echo $pageContent; ?>
            </div>
        </div>
        <!-- Generic App Alert/Confirm Modal -->
        <div id="app-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fade-in-up">
                <div class="p-10 text-center">
                    <div id="app-modal-icon-bg" class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 rotate-3">
                        <i id="app-modal-icon" class="text-2xl"></i>
                    </div>
                    <h3 id="app-modal-title" class="text-xl font-extrabold text-slate-800 dark:text-white mb-2">Confirmation</h3>
                    <p id="app-modal-message" class="text-slate-500 dark:text-slate-400 text-sm font-medium leading-relaxed mb-8"></p>
                    
                    <div id="app-modal-buttons" class="flex flex-col gap-2">
                        <!-- Buttons dynamically added here -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Contract Submission Script -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Handle Auto-Print from history
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('autoprint')) {
            setTimeout(() => {
                window.print();
            }, 800);
        }

        function showAppModal({ title, message, icon, iconBg, buttons }) {
            const modal = document.getElementById('app-modal');
            document.getElementById('app-modal-title').textContent = title;
            document.getElementById('app-modal-message').textContent = message;
            
            const iconEl = document.getElementById('app-modal-icon');
            iconEl.className = icon || 'fa-solid fa-circle-info';
            
            const iconBgEl = document.getElementById('app-modal-icon-bg');
            iconBgEl.className = `w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 rotate-3 ${iconBg || 'bg-blue-100 text-blue-600'}`;

            const btnContainer = document.getElementById('app-modal-buttons');
            btnContainer.innerHTML = '';
            
            buttons.forEach(btn => {
                const b = document.createElement('button');
                b.className = btn.className || 'w-full py-3.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-2xl font-bold transition-all hover:bg-slate-200 dark:hover:bg-slate-600 text-sm';
                b.innerHTML = btn.text;
                b.onclick = () => {
                    modal.classList.add('hidden');
                    if (btn.onClick) btn.onClick();
                };
                btnContainer.appendChild(b);
            });
            
            modal.classList.remove('hidden');
        }

        const submitBtns = document.querySelectorAll('.btn-submit-contract');
        submitBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                showAppModal({
                    title: 'Submit Contract',
                    message: 'Are you sure you want to finalize and submit this document?',
                    icon: 'fa-solid fa-file-contract',
                    iconBg: 'bg-blue-100 text-blue-600',
                    buttons: [
                        {
                            text: 'Yes, Submit Now',
                            className: 'w-full py-3.5 bg-gradient-primary text-white rounded-2xl font-bold shadow-lg shadow-blue-500/20 active:scale-95 transition-all text-sm',
                            onClick: () => executeGenericSubmission(btn)
                        },
                        { text: 'Cancel' }
                    ]
                });
            });
        });

        async function executeGenericSubmission(btn) {
            const agreeCheck = btn.parentElement.querySelector('#contract-agree');
            if (agreeCheck && !agreeCheck.checked) {
                showAppModal({
                    title: 'Wait!',
                    message: 'Please agree to the terms and conditions before submitting.',
                    icon: 'fa-solid fa-hand',
                    iconBg: 'bg-amber-100 text-amber-600',
                    buttons: [{ text: 'I understand' }]
                });
                return;
            }

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';

            const formData = {};
            document.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.id) formData[el.id] = el.value;
            });

            try {
                const res = await fetch('../api/contract_api.php?action=submit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        page_id: <?php echo $page['id'] ?? 0; ?>,
                        contract_data: formData
                    })
                });
                const data = await res.json();
                
                if (data.success) {
                    btn.style.backgroundColor = '#22c55e';
                    btn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Successfully Submitted';
                    
                    showAppModal({
                        title: 'Success!',
                        message: 'Your contract has been submitted successfully. Ref: ' + data.ref_no,
                        icon: 'fa-solid fa-circle-check',
                        iconBg: 'bg-green-100 text-green-600',
                        buttons: [
                            {
                                text: '<i class="fa-solid fa-print mr-2"></i> Print Contract Now',
                                className: 'w-full py-3.5 bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-blue-500/20 active:scale-95 transition-all text-sm',
                                onClick: () => {
                                    window.open('print_contract.php?id=' + data.id, '_blank');
                                    setTimeout(() => window.location.href = 'index.php', 1000);
                                }
                            },
                            {
                                text: 'Back to Dashboard',
                                onClick: () => window.location.href = 'index.php'
                            }
                        ]
                    });
                } else {
                    showAppModal({
                        title: 'Failed',
                        message: data.message || 'Submission failed.',
                        icon: 'fa-solid fa-circle-xmark',
                        iconBg: 'bg-rose-100 text-rose-600',
                        buttons: [{ text: 'Retry' }]
                    });
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (e) {
                showAppModal({
                    title: 'Error',
                    message: 'An unexpected connection error occurred.',
                    icon: 'fa-solid fa-wifi-slash',
                    iconBg: 'bg-rose-100 text-rose-600',
                    buttons: [{ text: 'Dismiss' }]
                });
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    });
    </script>
</body>
</html>
