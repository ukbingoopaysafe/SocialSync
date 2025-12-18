<?php
require_once 'config.php';
require_once 'db.php';

// Start session
session_name(SESSION_NAME);
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BroMan Social - Social Media Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .modal-backdrop {
            backdrop-filter: blur(4px);
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .platform-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="images/Final_Logo.png" alt="BroMan Social" style="height: 40px;">
                <span id="userBadge" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium"></span>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="createNewPost()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition shadow-md hover:shadow-lg">
                    + New Post
                </button>
                <button onclick="logout()" class="text-gray-600 hover:text-gray-800 font-medium transition">
                    Logout
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex gap-6">
            
            <!-- Sidebar -->
            <aside class="w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-md p-4 sticky top-6">
                    <!-- View Toggle -->
                    <div class="mb-6">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">View</h3>
                        <div class="space-y-2">
                            <button onclick="switchView('BOARD')" id="viewBoard" class="w-full text-left px-3 py-2 rounded-lg font-medium transition">
                                📊 Kanban Board
                            </button>
                            <button onclick="switchView('CALENDAR')" id="viewCalendar" class="w-full text-left px-3 py-2 rounded-lg font-medium transition">
                                📅 Calendar
                            </button>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Filters</h3>
                        
                        <!-- Quick Filters -->
                        <div class="space-y-2 mb-4">
                            <button onclick="setStatusFilter('all')" id="filterAll" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition">
                                All Posts
                            </button>
                            <button onclick="setStatusFilter('my')" id="filterMy" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition">
                                My Posts
                            </button>
                            <button onclick="setStatusFilter('PENDING_REVIEW')" id="filterPending" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition">
                                Pending Review
                            </button>
                            <button onclick="setStatusFilter('urgent')" id="filterUrgent" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition">
                                🔥 Urgent
                            </button>
                        </div>

                        <!-- Platform Filter -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-xs font-semibold text-gray-600 mb-2">Platform</h4>
                            <select id="platformFilter" onchange="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">All Platforms</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Instagram">Instagram</option>
                                <option value="LinkedIn">LinkedIn</option>
                                <option value="Twitter">Twitter</option>
                                <option value="TikTok">TikTok</option>
                                <option value="Website">Website</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="Search posts..." 
                                onkeyup="debounceSearch()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 min-w-0">
                <!-- Board View -->
                <div id="boardView" class="hidden">
                    <div class="flex gap-4 overflow-x-auto pb-4">
                        <div id="columnIdea" class="flex-shrink-0 w-80 bg-gray-100 rounded-lg p-4">
                            <h3 class="font-bold text-gray-700 mb-4 flex items-center justify-between">
                                <span>💡 Ideas</span>
                                <span id="countIdea" class="text-sm font-normal text-gray-500">0</span>
                            </h3>
                            <div id="postsIdea" class="space-y-3"></div>
                        </div>

                        <div id="columnDraft" class="flex-shrink-0 w-80 bg-blue-50 rounded-lg p-4">
                            <h3 class="font-bold text-blue-700 mb-4 flex items-center justify-between">
                                <span>✏️ Draft</span>
                                <span id="countDraft" class="text-sm font-normal text-blue-500">0</span>
                            </h3>
                            <div id="postsDraft" class="space-y-3"></div>
                        </div>

                        <div id="columnPending" class="flex-shrink-0 w-80 bg-yellow-50 rounded-lg p-4">
                            <h3 class="font-bold text-yellow-700 mb-4 flex items-center justify-between">
                                <span>⏳ Pending Review</span>
                                <span id="countPending" class="text-sm font-normal text-yellow-500">0</span>
                            </h3>
                            <div id="postsPending" class="space-y-3"></div>
                        </div>

                        <div id="columnApproved" class="flex-shrink-0 w-80 bg-green-50 rounded-lg p-4">
                            <h3 class="font-bold text-green-700 mb-4 flex items-center justify-between">
                                <span>✅ Approved</span>
                                <span id="countApproved" class="text-sm font-normal text-green-500">0</span>
                            </h3>
                            <div id="postsApproved" class="space-y-3"></div>
                        </div>

                        <div id="columnScheduled" class="flex-shrink-0 w-80 bg-purple-50 rounded-lg p-4">
                            <h3 class="font-bold text-purple-700 mb-4 flex items-center justify-between">
                                <span>📅 Scheduled</span>
                                <span id="countScheduled" class="text-sm font-normal text-purple-500">0</span>
                            </h3>
                            <div id="postsScheduled" class="space-y-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Calendar View -->
                <div id="calendarView" class="hidden">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-800" id="calendarTitle">Week Calendar</h2>
                            <div class="flex gap-2">
                                <button onclick="previousWeek()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                                    ← Previous
                                </button>
                                <button onclick="currentWeek()" class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition font-medium">
                                    Today
                                </button>
                                <button onclick="nextWeek()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                                    Next →
                                </button>
                            </div>
                        </div>
                        <div id="calendarGrid" class="grid grid-cols-7 gap-4"></div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Post Editor Modal -->
    <div id="postModal" class="hidden fixed inset-0 bg-black bg-opacity-50 modal-backdrop z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Create Post</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-3xl leading-none">&times;</button>
            </div>
            
            <form id="postForm" class="p-6 space-y-5">
                <input type="hidden" id="postId" name="id">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                    <input 
                        type="text" 
                        id="postTitle" 
                        name="title" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Enter post title"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Content *</label>
                    <textarea 
                        id="postContent" 
                        name="content" 
                        rows="6" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                        placeholder="Write your post content here..."
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Platform *</label>
                        <select 
                            id="postPlatform" 
                            name="platform" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                            <option value="">Select Platform</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Instagram">Instagram</option>
                            <option value="LinkedIn">LinkedIn</option>
                            <option value="Twitter">Twitter</option>
                            <option value="TikTok">TikTok</option>
                            <option value="Website">Website</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select 
                            id="postStatus" 
                            name="status"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                            <option value="IDEA">Idea</option>
                            <option value="DRAFT" selected>Draft</option>
                            <option value="PENDING_REVIEW">Pending Review</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Scheduled Date & Time</label>
                    <input 
                        type="datetime-local" 
                        id="postScheduledDate" 
                        name="scheduled_date"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Image URL (Optional)</label>
                    <input 
                        type="url" 
                        id="postImageUrl" 
                        name="image_url"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="https://example.com/image.jpg"
                    >
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="postUrgency" 
                        name="urgency"
                        class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                    >
                    <label for="postUrgency" class="ml-3 text-sm font-medium text-gray-700">
                        🔥 Mark as Urgent
                    </label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button 
                        type="submit" 
                        class="flex-1 bg-indigo-600 text-white font-semibold py-3 rounded-lg hover:bg-indigo-700 transition shadow-md hover:shadow-lg"
                    >
                        Save Post
                    </button>
                    <button 
                        type="button" 
                        onclick="closeModal()" 
                        class="px-6 bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg hover:bg-gray-300 transition"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ===== Application State =====
        const appState = {
            user: null,
            posts: [],
            view: 'BOARD',
            filters: {
                status: 'all',
                platform: '',
                search: '',
                myPosts: false,
                urgent: false
            },
            currentWeek: new Date()
        };

        // ===== Initialization =====
        async function init() {
            await loadUser();
            await loadPosts();
            switchView('BOARD');
        }

        // ===== User Management =====
        async function loadUser() {
            try {
                const response = await fetch('api.php?action=get_user');
                const data = await response.json();
                
                if (data.success) {
                    appState.user = data.data;
                    document.getElementById('userBadge').textContent = `${data.data.username} (${data.data.role})`;
                } else {
                    window.location.href = 'login.php';
                }
            } catch (error) {
                console.error('Error loading user:', error);
                window.location.href = 'login.php';
            }
        }

        async function logout() {
            try {
                await fetch('api.php?action=logout', { method: 'POST' });
                window.location.href = 'login.php';
            } catch (error) {
                console.error('Logout error:', error);
                window.location.href = 'login.php';
            }
        }

        // ===== Posts Management =====
        async function loadPosts() {
            try {
                let url = 'api.php?action=fetch_posts';
                
                // Apply filters
                if (appState.filters.status !== 'all' && appState.filters.status !== 'my' && appState.filters.status !== 'urgent') {
                    url += `&status=${appState.filters.status}`;
                }
                if (appState.filters.platform) {
                    url += `&platform=${appState.filters.platform}`;
                }
                if (appState.filters.search) {
                    url += `&search=${encodeURIComponent(appState.filters.search)}`;
                }
                if (appState.filters.myPosts) {
                    url += `&my_posts=true`;
                }
                if (appState.filters.urgent) {
                    url += `&urgent=true`;
                }
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    appState.posts = data.data;
                    render();
                }
            } catch (error) {
                console.error('Error loading posts:', error);
            }
        }

        // ===== View Management =====
        function switchView(view) {
            appState.view = view;
            
            // Update button styles
            document.getElementById('viewBoard').className = view === 'BOARD' 
                ? 'w-full text-left px-3 py-2 rounded-lg font-medium bg-indigo-100 text-indigo-700 transition'
                : 'w-full text-left px-3 py-2 rounded-lg font-medium text-gray-700 hover:bg-gray-100 transition';
            
            document.getElementById('viewCalendar').className = view === 'CALENDAR'
                ? 'w-full text-left px-3 py-2 rounded-lg font-medium bg-indigo-100 text-indigo-700 transition'
                : 'w-full text-left px-3 py-2 rounded-lg font-medium text-gray-700 hover:bg-gray-100 transition';
            
            // Show/hide views
            document.getElementById('boardView').classList.toggle('hidden', view !== 'BOARD');
            document.getElementById('calendarView').classList.toggle('hidden', view !== 'CALENDAR');
            
            render();
        }

        function render() {
            if (appState.view === 'BOARD') {
                renderBoard();
            } else {
                renderCalendar();
            }
        }

        // ===== Board View =====
        function renderBoard() {
            const statuses = ['IDEA', 'DRAFT', 'PENDING_REVIEW', 'APPROVED', 'SCHEDULED'];
            
            statuses.forEach(status => {
                const container = document.getElementById(`posts${status.split('_').map(s => s.charAt(0) + s.slice(1).toLowerCase()).join('')}`);
                const countEl = document.getElementById(`count${status.split('_').map(s => s.charAt(0) + s.slice(1).toLowerCase()).join('')}`);
                
                const statusPosts = appState.posts.filter(p => p.status === status);
                countEl.textContent = statusPosts.length;
                
                container.innerHTML = statusPosts.map(post => createPostCard(post)).join('');
            });
        }

        function createPostCard(post) {
            const statusColors = {
                'IDEA': 'bg-gray-200 text-gray-800',
                'DRAFT': 'bg-blue-200 text-blue-800',
                'PENDING_REVIEW': 'bg-yellow-200 text-yellow-800',
                'APPROVED': 'bg-green-200 text-green-800',
                'REJECTED': 'bg-red-200 text-red-800',
                'SCHEDULED': 'bg-purple-200 text-purple-800',
                'PUBLISHED': 'bg-indigo-200 text-indigo-800'
            };
            
            const platformColors = {
                'Facebook': 'bg-blue-100 text-blue-700',
                'Instagram': 'bg-pink-100 text-pink-700',
                'LinkedIn': 'bg-blue-100 text-blue-800',
                'Twitter': 'bg-sky-100 text-sky-700',
                'TikTok': 'bg-gray-800 text-white',
                'Website': 'bg-indigo-100 text-indigo-700'
            };
            
            const isApprover = appState.user.role === 'approver';
            const canApprove = isApprover && post.status === 'PENDING_REVIEW';
            
            return `
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-4 cursor-pointer" onclick="editPost(${post.id})">
                    ${post.urgency ? '<div class="text-red-500 text-sm font-semibold mb-2">🔥 URGENT</div>' : ''}
                    
                    <h4 class="font-bold text-gray-800 mb-2 line-clamp-2">${escapeHtml(post.title)}</h4>
                    <p class="text-sm text-gray-600 mb-3 line-clamp-3">${escapeHtml(post.content)}</p>
                    
                    <div class="flex items-center justify-between mb-3">
                        <span class="platform-badge ${platformColors[post.platform] || 'bg-gray-100 text-gray-700'}">
                            ${post.platform}
                        </span>
                        <span class="text-xs text-gray-500">by ${post.author_name}</span>
                    </div>
                    
                    ${post.scheduled_date ? `
                        <div class="text-xs text-gray-500 mb-2">
                            📅 ${formatDate(post.scheduled_date)}
                        </div>
                    ` : ''}
                    
                    ${post.rejected_reason ? `
                        <div class="bg-red-50 border border-red-200 text-red-700 text-xs p-2 rounded mb-2">
                            Rejected: ${escapeHtml(post.rejected_reason)}
                        </div>
                    ` : ''}
                    
                    ${canApprove ? `
                        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-200" onclick="event.stopPropagation()">
                            <button onclick="approvePost(${post.id})" class="flex-1 bg-green-500 text-white text-xs font-semibold py-2 rounded hover:bg-green-600 transition">
                                ✓ Approve
                            </button>
                            <button onclick="rejectPost(${post.id})" class="flex-1 bg-red-500 text-white text-xs font-semibold py-2 rounded hover:bg-red-600 transition">
                                ✗ Reject
                            </button>
                        </div>
                    ` : ''}
                </div>
            `;
        }

        // ===== Calendar View =====
        function renderCalendar() {
            const weekStart = getWeekStart(appState.currentWeek);
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);
            
            document.getElementById('calendarTitle').textContent = 
                `${formatDateShort(weekStart)} - ${formatDateShort(weekEnd)}`;
            
            const grid = document.getElementById('calendarGrid');
            const days = [];
            
            for (let i = 0; i < 7; i++) {
                const day = new Date(weekStart);
                day.setDate(day.getDate() + i);
                days.push(day);
            }
            
            grid.innerHTML = days.map(day => {
                const dayPosts = appState.posts.filter(post => {
                    if (!post.scheduled_date) return false;
                    const postDate = new Date(post.scheduled_date);
                    return postDate.toDateString() === day.toDateString();
                });
                
                const isToday = day.toDateString() === new Date().toDateString();
                
                return `
                    <div class="border ${isToday ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 bg-white'} rounded-lg p-3 min-h-[200px]">
                        <div class="font-bold text-gray-700 mb-3 ${isToday ? 'text-indigo-600' : ''}">
                            ${day.toLocaleDateString('en-US', { weekday: 'short' })}
                            <div class="text-2xl ${isToday ? 'text-indigo-600' : 'text-gray-800'}">${day.getDate()}</div>
                        </div>
                        <div class="space-y-2">
                            ${dayPosts.map(post => createCalendarPost(post)).join('')}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function createCalendarPost(post) {
            const platformColors = {
                'Facebook': 'bg-blue-500',
                'Instagram': 'bg-pink-500',
                'LinkedIn': 'bg-blue-700',
                'Twitter': 'bg-sky-500',
                'TikTok': 'bg-gray-800',
                'Website': 'bg-indigo-600'
            };
            
            const time = new Date(post.scheduled_date).toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
            
            return `
                <div onclick="editPost(${post.id})" class="bg-white border-l-4 ${platformColors[post.platform] || 'border-gray-500'} p-2 rounded shadow-sm hover:shadow-md transition cursor-pointer">
                    <div class="text-xs font-semibold text-gray-600">${time}</div>
                    <div class="text-sm font-medium text-gray-800 line-clamp-2">${escapeHtml(post.title)}</div>
                    <div class="text-xs text-gray-500 mt-1">${post.platform}</div>
                    ${post.urgency ? '<div class="text-xs text-red-500 font-semibold mt-1">🔥 Urgent</div>' : ''}
                </div>
            `;
        }

        function getWeekStart(date) {
            const d = new Date(date);
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Monday as start
            return new Date(d.setDate(diff));
        }

        function previousWeek() {
            appState.currentWeek.setDate(appState.currentWeek.getDate() - 7);
            renderCalendar();
        }

        function nextWeek() {
            appState.currentWeek.setDate(appState.currentWeek.getDate() + 7);
            renderCalendar();
        }

        function currentWeek() {
            appState.currentWeek = new Date();
            renderCalendar();
        }

        // ===== Modal Management =====
        function createNewPost() {
            document.getElementById('modalTitle').textContent = 'Create Post';
            document.getElementById('postForm').reset();
            document.getElementById('postId').value = '';
            document.getElementById('postModal').classList.remove('hidden');
        }

        async function editPost(id) {
            try {
                const response = await fetch(`api.php?action=get_post&id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const post = data.data;
                    document.getElementById('modalTitle').textContent = 'Edit Post';
                    document.getElementById('postId').value = post.id;
                    document.getElementById('postTitle').value = post.title;
                    document.getElementById('postContent').value = post.content;
                    document.getElementById('postPlatform').value = post.platform;
                    document.getElementById('postStatus').value = post.status;
                    document.getElementById('postUrgency').checked = post.urgency == 1;
                    document.getElementById('postImageUrl').value = post.image_url || '';
                    
                    if (post.scheduled_date) {
                        const date = new Date(post.scheduled_date);
                        const formatted = date.toISOString().slice(0, 16);
                        document.getElementById('postScheduledDate').value = formatted;
                    }
                    
                    document.getElementById('postModal').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading post:', error);
            }
        }

        function closeModal() {
            document.getElementById('postModal').classList.add('hidden');
        }

        document.getElementById('postForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = {
                id: document.getElementById('postId').value || null,
                title: document.getElementById('postTitle').value,
                content: document.getElementById('postContent').value,
                platform: document.getElementById('postPlatform').value,
                status: document.getElementById('postStatus').value,
                urgency: document.getElementById('postUrgency').checked,
                scheduled_date: document.getElementById('postScheduledDate').value || null,
                image_url: document.getElementById('postImageUrl').value || null
            };
            
            try {
                const response = await fetch('api.php?action=save_post', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeModal();
                    await loadPosts();
                } else {
                    alert(data.message || 'Error saving post');
                }
            } catch (error) {
                console.error('Error saving post:', error);
                alert('Error saving post');
            }
        });

        // ===== Post Actions =====
        async function approvePost(id) {
            if (!confirm('Approve this post?')) return;
            
            try {
                const response = await fetch('api.php?action=update_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, status: 'APPROVED' })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadPosts();
                } else {
                    alert(data.message || 'Error approving post');
                }
            } catch (error) {
                console.error('Error approving post:', error);
            }
        }

        async function rejectPost(id) {
            const reasons = [
                'Image quality issues',
                'Caption too long',
                'Off-brand content',
                'Spelling/grammar errors',
                'Other'
            ];
            
            let reason = prompt(`Select rejection reason:\n${reasons.map((r, i) => `${i + 1}. ${r}`).join('\n')}\n\nEnter number or custom reason:`);
            
            if (!reason) return;
            
            const reasonIndex = parseInt(reason) - 1;
            if (reasonIndex >= 0 && reasonIndex < reasons.length) {
                reason = reasons[reasonIndex];
            }
            
            try {
                const response = await fetch('api.php?action=update_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, status: 'REJECTED', rejected_reason: reason })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadPosts();
                } else {
                    alert(data.message || 'Error rejecting post');
                }
            } catch (error) {
                console.error('Error rejecting post:', error);
            }
        }

        // ===== Filters =====
        function setStatusFilter(status) {
            appState.filters.status = status;
            appState.filters.myPosts = status === 'my';
            appState.filters.urgent = status === 'urgent';
            
            // Update button styles
            const buttons = ['filterAll', 'filterMy', 'filterPending', 'filterUrgent'];
            buttons.forEach(btn => {
                const el = document.getElementById(btn);
                const isActive = 
                    (btn === 'filterAll' && status === 'all') ||
                    (btn === 'filterMy' && status === 'my') ||
                    (btn === 'filterPending' && status === 'PENDING_REVIEW') ||
                    (btn === 'filterUrgent' && status === 'urgent');
                
                el.className = isActive
                    ? 'w-full text-left px-3 py-2 rounded-lg text-sm font-medium bg-indigo-100 text-indigo-700 transition'
                    : 'w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition';
            });
            
            loadPosts();
        }

        function applyFilters() {
            appState.filters.platform = document.getElementById('platformFilter').value;
            loadPosts();
        }

        let searchTimeout;
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                appState.filters.search = document.getElementById('searchInput').value;
                loadPosts();
            }, 500);
        }

        // ===== Utility Functions =====
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }

        function formatDateShort(date) {
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        // ===== Initialize App =====
        init();
    </script>
</body>
</html>
