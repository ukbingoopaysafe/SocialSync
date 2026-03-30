<?php
// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Shared Sidebar Layout -->
<div class="flex min-h-screen">
    
    <!-- Dark Sidebar -->
    <aside id="sidebar" class="w-16 hover:w-56 transition-all duration-300 bg-[#0a1628] flex flex-col fixed h-full z-[60] group">
        <!-- Logo -->
        <div class="h-14 flex items-center justify-center border-b border-slate-700/50 px-0 overflow-hidden">
            <img id="sidebarLogo" src="images/Final_Logo White.png" alt="BroMan" class="h-11 max-w-[180px] object-contain hidden group-hover:block transition-all duration-300">
            <img id="sidebarLogoSmall" src="images/Final_Logo White.png" alt="BroMan" class="h-10 w-auto max-w-[58px] object-contain group-hover:hidden" style="image-rendering: -webkit-optimize-contrast;">
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 py-4 px-2 space-y-1">
            <a href="index.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $currentPage === 'index' ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/10' ?> transition-colors">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Dashboard</span>
            </a>
            <a href="calendar.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $currentPage === 'calendar' ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/10' ?> transition-colors">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Calendar</span>
            </a>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
            <a href="users.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $currentPage === 'users' ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/10' ?> transition-colors">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Users</span>
            </a>
            <?php endif; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager'): ?>
            <a href="logs.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $currentPage === 'logs' ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/10' ?> transition-colors">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Activity Logs</span>
            </a>
            <?php endif; ?>
        </nav>
        
        <!-- Bottom Actions -->
        <div class="p-2 border-t border-slate-700/50">
            <a href="api.php?action=logout" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span class="text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Logout</span>
            </a>
        </div>
    </aside>
    
    <!-- Main Content Area -->
    <div class="flex-1 ml-16 transition-all duration-300">
        <!-- Top Header -->
        <header class="h-14 bg-[#0a1628] border-b border-slate-700/50 sticky top-0 z-40 flex items-center justify-between px-6">
            <div class="flex items-center gap-4">
                <h1 id="pageTitle" class="text-lg font-semibold text-white"><?= $pageTitle ?? 'Dashboard' ?></h1>
            </div>
            <div class="flex items-center gap-4">
                <!-- Search -->
                <div class="relative">
                    <input type="text" id="globalSearch" placeholder="Search..." class="w-64 pl-10 pr-4 py-2 bg-white/10 border-0 rounded-lg text-sm text-white placeholder-slate-400 focus:ring-2 focus:ring-sky-500 focus:bg-white/20 transition-colors">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <!-- User -->
                <div class="flex items-center gap-2 pl-4 border-l border-slate-700/50">
                    <div class="w-8 h-8 bg-sky-500 rounded-full flex items-center justify-center text-white font-medium text-sm">
                        <?= isset($_SESSION['user']) ? strtoupper(substr($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'], 0, 1)) : 'U' ?>
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-white text-sm font-medium"><?= $_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? '' ?></div>
                        <div class="text-slate-400 text-xs capitalize"><?= $_SESSION['user']['role'] ?? '' ?></div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Content -->
        <main class="p-6">
