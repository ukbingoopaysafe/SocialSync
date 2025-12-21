<?php
/**
 * BroMan Social - Content Calendar
 * View scheduled and published posts in a calendar format
 */
require_once 'config.php';
require_once 'db.php';
session_name(SESSION_NAME);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pageTitle = 'Calendar';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Calendar - BroMan Social</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                brand: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' }
            }}}
        }
    </script>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .calendar-day { min-height: 120px; }
        .calendar-post { transition: all 0.15s; cursor: pointer; }
        .calendar-post:hover { background: #e2e8f0; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-700">
    
    <?php include 'includes/sidebar.php'; ?>
        
        <!-- Calendar Controls -->
        <div class="flex items-center justify-between mb-6">
            <button onclick="prevMonth()" class="px-4 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-medium text-slate-600">
                <i class="fa-solid fa-chevron-left mr-2"></i>Previous
            </button>
            <h2 id="currentMonth" class="text-xl font-semibold text-slate-800"></h2>
            <button onclick="nextMonth()" class="px-4 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-medium text-slate-600">
                Next<i class="fa-solid fa-chevron-right ml-2"></i>
            </button>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-4 mb-6 text-sm text-slate-600">
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-indigo-400"></span> Scheduled</div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-slate-400"></span> Published</div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-blue-500"></span> Facebook</div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-pink-500"></span> Instagram</div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-slate-800"></span> X</div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-red-500"></span> YouTube</div>
        </div>

        <!-- Calendar Grid -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 bg-slate-100 text-slate-600 text-center text-sm font-medium">
                <div class="py-3">Sunday</div>
                <div class="py-3">Monday</div>
                <div class="py-3">Tuesday</div>
                <div class="py-3">Wednesday</div>
                <div class="py-3">Thursday</div>
                <div class="py-3">Friday</div>
                <div class="py-3">Saturday</div>
            </div>
            <!-- Calendar Days -->
            <div id="calendarGrid" class="grid grid-cols-7 divide-x divide-y divide-slate-100">
                <!-- Days will be rendered here -->
            </div>
        </div>
    </main>

    <!-- Post Preview Modal -->
    <div id="previewModal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 border border-slate-200">
            <div class="flex justify-between items-start mb-4">
                <h3 id="previewTitle" class="text-lg font-semibold text-slate-800"></h3>
                <button onclick="closePreview()" class="text-xl text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <div id="previewContent" class="text-slate-500 mb-4"></div>
            <div class="flex items-center justify-between text-sm">
                <span id="previewPlatform" class="px-3 py-1 rounded-full text-white"></span>
                <span id="previewDate" class="text-slate-400"></span>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100">
                <a id="previewLink" href="#" class="block w-full text-center bg-brand-500 hover:bg-brand-600 text-white font-medium py-2.5 rounded-lg">
                    View Full Post
                </a>
            </div>
        </div>
    </div>

<script>
const PLATFORM_COLORS = {
    Facebook: 'bg-blue-600', Instagram: 'bg-pink-500', LinkedIn: 'bg-blue-700',
    X: 'bg-black', TikTok: 'bg-slate-800', YouTube: 'bg-red-600', Snapchat: 'bg-yellow-400', Website: 'bg-indigo-500'
};

let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth() + 1;
let calendarPosts = [];

async function loadCalendar() {
    const res = await fetch(`api.php?action=get_calendar_posts&month=${currentMonth}&year=${currentYear}`);
    const data = await res.json();
    if (data.success) {
        calendarPosts = data.data.posts;
        renderCalendar();
    }
}

function renderCalendar() {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('currentMonth').textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
    
    const firstDay = new Date(currentYear, currentMonth - 1, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
    const today = new Date();
    
    let html = '';
    
    // Empty cells for days before first of month
    for (let i = 0; i < firstDay; i++) {
        html += '<div class="calendar-day bg-slate-50 p-2"></div>';
    }
    
    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = today.getFullYear() === currentYear && today.getMonth() + 1 === currentMonth && today.getDate() === day;
        const dayPosts = calendarPosts.filter(p => {
            const postDate = p.scheduled_date || p.published_date;
            return postDate && postDate.startsWith(dateStr);
        });
        
        html += `
            <div class="calendar-day p-2 ${isToday ? 'bg-gold-50 ring-2 ring-gold-400 ring-inset' : 'bg-white'}">
                <div class="font-semibold text-sm mb-2 ${isToday ? 'text-gold-600' : 'text-slate-700'}">${day}</div>
                <div class="space-y-1">
                    ${dayPosts.map(p => `
                        <div onclick="showPreview(${p.id})" 
                             class="calendar-post text-xs p-1.5 rounded ${p.status === 'PUBLISHED' ? 'bg-slate-100 text-slate-600' : 'bg-indigo-100 text-indigo-700'} truncate">
                            <i class="${getPlatformIcon(p.platform)} mr-1"></i>
                            ${escapeHtml(p.title.substring(0, 20))}${p.title.length > 20 ? '...' : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    document.getElementById('calendarGrid').innerHTML = html;
}

function getPlatformIcon(platform) {
    const icons = {
        Facebook: 'fa-brands fa-facebook', Instagram: 'fa-brands fa-instagram', LinkedIn: 'fa-brands fa-linkedin',
        X: 'fa-brands fa-x-twitter', TikTok: 'fa-brands fa-tiktok', YouTube: 'fa-brands fa-youtube',
        Snapchat: 'fa-brands fa-snapchat', Website: 'fa-solid fa-globe'
    };
    return icons[platform] || 'fa-solid fa-share-nodes';
}

function prevMonth() {
    currentMonth--;
    if (currentMonth < 1) { currentMonth = 12; currentYear--; }
    loadCalendar();
}

function nextMonth() {
    currentMonth++;
    if (currentMonth > 12) { currentMonth = 1; currentYear++; }
    loadCalendar();
}

function showPreview(postId) {
    const post = calendarPosts.find(p => p.id === postId);
    if (!post) return;
    
    document.getElementById('previewTitle').textContent = post.title;
    document.getElementById('previewContent').textContent = post.author_full_name || post.author_name;
    
    const platformEl = document.getElementById('previewPlatform');
    platformEl.textContent = post.platform;
    platformEl.className = `px-3 py-1 rounded-full text-white ${PLATFORM_COLORS[post.platform] || 'bg-slate-500'}`;
    
    const date = post.scheduled_date || post.published_date;
    document.getElementById('previewDate').textContent = date ? new Date(date).toLocaleString() : '';
    document.getElementById('previewLink').href = `index.php?view=${postId}`;
    
    document.getElementById('previewModal').classList.remove('hidden');
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

loadCalendar();
</script>
<?php include 'includes/sidebar_footer.php'; ?>
</body>
</html>
