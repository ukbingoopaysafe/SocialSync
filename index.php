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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                gold: { 400: '#facc15', 500: '#D4AF37', 600: '#ca8a04' },
                navy: { 800: '#0f2847', 900: '#0a1628', 950: '#050d17' }
            }}}
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .post-card { transition: all 0.2s; cursor: pointer; }
        .post-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .upload-zone { border: 2px dashed #cbd5e1; transition: all 0.2s; }
        .upload-zone:hover, .upload-zone.drag-over { border-color: #D4AF37; background: rgba(212,175,55,0.05); }
        .status-badge { font-size: 0.7rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
</head>
<body class="bg-slate-100">
    
    <!-- Header -->
    <header class="bg-navy-900 shadow-xl sticky top-0 z-40">
        <div class="max-w-full mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="images/Final_Logo%20White.png" alt="BroMan Social" style="height: 40px;">
                <div class="hidden md:flex gap-1 ml-4">
                    <button onclick="switchTab('dashboard')" id="tabDashboard" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-slate-300">Dashboard</button>
                    <button onclick="switchTab('board')" id="tabBoard" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-slate-300">Board</button>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <button onclick="toggleNotifications()" class="p-2 text-slate-300 hover:text-gold-500 relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span id="notifBadge" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">0</span>
                    </button>
                    <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border z-50">
                        <div class="px-4 py-3 bg-navy-900 text-white rounded-t-xl flex justify-between">
                            <span class="font-semibold">Notifications</span>
                            <button onclick="markAllRead()" class="text-xs text-gold-400 hover:underline">Mark all read</button>
                        </div>
                        <div id="notifList" class="max-h-80 overflow-y-auto"></div>
                    </div>
                </div>
                <button onclick="openCreateModal()" class="bg-gold-500 hover:bg-gold-600 text-navy-900 px-4 py-2 rounded-lg font-semibold text-sm shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    New Post
                </button>
                <div id="adminLink" class="hidden">
                    <a href="users.php" class="text-slate-300 hover:text-gold-400 text-sm flex items-center gap-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Users
                    </a>
                </div>
                <div class="flex items-center gap-2 ml-2 pl-4 border-l border-navy-700">
                    <div class="text-right hidden sm:block">
                        <div id="userName" class="text-white text-sm font-medium"></div>
                        <div id="userRole" class="text-gold-400 text-xs capitalize"></div>
                    </div>
                    <button onclick="logout()" class="p-2 text-slate-400 hover:text-red-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-full mx-auto px-4 py-6">
        <!-- Dashboard View -->
        <div id="dashboardView" class="hidden">
            <h2 class="text-2xl font-bold text-navy-900 mb-6">Dashboard</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-xl p-5 border-l-4 border-slate-400"><div id="statTotal" class="text-3xl font-bold">0</div><div class="text-sm text-slate-500">Total Posts</div></div>
                <div class="bg-white rounded-xl p-5 border-l-4 border-purple-500"><div id="statIdeas" class="text-3xl font-bold">0</div><div class="text-sm text-slate-500">Ideas</div></div>
                <div class="bg-white rounded-xl p-5 border-l-4 border-blue-500"><div id="statDrafts" class="text-3xl font-bold">0</div><div class="text-sm text-slate-500">Drafts</div></div>
                <div class="bg-white rounded-xl p-5 border-l-4 border-yellow-500"><div id="statPending" class="text-3xl font-bold">0</div><div class="text-sm text-slate-500">Pending Review</div></div>
                <div class="bg-white rounded-xl p-5 border-l-4 border-green-500"><div id="statApproved" class="text-3xl font-bold">0</div><div class="text-sm text-slate-500">Approved</div></div>
            </div>
            <div class="bg-white rounded-xl p-6"><h3 class="text-lg font-bold mb-4">Recent Activity</h3><div id="recentActivity" class="space-y-3"></div></div>
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
                <div class="flex-shrink-0 w-80 bg-purple-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <h3 class="font-bold text-purple-700 flex items-center gap-2">💡 Ideas <span id="countIDEA" class="text-sm font-normal bg-purple-200 px-2 py-0.5 rounded-full">0</span></h3>
                    </div>
                    <div id="postsIDEA" class="space-y-3 min-h-[200px]"></div>
                </div>
                
                <!-- DRAFTS -->
                <div class="flex-shrink-0 w-80 bg-blue-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <h3 class="font-bold text-blue-700 flex items-center gap-2">✏️ Drafts <span id="countDRAFT" class="text-sm font-normal bg-blue-200 px-2 py-0.5 rounded-full">0</span></h3>
                    </div>
                    <div id="postsDRAFT" class="space-y-3 min-h-[200px]"></div>
                </div>
                
                <!-- PENDING REVIEW -->
                <div class="flex-shrink-0 w-80 bg-yellow-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <h3 class="font-bold text-yellow-700 flex items-center gap-2">⏳ Pending Review <span id="countPENDING_REVIEW" class="text-sm font-normal bg-yellow-200 px-2 py-0.5 rounded-full">0</span></h3>
                    </div>
                    <div id="postsPENDING_REVIEW" class="space-y-3 min-h-[200px]"></div>
                </div>
                
                <!-- APPROVED -->
                <div class="flex-shrink-0 w-80 bg-green-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <h3 class="font-bold text-green-700 flex items-center gap-2">✅ Approved <span id="countAPPROVED" class="text-sm font-normal bg-green-200 px-2 py-0.5 rounded-full">0</span></h3>
                    </div>
                    <div id="postsAPPROVED" class="space-y-3 min-h-[200px]"></div>
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
                    <button type="submit" class="flex-1 bg-gold-500 hover:bg-gold-600 text-navy-900 font-bold py-3.5 rounded-xl text-lg shadow-lg">Create Post</button>
                    <button type="button" onclick="closeCreateModal()" class="px-8 bg-slate-200 hover:bg-slate-300 text-slate-700 py-3.5 rounded-xl font-semibold">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== VIEW POST MODAL (Read-Only) ==================== -->
    <div id="viewModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-start justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl my-8">
            <!-- Header -->
            <div class="bg-navy-900 text-white px-6 py-4 flex justify-between items-center rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <span id="viewStatusBadge" class="status-badge bg-blue-500 text-white">DRAFT</span>
                    <span id="viewPlatformBadge" class="text-sm text-slate-300"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button id="viewEditBtn" onclick="switchToEditMode()" class="text-slate-300 hover:text-gold-400 hover:bg-white/10 p-2 rounded-lg transition-all" title="Edit Post">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="closeViewModal()" class="text-2xl hover:text-gold-400">&times;</button>
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
                        <span id="viewUrgentBadge" class="hidden text-red-500 font-semibold">🔥 URGENT</span>
                    </div>
                </div>
                
                <!-- Changes Requested Notice -->
                <div id="viewChangesNotice" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                    <div class="font-semibold text-amber-700 mb-1">⚠️ Changes Requested</div>
                    <div id="viewChangesReason" class="text-amber-600 text-sm"></div>
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
                <details class="border-t pt-6 mt-6">
                    <summary class="font-bold text-lg cursor-pointer hover:text-gold-600">📋 Activity History</summary>
                    <div id="viewActivity" class="mt-4 space-y-2 max-h-48 overflow-y-auto"></div>
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
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Platform</label>
                        <select id="editPlatform" class="w-full px-4 py-3 border rounded-xl">
                            <option>Facebook</option><option>Instagram</option><option>LinkedIn</option>
                            <option>X</option><option>TikTok</option><option>YouTube</option><option>Snapchat</option><option>Website</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Scheduled Date</label>
                        <input type="datetime-local" id="editScheduled" class="w-full px-4 py-3 border rounded-xl">
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
                    <button type="submit" class="flex-1 bg-gold-500 hover:bg-gold-600 text-navy-900 font-bold py-3.5 rounded-xl">Save Changes</button>
                    <button type="button" onclick="closeEditModal()" class="px-8 bg-slate-200 hover:bg-slate-300 text-slate-700 py-3.5 rounded-xl font-semibold">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== REQUEST CHANGES MODAL ==================== -->
    <div id="changesModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold mb-4 flex items-center gap-2">↩️ Request Changes</h3>
            <p class="text-slate-600 text-sm mb-4">Please explain what changes are needed. This feedback will be visible to the post author.</p>
            <textarea id="changesReasonInput" rows="4" class="w-full px-4 py-3 border rounded-xl mb-4" placeholder="What changes are needed?"></textarea>
            <div class="flex gap-3">
                <button onclick="confirmRequestChanges()" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl">Request Changes</button>
                <button onclick="closeChangesModal()" class="px-6 bg-slate-200 hover:bg-slate-300 py-3 rounded-xl font-semibold">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toasts" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

<script>
const app = { user: null, posts: [], currentPost: null };
const STATUS_LIST = ['IDEA', 'DRAFT', 'PENDING_REVIEW', 'APPROVED'];
const STATUS_COLORS = {
    'IDEA': 'bg-purple-500',
    'DRAFT': 'bg-blue-500',
    'PENDING_REVIEW': 'bg-yellow-500',
    'CHANGES_REQUESTED': 'bg-amber-500',
    'APPROVED': 'bg-green-500'
};
const STATUS_LABELS = {
    'IDEA': '💡 Idea',
    'DRAFT': '✏️ Draft',
    'PENDING_REVIEW': '⏳ Pending Review',
    'CHANGES_REQUESTED': '⚠️ Changes Requested',
    'APPROVED': '✅ Approved'
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

async function loadDashboard() {
    const data = await api('get_dashboard_stats');
    if (data.success) {
        const s = data.data;
        document.getElementById('statTotal').textContent = s.total_posts || 0;
        document.getElementById('statIdeas').textContent = s.ideas || 0;
        document.getElementById('statDrafts').textContent = s.drafts || 0;
        document.getElementById('statPending').textContent = s.pending || 0;
        document.getElementById('statApproved').textContent = s.approved || 0;
        
        document.getElementById('recentActivity').innerHTML = (s.recent_activity || []).map(a => `
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                <div class="w-8 h-8 bg-gold-100 text-gold-600 rounded-full flex items-center justify-center font-bold text-sm">${a.username[0].toUpperCase()}</div>
                <div><div class="text-sm"><strong>${a.username}</strong> ${a.action} on "${a.post_title}"</div>
                <div class="text-xs text-slate-400">${formatDate(a.created_at)}</div></div>
            </div>
        `).join('') || '<p class="text-slate-400 text-center py-4">No recent activity</p>';
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
    const grouped = { IDEA: [], DRAFT: [], PENDING_REVIEW: [], APPROVED: [] };
    
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
            ${post.urgency == 1 ? '<div class="text-red-500 text-xs font-bold mb-2">🔥 URGENT</div>' : ''}
            ${hasChanges ? '<div class="text-amber-500 text-xs font-bold mb-2">⚠️ Changes Requested</div>' : ''}
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
    
    document.getElementById('viewModal').classList.remove('hidden');
}

function renderActionButtons(p) {
    const container = document.getElementById('actionButtons');
    const isAdmin = app.user.role === 'admin';
    const isOwner = p.author_id == app.user.id;
    let buttons = [];
    
    if (p.status === 'IDEA') {
        if (isAdmin) {
            buttons.push(`<button onclick="approveIdea()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl flex items-center justify-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Convert to Draft</button>`);
        }
    } else if (p.status === 'DRAFT' || p.status === 'CHANGES_REQUESTED') {
        if (isOwner || isAdmin) {
            buttons.push(`<button onclick="submitForReview()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl flex items-center justify-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg> 🚀 Submit for Review</button>`);
        }
    } else if (p.status === 'PENDING_REVIEW') {
        if (isAdmin) {
            buttons.push(`<button onclick="approvePost()" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-xl flex items-center justify-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> ✅ Approve</button>`);
            buttons.push(`<button onclick="openChangesModal()" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-6 rounded-xl flex items-center justify-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg> ↩️ Request Changes</button>`);
        }
    } else if (p.status === 'APPROVED') {
        if (isAdmin) {
            buttons.push(`<div class="flex-1 text-center py-3 px-6 bg-green-100 text-green-700 font-semibold rounded-xl">✅ This post is approved and ready!</div>`);
        }
    }
    
    container.innerHTML = buttons.join('') || '<div class="text-slate-400 text-center py-2">No actions available</div>';
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
    document.getElementById('viewActivity').innerHTML = activities.map(a => `
        <div class="flex gap-3 items-start text-sm">
            <div class="w-2 h-2 bg-gold-500 rounded-full mt-1.5 flex-shrink-0"></div>
            <div class="flex-1">
                <span class="font-medium">${a.username}</span> 
                <span class="text-slate-600">${a.action}</span>
                ${a.description ? `<span class="text-slate-400">- ${escapeHtml(a.description)}</span>` : ''}
                <div class="text-xs text-slate-400">${formatDate(a.created_at)}</div>
            </div>
        </div>
    `).join('') || '<p class="text-slate-400 text-center">No activity</p>';
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
    document.getElementById('editScheduled').value = post.scheduled_date ? post.scheduled_date.slice(0, 16) : '';
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
            <div class="p-3 border-b ${n.is_read ? 'bg-white' : 'bg-gold-50'} hover:bg-slate-50 cursor-pointer" onclick="notifClick(${n.id}, ${n.post_id})">
                <div class="font-semibold text-sm">${n.title}</div>
                <div class="text-xs text-slate-500 line-clamp-2">${n.message}</div>
                <div class="text-xs text-slate-400 mt-1">${formatDate(n.created_at)}</div>
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
