<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/security.php';
session_name(SESSION_NAME);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BroMan Social</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                brand: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' }
            }}}
        }
    </script>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #f8fafc; }
    </style>
    <!-- OneSignal Web SDK (Push Notifications - requires HTTPS) -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        if (location.protocol === 'https:' || location.hostname === 'localhost') {
            console.log('[OneSignal] Secure origin detected:', location.origin);
            window.OneSignalDeferred = window.OneSignalDeferred || [];
            window.syncOneSignalWorkspace = async function(OneSignal) {
                const userId = String(<?= (int)$_SESSION['user_id'] ?>);
                const companyId = String(<?= (int)($_SESSION['company_id'] ?? 1) ?>);

                await OneSignal.login(userId);
                await OneSignal.User.addAlias('workspace_user', `${userId}:${companyId}`);
            };

            window.detachOneSignalUser = async function() {
                try {
                    if (window.OneSignal && typeof window.OneSignal.logout === 'function') {
                        await window.OneSignal.logout();
                        return;
                    }

                    await new Promise((resolve) => {
                        window.OneSignalDeferred.push(async function(OneSignal) {
                            try {
                                if (typeof OneSignal.logout === 'function') {
                                    await OneSignal.logout();
                                }
                            } catch (logoutError) {
                                console.warn('[OneSignal] Logout warning:', logoutError);
                            }
                            resolve();
                        });
                    });
                } catch (e) {
                    console.warn('[OneSignal] Logout warning:', e);
                }
            };

            OneSignalDeferred.push(async function(OneSignal) {
                try {
                    console.log('[OneSignal] Starting init with appId: <?= ONESIGNAL_APP_ID ?>');
                    await OneSignal.init({
                        appId: "<?= ONESIGNAL_APP_ID ?>",
                        allowLocalhostAsSecureOrigin: true,
                        serviceWorkerParam: { scope: "/" },
                        serviceWorkerPath: "OneSignalSDKWorker.js"
                    });
                    console.log('[OneSignal] Init successful');
                    
                    const permission = OneSignal.Notifications.permission;
                    console.log('[OneSignal] Current permission:', permission);
                    
                    console.log('[OneSignal] Syncing user/workspace context');
                    await window.syncOneSignalWorkspace(OneSignal);
                    console.log('[OneSignal] Login successful');
                } catch(e) {
                    console.error('[OneSignal] ERROR:', e);
                }
            });
        } else {
            console.log('[OneSignal] Skipped - not a secure origin:', location.protocol, location.hostname);
        }
    </script>
    <?php endif; ?>
</head>
<body class="bg-slate-100 text-slate-700">
    <!-- Initial Loading Spinner -->
    <div id="appLoader" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-50 transition-opacity duration-300">
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 border-4 border-brand-200 border-t-brand-500 rounded-full animate-spin mb-4"></div>
            <div class="text-slate-500 font-medium animate-pulse">Loading BroMan Social...</div>
        </div>
    </div>
    
    <!-- New Layout: Sidebar + Main -->
    <div class="flex min-h-screen">
        
        <!-- Dark Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 -translate-x-full lg:translate-x-0 w-64 lg:w-16 lg:hover:w-56 transition-all duration-300 bg-[#0a1628] flex flex-col z-[60] group">
            <!-- Logo -->
            <div class="h-14 flex items-center justify-center border-b border-slate-700/50 px-0 overflow-hidden">
                <!-- Dynamic Company Logo -->
                <img id="sidebarLogo" src="images/Final_Logo White.png" alt="Company" class="h-11 max-w-[180px] object-contain hidden group-hover:block transition-all duration-300">
                <img id="sidebarLogoSmall" src="images/Final_Logo White.png" alt="Company" class="h-10 w-auto max-w-[58px] object-contain group-hover:hidden" style="image-rendering: -webkit-optimize-contrast;">
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 py-4 px-2 space-y-1">
                <button onclick="switchTab('dashboard'); closeSidebarOnMobile()" id="tabDashboard" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span class="text-sm font-medium whitespace-nowrap lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Dashboard</span>
                </button>
                <button onclick="switchTab('board'); closeSidebarOnMobile()" id="tabBoard" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    <span class="text-sm font-medium whitespace-nowrap lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Board</span>
                </button>
                <button onclick="switchTab('calendar'); closeSidebarOnMobile()" id="tabCalendar" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-sm font-medium whitespace-nowrap lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Calendar</span>
                </button>
                <button onclick="switchTab('ideas'); closeSidebarOnMobile()" id="tabIdeas" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span class="text-sm font-medium whitespace-nowrap lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">My Ideas</span>
                </button>
                <div id="adminLink" class="hidden">
                    <button onclick="switchTab('users'); closeSidebarOnMobile()" id="tabUsers" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="text-sm font-medium whitespace-nowrap lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Users</span>
                    </button>
                </div>
                <div id="managerLogsLink" class="hidden">
                    <a href="logs.php" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span class="text-sm font-medium whitespace-nowrap lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Activity Logs</span>
                    </a>
                </div>
            </nav>
            
            <!-- Bottom Actions -->
            <div class="p-2 border-t border-slate-700/50 flex flex-col gap-2">
                <!-- Company Switcher -->
                <div class="relative group/switcher w-full">
                    <button type="button" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors text-left" id="companySwitcherBtn" onclick="toggleCompanySwitcher()">
                        <div class="w-5 h-5 rounded flex-shrink-0 bg-white p-[2px] flex items-center justify-center">
                            <img src="<?= htmlspecialchars($_SESSION['company_logo'] ?? 'images/Final_Logo White.png') ?>" alt="" class="w-full h-full object-contain">
                        </div>
                        <div class="flex-1 flex items-center justify-between min-w-0 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                            <span class="text-sm font-medium truncate whitespace-nowrap"><?= htmlspecialchars($_SESSION['company_name'] ?? 'BroMan') ?></span>
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0 ml-2 lg:hidden lg:group-hover:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div class="absolute bottom-full left-0 w-48 mb-2 bg-[#0f1f38] border border-slate-700/50 rounded-lg shadow-xl shadow-black/50 overflow-hidden hidden z-50" id="companySwitcherMenu">
                        <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-800/50 border-b border-slate-700/50">Switch Workspace</div>
                        <div class="py-1 flex flex-col max-h-[40vh] overflow-y-auto custom-scrollbar" id="companySwitcherList">
                            <div class="px-4 py-2 text-sm text-slate-400 text-center">Loading...</div>
                        </div>
                    </div>
                </div>

                <button onclick="logout()" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="text-sm font-medium whitespace-nowrap lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Logout</span>
                </button>
            </div>
        </aside>
        
        <!-- Mobile Sidebar Overlay -->
        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden backdrop-blur-sm"></div>
        
        <!-- Main Content Area -->
        <div class="flex-1 lg:ml-16 w-full min-w-0 transition-all duration-300">
            <!-- Top Header -->
            <header class="h-14 bg-[#0a1628] border-b border-slate-700/50 sticky top-0 z-50 flex items-center justify-between px-4 lg:px-6">
                <!-- Mobile Menu Button -->
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex items-center gap-4">
                    <h1 id="pageTitle" class="text-base lg:text-lg font-semibold text-white truncate max-w-[120px] sm:max-w-none">Dashboard</h1>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <div class="relative hidden sm:block">
                        <input type="text" id="globalSearch" placeholder="Search..." class="w-32 lg:w-64 pl-10 pr-4 py-2 bg-white/10 border-0 rounded-lg text-sm text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:bg-white/20 transition-colors">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <!-- New Post Button -->
                    <button onclick="openCreateModal()" class="bg-brand-500 hover:bg-brand-600 text-white p-2 lg:px-4 lg:py-2 rounded-lg font-medium text-sm flex items-center gap-2 shadow-sm transition-all active:scale-95">
                        <svg class="w-5 h-5 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span class="hidden lg:inline text-right">New Post</span>
                    </button>
                    <!-- Notifications -->
                    <div class="relative">
                        <button onclick="toggleNotifications()" class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span id="notifBadge" class="hidden absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-medium">0</span>
                        </button>
                        <div id="notifDropdown" class="hidden fixed lg:absolute left-4 right-4 lg:left-auto lg:right-0 top-16 lg:top-full mt-2 lg:w-80 bg-white rounded-xl shadow-2xl border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center">
                                <span class="font-semibold text-slate-800">Notifications</span>
                                <button onclick="markAllRead()" class="text-xs text-brand-500 hover:underline">Mark all read</button>
                            </div>
                            <div id="notifList" class="max-h-80 overflow-y-auto"></div>
                        </div>
                    </div>
                    <!-- User -->
                    <div class="flex items-center gap-2 pl-4 border-l border-slate-700/50">
                        <div class="w-8 h-8 bg-brand-500 rounded-full flex items-center justify-center text-white font-medium text-sm" id="userAvatar">U</div>
                        <div class="hidden sm:block">
                            <div id="userName" class="text-white text-sm font-medium"></div>
                            <div id="userRole" class="text-slate-400 text-xs capitalize"></div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <main class="p-4 lg:p-6 min-h-[calc(100vh-3.5rem)]">
        <!-- Dashboard View - Advanced Analytics -->
        <div id="dashboardView" class="hidden max-w-7xl mx-auto">
            <!-- Pulse Header: Health + Main KPIs -->
            <div class="pulse-header flex flex-col lg:flex-row gap-6 lg:gap-8 items-center p-4 lg:p-6">
                <!-- Health Pulse -->
                <div class="flex items-center gap-6 lg:pr-8 lg:border-r border-white/10 w-full lg:w-auto">
                    <div class="relative w-24 h-24">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <circle class="text-white/10" stroke="currentColor" stroke-width="3" fill="none" r="16" cy="18" cx="18"></circle>
                            <path id="healthPath" class="text-emerald-400 perf-ring" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span id="healthScore" class="text-2xl font-bold tracking-tight">0</span>
                            <span class="text-[8px] uppercase tracking-widest text-white/50">Score</span>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold mb-0.5">Pipeline Pulse</h2>
                        <p id="healthLabel" class="text-white/60 text-xs font-medium uppercase tracking-wider">Analyzing stats...</p>
                        <div class="flex items-center gap-2 mt-2">
                             <select id="analyticsPeriod" onchange="loadDashboard()" class="bg-white/10 border-0 rounded-lg text-xs py-1 px-3 text-white focus:ring-1 focus:ring-brand-400 outline-none cursor-pointer">
                                <option value="7" class="bg-slate-800">Last 7d</option>
                                <option value="30" selected class="bg-slate-800">Last 30d</option>
                                <option value="90" class="bg-slate-800">Last 90d</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Snapshot Row -->
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                    <div class="glass-stat">
                        <div class="text-white/50 text-[10px] uppercase font-bold mb-1">Total Impact</div>
                        <div class="flex items-baseline gap-2">
                            <span id="kpiTotal" class="text-2xl font-bold">0</span>
                            <span class="text-white/30 text-[10px]">Posts</span>
                        </div>
                    </div>
                    <div class="glass-stat">
                        <div class="text-white/50 text-[10px] uppercase font-bold mb-1">Published</div>
                        <div class="flex items-baseline justify-between">
                            <span id="kpiPublished" class="text-2xl font-bold text-emerald-400">0</span>
                            <span id="kpiPublishedTrend" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400"></span>
                        </div>
                    </div>
                    <div class="glass-stat">
                        <div class="text-white/50 text-[10px] uppercase font-bold mb-1">Approval Velocity</div>
                        <div class="flex items-baseline justify-between">
                            <span id="kpiApprovalRate" class="text-2xl font-bold text-blue-400">0%</span>
                            <span id="kpiApprovalTrend" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-400"></span>
                        </div>
                    </div>
                    <div class="glass-stat">
                         <div class="text-white/50 text-[10px] uppercase font-bold mb-1">Queue Status</div>
                         <div class="flex items-center gap-3">
                            <div>
                                <div id="kpiPending" class="text-xl font-bold text-amber-400">0</div>
                                <div class="text-[8px] text-white/30 uppercase">Review</div>
                            </div>
                            <div class="w-px h-8 bg-white/10"></div>
                            <div>
                                <div id="kpiScheduled" class="text-xl font-bold text-indigo-400">0</div>
                                <div class="text-[8px] text-white/30 uppercase">Sched</div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Strategic Insights Row -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                <!-- Smart Recommendations -->
                <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <span class="p-1.5 bg-brand-50 text-brand-600 rounded-lg"><i class="fa-solid fa-lightbulb text-sm"></i></span>
                            Strategic Intelligence
                        </h3>
                    </div>
                    <div id="recommendationsSection" class="grid grid-cols-1 sm:grid-cols-2 gap-4"></div>
                </div>

                <!-- Delivery Efficiency -->
                <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col">
                    <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg"><i class="fa-solid fa-clock-rotate-left text-sm"></i></span>
                        Best Delivery
                    </h3>
                    <div class="flex-1 flex flex-col justify-center space-y-4">
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-tight">Peak Performance Day</div>
                                <div id="bestDay" class="text-xl font-bold text-slate-700">-</div>
                            </div>
                            <div id="bestDayCount" class="text-xs font-bold text-slate-400 bg-white px-2 py-1 rounded-lg border border-slate-100"></div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-tight">Prime Publishing Hour</div>
                                <div id="bestHour" class="text-xl font-bold text-slate-700">-</div>
                            </div>
                            <div id="bestHourCount" class="text-xs font-bold text-slate-400 bg-white px-2 py-1 rounded-lg border border-slate-100"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Flow & Monitoring Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Content Pipeline Evolution -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="font-bold text-slate-800">Pipeline Distribution</h3>
                        <div class="flex gap-2 text-[10px] font-bold text-slate-400">
                             <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-brand-500"></span> FLOW</span>
                        </div>
                    </div>
                    <div id="workflowFunnel" class="flex items-end justify-between h-40 lg:h-56 gap-2 lg:gap-4"></div>
                </div>
                
                <!-- Platform Dominance -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col">
                    <h3 class="font-bold text-slate-800 mb-6">Channel Authority</h3>
                    <div class="relative flex-1 flex items-center justify-center mb-4">
                        <div class="w-full h-40">
                            <canvas id="platformChart"></canvas>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="text-center">
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Multi</div>
                                <div class="text-sm font-bold text-slate-700">Platform</div>
                            </div>
                        </div>
                    </div>
                    <div id="platformLegend" class="grid grid-cols-2 gap-x-4 gap-y-2 mt-auto pt-4 border-t border-slate-50"></div>
                </div>
            </div>
            
            <!-- Employee Monitoring & Pipeline Health -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                <!-- Team Performance Monitoring - HIGH DENSITY -->
                <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="font-bold text-slate-800">Employee Performance Monitor</h3>
                            <p class="text-xs text-slate-400">Productivity tracking based on approved vs requested changes</p>
                        </div>
                        <span id="teamMemberCount" class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded-full uppercase"></span>
                    </div>
                    <div id="userPerformanceCards" class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:max-h-[600px] lg:overflow-y-auto pr-2 custom-scrollbar"></div>
                </div>
                
                <!-- Right Rail: Bottlenecks & Schedule -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Workflow Health Markers -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                        <h3 class="font-bold text-slate-800 mb-4 text-sm uppercase tracking-wider">Workflow Bottlenecks</h3>
                        <div id="bottleneckBars" class="space-y-4"></div>
                    </div>

                    <!-- Upcoming Timeline -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider text-indigo-600">Upcoming Post</h3>
                            <a href="#calendar" onclick="switchTab('calendar')" class="text-[10px] font-bold text-brand-500 hover:text-brand-600 uppercase tracking-widest">Full View →</a>
                        </div>
                        <div id="upcomingScheduled" class="space-y-3"></div>
                    </div>
                </div>
            </div>
            
            <!-- Global Activity Log -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Activity Feed
                    </h3>
                    <span id="activityCount" class="text-[10px] font-bold text-slate-400 uppercase"></span>
                </div>
                <div id="recentActivity" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-h-[800px] overflow-y-auto pr-2 custom-scrollbar"></div>
            </div>
        </div>

        <!-- Board View -->
        <div id="boardView" class="hidden">
            <!-- Filters Row -->
            <div class="mb-4 flex flex-col sm:flex-row flex-wrap gap-3 items-start sm:items-center">
                <select id="platformFilter" onchange="loadPosts()" class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Platforms</option>
                    <option value="Facebook">Facebook</option><option value="Instagram">Instagram</option><option value="LinkedIn">LinkedIn</option>
                    <option value="X">X</option><option value="TikTok">TikTok</option><option value="YouTube">YouTube</option><option value="Snapchat">Snapchat</option><option value="Website">Website</option>
                </select>
                <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                    <input type="checkbox" id="myPostsFilter" onchange="loadPosts()" class="rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                    My Posts
                </label>
                <div class="relative w-full sm:flex-1 sm:max-w-xs">
                    <input type="text" id="searchInput" placeholder="Search posts..." onkeyup="debounceSearch()" class="w-full px-4 py-2 pl-10 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            
            <!-- Status Tabs -->
            <div class="bg-white rounded-lg border border-slate-200 mb-6 overflow-hidden">
                <div class="flex overflow-x-auto scrollbar-none whitespace-nowrap border-b border-slate-200">
                    <button onclick="setStatusFilter('')" id="tabAll" class="status-tab px-3 py-2 text-xs font-bold text-slate-800 border-b-2 border-slate-800 bg-transparent transition-all flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-gradient-to-r from-violet-400 to-slate-400"></span>
                        All <span id="countAll" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('DRAFT')" id="tabDRAFT" class="status-tab px-3 py-2 text-xs font-medium text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                        Drafts <span id="countDRAFT" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('PENDING_REVIEW')" id="tabPENDING_REVIEW" class="status-tab px-3 py-2 text-xs font-medium text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        Pending <span id="countPENDING_REVIEW" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('REVIEWED')" id="tabREVIEWED" class="status-tab px-3 py-2 text-xs font-medium text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                        Reviewed <span id="countREVIEWED" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('APPROVED')" id="tabAPPROVED" class="status-tab px-3 py-2 text-xs font-medium text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Approved <span id="countAPPROVED" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('SCHEDULED')" id="tabSCHEDULED" class="status-tab px-3 py-2 text-xs font-medium text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                        Scheduled <span id="countSCHEDULED" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('PUBLISHED')" id="tabPUBLISHED" class="status-tab px-3 py-2 text-xs font-medium text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        Published <span id="countPUBLISHED" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                </div>
            </div>
            
            <!-- Posts Grid -->

            <div id="postsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Posts will be rendered here -->
            </div>
            
            <!-- Empty State -->
            <div id="emptyState" class="hidden text-center py-16">
                <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-slate-500 text-lg mb-2">No posts found</p>
                <p class="text-slate-400 text-sm">Try adjusting your filters or create a new post</p>
            </div>
        </div>

        <!-- Calendar View -->
        <div id="calendarView" class="hidden">
            <!-- Calendar Header with Stats -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <button onclick="prevMonth()" class="p-2 bg-slate-100 border border-slate-200 rounded-lg hover:bg-slate-200 text-slate-600 transition-colors">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <h2 id="currentMonth" class="text-xl font-bold text-slate-800 min-w-[180px] text-center"></h2>
                        <button onclick="nextMonth()" class="p-2 bg-slate-100 border border-slate-200 rounded-lg hover:bg-slate-200 text-slate-600 transition-colors">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                        <button onclick="goToToday()" class="px-3 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-medium transition-colors">
                            Today
                        </button>
                    </div>
                    <!-- Month Stats -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 rounded-lg border border-indigo-200">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span class="text-sm text-indigo-700"><span id="scheduledCount" class="font-bold">0</span> Scheduled</span>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 rounded-lg border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-sm text-emerald-700"><span id="publishedCount" class="font-bold">0</span> Published</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Platform Legend (Simplified) -->
            <div class="flex items-center gap-4 mb-4 text-xs text-slate-500">
                <span class="font-medium">Legend:</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> Scheduled</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Published</span>
            </div>
            
            <!-- Calendar Grid -->
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200">
                    <div class="py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Sun</div>
                    <div class="py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Mon</div>
                    <div class="py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Tue</div>
                    <div class="py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Wed</div>
                    <div class="py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Thu</div>
                    <div class="py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Fri</div>
                    <div class="py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Sat</div>
                </div>
                <div id="calendarGrid" class="grid grid-cols-7 divide-x divide-y divide-slate-100 bg-slate-100"></div>
            </div>
        </div>

        <!-- Users View (Admin Only) -->
        <div id="usersView" class="hidden max-w-7xl mx-auto px-4 lg:px-0">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 mb-8">
                <div class="flex gap-4 w-full lg:flex-1 overflow-x-auto pb-2 lg:pb-0 scrollbar-none">
                    <div class="bg-white rounded-xl px-4 lg:px-6 py-4 lg:py-5 border border-slate-200 shadow-sm flex-1 min-w-[140px] max-w-[200px]">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Users</div>
                        <div id="totalUsersCount" class="text-2xl lg:text-3xl font-bold text-slate-800 tracking-tight">0</div>
                    </div>
                    <div class="bg-white rounded-xl px-4 lg:px-6 py-4 lg:py-5 border border-slate-200 shadow-sm flex-1 min-w-[140px] max-w-[200px]">
                        <div class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-1">Active</div>
                        <div id="activeUsersCount" class="text-2xl lg:text-3xl font-bold text-emerald-600 tracking-tight">0</div>
                    </div>
                    <div class="bg-white rounded-xl px-4 lg:px-6 py-4 lg:py-5 border border-slate-200 shadow-sm flex-1 min-w-[140px] max-w-[200px]">
                         <div class="text-[10px] font-bold text-purple-500 uppercase tracking-wider mb-1">Admins</div>
                        <div id="adminUsersCount" class="text-2xl lg:text-3xl font-bold text-purple-600 tracking-tight">0</div>
                    </div>
                </div>
                <!-- User Actions Container -->
                <div id="userActionsContainer" class="w-full lg:w-auto"></div>
            </div>
            <div class="users-container">
                <!-- Desktop View -->
                <div class="hidden lg:block bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200">
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
                <!-- Mobile View -->
                <div id="usersMobileGrid" class="lg:hidden grid grid-cols-1 gap-4"></div>
            </div>
        </div>

        <!-- Ideas View (Personal Workspace) -->
        <div id="ideasView" class="hidden max-w-7xl mx-auto px-4 lg:px-0">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                        <span class="text-3xl">🧠</span> My Ideas
                    </h2>
                    <p class="text-slate-500 text-sm mt-1">Your personal brain space — jot down ideas without pressure</p>
                </div>
                <button onclick="openIdeaModal()" class="flex items-center gap-2 px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-medium text-sm shadow-lg shadow-violet-600/20 transition-all active:scale-95">
                    <i class="fa-solid fa-plus"></i>
                    New Idea
                </button>
            </div>
            
            <!-- Ideas Grid -->
            <div id="ideasGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Ideas will be rendered here -->
            </div>
            
            <!-- Empty State -->
            <div id="ideasEmptyState" class="hidden text-center py-16">
                <div class="text-6xl mb-4">💭</div>
                <p class="text-slate-500 text-lg mb-2">No ideas yet</p>
                <p class="text-slate-400 text-sm mb-6">Start capturing your thoughts — no pressure!</p>
                <button onclick="openIdeaModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-medium text-sm transition-colors">
                    <i class="fa-solid fa-plus"></i>
                    Create Your First Idea
                </button>
            </div>
        </div>
    </main>

    <!-- ==================== IDEA MODAL (Create/Edit) ==================== -->
    <div id="ideaModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-0 lg:p-4">
        <div class="bg-white rounded-none lg:rounded-xl shadow-2xl w-full max-w-xl h-full lg:h-auto lg:max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="bg-gradient-to-r from-violet-600 to-purple-600 px-6 py-4 flex justify-between items-center rounded-t-xl flex-shrink-0">
                <h2 id="ideaModalTitle" class="text-lg font-bold text-white flex items-center gap-2">
                    <span>💡</span> New Idea
                </h2>
                <button onclick="closeIdeaModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <!-- Content -->
            <form id="ideaForm" class="p-6 space-y-5 overflow-y-auto custom-scrollbar flex-1">
                <input type="hidden" id="ideaId">
                
                <!-- Title (Optional) -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Title (optional)</label>
                    <input type="text" id="ideaTitle" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all font-medium text-slate-800 placeholder-slate-400" placeholder="Give your idea a name...">
                </div>
                
                <!-- Content (Required) -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Brain Dump 💭</label>
                    <textarea id="ideaContent" rows="6" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all text-slate-700 placeholder-slate-400 resize-none" placeholder="Write freely... bullet points, links, emojis — anything goes! 🚀"></textarea>
                    <p class="text-xs text-slate-400 mt-2">No rules here. Just let your ideas flow.</p>
                </div>
                
                <!-- Media Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">📎 Attachments</label>
                    
                    <!-- Media Preview Grid -->
                    <div id="ideaMediaPreview" class="grid grid-cols-3 gap-2 mb-3"></div>
                    
                    <!-- Upload Zone -->
                    <div id="ideaDropZone" onclick="document.getElementById('ideaFileInput').click()" class="border-2 border-dashed border-slate-300 rounded-lg p-4 text-center cursor-pointer hover:border-violet-400 hover:bg-violet-50/50 transition-all">
                        <input type="file" id="ideaFileInput" class="hidden" accept="image/*,video/mp4,video/webm" multiple onchange="handleIdeaFileSelect(event)">
                        <div class="text-slate-400">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl mb-2"></i>
                            <p class="text-sm">Click or drag files here</p>
                            <p class="text-xs text-slate-400 mt-1">Images & Videos (Max 100MB each)</p>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl flex gap-3 flex-shrink-0">
                <button type="button" onclick="closeIdeaModal()" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-600 font-bold text-sm rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="button" onclick="saveIdea()" class="flex-1 px-5 py-2.5 bg-violet-600 text-white font-bold text-sm rounded-lg hover:bg-violet-700 transition-colors shadow-lg shadow-violet-600/20">Save Idea</button>
            </div>
        </div>
    </div>

    <!-- ==================== VIEW IDEA MODAL ==================== -->
    <div id="viewIdeaModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-0 lg:p-4">
        <div class="bg-white rounded-none lg:rounded-xl shadow-2xl w-full max-w-2xl h-full lg:h-auto lg:max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="bg-gradient-to-r from-violet-600 to-purple-600 px-6 py-4 flex justify-between items-center rounded-t-xl flex-shrink-0">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span>💡</span> <span id="viewIdeaTitle">Idea Details</span>
                </h2>
                <button onclick="closeViewIdeaModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                <!-- Media Gallery -->
                <div id="viewIdeaMediaGallery" class="mb-5"></div>
                
                <!-- Content Text -->
                <div id="viewIdeaContent" class="prose prose-slate max-w-none text-slate-700 whitespace-pre-wrap"></div>
                
                <!-- Meta Info -->
                <div id="viewIdeaMeta" class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-4 text-sm text-slate-400">
                    <span id="viewIdeaDate"></span>
                </div>
            </div>
            
            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl flex gap-3 flex-shrink-0">
                <button type="button" onclick="viewIdeaEdit()" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-600 font-bold text-sm rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </button>
                <button type="button" onclick="viewIdeaConvert()" class="flex-1 px-5 py-2.5 bg-violet-600 text-white font-bold text-sm rounded-lg hover:bg-violet-700 transition-colors shadow-lg shadow-violet-600/20 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Convert to Draft
                </button>
                <button type="button" onclick="viewIdeaDelete()" class="px-5 py-2.5 bg-white border border-red-200 text-red-500 font-bold text-sm rounded-lg hover:bg-red-50 transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== CREATE POST MODAL ==================== -->
    <div id="createModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-0 lg:p-4">
        <div class="bg-white rounded-none lg:rounded-xl shadow-2xl w-full max-w-2xl h-full lg:h-auto lg:max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="bg-white px-6 py-4 flex justify-between items-center rounded-t-xl border-b border-slate-100 flex-shrink-0">
                <h2 class="text-lg font-bold text-slate-800">New Post</h2>
                <button onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <!-- Scrollable Content -->
            <form id="createForm" class="p-6 space-y-6 overflow-y-auto custom-scrollbar">
                <!-- Title & Content -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Title</label>
                        <input type="text" id="createTitle" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-medium text-slate-800 placeholder-slate-400" placeholder="Post title...">
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                             <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Content</label>
                             <button type="button" onclick="toggleEmojiPicker('createContent', 'createEmojiBtn')" id="createEmojiBtn" class="text-slate-400 hover:text-brand-500 transition-colors text-sm">
                                <i class="fa-regular fa-face-smile"></i>
                             </button>
                        </div>
                        <div class="relative">
                            <textarea id="createContent" rows="4" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all text-slate-700 placeholder-slate-400" placeholder="What's on your mind?"></textarea>
                            <div id="createEmojiPickerContainer" class="absolute z-50 hidden mt-1 right-0 shadow-xl rounded-lg overflow-hidden border border-slate-200">
                                 <emoji-picker class="light"></emoji-picker>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platforms & Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Platforms</label>
                        <div class="grid grid-cols-4 gap-2" id="createPlatformsGrid">
                            <!-- Compact Platform Toggles -->
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="createPlatforms" value="Facebook" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all hover:bg-slate-50">
                                    <span class="text-blue-600 text-lg mb-1"><i class="fa-brands fa-facebook"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">FB</span>
                                </div>
                            </label>
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="createPlatforms" value="Instagram" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-pink-500 peer-checked:bg-pink-50 transition-all hover:bg-slate-50">
                                    <span class="text-pink-600 text-lg mb-1"><i class="fa-brands fa-instagram"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">IG</span>
                                </div>
                            </label>
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="createPlatforms" value="LinkedIn" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-blue-700 peer-checked:bg-blue-50 transition-all hover:bg-slate-50">
                                    <span class="text-blue-700 text-lg mb-1"><i class="fa-brands fa-linkedin"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">IN</span>
                                </div>
                            </label>
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="createPlatforms" value="X" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-black peer-checked:bg-slate-100 transition-all hover:bg-slate-50">
                                    <span class="text-black text-lg mb-1"><i class="fa-brands fa-x-twitter"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">X</span>
                                </div>
                            </label>
                            <!-- Row 2 -->
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="createPlatforms" value="TikTok" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-slate-800 peer-checked:bg-slate-100 transition-all hover:bg-slate-50">
                                    <span class="text-slate-800 text-lg mb-1"><i class="fa-brands fa-tiktok"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">TikTok</span>
                                </div>
                            </label>
                             <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="createPlatforms" value="YouTube" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-red-600 peer-checked:bg-red-50 transition-all hover:bg-slate-50">
                                    <span class="text-red-600 text-lg mb-1"><i class="fa-brands fa-youtube"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">YT</span>
                                </div>
                            </label>
                             <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="createPlatforms" value="Snapchat" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-yellow-400 peer-checked:bg-yellow-50 transition-all hover:bg-slate-50">
                                    <span class="text-yellow-500 text-lg mb-1"><i class="fa-brands fa-snapchat"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">Snap</span>
                                </div>
                            </label>
                             <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="createPlatforms" value="Website" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all hover:bg-slate-50">
                                    <span class="text-indigo-600 text-lg mb-1"><i class="fa-solid fa-globe"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">Web</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                         <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Initial Status</label>
                            <div class="relative">
                                <select id="createStatus" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 appearance-none font-medium text-slate-700">
                                    <option value="DRAFT">Draft</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                         
                         <label class="flex items-center gap-3 p-3 bg-red-50 border border-red-100 rounded-lg cursor-pointer hover:bg-red-100 transition-colors">
                            <input type="checkbox" id="createUrgent" class="w-4 h-4 text-red-600 rounded border-red-300 focus:ring-red-500">
                            <span class="text-sm font-bold text-red-700">Urgent Priority</span>
                        </label>
                    </div>
                </div>

                <!-- Media Upload -->
                <div>
                     <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Media</label>
                    <div id="createMediaGallery" class="grid grid-cols-4 gap-2 mb-3 empty:hidden"></div>
                    <div class="group rounded-lg p-6 text-center cursor-pointer border border-dashed border-slate-300 hover:border-brand-400 hover:bg-brand-50/30 transition-all" onclick="document.getElementById('createFileInput').click()">
                        <input type="file" id="createFileInput" class="hidden" accept="image/*,video/*" multiple onchange="previewCreateFiles(event)">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center group-hover:bg-brand-100 group-hover:text-brand-500 transition-colors">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-600 group-hover:text-brand-600">Click to upload media</p>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl flex gap-3 flex-shrink-0">
                <button type="button" onclick="closeCreateModal()" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-600 font-bold text-sm rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="button" onclick="document.getElementById('createForm').dispatchEvent(new Event('submit'))" class="flex-1 px-5 py-2.5 bg-slate-900 text-white font-bold text-sm rounded-lg hover:bg-slate-800 transition-colors shadow-lg shadow-slate-900/10">Create Post</button>
            </div>
        </div>
    </div>

    <!-- ==================== VIEW POST MODAL (Read-Only) ==================== -->
    <div id="viewModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[60] flex items-center justify-center p-0 lg:p-8">
        <div id="viewModalContainer" class="bg-white rounded-none lg:rounded-2xl shadow-2xl w-full max-w-7xl h-full lg:h-[85vh] flex flex-col lg:flex-row overflow-hidden border border-slate-100 text-right">
            
            <!-- Left Column: Media (Dark Mode) - Reduced Width -->
            <div id="viewMediaColumn" class="hidden lg:w-[48%] xl:w-[48%] flex-shrink-0 bg-slate-900 items-center justify-center relative group border-l border-slate-100">
                 <div id="viewMediaWrapper" class="w-full h-full flex items-center justify-center p-4"></div>
            </div>

            <!-- Right Column: Content & Details - Increased Width -->
            <div id="viewContentColumn" class="flex-1 flex flex-col bg-white h-full relative w-full">
                
                <!-- Fixed Header (Sticky) -->
                <div class="px-4 lg:px-6 py-4 flex justify-between items-center border-b border-slate-100 bg-white flex-shrink-0 gap-4 z-10 sticky top-0">
                     <!-- Status & Platforms -->
                    <div class="flex items-center gap-3 overflow-hidden flex-1 min-w-0">
                        <span id="viewStatusBadge" class="status-badge px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 flex-shrink-0">DRAFT</span>
                        <div id="viewPlatformBadge" class="flex items-center gap-1.5 overflow-x-auto scrollbar-none mask-linear-fade pl-4"></div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button id="viewEditBtn" onclick="switchToEditMode()" class="text-slate-400 hover:text-brand-600 hover:bg-brand-50 p-2 rounded-lg transition-all" title="Edit Post">
                            <i class="fa-solid fa-pen text-sm"></i>
                        </button>
                        <button id="viewDeleteBtn" onclick="deletePost()" class="text-slate-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-lg transition-all" title="Delete Post">
                             <i class="fa-solid fa-trash text-sm"></i>
                        </button>
                        <div class="w-px h-6 bg-slate-200 mx-1"></div>
                        <button onclick="closeViewModal()" class="text-slate-400 hover:text-slate-600 hover:bg-slate-50 p-2 rounded-lg transition-colors">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
                    <!-- Mobile Media Fallback -->
                    <div id="viewMediaMobile" class="lg:hidden bg-slate-100 border-b border-slate-200"></div>

                    <div class="p-8 space-y-8">
                        <!-- Header Section: Title & Meta -->
                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-4">
                                <h1 id="viewTitle" class="text-xl font-bold text-slate-900 leading-snug tracking-tight"></h1>
                                <span id="viewUrgentBadge" class="hidden flex-shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-rose-50 text-rose-600 border border-rose-100">
                                    <i class="fa-solid fa-bolt text-[9px]"></i> Urgent
                                </span>
                            </div>
                            
                            <!-- Detailed Meta Row -->
                            <div class="flex items-center flex-wrap gap-4 text-xs font-medium text-slate-500 border-b border-slate-100 pb-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <span id="viewAuthor" class="text-slate-700"></span>
                                </div>
                                <div class="w-1 h-1 rounded-full bg-slate-300"></div>
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-slate-400"></i>
                                    <span id="viewDate"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Changes Notice -->
                        <div id="viewChangesNotice" class="hidden">
                            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4 flex gap-3 items-start">
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
                                <div>
                                    <h4 class="text-sm font-bold text-amber-800">Changes Requested</h4>
                                    <p id="viewChangesReason" class="text-sm text-amber-700 mt-1 leading-relaxed"></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Main Content -->
                        <div class="prose prose-slate max-w-none prose-p:leading-relaxed prose-p:text-slate-600 prose-headings:font-bold prose-headings:tracking-tight prose-a:text-brand-600">
                            <p id="viewContent" class="whitespace-pre-wrap"></p>
                        </div>
                        

                        <!-- Action Area -->
                        <div id="viewActions" class="pt-2">
                            <div id="actionButtons" class="flex flex-wrap gap-3"></div>
                        </div>
                        
                        <!-- Stacked Sections (Discussion & History) -->
                        <div class="flex flex-col gap-8 pt-8 border-t border-slate-200">
                             <!-- Comments Section -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <i class="fa-regular fa-comments text-slate-400"></i> Discussion
                                    </h3>
                                    <span id="viewCommentCount" class="text-xs font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">0</span>
                                </div>
                                
                                <div id="viewComments" class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar"></div>
                                
                                <div class="sticky bottom-0 bg-white pt-2">
                                    <div class="relative">
                                        <input type="text" id="viewNewComment" placeholder="Write a comment..." class="w-full pl-4 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all shadow-sm" onkeypress="if(event.key==='Enter')addViewComment()">
                                        <button onclick="addViewComment()" class="absolute right-1.5 top-1.5 p-1.5 text-brand-600 hover:bg-brand-50 rounded-md transition-colors">
                                            <i class="fa-solid fa-paper-plane text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Activity Timeline -->
                            <div class="space-y-4 pt-8 border-t border-slate-100">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-4">
                                    <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> History
                                </h3>
                                <div id="viewActivity" class="relative space-y-6 before:absolute before:left-[5px] before:top-2 before:bottom-0 before:w-px before:bg-slate-200 ml-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== EDIT POST MODAL ==================== -->
    <div id="editModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col my-8">
            <!-- Header -->
            <div class="bg-white px-6 py-4 flex justify-between items-center rounded-t-xl border-b border-slate-100 flex-shrink-0">
                <h2 class="text-lg font-bold text-slate-800">Edit Post</h2>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                     <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <form id="editForm" class="p-6 space-y-6 overflow-y-auto custom-scrollbar">
                <input type="hidden" id="editPostId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Title</label>
                        <input type="text" id="editTitle" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-medium text-slate-800">
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                             <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Content</label>
                             <button type="button" onclick="toggleEmojiPicker('editContent', 'editEmojiBtn')" id="editEmojiBtn" class="text-slate-400 hover:text-brand-500 transition-colors text-sm">
                                <i class="fa-regular fa-face-smile"></i>
                             </button>
                        </div>
                        <div class="relative">
                            <textarea id="editContent" rows="5" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all text-slate-700"></textarea>
                            <div id="editEmojiPickerContainer" class="absolute z-50 hidden mt-1 right-0 shadow-xl rounded-lg overflow-hidden border border-slate-200">
                                 <emoji-picker class="light"></emoji-picker>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Platforms</label>
                        <div class="grid grid-cols-4 gap-2" id="editPlatformsGrid">
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="editPlatforms" value="Facebook" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all hover:bg-slate-50">
                                    <span class="text-blue-600 text-lg mb-1"><i class="fa-brands fa-facebook"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">FB</span>
                                </div>
                            </label>
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="editPlatforms" value="Instagram" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-pink-500 peer-checked:bg-pink-50 transition-all hover:bg-slate-50">
                                    <span class="text-pink-600 text-lg mb-1"><i class="fa-brands fa-instagram"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">IG</span>
                                </div>
                            </label>
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="editPlatforms" value="LinkedIn" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-blue-700 peer-checked:bg-blue-50 transition-all hover:bg-slate-50">
                                    <span class="text-blue-700 text-lg mb-1"><i class="fa-brands fa-linkedin"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">IN</span>
                                </div>
                            </label>
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="editPlatforms" value="X" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-black peer-checked:bg-slate-100 transition-all hover:bg-slate-50">
                                    <span class="text-black text-lg mb-1"><i class="fa-brands fa-x-twitter"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">X</span>
                                </div>
                            </label>
                            <!-- Row 2 -->
                            <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="editPlatforms" value="TikTok" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-slate-800 peer-checked:bg-slate-100 transition-all hover:bg-slate-50">
                                    <span class="text-slate-800 text-lg mb-1"><i class="fa-brands fa-tiktok"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">TikTok</span>
                                </div>
                            </label>
                             <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="editPlatforms" value="YouTube" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-red-600 peer-checked:bg-red-50 transition-all hover:bg-slate-50">
                                    <span class="text-red-600 text-lg mb-1"><i class="fa-brands fa-youtube"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">YT</span>
                                </div>
                            </label>
                             <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="editPlatforms" value="Snapchat" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-yellow-400 peer-checked:bg-yellow-50 transition-all hover:bg-slate-50">
                                    <span class="text-yellow-500 text-lg mb-1"><i class="fa-brands fa-snapchat"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">Snap</span>
                                </div>
                            </label>
                             <label class="platform-checkbox cursor-pointer">
                                <input type="checkbox" name="editPlatforms" value="Website" class="hidden peer">
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all hover:bg-slate-50">
                                    <span class="text-indigo-600 text-lg mb-1"><i class="fa-solid fa-globe"></i></span>
                                    <span class="text-[10px] font-medium text-slate-600">Web</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                 <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editUrgent" class="w-5 h-5 text-red-500 rounded focus:ring-red-500">
                        <span class="text-sm font-medium text-slate-700">🔥 Urgent Priority</span>
                    </label>
                </div>

                <!-- Media Gallery -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Media</label>
                    <div id="editMediaGallery" class="grid grid-cols-4 gap-3 mb-3 empty:hidden"></div>
                    <div class="group rounded-lg p-6 text-center cursor-pointer border border-dashed border-slate-300 hover:border-brand-400 hover:bg-brand-50/30 transition-all" onclick="document.getElementById('editFileInput').click()">
                        <input type="file" id="editFileInput" class="hidden" accept="image/*,video/*" onchange="uploadEditFile(event)" multiple>
                         <div class="flex flex-col items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center group-hover:bg-brand-100 group-hover:text-brand-500 transition-colors">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-600 group-hover:text-brand-600">Add more media</p>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl flex gap-3 flex-shrink-0">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-600 font-bold text-sm rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="button" onclick="document.getElementById('editForm').dispatchEvent(new Event('submit'))" class="flex-1 px-5 py-2.5 bg-slate-900 text-white font-bold text-sm rounded-lg hover:bg-slate-800 transition-colors shadow-lg shadow-slate-900/10">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- ==================== REQUEST CHANGES MODAL ==================== -->
    <div id="changesModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/50 modal-backdrop z-[70] flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 border border-slate-200">
            <h3 class="text-lg font-semibold mb-4 text-slate-800">Request Changes</h3>
            <p class="text-slate-500 text-sm mb-4">Please explain what changes are needed. This feedback will be visible to the post author.</p>
            <textarea id="changesReasonInput" rows="4" class="w-full px-4 py-3 border border-slate-200 rounded-lg mb-4 focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none" placeholder="What changes are needed?"></textarea>
            <div class="flex gap-3">
                <button onclick="confirmRequestChanges()" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl">Request Changes</button>
                <button onclick="closeChangesModal()" class="px-6 bg-slate-200 hover:bg-slate-300 py-3 rounded-xl font-semibold">Cancel</button>
            </div>
        </div>
    </div>

    <!-- ==================== SCHEDULE MODAL ==================== -->
    <div id="scheduleModal" dir="rtl" class="hidden fixed inset-0 bg-black/60 z-[70] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold mb-4 flex items-center gap-2">📅 Schedule for Publishing</h3>
            <p class="text-slate-600 text-sm mb-4">Select when this post should be published. The post will automatically move to Published at the scheduled time.</p>
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Publish Date & Time</label>
                <input type="datetime-local" id="scheduleDateTime" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex gap-3">
                <button onclick="confirmSchedule()" class="flex-1 bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-3 rounded-xl">Schedule Post</button>
                <button onclick="closeScheduleModal()" class="px-6 bg-slate-200 hover:bg-slate-300 py-3 rounded-xl font-semibold">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toasts" class="fixed bottom-4 right-4 z-[9999] space-y-2"></div>

    <!-- Edit User Modal -->
    <div id="editUserModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-slate-100">
            <div class="bg-white px-6 py-4 flex justify-between items-center rounded-t-xl border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800">Edit User</h2>
                <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <form id="editUserForm" class="p-6 space-y-5">
                <input type="hidden" id="editUserId">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Username</label>
                    <input type="text" id="editUserUsername" disabled class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-500 font-medium cursor-not-allowed">
                </div>
                <div>
                     <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Name</label>
                    <input type="text" id="editUserFullName" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-medium text-slate-800">
                </div>
                <div>
                     <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Role</label>
                    <div class="relative">
                        <select id="editUserRole" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 appearance-none font-medium text-slate-700">
                            <option value="staff">Staff</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div>
                     <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">New Password <span class="text-slate-400 font-normal normal-case">(optional)</span></label>
                    <input type="password" id="editUserPassword" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-medium text-slate-800" placeholder="••••••••">
                </div>
                <div class="pt-2 flex gap-3">
                    <button type="button" onclick="closeEditUserModal()" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-600 font-bold text-sm rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-slate-900 text-white font-bold text-sm rounded-lg hover:bg-slate-800 transition-colors shadow-lg shadow-slate-900/10">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-slate-100">
            <div class="bg-white px-6 py-4 flex justify-between items-center rounded-t-xl border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800">Add New User</h2>
                <button onclick="closeAddUserModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <form id="addUserForm" class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Username</label>
                    <input type="text" id="addUserUsername" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-medium text-slate-800" placeholder="username">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Name</label>
                    <input type="text" id="addUserFullName" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-medium text-slate-800" placeholder="Full Name">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Role</label>
                    <div class="relative">
                        <select id="addUserRole" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 appearance-none font-medium text-slate-700">
                            <option value="staff">Staff</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" id="addUserPassword" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-medium text-slate-800" placeholder="••••••••">
                </div>
                <div class="pt-2 flex gap-3">
                    <button type="button" onclick="closeAddUserModal()" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-600 font-bold text-sm rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-slate-900 text-white font-bold text-sm rounded-lg hover:bg-slate-800 transition-colors shadow-lg shadow-slate-900/10">Create User</button>
                </div>
            </form>
        </div>
    </div>

<script>
const app = { user: null, posts: [], currentPost: null, lastUnreadCount: 0, notificationsInitialized: false };
const STATUS_LIST = ['DRAFT', 'PENDING_REVIEW', 'REVIEWED', 'APPROVED', 'SCHEDULED', 'PUBLISHED'];
const STATUS_COLORS = {
    'DRAFT': 'bg-sky-100 text-sky-700',
    'PENDING_REVIEW': 'bg-amber-100 text-amber-700',
    'REVIEWED': 'bg-orange-100 text-orange-700',
    'CHANGES_REQUESTED': 'bg-red-100 text-red-700',
    'APPROVED': 'bg-emerald-100 text-emerald-700',
    'SCHEDULED': 'bg-indigo-100 text-indigo-700',
    'PUBLISHED': 'bg-slate-100 text-slate-600'
};
const STATUS_LABELS = {
    'DRAFT': 'Draft',
    'PENDING_REVIEW': 'In Review',
    'REVIEWED': 'Reviewed',
    'CHANGES_REQUESTED': 'Changes',
    'APPROVED': 'Approved',
    'SCHEDULED': 'Scheduled',
    'PUBLISHED': 'Published'
};
const PLATFORM_COLORS = {
    Facebook: 'bg-blue-600', Instagram: 'bg-gradient-to-r from-purple-500 to-pink-500', LinkedIn: 'bg-blue-700',
    X: 'bg-black', TikTok: 'bg-slate-800', YouTube: 'bg-red-600', Snapchat: 'bg-yellow-400', Website: 'bg-indigo-500'
};
const PLATFORM_ICONS = {
    Facebook: 'fa-brands fa-facebook', Instagram: 'fa-brands fa-instagram', LinkedIn: 'fa-brands fa-linkedin',
    X: 'fa-brands fa-x-twitter', TikTok: 'fa-brands fa-tiktok', YouTube: 'fa-brands fa-youtube',
    Snapchat: 'fa-brands fa-snapchat', Website: 'fa-solid fa-globe'
};

function isVideoFile(path) {
    if (!path) return false;
    const ext = path.split('.').pop().toLowerCase();
    return ['mp4', 'webm', 'ogg', 'mov'].includes(ext);
}

async function init() {
    try {
        // Ensure loader is visible (though it is by default in HTML)
        const loader = document.getElementById('appLoader');
        if (loader) loader.classList.remove('hidden');

        await loadUser();
        // Parallelize these for faster loading
        await Promise.all([loadPosts(), loadNotifications()]);
    
        // Get tab from URL hash or default to 'board'
        const validTabs = ['dashboard', 'board', 'calendar', 'users'];
        let initialTab = window.location.hash.replace('#', '');
        if (!validTabs.includes(initialTab)) initialTab = 'board';
        
        // Check if unauthorized role trying to access users tab
        if (initialTab === 'users' && !['admin', 'manager'].includes(app.user?.role?.toLowerCase())) initialTab = 'board';
        
        switchTab(initialTab);

        // Start polling: Notifications (5s), Posts (10s)
        setInterval(loadNotifications, 5000);
        setInterval(() => loadPosts(true), 10000);
        
        // Initialize audio context on first user interaction
        const unlockAudio = () => {
            initAudio();
            // Remove listeners once activated
            document.removeEventListener('click', unlockAudio);
            document.removeEventListener('keydown', unlockAudio);
        };
        document.addEventListener('click', unlockAudio);
        document.addEventListener('keydown', unlockAudio);

    } catch (error) {
        console.error("Initialization failed:", error);
        toast('Failed to load application data', 'error');
    } finally {
        // Fade out loader
        const loader = document.getElementById('appLoader');
        if (loader) {
            loader.classList.add('opacity-0');
            setTimeout(() => loader.classList.add('hidden'), 300);
        }
    }
}

// Handle browser back/forward buttons
window.addEventListener('hashchange', function() {
    const validTabs = ['dashboard', 'board', 'calendar', 'users'];
    let tab = window.location.hash.replace('#', '');
    if (!validTabs.includes(tab)) tab = 'board';
    if (tab === 'users' && !['admin', 'manager'].includes(app.user?.role?.toLowerCase())) tab = 'board';
    switchTab(tab);
});

async function api(action, method = 'GET', body = null) {
    const opts = { method, headers: {} };
    // Add CSRF token for non-GET requests
    if (method !== 'GET') {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) opts.headers['X-CSRF-TOKEN'] = csrfToken;
    }
    if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    
    // Prevent caching for GET requests
    let url = `api.php?action=${action}`;
    if (method === 'GET') {
        url += `&_t=${Date.now()}`;
    }

    const res = await fetch(url, opts);
    return res.json();
}

async function loadUser() {
    const data = await api('get_user');
    if (data.success) {
        app.user = data.data;
        document.getElementById('userName').textContent = data.data.full_name || data.data.username;
        document.getElementById('userRole').textContent = data.data.role;
        document.getElementById('userAvatar').textContent = (data.data.full_name || data.data.username)[0].toUpperCase();
        if (['admin', 'manager'].includes(data.data.role)) document.getElementById('adminLink').classList.remove('hidden');
        if (data.data.role === 'manager') document.getElementById('managerLogsLink').classList.remove('hidden');
        
        // Update company branding (use white logo for dark sidebar)
        if (data.data.company_logo) {
            // Convert to white logo version for dark sidebar background
            let sidebarLogo = data.data.company_logo;
            // For BroMan, use white logo in sidebar
            // For BroMan, use white logo in sidebar
            if (sidebarLogo.includes('Final_Logo.png')) {
                sidebarLogo = sidebarLogo.replace('Final_Logo.png', 'Final_Logo White.png');
            } else if (sidebarLogo.includes('logo_BFM2.svg')) {
                sidebarLogo = sidebarLogo.replace('logo_BFM2.svg', 'logo_BFM.svg');
            }
            document.getElementById('sidebarLogo').src = sidebarLogo;
            document.getElementById('sidebarLogoSmall').src = sidebarLogo;
        }
        if (data.data.company_name) {
            document.title = data.data.company_name + ' - Social Management';
        }
    } else {
        location.href = 'login.php';
    }
}

async function logout() {
    if (typeof window.detachOneSignalUser === 'function') {
        await window.detachOneSignalUser();
    }
    await api('logout', 'POST');
    location.href = 'login.php';
}

function switchTab(tab) {
    const allTabs = ['dashboard', 'board', 'calendar', 'ideas', 'users'];
    const titles = { dashboard: 'Dashboard', board: 'Content Board', calendar: 'Calendar', ideas: 'My Ideas', users: 'User Management' };
    
    allTabs.forEach(t => {
        const view = document.getElementById(t + 'View');
        if (view) view.classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
        if (btn) {
            btn.className = t === tab 
                ? 'sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-white bg-white/10 transition-colors'
                : 'sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors';
        }
    });
    
    document.getElementById('pageTitle').textContent = titles[tab] || tab;
    
    // Update URL hash without triggering hashchange event
    if (window.location.hash !== '#' + tab) {
        history.replaceState(null, '', '#' + tab);
    }
    
    if (tab === 'dashboard') loadDashboard();
    if (tab === 'calendar') loadCalendar();
    if (tab === 'ideas') loadIdeas();
    if (tab === 'users') loadUsers();
}

// ==================== IDEAS WORKSPACE ====================
let userIdeas = [];

async function loadIdeas() {
    try {
        const data = await api('get_user_ideas');
        if (!data.success) return;
        
        userIdeas = data.data || [];
        renderIdeas();
    } catch (error) {
        console.error('Failed to load ideas:', error);
    }
}

function renderIdeas() {
    const grid = document.getElementById('ideasGrid');
    const emptyState = document.getElementById('ideasEmptyState');
    
    if (userIdeas.length === 0) {
        grid.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    grid.innerHTML = userIdeas.map(idea => {
        const title = idea.title || 'Untitled Idea';
        const content = idea.content || '';
        const preview = content.length > 100 ? content.substring(0, 100) + '...' : content;
        const date = new Date(idea.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const media = idea.media || [];
        const hasMedia = media.length > 0;
        const primaryMedia = media.find(m => m.is_primary) || media[0];
        const isVideo = primaryMedia?.file_type?.startsWith('video/');
        
        // Media thumbnail section
        let mediaThumbnail = '';
        if (hasMedia && primaryMedia) {
            mediaThumbnail = isVideo 
                ? `<div class="relative mb-3 rounded-lg overflow-hidden bg-slate-100 aspect-video">
                     <video src="${primaryMedia.file_path}" class="w-full h-full object-cover"></video>
                     <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                       <i class="fa-solid fa-play text-white text-xl"></i>
                     </div>
                     ${media.length > 1 ? `<span class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-1.5 py-0.5 rounded">+${media.length - 1}</span>` : ''}
                   </div>`
                : `<div class="relative mb-3 rounded-lg overflow-hidden bg-slate-100 aspect-video">
                     <img src="${primaryMedia.file_path}" class="w-full h-full object-cover" alt="">
                     ${media.length > 1 ? `<span class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-1.5 py-0.5 rounded">+${media.length - 1}</span>` : ''}
                   </div>`;
        }
        
        return `
            <div class="group bg-white rounded-xl border border-slate-200 hover:border-violet-300 hover:shadow-lg transition-all p-5 flex flex-col cursor-pointer" onclick="viewIdea(${idea.id})">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-slate-800 text-sm line-clamp-1">${escapeHtml(title)}</h3>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="event.stopPropagation(); editIdea(${idea.id})" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>
                        <button onclick="event.stopPropagation(); deleteIdea(${idea.id})" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors" title="Delete">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
                ${mediaThumbnail}
                <p class="text-slate-500 text-sm flex-1 mb-4 whitespace-pre-line line-clamp-3">${escapeHtml(preview)}</p>
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <span class="text-xs text-slate-400">${date}${hasMedia ? ` • <i class="fa-solid fa-paperclip"></i> ${media.length}` : ''}</span>
                    <button onclick="event.stopPropagation(); convertIdeaToDraft(${idea.id})" class="text-xs font-medium text-violet-600 hover:text-violet-700 flex items-center gap-1 transition-colors">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        Convert to Draft
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

let currentIdeaMedia = [];

function openIdeaModal(ideaId = null) {
    document.getElementById('ideaId').value = ideaId || '';
    document.getElementById('ideaTitle').value = '';
    document.getElementById('ideaContent').value = '';
    currentIdeaMedia = [];
    renderIdeaMediaPreview();
    document.getElementById('ideaModalTitle').innerHTML = ideaId ? '<span>✏️</span> Edit Idea' : '<span>💡</span> New Idea';
    document.getElementById('ideaModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeIdeaModal() {
    document.getElementById('ideaModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentIdeaMedia = [];
}

async function saveIdea() {
    const id = document.getElementById('ideaId').value;
    const title = document.getElementById('ideaTitle').value.trim();
    const content = document.getElementById('ideaContent').value.trim();
    
    if (!content) {
        alert('Please write something in your idea!');
        return;
    }
    
    try {
        const action = id ? 'update_idea' : 'create_idea';
        const payload = { title, content };
        if (id) payload.id = parseInt(id);
        
        const data = await api(action, 'POST', payload);
        
        if (data.success) {
            closeIdeaModal();
            loadIdeas();
        } else {
            alert(data.message || 'Failed to save idea');
        }
    } catch (error) {
        console.error('Save idea error:', error);
        alert('An error occurred while saving your idea');
    }
}

function editIdea(ideaId) {
    const idea = userIdeas.find(i => i.id == ideaId);
    if (!idea) return;
    
    document.getElementById('ideaId').value = idea.id;
    document.getElementById('ideaTitle').value = idea.title || '';
    document.getElementById('ideaContent').value = idea.content || '';
    currentIdeaMedia = idea.media || [];
    renderIdeaMediaPreview();
    document.getElementById('ideaModalTitle').innerHTML = '<span>✏️</span> Edit Idea';
    document.getElementById('ideaModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

async function deleteIdea(ideaId) {
    if (!confirm('Delete this idea? This cannot be undone.')) return;
    
    try {
        const data = await api(`delete_idea&id=${ideaId}`, 'GET');
        if (data.success) {
            loadIdeas();
        } else {
            alert(data.message || 'Failed to delete idea');
        }
    } catch (error) {
        console.error('Delete idea error:', error);
        alert('An error occurred while deleting the idea');
    }
}

async function convertIdeaToDraft(ideaId) {
    if (!confirm('Convert this idea to a Draft post?\n\nThis will create a new Draft in your workflow. You can choose to keep or delete the original idea.')) return;
    
    const deleteAfter = confirm('Do you want to delete the idea after converting?\n\nClick OK to delete, Cancel to keep it.');
    
    try {
        const data = await api('convert_idea_to_draft', 'POST', { idea_id: ideaId, delete_idea: deleteAfter });
        
        if (data.success) {
            alert('✅ Idea converted to Draft successfully!\n\nSwitch to the Board to see your new draft.');
            if (deleteAfter) {
                loadIdeas();
            }
        } else {
            alert(data.message || 'Failed to convert idea');
        }
    } catch (error) {
        console.error('Convert idea error:', error);
        alert('An error occurred while converting the idea');
    }
}

// --- Idea Media Functions ---

function renderIdeaMediaPreview() {
    const container = document.getElementById('ideaMediaPreview');
    if (!container) return;
    
    if (currentIdeaMedia.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    container.innerHTML = currentIdeaMedia.map(m => {
        const isVideo = m.file_type?.startsWith('video/');
        return `
            <div class="relative group rounded-lg overflow-hidden bg-slate-100 aspect-square">
                ${isVideo 
                    ? `<video src="${m.file_path}" class="w-full h-full object-cover"></video>
                       <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                         <i class="fa-solid fa-play text-white text-sm"></i>
                       </div>`
                    : `<img src="${m.file_path}" class="w-full h-full object-cover" alt="">`
                }
                <button onclick="deleteIdeaMedia(${m.id})" class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        `;
    }).join('');
}

async function handleIdeaFileSelect(event) {
    const files = event.target.files;
    if (!files || files.length === 0) return;
    
    const ideaId = document.getElementById('ideaId').value;
    
    // If no idea ID yet, we need to create the idea first
    if (!ideaId) {
        const title = document.getElementById('ideaTitle').value.trim();
        const content = document.getElementById('ideaContent').value.trim() || 'New idea with attachments';
        
        // Create idea first
        const createData = await api('create_idea', 'POST', { title, content });
        if (!createData.success) {
            alert('Please save the idea first before adding files');
            return;
        }
        
        document.getElementById('ideaId').value = createData.data.id;
        document.getElementById('ideaContent').value = content;
    }
    
    const currentIdeaId = document.getElementById('ideaId').value;
    
    for (const file of files) {
        await uploadIdeaMedia(currentIdeaId, file);
    }
    
    // Reload ideas to get updated media list
    await loadIdeas();
    const idea = userIdeas.find(i => i.id == currentIdeaId);
    if (idea) {
        currentIdeaMedia = idea.media || [];
        renderIdeaMediaPreview();
    }
    
    // Clear file input
    event.target.value = '';
}

async function uploadIdeaMedia(ideaId, file) {
    const formData = new FormData();
    formData.append('idea_id', ideaId);
    formData.append('file', file);
    
    try {
        const response = await fetch(`api.php?action=upload_idea_media`, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        
        const data = await response.json();
        if (!data.success) {
            alert(data.message || 'Failed to upload file');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Failed to upload file');
    }
}

async function deleteIdeaMedia(mediaId) {
    if (!confirm('Delete this attachment?')) return;
    
    try {
        const data = await api(`delete_idea_media&id=${mediaId}`, 'GET');
        if (data.success) {
            currentIdeaMedia = currentIdeaMedia.filter(m => m.id != mediaId);
            renderIdeaMediaPreview();
            loadIdeas(); // Refresh in background
        } else {
            alert(data.message || 'Failed to delete attachment');
        }
    } catch (error) {
        console.error('Delete media error:', error);
        alert('Failed to delete attachment');
    }
}

// --- View Idea Modal Functions ---
let currentViewIdea = null;

function viewIdea(ideaId) {
    const idea = userIdeas.find(i => i.id == ideaId);
    if (!idea) return;
    
    currentViewIdea = idea;
    
    // Set title
    document.getElementById('viewIdeaTitle').textContent = idea.title || 'Untitled Idea';
    
    // Set content
    document.getElementById('viewIdeaContent').textContent = idea.content || '';
    
    // Set date
    const createdDate = new Date(idea.created_at).toLocaleDateString('en-US', { 
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' 
    });
    const updatedDate = new Date(idea.updated_at).toLocaleDateString('en-US', { 
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' 
    });
    document.getElementById('viewIdeaDate').innerHTML = `
        <i class="fa-regular fa-clock"></i> Created: ${createdDate}
        ${idea.created_at !== idea.updated_at ? `<span class="mx-2">•</span> Updated: ${updatedDate}` : ''}
    `;
    
    // Render media gallery
    const media = idea.media || [];
    const galleryContainer = document.getElementById('viewIdeaMediaGallery');
    
    if (media.length === 0) {
        galleryContainer.innerHTML = '';
    } else if (media.length === 1) {
        const m = media[0];
        const isVideo = m.file_type?.startsWith('video/');
        galleryContainer.innerHTML = isVideo 
            ? `<video src="${m.file_path}" controls class="w-full rounded-lg max-h-80 object-contain bg-black"></video>`
            : `<img src="${m.file_path}" class="w-full rounded-lg max-h-80 object-contain bg-slate-100" alt="">`;
    } else {
        galleryContainer.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                ${media.map(m => {
                    const isVideo = m.file_type?.startsWith('video/');
                    return `
                        <div class="relative rounded-lg overflow-hidden bg-slate-100 aspect-video cursor-pointer" onclick="window.open('${m.file_path}', '_blank')">
                            ${isVideo 
                                ? `<video src="${m.file_path}" class="w-full h-full object-cover"></video>
                                   <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                                     <i class="fa-solid fa-play text-white text-lg"></i>
                                   </div>`
                                : `<img src="${m.file_path}" class="w-full h-full object-cover" alt="">`
                            }
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }
    
    document.getElementById('viewIdeaModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeViewIdeaModal() {
    document.getElementById('viewIdeaModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentViewIdea = null;
}

function viewIdeaEdit() {
    if (!currentViewIdea) return;
    const ideaId = currentViewIdea.id;
    closeViewIdeaModal();
    editIdea(ideaId);
}

function viewIdeaConvert() {
    if (!currentViewIdea) return;
    const ideaId = currentViewIdea.id;
    closeViewIdeaModal();
    convertIdeaToDraft(ideaId);
}

function viewIdeaDelete() {
    if (!currentViewIdea) return;
    const ideaId = currentViewIdea.id;
    closeViewIdeaModal();
    deleteIdea(ideaId);
}

// Helper function for HTML escaping
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

let platformChart = null;

async function loadDashboard() {
    try {
        const days = document.getElementById('analyticsPeriod')?.value || 30;
        const data = await api(`get_dashboard_stats&days=${days}`);
        if (!data.success) return;
        
        const d = data.data;
        
        // === Health Score Ring ===
        const health = d.health || { score: 0, status: 'healthy', label: 'Loading...' };
        const healthPath = document.getElementById('healthPath');
        if (healthPath) {
            const healthColors = { healthy: '#10b981', warning: '#f59e0b', critical: '#ef4444' };
            healthPath.setAttribute('stroke-dasharray', `${health.score}, 100`);
            healthPath.style.stroke = healthColors[health.status];
            document.getElementById('healthScore').textContent = health.score;
            document.getElementById('healthLabel').textContent = health.label;
        }
    
    // === Smart Recommendations ===
    const recColors = { 
        warning: 'bg-amber-50 border-amber-100 rec-item', 
        success: 'bg-emerald-50 border-emerald-100 rec-item', 
        alert: 'bg-red-50 border-red-100 rec-item', 
        info: 'bg-blue-50 border-blue-100 rec-item' 
    };
    const recTextColors = { warning: 'text-amber-800', success: 'text-emerald-800', alert: 'text-red-800', info: 'text-blue-800' };
    const recIcons = { warning: '⚠️', success: '✅', alert: '🚨', info: 'ℹ️' };
    
    document.getElementById('recommendationsSection').innerHTML = (d.recommendations || []).slice(0, 4).map(r => `
        <div class="flex items-start gap-3 p-4 rounded-xl border-l-4 ${recColors[r.type] || 'bg-slate-50 border-slate-200'}">
            <span class="text-xl">${r.icon || recIcons[r.type] || '💡'}</span>
            <div>
                <div class="text-xs font-black uppercase tracking-wider mb-1 ${recTextColors[r.type] || 'text-slate-800'}">${r.title}</div>
                <div class="text-[11px] leading-relaxed text-slate-600 font-medium">${r.message}</div>
            </div>
        </div>
    `).join('') || '<div class="col-span-2 py-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">System stabilized. No critical insights.</div>';
    
    // === KPIs with Trends ===
    document.getElementById('kpiTotal').textContent = d.overview?.total_posts || 0;
    document.getElementById('kpiPublished').textContent = d.overview?.published_period || 0;
    document.getElementById('kpiPending').textContent = (d.overview?.pending_review || 0) + (d.overview?.reviewed || 0);
    document.getElementById('kpiScheduled').textContent = d.overview?.scheduled_upcoming || 0;
    document.getElementById('kpiApprovalRate').textContent = (d.overview?.approval_rate || 0) + '%';
    
    const trendBadge = (val) => {
        if (!val || val === 0) return '';
        const isPos = val > 0;
        return `
            <span class="text-[10px] font-black px-1.5 py-0.5 rounded ${isPos ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400'}">
                ${isPos ? '↑' : '↓'}${Math.abs(val)}%
            </span>
        `;
    };

    const pubTrend = d.overview?.published_trend || 0;
    document.getElementById('kpiPublishedTrend').innerHTML = pubTrend !== 0 ? (pubTrend > 0 ? '↑' : '↓') + Math.abs(pubTrend) + '%' : '';
    document.getElementById('kpiPublishedTrend').className = `text-[9px] font-bold px-1.5 py-0.5 rounded ${pubTrend >= 0 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400'}`;
    if (pubTrend === 0) document.getElementById('kpiPublishedTrend').classList.add('hidden');
    else document.getElementById('kpiPublishedTrend').classList.remove('hidden');

    const appTrend = d.overview?.approval_trend || 0;
    document.getElementById('kpiApprovalTrend').innerHTML = appTrend !== 0 ? (appTrend > 0 ? '↑' : '↓') + Math.abs(appTrend) + '%' : '';
    document.getElementById('kpiApprovalTrend').className = `text-[9px] font-bold px-1.5 py-0.5 rounded ${appTrend >= 0 ? 'bg-blue-500/20 text-blue-400' : 'bg-red-500/20 text-red-400'}`;
    if (appTrend === 0) document.getElementById('kpiApprovalTrend').classList.add('hidden');
    else document.getElementById('kpiApprovalTrend').classList.remove('hidden');
    
    // === Time Insights ===
    const timeInsights = d.time_insights || {};
    document.getElementById('bestDay').textContent = timeInsights.best_day || '-';
    document.getElementById('bestDayCount').textContent = timeInsights.best_day_count ? `${timeInsights.best_day_count} posts` : '';
    document.getElementById('bestHour').textContent = timeInsights.best_hour || '-';
    document.getElementById('bestHourCount').textContent = timeInsights.best_hour_count ? `${timeInsights.best_hour_count} posts` : '';
    
    // === Bottleneck Bars ===
    const bottlenecks = d.bottlenecks || [];
    const maxHours = Math.max(...bottlenecks.map(b => b.avg_hours || 0), 48);
    document.getElementById('bottleneckBars').innerHTML = bottlenecks.map(b => {
        const pct = Math.min((b.avg_hours / maxHours) * 100, 100);
        const color = b.is_bottleneck ? 'bg-red-500' : 'bg-brand-500';
        return `
            <div class="group">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-tight">${b.stage}</span>
                    <span class="text-[10px] font-black ${b.is_bottleneck ? 'text-red-500' : 'text-slate-400'}">${b.avg_days}d avg</span>
                </div>
                <div class="h-1.5 bg-slate-50 rounded-full overflow-hidden">
                    <div class="${color} h-full rounded-full transition-all duration-1000" style="width: ${pct}%"></div>
                </div>
            </div>
        `;
    }).join('') || '<p class="text-slate-400 text-[10px] font-bold uppercase py-4">Efficiency data pending...</p>';
    
    // === Workflow Funnel ===
    const statuses = ['DRAFT', 'PENDING_REVIEW', 'REVIEWED', 'APPROVED', 'SCHEDULED', 'PUBLISHED'];
    const maxCount = Math.max(...statuses.map(s => d.by_status?.[s] || 0), 1);
    const statusLabels = ['Drafts', 'Review', 'Reviewed', 'Approved', 'Sched', 'Pub'];
    const gradients = [
        'from-sky-400 to-sky-600', 
        'from-amber-400 to-amber-600',
        'from-orange-400 to-orange-600',
        'from-emerald-400 to-emerald-600',
        'from-indigo-400 to-indigo-600',
        'from-slate-400 to-slate-600'
    ];
    
    document.getElementById('workflowFunnel').innerHTML = statuses.map((s, i) => {
        const count = d.by_status?.[s] || 0;
        const height = Math.max((count / maxCount) * 100, 10);
        return `
            <div class="flex flex-col h-full items-center group relative flex-1">
                <div class="flex-1 flex flex-col justify-end w-full px-1">
                    <div class="bg-gradient-to-t ${gradients[i]} rounded-t-xl shadow-sm flex flex-col items-center justify-end pb-3 transition-all duration-500 hover:brightness-110" style="height: ${height}%">
                        <span class="text-xl font-black text-white drop-shadow-md">${count}</span>
                    </div>
                </div>
                <div class="text-center mt-3 w-full border-t border-slate-50 pt-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">${statusLabels[i]}</span>
                </div>
                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                    ${count} posts in ${statusLabels[i]}
                </div>
            </div>
        `;
    }).join('');
    
    // === Platform Chart ===
    const platformData = d.by_platform || [];
    const platformColors = {
        Facebook: '#1877f2', Instagram: '#e4405f', LinkedIn: '#0077b5',
        X: '#000000', TikTok: '#000000', YouTube: '#ff0000', 
        Snapchat: '#fffc00', Website: '#6366f1'
    };
    
    const ctx = document.getElementById('platformChart')?.getContext('2d');
    if (ctx) {
        if (platformChart) platformChart.destroy();
        platformChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: platformData.map(p => p.platform),
                datasets: [{
                    data: platformData.map(p => p.count),
                    backgroundColor: platformData.map(p => platformColors[p.platform] || '#94a3b8'),
                    borderWidth: 4,
                    borderColor: '#ffffff',
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '75%',
                animation: { animateRotate: true, animateScale: true }
            }
        });
        
        document.getElementById('platformLegend').innerHTML = (platformData.length > 0 ? platformData.slice(0, 6) : []).map(p => `
            <div class="flex items-center justify-between p-1">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: ${platformColors[p.platform] || '#94a3b8'}"></span>
                    <span class="text-[10px] font-bold text-slate-500 truncate uppercase">${p.platform}</span>
                </div>
                <span class="text-[10px] font-black text-slate-800">${p.count}</span>
            </div>
        `).join('') || '<div class="col-span-2 text-center text-[8px] text-slate-300 font-bold uppercase tracking-widest pt-4">No data</div>';
    }
    
    // === User Performance Cards ===
    const teamMembers = d.user_performance || [];
    document.getElementById('teamMemberCount').textContent = teamMembers.length > 0 ? `${teamMembers.length} ACTIVE` : 'NONE';
    
    document.getElementById('userPerformanceCards').innerHTML = teamMembers.map(u => {
        const roleStr = String(u.role).toLowerCase();
        const isReviewerMember = roleStr === 'admin' || roleStr === 'manager';
        const lastActivity = u.last_activity ? formatDate(u.last_activity) : 'Never';
        
        // Dynamic Metrics based on Role
        let metricsHtml = '';
        if (isReviewerMember) {
            metricsHtml = `
                <div class="grid grid-cols-2 gap-2 py-3 border-y border-slate-50 mb-3">
                    <div class="px-3 py-2 bg-indigo-50/50 rounded-xl">
                        <div class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-0.5">Reviews</div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-sm font-black text-indigo-600">${u.reviews_approved + u.reviews_rejected}</span>
                            <span class="text-[8px] text-indigo-300 font-bold">Total</span>
                        </div>
                    </div>
                    <div class="px-3 py-2 bg-emerald-50/50 rounded-xl">
                        <div class="text-[10px] text-emerald-400 font-black uppercase tracking-widest mb-0.5">Approved</div>
                        <div class="text-sm font-black text-emerald-600">${u.reviews_approved}</div>
                    </div>
                    <div class="px-3 py-2 bg-amber-50/50 rounded-xl">
                        <div class="text-[10px] text-amber-400 font-black uppercase tracking-widest mb-0.5">Rejections</div>
                        <div class="text-sm font-black text-amber-600">${u.reviews_rejected}</div>
                    </div>
                    <div class="px-3 py-2 bg-blue-50/50 rounded-xl">
                        <div class="text-[10px] text-blue-400 font-black uppercase tracking-widest mb-0.5">Comments</div>
                        <div class="text-sm font-black text-blue-600">${u.comments_count}</div>
                    </div>
                    <div class="px-3 py-2 bg-violet-50/50 rounded-xl">
                        <div class="text-[10px] text-violet-400 font-black uppercase tracking-widest mb-0.5">Drafted</div>
                        <div class="text-sm font-black text-violet-600">${u.drafts_created}</div>
                    </div>
                    <div class="px-3 py-2 bg-slate-50/50 rounded-xl">
                        <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-0.5">Published</div>
                        <div class="text-sm font-black text-slate-600">${u.published_count}</div>
                    </div>
                </div>
            `;
        } else {
            metricsHtml = `
                <div class="grid grid-cols-2 gap-2 py-3 border-y border-slate-50 mb-3">
                    <div class="px-3 py-2 bg-violet-50/50 rounded-xl">
                        <div class="text-[10px] text-violet-400 font-black uppercase tracking-widest mb-0.5">Ideas</div>
                        <div class="text-sm font-black text-violet-600">${u.ideas_created || 0}</div>
                    </div>
                    <div class="px-3 py-2 bg-sky-50/50 rounded-xl">
                        <div class="text-[10px] text-sky-400 font-black uppercase tracking-widest mb-0.5">Drafts</div>
                        <div class="text-sm font-black text-sky-600">${u.drafts_created}</div>
                    </div>
                    <div class="px-3 py-2 bg-amber-50/50 rounded-xl">
                        <div class="text-[10px] text-amber-400 font-black uppercase tracking-widest mb-0.5">In Review</div>
                        <div class="text-sm font-black text-amber-600">${u.pending_review_count}</div>
                    </div>
                    <div class="px-3 py-2 bg-emerald-50/50 rounded-xl">
                        <div class="text-[10px] text-emerald-400 font-black uppercase tracking-widest mb-0.5">Accepted</div>
                        <div class="text-sm font-black text-emerald-600">${u.approved_count}</div>
                    </div>
                    <div class="px-3 py-2 bg-slate-50/50 rounded-xl">
                        <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-0.5">Published</div>
                        <div class="text-sm font-black text-slate-600">${u.published_count}</div>
                    </div>
                    <div class="px-3 py-2 bg-indigo-50/50 rounded-xl">
                        <div class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-0.5">Comments</div>
                        <div class="text-sm font-black text-indigo-600">${u.comments_count}</div>
                    </div>
                </div>
            `;
        }
        
        return `
        <div class="p-5 bg-white border border-slate-100 rounded-3xl hover:border-brand-200 hover:shadow-xl transition-all duration-500 group">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 bg-slate-50 text-slate-600 rounded-2xl flex items-center justify-center font-black text-2xl border border-slate-100 shadow-sm overflow-hidden transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:bg-white group-hover:border-brand-100">
                    ${(u.full_name || u.username)[0].toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-black text-slate-800 truncate text-lg tracking-tight">${u.full_name || u.username}</div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase tracking-widest ${isReviewerMember ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600'}">${u.role}</span>
                        <span class="text-[9px] text-slate-300 font-bold">•</span>
                        <span class="text-[8px] text-slate-400 font-black uppercase tracking-tighter">${lastActivity}</span>
                    </div>
                </div>
            </div>
            
            ${metricsHtml}
            
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full ${isReviewerMember ? 'bg-purple-500 shadow-[0_0_8px_rgba(168,85,247,0.4)]' : 'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.4)]'}"></div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Active Pulse</span>
                </div>
            </div>
        </div>
    `}).join('') || '<div class="col-span-full py-12 text-center text-slate-400 font-bold uppercase tracking-widest">No team pulse detected</div>';
    
    // === Upcoming Scheduled ===
    document.getElementById('upcomingScheduled').innerHTML = (d.upcoming_scheduled || []).map(p => {
        const schedDate = new Date(p.scheduled_date);
        return `
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-indigo-50 transition-colors group" onclick="openViewModal(${p.id})">
                <div class="w-10 h-10 bg-white border border-slate-100 text-indigo-600 rounded-xl flex flex-col items-center justify-center text-[10px] font-black shadow-sm group-hover:border-indigo-200">
                    <span class="leading-none">${schedDate.getDate()}</span>
                    <span class="uppercase tracking-tighter opacity-50">${schedDate.toLocaleString('en', {month: 'short'})}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-slate-700 truncate group-hover:text-indigo-700">${escapeHtml(p.title)}</div>
                    <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">${p.platforms ? JSON.parse(p.platforms).join(' · ') : ''} · ${schedDate.toLocaleTimeString('en', {hour: '2-digit', minute: '2-digit'})}</div>
                </div>
            </div>
        `;
    }).join('') || '<p class="text-slate-300 text-[10px] font-bold uppercase py-6 text-center">Calendar is clear</p>';
    
    // === Recent Activity ===
    const actionConfig = {
        'created': { icon: '✚', bg: 'bg-emerald-50 text-emerald-500', label: 'Draft Created' },
        'updated': { icon: '✎', bg: 'bg-blue-50 text-blue-500', label: 'Revised Content' },
        'status_changed': { icon: '⟳', bg: 'bg-amber-50 text-amber-500', label: 'Status Update' },
        'comment_added': { icon: '💬', bg: 'bg-indigo-50 text-indigo-500', label: 'Internal Comms' },
        'media_uploaded': { icon: '📎', bg: 'bg-purple-50 text-purple-500', label: 'Media Injection' },
        'deleted': { icon: '✕', bg: 'bg-red-50 text-red-500', label: 'Node Purged' },
    };
    
    const activityStatusColors = {
        'DRAFT': 'bg-sky-500', 'PENDING_REVIEW': 'bg-amber-500',
        'REVIEWED': 'bg-orange-500', 'CHANGES_REQUESTED': 'bg-orange-500', 'APPROVED': 'bg-emerald-500',
        'SCHEDULED': 'bg-indigo-500', 'PUBLISHED': 'bg-slate-600'
    };
    
    document.getElementById('recentActivity').innerHTML = (d.recent_activity || []).map(a => {
        const config = actionConfig[a.action] || { icon: '•', bg: 'bg-slate-50 text-slate-500', label: a.action };
        
        let statusTransition = '';
        if (a.action === 'status_changed' && a.new_value) {
            statusTransition = `<span class="ml-2 px-1.5 py-0.5 rounded-[4px] text-[8px] font-black text-white uppercase ${activityStatusColors[a.new_value] || 'bg-slate-400'}">${a.new_value.replace('_', ' ')}</span>`;
        }
        
        return `
        <div class="p-4 bg-white border border-slate-100 rounded-2xl hover:border-brand-100 hover:shadow-md transition-all cursor-pointer group" onclick="openViewModal(${a.post_id})">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 ${config.bg} rounded-xl flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform">${config.icon}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black text-slate-800 uppercase tracking-tight truncate">${a.full_name || a.username}</span>
                        <span class="text-[9px] font-bold text-slate-300 uppercase">${formatDate(a.created_at)}</span>
                    </div>
                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center">
                        ${config.label} ${statusTransition}
                    </div>
                    <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-50 group-hover:bg-slate-100 transition-colors">
                        <div class="text-[11px] font-bold text-slate-700 truncate">${escapeHtml(a.post_title)}</div>
                    </div>
                </div>
            </div>
        </div>
    `}).join('') || '<div class="col-span-full py-12 text-center text-slate-300 text-xs font-bold uppercase tracking-widest">Awaiting system events...</div>';
    
    // Update activity count
    const activityCount = (d.recent_activity || []).length;
    document.getElementById('activityCount').textContent = activityCount > 0 ? `${activityCount} REAL-TIME EVENTS` : '';
    
    } catch (err) {
        console.error('loadDashboard ERROR:', err);
        toast('Failed to load dashboard sync', 'error');
    }
}

async function loadPosts(checkChanges = false) {
    let url = 'fetch_posts';
    const params = [];
    const platform = document.getElementById('platformFilter')?.value;
    const myPosts = document.getElementById('myPostsFilter')?.checked;
    const search = document.getElementById('searchInput')?.value;
    if (platform) params.push(`platform=${platform}`);
    if (myPosts) params.push('my_posts=true');
    if (search) params.push(`search=${encodeURIComponent(search)}`);
    if (params.length) url += '&' + params.join('&');
    
    // Preserve scroll position if checking changes
    const scrollPos = window.scrollY;

    const data = await api(url);
    if (data.success) { 
        // Always render to ensure fresh data
        app.posts = data.data; 
        renderBoard(); 
        
        // Restore scroll if it was a background update
        if (checkChanges) window.scrollTo(0, scrollPos);
    }
}

// Current status filter for board tabs
let currentStatusFilter = '';

function renderBoard() {
    const grouped = { DRAFT: [], PENDING_REVIEW: [], REVIEWED: [], APPROVED: [], SCHEDULED: [], PUBLISHED: [] };
    
    app.posts.forEach(p => {
        if (p.status === 'CHANGES_REQUESTED') grouped.DRAFT.push(p);
        else if (grouped[p.status]) grouped[p.status].push(p);
    });
    
    // Update all tab counts
    let totalCount = 0;
    STATUS_LIST.forEach(status => {
        const count = grouped[status]?.length || 0;
        totalCount += count;
        const countEl = document.getElementById('count' + status);
        if (countEl) countEl.textContent = count;
    });
    
    // Update "All" tab count
    const countAllEl = document.getElementById('countAll');
    if (countAllEl) countAllEl.textContent = totalCount;
    
    // Filter posts based on current status filter
    let filteredPosts = [];
    if (currentStatusFilter === '') {
        // Show all posts
        STATUS_LIST.forEach(status => {
            filteredPosts = filteredPosts.concat(grouped[status] || []);
        });
    } else {
        filteredPosts = grouped[currentStatusFilter] || [];
    }
    
    // Render posts in grid
    const grid = document.getElementById('postsGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (filteredPosts.length === 0) {
        grid.innerHTML = '';
        emptyState.classList.remove('hidden');
    } else {
        emptyState.classList.add('hidden');
        grid.innerHTML = filteredPosts.map(p => cardHTML(p)).join('');
    }
    
    // Update active tab styling
    updateActiveTab();
}

function setStatusFilter(status) {
    currentStatusFilter = status;
    renderBoard();
}

function updateActiveTab() {
    const tabs = ['All', 'DRAFT', 'PENDING_REVIEW', 'APPROVED', 'SCHEDULED', 'PUBLISHED'];
    
    tabs.forEach(tab => {
        const tabId = tab === 'All' ? 'tabAll' : 'tab' + tab;
        const tabEl = document.getElementById(tabId);
        if (!tabEl) return;
        
        const isActive = (tab === 'All' && currentStatusFilter === '') || currentStatusFilter === tab;
        
        // Minimalist Tab Style
        if (isActive) {
            tabEl.className = `status-tab px-3 py-2 text-xs font-bold text-slate-800 border-b-2 border-slate-800 bg-transparent transition-all flex items-center gap-2`;
        } else {
            tabEl.className = 'status-tab px-3 py-2 text-xs font-medium text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2';
        }
    });
}

function cardHTML(post) {
    const hasChanges = post.status === 'CHANGES_REQUESTED';
    
    // Parse platforms
    let platforms = [];
    if (post.platforms) {
        platforms = typeof post.platforms === 'string' ? JSON.parse(post.platforms) : post.platforms;
    } else if (post.platform) {
        platforms = [post.platform];
    }
    
    // Minimalist Status Indicators
    const statusConfig = {
        'DRAFT': { color: 'bg-sky-50 text-sky-600', dot: 'bg-sky-400', label: 'Draft', border: 'border-sky-400' },
        'PENDING_REVIEW': { color: 'bg-amber-50 text-amber-600', dot: 'bg-amber-400', label: 'Pending', border: 'border-amber-400' },
        'CHANGES_REQUESTED': { color: 'bg-orange-50 text-orange-600', dot: 'bg-orange-400', label: 'Revise', border: 'border-orange-400' },
        'APPROVED': { color: 'bg-emerald-50 text-emerald-600', dot: 'bg-emerald-500', label: 'Approved', border: 'border-emerald-400' },
        'SCHEDULED': { color: 'bg-indigo-50 text-indigo-600', dot: 'bg-indigo-500', label: 'Scheduled', border: 'border-indigo-400' },
        'PUBLISHED': { color: 'bg-slate-50 text-slate-600', dot: 'bg-slate-500', label: 'Published', border: 'border-slate-400' }
    };
    
    const config = statusConfig[post.status] || { color: 'bg-slate-50 text-slate-500', dot: 'bg-slate-400', label: post.status, border: 'border-slate-300' };
    
    // Media Content Logic
    let mediaHtml = '';
    let mediaList = [];
    
    // Safety check for media parsing
    if (post.media) {
        try {
            mediaList = typeof post.media === 'string' ? JSON.parse(post.media) : post.media;
            if (!Array.isArray(mediaList)) mediaList = [];
        } catch (e) {
            console.error('Media parse error', e);
            mediaList = [];
        }
    } else if (post.primary_image) {
        mediaList = [{ file_path: post.primary_image }];
    }

    if (mediaList && mediaList.length > 0) {
        if (mediaList.length === 1) {
            // Single Media
            const m = mediaList[0];
            if (isVideoFile(m.file_path)) {
                mediaHtml = `<div class="h-40 w-full overflow-hidden bg-slate-100 mb-3 rounded border border-slate-100 relative group-hover:border-slate-200 transition-colors flex-shrink-0"><video src="${m.file_path}" class="w-full h-full object-cover" muted></video><div class="absolute inset-0 flex items-center justify-center bg-black/10"><div class="w-6 h-6 bg-white/90 rounded-full flex items-center justify-center shadow-sm"><i class="fa-solid fa-play text-[8px] text-slate-800 ml-0.5"></i></div></div></div>`;
            } else {
                mediaHtml = `<div class="h-40 w-full overflow-hidden bg-slate-100 mb-3 rounded border border-slate-100 group-hover:border-slate-200 transition-colors flex-shrink-0"><img src="${m.file_path}" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700"></div>`;
            }
        } else if (mediaList.length === 2) {
            // Split View (50/50)
            mediaHtml = `<div class="h-40 w-full grid grid-cols-2 gap-0.5 overflow-hidden bg-slate-100 mb-3 rounded border border-slate-100 group-hover:border-slate-200 transition-colors flex-shrink-0">
                ${mediaList.map(m => isVideoFile(m.file_path) 
                    ? `<div class="relative w-full h-full"><video src="${m.file_path}" class="w-full h-full object-cover"></video><div class="absolute inset-0 flex items-center justify-center bg-black/10"><i class="fa-solid fa-play text-[8px] text-white"></i></div></div>`
                    : `<img src="${m.file_path}" class="w-full h-full object-cover">`
                ).join('')}
            </div>`;
        } else {
            // Collage View (Main + Badge)
            const m = mediaList[0];
            const extraCount = mediaList.length - 1;
            mediaHtml = `<div class="h-40 w-full overflow-hidden bg-slate-100 mb-3 rounded border border-slate-100 group-hover:border-slate-200 transition-colors flex-shrink-0 relative">
                ${isVideoFile(m.file_path)
                    ? `<video src="${m.file_path}" class="w-full h-full object-cover"></video>`
                    : `<img src="${m.file_path}" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700">`
                }
                <div class="absolute bottom-2 right-2 bg-black/60 text-white text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1 backdrop-blur-sm">
                    <i class="fa-regular fa-clone"></i> +${extraCount}
                </div>
            </div>`;
        }
    }
    
    // Changes Requested Indicator (Minimal)
    const changesIndicator = hasChanges ? 
        `<div class="flex items-center gap-1 text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded mb-2"><i class="fa-solid fa-circle-exclamation"></i> Revision Requested</div>` : '';

    // Date Logic
    const updatedDate = formatDate(post.updated_at || post.created_at);

    // Platform Icons
    const platformIcons = platforms.map(p => {
        const color = PLATFORM_COLORS[p] || 'text-slate-400';
        const icon = PLATFORM_ICONS[p] || 'fa-solid fa-share-nodes';
        const textColor = color.replace('bg-', 'text-').replace('-500', '-600');
        return `<span class="${textColor} text-xs"><i class="${icon}"></i></span>`; // Increased size slightly
    }).join('');

    // --- HTML Structure ---
    // Added border-t-4 for status distinction
    // Fixed height h-96 (24rem)
    return `
        <div class="group bg-white rounded-lg p-4 border border-slate-200 border-t-4 ${config.border} hover:shadow-md transition-all cursor-pointer h-96 flex flex-col" onclick="openViewModal(${post.id})">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-3 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">${config.label}</span>
                </div>
                ${post.urgency == 1 ? '<div class="text-[10px] font-bold text-rose-600 flex items-center gap-1 bg-rose-50 px-1.5 py-0.5 rounded"><i class="fa-solid fa-bolt"></i> Urgent</div>' : ''}
            </div>

            ${changesIndicator}
            ${mediaHtml}
            
            <!-- Content (Flex Grow) -->
            <div class="flex-1 min-h-0 flex flex-col">
                <h4 class="text-sm font-bold text-slate-800 mb-1 leading-snug line-clamp-2 group-hover:text-brand-600 transition-colors">${escapeHtml(post.title)}</h4>
                <p class="text-[11px] text-slate-500 leading-relaxed line-clamp-3 overflow-hidden">${escapeHtml(post.content)}</p>
                
                <!-- Spacer to push footer down if content is short -->
                <div class="flex-grow"></div>
            </div>
            
            <!-- Footer -->
            <div class="flex items-center justify-between pt-3 mt-2 border-t border-slate-50 flex-shrink-0">
                <div class="flex items-center gap-2 text-xs">
                   ${platformIcons ? `<div class="flex gap-2 opacity-70 group-hover:opacity-100 transition-opacity">${platformIcons}</div>` : ''}
                </div>
                <div class="flex flex-col items-end">
                     <div class="flex items-center gap-1.5 mb-0.5">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">${post.author_name?.split(' ')[0]}</span>
                        ${post.comment_count > 0 ? `<div class="flex items-center gap-1 text-[10px] text-slate-400 bg-slate-100 px-1 rounded"><i class="fa-regular fa-comment"></i> ${post.comment_count}</div>` : ''}
                     </div>
                     <span class="text-[9px] text-slate-300 font-medium">${updatedDate}</span>
                </div>
            </div>
        </div>
    `;
}

// Utility: Format date
function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

// ==================== CREATE MODAL ====================
// Store selected files for create form
let createMediaFiles = [];

function openCreateModal() {
    document.getElementById('createForm').reset();
    // Clear all platform checkboxes
    document.querySelectorAll('input[name="createPlatforms"]').forEach(cb => cb.checked = false);
    // Clear media gallery
    createMediaFiles = [];
    renderCreateMediaGallery();
    document.getElementById('createFileInput').value = '';
    document.getElementById('createModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    createMediaFiles = [];
}

function previewCreateFiles(e) {
    const files = Array.from(e.target.files);
    files.forEach(file => {
        if (file.size > 100 * 1024 * 1024) {
            toast(`File ${file.name} exceeds 100MB limit`, 'error');
            return;
        }
        createMediaFiles.push(file);
    });
    renderCreateMediaGallery();
    e.target.value = ''; // Allow selecting same file again
}

function renderCreateMediaGallery() {
    const gallery = document.getElementById('createMediaGallery');
    if (createMediaFiles.length === 0) {
        gallery.innerHTML = '';
        return;
    }
    
    gallery.innerHTML = createMediaFiles.map((file, index) => {
        const isVideo = file.type.startsWith('video/');
        const url = URL.createObjectURL(file);
        const mediaEl = isVideo 
            ? `<video src="${url}" class="w-full h-20 object-cover rounded-lg" muted></video>`
            : `<img src="${url}" class="w-full h-20 object-cover rounded-lg">`;
        return `
            <div class="relative group">
                ${mediaEl}
                <button type="button" onclick="removeCreateMedia(${index})" class="absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full opacity-0 group-hover:opacity-100 text-sm font-bold shadow-lg">×</button>
                ${isVideo ? '<span class="absolute bottom-1 left-1 bg-black/60 text-white text-[8px] px-1 rounded">VIDEO</span>' : ''}
            </div>
        `;
    }).join('');
}

function removeCreateMedia(index) {
    createMediaFiles.splice(index, 1);
    renderCreateMediaGallery();
}

document.getElementById('createForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Collect selected platforms from checkboxes
    const selectedPlatforms = Array.from(document.querySelectorAll('input[name="createPlatforms"]:checked'))
        .map(cb => cb.value);
    
    if (selectedPlatforms.length === 0) {
        toast('Please select at least one platform', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('title', document.getElementById('createTitle').value);
    formData.append('content', document.getElementById('createContent').value);
    formData.append('platforms', JSON.stringify(selectedPlatforms));
    formData.append('status', document.getElementById('createStatus').value);
    formData.append('urgency', document.getElementById('createUrgent').checked ? '1' : '0');
    
    // Append all media files
    createMediaFiles.forEach((file, i) => {
        formData.append(`files[${i}]`, file);
    });
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const res = await fetch('api.php?action=save_post', { 
        method: 'POST', 
        body: formData,
        headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}
    });
    const data = await res.json();
    
    if (data.success) {
        toast('Post created successfully!', 'success');
        closeCreateModal();
        loadPosts();
    } else {
        toast(data.message || 'Error creating post', 'error');
    }
});

// ==================== VIEW MODAL ====================
async function openViewModal(id) {
    try {
        const data = await api(`get_post&id=${id}`);
        if (!data.success) { toast('Failed to load post', 'error'); return; }
        
        app.currentPost = data.data;
        const p = data.data;
        
        // Status badge
        const statusBadge = document.getElementById('viewStatusBadge');
        if (statusBadge) {
            statusBadge.textContent = (STATUS_LABELS && STATUS_LABELS[p.status]) || p.status;
            const statusColors = {
                'DRAFT': 'bg-slate-100 text-slate-700 border-slate-200',
                'PENDING_REVIEW': 'bg-amber-50 text-amber-700 border-amber-200',
                'CHANGES_REQUESTED': 'bg-orange-50 text-orange-700 border-orange-200',
                'APPROVED': 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'SCHEDULED': 'bg-blue-50 text-blue-700 border-blue-200',
                'PUBLISHED': 'bg-slate-900 text-white border-slate-900'
            };
            statusBadge.className = `px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider flex-shrink-0 border ${statusColors[p.status] || 'bg-slate-100 text-slate-600'}`;
        }
        
        // Platform badges
        let platforms = [];
        if (p.platforms) {
            platforms = typeof p.platforms === 'string' ? JSON.parse(p.platforms) : p.platforms;
        } else if (p.platform) {
            platforms = [p.platform];
        }
        
        // Safe access to globals
        const icons = typeof PLATFORM_ICONS !== 'undefined' ? PLATFORM_ICONS : {};
        const colors = typeof PLATFORM_COLORS !== 'undefined' ? PLATFORM_COLORS : {};
        
        const platformBadgesHtml = platforms.map(plat => {
            const icon = icons[plat] || 'fa-solid fa-share-nodes';
            const colorClass = colors[plat]?.replace('bg-', 'text-').replace('50', '600') || 'text-slate-600';
            return `<div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-full shadow-sm">
                        <i class="${icon} ${colorClass} text-xs"></i>
                        <span class="text-[11px] font-bold text-slate-700">${plat}</span>
                    </div>`;
        }).join('');
        const badgeContainer = document.getElementById('viewPlatformBadge');
        if (badgeContainer) badgeContainer.innerHTML = platformBadgesHtml;
        
        // Title and meta
        const titleEl = document.getElementById('viewTitle');
        if (titleEl) titleEl.textContent = p.title;
        
        const authorEl = document.getElementById('viewAuthor');
        if (authorEl) authorEl.textContent = p.author_full_name || p.author_name;
        
        const dateEl = document.getElementById('viewDate');
        if (dateEl) dateEl.textContent = new Date(p.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        
        const urgentBadge = document.getElementById('viewUrgentBadge');
        if (urgentBadge) {
            if (p.urgency == 1) {
                urgentBadge.classList.remove('hidden');
                urgentBadge.classList.add('inline-flex');
            } else {
                urgentBadge.classList.add('hidden');
                urgentBadge.classList.remove('inline-flex');
            }
        }
        
        // Content
        const contentEl = document.getElementById('viewContent');
        if (contentEl) contentEl.textContent = p.content;
        

        
        // Media Gallery - Handle Split View (Desktop) and Mobile Fallback
        const mediaColumn = document.getElementById('viewMediaColumn');
        const mediaWrapper = document.getElementById('viewMediaWrapper'); // Desktop
        const mediaMobile = document.getElementById('viewMediaMobile');   // Mobile
        const modalContainer = document.getElementById('viewModalContainer');
        const contentColumn = document.getElementById('viewContentColumn');
        
        const hasMedia = p.media && p.media.length > 0;
        
        // Helper to generate Media HTML
        const getMediaHtml = (isMobile) => {
            if (p.media.length === 0) return '';
            
            // Generate Slides
            const slides = p.media.map((m, idx) => {
                const isVid = isVideoFile(m.file_path);
                const isActive = idx === 0 ? '' : 'hidden';
                const heightClass = isMobile ? 'max-h-96' : 'max-h-full h-auto';
                const objectClass = isMobile ? 'object-contain bg-slate-100' : 'object-contain';
                
                return `<div class="media-slide w-full h-full flex items-center justify-center transition-opacity duration-300 ${isActive}" data-index="${idx}">
                    ${isVid 
                        ? `<video src="${m.file_path}" controls playsinline webkit-playsinline class="w-full ${heightClass} ${objectClass} rounded-lg shadow-sm bg-black"></video>` 
                        : `<img src="${m.file_path}" class="w-full ${heightClass} ${objectClass} rounded-lg shadow-sm mx-auto">`}
                </div>`;
            }).join('');
            
            // Single Item - No Controls
            if (p.media.length === 1) return slides;
            
            // Carousel Controls
            return `
                <div class="relative w-full h-full group">
                    <!-- Slides Container -->
                    <div class="w-full h-full flex items-center justify-center" id="carouselSlides-${isMobile ? 'mo' : 'dt'}">
                        ${slides}
                    </div>
                    
                    <!-- Prev Button -->
                    <button onclick="changeMediaSlide(-1, '${isMobile ? 'mo' : 'dt'}')" class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white w-10 h-10 rounded-full flex items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    
                    <!-- Next Button -->
                    <button onclick="changeMediaSlide(1, '${isMobile ? 'mo' : 'dt'}')" class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white w-10 h-10 rounded-full flex items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    
                    <!-- Dots -->
                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-1.5 z-10 pointer-events-none">
                        ${p.media.map((_, i) => `<div class="w-1.5 h-1.5 rounded-full transition-colors pointer-events-auto ${i === 0 ? 'bg-white' : 'bg-white/40'}" id="dot-${isMobile ? 'mo' : 'dt'}-${i}"></div>`).join('')}
                    </div>
                </div>
            `;
        };
        
        // Expose slider function globally if not already
        if (!window.changeMediaSlide) {
            window.changeMediaSlide = (dir, type) => {
                const container = document.getElementById(`carouselSlides-${type}`);
                if (!container) return;
                
                const slides = container.querySelectorAll('.media-slide');
                let currentIndex = Array.from(slides).findIndex(s => !s.classList.contains('hidden'));
                
                // Hide current
                slides[currentIndex].classList.add('hidden');
                
                // Calculate next
                let nextIndex = currentIndex + dir;
                if (nextIndex >= slides.length) nextIndex = 0;
                if (nextIndex < 0) nextIndex = slides.length - 1;
                
                // Show next
                slides[nextIndex].classList.remove('hidden');
                
                // Update dots
                const dots = document.querySelectorAll(`[id^="dot-${type}-"]`);
                dots.forEach((d, i) => {
                    if (i === nextIndex) {
                        d.classList.remove('bg-white/40');
                        d.classList.add('bg-white');
                    } else {
                        d.classList.add('bg-white/40');
                        d.classList.remove('bg-white');
                    }
                });
            };
        }

        if (hasMedia) {
            const isMobile = window.innerWidth < 1024;
            
            // Adjust modal width for media
            if (modalContainer) {
                modalContainer.classList.remove('max-w-2xl');
                modalContainer.classList.add('max-w-7xl');
            }

            // Restore split layout balance
            if (contentColumn) {
                contentColumn.classList.remove('lg:w-full');
                contentColumn.classList.add('lg:w-[52%]');
            }

            // Desktop: Show Left Column
            if (mediaColumn && mediaWrapper) {
                if (!isMobile) {
                    mediaColumn.classList.remove('hidden');
                    mediaColumn.classList.add('lg:flex');
                    mediaWrapper.innerHTML = getMediaHtml(false);
                } else {
                    mediaColumn.classList.add('hidden');
                    mediaColumn.classList.remove('lg:flex');
                    mediaWrapper.innerHTML = '';
                }
            }
            
            // Mobile: Show Top Section
            if (mediaMobile) {
                if (isMobile) {
                    mediaMobile.classList.remove('hidden');
                    mediaMobile.innerHTML = `<div class="p-6 flex justify-center">${getMediaHtml(true)}</div>`;
                } else {
                    mediaMobile.classList.add('hidden');
                    mediaMobile.innerHTML = '';
                }
            }
        } else {
            // No Media: Adjust modal width for text-only
            if (modalContainer) {
                modalContainer.classList.remove('max-w-7xl');
                modalContainer.classList.add('max-w-2xl');
            }

            // Make content column full width
            if (contentColumn) {
                contentColumn.classList.remove('lg:w-[52%]');
                contentColumn.classList.add('lg:w-full');
            }

            // Hide both AND clear content
            if (mediaColumn) {
                mediaColumn.classList.add('hidden');
                mediaColumn.classList.remove('lg:flex');
            }
            if (mediaWrapper) mediaWrapper.innerHTML = '';
            if (mediaMobile) {
                mediaMobile.classList.add('hidden');
                mediaMobile.innerHTML = '';
            }
        }
        
        // Changes notice
        const changesNotice = document.getElementById('viewChangesNotice');
        if (changesNotice && p.change_request_reason) {
            const reasonEl = document.getElementById('viewChangesReason');
            if (reasonEl) reasonEl.textContent = `${p.change_request_reason} — ${p.change_requested_by_name || 'Admin'}`;
            changesNotice.classList.remove('hidden');
        } else if (changesNotice) {
            changesNotice.classList.add('hidden');
        }
        
        // Action buttons
        if (typeof renderActionButtons === 'function') renderActionButtons(p);
        
        // Comments
        if (typeof renderViewComments === 'function') renderViewComments(p.comments || []);
        
        // Activity
        if (typeof renderViewActivity === 'function') renderViewActivity(p.activity || []);
        
        // Buttons visibility
        // Buttons visibility
        const isManager = app.user.role === 'manager';
        const isOwner = p.author_id == app.user.id;
        const isApprovedOrLater = ['APPROVED', 'SCHEDULED', 'PUBLISHED'].includes(p.status);
        
        let showActionButtons = false;
        if (isApprovedOrLater) {
            showActionButtons = false;
        } else {
            if (isManager) {
                showActionButtons = true;
            } else if (['admin', 'staff'].includes(app.user.role)) {
                showActionButtons = isOwner;
            }
        }
        
        const editBtn = document.getElementById('viewEditBtn');
        if (editBtn) editBtn.classList.toggle('hidden', !showActionButtons);
        
        const deleteBtn = document.getElementById('viewDeleteBtn');
        if (deleteBtn) deleteBtn.classList.toggle('hidden', !showActionButtons);
        
        const modal = document.getElementById('viewModal');
        if (modal) modal.classList.remove('hidden');
        
    } catch (err) {
        console.error('Error opening view modal:', err);
        toast('Error opening post: ' + err.message, 'error');
    }
}

function renderActionButtons(p) {
    const container = document.getElementById('actionButtons');
    const isAdmin = app.user.role === 'admin';
    const isManager = app.user.role === 'manager';
    const isAdminOrManager = ['admin', 'manager'].includes(app.user.role);
    const isOwner = p.author_id == app.user.id;
    let buttons = [];
    
    // Premium button base styles
    const btnPrimary = 'flex-1 bg-slate-800 hover:bg-slate-900 text-white font-medium py-2.5 px-5 rounded-md flex items-center justify-center gap-2 text-sm transition-colors';
    const btnSuccess = 'flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-5 rounded-md flex items-center justify-center gap-2 text-sm transition-colors';
    const btnWarning = 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-2.5 px-5 rounded-md flex items-center justify-center gap-2 text-sm border border-slate-200 transition-colors';
    const btnSecondary = 'bg-white hover:bg-slate-50 text-slate-600 font-medium py-2.5 px-4 rounded-md text-sm border border-slate-200 transition-colors';
    
    
    if (p.status === 'DRAFT' || p.status === 'CHANGES_REQUESTED') {
        if (isOwner || isAdmin || isManager) {
            buttons.push(`<button onclick="submitForReview()" class="${btnPrimary}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Submit for Review</button>`);
        }
    } else if (p.status === 'PENDING_REVIEW') {
        if (isAdmin) {
            buttons.push(`<button onclick="sendToReviewed()" class="${btnSuccess}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Approve</button>`);
            buttons.push(`<button onclick="openChangesModal()" class="${btnWarning}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>Request Changes</button>`);
        }
        if (isOwner) {
            buttons.push(`<button onclick="recallToDraft()" class="${btnSecondary}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>Recall to Draft</button>`);
        }
    } else if (p.status === 'REVIEWED') {
        if (isManager) {
            buttons.push(`<button onclick="approvePost()" class="${btnSuccess}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Approve</button>`);
            buttons.push(`<button onclick="openChangesModal()" class="${btnWarning}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>Request Changes</button>`);
        } else if (isAdmin) {
            buttons.push(`<button onclick="recallFromManager()" class="${btnSecondary}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>Recall to Review</button>`);
        } else {
            buttons.push(`<div class="flex-1 text-center py-2.5 px-5 bg-orange-50 text-orange-600 font-medium rounded-md text-sm border border-orange-200"><svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Awaiting Manager Approval</div>`);
        }
    } else if (p.status === 'APPROVED') {
        if (isAdminOrManager || app.user.role === 'staff') {
            buttons.push(`<button onclick="openScheduleModal()" class="${btnPrimary}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Schedule for Publishing</button>`);
        }
        if (isManager) {
            buttons.push(`<button onclick="revokeApproval()" class="${btnWarning}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>Revoke Approval</button>`);
        }
    } else if (p.status === 'SCHEDULED') {
        const schedDate = p.scheduled_date ? new Date(p.scheduled_date) : null;
        const schedStr = schedDate ? schedDate.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Unknown';
        buttons.push(`<div class="flex-1 text-center py-2.5 px-5 bg-slate-50 text-slate-600 font-medium rounded-md text-sm border border-slate-200"><svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Scheduled: ${schedStr}</div>`);
        if (isAdminOrManager) {
            buttons.push(`<button onclick="publishNow()" class="${btnSuccess}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>Publish Now</button>`);
        }
        if (isAdminOrManager || app.user.role === 'staff') {
            buttons.push(`<button onclick="unschedulePost()" class="${btnSecondary}">Unschedule</button>`);
        }
    } else if (p.status === 'PUBLISHED') {
        const pubDate = p.published_date ? new Date(p.published_date) : null;
        const pubStr = pubDate ? pubDate.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Unknown';
        buttons.push(`<div class="flex-1 text-center py-2.5 px-5 bg-slate-50 text-slate-500 font-medium rounded-md text-sm border border-slate-200"><svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Published: ${pubStr}</div>`);
    }
    
    container.innerHTML = buttons.join('') || '<div class="text-slate-400 text-center py-2 text-sm">No actions available</div>';
}

function renderViewComments(comments) {
    document.getElementById('viewCommentCount').textContent = comments.length ? `(${comments.length})` : '';
    document.getElementById('viewComments').innerHTML = comments.map(c => `
        <div class="flex gap-3">
            <div class="w-9 h-9 bg-gold-100 text-gold-600 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">${c.username[0].toUpperCase()}</div>
            <div class="flex-1 bg-slate-50 rounded-xl p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-semibold text-sm">${c.full_name || c.username}</span>
                    <span class="text-xs text-slate-400">${formatDate(c.created_at)}</span>
                </div>
                <p class="text-sm text-slate-600">${escapeHtml(c.content)}</p>
            </div>
        </div>
    `).join('') || '<p class="text-slate-400 text-center py-4">No comments yet</p>';
}

function renderViewActivity(activities) {
    // Calculate stats
    const revisionCount = activities.filter(a => a.action === 'status_changed' && a.new_value === 'CHANGES_REQUESTED').length;
    const statsEl = document.getElementById('activityStats');
    if (statsEl) {
        if (revisionCount > 0) {
            statsEl.textContent = `(${revisionCount} revision${revisionCount > 1 ? 's' : ''} requested)`;
        } else {
            statsEl.textContent = `(${activities.length} event${activities.length !== 1 ? 's' : ''})`;
        }
    }
    
    // Action icons and labels mapping
    const actionConfig = {
        'created': { icon: '✦', color: 'text-emerald-500', label: 'created this post' },
        'updated': { icon: '✎', color: 'text-blue-500', label: 'updated the content' },
        'status_changed': { icon: '→', color: 'text-amber-500', label: 'changed status' },
        'comment_added': { icon: '💬', color: 'text-indigo-500', label: 'added a comment' },
        'media_uploaded': { icon: '📎', color: 'text-purple-500', label: 'uploaded media' },
        'media_deleted': { icon: '🗑', color: 'text-red-500', label: 'removed media' },
        'rejected': { icon: '✕', color: 'text-red-500', label: 'rejected' },
        'admin_approved': { icon: '★', color: 'text-emerald-500', label: 'approved (partially)' },
        'approved': { icon: '✓', color: 'text-emerald-500', label: 'approved' },
        'scheduled': { icon: '📅', color: 'text-indigo-500', label: 'scheduled' },
        'published': { icon: '🚀', color: 'text-slate-600', label: 'published' }
    };
    
    const statusLabels = {
        'DRAFT': 'Draft', 'PENDING_REVIEW': 'Pending Review',
        'CHANGES_REQUESTED': 'Changes Requested', 'APPROVED': 'Approved',
        'SCHEDULED': 'Scheduled', 'PUBLISHED': 'Published'
    };
    
    document.getElementById('viewActivity').innerHTML = activities.map(a => {
        const config = actionConfig[a.action] || { icon: '•', color: 'text-slate-400', label: a.action };
        
        // Build detailed description
        let details = '';
        if (a.action === 'status_changed' && a.old_value && a.new_value) {
            const fromLabel = statusLabels[a.old_value] || a.old_value;
            const toLabel = statusLabels[a.new_value] || a.new_value;
            details = `<span class="text-slate-500">${fromLabel}</span> <span class="text-slate-400">→</span> <span class="font-medium text-slate-700">${toLabel}</span>`;
            if (a.description && a.new_value === 'CHANGES_REQUESTED') {
                details += `<div class="mt-1 text-sm text-slate-500 bg-slate-50 p-2 rounded border-l-2 border-amber-300 italic">"${escapeHtml(a.description)}"</div>`;
            } else if (a.description) {
                details += `<span class="text-slate-400 ml-1">· ${escapeHtml(a.description)}</span>`;
            }
        } else if (a.description) {
            details = `<span class="text-slate-500">${escapeHtml(a.description)}</span>`;
        }
        
        return `
        <div class="relative flex gap-3 items-start text-sm pb-3">
            <div class="absolute -left-5 top-0.5 w-2 h-2 bg-slate-300 rounded-full"></div>
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="${config.color} font-medium">${config.icon}</span>
                    <span class="font-medium text-slate-800">${a.username}</span>
                    <span class="text-slate-500">${config.label}</span>
                </div>
                ${details ? `<div class="mt-1">${details}</div>` : ''}
                <div class="text-xs text-slate-400 mt-1">${formatDate(a.created_at)}</div>
            </div>
        </div>
    `}).join('') || '<p class="text-slate-400 text-center py-4">No activity recorded</p>';
}


function toggleEmojiPicker(textareaId, btnId) {
    const containerId = textareaId === 'createContent' ? 'createEmojiPickerContainer' : 'editEmojiPickerContainer';
    const container = document.getElementById(containerId);
    const isHidden = container.classList.contains('hidden');
    
    // Hide all other pickers
    document.querySelectorAll('[id$="EmojiPickerContainer"]').forEach(c => c.classList.add('hidden'));
    
    if (isHidden) {
        container.classList.remove('hidden');
        
        // One-time initialization of the picker inside this container
        const picker = container.querySelector('emoji-picker');
        if (!picker.dataset.initialized) {
            picker.addEventListener('emoji-click', event => {
                const textarea = document.getElementById(textareaId);
                const emoji = event.detail.unicode;
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const text = textarea.value;
                textarea.value = text.substring(0, start) + emoji + text.substring(end);
                textarea.focus();
                textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
                container.classList.add('hidden');
            });
            picker.dataset.initialized = 'true';
        }
    }
}

// Close emoji pickers when clicking outside
document.addEventListener('mousedown', (e) => {
    if (!e.target.closest('[id$="EmojiPickerContainer"]') && !e.target.closest('[id$="EmojiBtn"]')) {
        document.querySelectorAll('[id$="EmojiPickerContainer"]').forEach(c => c.classList.add('hidden'));
    }
});

async function addViewComment() {
    const content = document.getElementById('viewNewComment').value.trim();
    if (!content || !app.currentPost) return;
    
    const data = await api('add_comment', 'POST', { post_id: app.currentPost.id, content });
    if (data.success) {
        document.getElementById('viewNewComment').value = '';
        openViewModal(app.currentPost.id); // Refresh
    }
}

async function deletePost() {
    if (!app.currentPost) return;
    
    const postTitle = app.currentPost.title || 'this post';
    if (!confirm(`Are you sure you want to delete "${postTitle}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    const data = await api(`delete_post&id=${app.currentPost.id}`, 'DELETE');
    if (data.success) {
        toast('Post deleted', 'success');
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Failed to delete post', 'error');
    }
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
    app.currentPost = null;
}

// ==================== WORKFLOW ACTIONS ====================
async function approveIdea() {
    if (!app.currentPost) return;
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'DRAFT' });
    if (data.success) {
        toast('Idea converted to Draft!', 'success');
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

async function submitForReview() {
    if (!app.currentPost) return;
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'PENDING_REVIEW' });
    if (data.success) {
        toast('Submitted for review!', 'success');
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

async function sendToReviewed() {
    if (!app.currentPost) return;
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'REVIEWED' });
    if (data.success) {
        if (data.data && data.data.status === 'PENDING_REVIEW') {
            // Partial approval
            toast(data.message || 'Approval recorded!', 'success');
            openViewModal(app.currentPost.id); // Refresh view to show new progress
        } else {
            // Full approval
            toast('Sent to manager for approval!', 'success');
            closeViewModal();
            loadPosts();
        }
    } else {
        toast(data.message || 'Error', 'error');
    }
}

async function approvePost() {
    if (!app.currentPost) return;
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'APPROVED' });
    if (data.success) {
        toast('Post approved!', 'success');
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

async function revokeApproval() {
    if (!app.currentPost) return;
    if (!confirm('Are you sure you want to revoke approval for this post? It will return to the Review stage.')) return;
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'REVIEWED' });
    if (data.success) {
        toast('Approval revoked!', 'success');
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

async function recallFromManager() {
    if (!app.currentPost) return;
    if (!confirm('Recall this post from the manager?')) return;
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'PENDING_REVIEW' });
    if (data.success) {
        toast('Recalled from manager!', 'success');
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

async function recallToDraft() {
    if (!app.currentPost) return;
    if (!confirm('Recall this post to Draft?')) return;
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'DRAFT' });
    if (data.success) {
        toast('Recalled to draft!', 'success');
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

function openChangesModal() {
    document.getElementById('changesReasonInput').value = '';
    document.getElementById('changesModal').classList.remove('hidden');
}

function closeChangesModal() {
    document.getElementById('changesModal').classList.add('hidden');
}

async function confirmRequestChanges() {
    const reason = document.getElementById('changesReasonInput').value.trim();
    if (!reason) { toast('Please provide a reason', 'error'); return; }
    if (!app.currentPost) return;
    
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'CHANGES_REQUESTED', reason });
    if (data.success) {
        toast('Changes requested!', 'success');
        closeChangesModal();
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

// ==================== SCHEDULE MODAL ====================
function openScheduleModal() {
    // Set default to tomorrow at 9 AM
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(9, 0, 0, 0);
    document.getElementById('scheduleDateTime').value = tomorrow.toISOString().slice(0, 16);
    document.getElementById('scheduleModal').classList.remove('hidden');
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
}

async function confirmSchedule() {
    const dateTime = document.getElementById('scheduleDateTime').value;
    if (!dateTime) { toast('Please select a date and time', 'error'); return; }
    if (!app.currentPost) return;
    
    const scheduledDate = new Date(dateTime);
    if (scheduledDate <= new Date()) {
        toast('Please select a future date and time', 'error');
        return;
    }
    
    const data = await api('update_status', 'POST', { 
        id: app.currentPost.id, 
        status: 'SCHEDULED',
        scheduled_date: dateTime
    });
    
    if (data.success) {
        toast('Post scheduled!', 'success');
        closeScheduleModal();
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

async function publishNow() {
    if (!app.currentPost) return;
    if (!confirm('Publish this post now?')) return;
    
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'PUBLISHED' });
    if (data.success) {
        toast('Post published!', 'success');
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

async function unschedulePost() {
    if (!app.currentPost) return;
    if (!confirm('Unschedule this post? It will return to Approved status.')) return;
    
    const data = await api('update_status', 'POST', { id: app.currentPost.id, status: 'APPROVED' });
    if (data.success) {
        toast('Post unscheduled', 'success');
        closeViewModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
}

// ==================== EDIT MODAL ====================
function switchToEditMode() {
    if (!app.currentPost) {
        toast('No post selected', 'error');
        return;
    }
    // Save the post BEFORE closeViewModal() clears app.currentPost
    const postToEdit = { ...app.currentPost };
    closeViewModal();
    openEditModal(postToEdit);
}

function openEditModal(post) {
    if (!post) {
        toast('Invalid post data', 'error');
        return;
    }
    document.getElementById('editPostId').value = post.id;
    document.getElementById('editTitle').value = post.title || '';
    document.getElementById('editContent').value = post.content || '';
    
    // Set platform checkboxes
    let platforms = [];
    if (post.platforms) {
        platforms = typeof post.platforms === 'string' ? JSON.parse(post.platforms) : post.platforms;
    } else if (post.platform) {
        platforms = [post.platform];
    }
    // Clear all checkboxes first
    document.querySelectorAll('input[name="editPlatforms"]').forEach(cb => {
        cb.checked = platforms.includes(cb.value);
    });
    
    document.getElementById('editUrgent').checked = post.urgency == 1;
    
    // Media gallery
    renderEditMediaGallery(post.media || []);
    
    document.getElementById('editModal').classList.remove('hidden');
}

function renderEditMediaGallery(media) {
    document.getElementById('editMediaGallery').innerHTML = media.map(m => {
        const isVideo = isVideoFile(m.file_path);
        const mediaEl = isVideo 
            ? `<video src="${m.file_path}" class="w-full h-20 object-cover rounded-lg" muted></video>`
            : `<img src="${m.file_path}" class="w-full h-20 object-cover rounded-lg">`;
        return `
            <div class="relative group">
                ${mediaEl}
                <button type="button" onclick="deleteMedia(${m.id})" class="absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full opacity-0 group-hover:opacity-100 text-sm font-bold shadow-lg">×</button>
                ${isVideo ? '<span class="absolute bottom-1 left-1 bg-black/60 text-white text-[8px] px-1 rounded">VIDEO</span>' : ''}
            </div>
        `;
    }).join('') || '<p class="col-span-4 text-slate-400 text-sm">No media attached</p>';
}

async function uploadEditFile(e) {
    const postId = document.getElementById('editPostId').value;
    if (!postId) return;
    
    for (const file of e.target.files) {
        const fd = new FormData();
        fd.append('file', file);
        fd.append('post_id', postId);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch('api.php?action=upload_media', { 
            method: 'POST', 
            body: fd,
            headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}
        });
        const data = await res.json();
        if (data.success) {
            toast('Media uploaded!', 'success');
            // Refresh media gallery
            const postData = await api(`get_post&id=${postId}`);
            if (postData.success) renderEditMediaGallery(postData.data.media || []);
        } else {
            toast(data.message || 'Upload failed', 'error');
        }
    }
    e.target.value = '';
}

async function deleteMedia(id) {
    if (!confirm('Delete this media?')) return;
    await api(`delete_media&id=${id}`);
    const postId = document.getElementById('editPostId').value;
    const postData = await api(`get_post&id=${postId}`);
    if (postData.success) renderEditMediaGallery(postData.data.media || []);
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Collect selected platforms
    const selectedPlatforms = Array.from(document.querySelectorAll('input[name="editPlatforms"]:checked'))
        .map(cb => cb.value);
    
    if (selectedPlatforms.length === 0) {
        toast('Please select at least one platform', 'error');
        return;
    }
    
    const formData = {
        id: document.getElementById('editPostId').value,
        title: document.getElementById('editTitle').value,
        content: document.getElementById('editContent').value,
        platforms: selectedPlatforms,
        scheduled_date: document.getElementById('editScheduled')?.value || null,
        urgency: document.getElementById('editUrgent').checked
    };
    
    const data = await api('save_post', 'POST', formData);
    if (data.success) {
        toast('Post updated!', 'success');
        closeEditModal();
        loadPosts();
    } else {
        toast(data.message || 'Error', 'error');
    }
});

// ==================== NOTIFICATIONS ====================
async function loadNotifications() {
    try {
        const data = await api('get_notifications');
        if (data.success) {
            const badge = document.getElementById('notifBadge');
            const newCount = data.data.unread_count || 0;

            // Play sound if new notifications arrived (and system is initialized)
            if (app.notificationsInitialized && newCount > app.lastUnreadCount) {
                playNotificationSound();
                toast('New notification received', 'info');
            }

            // Update state
            app.lastUnreadCount = newCount;
            app.notificationsInitialized = true;

            if (newCount > 0) { 
                badge.textContent = newCount; 
                badge.classList.remove('hidden'); 
            } else {
                badge.classList.add('hidden');
            }
        
        const notifList = document.getElementById('notifList');
        const notifHtml = data.data.notifications.slice(0, 15).map(n => `
            <div class="p-4 border-b border-slate-50 cursor-pointer transition-all ${n.is_read ? 'bg-white hover:bg-slate-50' : 'bg-brand-50/30 hover:bg-brand-50/50 border-l-4 border-l-brand-500'}" onclick="notifClick(${n.id}, ${n.post_id})">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full ${n.is_read ? 'bg-slate-100 text-slate-400' : 'bg-brand-100 text-brand-600'} flex items-center justify-center flex-shrink-0 transition-colors">
                        <i class="fa-solid ${n.title.toLowerCase().includes('approval') ? 'fa-check-double' : (n.title.toLowerCase().includes('comment') ? 'fa-comment' : 'fa-bell')} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="${n.is_read ? 'font-medium text-slate-600' : 'font-bold text-slate-900'} text-sm mb-1 line-clamp-1">${n.title}</div>
                        <div class="text-[13px] ${n.is_read ? 'text-slate-400' : 'text-slate-600'} line-clamp-2 leading-relaxed mb-2">${n.message}</div>
                        <div class="flex items-center gap-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                             <span class="flex items-center gap-1"><i class="fa-regular fa-clock"></i> ${formatDate(n.created_at)}</span>
                             ${!n.is_read ? '<span class="w-1.5 h-1.5 bg-brand-500 rounded-full"></span>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('') || '<div class="flex flex-col items-center justify-center py-12 px-6 text-center"><div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-3"><i class="fa-solid fa-bell-slash text-xl"></i></div><p class="text-slate-400 text-sm font-medium">No notifications yet</p></div>';
        
        notifList.innerHTML = notifHtml;
    }
    } catch (e) {
        console.error('Notification load failed', e);
    }
}

function toggleNotifications() { 
    const dropdown = document.getElementById('notifDropdown');
    const isHidden = dropdown.classList.contains('hidden');
    dropdown.classList.toggle('hidden');
    
    // Smart mobile backdrop
    if (window.innerWidth < 1024) {
        let overlay = document.getElementById('notifOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'notifOverlay';
            overlay.className = 'fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden transition-opacity duration-300 opacity-0';
            overlay.onclick = toggleNotifications;
            document.body.appendChild(overlay);
        }
        
        if (isHidden) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.style.opacity = '1', 10);
            document.body.style.overflow = 'hidden';
        } else {
            overlay.style.opacity = '0';
            setTimeout(() => overlay.classList.add('hidden'), 300);
            document.body.style.overflow = '';
        }
    }
}
async function notifClick(id, postId) { 
    await api('mark_notification_read', 'POST', { id }); 
    toggleNotifications(); 
    if (postId) openViewModal(postId); 
    loadNotifications(); 
}
async function markAllRead() { await api('mark_notification_read', 'POST', { mark_all: true }); loadNotifications(); }

// ==================== UTILITIES ====================
function escapeHtml(text) { const div = document.createElement('div'); div.textContent = text || ''; return div.innerHTML; }
function formatDate(str) { return str ? new Date(str).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''; }

let searchTimeout;
function debounceSearch() { clearTimeout(searchTimeout); searchTimeout = setTimeout(loadPosts, 500); }

// ==================== CALENDAR FUNCTIONS ====================
let calendarYear = new Date().getFullYear();
let calendarMonth = new Date().getMonth();
let calendarPosts = [];

function prevMonth() { calendarMonth--; if (calendarMonth < 0) { calendarMonth = 11; calendarYear--; } loadCalendar(); }
function nextMonth() { calendarMonth++; if (calendarMonth > 11) { calendarMonth = 0; calendarYear++; } loadCalendar(); }
function goToToday() { 
    const today = new Date();
    calendarYear = today.getFullYear();
    calendarMonth = today.getMonth();
    loadCalendar();
}

async function loadCalendar() {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('currentMonth').textContent = `${monthNames[calendarMonth]} ${calendarYear}`;
    
    const data = await api(`fetch_calendar&year=${calendarYear}&month=${calendarMonth + 1}`);
    calendarPosts = data.success ? data.data : [];
    
    // Update stats
    const scheduledCount = calendarPosts.filter(p => p.status === 'SCHEDULED').length;
    const publishedCount = calendarPosts.filter(p => p.status === 'PUBLISHED').length;
    document.getElementById('scheduledCount').textContent = scheduledCount;
    document.getElementById('publishedCount').textContent = publishedCount;
    
    renderCalendar();
}

function renderCalendar() {
    const grid = document.getElementById('calendarGrid');
    const firstDay = new Date(calendarYear, calendarMonth, 1).getDay();
    const daysInMonth = new Date(calendarYear, calendarMonth + 1, 0).getDate();
    const today = new Date();
    
    const platformIcons = {
        'Facebook': 'fa-brands fa-facebook', 'Instagram': 'fa-brands fa-instagram',
        'LinkedIn': 'fa-brands fa-linkedin', 'X': 'fa-brands fa-x-twitter',
        'TikTok': 'fa-brands fa-tiktok', 'YouTube': 'fa-brands fa-youtube',
        'Snapchat': 'fa-brands fa-snapchat', 'Website': 'fa-solid fa-globe'
    };
    
    const platformColors = {
        'Facebook': 'text-blue-600', 'Instagram': 'text-pink-600',
        'LinkedIn': 'text-blue-700', 'X': 'text-slate-800',
        'TikTok': 'text-slate-900', 'YouTube': 'text-red-600',
        'Snapchat': 'text-yellow-500', 'Website': 'text-indigo-600'
    };
    
    let html = '';
    // Empty cells for days before the 1st
    for (let i = 0; i < firstDay; i++) {
        html += '<div class="h-[140px] bg-slate-50/30 border-r border-b border-slate-100"></div>';
    }
    
    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${calendarYear}-${String(calendarMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = today.getFullYear() === calendarYear && today.getMonth() === calendarMonth && today.getDate() === day;
        const dayPosts = calendarPosts.filter(p => (p.scheduled_date || p.published_date || '').startsWith(dateStr));
        
        // Professional: Clean white background, today gets subtle brand accent
        const dayBg = isToday ? 'bg-brand-50/50' : 'bg-white';
        const dateStyle = isToday 
            ? 'w-6 h-6 flex items-center justify-center rounded-full bg-brand-600 text-white text-[10px] font-bold' 
            : 'text-[10px] text-slate-400 font-medium';
        
        html += `
            <div class="h-[140px] p-1.5 ${dayBg} border-r border-b border-slate-100 hover:bg-slate-50/50 transition-colors flex flex-col">
                <div class="flex items-center justify-between mb-1 flex-shrink-0">
                    <span class="${dateStyle}">${day}</span>
                    ${dayPosts.length > 0 ? `<span class="text-[9px] font-bold text-white bg-brand-500 px-1.5 py-0.5 rounded-full">${dayPosts.length}</span>` : ''}
                </div>
                <div class="space-y-1 flex-1 overflow-y-auto pr-0.5" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
                    ${dayPosts.map(p => {
                        const time = p.scheduled_date ? new Date(p.scheduled_date).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'}) : '';
                        
                        // Status styling
                        const isScheduled = p.status === 'SCHEDULED';
                        const statusDot = isScheduled ? 'bg-indigo-500' : 'bg-emerald-500';
                        
                        // Platforms
                        let platforms = [];
                        if (p.platforms) {
                            platforms = typeof p.platforms === 'string' ? JSON.parse(p.platforms) : p.platforms;
                        }
                        
                        const iconsHtml = platforms.slice(0, 2).map(plat => {
                            const icon = platformIcons[plat] || 'fa-solid fa-share-nodes';
                            const color = platformColors[plat] || 'text-slate-400';
                            return `<i class="${icon} ${color} text-[9px]"></i>`;
                        }).join('');
                        
                        // Compact card
                        return `
                            <div onclick="openViewModal(${p.id})" class="cursor-pointer p-1.5 rounded border border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm transition-all group">
                                <div class="flex items-center gap-1 mb-0.5">
                                    <div class="w-1 h-1 rounded-full ${statusDot} flex-shrink-0"></div>
                                    <span class="text-[9px] font-mono text-slate-400">${time}</span>
                                </div>
                                <div class="text-[10px] font-semibold text-slate-700 truncate leading-tight group-hover:text-brand-600 transition-colors">${escapeHtml(p.title)}</div>
                                <div class="flex items-center gap-1 mt-0.5">${iconsHtml}</div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }
    
    grid.innerHTML = html;
}

// Show all posts for a specific day in a modal/popup
function showDayPosts(dateStr) {
    const dayPosts = calendarPosts.filter(p => (p.scheduled_date || p.published_date || '').startsWith(dateStr));
    if (dayPosts.length > 0) {
        // Open the first post for now (can be enhanced to show a list modal)
        openViewModal(dayPosts[0].id);
    }
}

// ==================== USERS MANAGEMENT ====================
let allUsers = [];

async function loadUsers() {
    if (!['admin', 'manager'].includes(app.user?.role?.toLowerCase())) return;
    
    const data = await api('fetch_users');
    if (data.success) {
        allUsers = data.data;
        document.getElementById('totalUsersCount').textContent = allUsers.length;
        document.getElementById('activeUsersCount').textContent = allUsers.filter(u => u.is_active).length;
        document.getElementById('adminUsersCount').textContent = allUsers.filter(u => u.role?.toLowerCase() === 'admin').length;
        
        // Render Action Button
        const actionContainer = document.getElementById('userActionsContainer');
        if (actionContainer) {
            actionContainer.innerHTML = app.user?.role?.toLowerCase() === 'manager' ? `
                <button onclick="openAddUserModal()" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg font-bold text-sm flex items-center gap-2 shadow-lg shadow-slate-900/10 transition-all">
                    <i class="fa-solid fa-plus"></i>
                    Add User
                </button>
            ` : '';
        }

        renderUsersTable();
    }
}

function renderUsersTable() {
    const tableBody = document.getElementById('usersTableBody');
    const mobileGrid = document.getElementById('usersMobileGrid');
    
    // Desktop HTML
    const desktopHtml = allUsers.map(u => `
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center font-bold">
                        ${(u.full_name || u.username)[0].toUpperCase()}
                    </div>
                    <div>
                        <div class="font-medium text-slate-800">${escapeHtml(u.full_name || u.username)}</div>
                        <div class="text-sm text-slate-400">@${u.username}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-full text-xs font-medium ${u.role === 'admin' ? 'bg-purple-100 text-purple-700' : (u.role === 'manager' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700')}">
                    ${u.role}
                </span>
            </td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-full text-xs font-medium ${u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                    ${u.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-slate-500">${formatDate(u.created_at)}</td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    ${app.user?.role?.toLowerCase() === 'manager' ? `
                    <button onclick="openEditUserModal(${u.id})" class="px-3 py-1 text-xs rounded-lg bg-brand-100 text-brand-600 hover:bg-brand-200">
                        Edit
                    </button>
                    <button onclick="toggleUserStatus(${u.id}, ${u.is_active ? 0 : 1})" class="px-3 py-1 text-xs rounded-lg ${u.is_active ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-green-100 text-green-600 hover:bg-green-200'}">
                        ${u.is_active ? 'Deactivate' : 'Activate'}
                    </button>
                    ` : '<span class="text-xs text-slate-400">View Only</span>'}
                </div>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="5" class="text-center py-8 text-slate-400">No users found</td></tr>';

    // Mobile HTML
    const mobileHtml = allUsers.map(u => `
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center font-bold">
                        ${(u.full_name || u.username)[0].toUpperCase()}
                    </div>
                    <div>
                        <div class="font-bold text-slate-800">${escapeHtml(u.full_name || u.username)}</div>
                        <div class="text-xs text-slate-400">@${u.username}</div>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${u.role === 'admin' ? 'bg-purple-100 text-purple-700' : (u.role === 'manager' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700')}">
                        ${u.role}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                        ${u.is_active ? 'Active' : 'Inactive'}
                    </span>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-50 flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Joined: ${formatDate(u.created_at)}</span>
                <div class="flex gap-2">
                    ${app.user?.role?.toLowerCase() === 'manager' ? `
                    <button onclick="openEditUserModal(${u.id})" class="p-2 text-brand-600 bg-brand-50 rounded-lg hover:bg-brand-100">
                        <i class="fa-solid fa-pen text-sm"></i>
                    </button>
                    <button onclick="toggleUserStatus(${u.id}, ${u.is_active ? 0 : 1})" class="p-2 ${u.is_active ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'} rounded-lg">
                        <i class="fa-solid ${u.is_active ? 'fa-user-slash' : 'fa-user-check'} text-sm"></i>
                    </button>
                    ` : '<span class="text-[10px] font-bold text-slate-300">READ ONLY</span>'}
                </div>
            </div>
        </div>
    `).join('') || '<div class="text-center py-8 text-slate-400">No users found</div>';

    if (tableBody) tableBody.innerHTML = desktopHtml;
    if (mobileGrid) mobileGrid.innerHTML = mobileHtml;
}

async function toggleUserStatus(id, active) {
    const data = await api('update_user_status', 'POST', { id, is_active: active });
    if (data.success) { toast(active ? 'User activated' : 'User deactivated', 'success'); loadUsers(); }
    else toast(data.error || 'Failed to update user', 'error');
}

function openEditUserModal(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;
    
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUserUsername').value = user.username;
    document.getElementById('editUserFullName').value = user.full_name || '';
    document.getElementById('editUserRole').value = user.role;
    document.getElementById('editUserPassword').value = '';
    
    document.getElementById('editUserModal').classList.remove('hidden');
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.add('hidden');
}

document.getElementById('editUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const data = {
        id: parseInt(document.getElementById('editUserId').value),
        full_name: document.getElementById('editUserFullName').value.trim(),
        role: document.getElementById('editUserRole').value,
        password: document.getElementById('editUserPassword').value
    };
    
    if (!data.full_name) {
        toast('Full name is required', 'error');
        return;
    }
    
    const result = await api('update_user', 'POST', data);
    if (result.success) {
        toast('User updated successfully', 'success');
        closeEditUserModal();
        loadUsers();
    } else {
        toast(result.message || 'Failed to update user', 'error');
    }
});

function openAddUserModal() {
    document.getElementById('addUserUsername').value = '';
    document.getElementById('addUserFullName').value = '';
    document.getElementById('addUserRole').value = 'staff';
    document.getElementById('addUserPassword').value = '';
    document.getElementById('addUserModal').classList.remove('hidden');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
}

document.getElementById('addUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const data = {
        username: document.getElementById('addUserUsername').value.trim(),
        full_name: document.getElementById('addUserFullName').value.trim(),
        role: document.getElementById('addUserRole').value,
        password: document.getElementById('addUserPassword').value
    };
    
    if (!data.username) {
        toast('Username is required', 'error');
        return;
    }
    if (!data.full_name) {
        toast('Full name is required', 'error');
        return;
    }
    if (!data.password) {
        toast('Password is required', 'error');
        return;
    }
    
    const result = await api('create_user', 'POST', data);
    if (result.success) {
        toast('User created successfully', 'success');
        closeAddUserModal();
        loadUsers();
    } else {
        toast(result.message || 'Failed to create user', 'error');
    }
});


function toast(msg, type = 'info') {
    const config = { 
        success: { bg: 'bg-emerald-600', icon: 'fa-circle-check' }, 
        error: { bg: 'bg-rose-600', icon: 'fa-circle-exclamation' }, 
        info: { bg: 'bg-slate-800', icon: 'fa-circle-info' } 
    };
    const style = config[type] || config.info;
    
    const t = document.createElement('div');
    t.className = `${style.bg} text-white px-6 py-3 rounded-lg shadow-xl text-sm font-medium flex items-center gap-3 transform translate-y-4 opacity-0 transition-all duration-300 min-w-[300px] border border-white/10 z-50`;
    t.innerHTML = `<i class="fa-solid ${style.icon} text-lg opacity-90"></i> <span>${msg}</span>`;
    
    document.getElementById('toasts').appendChild(t);
    
    // Animate in
    requestAnimationFrame(() => {
        t.classList.remove('translate-y-4', 'opacity-0');
    });

    // Remove after delay
    setTimeout(() => {
        t.classList.add('translate-y-4', 'opacity-0');
        setTimeout(() => t.remove(), 300);
    }, 3500);
}

document.addEventListener('click', e => { 
    // Specific check for notification dropdown to avoid accidental closures or non-closures
    const notifDropdown = document.getElementById('notifDropdown');
    const isNotifButton = e.target.closest('button[onclick="toggleNotifications()"]');
    const isInsideNotif = e.target.closest('#notifDropdown');
    
    if (!isNotifButton && !isInsideNotif) {
        notifDropdown.classList.add('hidden');
    }

    // Handle other dropdowns (like notification badge clicks or others that might use .relative if any)
    if (!e.target.closest('.relative') && !isInsideNotif) {
         // This is a bit generic, keeping it for other possible future dropdowns but specifically excluded notif
    }    
    // Auto-close sidebar when clicking outside on mobile
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (window.innerWidth < 1024 && !sidebar.contains(e.target) && !e.target.closest('button[onclick="toggleSidebar()"]')) {
        closeSidebarOnMobile();
    }
});

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isHidden = sidebar.classList.contains('-translate-x-full');
    
    if (isHidden) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function closeSidebarOnMobile() {
    if (window.innerWidth < 1024) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Reset sidebar and modal state on window resize (smart refresh)
let lastWidth = window.innerWidth;
window.addEventListener('resize', () => {
    const currentWidth = window.innerWidth;
    const isLg = currentWidth >= 1024;
    const wasLg = lastWidth >= 1024;
    
    if (isLg) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.remove('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        document.body.style.overflow = '';
    } else {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) sidebar.classList.add('-translate-x-full');
    }
    
    // Refresh view modal if open ONLY if breakpoint changed (to fix media layout without resetting video)
    if (isLg !== wasLg && app.currentPost && !document.getElementById('viewModal').classList.contains('hidden')) {
        openViewModal(app.currentPost.id);
    }
    
    lastWidth = currentWidth;
});

// ==================== AUDIO / NOTIFICATIONS ====================
let audioCtx = null;

function initAudio() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
}

function playNotificationSound(force = false) {
    if (!audioCtx) initAudio();
    if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
    
    if (!audioCtx) {
        if (force) alert("Audio is disabled. interact with the page first.");
        return;
    }

    try {
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        // Pleasant "Glass/Bell" Chime
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(784, audioCtx.currentTime); // G5
        oscillator.frequency.exponentialRampToValueAtTime(1174, audioCtx.currentTime + 0.1); // D6 (Upward soft chime)

        gainNode.gain.setValueAtTime(0.05, audioCtx.currentTime); // Start quiet
        gainNode.gain.linearRampToValueAtTime(0.2, audioCtx.currentTime + 0.05); // Attack
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 1.2); // Long elegant decay

        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 1.2);
    } catch (e) {
        console.warn("Audio playback failed", e);
    }
}

// Safely start init
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
// Company Switcher Logic
let companiesLoaded = false;
async function toggleCompanySwitcher() {
    const menu = document.getElementById('companySwitcherMenu');
    menu.classList.toggle('hidden');
    
    if (!menu.classList.contains('hidden') && !companiesLoaded) {
        try {
            const res = await fetch('api.php?action=get_companies');
            const json = await res.json();
            if (json.success) {
                const list = document.getElementById('companySwitcherList');
                list.innerHTML = '';
                const currentId = <?= isset($_SESSION['company_id']) ? (int)$_SESSION['company_id'] : 1 ?>;
                
                json.data.forEach(c => {
                    const btn = document.createElement('button');
                    btn.className = `w-full text-left px-3 py-2 flex items-center gap-3 hover:bg-white/10 transition-colors ${c.id == currentId ? 'bg-white/5' : ''}`;
                    btn.onclick = () => switchCompany(c.id);
                    btn.innerHTML = `
                        <div class="w-6 h-6 rounded bg-white p-[2px] flex items-center justify-center flex-shrink-0">
                            <img src="${c.logo_url}" alt="" class="max-w-full max-h-full object-contain">
                        </div>
                        <span class="text-sm font-medium text-slate-300 truncate ${c.id == currentId ? 'text-white' : ''}">${c.name}</span>
                        ${c.id == currentId ? '<svg class="w-4 h-4 ml-auto text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'  : ''}
                    `;
                    list.appendChild(btn);
                });
                companiesLoaded = true;
            }
        } catch (e) {
            console.error('Failed to load companies', e);
        }
    }
}

async function switchCompany(id) {
    try {
        const res = await fetch('api.php?action=switch_company', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ company_id: id })
        });
        const json = await res.json();
        if (json.success) {
            localStorage.clear();
            sessionStorage.clear();
            window.location.reload();
        } else {
            alert(json.message || 'Failed to switch company');
        }
    } catch (e) {
        console.error(e);
        alert('An error occurred');
    }
}

document.addEventListener('click', (e) => {
    const btn = document.getElementById('companySwitcherBtn');
    const menu = document.getElementById('companySwitcherMenu');
    if (btn && menu && !btn.parentElement.contains(e.target) && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }
});
</script>
            </main>
        </div>
    </div>
</body>
</html>
