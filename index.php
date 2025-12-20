<?php
require_once 'config.php';
require_once 'db.php';
session_name(SESSION_NAME);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BroMan Social</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
    </style>
</head>
<body class="bg-slate-50 text-slate-700">
    
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-full mx-auto px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="index.php" class="flex items-center">
                    <img src="images/Final_Logo.png" alt="BroMan Social" class="h-9">
                </a>
                <nav class="hidden md:flex items-center gap-1 bg-slate-100 rounded-lg p-1">
                    <button onclick="switchTab('dashboard')" id="tabDashboard" class="tab-btn px-4 py-2 rounded-md text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-white">Dashboard</button>
                    <button onclick="switchTab('board')" id="tabBoard" class="tab-btn px-4 py-2 rounded-md text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-white">Board</button>
                    <a href="calendar.php" class="px-4 py-2 rounded-md text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-white flex items-center gap-2"><i class="fa-regular fa-calendar text-slate-400"></i> Calendar</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <button onclick="toggleNotifications()" class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg relative">
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
                <button onclick="openCreateModal()" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    New Post
                </button>
                <div id="adminLink" class="hidden">
                    <a href="users.php" class="text-slate-500 hover:text-slate-700 text-sm flex items-center gap-1.5 hover:bg-slate-100 px-3 py-2 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Users
                    </a>
                </div>
                <div class="flex items-center gap-3 ml-2 pl-4 border-l border-slate-200">
                    <div class="text-right hidden sm:block">
                        <div id="userName" class="text-slate-800 text-sm font-medium"></div>
                        <div id="userRole" class="text-slate-400 text-xs capitalize"></div>
                    </div>
                    <button onclick="logout()" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg" title="Logout"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-full mx-auto px-4 py-6">
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
            <div class="mb-4 flex flex-wrap gap-3 items-center">
                <select id="platformFilter" onchange="loadPosts()" class="px-3 py-2 border rounded-lg text-sm bg-white">
                    <option value="">All Platforms</option>
                    <option value="Facebook">Facebook</option><option value="Instagram">Instagram</option><option value="LinkedIn">LinkedIn</option>
                    <option value="X">X</option><option value="TikTok">TikTok</option><option value="YouTube">YouTube</option><option value="Snapchat">Snapchat</option><option value="Website">Website</option>
                </select>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" id="myPostsFilter" onchange="loadPosts()" class="rounded"> My Posts</label>
                <input type="text" id="searchInput" placeholder="Search..." onkeyup="debounceSearch()" class="px-3 py-2 border rounded-lg text-sm flex-1 max-w-xs">
            </div>
            
            <div class="flex gap-4 overflow-x-auto pb-4">
                <!-- IDEAS -->
                <div class="flex-shrink-0 w-80">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-violet-400"></span>
                            Ideas <span id="countIDEA" class="text-xs font-medium bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">0</span>
                        </h3>
                    </div>
                    <div id="postsIDEA" class="space-y-3 min-h-[200px]"></div>
                </div>
                
                <!-- DRAFTS -->
                <div class="flex-shrink-0 w-80">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                            Drafts <span id="countDRAFT" class="text-xs font-medium bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">0</span>
                        </h3>
                    </div>
                    <div id="postsDRAFT" class="space-y-3 min-h-[200px]"></div>
                </div>
                
                <!-- PENDING REVIEW -->
                <div class="flex-shrink-0 w-80">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            Pending Review <span id="countPENDING_REVIEW" class="text-xs font-medium bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">0</span>
                        </h3>
                    </div>
                    <div id="postsPENDING_REVIEW" class="space-y-3 min-h-[200px]"></div>
                </div>
                
                <!-- APPROVED -->
                <div class="flex-shrink-0 w-80">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Approved <span id="countAPPROVED" class="text-xs font-medium bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">0</span>
                        </h3>
                    </div>
                    <div id="postsAPPROVED" class="space-y-3 min-h-[200px]"></div>
                </div>
                
                <!-- SCHEDULED -->
                <div class="flex-shrink-0 w-80">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                            Scheduled <span id="countSCHEDULED" class="text-xs font-medium bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">0</span>
                        </h3>
                    </div>
                    <div id="postsSCHEDULED" class="space-y-3 min-h-[200px]"></div>
                </div>
                
                <!-- PUBLISHED -->
                <div class="flex-shrink-0 w-80">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            Published <span id="countPUBLISHED" class="text-xs font-medium bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">0</span>
                        </h3>
                    </div>
                    <div id="postsPUBLISHED" class="space-y-3 min-h-[200px]"></div>
                </div>
            </div>
        </div>
    </main>

    <!-- ==================== CREATE POST MODAL ==================== -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-start justify-center p-4 overflow-y-auto">
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
                    <label class="block text-sm font-semibold mb-1.5">Content <span class="text-red-500">*</span></label>
                    <textarea id="createContent" rows="4" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-gold-500" placeholder="Write your post content..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Platform <span class="text-red-500">*</span></label>
                        <select id="createPlatform" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-gold-500">
                            <option value="">Select platform</option>
                            <option>Facebook</option><option>Instagram</option><option>LinkedIn</option>
                            <option>X</option><option>TikTok</option><option>YouTube</option><option>Snapchat</option><option>Website</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Start As</label>
                        <select id="createStatus" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-gold-500">
                            <option value="DRAFT">📝 Draft (Ready to work)</option>
                            <option value="IDEA">💡 Idea (Just a suggestion)</option>
                        </select>
                    </div>
                </div>
                <!-- Media Upload -->
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Attach Media (Optional)</label>
                    <div class="upload-zone rounded-xl p-6 text-center cursor-pointer" onclick="document.getElementById('createFileInput').click()">
                        <input type="file" id="createFileInput" class="hidden" accept="image/*,video/*" onchange="previewCreateFile(event)">
                        <div id="createPreviewArea" class="hidden mb-3"></div>
                        <div id="createUploadPrompt">
                            <svg class="w-10 h-10 mx-auto text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-slate-500 text-sm">Click or drag to upload</p>
                            <p class="text-slate-400 text-xs mt-1">Max 10MB • JPG, PNG, GIF, MP4</p>
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
    <div id="viewModal" class="hidden fixed inset-0 bg-slate-900/50 modal-backdrop z-50 flex items-start justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl my-8 border border-slate-200">
            <!-- Header -->
            <div class="px-6 py-4 flex justify-between items-center border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <span id="viewStatusBadge" class="status-badge bg-sky-100 text-sky-700">DRAFT</span>
                    <span id="viewPlatformBadge" class="text-sm text-slate-500"></span>
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
    <div id="editModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-start justify-center p-4 overflow-y-auto">
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
                    <label class="block text-sm font-semibold mb-1.5">Content <span class="text-red-500">*</span></label>
                    <textarea id="editContent" rows="5" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-gold-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Platform</label>
                    <select id="editPlatform" class="w-full px-4 py-3 border rounded-xl">
                        <option>Facebook</option><option>Instagram</option><option>LinkedIn</option>
                        <option>X</option><option>TikTok</option><option>YouTube</option><option>Snapchat</option><option>Website</option>
                    </select>
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
    <div id="changesModal" class="hidden fixed inset-0 bg-slate-900/50 modal-backdrop z-50 flex items-center justify-center p-4">
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
    <div id="scheduleModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
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

<script>
const app = { user: null, posts: [], currentPost: null };
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
    await loadUser();
    await loadPosts();
    loadNotifications();
    switchTab('board');
}

async function api(action, method = 'GET', body = null) {
    const opts = { method, headers: {} };
    if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    const res = await fetch(`api.php?action=${action}`, opts);
    return res.json();
}

async function loadUser() {
    const data = await api('get_user');
    if (data.success) {
        app.user = data.data;
        document.getElementById('userName').textContent = data.data.full_name || data.data.username;
        document.getElementById('userRole').textContent = data.data.role;
        if (data.data.role === 'admin') document.getElementById('adminLink').classList.remove('hidden');
    } else {
        location.href = 'login.php';
    }
}

async function logout() { await api('logout', 'POST'); location.href = 'login.php'; }

function switchTab(tab) {
    ['dashboard', 'board'].forEach(t => {
        document.getElementById(t + 'View').classList.toggle('hidden', t !== tab);
        document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1)).className = 
            `tab-btn px-4 py-2 rounded-lg text-sm font-medium ${t === tab ? 'bg-gold-500 text-navy-900' : 'text-slate-300 hover:text-white'}`;
    });
    if (tab === 'dashboard') loadDashboard();
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

async function loadPosts() {
    let url = 'fetch_posts';
    const params = [];
    const platform = document.getElementById('platformFilter')?.value;
    const myPosts = document.getElementById('myPostsFilter')?.checked;
    const search = document.getElementById('searchInput')?.value;
    if (platform) params.push(`platform=${platform}`);
    if (myPosts) params.push('my_posts=true');
    if (search) params.push(`search=${encodeURIComponent(search)}`);
    if (params.length) url += '&' + params.join('&');
    
    const data = await api(url);
    if (data.success) { app.posts = data.data; renderBoard(); }
}

function renderBoard() {
    const grouped = { IDEA: [], DRAFT: [], PENDING_REVIEW: [], APPROVED: [], SCHEDULED: [], PUBLISHED: [] };
    
    app.posts.forEach(p => {
        if (p.status === 'CHANGES_REQUESTED') grouped.DRAFT.push(p);
        else if (grouped[p.status]) grouped[p.status].push(p);
    });
    
    STATUS_LIST.forEach(status => {
        const container = document.getElementById('posts' + status);
        const countEl = document.getElementById('count' + status);
        if (!container) return;
        
        const posts = grouped[status] || [];
        countEl.textContent = posts.length;
        container.innerHTML = posts.map(p => cardHTML(p)).join('') || '<p class="text-center text-slate-400 text-sm py-8">No posts</p>';
    });
}

function cardHTML(post) {
    const hasChanges = post.status === 'CHANGES_REQUESTED';
    const platformColor = PLATFORM_COLORS[post.platform] || 'bg-slate-500';
    const platformIcon = PLATFORM_ICONS[post.platform] || 'fa-solid fa-share-nodes';
    
    // Handle media (image vs video)
    let mediaHtml = '';
    if (post.primary_image) {
        if (isVideoFile(post.primary_image)) {
            mediaHtml = `<video src="${post.primary_image}" class="w-full h-32 object-cover rounded-lg mb-3" muted></video>`;
        } else {
            mediaHtml = `<img src="${post.primary_image}" class="w-full h-32 object-cover rounded-lg mb-3">`;
        }
    }
    
    return `
        <div class="post-card bg-white rounded-xl shadow-sm p-4 border border-slate-200" onclick="openViewModal(${post.id})">
            ${post.urgency == 1 ? '<div class="text-red-600 text-xs font-medium mb-2 bg-red-50 inline-block px-2 py-0.5 rounded">URGENT</div>' : ''}
            ${hasChanges ? '<div class="text-slate-600 text-xs font-medium mb-2 bg-slate-100 inline-block px-2 py-0.5 rounded">Revision Requested</div>' : ''}
            ${mediaHtml}
            <h4 class="font-semibold text-slate-800 mb-1 line-clamp-2">${escapeHtml(post.title)}</h4>
            <p class="text-slate-500 text-sm mb-3 line-clamp-2">${escapeHtml(post.content)}</p>
            <div class="flex items-center justify-between">
                <span class="text-xs px-2 py-1 rounded-lg text-white ${platformColor}"><i class="${platformIcon} mr-1"></i>${post.platform}</span>
                <span class="text-xs text-slate-400">${post.author_name}</span>
            </div>
            ${post.comment_count > 0 ? `<div class="text-xs text-slate-400 mt-2 flex items-center gap-1"><i class="fa-regular fa-comment"></i> ${post.comment_count}</div>` : ''}
        </div>
    `;
}

// ==================== CREATE MODAL ====================
function openCreateModal() {
    document.getElementById('createForm').reset();
    document.getElementById('createPreviewArea').classList.add('hidden');
    document.getElementById('createUploadPrompt').classList.remove('hidden');
    document.getElementById('createFileInput').value = '';
    document.getElementById('createModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function previewCreateFile(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const preview = document.getElementById('createPreviewArea');
    const prompt = document.getElementById('createUploadPrompt');
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="max-h-40 mx-auto rounded-lg">`;
            preview.classList.remove('hidden');
            prompt.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = `<div class="text-slate-600"><svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>${file.name}</div>`;
        preview.classList.remove('hidden');
        prompt.classList.add('hidden');
    }
}

document.getElementById('createForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('title', document.getElementById('createTitle').value);
    formData.append('content', document.getElementById('createContent').value);
    formData.append('platform', document.getElementById('createPlatform').value);
    formData.append('status', document.getElementById('createStatus').value);
    formData.append('urgency', document.getElementById('createUrgent').checked ? '1' : '0');
    
    const fileInput = document.getElementById('createFileInput');
    if (fileInput.files[0]) {
        formData.append('file', fileInput.files[0]);
    }
    
    const res = await fetch('api.php?action=save_post', { method: 'POST', body: formData });
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
    
    // Platform badge with icon
    const platformIcon = PLATFORM_ICONS[p.platform] || 'fa-solid fa-share-nodes';
    document.getElementById('viewPlatformBadge').innerHTML = `<span class="px-2 py-0.5 rounded text-white text-xs ${PLATFORM_COLORS[p.platform] || 'bg-slate-500'}"><i class="${platformIcon} mr-1"></i>${p.platform}</span>`;
    
    // Title and meta
    document.getElementById('viewTitle').textContent = p.title;
    document.getElementById('viewAuthor').innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> ${p.author_full_name || p.author_name}`;
    document.getElementById('viewDate').textContent = formatDate(p.created_at);
    document.getElementById('viewUrgentBadge').classList.toggle('hidden', p.urgency != 1);
    
    // Content
    document.getElementById('viewContent').textContent = p.content;
    
    // Media
    const mediaContainer = document.getElementById('viewMediaContainer');
    const mediaWrapper = document.getElementById('viewMediaWrapper');
    if (p.media && p.media.length > 0) {
        const primary = p.media.find(m => m.is_primary) || p.media[0];
        if (isVideoFile(primary.file_path)) {
            mediaWrapper.innerHTML = `<video src="${primary.file_path}" controls class="w-full max-h-96 rounded-xl bg-slate-100"></video>`;
        } else {
            mediaWrapper.innerHTML = `<img src="${primary.file_path}" class="w-full max-h-96 object-contain rounded-xl bg-slate-100">`;
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
    document.getElementById('editPlatform').value = post.platform || 'Facebook';
    document.getElementById('editUrgent').checked = post.urgency == 1;
    
    // Media gallery
    renderEditMediaGallery(post.media || []);
    
    document.getElementById('editModal').classList.remove('hidden');
}

function renderEditMediaGallery(media) {
    document.getElementById('editMediaGallery').innerHTML = media.map(m => `
        <div class="relative group">
            <img src="${m.file_path}" class="w-full h-20 object-cover rounded-lg">
            <button type="button" onclick="deleteMedia(${m.id})" class="absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full opacity-0 group-hover:opacity-100 text-sm font-bold shadow-lg">×</button>
        </div>
    `).join('') || '<p class="col-span-4 text-slate-400 text-sm">No media attached</p>';
}

async function uploadEditFile(e) {
    const postId = document.getElementById('editPostId').value;
    if (!postId) return;
    
    for (const file of e.target.files) {
        const fd = new FormData();
        fd.append('file', file);
        fd.append('post_id', postId);
        const res = await fetch('api.php?action=upload_media', { method: 'POST', body: fd });
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
    
    const formData = {
        id: document.getElementById('editPostId').value,
        title: document.getElementById('editTitle').value,
        content: document.getElementById('editContent').value,
        platform: document.getElementById('editPlatform').value,
        scheduled_date: document.getElementById('editScheduled').value || null,
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
    const data = await api('get_notifications');
    if (data.success) {
        const badge = document.getElementById('notifBadge');
        if (data.data.unread_count > 0) { 
            badge.textContent = data.data.unread_count; 
            badge.classList.remove('hidden'); 
        } else {
            badge.classList.add('hidden');
        }
        
        document.getElementById('notifList').innerHTML = data.data.notifications.slice(0, 10).map(n => `
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

init();
</script>
</body>
</html>
