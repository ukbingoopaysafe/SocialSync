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
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #f8fafc; }
        
        /* Modern Card Style */
        .post-card { 
            background: white; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .post-card:hover { 
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        /* Clean Upload Zone */
        .upload-zone { 
            border: 2px dashed #e2e8f0; 
            border-radius: 12px;
            transition: all 0.15s ease;
            background: #fafafa;
        }
        .upload-zone:hover, .upload-zone.drag-over { 
            border-color: #0ea5e9; 
            background: #f0f9ff;
        }
        
        /* Refined Status Badge */
        .status-badge { 
            font-size: 0.65rem; 
            padding: 0.2rem 0.6rem; 
            border-radius: 6px; 
            font-weight: 500; 
            text-transform: uppercase; 
            letter-spacing: 0.03em;
        }
        
        /* Smooth Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Button Transitions */
        button, a { transition: all 0.15s ease; }
        
        /* Modal Backdrop */
        .modal-backdrop { backdrop-filter: blur(4px); }
        
        /* Platform Checkbox Styling */
        .platform-checkbox:has(input:checked) {
            background: #ecfdf5;
            border-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }
        .platform-checkbox:has(input:checked)::after {
            content: '✓';
            position: absolute;
            top: -4px;
            right: -4px;
            background: #10b981;
            color: white;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .platform-checkbox { position: relative; }
    </style>
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
        <aside id="sidebar" class="w-16 hover:w-56 transition-all duration-300 bg-[#0a1628] flex flex-col fixed h-full z-50 group">
            <!-- Logo -->
            <div class="h-14 flex items-center justify-center border-b border-slate-700/50 px-2 overflow-hidden">
                <!-- Dynamic Company Logo -->
                <img id="sidebarLogo" src="images/Final_Logo White.png" alt="Company" class="h-8 max-w-full object-contain hidden group-hover:block">
                <img id="sidebarLogoSmall" src="images/Final_Logo White.png" alt="Company" class="w-10 h-10 object-contain group-hover:hidden" style="image-rendering: -webkit-optimize-contrast;">
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 py-4 px-2 space-y-1">
                <button onclick="switchTab('dashboard')" id="tabDashboard" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Dashboard</span>
                </button>
                <button onclick="switchTab('board')" id="tabBoard" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Board</span>
                </button>
                <button onclick="switchTab('calendar')" id="tabCalendar" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Calendar</span>
                </button>
                <div id="adminLink" class="hidden">
                    <button onclick="switchTab('users')" id="tabUsers" class="sidebar-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Users</span>
                    </button>
                </div>
            </nav>
            
            <!-- Bottom Actions -->
            <div class="p-2 border-t border-slate-700/50">
                <button onclick="logout()" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Logout</span>
                </button>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <div class="flex-1 ml-16">
            <!-- Top Header -->
            <header class="h-14 bg-[#0a1628] border-b border-slate-700/50 sticky top-0 z-40 flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <h1 id="pageTitle" class="text-lg font-semibold text-white">Dashboard</h1>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" id="globalSearch" placeholder="Search..." class="w-64 pl-10 pr-4 py-2 bg-white/10 border-0 rounded-lg text-sm text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:bg-white/20 transition-colors">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <!-- New Post Button -->
                    <button onclick="openCreateModal()" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        New Post
                    </button>
                    <!-- Notifications -->
                    <div class="relative">
                        <button onclick="toggleNotifications()" class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span id="notifBadge" class="hidden absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-medium">0</span>
                        </button>
                        <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-200 z-50">
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
            <main class="p-6">
        <!-- Dashboard View - Advanced Analytics -->
        <div id="dashboardView" class="hidden max-w-7xl mx-auto">
            <!-- Header with Health Score -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div id="healthRing" class="relative w-16 h-16">
                        <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path id="healthPath" class="text-emerald-500" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <span id="healthScore" class="absolute inset-0 flex items-center justify-center text-sm font-bold text-slate-700">0</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">Analytics Dashboard</h2>
                        <p id="healthLabel" class="text-slate-500 text-sm">Content Health Score</p>
                    </div>
                </div>
                <select id="analyticsPeriod" onchange="loadDashboard()" class="px-4 py-2 border border-slate-200 rounded-lg text-sm bg-white shadow-sm">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
            
            <!-- Smart Recommendations -->
            <div id="recommendationsSection" class="mb-6 space-y-3"></div>
            
            <!-- Overview KPIs with Trends -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-500 text-xs font-medium uppercase tracking-wide">Total Posts</span>
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span id="kpiTotal" class="text-3xl font-bold text-slate-800">0</span>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-500 text-xs font-medium uppercase tracking-wide">Published</span>
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span id="kpiPublished" class="text-3xl font-bold text-emerald-600">0</span>
                        <span id="kpiPublishedTrend" class="text-xs font-medium px-1.5 py-0.5 rounded"></span>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-500 text-xs font-medium uppercase tracking-wide">Scheduled</span>
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span id="kpiScheduled" class="text-3xl font-bold text-indigo-600">0</span>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-500 text-xs font-medium uppercase tracking-wide">Pending</span>
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span id="kpiPending" class="text-3xl font-bold text-amber-600">0</span>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-500 text-xs font-medium uppercase tracking-wide">Approval Rate</span>
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span id="kpiApprovalRate" class="text-3xl font-bold text-blue-600">0%</span>
                        <span id="kpiApprovalTrend" class="text-xs font-medium px-1.5 py-0.5 rounded"></span>
                    </div>
                </div>
            </div>
            
            <!-- Time Insights + Bottleneck Row -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-5 text-white shadow-lg">
                    <div class="text-indigo-100 text-xs font-medium uppercase tracking-wide mb-2">Best Day to Publish</div>
                    <div id="bestDay" class="text-2xl font-bold">-</div>
                    <div id="bestDayCount" class="text-indigo-200 text-sm"></div>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-5 text-white shadow-lg">
                    <div class="text-amber-100 text-xs font-medium uppercase tracking-wide mb-2">Peak Hour</div>
                    <div id="bestHour" class="text-2xl font-bold">-</div>
                    <div id="bestHourCount" class="text-amber-200 text-sm"></div>
                </div>
                <div class="lg:col-span-2 bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <div class="text-slate-500 text-xs font-medium uppercase tracking-wide mb-3">Workflow Bottlenecks</div>
                    <div id="bottleneckBars" class="space-y-2"></div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Content Pipeline Funnel - IMPROVED -->
                <div class="lg:col-span-2 bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-6">Content Pipeline</h3>
                    <div id="workflowFunnel" class="grid grid-cols-6 gap-3 h-44"></div>
                </div>
                
                <!-- Platform Distribution -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-4">Platform Distribution</h3>
                    <div class="relative h-32">
                        <canvas id="platformChart"></canvas>
                    </div>
                    <div id="platformLegend" class="mt-4 space-y-2 text-sm"></div>
                </div>
            </div>
            
            <!-- Team Performance with Score + Scheduled -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- User Performance - DETAILED -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800">Team Performance</h3>
                        <span id="teamMemberCount" class="text-xs text-slate-400"></span>
                    </div>
                    <div id="userPerformanceCards" class="space-y-4 max-h-[500px] overflow-y-auto pr-2"></div>
                </div>
                
                <!-- Upcoming Scheduled -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800">Upcoming Scheduled</h3>
                        <a href="calendar.php" class="text-brand-500 text-sm hover:underline">View Calendar →</a>
                    </div>
                    <div id="upcomingScheduled" class="space-y-3"></div>
                </div>
            </div>
            
            <!-- Recent Activity - DETAILED -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-800">Recent Activity</h3>
                    <span id="activityCount" class="text-xs text-slate-400"></span>
                </div>
                <div id="recentActivity" class="space-y-4 max-h-[600px] overflow-y-auto pr-2"></div>
            </div>
        </div>

        <!-- Board View -->
        <div id="boardView" class="hidden">
            <!-- Filters Row -->
            <div class="mb-4 flex flex-wrap gap-3 items-center">
                <select id="platformFilter" onchange="loadPosts()" class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Platforms</option>
                    <option value="Facebook">Facebook</option><option value="Instagram">Instagram</option><option value="LinkedIn">LinkedIn</option>
                    <option value="X">X</option><option value="TikTok">TikTok</option><option value="YouTube">YouTube</option><option value="Snapchat">Snapchat</option><option value="Website">Website</option>
                </select>
                <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                    <input type="checkbox" id="myPostsFilter" onchange="loadPosts()" class="rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                    My Posts
                </label>
                <div class="relative flex-1 max-w-xs">
                    <input type="text" id="searchInput" placeholder="Search posts..." onkeyup="debounceSearch()" class="w-full px-4 py-2 pl-10 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            
            <!-- Status Tabs -->
            <div class="bg-white rounded-xl border border-slate-200 mb-6 overflow-hidden">
                <div class="flex flex-wrap border-b border-slate-200">
                    <button onclick="setStatusFilter('')" id="tabAll" class="status-tab px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:bg-slate-50 transition-colors flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-gradient-to-r from-violet-400 to-slate-400"></span>
                        All <span id="countAll" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('IDEA')" id="tabIDEA" class="status-tab px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:bg-slate-50 transition-colors flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-violet-400"></span>
                        Ideas <span id="countIDEA" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('DRAFT')" id="tabDRAFT" class="status-tab px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:bg-slate-50 transition-colors flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                        Drafts <span id="countDRAFT" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('PENDING_REVIEW')" id="tabPENDING_REVIEW" class="status-tab px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:bg-slate-50 transition-colors flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        Pending <span id="countPENDING_REVIEW" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('APPROVED')" id="tabAPPROVED" class="status-tab px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:bg-slate-50 transition-colors flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Approved <span id="countAPPROVED" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('SCHEDULED')" id="tabSCHEDULED" class="status-tab px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:bg-slate-50 transition-colors flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                        Scheduled <span id="countSCHEDULED" class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0</span>
                    </button>
                    <button onclick="setStatusFilter('PUBLISHED')" id="tabPUBLISHED" class="status-tab px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:bg-slate-50 transition-colors flex items-center gap-2">
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
            
            <!-- Platform Legend -->
            <div class="flex flex-wrap gap-3 mb-4 text-xs">
                <span class="text-slate-500 font-medium">Platforms:</span>
                <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-blue-600 text-white flex items-center justify-center text-[10px]"><i class="fa-brands fa-facebook"></i></span> Facebook</div>
                <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-gradient-to-r from-purple-500 to-pink-500 text-white flex items-center justify-center text-[10px]"><i class="fa-brands fa-instagram"></i></span> Instagram</div>
                <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-blue-700 text-white flex items-center justify-center text-[10px]"><i class="fa-brands fa-linkedin"></i></span> LinkedIn</div>
                <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-black text-white flex items-center justify-center text-[10px]"><i class="fa-brands fa-x-twitter"></i></span> X</div>
                <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-red-600 text-white flex items-center justify-center text-[10px]"><i class="fa-brands fa-youtube"></i></span> YouTube</div>
                <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-yellow-400 text-slate-800 flex items-center justify-center text-[10px]"><i class="fa-brands fa-snapchat"></i></span> Snapchat</div>
            </div>
            
            <!-- Calendar Grid -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="grid grid-cols-7 bg-gradient-to-r from-slate-700 to-slate-800">
                    <div class="py-3 text-center text-sm font-medium text-white">Sun</div>
                    <div class="py-3 text-center text-sm font-medium text-white">Mon</div>
                    <div class="py-3 text-center text-sm font-medium text-white">Tue</div>
                    <div class="py-3 text-center text-sm font-medium text-white">Wed</div>
                    <div class="py-3 text-center text-sm font-medium text-white">Thu</div>
                    <div class="py-3 text-center text-sm font-medium text-white">Fri</div>
                    <div class="py-3 text-center text-sm font-medium text-white">Sat</div>
                </div>
                <div id="calendarGrid" class="grid grid-cols-7"></div>
            </div>
        </div>

        <!-- Users View (Admin Only) -->
        <div id="usersView" class="hidden">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div class="flex gap-4">
                    <div class="bg-white rounded-xl px-6 py-4 border border-slate-200">
                        <div id="totalUsersCount" class="text-2xl font-bold text-slate-800">0</div>
                        <div class="text-sm text-slate-500">Total Users</div>
                    </div>
                    <div class="bg-white rounded-xl px-6 py-4 border border-slate-200">
                        <div id="activeUsersCount" class="text-2xl font-bold text-emerald-600">0</div>
                        <div class="text-sm text-slate-500">Active</div>
                    </div>
                    <div class="bg-white rounded-xl px-6 py-4 border border-slate-200">
                        <div id="adminUsersCount" class="text-2xl font-bold text-purple-600">0</div>
                        <div class="text-sm text-slate-500">Admins</div>
                    </div>
                </div>
                <button onclick="openAddUserModal()" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add User
                </button>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Created</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- ==================== CREATE POST MODAL ==================== -->
    <div id="createModal" dir="rtl" class="hidden fixed inset-0 bg-black/60 z-50 flex items-start justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-8">
            <div class="bg-navy-900 text-white px-6 py-4 flex justify-between rounded-t-2xl">
                <h2 class="text-xl font-bold">✨ Create New Post</h2>
                <button onclick="closeCreateModal()" class="text-2xl hover:text-gold-400">&times;</button>
            </div>
            <form id="createForm" class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="createTitle" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-gold-500 focus:border-gold-500" placeholder="Enter a compelling title...">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5 flex justify-between items-center">
                        <span>Content <span class="text-red-500">*</span></span>
                        <button type="button" onclick="toggleEmojiPicker('createContent', 'createEmojiBtn')" id="createEmojiBtn" class="text-slate-400 hover:text-brand-500 transition-colors">
                            <i class="fa-regular fa-face-smile text-lg"></i>
                        </button>
                    </label>
                    <div class="relative">
                        <textarea id="createContent" rows="4" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-gold-500" placeholder="Write your post content..."></textarea>
                        <div id="createEmojiPickerContainer" class="absolute z-50 hidden mt-2 right-0 shadow-2xl rounded-xl overflow-hidden border border-slate-200">
                             <emoji-picker class="light"></emoji-picker>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-2">Platforms <span class="text-red-500">*</span> <span class="text-xs text-slate-400 font-normal">(Select one or more)</span></label>
                        <div class="grid grid-cols-4 gap-3" id="createPlatformsGrid">
                            <label class="platform-checkbox flex items-center gap-3 p-2 border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all">
                                <input type="checkbox" name="createPlatforms" value="Facebook" class="hidden">
                                <span class="w-6 h-6 bg-blue-600 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-facebook"></i></span>
                                <span class="text-sm">Facebook</span>
                            </label>
                            <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-pink-50 hover:border-pink-300 transition-all">
                                <input type="checkbox" name="createPlatforms" value="Instagram" class="hidden">
                                <span class="w-6 h-6 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-instagram"></i></span>
                                <span class="text-sm">Instagram</span>
                            </label>
                            <label class="platform-checkbox flex items-center gap-3 p-2 border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all">
                                <input type="checkbox" name="createPlatforms" value="LinkedIn" class="hidden">
                                <span class="w-6 h-6 bg-blue-700 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-linkedin"></i></span>
                                <span class="text-sm">LinkedIn</span>
                            </label>
                            <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-slate-100 hover:border-slate-400 transition-all">
                                <input type="checkbox" name="createPlatforms" value="X" class="hidden">
                                <span class="w-6 h-6 bg-black text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-x-twitter"></i></span>
                                <span class="text-sm">X</span>
                            </label>
                            <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-slate-100 hover:border-slate-400 transition-all">
                                <input type="checkbox" name="createPlatforms" value="TikTok" class="hidden">
                                <span class="w-6 h-6 bg-slate-800 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-tiktok"></i></span>
                                <span class="text-sm">TikTok</span>
                            </label>
                            <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-red-50 hover:border-red-300 transition-all">
                                <input type="checkbox" name="createPlatforms" value="YouTube" class="hidden">
                                <span class="w-6 h-6 bg-red-600 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-youtube"></i></span>
                                <span class="text-sm">YouTube</span>
                            </label>
                            <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-yellow-50 hover:border-yellow-300 transition-all">
                                <input type="checkbox" name="createPlatforms" value="Snapchat" class="hidden">
                                <span class="w-6 h-6 bg-yellow-400 text-slate-800 rounded flex items-center justify-center text-xs"><i class="fa-brands fa-snapchat"></i></span>
                                <span class="text-sm">Snapchat</span>
                            </label>
                            <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-all">
                                <input type="checkbox" name="createPlatforms" value="Website" class="hidden">
                                <span class="w-6 h-6 bg-indigo-600 text-white rounded flex items-center justify-center text-xs"><i class="fa-solid fa-globe"></i></span>
                                <span class="text-sm">Website</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Start As</label>
                        <select id="createStatus" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-gold-500">
                            <option value="DRAFT">📝 Draft (Ready to work)</option>
                            <option value="IDEA">💡 Idea (Just a suggestion)</option>
                        </select>
                    </div>
                </div>
                <!-- Media Upload (Multiple) -->
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Attach Media (Optional) <span class="text-xs text-slate-400 font-normal">Multiple files allowed</span></label>
                    <div id="createMediaGallery" class="grid grid-cols-4 gap-2 mb-3"></div>
                    <div class="upload-zone rounded-xl p-4 text-center cursor-pointer border-2 border-dashed border-slate-300 hover:border-sky-400 transition-colors" onclick="document.getElementById('createFileInput').click()">
                        <input type="file" id="createFileInput" class="hidden" accept="image/*,video/*" multiple onchange="previewCreateFiles(event)">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                            <div class="text-left">
                                <p class="text-slate-600 text-sm font-medium">Click to add media</p>
                                <p class="text-slate-400 text-xs">Max 100MB each • JPG, PNG, GIF, MP4</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="createUrgent" class="w-5 h-5 text-red-500 rounded focus:ring-red-500">
                        <span class="text-sm font-medium">🔥 Mark as Urgent</span>
                    </label>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-medium py-3 rounded-lg">Create Post</button>
                    <button type="button" onclick="closeCreateModal()" class="px-6 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-lg font-medium">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== VIEW POST MODAL (Read-Only) ==================== -->
    <div id="viewModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/50 modal-backdrop z-50 flex items-start justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl my-8 border border-slate-200">
            <!-- Header -->
            <div class="px-6 py-4 flex justify-between items-center border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <span id="viewStatusBadge" class="status-badge bg-sky-100 text-sky-700">DRAFT</span>
                    <div id="viewPlatformBadge" class="flex flex-wrap gap-1.5"></div>
                </div>
                <div class="flex items-center gap-1">
                    <button id="viewEditBtn" onclick="switchToEditMode()" class="text-slate-400 hover:text-brand-500 hover:bg-slate-100 p-2 rounded-lg transition-all" title="Edit Post">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button id="viewDeleteBtn" onclick="deletePost()" class="text-slate-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-all" title="Delete Post">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    <button onclick="closeViewModal()" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-lg ml-2 text-xl font-light">&times;</button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <!-- Media Preview -->
                <div id="viewMediaContainer" class="hidden mb-6">
                    <div id="viewMediaWrapper"></div>
                </div>
                
                <!-- Title & Meta -->
                <div class="mb-6">
                    <h1 id="viewTitle" class="text-2xl font-bold text-navy-900 mb-2"></h1>
                    <div class="flex items-center gap-4 text-sm text-slate-500">
                        <span id="viewAuthor" class="flex items-center gap-1"></span>
                        <span id="viewDate"></span>
                        <span id="viewUrgentBadge" class="hidden text-red-600 font-medium text-xs bg-red-50 px-2 py-0.5 rounded">URGENT</span>
                    </div>
                </div>
                
                <!-- Changes Requested Notice -->
                <div id="viewChangesNotice" class="hidden bg-slate-50 border-l-4 border-slate-400 rounded-r-md p-4 mb-6">
                    <div class="font-medium text-slate-700 text-sm mb-1">Revision Requested</div>
                    <div id="viewChangesReason" class="text-slate-500 text-sm"></div>
                </div>
                
                <!-- Content -->
                <div class="prose max-w-none mb-8">
                    <p id="viewContent" class="text-slate-700 whitespace-pre-wrap leading-relaxed"></p>
                </div>
                
                <!-- Action Buttons (Workflow) -->
                <div id="viewActions" class="border-t pt-6 mb-6">
                    <div id="actionButtons" class="flex flex-wrap gap-3"></div>
                </div>
                
                <!-- Comments Section -->
                <div class="border-t pt-6">
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">💬 Comments <span id="viewCommentCount" class="text-sm font-normal text-slate-400"></span></h3>
                    <div id="viewComments" class="space-y-4 mb-4 max-h-64 overflow-y-auto"></div>
                    <div class="flex gap-2">
                        <input type="text" id="viewNewComment" placeholder="Add a comment..." class="flex-1 px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-gold-500" onkeypress="if(event.key==='Enter')addViewComment()">
                        <button onclick="addViewComment()" class="px-5 py-2.5 bg-gold-500 hover:bg-gold-600 text-navy-900 font-semibold rounded-xl">Send</button>
                    </div>
                </div>
                
                <!-- Activity Log (Collapsible) -->
                <details class="border-t pt-6 mt-6" open>
                    <summary class="font-semibold text-slate-700 cursor-pointer hover:text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Activity Timeline
                        <span id="activityStats" class="text-xs font-normal text-slate-400 ml-2"></span>
                    </summary>
                    <div id="viewActivity" class="mt-4 space-y-3 max-h-64 overflow-y-auto border-l-2 border-slate-200 ml-2 pl-4"></div>
                </details>
            </div>
        </div>
    </div>

    <!-- ==================== EDIT POST MODAL ==================== -->
    <div id="editModal" dir="rtl" class="hidden fixed inset-0 bg-black/60 z-50 flex items-start justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-8">
            <div class="bg-navy-900 text-white px-6 py-4 flex justify-between rounded-t-2xl">
                <h2 class="text-xl font-bold">✏️ Edit Post</h2>
                <button onclick="closeEditModal()" class="text-2xl hover:text-gold-400">&times;</button>
            </div>
            <form id="editForm" class="p-6 space-y-5">
                <input type="hidden" id="editPostId">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="editTitle" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-gold-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5 flex justify-between items-center">
                        <span>Content <span class="text-red-500">*</span></span>
                        <button type="button" onclick="toggleEmojiPicker('editContent', 'editEmojiBtn')" id="editEmojiBtn" class="text-slate-400 hover:text-brand-500 transition-colors">
                            <i class="fa-regular fa-face-smile text-lg"></i>
                        </button>
                    </label>
                    <div class="relative">
                        <textarea id="editContent" rows="5" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-gold-500"></textarea>
                        <div id="editEmojiPickerContainer" class="absolute z-50 hidden mt-2 right-0 shadow-2xl rounded-xl overflow-hidden border border-slate-200">
                             <emoji-picker class="light"></emoji-picker>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Platforms <span class="text-red-500">*</span> <span class="text-xs text-slate-400 font-normal">(Select one or more)</span></label>
                    <div class="grid grid-cols-4 gap-3" id="editPlatformsGrid">
                        <label class="platform-checkbox flex items-center gap-3 p-2 border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all">
                            <input type="checkbox" name="editPlatforms" value="Facebook" class="hidden">
                            <span class="w-6 h-6 bg-blue-600 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-facebook"></i></span>
                            <span class="text-sm">Facebook</span>
                        </label>
                        <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-pink-50 hover:border-pink-300 transition-all">
                            <input type="checkbox" name="editPlatforms" value="Instagram" class="hidden">
                            <span class="w-6 h-6 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-instagram"></i></span>
                            <span class="text-sm">Instagram</span>
                        </label>
                        <label class="platform-checkbox flex items-center gap-3 p-2 border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all">
                            <input type="checkbox" name="editPlatforms" value="LinkedIn" class="hidden">
                            <span class="w-6 h-6 bg-blue-700 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-linkedin"></i></span>
                            <span class="text-sm">LinkedIn</span>
                        </label>
                        <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-slate-100 hover:border-slate-400 transition-all">
                            <input type="checkbox" name="editPlatforms" value="X" class="hidden">
                            <span class="w-6 h-6 bg-black text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-x-twitter"></i></span>
                            <span class="text-sm">X</span>
                        </label>
                        <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-slate-100 hover:border-slate-400 transition-all">
                            <input type="checkbox" name="editPlatforms" value="TikTok" class="hidden">
                            <span class="w-6 h-6 bg-slate-800 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-tiktok"></i></span>
                            <span class="text-sm">TikTok</span>
                        </label>
                        <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-red-50 hover:border-red-300 transition-all">
                            <input type="checkbox" name="editPlatforms" value="YouTube" class="hidden">
                            <span class="w-6 h-6 bg-red-600 text-white rounded flex items-center justify-center text-xs"><i class="fa-brands fa-youtube"></i></span>
                            <span class="text-sm">YouTube</span>
                        </label>
                        <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-yellow-50 hover:border-yellow-300 transition-all">
                            <input type="checkbox" name="editPlatforms" value="Snapchat" class="hidden">
                            <span class="w-6 h-6 bg-yellow-400 text-slate-800 rounded flex items-center justify-center text-xs"><i class="fa-brands fa-snapchat"></i></span>
                            <span class="text-sm">Snapchat</span>
                        </label>
                        <label class="platform-checkbox flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-all">
                            <input type="checkbox" name="editPlatforms" value="Website" class="hidden">
                            <span class="w-6 h-6 bg-indigo-600 text-white rounded flex items-center justify-center text-xs"><i class="fa-solid fa-globe"></i></span>
                            <span class="text-sm">Website</span>
                        </label>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editUrgent" class="w-5 h-5 text-red-500 rounded">
                        <span class="text-sm font-medium">🔥 Urgent</span>
                    </label>
                </div>
                <!-- Media Gallery -->
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Media</label>
                    <div id="editMediaGallery" class="grid grid-cols-4 gap-3 mb-3"></div>
                    <div class="upload-zone rounded-xl p-4 text-center cursor-pointer" onclick="document.getElementById('editFileInput').click()">
                        <input type="file" id="editFileInput" class="hidden" accept="image/*,video/*" onchange="uploadEditFile(event)" multiple>
                        <p class="text-slate-500 text-sm">+ Add more media</p>
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-medium py-3 rounded-lg">Save Changes</button>
                    <button type="button" onclick="closeEditModal()" class="px-6 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-lg font-medium">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== REQUEST CHANGES MODAL ==================== -->
    <div id="changesModal" dir="rtl" class="hidden fixed inset-0 bg-slate-900/50 modal-backdrop z-50 flex items-center justify-center p-4">
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
    <div id="scheduleModal" dir="rtl" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
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
    <div id="toasts" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <!-- Edit User Modal -->
    <div id="editUserModal" dir="rtl" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="bg-[#0a1628] text-white px-6 py-4 flex justify-between rounded-t-2xl">
                <h2 class="text-xl font-bold">✏️ Edit User</h2>
                <button onclick="closeEditUserModal()" class="text-2xl hover:text-sky-400">&times;</button>
            </div>
            <form id="editUserForm" class="p-6 space-y-4">
                <input type="hidden" id="editUserId">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Username</label>
                    <input type="text" id="editUserUsername" disabled class="w-full px-4 py-3 border rounded-xl bg-slate-100 text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="editUserFullName" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select id="editUserRole" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-brand-500">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">New Password <span class="text-slate-400 text-xs">(leave blank to keep current)</span></label>
                    <input type="password" id="editUserPassword" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="••••••••">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-medium py-3 rounded-lg">Save Changes</button>
                    <button type="button" onclick="closeEditUserModal()" class="px-6 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-lg font-medium">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" dir="rtl" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="bg-[#0a1628] text-white px-6 py-4 flex justify-between rounded-t-2xl">
                <h2 class="text-xl font-bold">➕ Add New User</h2>
                <button onclick="closeAddUserModal()" class="text-2xl hover:text-sky-400">&times;</button>
            </div>
            <form id="addUserForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Username <span class="text-red-500">*</span></label>
                    <input type="text" id="addUserUsername" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="username">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="addUserFullName" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Full Name">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select id="addUserRole" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-brand-500">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" id="addUserPassword" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="••••••••">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-medium py-3 rounded-lg">Create User</button>
                    <button type="button" onclick="closeAddUserModal()" class="px-6 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-lg font-medium">Cancel</button>
                </div>
            </form>
        </div>
    </div>

<script>
const app = { user: null, posts: [], currentPost: null, lastUnreadCount: 0, notificationsInitialized: false };
const STATUS_LIST = ['IDEA', 'DRAFT', 'PENDING_REVIEW', 'APPROVED', 'SCHEDULED', 'PUBLISHED'];
const STATUS_COLORS = {
    'IDEA': 'bg-violet-100 text-violet-700',
    'DRAFT': 'bg-sky-100 text-sky-700',
    'PENDING_REVIEW': 'bg-amber-100 text-amber-700',
    'CHANGES_REQUESTED': 'bg-orange-100 text-orange-700',
    'APPROVED': 'bg-emerald-100 text-emerald-700',
    'SCHEDULED': 'bg-indigo-100 text-indigo-700',
    'PUBLISHED': 'bg-slate-100 text-slate-600'
};
const STATUS_LABELS = {
    'IDEA': 'Idea',
    'DRAFT': 'Draft',
    'PENDING_REVIEW': 'In Review',
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
        
        // Check if non-admin trying to access users tab
        if (initialTab === 'users' && app.user?.role !== 'admin') initialTab = 'board';
        
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
    if (tab === 'users' && app.user?.role !== 'admin') tab = 'board';
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
        if (data.data.role === 'admin') document.getElementById('adminLink').classList.remove('hidden');
        
        // Update company branding (use white logo for dark sidebar)
        if (data.data.company_logo) {
            // Convert to white logo version for dark sidebar background
            let sidebarLogo = data.data.company_logo;
            // For BroMan, use white logo in sidebar
            if (sidebarLogo.includes('Final_Logo.png')) {
                sidebarLogo = sidebarLogo.replace('Final_Logo.png', 'Final_Logo White.png');
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

async function logout() { await api('logout', 'POST'); location.href = 'login.php'; }

function switchTab(tab) {
    const allTabs = ['dashboard', 'board', 'calendar', 'users'];
    const titles = { dashboard: 'Dashboard', board: 'Content Board', calendar: 'Calendar', users: 'User Management' };
    
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
    if (tab === 'users') loadUsers();
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
        if (!healthPath) return;
        const healthColors = { healthy: '#10b981', warning: '#f59e0b', critical: '#ef4444' };
        healthPath.setAttribute('stroke-dasharray', `${health.score}, 100`);
        healthPath.style.stroke = healthColors[health.status];
        document.getElementById('healthScore').textContent = health.score;
        document.getElementById('healthLabel').textContent = health.label;
    
    // === Smart Recommendations ===
    const recColors = { warning: 'bg-amber-50 border-amber-200', success: 'bg-emerald-50 border-emerald-200', alert: 'bg-red-50 border-red-200', info: 'bg-blue-50 border-blue-200' };
    const recTextColors = { warning: 'text-amber-800', success: 'text-emerald-800', alert: 'text-red-800', info: 'text-blue-800' };
    document.getElementById('recommendationsSection').innerHTML = (d.recommendations || []).slice(0, 3).map(r => `
        <div class="flex items-start gap-3 p-4 rounded-xl border ${recColors[r.type] || 'bg-slate-50 border-slate-200'}">
            <span class="text-2xl">${r.icon}</span>
            <div>
                <div class="font-semibold ${recTextColors[r.type] || 'text-slate-800'}">${r.title}</div>
                <div class="text-sm text-slate-600">${r.message}</div>
            </div>
        </div>
    `).join('');
    
    // === KPIs with Trends ===
    document.getElementById('kpiTotal').textContent = d.overview?.total_posts || 0;
    document.getElementById('kpiPublished').textContent = d.overview?.published_period || 0;
    document.getElementById('kpiScheduled').textContent = d.overview?.scheduled_upcoming || 0;
    document.getElementById('kpiPending').textContent = d.overview?.pending_review || 0;
    document.getElementById('kpiApprovalRate').textContent = (d.overview?.approval_rate || 0) + '%';
    
    // Trend badges
    const pubTrend = d.overview?.published_trend || 0;
    const pubTrendEl = document.getElementById('kpiPublishedTrend');
    if (pubTrend !== 0) {
        pubTrendEl.textContent = (pubTrend > 0 ? '↑' : '↓') + Math.abs(pubTrend) + '%';
        pubTrendEl.className = `text-xs font-medium px-1.5 py-0.5 rounded ${pubTrend > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`;
    } else {
        pubTrendEl.textContent = '';
    }
    
    const appTrend = d.overview?.approval_trend || 0;
    const appTrendEl = document.getElementById('kpiApprovalTrend');
    if (appTrend !== 0) {
        appTrendEl.textContent = (appTrend > 0 ? '↑' : '↓') + Math.abs(appTrend) + '%';
        appTrendEl.className = `text-xs font-medium px-1.5 py-0.5 rounded ${appTrend > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`;
    } else {
        appTrendEl.textContent = '';
    }
    
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
        const color = b.is_bottleneck ? 'bg-red-500' : 'bg-emerald-500';
        return `
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 w-20">${b.stage}</span>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="${color} h-full rounded-full transition-all" style="width: ${pct}%"></div>
                </div>
                <span class="text-xs font-medium ${b.is_bottleneck ? 'text-red-600' : 'text-slate-600'} w-16 text-right">${b.avg_days}d</span>
            </div>
        `;
    }).join('') || '<p class="text-slate-400 text-sm">No data</p>';
    
    // === Workflow Funnel ===
    const statuses = ['IDEA', 'DRAFT', 'PENDING_REVIEW', 'APPROVED', 'SCHEDULED', 'PUBLISHED'];
    const colors = ['#8b5cf6', '#0ea5e9', '#f59e0b', '#10b981', '#6366f1', '#64748b'];
    const maxCount = Math.max(...statuses.map(s => d.by_status?.[s] || 0), 1);
    const statusLabels = ['Ideas', 'Drafts', 'Review', 'Approved', 'Scheduled', 'Published'];
    const gradients = [
        'from-purple-400 to-purple-600',
        'from-sky-400 to-sky-600', 
        'from-amber-400 to-amber-600',
        'from-emerald-400 to-emerald-600',
        'from-indigo-400 to-indigo-600',
        'from-slate-400 to-slate-600'
    ];
    
    document.getElementById('workflowFunnel').innerHTML = statuses.map((s, i) => {
        const count = d.by_status?.[s] || 0;
        const height = Math.max((count / maxCount) * 100, 15);
        return `
            <div class="flex flex-col h-full">
                <div class="flex-1 flex flex-col justify-end">
                    <div class="bg-gradient-to-t ${gradients[i]} rounded-lg shadow-sm flex flex-col items-center justify-end p-3 transition-all hover:scale-105" style="height: ${height}%">
                        <span class="text-2xl font-bold text-white drop-shadow">${count}</span>
                    </div>
                </div>
                <div class="text-center mt-2">
                    <span class="text-xs font-medium text-slate-500">${statusLabels[i]}</span>
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
    if (ctx && platformData.length > 0) {
        if (platformChart) platformChart.destroy();
        platformChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: platformData.map(p => p.platform),
                datasets: [{
                    data: platformData.map(p => p.count),
                    backgroundColor: platformData.map(p => platformColors[p.platform] || '#94a3b8'),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '65%'
            }
        });
        
        document.getElementById('platformLegend').innerHTML = platformData.slice(0, 4).map(p => `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded" style="background: ${platformColors[p.platform] || '#94a3b8'}"></span>
                    <span class="text-slate-600">${p.platform}</span>
                </div>
                <span class="font-medium">${p.count}</span>
            </div>
        `).join('');
    }
    
    // === User Performance Cards - DETAILED ===
    const teamMembers = d.user_performance || [];
    document.getElementById('teamMemberCount').textContent = teamMembers.length > 0 ? `${teamMembers.length} members` : '';
    
    document.getElementById('userPerformanceCards').innerHTML = teamMembers.map(u => {
        const score = u.productivity_score || 0;
        const scoreColor = score >= 70 ? 'text-emerald-600' : (score >= 40 ? 'text-amber-600' : 'text-red-600');
        const scoreBg = score >= 70 ? 'stroke-emerald-500' : (score >= 40 ? 'stroke-amber-500' : 'stroke-red-500');
        const roleColor = u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-sky-100 text-sky-700';
        const lastActivity = u.last_activity ? formatDate(u.last_activity) : 'No activity';
        
        return `
        <div class="p-4 bg-white border border-slate-200 rounded-xl hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-brand-400 to-brand-600 text-white rounded-xl flex items-center justify-center font-bold text-lg shadow-sm">
                        ${(u.full_name || u.username)[0].toUpperCase()}
                    </div>
                    <div>
                        <div class="font-semibold text-slate-800">${u.full_name || u.username}</div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400">@${u.username}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium ${roleColor}">${u.role === 'admin' ? 'Admin' : 'Staff'}</span>
                        </div>
                    </div>
                </div>
                <div class="relative w-14 h-14">
                    <svg class="w-14 h-14 -rotate-90" viewBox="0 0 36 36">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" class="${scoreBg}" stroke-width="3" stroke-dasharray="${score}, 100" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-sm font-bold ${scoreColor}">${score}</span>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-2 text-center mb-3">
                <div class="bg-slate-50 rounded-lg p-2">
                    <div class="text-lg font-bold text-slate-700">${u.total_posts || 0}</div>
                    <div class="text-xs text-slate-400">Posts</div>
                </div>
                <div class="bg-emerald-50 rounded-lg p-2">
                    <div class="text-lg font-bold text-emerald-600">${u.published || 0}</div>
                    <div class="text-xs text-slate-400">Published</div>
                </div>
                <div class="bg-indigo-50 rounded-lg p-2">
                    <div class="text-lg font-bold text-indigo-600">${u.scheduled || 0}</div>
                    <div class="text-xs text-slate-400">Scheduled</div>
                </div>
            </div>
            
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-4">
                    <span class="text-slate-500">Pending: <span class="font-medium text-amber-600">${u.pending || 0}</span></span>
                    <span class="text-slate-500">Revisions: <span class="font-medium text-red-600">${u.revisions_received || 0}</span></span>
                    <span class="text-slate-500">Rate: <span class="font-medium text-emerald-600">${u.approval_rate || 0}%</span></span>
                </div>
                <span class="text-slate-400">${lastActivity}</span>
            </div>
        </div>
    `}).join('') || '<p class="text-slate-400 text-center py-8">No team data available</p>';
    
    // === Upcoming Scheduled ===
    document.getElementById('upcomingScheduled').innerHTML = (d.upcoming_scheduled || []).map(p => {
        const schedDate = new Date(p.scheduled_date);
        return `
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg cursor-pointer hover:bg-slate-100" onclick="openViewModal(${p.id})">
                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex flex-col items-center justify-center text-xs">
                    <span class="font-bold">${schedDate.getDate()}</span>
                    <span class="text-[10px]">${schedDate.toLocaleString('en', {month: 'short'})}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-slate-700 truncate">${escapeHtml(p.title)}</div>
                    <div class="text-xs text-slate-400">${p.platform} · ${schedDate.toLocaleTimeString('en', {hour: '2-digit', minute: '2-digit'})}</div>
                </div>
            </div>
        `;
    }).join('') || '<p class="text-slate-400 text-center py-6">No upcoming scheduled posts</p>';
    
    // === Recent Activity - DETAILED ===
    const actionConfig = {
        'created': { icon: '✚', bg: 'bg-emerald-100', text: 'text-emerald-600', label: 'Created new post' },
        'updated': { icon: '✎', bg: 'bg-blue-100', text: 'text-blue-600', label: 'Updated content' },
        'status_changed': { icon: '⟳', bg: 'bg-amber-100', text: 'text-amber-600', label: 'Changed status' },
        'comment_added': { icon: '💬', bg: 'bg-indigo-100', text: 'text-indigo-600', label: 'Added comment' },
        'media_uploaded': { icon: '📎', bg: 'bg-purple-100', text: 'text-purple-600', label: 'Uploaded media' },
        'deleted': { icon: '✕', bg: 'bg-red-100', text: 'text-red-600', label: 'Deleted post' },
    };
    
    const activityStatusLabels = {
        'IDEA': 'Idea', 'DRAFT': 'Draft', 'PENDING_REVIEW': 'Pending Review',
        'CHANGES_REQUESTED': 'Changes Requested', 'APPROVED': 'Approved',
        'SCHEDULED': 'Scheduled', 'PUBLISHED': 'Published'
    };
    
    const activityStatusColors = {
        'IDEA': 'bg-purple-500', 'DRAFT': 'bg-sky-500', 'PENDING_REVIEW': 'bg-amber-500',
        'CHANGES_REQUESTED': 'bg-orange-500', 'APPROVED': 'bg-emerald-500',
        'SCHEDULED': 'bg-indigo-500', 'PUBLISHED': 'bg-slate-600'
    };
    
    const platformIcons = {
        'Facebook': '📘', 'Instagram': '📸', 'LinkedIn': '💼', 'X': 'X', 
        'TikTok': '🎵', 'YouTube': '▶️', 'Snapchat': '👻', 'Website': '🌐'
    };
    
    document.getElementById('recentActivity').innerHTML = (d.recent_activity || []).map(a => {
        const config = actionConfig[a.action] || { icon: '•', bg: 'bg-slate-100', text: 'text-slate-500', label: a.action };
        
        // Status transition for status_changed
        let statusTransition = '';
        if (a.action === 'status_changed' && a.old_value && a.new_value) {
            statusTransition = `
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-2 py-1 rounded text-xs font-medium bg-slate-200 text-slate-600">${activityStatusLabels[a.old_value] || a.old_value}</span>
                    <span class="text-slate-400">→</span>
                    <span class="px-2 py-1 rounded text-xs font-medium text-white ${activityStatusColors[a.new_value] || 'bg-slate-500'}">${activityStatusLabels[a.new_value] || a.new_value}</span>
                </div>`;
        } else if (a.action === 'status_changed' && a.new_value) {
            statusTransition = `
                <div class="mt-2">
                    <span class="px-2 py-1 rounded text-xs font-medium text-white ${activityStatusColors[a.new_value] || 'bg-slate-500'}">${activityStatusLabels[a.new_value] || a.new_value}</span>
                </div>`;
        }
        
        // Description/reason if available
        let description = '';
        if (a.description) {
            description = `<div class="mt-2 text-sm text-slate-600 bg-slate-100 p-2 rounded-lg border-l-2 border-amber-400 italic">"${escapeHtml(a.description)}"</div>`;
        }
        
        // Platform badge
        const platformBadge = a.platform ? `<span class="text-xs text-slate-500">${platformIcons[a.platform] || '📱'} ${a.platform}</span>` : '';
        
        return `
        <div class="p-4 bg-white hover:bg-slate-50 rounded-xl transition-colors border border-slate-200 shadow-sm cursor-pointer" onclick="openViewModal(${a.post_id})">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 ${config.bg} rounded-xl flex items-center justify-center text-xl ${config.text} flex-shrink-0">${config.icon}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold text-slate-800">${a.full_name || a.username}</span>
                        <span class="text-xs text-slate-400">${formatDate(a.created_at)}</span>
                    </div>
                    <div class="text-sm text-slate-500 mt-0.5">${config.label}</div>
                    <div class="mt-2 p-2 bg-slate-50 rounded-lg">
                        <div class="font-medium text-slate-700">${escapeHtml(a.post_title)}</div>
                        <div class="flex items-center gap-3 mt-1">
                            ${platformBadge}
                        </div>
                    </div>
                    ${statusTransition}
                    ${description}
                </div>
            </div>
        </div>
    `}).join('') || '<p class="text-slate-400 text-center py-8">No recent activity</p>';
    
    // Update activity count
    const activityCount = (d.recent_activity || []).length;
    document.getElementById('activityCount').textContent = activityCount > 0 ? `Showing ${activityCount} activities` : '';
    } catch (err) {
        console.error('loadDashboard ERROR:', err);
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
    const grouped = { IDEA: [], DRAFT: [], PENDING_REVIEW: [], APPROVED: [], SCHEDULED: [], PUBLISHED: [], CHANGES_REQUESTED: [] };
    
    // Sort posts by updated_at (most recent first) before grouping
    const sortedPosts = [...app.posts].sort((a, b) => {
        const dateA = new Date(a.updated_at || a.created_at);
        const dateB = new Date(b.updated_at || b.created_at);
        return dateB - dateA;
    });
    
    sortedPosts.forEach(p => {
        if (p.status === 'CHANGES_REQUESTED') {
            grouped.CHANGES_REQUESTED.push(p);
            grouped.DRAFT.push(p); // Also count in DRAFT for tab count
        } else if (grouped[p.status]) {
            grouped[p.status].push(p);
        }
    });
    
    // Sort each group by updated_at (most recent first)
    Object.keys(grouped).forEach(key => {
        grouped[key].sort((a, b) => {
            const dateA = new Date(a.updated_at || a.created_at);
            const dateB = new Date(b.updated_at || b.created_at);
            return dateB - dateA;
        });
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
    
    // Render posts
    const grid = document.getElementById('postsGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (currentStatusFilter === '') {
        // Show all posts grouped by status with headers
        let hasPosts = false;
        let html = '';
        
        // Define status order and labels for "All" view
        const statusOrder = [
            { key: 'PENDING_REVIEW', label: 'Pending Review', icon: '🔍', color: 'amber' },
            { key: 'CHANGES_REQUESTED', label: 'Changes Requested', icon: '🔄', color: 'orange' },
            { key: 'APPROVED', label: 'Approved', icon: '✅', color: 'emerald' },
            { key: 'SCHEDULED', label: 'Scheduled', icon: '📅', color: 'indigo' },
            { key: 'DRAFT', label: 'Drafts', icon: '📝', color: 'sky' },
            { key: 'PUBLISHED', label: 'Published', icon: '🚀', color: 'slate' },
            { key: 'IDEA', label: 'Ideas', icon: '💡', color: 'violet' }
        ];
        
        statusOrder.forEach(({ key, label, icon, color }) => {
            const posts = grouped[key] || [];
            if (posts.length > 0) {
                hasPosts = true;
                html += `
                    <div class="w-full mb-8">
                        <div class="flex items-center gap-3 mb-4 pb-2 border-b-2 border-${color}-200">
                            <span class="text-2xl">${icon}</span>
                            <h3 class="text-lg font-semibold text-slate-800">${label}</h3>
                            <span class="text-sm text-slate-500 bg-slate-100 px-2 py-1 rounded-full">${posts.length}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            ${posts.map(p => cardHTML(p)).join('')}
                        </div>
                    </div>
                `;
            }
        });
        
        if (hasPosts) {
            // Change grid to a regular div container for grouped view
            grid.className = 'space-y-6';
            grid.innerHTML = html;
            emptyState.classList.add('hidden');
        } else {
            // Restore grid layout for filtered view
            grid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4';
            grid.innerHTML = '';
            emptyState.classList.remove('hidden');
        }
    } else {
        // Show filtered posts for specific status
        // Restore grid layout for filtered view
        grid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4';
        const filteredPosts = grouped[currentStatusFilter] || [];
        
        if (filteredPosts.length === 0) {
            grid.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            grid.innerHTML = filteredPosts.map(p => cardHTML(p)).join('');
        }
    }
    
    // Update active tab styling
    updateActiveTab();
}

function setStatusFilter(status) {
    currentStatusFilter = status;
    renderBoard();
}

function updateActiveTab() {
    const tabs = ['All', 'IDEA', 'DRAFT', 'PENDING_REVIEW', 'APPROVED', 'SCHEDULED', 'PUBLISHED'];
    const statusColors = {
        '': 'border-slate-600',
        'IDEA': 'border-violet-500',
        'DRAFT': 'border-sky-500',
        'PENDING_REVIEW': 'border-amber-500',
        'APPROVED': 'border-emerald-500',
        'SCHEDULED': 'border-indigo-500',
        'PUBLISHED': 'border-slate-500'
    };
    
    tabs.forEach(tab => {
        const tabId = tab === 'All' ? 'tabAll' : 'tab' + tab;
        const tabEl = document.getElementById(tabId);
        if (!tabEl) return;
        
        const isActive = (tab === 'All' && currentStatusFilter === '') || currentStatusFilter === tab;
        const borderColor = statusColors[tab === 'All' ? '' : tab] || 'border-slate-600';
        
        if (isActive) {
            tabEl.className = `status-tab px-4 py-3 text-sm font-semibold border-b-2 ${borderColor} bg-slate-50 transition-colors flex items-center gap-2 text-slate-800`;
        } else {
            tabEl.className = 'status-tab px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:bg-slate-50 transition-colors flex items-center gap-2 text-slate-600';
        }
    });
}

function cardHTML(post) {
    const hasChanges = post.status === 'CHANGES_REQUESTED';
    
    // Parse platforms - handle JSON string, array, or legacy single platform
    let platforms = [];
    if (post.platforms) {
        platforms = typeof post.platforms === 'string' ? JSON.parse(post.platforms) : post.platforms;
    } else if (post.platform) {
        platforms = [post.platform];
    }
    
    // Status badge colors
    const statusBadgeColors = {
        'IDEA': 'bg-violet-100 text-violet-700 border-violet-200',
        'DRAFT': 'bg-sky-100 text-sky-700 border-sky-200',
        'PENDING_REVIEW': 'bg-amber-100 text-amber-700 border-amber-200',
        'CHANGES_REQUESTED': 'bg-orange-100 text-orange-700 border-orange-200',
        'APPROVED': 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'SCHEDULED': 'bg-indigo-100 text-indigo-700 border-indigo-200',
        'PUBLISHED': 'bg-slate-100 text-slate-600 border-slate-200'
    };
    
    const statusBadgeLabels = {
        'IDEA': '💡 Idea',
        'DRAFT': '📝 Draft',
        'PENDING_REVIEW': '🔍 Pending',
        'CHANGES_REQUESTED': '🔄 Changes',
        'APPROVED': '✅ Approved',
        'SCHEDULED': '📅 Scheduled',
        'PUBLISHED': '🚀 Published'
    };
    
    const statusBadgeColor = statusBadgeColors[post.status] || 'bg-slate-100 text-slate-600';
    const statusBadgeLabel = statusBadgeLabels[post.status] || post.status;
    
    // Handle media (image vs video)
    let mediaHtml = '';
    if (post.primary_image) {
        if (isVideoFile(post.primary_image)) {
            mediaHtml = `<video src="${post.primary_image}" class="w-full h-32 object-cover rounded-lg mb-3" muted></video>`;
        } else {
            mediaHtml = `<img src="${post.primary_image}" class="w-full h-32 object-cover rounded-lg mb-3">`;
        }
    }
    
    // Generate platform badges HTML
    const platformBadgesHtml = platforms.map(p => {
        const color = PLATFORM_COLORS[p] || 'bg-slate-500';
        const icon = PLATFORM_ICONS[p] || 'fa-solid fa-share-nodes';
        return `<span class="text-[10px] px-1.5 py-0.5 rounded text-white ${color}"><i class="${icon}"></i></span>`;
    }).join('');
    
    // Format date
    const formatDate = (dateStr) => {
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
    };
    
    const updatedDate = formatDate(post.updated_at || post.created_at);
    const scheduledDate = post.scheduled_date ? formatDate(post.scheduled_date) : null;
    
    return `
        <div class="post-card bg-white rounded-xl shadow-sm p-4 border border-slate-200 hover:shadow-md transition-shadow" onclick="openViewModal(${post.id})">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium px-2.5 py-1 rounded-md border ${statusBadgeColor}">${statusBadgeLabel}</span>
                ${post.urgency == 1 ? '<span class="text-red-600 text-xs font-bold bg-red-50 px-2 py-0.5 rounded border border-red-200">🔥 URGENT</span>' : ''}
            </div>
            ${hasChanges ? '<div class="text-orange-600 text-xs font-medium mb-2 bg-orange-50 inline-block px-2 py-0.5 rounded border border-orange-200">⚠️ Revision Requested</div>' : ''}
            ${mediaHtml}
            <h4 class="font-semibold text-slate-800 mb-2 line-clamp-2 text-sm leading-tight">${escapeHtml(post.title)}</h4>
            <p class="text-slate-500 text-xs mb-3 line-clamp-2 leading-relaxed">${escapeHtml(post.content)}</p>
            
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div class="flex flex-wrap items-center gap-1.5">${platformBadgesHtml}</div>
                    ${post.comment_count > 0 ? `<div class="text-xs text-slate-500 flex items-center gap-1"><i class="fa-regular fa-comment"></i> ${post.comment_count}</div>` : ''}
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5 text-slate-500">
                        <i class="fa-regular fa-user text-slate-400"></i>
                        <span class="font-medium">${escapeHtml(post.author_full_name || post.author_name || 'Unknown')}</span>
                    </div>
                    <div class="text-slate-400 flex items-center gap-1">
                        <i class="fa-regular fa-clock text-[10px]"></i>
                        <span>${updatedDate}</span>
                    </div>
                </div>
                ${scheduledDate ? `<div class="text-xs text-indigo-600 flex items-center gap-1.5 mt-1"><i class="fa-regular fa-calendar"></i> Scheduled: ${scheduledDate}</div>` : ''}
            </div>
        </div>
    `;
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
    const data = await api(`get_post&id=${id}`);
    if (!data.success) { toast('Failed to load post', 'error'); return; }
    
    app.currentPost = data.data;
    const p = data.data;
    
    // Status badge
    const statusBadge = document.getElementById('viewStatusBadge');
    statusBadge.textContent = STATUS_LABELS[p.status] || p.status;
    statusBadge.className = `status-badge ${STATUS_COLORS[p.status] || 'bg-slate-500'} text-white`;
    
    // Platform badges with icons (multi-platform support)
    let platforms = [];
    if (p.platforms) {
        platforms = typeof p.platforms === 'string' ? JSON.parse(p.platforms) : p.platforms;
    } else if (p.platform) {
        platforms = [p.platform];
    }
    const platformBadgesHtml = platforms.map(plat => {
        const icon = PLATFORM_ICONS[plat] || 'fa-solid fa-share-nodes';
        const color = PLATFORM_COLORS[plat] || 'bg-slate-500';
        return `<span class="px-3 py-1.5 rounded text-white text-[11px] font-bold ${color} flex items-center gap-3 whitespace-nowrap shadow-sm"><i class="${icon} text-xs"></i>${plat}</span>`;
    }).join('');
    document.getElementById('viewPlatformBadge').innerHTML = platformBadgesHtml;
    
    // Title and meta
    document.getElementById('viewTitle').textContent = p.title;
    document.getElementById('viewAuthor').innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> ${p.author_full_name || p.author_name}`;
    document.getElementById('viewDate').textContent = formatDate(p.created_at);
    document.getElementById('viewUrgentBadge').classList.toggle('hidden', p.urgency != 1);
    
    // Content
    document.getElementById('viewContent').textContent = p.content;
    
    // Media Gallery (show all media, not just primary)
    const mediaContainer = document.getElementById('viewMediaContainer');
    const mediaWrapper = document.getElementById('viewMediaWrapper');
    if (p.media && p.media.length > 0) {
        if (p.media.length === 1) {
            // Single media - show large
            const m = p.media[0];
            if (isVideoFile(m.file_path)) {
                mediaWrapper.innerHTML = `<video src="${m.file_path}" controls class="w-full max-h-96 rounded-xl bg-slate-100"></video>`;
            } else {
                mediaWrapper.innerHTML = `<img src="${m.file_path}" class="w-full max-h-96 object-contain rounded-xl bg-slate-100">`;
            }
        } else {
            // Multiple media - show as gallery grid
            mediaWrapper.innerHTML = `
                <div class="grid grid-cols-${p.media.length <= 4 ? p.media.length : 4} gap-2">
                    ${p.media.map(m => {
                        if (isVideoFile(m.file_path)) {
                            return `<div class="relative"><video src="${m.file_path}" class="w-full h-32 object-cover rounded-lg cursor-pointer" onclick="window.open('${m.file_path}', '_blank')" muted></video><span class="absolute bottom-1 left-1 bg-black/70 text-white text-[8px] px-1 rounded">VIDEO</span></div>`;
                        } else {
                            return `<img src="${m.file_path}" class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-90" onclick="window.open('${m.file_path}', '_blank')">`;
                        }
                    }).join('')}
                </div>
                <p class="text-xs text-slate-400 mt-2 text-center">${p.media.length} files • Click to view full size</p>
            `;
        }
        mediaContainer.classList.remove('hidden');
    } else {
        mediaContainer.classList.add('hidden');
    }
    
    // Changes notice
    const changesNotice = document.getElementById('viewChangesNotice');
    if (p.change_request_reason) {
        document.getElementById('viewChangesReason').textContent = `${p.change_request_reason} — by ${p.change_requested_by_name || 'Admin'}`;
        changesNotice.classList.remove('hidden');
    } else {
        changesNotice.classList.add('hidden');
    }
    
    // Action buttons
    renderActionButtons(p);
    
    // Comments
    renderViewComments(p.comments || []);
    
    // Activity
    renderViewActivity(p.activity || []);
    
    // Edit button visibility
    const isAdmin = app.user.role === 'admin';
    const isOwner = p.author_id == app.user.id;
    const canEditStatus = ['DRAFT', 'CHANGES_REQUESTED', 'IDEA'].includes(p.status);
    const canEdit = isAdmin || (isOwner && canEditStatus);
    document.getElementById('viewEditBtn').classList.toggle('hidden', !canEdit);
    
    // Delete button visibility - Admin can delete any, Staff can delete own drafts/ideas
    const canDelete = isAdmin || (isOwner && ['DRAFT', 'IDEA'].includes(p.status));
    document.getElementById('viewDeleteBtn').classList.toggle('hidden', !canDelete);
    
    document.getElementById('viewModal').classList.remove('hidden');
}

function renderActionButtons(p) {
    const container = document.getElementById('actionButtons');
    const isAdmin = app.user.role === 'admin';
    const isOwner = p.author_id == app.user.id;
    let buttons = [];
    
    // Premium button base styles
    const btnPrimary = 'flex-1 bg-slate-800 hover:bg-slate-900 text-white font-medium py-2.5 px-5 rounded-md flex items-center justify-center gap-2 text-sm transition-colors';
    const btnSuccess = 'flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-5 rounded-md flex items-center justify-center gap-2 text-sm transition-colors';
    const btnWarning = 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-2.5 px-5 rounded-md flex items-center justify-center gap-2 text-sm border border-slate-200 transition-colors';
    const btnSecondary = 'bg-white hover:bg-slate-50 text-slate-600 font-medium py-2.5 px-4 rounded-md text-sm border border-slate-200 transition-colors';
    
    if (p.status === 'IDEA') {
        if (isAdmin) {
            buttons.push(`<button onclick="approveIdea()" class="${btnPrimary}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Convert to Draft</button>`);
        }
    } else if (p.status === 'DRAFT' || p.status === 'CHANGES_REQUESTED') {
        if (isOwner || isAdmin) {
            buttons.push(`<button onclick="submitForReview()" class="${btnPrimary}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Submit for Review</button>`);
        }
    } else if (p.status === 'PENDING_REVIEW') {
        if (isAdmin) {
            buttons.push(`<button onclick="approvePost()" class="${btnSuccess}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Approve</button>`);
            buttons.push(`<button onclick="openChangesModal()" class="${btnWarning}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>Request Changes</button>`);
        }
    } else if (p.status === 'APPROVED') {
        if (isAdmin) {
            buttons.push(`<button onclick="openScheduleModal()" class="${btnPrimary}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Schedule for Publishing</button>`);
        }
    } else if (p.status === 'SCHEDULED') {
        const schedDate = p.scheduled_date ? new Date(p.scheduled_date) : null;
        const schedStr = schedDate ? schedDate.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Unknown';
        buttons.push(`<div class="flex-1 text-center py-2.5 px-5 bg-slate-50 text-slate-600 font-medium rounded-md text-sm border border-slate-200"><svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Scheduled: ${schedStr}</div>`);
        if (isAdmin) {
            buttons.push(`<button onclick="publishNow()" class="${btnSuccess}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>Publish Now</button>`);
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
    if (revisionCount > 0) {
        statsEl.textContent = `(${revisionCount} revision${revisionCount > 1 ? 's' : ''} requested)`;
    } else {
        statsEl.textContent = `(${activities.length} event${activities.length !== 1 ? 's' : ''})`;
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
        'approved': { icon: '✓', color: 'text-emerald-500', label: 'approved' },
        'scheduled': { icon: '📅', color: 'text-indigo-500', label: 'scheduled' },
        'published': { icon: '🚀', color: 'text-slate-600', label: 'published' }
    };
    
    const statusLabels = {
        'IDEA': 'Idea', 'DRAFT': 'Draft', 'PENDING_REVIEW': 'Pending Review',
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
        const notifHtml = data.data.notifications.slice(0, 10).map(n => `
            <div class="p-3 border-b border-slate-100 cursor-pointer transition-colors ${n.is_read ? 'bg-white hover:bg-slate-50' : 'bg-blue-50 hover:bg-blue-100 border-l-4 border-l-brand-500'}" onclick="notifClick(${n.id}, ${n.post_id})">
                <div class="flex items-start gap-2">
                    ${!n.is_read ? '<span class="w-2 h-2 bg-brand-500 rounded-full mt-1.5 flex-shrink-0"></span>' : ''}
                    <div class="flex-1">
                        <div class="${n.is_read ? 'font-medium text-slate-600' : 'font-semibold text-slate-800'} text-sm">${n.title}</div>
                        <div class="text-xs ${n.is_read ? 'text-slate-400' : 'text-slate-600'} line-clamp-2">${n.message}</div>
                        <div class="text-xs text-slate-400 mt-1">${formatDate(n.created_at)}</div>
                    </div>
                </div>
            </div>
        `).join('') || '<p class="p-4 text-slate-400 text-center">No notifications</p>';
        
        notifList.innerHTML = notifHtml;
    }
    } catch (e) {
        console.error('Notification load failed', e);
    }
}

function toggleNotifications() { document.getElementById('notifDropdown').classList.toggle('hidden'); }
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
    
    // Platform icons mapping
    const platformIcons = {
        'Facebook': '<i class="fa-brands fa-facebook"></i>',
        'Instagram': '<i class="fa-brands fa-instagram"></i>',
        'LinkedIn': '<i class="fa-brands fa-linkedin"></i>',
        'X': '<i class="fa-brands fa-x-twitter"></i>',
        'TikTok': '<i class="fa-brands fa-tiktok"></i>',
        'YouTube': '<i class="fa-brands fa-youtube"></i>',
        'Snapchat': '<i class="fa-brands fa-snapchat"></i>',
        'Website': '<i class="fa-solid fa-globe"></i>'
    };
    
    const platformColors = {
        'Facebook': 'bg-blue-600',
        'Instagram': 'bg-gradient-to-r from-purple-500 to-pink-500',
        'LinkedIn': 'bg-blue-700',
        'X': 'bg-black',
        'TikTok': 'bg-slate-800',
        'YouTube': 'bg-red-600',
        'Snapchat': 'bg-yellow-400 text-slate-800',
        'Website': 'bg-indigo-600'
    };
    
    let html = '';
    // Empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
        html += '<div class="min-h-[130px] border-b border-r border-slate-200 bg-slate-50/50"></div>';
    }
    
    // Days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${calendarYear}-${String(calendarMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = today.getFullYear() === calendarYear && today.getMonth() === calendarMonth && today.getDate() === day;
        const isPast = new Date(dateStr) < new Date(today.toDateString());
        const dayPosts = calendarPosts.filter(p => (p.scheduled_date || p.published_date || '').startsWith(dateStr));
        
        const dayBg = isToday ? 'bg-blue-50 ring-2 ring-blue-400 ring-inset' : (isPast ? 'bg-slate-50/30' : 'bg-white');
        
        html += `
            <div class="min-h-[130px] border-b border-r border-slate-200 p-2 ${dayBg} hover:bg-slate-50 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold ${isToday ? 'bg-blue-600 text-white px-2 py-0.5 rounded-full' : (isPast ? 'text-slate-400' : 'text-slate-700')}">${day}</span>
                    ${dayPosts.length > 0 ? `<span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-200 text-slate-600 font-medium">${dayPosts.length}</span>` : ''}
                </div>
                <div class="space-y-1.5 overflow-y-auto max-h-[90px] scrollbar-thin">
                    ${dayPosts.map(p => {
                        const time = p.scheduled_date ? new Date(p.scheduled_date).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'}) : '';
                        const statusStyle = p.status === 'SCHEDULED' 
                            ? 'border-l-indigo-500 bg-indigo-50' 
                            : 'border-l-emerald-500 bg-emerald-50';
                        
                        // Parse platforms
                        let platforms = [];
                        if (p.platforms) {
                            platforms = typeof p.platforms === 'string' ? JSON.parse(p.platforms) : p.platforms;
                        } else if (p.platform) {
                            platforms = [p.platform];
                        }
                        
                        // Generate platform icons for first 2 platforms
                        const platformIconsHtml = platforms.slice(0, 2).map(plat => {
                            const pIcon = platformIcons[plat] || '<i class="fa-solid fa-share-nodes"></i>';
                            const pColor = platformColors[plat] || 'bg-slate-600';
                            return `<span class="flex-shrink-0 w-4 h-4 ${pColor} text-white rounded flex items-center justify-center text-[8px]">${pIcon}</span>`;
                        }).join('');
                        const moreCount = platforms.length > 2 ? `<span class="text-[8px] text-slate-400">+${platforms.length - 2}</span>` : '';
                        
                        return `
                            <div onclick="openViewModal(${p.id})" class="calendar-post group cursor-pointer p-1.5 rounded-md border-l-3 ${statusStyle} hover:shadow-sm transition-all">
                                <div class="flex items-start gap-1.5">
                                    <div class="flex items-center gap-0.5">${platformIconsHtml}${moreCount}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-medium text-slate-700 truncate group-hover:text-slate-900">${escapeHtml(p.title)}</div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            ${time ? `<span class="text-[10px] text-slate-500">${time}</span>` : ''}
                                            <span class="text-[10px] ${p.status === 'SCHEDULED' ? 'text-indigo-600' : 'text-emerald-600'} font-medium">${p.status === 'SCHEDULED' ? '📅' : '✓'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }
    
    grid.innerHTML = html;
}

// ==================== USERS MANAGEMENT ====================
let allUsers = [];

async function loadUsers() {
    if (app.user?.role !== 'admin') return;
    
    const data = await api('fetch_users');
    if (data.success) {
        allUsers = data.data;
        document.getElementById('totalUsersCount').textContent = allUsers.length;
        document.getElementById('activeUsersCount').textContent = allUsers.filter(u => u.is_active).length;
        document.getElementById('adminUsersCount').textContent = allUsers.filter(u => u.role === 'admin').length;
        renderUsersTable();
    }
}

function renderUsersTable() {
    document.getElementById('usersTableBody').innerHTML = allUsers.map(u => `
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
                <span class="px-2 py-1 rounded-full text-xs font-medium ${u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700'}">
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
                    <button onclick="openEditUserModal(${u.id})" class="px-3 py-1 text-xs rounded-lg bg-brand-100 text-brand-600 hover:bg-brand-200">
                        Edit
                    </button>
                    <button onclick="toggleUserStatus(${u.id}, ${u.is_active ? 0 : 1})" class="px-3 py-1 text-xs rounded-lg ${u.is_active ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-green-100 text-green-600 hover:bg-green-200'}">
                        ${u.is_active ? 'Deactivate' : 'Activate'}
                    </button>
                </div>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="5" class="text-center py-8 text-slate-400">No users found</td></tr>';
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
    const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' };
    const t = document.createElement('div');
    t.className = `${colors[type]} text-white px-4 py-2 rounded-lg shadow-lg text-sm animate-pulse`;
    t.textContent = msg;
    document.getElementById('toasts').appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

document.addEventListener('click', e => { 
    if (!e.target.closest('.relative')) document.getElementById('notifDropdown').classList.add('hidden'); 
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
</script>
            </main>
        </div>
    </div>
</body>
</html>
