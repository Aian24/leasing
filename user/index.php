<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}


// Maintenance Mode Check
require_once __DIR__ . '/../database/config.php';
if (getSetting('maintenance_mode') === 'true' && !in_array($_SESSION['role'], ['Admin', 'Manager', 'Staff'])) {
    header('Location: ../maintenance.php');
    exit;
}

$appName = getSetting('app_name', 'LeasePro');
$appTagline = getSetting('app_tagline', 'Lease Management System');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appName); ?> — <?php echo htmlspecialchars($appTagline); ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        window.ENABLE_ANNOUNCEMENTS = true;
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#3b82f6', // blue-500
                            600: '#2563eb', // blue-600
                            700: '#1d4ed8', // blue-700
                        },
                        dark: '#0f172a', /* slate-900 */
                    }
                }
            }
        }
    </script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>

<body
    class="bg-slate-50 dark:bg-slate-900 border-none text-slate-800 dark:text-slate-200 antialiased overflow-hidden h-screen flex transition-colors duration-300">

    <!-- Floating Minimal Sidebar -->
    <aside id="sidebar"
        class="w-[280px] bg-white/80 dark:bg-slate-800/90 backdrop-blur-xl border border-white/40 dark:border-slate-700/50 flex flex-col fixed inset-y-4 left-4 z-50 transform -translate-x-[120%] lg:translate-x-0 lg:relative transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] shadow-[0_8px_32px_0_rgba(31,38,135,0.05)] rounded-3xl overflow-hidden">
        <div class="h-20 flex items-center justify-between px-8 border-b border-transparent shrink-0">
            <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-primary flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
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
            <button id="close-sidebar"
                class="lg:hidden text-slate-400 hover:text-slate-700 dark:text-slate-200 transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <div class="p-6 flex-1 overflow-y-auto">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4 px-2">Contract Mgt</p>
            <nav class="space-y-2">
                <!-- Navigation Item 1 -->
                <a href="#"
                    class="nav-link active font-semibold flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all"
                    data-target="lessee-section">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <span class="nav-text text-[15px]">Lessee Account</span>
                </a>
                <!-- Navigation Item 2 -->
                <a href="#"
                    class="nav-link text-slate-500 dark:text-slate-400 font-medium hover:bg-slate-50 dark:bg-slate-800/50 flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all"
                    data-target="stall-section">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <span class="nav-text text-[15px]">Select Stall</span>
                </a>
                <!-- Navigation Item 3 -->
                <a href="#"
                    class="nav-link text-slate-500 dark:text-slate-400 font-medium hover:bg-slate-50 dark:bg-slate-800/50 flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all"
                    data-target="terms-section">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <span class="nav-text text-[15px]">Lease Term & Dates</span>
                </a>
                <a href="#"
                    class="nav-link text-slate-500 dark:text-slate-400 font-medium hover:bg-slate-50 dark:bg-slate-800/50 flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all"
                    data-target="contracts-section">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <span class="nav-text text-[15px]">Submitted Contracts</span>
                </a>
            </nav>

            <div class="mt-10">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4 px-2">Other Pages</p>
                <nav class="space-y-2">
                    <?php
                    require_once '../database/config.php';
                    $pdo = getPDO();
                    $stmt = $pdo->query("SELECT * FROM pages WHERE type = 'frontend' AND is_visible = 1 ORDER BY page_name ASC");
                    $pages = $stmt->fetchAll();
                    foreach($pages as $p):
                    ?>
                    <a href="page.php?slug=<?php echo htmlspecialchars($p['slug']); ?>"
                        class="text-slate-500 dark:text-slate-400 font-medium hover:bg-slate-50 dark:bg-slate-800/50 flex items-center gap-4 px-4 py-3 rounded-2xl transition-all">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <span class="text-[15px]"><?php echo htmlspecialchars($p['page_name']); ?></span>
                    </a>
                    <?php endforeach; ?>
                    <a href="javascript:void(0)" onclick="confirmLogout()"
                        class="text-rose-500 hover:text-rose-600 font-bold hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center gap-4 px-4 py-3 rounded-2xl transition-all">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg">
                            <i class="fa-solid fa-power-off"></i>
                        </div>
                        <span class="text-[15px]">Sign Out</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Bottom Profille -->
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
        <!-- Announcements Overlay -->
        <div id="ann-notifications" class="fixed top-24 right-8 z-[100] w-80 space-y-3 pointer-events-none"></div>

        <!-- Floating Glass Header -->
        <header
            class="h-20 flex items-center justify-between px-6 lg:px-10 shrink-0 z-[60] mt-4 mx-4 lg:mx-8 premium-card !overflow-visible border-none bg-white/60 dark:bg-slate-800/60">
            <div class="flex items-center gap-5">
                <button id="open-sidebar"
                    class="lg:hidden text-slate-500 dark:text-slate-400 hover:text-blue-600 bg-white dark:bg-slate-700 shadow-sm p-3 rounded-xl transition">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div
                    class="hidden md:flex items-center gap-2 text-sm font-medium text-slate-400 dark:text-slate-500 dark:text-slate-400">
                    <span>Contracts</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    <span class="text-blue-600" id="header-breadcrumb">Creation</span>
                </div>
                <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 hidden md:block mx-2"></div>
                <h2 id="header-title"
                    class="text-xl md:text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight">Lessee
                    Account</h2>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:flex relative" id="search-wrapper">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                    <input type="text" id="global-search" placeholder="Quick search..."
                        class="pl-11 pr-4 py-2.5 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/50 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white transition-all w-64 shadow-sm"
                        autocomplete="off">
                    <!-- Search Results -->
                    <div id="search-results" class="dropdown-menu search-dropdown"></div>
                </div>

                <!-- Notifications -->
                <div class="relative">
                    <button id="noti-toggle"
                        class="relative flex items-center justify-center w-11 h-11 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-400 dark:text-slate-500 rounded-xl hover:text-blue-600 dark:hover:text-blue-400 hover:shadow-md transition-all shadow-sm">
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

                <!-- Dark Mode Toggle -->
                <button id="theme-toggle"
                    class="relative flex items-center justify-center w-11 h-11 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl hover:text-blue-600 dark:hover:text-blue-400 hover:shadow-md transition-all shadow-sm">
                    <i id="theme-icon" class="fa-solid fa-moon text-[1.1rem]"></i>
                </button>
            </div>
        </header>

        <!-- Scrollable Body Section -->
        <div class="flex-1 overflow-auto px-2 md:px-6 lg:px-8 pt-2 pb-24">
            <div class="w-full">
                <!-- Removed Top Toolbar Wrapper for spacing efficiency -->

                <!-- ============================ -->
                <!-- SECTION 1: LESSEE PROFILE    -->
                <!-- ============================ -->
                <section id="lessee-section" class="page-section animate-fade-in space-y-2"
                    style="animation-delay: 0.2s;">

                    <div class="premium-card wait-for-data skeleton">
                        <!-- Card Header -->
                        <div
                            class="bg-gradient-primary border-b border-blue-500/30 px-8 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <div class="flex items-center gap-4">
                                <div>
                                    <h3 class="text-xl font-bold text-white mb-1">Lessee Information</h3>
                                    <p class="text-blue-100 text-sm">Tenant personal and business
                                        details</p>
                                </div>
                            </div>
                            <button
                                class="flex justify-center items-center gap-2 px-5 py-2.5 bg-white dark:bg-slate-800 text-blue-600 rounded-xl hover:bg-slate-50 dark:bg-slate-800 hover:shadow-sm transition-all text-sm font-semibold shadow-sm">
                                <i class="fa-regular fa-copy"></i> Copy Existing Profile
                            </button>
                        </div>

                        <!-- Card Body -->
                        <div class="p-8 space-y-7 bg-white/60 dark:bg-slate-800/70">
                            <!-- Form of Business -->
                            <div
                                class="field-grid pb-2 border-b border-slate-100 dark:border-slate-700/50 dark:border-slate-700 border-dashed">
                                <label class="field-label text-sm font-bold text-slate-700 dark:text-slate-200">Form of
                                    Business</label>
                                <div class="field-input flex flex-wrap gap-8">
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio" name="business_form" class="peer" checked>
                                        <span
                                            class="text-[15px] font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-semibold transition-colors">Single
                                            Proprietor</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio" name="business_form" class="peer">
                                        <span
                                            class="text-[15px] font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-semibold transition-colors">Corporation</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio" name="business_form" class="peer">
                                        <span
                                            class="text-[15px] font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-semibold transition-colors">Partnership</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Detailed Text Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Account
                                        Name</label>
                                    <input type="text" id="inp-account-name"
                                        class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] text-slate-800 dark:text-slate-100"
                                        placeholder="Enter Account Name" value="">
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-bold text-slate-700 dark:text-slate-200">Tradename/Storename</label>
                                    <input type="text" id="inp-trade-name"
                                        class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] text-slate-800 dark:text-slate-100"
                                        placeholder="Enter Tradename/Storename" value="">
                                </div>

                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Lessee
                                        Address</label>
                                    <input type="text" id="inp-lessee-addr"
                                        class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] text-slate-800 dark:text-slate-100"
                                        placeholder="Enter Lessee Address" value="">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Use of Leased
                                        Premises</label>
                                    <input type="text" id="inp-use"
                                        class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] text-slate-800 dark:text-slate-100"
                                        placeholder="Enter Use of Premises" value="">
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-sm font-bold text-slate-700 dark:text-slate-200 px-1 border-l-2 border-blue-400">Tax
                                        Identification No</label>
                                    <input type="text" id="inp-tin"
                                        class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] text-slate-800 dark:text-slate-100"
                                        placeholder="e.g. 123-456-789-000">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Email
                                        Address</label>
                                    <input type="email" id="inp-email"
                                        class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] text-slate-800 dark:text-slate-100"
                                        placeholder="user@example.com">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label
                                            class="text-sm font-bold text-slate-700 dark:text-slate-200">Mobile</label>
                                        <input type="text" id="inp-mobile"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] text-slate-800 dark:text-slate-100"
                                            placeholder="+63 900 000 0000">
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="text-sm font-bold text-slate-700 dark:text-slate-200">Landline</label>
                                        <input type="text" id="inp-landline"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] text-slate-800 dark:text-slate-100"
                                            placeholder="(02) 8000 0000">
                                    </div>
                                </div>
                            </div>

                            <!-- Dividers -->
                            <hr class="border-slate-200 dark:border-slate-700/60 dark:border-slate-700 my-6">

                            <!-- Radios block -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div class="space-y-3">
                                    <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Nature of
                                        Business</label>
                                    <div
                                        class="flex flex-wrap gap-5 bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="nat_business" class="peer" checked>
                                            <span
                                                class="text-sm font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-bold">Retail</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="nat_business" class="peer">
                                            <span
                                                class="text-sm font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-bold">Service</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="nat_business" class="peer">
                                            <span
                                                class="text-sm font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-bold">Mixed</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Franchise
                                        Type</label>
                                    <div
                                        class="grid grid-cols-2 gap-3 bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="franchise_type" class="peer">
                                            <span
                                                class="text-sm font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-bold">Franchisor</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="franchise_type" class="peer" checked>
                                            <span
                                                class="text-sm font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-bold">Owned</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="franchise_type" class="peer">
                                            <span
                                                class="text-sm font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-bold">Franchisee</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="franchise_type" class="peer">
                                            <span
                                                class="text-sm font-medium text-slate-600 dark:text-slate-300 peer-checked:text-blue-600 peer-checked:font-bold">Organizer</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nested Tabs (Corp vs Single Pro.) -->
                        <div
                            class="bg-blue-50 dark:bg-blue-900/40/30 dark:bg-blue-900/20 border-t border-slate-200 dark:border-slate-700/50 dark:border-slate-700">
                            <div class="flex px-4 pt-4 gap-2">
                                <a href="#"
                                    class="biz-tab font-bold text-sm px-6 py-3 rounded-t-xl hover:text-slate-800 dark:text-slate-100 hover:bg-white/ transition border border-transparent text-slate-500 dark:text-slate-400 text-center"
                                    data-group="biz" data-target="biz-corp">Corporation Data</a>
                                <a href="#"
                                    class="biz-tab font-bold text-sm px-6 py-3 rounded-t-xl text-blue-600 bg-white dark:bg-slate-800 border border-b-0 border-blue-100 transition shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)] text-center"
                                    data-group="biz" data-target="biz-single">Single Proprietor Data</a>
                            </div>

                            <!-- Corp Tab Content -->
                            <div id="biz-corp"
                                class="tab-content hidden p-8 bg-white dark:bg-slate-800 border-t border-blue-100"
                                data-group="biz">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-1.5">
                                        <label
                                            class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">President
                                            Name</label>
                                        <input type="text" id="inp-pres-name"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-sm" placeholder="Enter President Name">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label
                                            class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SEC
                                            Reg
                                            Date</label>
                                        <input type="date" id="inp-sec-date"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-sm" placeholder="Select SEC Reg Date">
                                    </div>
                                </div>
                            </div>

                            <!-- Single Tab Content -->
                            <div id="biz-single"
                                class="tab-content block p-8 bg-white dark:bg-slate-800 border-t border-blue-100"
                                data-group="biz">
                                <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-6">
                                    <div class="md:col-span-3 xl:col-span-2 space-y-1.5">
                                        <label
                                            class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Proprietor
                                            Name</label>
                                        <input type="text" id="inp-prop-name"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-sm font-semibold"
                                            placeholder="Enter Proprietor Name" value="">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label
                                            class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Last
                                            Name</label>
                                        <input type="text" id="inp-last-name"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-sm"
                                            placeholder="Last Name" value="">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label
                                            class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">First
                                            Name</label>
                                        <input type="text" id="inp-first-name"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-sm"
                                            placeholder="First Name" value="">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label
                                            class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">M.I.</label>
                                        <input type="text" id="inp-mi"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-sm"
                                            placeholder="M.I." value="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Details -->
                        <div
                            class="bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 p-6 sm:px-8 flex flex-col md:flex-row items-end gap-6">
                            <div class="flex-1 w-full space-y-1.5">
                                <label
                                    class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tenant
                                    Profile
                                    Number</label>
                                <div class="flex gap-2">
                                    <input type="text" id="inp-tenant-profile-num"
                                        class="form-input font-inter flex-1 px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold bg-white dark:bg-slate-800"
                                        placeholder="Auto-generated">
                                    <button
                                        class="px-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-700 hover:text-blue-600 font-bold transition-colors shadow-sm"><i
                                            class="fa-solid fa-ellipsis"></i></button>
                                </div>
                            </div>
                            <div class="flex-1 w-full space-y-1.5">
                                <label
                                    class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Profile
                                    Name</label>
                                <input type="text" id="inp-profile-name"
                                    class="form-input font-inter w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm bg-white dark:bg-slate-800"
                                    placeholder="Enter custom profile name">
                            </div>
                        </div>
                    </div>

                    <!-- Form Action Buttons -->
                        <button onclick="document.querySelector('[data-target=\'stall-section\']').click()" class="flex items-center gap-2 px-8 py-3 bg-gradient-primary text-white rounded-xl hover:opacity-90 transition-all font-bold shadow-lg shadow-blue-500/30 text-sm hover:-translate-y-0.5 ml-auto">
                            Continue to Stall <i class="fa-solid fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </section>


                <!-- ============================ -->
                <!-- SECTION 2: STALL CONFIG      -->
                <!-- ============================ -->
                <section id="stall-section" class="page-section hidden animate-fade-in space-y-2"
                    style="animation-delay: 0.2s;">
                    <div class="premium-card wait-for-data skeleton">
                        <!-- Banner Header -->
                        <div
                            class="bg-gradient-primary px-8 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <div class="flex items-center gap-4">
                                <div>
                                    <h3 class="text-xl font-bold text-white mb-1">Stall Configuration</h3>
                                    <p class="text-blue-100 text-sm">Select and organize contract units</p>
                                </div>
                            </div>
                        </div>

                        <!-- Stall Tabs -->
                        <div
                            class="flex flex-wrap border-b border-slate-200 dark:border-slate-700/60 dark:border-slate-700 bg-white/80 dark:bg-slate-800/90 px-4 pt-4 gap-2">
                            <a href="#"
                                class="stall-tab text-sm font-bold px-6 py-4 rounded-t-xl text-blue-600 bg-blue-50 dark:bg-blue-900/40/50 dark:bg-blue-900/40 border border-b-0 border-blue-100 transition shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)] text-center"
                                data-group="stall" data-target="stall-single"><i
                                    class="fa-solid fa-shop mr-2"></i>Single Stall</a>
                            <a href="#"
                                class="stall-tab text-sm font-bold px-6 py-4 rounded-t-xl text-slate-500 dark:text-slate-400 border border-transparent hover:text-slate-700 dark:text-slate-200 hover:bg-white/ transition text-center"
                                data-group="stall" data-target="stall-multi"><i
                                    class="fa-solid fa-layer-group mr-2"></i>Multiple Stalls</a>
                            <a href="#"
                                class="stall-tab text-sm font-bold px-6 py-4 rounded-t-xl text-slate-500 dark:text-slate-400 border border-transparent hover:text-slate-700 dark:text-slate-200 hover:bg-white/ transition text-center"
                                data-group="stall" data-target="stall-daily"><i
                                    class="fa-regular fa-calendar-check mr-2"></i>Daily</a>
                        </div>

                        <!-- Tab Content : Single Stall -->
                        <div id="stall-single" class="tab-content block p-6 md:p-10 bg-white/70 dark:bg-slate-800/80"
                            data-group="stall">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

                                <!-- Left Column Form -->
                                <div class="lg:col-span-5 space-y-5">
                                    <div
                                        class="bg-blue-50 dark:bg-blue-900/40/30 dark:bg-blue-900/20 border border-blue-100/50 rounded-2xl p-5 space-y-4 shadow-sm">
                                        <!-- Stall Code -->
                                        <div>
                                            <label
                                                class="block text-xs font-bold text-blue-900 uppercase tracking-wider mb-2">Stall
                                                Code</label>
                                            <div class="flex gap-2">
                                                <input type="text" id="inp-stall-code" value="" placeholder="e.g. 2B008"
                                                    class="form-input font-inter flex-1 px-4 py-2.5 bg-white dark:bg-slate-800 border border-blue-200 rounded-xl text-[15px] font-bold text-blue-900">
                                                <button id="btn-browse-stalls"
                                                    class="px-4 py-2.5 bg-blue-600 border border-blue-600 rounded-xl hover:bg-blue-700 text-white shadow-sm transition flex items-center justify-center gap-2 text-sm font-bold whitespace-nowrap"><i
                                                        class="fa-solid fa-folder-open"></i> Browse</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label
                                                class="text-xs font-bold text-slate-500 dark:text-slate-400 tracking-wider">Stall
                                                Number</label>
                                            <input type="text" id="inp-stall-num" value="" placeholder="Enter Stall Number"
                                                class="form-input font-inter w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label
                                                class="text-xs font-bold text-slate-500 dark:text-slate-400 tracking-wider">Floor</label>
                                            <input type="text" id="inp-floor" value="" placeholder="Enter Floor"
                                                class="form-input font-inter w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label
                                                class="text-xs font-bold text-slate-500 dark:text-slate-400 tracking-wider">Unit
                                                Type</label>
                                            <input type="text" id="inp-unit-type" value="" placeholder="Enter Unit Type"
                                                class="form-input font-inter w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label
                                                class="text-xs font-bold text-slate-500 dark:text-slate-400 tracking-wider">Section</label>
                                            <input type="text" id="inp-section" value="" placeholder="Enter Section"
                                                class="form-input font-inter w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300">
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center justify-between p-4 bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl mt-4 shadow-lg text-white">
                                        <label class="text-lg font-bold tracking-wide">Total Sqm</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" id="inp-area" step="0.01" value="" placeholder="0.00"
                                                class="w-24 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-lg font-bold text-right focus:ring-2 focus:ring-blue-500 transition text-slate-900 placeholder-slate-400">
                                            <span class="text-slate-400 font-medium text-sm">sq.m.</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column Form -->
                                <div class="lg:col-span-7 space-y-6">
                                    <div
                                        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm rounded-2xl p-6 space-y-4 hover:border-blue-200 transition-colors">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div
                                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm">
                                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                            </div>
                                            <h4 class="font-bold text-slate-800 dark:text-slate-100 text-lg">Rent Clause
                                            </h4>
                                        </div>
                                        <select
                                            class="w-full form-input font-inter px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300">
                                            <option>Standard Rent Format</option>
                                        </select>
                                        <textarea id="inp-rent-clause" rows="2"
                                            class="form-input font-inter w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-[15px] font-medium text-slate-700 dark:text-slate-200 leading-relaxed shadow-sm">PHP 27,000.00 /month; plus 12% EVAT; subject to 5% withholding tax</textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div
                                            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm rounded-2xl p-5 space-y-3 hover:border-blue-200 transition-colors">
                                            <h5
                                                class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2 text-sm">
                                                <i class="fa-solid fa-bolt text-amber-500"></i> CUSA Clause
                                            </h5>
                                            <select
                                                class="w-full form-input font-inter px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-300">
                                                <option>Standard format</option>
                                            </select>
                                            <textarea id="inp-cusa-clause" rows="2"
                                                class="form-input font-inter w-full px-3 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm">PHP 1,500.00 /month; plus 12% EVAT</textarea>
                                        </div>

                                        <div
                                            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm rounded-2xl p-5 space-y-3 hover:border-blue-200 transition-colors">
                                            <h5
                                                class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2 text-sm">
                                                <i class="fa-solid fa-fan text-blue-500"></i> Aircon Clause
                                            </h5>
                                            <select
                                                class="w-full form-input font-inter px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-600 dark:text-slate-300">
                                                <option>Standard format</option>
                                            </select>
                                            <textarea id="inp-aircon-clause" rows="2"
                                                class="form-input font-inter w-full px-3 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm">PHP 1,500.00 /month; plus 12% EVAT</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom 3 Columns Dashboard Cards -->
                            <div
                                class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12 bg-slate-50 dark:bg-slate-800/50 rounded-3xl p-6 border border-slate-100 dark:border-slate-700">
                                <!-- Col 1: Monthly Rent -->
                                <div
                                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/60 dark:border-slate-700 overflow-hidden transform hover:-translate-y-1 transition duration-300">
                                    <div
                                        class="bg-gradient-to-r from-blue-500 to-blue-600 px-5 py-3 flex items-center gap-2">
                                        <i class="fa-solid fa-chart-pie text-white opacity-80"></i>
                                        <h4 class="font-bold text-sm text-white tracking-wide">Monthly Rent</h4>
                                    </div>
                                    <div class="p-5 space-y-3.5">
                                        <div
                                            class="flex justify-between items-center bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-2 rounded-xl">
                                            <span class="text-slate-600 dark:text-slate-300 text-sm font-medium">Rate
                                                /sqm</span>
                                            <input type="text" id="inp-rent-rate"
                                                class="font-inter w-28 px-3 py-1.5 text-right border border-slate-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 font-bold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100"
                                                value="6,750.00">
                                        </div>
                                        <label class="flex justify-between items-center py-1 cursor-pointer group">
                                            <span
                                                class="text-slate-600 dark:text-slate-300 text-sm font-medium group-hover:text-blue-600 transition">Plus
                                                EVAT</span>
                                            <input type="checkbox" class="w-5 h-5 text-blue-600 rounded-md" checked>
                                        </label>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-500 dark:text-slate-400 text-sm">Basic Rent</span>
                                            <span id="txt-basic-rent"
                                                class="font-inter font-bold text-slate-700 dark:text-slate-200">27,000.00</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-500 dark:text-slate-400 text-sm">EVAT Amount</span>
                                            <span
                                                class="font-inter font-bold text-slate-700 dark:text-slate-200">3,240.00</span>
                                        </div>
                                        <div
                                            class="flex justify-between items-center pt-3 border-t border-slate-100 dark:border-slate-700/80">
                                            <span
                                                class="text-slate-800 dark:text-slate-100 font-extrabold text-sm">Total
                                                Amount</span>
                                            <span id="txt-rent-total"
                                                class="font-inter font-extrabold text-blue-700 text-lg">30,240.00</span>
                                        </div>

                                        <div
                                            class="border-t border-dashed border-slate-200 dark:border-slate-700 mt-4 pt-4 space-y-3">
                                            <label class="flex justify-between items-center cursor-pointer group">
                                                <span
                                                    class="font-bold text-xs text-slate-600 dark:text-slate-300 uppercase tracking-wide group-hover:text-blue-600 transition">Implements
                                                    WT</span>
                                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded-md">
                                            </label>
                                            <div class="flex justify-between items-center">
                                                <span class="text-slate-500 dark:text-slate-400 text-sm">WT Rate</span>
                                                <input type="text"
                                                    class="font-inter w-20 px-2 py-1 text-right border border-slate-200 dark:border-slate-700 rounded-md text-sm font-semibold bg-slate-50 dark:bg-slate-800"
                                                    value="5.00%">
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-slate-500 dark:text-slate-400 text-sm">WT
                                                    Amount</span>
                                                <span
                                                    class="font-inter text-slate-700 dark:text-slate-200 font-bold text-sm">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Col 2: Monthly CUSA -->
                                <div
                                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/60 dark:border-slate-700 overflow-hidden transform hover:-translate-y-1 transition duration-300">
                                    <div
                                        class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-3 flex items-center gap-2">
                                        <i class="fa-solid fa-bolt text-white opacity-80"></i>
                                        <h4 class="font-bold text-sm text-white tracking-wide">Monthly CUSA</h4>
                                    </div>
                                    <div class="p-5 space-y-3.5">
                                        <div
                                            class="flex justify-between items-center bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-2 rounded-xl">
                                            <span class="text-slate-600 dark:text-slate-300 text-sm font-medium">Rate
                                                /sqm</span>
                                            <input type="text" id="inp-cusa-rate"
                                                class="font-inter w-28 px-3 py-1.5 text-right border border-slate-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 font-bold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100"
                                                value="375.00">
                                        </div>
                                        <label class="flex justify-between items-center py-1 cursor-pointer group">
                                            <span
                                                class="text-slate-600 dark:text-slate-300 text-sm font-medium group-hover:text-amber-600 transition">Plus
                                                EVAT</span>
                                            <input type="checkbox" class="w-5 h-5 text-amber-500 rounded-md" checked>
                                        </label>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-500 dark:text-slate-400 text-sm">Basic CUSA</span>
                                            <span id="txt-basic-cusa"
                                                class="font-inter font-bold text-slate-700 dark:text-slate-200">1,500.00</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-500 dark:text-slate-400 text-sm">EVAT Amount</span>
                                            <span
                                                class="font-inter font-bold text-slate-700 dark:text-slate-200">180.00</span>
                                        </div>
                                        <div
                                            class="flex justify-between items-center pt-3 border-t border-slate-100 dark:border-slate-700/80">
                                            <span
                                                class="text-slate-800 dark:text-slate-100 font-extrabold text-sm">Total
                                                Amount</span>
                                            <span id="txt-cusa-total"
                                                class="font-inter font-extrabold text-amber-600 text-lg">1,680.00</span>
                                        </div>

                                        <div
                                            class="border-t border-dashed border-slate-200 dark:border-slate-700 mt-4 pt-4 space-y-3">
                                            <label class="flex justify-between items-center cursor-pointer group">
                                                <span
                                                    class="font-bold text-xs text-slate-600 dark:text-slate-300 uppercase tracking-wide group-hover:text-amber-600 transition">Implements
                                                    WT</span>
                                                <input type="checkbox" class="w-4 h-4 text-amber-500 rounded-md">
                                            </label>
                                            <div class="flex justify-between items-center">
                                                <span class="text-slate-500 dark:text-slate-400 text-sm">WT Rate</span>
                                                <input type="text"
                                                    class="font-inter w-20 px-2 py-1 text-right border border-slate-200 dark:border-slate-700 rounded-md text-sm font-semibold bg-slate-50 dark:bg-slate-800"
                                                    value="2.00%">
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-slate-500 dark:text-slate-400 text-sm">WT
                                                    Amount</span>
                                                <span
                                                    class="font-inter text-slate-700 dark:text-slate-200 font-bold text-sm">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Col 3: Monthly Aircon -->
                                <div
                                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/60 dark:border-slate-700 overflow-hidden transform hover:-translate-y-1 transition duration-300">
                                    <div
                                        class="bg-gradient-to-r from-teal-400 to-emerald-500 px-5 py-3 flex items-center gap-2">
                                        <i class="fa-solid fa-fan text-white opacity-80"></i>
                                        <h4 class="font-bold text-sm text-white tracking-wide">Monthly Aircon</h4>
                                    </div>
                                    <div class="p-5 space-y-3.5">
                                        <div
                                            class="flex justify-between items-center bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-2 rounded-xl">
                                            <span class="text-slate-600 dark:text-slate-300 text-sm font-medium">Rate
                                                /sqm</span>
                                            <input type="text" id="inp-aircon-rate"
                                                class="font-inter w-28 px-3 py-1.5 text-right border border-slate-300 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 font-bold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100"
                                                value="375.00">
                                        </div>
                                        <label class="flex justify-between items-center py-1 cursor-pointer group">
                                            <span
                                                class="text-slate-600 dark:text-slate-300 text-sm font-medium group-hover:text-emerald-500 transition">Plus
                                                EVAT</span>
                                            <input type="checkbox" class="w-5 h-5 text-emerald-500 rounded-md" checked>
                                        </label>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-500 dark:text-slate-400 text-sm">Basic Aircon</span>
                                            <span id="txt-basic-aircon"
                                                class="font-inter font-bold text-slate-700 dark:text-slate-200">1,500.00</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-500 dark:text-slate-400 text-sm">EVAT Amount</span>
                                            <span
                                                class="font-inter font-bold text-slate-700 dark:text-slate-200">180.00</span>
                                        </div>
                                        <div
                                            class="flex justify-between items-center pt-3 border-t border-slate-100 dark:border-slate-700/80">
                                            <span
                                                class="text-slate-800 dark:text-slate-100 font-extrabold text-sm">Total
                                                Amount</span>
                                            <span id="txt-aircon-total"
                                                class="font-inter font-extrabold text-emerald-600 text-lg">1,680.00</span>
                                        </div>

                                        <div
                                            class="border-t border-dashed border-slate-200 dark:border-slate-700 mt-4 pt-4 space-y-3">
                                            <label class="flex justify-between items-center cursor-pointer group">
                                                <span
                                                    class="font-bold text-xs text-slate-600 dark:text-slate-300 uppercase tracking-wide group-hover:text-emerald-500 transition">Implements
                                                    WT</span>
                                                <input type="checkbox" class="w-4 h-4 text-emerald-500 rounded-md">
                                            </label>
                                            <div class="flex justify-between items-center">
                                                <span class="text-slate-500 dark:text-slate-400 text-sm">WT Rate</span>
                                                <input type="text"
                                                    class="font-inter w-20 px-2 py-1 text-right border border-slate-200 dark:border-slate-700 rounded-md text-sm font-semibold bg-slate-50 dark:bg-slate-800"
                                                    value="2.00%">
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-slate-500 dark:text-slate-400 text-sm">WT
                                                    Amount</span>
                                                <span
                                                    class="font-inter text-slate-700 dark:text-slate-200 font-bold text-sm">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content placeholders -->
                        <div id="stall-multi"
                            class="tab-content hidden p-16 bg-white/70 dark:bg-slate-800/80 text-center"
                            data-group="stall">
                            <i class="fa-solid fa-layer-group text-5xl text-blue-200 mb-4 animate-bounce"></i>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Multiple Stalls</h3>
                            <p class="text-slate-500 dark:text-slate-400">Configuration panel for multi-stall contracts.
                            </p>
                        </div>
                        <div id="stall-daily"
                            class="tab-content hidden p-16 bg-white/70 dark:bg-slate-800/80 text-center"
                            data-group="stall">
                            <i class="fa-regular fa-calendar-check text-5xl text-blue-200 mb-4 animate-bounce"></i>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Daily Contracts</h3>
                            <p class="text-slate-500 dark:text-slate-400">Fast tracking setup for daily or event
                                leasing.</p>
                        </div>
                    </div>

                    <!-- Form Action Buttons -->
                    <div class="flex items-center justify-end gap-4 mt-8 bg-white/40 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/50 dark:border-slate-700">
                        <button onclick="document.querySelector('[data-target=\'lessee-section\']').click()" class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:bg-slate-800 transition-all font-bold shadow-sm text-sm mr-auto">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Back
                        </button>
                        <button onclick="document.querySelector('[data-target=\'terms-section\']').click()" class="flex items-center gap-2 px-8 py-3 bg-gradient-primary text-white rounded-xl hover:opacity-90 transition-all font-bold shadow-lg shadow-blue-500/30 text-sm hover:-translate-y-0.5">
                            Continue to Terms <i class="fa-solid fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </section>

                <!-- ============================ -->
                <!-- SECTION 3: LEASE TERMS       -->
                <!-- ============================ -->
                <section id="terms-section" class="page-section hidden animate-fade-in space-y-2"
                    style="animation-delay: 0.2s;">
                    <div class="w-full">
                        <div class="premium-card overflow-hidden wait-for-data skeleton">
                            <!-- Banner Header -->
                            <div class="bg-gradient-primary px-8 py-6">
                                <h3 class="text-xl font-bold text-white mb-1">Lease Duration</h3>
                                <p class="text-blue-100 text-sm">Configure contract start and end rules</p>
                            </div>

                            <div class="p-8 md:p-10 space-y-10 bg-white/80 dark:bg-slate-800/90">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-2 relative">
                                        <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Commencement
                                            of Lease</label>
                                        <input type="date" id="inp-commence-lease"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] font-semibold text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-300 transition"
                                            value="2022-07-01">
                                    </div>
                                    <div class="space-y-2 relative">
                                        <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Commencement
                                            of Rent</label>
                                        <input type="date" id="inp-commence-rent"
                                            class="form-input font-inter w-full px-4 py-3 rounded-xl text-[15px] font-semibold text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-300 transition"
                                            value="2022-07-01">
                                    </div>
                                </div>

                                <div
                                    class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-inner">
                                    <div class="flex items-center justify-between mb-4">
                                        <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Contract
                                            Type</label>
                                    </div>

                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/60 dark:border-slate-700 overflow-hidden">
                                        <div
                                            class="flex border-b border-slate-100 dark:border-slate-700 p-2 gap-2 bg-slate-50 dark:bg-slate-800/50">
                                            <a href="#"
                                                class="term-tab flex-1 py-2.5 rounded-xl text-center text-sm font-bold transition-all bg-gradient-primary text-white shadow-md transform scale-[1.02]"
                                                data-group="term" data-target="term-long">Long-Term</a>
                                            <a href="#"
                                                class="term-tab flex-1 py-2.5 rounded-xl text-center text-sm font-semibold text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-700 transition-all"
                                                data-group="term" data-target="term-short">Short-Term</a>
                                            <a href="#"
                                                class="term-tab flex-1 py-2.5 rounded-xl text-center text-sm font-semibold text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-700 transition-all hidden sm:block"
                                                data-group="term" data-target="term-exhibit">Exhibit</a>
                                            <a href="#"
                                                class="term-tab flex-1 py-2.5 rounded-xl text-center text-sm font-semibold text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-700 transition-all hidden sm:block"
                                                data-group="term" data-target="term-ambulant">Ambulant</a>
                                        </div>

                                        <div id="term-long" class="tab-content block p-6 md:px-10 space-y-5"
                                            data-group="term">
                                            <div class="flex items-center justify-between group">
                                                <span
                                                    class="text-[15px] font-semibold text-slate-600 dark:text-slate-300">Years</span>
                                                <div class="w-32">
                                                    <input type="number" name="years" id="inp-lease-years"
                                                        class="font-inter form-input w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-center text-lg font-bold focus:ring-2 focus:ring-blue-500 transition-all group-hover:border-blue-300"
                                                        value="2">
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between group">
                                                <span
                                                    class="text-[15px] font-semibold text-slate-600 dark:text-slate-300">And
                                                    Months</span>
                                                <div class="w-32">
                                                    <input type="number" id="inp-lease-months"
                                                        class="font-inter form-input w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-center text-lg font-bold focus:ring-2 focus:ring-blue-500 transition-all group-hover:border-blue-300"
                                                        value="6">
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between group">
                                                <span
                                                    class="text-[15px] font-semibold text-slate-600 dark:text-slate-300">And
                                                    Days</span>
                                                <div class="w-32">
                                                    <input type="number" id="inp-lease-days"
                                                        class="font-inter form-input w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-center text-lg font-bold focus:ring-2 focus:ring-blue-500 transition-all group-hover:border-blue-300"
                                                        value="1">
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="bg-blue-50 dark:bg-blue-900/40/80 p-4 text-center text-sm font-bold text-blue-700 border-t border-blue-100/60 tracking-wide">
                                            Two (2) Years and Six (6) Months and One (1) Day
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="bg-slate-800 text-white rounded-2xl p-6 flex flex-col md:flex-row items-center gap-6 shadow-lg shadow-slate-800/20">
                                    <div class="flex-1 w-full relative">
                                        <label
                                            class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 block">Expiration
                                            Date</label>
                                        <input type="date" id="inp-expire-date"
                                            class="font-inter w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-lg font-bold text-slate-800 dark:text-white focus:outline-none focus:border-blue-500 transition"
                                            value="2024-12-31">
                                    </div>
                                    <div class="h-12 w-px bg-white/ hidden md:block"></div>
                                    <div class="flex-1 w-full grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="text-xs font-bold text-blue-300 uppercase tracking-widest mb-1 block">Total
                                                Months</label>
                                            <input id="total-months" type="text"
                                                class="font-inter w-full bg-transparent border-0 text-3xl font-extrabold text-white p-0"
                                                value="30.00" readonly>
                                        </div>
                                        <div>
                                            <label
                                                class="text-xs font-bold text-blue-300 uppercase tracking-widest mb-1 block">Total
                                                Days</label>
                                            <input id="total-days" type="text"
                                                class="font-inter w-full bg-transparent border-0 text-3xl font-extrabold text-white p-0"
                                                value="914" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="border border-amber-200/60 rounded-3xl p-6 bg-gradient-to-br from-amber-50 to-orange-50/30">
                                    <label class="flex items-center gap-3 cursor-pointer mb-5">
                                        <input type="checkbox"
                                            class="w-5 h-5 text-amber-500 rounded-md border-amber-300 focus:ring-amber-500">
                                        <span class="text-[15px] font-bold text-amber-900 flex items-center gap-2">
                                            <i class="fa-solid fa-person-digging text-amber-600"></i> Construction Rent
                                            Free Period
                                        </span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 opacity-40 pointer-events-none transition-opacity duration-300"
                                        id="rent-free-fields">
                                        <div class="md:col-span-2 space-y-1.5">
                                            <label
                                                class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Turnover
                                                Date</label>
                                            <input type="date" id="inp-turnover-date"
                                                class="form-input font-inter w-full px-4 py-3 border border-amber-200 rounded-xl text-sm bg-white/80 dark:bg-slate-800/90">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label
                                                class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Rent
                                                Free From</label>
                                            <input type="date" id="inp-rent-free-from"
                                                class="form-input font-inter w-full px-4 py-3 border border-amber-200 rounded-xl text-sm bg-white/80 dark:bg-slate-800/90">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label
                                                class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Rent
                                                Free To</label>
                                            <input type="date" id="inp-rent-free-to"
                                                class="form-input font-inter w-full px-4 py-3 border border-amber-200 rounded-xl text-sm bg-white/80 dark:bg-slate-800/90">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-6 print:hidden">
                            <button onclick="document.querySelector('[data-target=\'stall-section\']').click()" class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:bg-slate-800 transition-all font-bold shadow-sm text-sm mr-auto">
                                <i class="fa-solid fa-arrow-left mr-1"></i> Back
                            </button>
                            <button id="btn-submit-main-contract" onclick="submitMainContract()" class="flex items-center gap-2 px-8 py-3 bg-gradient-primary text-white rounded-xl hover:opacity-90 transition-all font-bold shadow-lg shadow-blue-500/30 text-sm hover:-translate-y-0.5">
                                <i class="fa-solid fa-file-signature"></i> Finalize & Submit Contract
                            </button>
                        </div>
                    </div>
                </section>

                <!-- ============================ -->
                <!-- SECTION: MY CONTRACTS        -->
                <!-- ============================ -->
                <section id="contracts-section" class="page-section hidden animate-fade-in">
                    <div class="premium-card !p-0 overflow-hidden border-none shadow-2xl">
                        <!-- Unified Header -->
                        <div class="bg-gradient-primary px-8 py-8">
                            <h3 class="text-2xl font-black text-white mb-1 tracking-tight">Submitted Contracts</h3>
                            <p class="text-blue-100/80 text-sm font-medium">Monitor and manage your signed lease agreements</p>
                        </div>

                        <!-- Data Table Controls -->
                        <div class="p-6 bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Show</span>
                                <select id="history-length" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 outline-none focus:border-blue-500 transition-all">
                                    <option value="5">5 entries</option>
                                    <option value="10" selected>10 entries</option>
                                    <option value="25">25 entries</option>
                                    <option value="50">50 entries</option>
                                </select>
                            </div>
                            <div class="relative w-full md:w-72 group">
                                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                                <input type="text" id="history-search" placeholder="Search contracts..." class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-medium focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                            </div>
                        </div>

                        <!-- Main Table -->
                        <div class="px-6 py-4 bg-white dark:bg-slate-800 min-h-[400px]">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-separate border-spacing-y-3">
                                    <thead class="bg-slate-50/50 dark:bg-slate-900/50 rounded-2xl">
                                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <th class="px-6 py-4 rounded-l-2xl">Ref #</th>
                                            <th class="px-6 py-4 text-center">Lessee Name</th>
                                            <th class="px-6 py-4 text-center">Form Type</th>
                                            <th class="px-6 py-4 text-center">Submitted</th>
                                            <th class="px-6 py-4 text-center">Status</th>
                                            <th class="px-6 py-4 text-right rounded-r-2xl">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contracts-tbody" class="text-sm">
                                        <tr>
                                            <td colspan="6" class="w-full text-center py-20 text-slate-500 font-medium">
                                                <div class="flex flex-col items-center gap-4">
                                                    <i class="fa-solid fa-spinner fa-spin text-3xl text-blue-500"></i>
                                                    <p class="text-xs uppercase tracking-widest font-black opacity-50">Fetching your contracts...</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
                            <p id="history-info" class="text-xs font-bold text-slate-400 uppercase tracking-widest">Showing 0 to 0 of 0 entries</p>
                            <div id="history-pagination" class="flex items-center gap-2">
                                <!-- Pagination buttons injected here -->
                            </div>
                        </div>
                    </div>
                </section>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const tbody = document.getElementById('contracts-tbody');
                            const searchInput = document.getElementById('history-search');
                            const lengthSelect = document.getElementById('history-length');
                            const paginationContainer = document.getElementById('history-pagination');
                            const infoLabel = document.getElementById('history-info');
                            
                            let rawContracts = [];
                            let filteredContracts = [];
                            let currentPage = 1;
                            let rowsPerPage = 10;

                            async function loadContracts() {
                                try {
                                    const res = await fetch('../api/contract_api.php?action=list');
                                    const data = await res.json();
                                    if (data.success) {
                                        rawContracts = data.data;
                                        applyFilters();
                                    }
                                } catch (e) {
                                    tbody.innerHTML = `<tr><td colspan="6" class="py-20 text-center text-rose-500 font-black uppercase text-[10px] tracking-widest">Failed to connect to secure server</td></tr>`;
                                }
                            }

                            function applyFilters() {
                                const q = searchInput.value.toLowerCase();
                                filteredContracts = rawContracts.filter(c => {
                                    let name = '';
                                    try {
                                        const d = JSON.parse(c.contract_data);
                                        name = (d.lessee?.account_name || d['inp-account-name'] || d['inp-profile-name'] || '').toLowerCase();
                                    } catch(e) {}
                                    
                                    return c.ref_no.toLowerCase().includes(q) || 
                                           c.page_name.toLowerCase().includes(q) || 
                                           name.includes(q);
                                });
                                currentPage = 1;
                                renderTable();
                            }

                            function renderTable() {
                                rowsPerPage = parseInt(lengthSelect.value);
                                const last = currentPage * rowsPerPage;
                                const first = last - rowsPerPage;
                                const paginatedItems = filteredContracts.slice(first, last);
                                
                                buildTableRows(paginatedItems);
                                buildPagination();
                                
                                const total = filteredContracts.length;
                                const end = last > total ? total : last;
                                infoLabel.textContent = `Showing ${total ? first + 1 : 0} to ${end} of ${total} entries`;
                            }

                            function buildTableRows(items) {
                                if (items.length === 0) {
                                    tbody.innerHTML = `<tr><td colspan="6" class="py-24 text-center text-slate-400 font-black uppercase text-[10px] tracking-widest"><i class="fa-solid fa-database mb-3 block text-2xl opacity-20"></i> No matching records found</td></tr>`;
                                    return;
                                }

                                tbody.innerHTML = items.map(c => {
                                    const statusColor = c.status === 'Approved' ? 'text-green-600 bg-green-50' : (c.status === 'Rejected' ? 'text-rose-600 bg-rose-50' : 'text-amber-600 bg-amber-50');
                                    let lesseeName = 'Contract Draft';
                                    try {
                                        const d = JSON.parse(c.contract_data);
                                        lesseeName = d.lessee?.account_name || d['inp-account-name'] || d['inp-profile-name'] || 'Draft';
                                    } catch(e) {}

                                    return `
                                        <tr class="bg-white dark:bg-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all shadow-[0_1px_3px_rgba(0,0,0,0.02)] group">
                                            <td class="px-6 py-4 rounded-l-2xl font-mono text-[10px] font-black text-slate-400 group-hover:text-blue-500">${c.ref_no || 'N/A'}</td>
                                            <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-100 text-sm text-center">${lesseeName}</td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50/50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                                    <i class="fa-solid fa-file-invoice opacity-50"></i> ${c.page_name}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 font-bold text-slate-500 text-xs text-center">
                                                ${new Date(c.submitted_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-3 py-1 text-[9px] font-black rounded-lg uppercase tracking-[0.1em] shadow-sm ${statusColor}">${c.status}</span>
                                            </td>
                                            <td class="px-6 py-4 rounded-r-2xl text-right">
                                                <div class="flex items-center justify-end">
                                                    <button onclick="printFromHistory('${c.slug}', ${c.id})" class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center group/btn shadow-sm" title="Print This Contract">
                                                        <i class="fa-solid fa-print group-hover/btn:scale-110 transition"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                                }).join('');
                            }

                            function buildPagination() {
                                const pageCount = Math.ceil(filteredContracts.length / rowsPerPage);
                                paginationContainer.innerHTML = '';
                                
                                if (pageCount <= 1) return;

                                for (let i = 1; i <= pageCount; i++) {
                                    const btn = document.createElement('button');
                                    btn.className = `w-8 h-8 rounded-lg text-xs font-black transition-all ${currentPage === i ? 'bg-blue-600 text-white shadow-lg' : 'bg-slate-100 dark:bg-slate-700 text-slate-500'}`;
                                    btn.textContent = i;
                                    btn.onclick = () => { currentPage = i; renderTable(); }
                                    paginationContainer.appendChild(btn);
                                }
                            }

                            // Event Listeners
                            searchInput.addEventListener('input', applyFilters);
                            lengthSelect.addEventListener('change', () => { currentPage = 1; renderTable(); });
                            
                            // Load contracts when the tab is clicked
                            document.querySelector('[data-target="contracts-section"]').addEventListener('click', loadContracts);
                            loadContracts();
                        });

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

                        async function submitMainContract() {
                            showAppModal({
                                title: 'Confirm Submission',
                                message: 'Are you sure you want to finalize and submit this lease contract? This will create a permanent snapshot of the data.',
                                icon: 'fa-solid fa-file-signature',
                                iconBg: 'bg-blue-100 text-blue-600',
                                buttons: [
                                    {
                                        text: 'Yes, Submit Contract',
                                        className: 'w-full py-3.5 bg-gradient-primary text-white rounded-2xl font-bold shadow-lg shadow-blue-500/20 active:scale-95 transition-all text-sm',
                                        onClick: executeSubmission
                                    },
                                    {
                                        text: 'No, Wait',
                                        onClick: () => {}
                                    }
                                ]
                            });
                        }

                        async function executeSubmission() {
                            const btn = document.getElementById('btn-submit-main-contract');
                            const originalText = btn.innerHTML;
                            
                            const getVal = (id) => document.getElementById(id) ? document.getElementById(id).value : '';
                            const getTxt = (id) => document.getElementById(id) ? document.getElementById(id).textContent : '';

                            // Gather PDF-ready snapshot data
                            const contract_data = {
                                lessee: {
                                    account_name: getVal('inp-account-name'),
                                    trade_name: getVal('inp-trade-name'),
                                    address: getVal('inp-lessee-addr'),
                                    email: getVal('inp-email'),
                                    mobile: getVal('inp-mobile'),
                                    tin: getVal('inp-tin'),
                                    nature_of_business: document.querySelector('input[name="nat_business"]:checked')?.parentElement.textContent.trim() || ''
                                },
                                stall: {
                                    location: getVal('inp-stall-code'),
                                    stall_no: getVal('inp-stall-num'),
                                    area: getVal('inp-area'),
                                    rate: getVal('inp-rent-rate'),
                                    monthly_rent: getTxt('txt-rent-total')
                                },
                                terms: {
                                    start: getVal('inp-commence-lease'),
                                    end: getVal('inp-expire-date'),
                                    years: getVal('inp-lease-years'),
                                    months: getVal('inp-lease-months'),
                                    days: getVal('inp-lease-days')
                                }
                            };

                            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
                            btn.disabled = true;

                            try {
                                const res = await fetch('../api/contract_api.php?action=submit', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ 
                                        page_id: 999, // Dashboard ID
                                        contract_data: contract_data 
                                    })
                                });
                                const data = await res.json();
                                
                                if (data.success) {
                                    btn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Contract Submitted!';
                                    btn.style.background = '#22c55e';
                                    
                                    showAppModal({
                                        title: 'Submission Success',
                                        message: 'Your contract has been successfully submitted and saved. Reference No: ' + data.ref_no,
                                        icon: 'fa-solid fa-circle-check',
                                        iconBg: 'bg-green-100 text-green-600',
                                        buttons: [
                                            {
                                                text: '<i class="fa-solid fa-print mr-2"></i> Print Contract Now',
                                                className: 'w-full py-3.5 bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-blue-500/20 active:scale-95 transition-all text-sm',
                                                onClick: () => {
                                                    window.open('print_contract.php?id=' + data.id, '_blank');
                                                    setTimeout(() => window.location.reload(), 1000);
                                                }
                                            },
                                            {
                                                text: 'Done',
                                                onClick: () => window.location.reload()
                                            }
                                        ]
                                    });
                                } else {
                                    showAppModal({
                                        title: 'Submission Failed',
                                        message: data.message || 'We could not submit your contract at this time.',
                                        icon: 'fa-solid fa-triangle-exclamation',
                                        iconBg: 'bg-rose-100 text-rose-600',
                                        buttons: [{ text: 'Back to Form' }]
                                    });
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                }
                            } catch (e) {
                                showAppModal({
                                    title: 'System Error',
                                    message: 'An unexpected error occurred while connecting to the server.',
                                    icon: 'fa-solid fa-circle-xmark',
                                    iconBg: 'bg-rose-100 text-rose-600',
                                    buttons: [{ text: 'Dismiss' }]
                                });
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                            }
                        }

                        function printFromHistory(slug, id) {
                            if (slug === 'main-lease' || id) {
                                window.open('print_contract.php?id=' + id, '_blank');
                            } else {
                                window.location.href = 'page.php?slug=' + slug + '&autoprint=true';
                            }
                        }
                    </script>
                </section>
            </div>

            <!-- Footer empty space for scroll -->
            <div class="h-24"></div>
        </div>
        <!-- Stall Record Browse Modal -->
        <div id="stall-modal"
            class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
            <div
                class="bg-white dark:bg-slate-800 w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh] border border-slate-200 dark:border-slate-700 animate-fade-in-up">
                <div
                    class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/80 rounded-t-2xl">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
                        <i class="fa-solid fa-list-ul text-blue-500"></i> Select Stall Record
                    </h2>
                    <button class="close-stall-modal text-slate-400 hover:text-rose-500 transition-colors">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                </div>
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-8 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="stall-search" placeholder="Search by Space Code, Company, or Name..."
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:border-blue-500 text-slate-800 dark:text-slate-100">
                </div>
                <div class="flex-1 overflow-y-auto p-2">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-800/50">
                                <th class="p-4 rounded-tl-xl">Space Code</th>
                                <th class="p-4">Owner / Company</th>
                                <th class="p-4">Trade Name</th>
                                <th class="p-4">Area</th>
                                <th class="p-4">Status</th>
                            </tr>
                        </thead>
                        <tbody id="stall-modal-tbody" class="text-sm">
                            <tr>
                                <td colspan="3" class="p-8 text-center text-slate-500">
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading records...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="p-4 border-t border-slate-100 dark:border-slate-700 flex justify-end bg-slate-50 dark:bg-slate-800/80 rounded-b-2xl">
                    <button
                        class="close-stall-modal px-6 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600 rounded-xl font-bold transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
        <!-- Profile Modal -->
        <div id="profile-modal"
            class="hidden fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-4">
            <div
                class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fade-in-up">
                <div class="p-10">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-5">
                            <div class="relative group">
                                <img id="prof-avatar-preview" src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff" 
                                    class="w-24 h-24 rounded-[2rem] object-cover shadow-xl border-4 border-white dark:border-slate-700">
                                <label class="absolute -bottom-2 -right-2 w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center cursor-pointer hover:bg-blue-700 shadow-lg transition-transform hover:scale-110 active:scale-95">
                                    <i class="fa-solid fa-camera text-sm"></i>
                                    <input type="file" id="prof-avatar-input" hidden accept="image/*">
                                </label>
                            </div>
                            <div>
                                <h3 id="prof-display-name" class="text-3xl font-extrabold text-slate-800 dark:text-white leading-tight">Admin User</h3>
                                <p id="prof-display-role" class="text-blue-500 font-bold uppercase tracking-widest text-[11px]">Super Administrator</p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('profile-modal').classList.add('hidden')" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-700/50 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Tabs Nav -->
                    <div class="flex gap-4 mb-8 bg-slate-50 dark:bg-slate-900/50 p-2 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <button class="flex-1 py-3 px-6 rounded-xl font-extrabold text-sm transition-all profile-tab-btn active bg-white dark:bg-slate-800 text-blue-600 shadow-sm border border-blue-100 dark:border-slate-700" data-target="prof-tab-basic">
                            <i class="fa-solid fa-user-gear mr-2"></i> Basic Info
                        </button>
                        <button class="flex-1 py-3 px-6 rounded-xl font-extrabold text-sm transition-all profile-tab-btn text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" data-target="prof-tab-security">
                            <i class="fa-solid fa-shield-halved mr-2"></i> Security
                        </button>
                    </div>

                    <!-- Tab Contents -->
                    <div id="prof-tab-basic" class="prof-modal-tab block space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Full Name</label>
                                <input type="text" id="prof-input-name" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-[15px] font-bold text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Phone Number</label>
                                <input type="text" id="prof-input-phone" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-[15px] font-bold text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                            <div class="col-span-2 space-y-2">
                                <label class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                                <input type="email" id="prof-input-email" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-[15px] font-bold text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>
                        <div class="p-6 bg-gradient-to-br from-blue-500/5 to-purple-500/5 rounded-[2rem] border border-blue-500/10 flex items-center gap-5">
                            <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-2xl flex items-center justify-center text-blue-600 shadow-soft">
                                <i class="fa-solid fa-briefcase text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] mb-1">Position & Dept</p>
                                <p class="text-slate-700 dark:text-slate-200 font-bold"><span id="prof-display-position">Senior Manager</span> <span class="mx-2 text-slate-300">|</span> <span id="prof-display-dept" class="text-blue-600">Operations Dept</span></p>
                            </div>
                        </div>
                    </div>

                    <div id="prof-tab-security" class="prof-modal-tab hidden space-y-6">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Current Password</label>
                            <input type="password" id="prof-input-curr-pass" placeholder="Confirm your identity" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-[15px] font-bold outline-none focus:border-blue-500 transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">New Password</label>
                                <input type="password" id="prof-input-new-pass" placeholder="Min. 8 chars" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-[15px] font-bold outline-none focus:border-blue-500 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Confirm Password</label>
                                <input type="password" id="prof-input-conf-pass" placeholder="Match new password" class="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-[15px] font-bold outline-none focus:border-blue-500 transition-all">
                            </div>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/10 p-5 rounded-2xl border border-amber-100 dark:border-amber-900/30 flex items-start gap-4">
                            <i class="fa-solid fa-circle-exclamation text-amber-500 mt-1"></i>
                            <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed font-medium">Changing your password will sign you out of all other devices currently active.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-10 py-8 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex justify-end items-center gap-4">
                    <button onclick="document.getElementById('profile-modal').classList.add('hidden')" class="px-8 py-3.5 text-slate-500 font-bold hover:text-slate-800 dark:hover:text-white transition-colors">Dismiss</button>
                    <button id="btn-save-profile" class="px-10 py-3.5 bg-gradient-primary text-white rounded-[1.5rem] font-bold shadow-xl shadow-blue-500/20 hover:opacity-90 active:scale-95 transition-all flex items-center gap-3">
                        <i class="fa-solid fa-floppy-disk"></i> Update Profile
                    </button>
                </div>
            </div>
        </div>
        <!-- Unsaved Changes Modal -->
        <div id="confirm-modal"
            class="hidden fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-4">
            <div
                class="bg-white dark:bg-slate-800 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fade-in-up">
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-triangle-exclamation text-3xl text-amber-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Unsaved Changes</h3>
                    <p class="text-slate-500 dark:text-slate-400">You have unsaved changes in your form. Are you sure you want to leave this page and lose your progress?</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 p-6 flex gap-3">
                    <button id="btn-confirm-cancel" class="flex-1 px-6 py-3 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-2xl font-bold hover:bg-slate-50 dark:hover:bg-slate-600 transition-all">
                        Stay Here
                    </button>
                    <button id="btn-confirm-leave" class="flex-1 px-6 py-3 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 shadow-lg shadow-rose-500/30 transition-all">
                        Leave Page
                    </button>
                </div>
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

    <script>
        // Inline small behaviour adjustment exclusively for the checkbox opacity toggle
        document.querySelector('input[type="checkbox"]')?.addEventListener('change', function () {
            const fields = document.getElementById('rent-free-fields');
            if (fields) {
                if (this.checked) fields.classList.remove('opacity-40', 'pointer-events-none');
                else fields.classList.add('opacity-40', 'pointer-events-none');
            }
        });

        // ── Stall Record Modal Script ──
        let allStalls = [];

        document.getElementById('btn-browse-stalls')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('stall-modal').classList.remove('hidden');
            loadStallRecords();
        });

        document.querySelectorAll('.close-stall-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('stall-modal').classList.add('hidden');
            });
        });

        document.getElementById('stall-search')?.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            const res = allStalls.filter(s =>
                (s.space_code || '').toLowerCase().includes(q) ||
                (s.company_name || '').toLowerCase().includes(q) ||
                (s.owner_lessee_name || '').toLowerCase().includes(q) ||
                (s.trade_name || '').toLowerCase().includes(q)
            );
            renderStalls(res);
        });

        async function loadStallRecords() {
            try {
                const res = await fetch('/Lease/api/lessees_csv.php?action=list&limit=1000');
                const json = await res.json();
                if (json.success && json.data) {
                    allStalls = json.data;
                    renderStalls(allStalls);
                }
            } catch (e) {
                document.getElementById('stall-modal-tbody').innerHTML = `<tr><td colspan="5" class="p-8 text-center text-rose-500">Error loading records.</td></tr>`;
            }
        }

        function escapeHTML(s) {
            if (!s) return '—';
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function renderStalls(list) {
            const tbody = document.getElementById('stall-modal-tbody');
            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-slate-500">No records found.</td></tr>`;
                return;
            }

            tbody.innerHTML = list.map(item => {
                // Determine color based on status (Active = Occupied/Red, else = Available/Green)
                const isOccupied = item.status === 'Active';
                const statusColor = isOccupied ? 'text-rose-500 font-bold bg-rose-50 dark:bg-rose-500/10 px-2 py-1 inline-block rounded-md' : 'text-emerald-500 font-bold bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 inline-block rounded-md';
                const statusText = isOccupied ? 'Occupied' : 'Available';

                const trClass = isOccupied
                    ? "border-b border-slate-100 dark:border-slate-700/50 opacity-60 cursor-not-allowed bg-slate-50/50 dark:bg-slate-800/50"
                    : "border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors";
                const onClickAttr = isOccupied ? "" : `onclick='selectStall(\`${btoa(encodeURIComponent(JSON.stringify(item)))}\`)'`;

                return `<tr class="${trClass}" ${onClickAttr}>
                    <td class="p-4 font-bold text-blue-600 dark:text-blue-400">${escapeHTML(item.space_code)}</td>
                    <td class="p-4 text-slate-700 dark:text-slate-200">
                        <div class="font-bold">${escapeHTML(item.company_name) || escapeHTML(item.owner_lessee_name)}</div>
                    </td>
                    <td class="p-4 text-slate-600 dark:text-slate-300 text-xs italic">
                        ${escapeHTML(item.trade_name)}
                    </td>
                    <td class="p-4 font-semibold text-slate-600 dark:text-slate-300">${escapeHTML(item.total_area)} <span class="text-[10px] text-slate-400">sqm</span></td>
                    <td class="p-4">
                        <span class="${statusColor}">${statusText}</span>
                    </td>
                </tr>`;
            }).join('');
        }

        window.selectStall = function (base64Str) {
            const payload = JSON.parse(decodeURIComponent(atob(base64Str)));
            const el = id => document.getElementById(id);
            const setVal = (id, val) => { if(el(id)) el(id).value = val ?? ''; };
            const setTxt = (id, val) => { if(el(id)) el(id).textContent = val ?? '0.00'; };
            const setRadio = (name, val) => {
                if(!val) return;
                const radios = document.getElementsByName(name);
                for(let r of radios) {
                    const lbl = r.nextElementSibling ? r.nextElementSibling.textContent.trim().toLowerCase() : '';
                    if(lbl === val.toLowerCase() || val.toLowerCase().includes(lbl)) {
                        r.checked = true;
                        // Trigger change incase there are listeners
                        r.dispatchEvent(new Event('change'));
                        break;
                    }
                }
            };

            document.getElementById('stall-modal').classList.add('hidden');

            // 1. Stall Info
            setVal('inp-stall-code', payload.space_code);
            setVal('inp-stall-num', payload.space_code);
            setVal('inp-area', payload.total_area);
            
            setVal('inp-floor', payload.floor);
            setVal('inp-unit-type', payload.unit_type);
            setVal('inp-section', payload.section);

            // 2. Clauses
            if (payload.basic_rent) {
                setVal('inp-rent-clause', `PHP ${payload.basic_rent} /month; plus 12% EVAT; subject to 5% withholding tax`);
            }
            if (payload.cusa) {
                setVal('inp-cusa-clause', `PHP ${payload.cusa} /month; plus 12% EVAT`);
            }
            if (payload.aircon_charges) {
                setVal('inp-aircon-clause', `PHP ${payload.aircon_charges} /month; plus 12% EVAT`);
            }

            // 3. Summary Cards (Calculations based on Rate * Area if needed)
            const area = parseFloat(payload.total_area) || 0;
            const rentRate = parseFloat(payload.rate_per_sqm) || 0;
            const basicRent = parseFloat(payload.basic_rent) || (rentRate * area);

            setVal('inp-rent-rate', rentRate.toLocaleString('en-US', {minimumFractionDigits:2}));
            setTxt('txt-basic-rent', basicRent.toLocaleString('en-US', {minimumFractionDigits:2}));
            const rentTotal = basicRent * 1.12;
            setTxt('txt-rent-total', rentTotal.toLocaleString('en-US', {minimumFractionDigits:2}));

            setVal('inp-cusa-rate', '375.00'); 
            const basicCusa = parseFloat(payload.cusa) || 0;
            setTxt('txt-basic-cusa', basicCusa.toLocaleString('en-US', {minimumFractionDigits:2}));
            setTxt('txt-cusa-total', (basicCusa * 1.12).toLocaleString('en-US', {minimumFractionDigits:2}));

            setVal('inp-aircon-rate', '375.00'); 
            const basicAircon = parseFloat(payload.aircon_charges) || 0;
            setTxt('txt-basic-aircon', basicAircon.toLocaleString('en-US', {minimumFractionDigits:2}));
            setTxt('txt-aircon-total', (basicAircon * 1.12).toLocaleString('en-US', {minimumFractionDigits:2}));

            // 4. Lessee Profile Info
            setVal('inp-account-name', payload.company_name);
            setVal('inp-trade-name', payload.trade_name);
            setVal('inp-lessee-addr', payload.business_address || payload.owner_address);
            setVal('inp-use', payload.nature_of_business);
            setVal('inp-email', payload.email_address);
            setVal('inp-mobile', payload.contact_nos);
            setVal('inp-landline', payload.contact_nos); // Often sharing the same field or part of it
            setVal('inp-prop-name', payload.owner_lessee_name);
            setVal('inp-tin', payload.tin || '');

            // Set Nature of Business Radio
            if (payload.nature_of_business) setRadio('nat_business', payload.nature_of_business);

            // Attempt to split name for Single Proprietor fields
            if (payload.owner_lessee_name) {
                const parts = payload.owner_lessee_name.split(',');
                if (parts.length >= 2) {
                    setVal('inp-last-name', parts[0].trim());
                    const firstPart = parts[1].trim().split(' ');
                    setVal('inp-first-name', firstPart[0]);
                    if (firstPart.length > 1) setVal('inp-mi', firstPart[firstPart.length - 1]);
                }
            }

            // 5. Lease Terms
            if (payload.lease_period_start) {
                setVal('inp-commence-lease', payload.lease_period_start);
                setVal('inp-commence-rent', payload.lease_period_start);
            }
            if (payload.lease_period_end) {
                setVal('inp-expire-date', payload.lease_period_end);
            }

            if (payload.lease_period_notes) {
                const yrs = payload.lease_period_notes.match(/(\d+)\s*Year/i);
                const mos = payload.lease_period_notes.match(/(\d+)\s*Month/i);
                const dys = payload.lease_period_notes.match(/(\d+)\s*Day/i);
                if (yrs) setVal('inp-lease-years', yrs[1]);
                if (mos) setVal('inp-lease-months', mos[1]);
                if (dys) setVal('inp-lease-days', dys[1]);
            }

            // Update profile name
            setVal('inp-profile-name', payload.company_name || payload.owner_lessee_name);
        };

    </script>

    <!-- Logout Modal -->
    <div id="logout-modal" class="fixed inset-0 z-[120] flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeLogout()"></div>
        <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-10 max-w-sm w-full relative shadow-[0_32px_64px_-16px_rgba(0,0,0,0.3)] scale-95 opacity-0 transition-all duration-300" id="logout-modal-card">
            <div class="w-20 h-20 bg-rose-50 dark:bg-rose-500/10 rounded-3xl flex items-center justify-center text-rose-500 text-3xl mb-8 mx-auto">
                <i class="fa-solid fa-power-off"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-3 text-center">Ready to leave?</h3>
            <p class="text-slate-500 dark:text-slate-400 text-[15px] leading-relaxed mb-10 text-center">Your session will be ended securely. Please ensure any unsaved changes are saved.</p>
            <div class="flex flex-col gap-3">
                <a href="../logout.php" class="w-full py-4 bg-rose-500 text-white rounded-2xl font-bold text-sm text-center transition-all hover:bg-rose-600 shadow-xl shadow-rose-500/30 hover:-translate-y-0.5 active:translate-y-0">Yes, Sign Out</a>
                <button onclick="closeLogout()" class="w-full py-4 text-slate-400 dark:text-slate-500 font-bold text-sm transition-all hover:text-slate-600 dark:hover:text-slate-300">Maybe later</button>
            </div>
        </div>
    </div>

    <script>
        window.confirmLogout = function() {
            const m = document.getElementById('logout-modal');
            const c = document.getElementById('logout-modal-card');
            m.classList.remove('hidden');
            setTimeout(() => {
                c.style.opacity = '1';
                c.style.transform = 'scale(1)';
            }, 10);
        };
        window.closeLogout = function() {
            const m = document.getElementById('logout-modal');
            const c = document.getElementById('logout-modal-card');
            c.style.opacity = '0';
            c.style.transform = 'scale(0.95)';
            setTimeout(() => m.classList.add('hidden'), 300);
        };
    </script>
</body>

</html>