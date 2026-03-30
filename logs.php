<?php
/**
 * BroMan Social - Activity Logs
 * Manager-only page for viewing all system activity
 */
require_once 'config.php';
require_once 'db.php';
session_name(SESSION_NAME);
session_start();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Manager-only check
$currentUser = fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
if (!$currentUser || $currentUser['role'] !== 'manager') {
    header('Location: index.php');
    exit;
}
$_SESSION['user'] = $currentUser;
$pageTitle = 'Activity Logs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - BroMan Social</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
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
        .logs-content { direction: rtl; text-align: right; }
        .log-row:hover { background: #f8fafc; }
        .action-badge { font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 6px; font-weight: 600; }
        .detail-panel { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .detail-panel.open { max-height: 500px; }
        .change-diff { background: #f1f5f9; border-radius: 8px; padding: 12px; font-size: 0.82rem; }
        .change-old { color: #dc2626; text-decoration: line-through; }
        .change-new { color: #16a34a; font-weight: 500; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-700">
    
    <?php include 'includes/sidebar.php'; ?>
    <div class="logs-content">
        <!-- Header & Filters -->
        <div class="mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">سجل النشاطات</h2>
                    <p class="text-sm text-slate-500 mt-1">جميع العمليات التي تمت على النظام</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="totalLogs" class="text-sm text-slate-500">0 سجل</span>
                    <button onclick="exportLogs()" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                        <i class="fas fa-download"></i> تصدير
                    </button>
                </div>
            </div>
            
            <!-- Filters Row -->
            <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">بحث</label>
                    <input type="text" id="searchInput" placeholder="بحث في السجلات..." 
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">نوع العملية</label>
                    <select id="actionFilter" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">الكل</option>
                    </select>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">المستخدم</label>
                    <select id="userFilter" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">الكل</option>
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">من تاريخ</label>
                    <input type="date" id="dateFrom" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500">
                </div>
                <div class="w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">إلى تاريخ</label>
                    <input type="date" id="dateTo" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500">
                </div>
                <button onclick="loadLogs(1)" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-filter"></i> تصفية
                </button>
                <button onclick="resetFilters()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-times"></i> مسح
                </button>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="text-right px-4 py-3 font-semibold text-sm w-40">التاريخ</th>
                            <th class="text-right px-4 py-3 font-semibold text-sm w-32">المستخدم</th>
                            <th class="text-right px-4 py-3 font-semibold text-sm w-28">العملية</th>
                            <th class="text-right px-4 py-3 font-semibold text-sm">التفاصيل</th>
                            <th class="text-right px-4 py-3 font-semibold text-sm w-40">البوست</th>
                            <th class="text-center px-4 py-3 font-semibold text-sm w-16"></th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody" class="divide-y divide-slate-100">
                        <tr><td colspan="6" class="text-center py-12 text-slate-400">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="flex items-center justify-between mt-4 text-sm text-slate-500">
            <div id="pageInfo"></div>
            <div id="pageButtons" class="flex gap-2"></div>
        </div>
        </div>
    </main>

    <!-- Detail Modal -->
    <div id="detailModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" dir="rtl" style="backdrop-filter: blur(4px);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden">
            <div class="bg-slate-800 text-white px-6 py-4 flex justify-between items-center">
                <h2 class="text-lg font-bold">تفاصيل السجل</h2>
                <button onclick="closeDetailModal()" class="text-2xl hover:text-sky-400 leading-none">&times;</button>
            </div>
            <div id="detailContent" class="p-6 overflow-y-auto max-h-[60vh]"></div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toasts" class="fixed bottom-4 left-4 z-50 space-y-2"></div>

<script>
let currentPage = 1;
let filtersLoaded = false;

const ACTION_LABELS = {
    'created': { label: 'إنشاء', color: 'bg-green-100 text-green-700', icon: 'fas fa-plus-circle' },
    'updated': { label: 'تعديل', color: 'bg-blue-100 text-blue-700', icon: 'fas fa-edit' },
    'status_changed': { label: 'تغيير حالة', color: 'bg-purple-100 text-purple-700', icon: 'fas fa-exchange-alt' },
    'post_deleted': { label: 'حذف بوست', color: 'bg-red-100 text-red-700', icon: 'fas fa-trash' },
    'media_uploaded': { label: 'رفع ملف', color: 'bg-cyan-100 text-cyan-700', icon: 'fas fa-upload' },
    'media_deleted': { label: 'حذف ملف', color: 'bg-orange-100 text-orange-700', icon: 'fas fa-file-excel' },
    'comment_added': { label: 'تعليق', color: 'bg-yellow-100 text-yellow-700', icon: 'fas fa-comment' },
    'auto_published': { label: 'نشر تلقائي', color: 'bg-emerald-100 text-emerald-700', icon: 'fas fa-robot' },
    'user_created': { label: 'إنشاء مستخدم', color: 'bg-green-100 text-green-700', icon: 'fas fa-user-plus' },
    'user_updated': { label: 'تعديل مستخدم', color: 'bg-blue-100 text-blue-700', icon: 'fas fa-user-edit' },
    'user_deleted': { label: 'حذف مستخدم', color: 'bg-red-100 text-red-700', icon: 'fas fa-user-minus' },
};

async function api(action, params = '') {
    const res = await fetch(`api.php?action=${action}${params}`);
    return res.json();
}

async function loadLogs(page = 1) {
    currentPage = page;
    const search = document.getElementById('searchInput').value;
    const actionType = document.getElementById('actionFilter').value;
    const userId = document.getElementById('userFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    let params = `&page=${page}`;
    if (search) params += `&search=${encodeURIComponent(search)}`;
    if (actionType) params += `&action_type=${encodeURIComponent(actionType)}`;
    if (userId) params += `&user_id=${userId}`;
    if (dateFrom) params += `&date_from=${dateFrom}`;
    if (dateTo) params += `&date_to=${dateTo}`;
    
    const data = await api('get_all_logs', params);
    
    if (!data.success) {
        toast(data.message || 'حدث خطأ', 'error');
        return;
    }
    
    const { logs, total, total_pages, action_types, users } = data.data;
    
    // Populate filters once
    if (!filtersLoaded && action_types && users) {
        populateFilters(action_types, users);
        filtersLoaded = true;
    }
    
    document.getElementById('totalLogs').textContent = `${total} سجل`;
    renderLogs(logs);
    renderPagination(page, total_pages, total);
}

function populateFilters(actionTypes, users) {
    const actionSelect = document.getElementById('actionFilter');
    actionTypes.forEach(action => {
        const label = ACTION_LABELS[action]?.label || action;
        const opt = document.createElement('option');
        opt.value = action;
        opt.textContent = label;
        actionSelect.appendChild(opt);
    });
    
    const userSelect = document.getElementById('userFilter');
    users.forEach(u => {
        const opt = document.createElement('option');
        opt.value = u.id;
        opt.textContent = u.full_name || u.username;
        userSelect.appendChild(opt);
    });
}

function renderLogs(logs) {
    const tbody = document.getElementById('logsTableBody');
    
    if (logs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 text-slate-400">
            <i class="fas fa-clipboard-list text-4xl mb-3 block text-slate-300"></i>
            لا توجد سجلات
        </td></tr>`;
        return;
    }
    
    tbody.innerHTML = logs.map(log => {
        const actionInfo = ACTION_LABELS[log.action] || { label: log.action, color: 'bg-slate-100 text-slate-600', icon: 'fas fa-circle' };
        const hasDetails = log.old_value || log.new_value;
        const postTitle = log.post_title ? escapeHtml(truncate(log.post_title, 30)) : '<span class="text-slate-400 italic">محذوف</span>';
        
        return `
            <tr class="log-row transition-colors">
                <td class="px-4 py-3 text-sm text-slate-500">
                    <div>${formatDate(log.created_at)}</div>
                    <div class="text-xs text-slate-400">${formatTime(log.created_at)}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">
                            ${(log.user_full_name || log.username || '?')[0].toUpperCase()}
                        </div>
                        <span class="text-sm font-medium text-slate-700">${escapeHtml(log.user_full_name || log.username)}</span>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="action-badge ${actionInfo.color}">
                        <i class="${actionInfo.icon} ml-1"></i> ${actionInfo.label}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">
                    ${log.description ? escapeHtml(truncate(log.description, 60)) : '<span class="text-slate-400">—</span>'}
                </td>
                <td class="px-4 py-3 text-sm">
                    ${log.post_id ? `<a href="index.php#post-${log.post_id}" class="text-sky-600 hover:underline">${postTitle}</a>` : (log.action === 'post_deleted' ? `<span class="text-red-400 italic">محذوف</span>` : '<span class="text-slate-400">—</span>')}
                </td>
                <td class="px-4 py-3 text-center">
                    ${hasDetails ? `<button onclick='showDetail(${JSON.stringify(log).replace(/'/g, "&#39;")})' class="text-sky-500 hover:text-sky-700 p-1" title="عرض التفاصيل">
                        <i class="fas fa-eye"></i>
                    </button>` : ''}
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(page, totalPages, total) {
    const info = document.getElementById('pageInfo');
    const btns = document.getElementById('pageButtons');
    
    info.textContent = `صفحة ${page} من ${totalPages} (${total} سجل)`;
    
    let html = '';
    if (page > 1) {
        html += `<button onclick="loadLogs(${page - 1})" class="px-3 py-1 bg-white border rounded-lg hover:bg-slate-50 text-sm">السابق</button>`;
    }
    
    // Show page numbers
    const start = Math.max(1, page - 2);
    const end = Math.min(totalPages, page + 2);
    for (let i = start; i <= end; i++) {
        html += `<button onclick="loadLogs(${i})" class="px-3 py-1 ${i === page ? 'bg-sky-500 text-white' : 'bg-white border hover:bg-slate-50'} rounded-lg text-sm">${i}</button>`;
    }
    
    if (page < totalPages) {
        html += `<button onclick="loadLogs(${page + 1})" class="px-3 py-1 bg-white border rounded-lg hover:bg-slate-50 text-sm">التالي</button>`;
    }
    
    btns.innerHTML = html;
}

function showDetail(log) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('detailContent');
    const actionInfo = ACTION_LABELS[log.action] || { label: log.action, color: 'bg-slate-100 text-slate-600' };
    
    let detailHtml = `
        <div class="space-y-4">
            <div class="flex items-center gap-3 mb-4">
                <span class="action-badge ${actionInfo.color} text-sm">${actionInfo.label}</span>
                <span class="text-sm text-slate-500">${formatDate(log.created_at)} ${formatTime(log.created_at)}</span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-slate-50 rounded-lg p-3">
                    <div class="text-xs text-slate-500 mb-1">المستخدم</div>
                    <div class="font-medium">${escapeHtml(log.user_full_name || log.username)}</div>
                </div>
                <div class="bg-slate-50 rounded-lg p-3">
                    <div class="text-xs text-slate-500 mb-1">البوست</div>
                    <div class="font-medium">${log.post_title ? escapeHtml(log.post_title) : '<span class="text-slate-400">محذوف / غير مرتبط</span>'}</div>
                </div>
            </div>
    `;
    
    if (log.description) {
        detailHtml += `
            <div class="bg-slate-50 rounded-lg p-3 mb-4">
                <div class="text-xs text-slate-500 mb-1">الوصف</div>
                <div class="text-sm">${escapeHtml(log.description)}</div>
            </div>
        `;
    }
    
    // Show old/new values with diff
    if (log.old_value || log.new_value) {
        let oldData = null, newData = null;
        try { oldData = JSON.parse(log.old_value); } catch(e) { oldData = log.old_value; }
        try { newData = JSON.parse(log.new_value); } catch(e) { newData = log.new_value; }
        
        if (typeof oldData === 'object' && oldData !== null && typeof newData === 'object' && newData !== null) {
            // JSON diff view - show field-by-field changes
            detailHtml += `<div class="space-y-3">
                <div class="text-sm font-semibold text-slate-700">التغييرات:</div>`;
            
            const allKeys = new Set([...Object.keys(oldData || {}), ...Object.keys(newData || {})]);
            const fieldLabels = {
                title: 'العنوان',
                content: 'المحتوى',
                platforms: 'المنصات',
                urgency: 'الأولوية العاجلة',
                priority: 'درجة الأهمية',
                status: 'الحالة',
                file_name: 'اسم الملف',
                file_type: 'نوع الملف'
            };
            
            allKeys.forEach(key => {
                const label = fieldLabels[key] || key;
                const oldVal = formatFieldValue(key, oldData?.[key]);
                const newVal = newData?.[key] !== undefined ? formatFieldValue(key, newData[key]) : null;
                
                detailHtml += `
                    <div class="change-diff">
                        <div class="text-xs font-semibold text-slate-500 mb-1">${label}</div>
                        ${oldVal ? `<div class="change-old mb-1">- ${oldVal}</div>` : ''}
                        ${newVal ? `<div class="change-new">+ ${newVal}</div>` : ''}
                    </div>
                `;
            });
            
            detailHtml += `</div>`;
        } else {
            // Simple text view
            if (oldData) {
                detailHtml += `
                    <div class="change-diff mb-2">
                        <div class="text-xs font-semibold text-slate-500 mb-1">القيمة السابقة</div>
                        <div class="change-old">${escapeHtml(typeof oldData === 'string' ? oldData : JSON.stringify(oldData))}</div>
                    </div>
                `;
            }
            if (newData) {
                detailHtml += `
                    <div class="change-diff">
                        <div class="text-xs font-semibold text-slate-500 mb-1">القيمة الجديدة</div>
                        <div class="change-new">${escapeHtml(typeof newData === 'string' ? newData : JSON.stringify(newData))}</div>
                    </div>
                `;
            }
        }
    }
    
    detailHtml += '</div>';
    content.innerHTML = detailHtml;
    modal.classList.remove('hidden');
}

function formatFieldValue(key, value) {
    if (value === null || value === undefined) return null;
    if (key === 'platforms') {
        try {
            const platforms = typeof value === 'string' ? JSON.parse(value) : value;
            return Array.isArray(platforms) ? platforms.join(', ') : String(value);
        } catch(e) { return String(value); }
    }
    if (key === 'urgency') return value ? 'نعم' : 'لا';
    if (typeof value === 'boolean') return value ? 'نعم' : 'لا';
    return escapeHtml(String(value));
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('actionFilter').value = '';
    document.getElementById('userFilter').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    loadLogs(1);
}

function exportLogs() {
    // Build CSV export
    const rows = document.querySelectorAll('#logsTableBody tr');
    let csv = 'التاريخ,المستخدم,العملية,التفاصيل,البوست\n';
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 5) {
            csv += Array.from(cells).slice(0, 5).map(c => `"${c.textContent.trim().replace(/"/g, '""')}"`).join(',') + '\n';
        }
    });
    
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `activity_logs_${new Date().toISOString().slice(0,10)}.csv`;
    link.click();
    toast('تم تصدير السجلات', 'success');
}

// Search on Enter
document.getElementById('searchInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') loadLogs(1);
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function truncate(str, len) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
}

function formatDate(str) {
    if (!str) return '';
    const d = new Date(str);
    return d.toLocaleDateString('ar-EG', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatTime(str) {
    if (!str) return '';
    const d = new Date(str);
    return d.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
}

function toast(msg, type = 'info') {
    const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' };
    const t = document.createElement('div');
    t.className = `${colors[type]} text-white px-4 py-2 rounded-lg shadow-lg text-sm`;
    t.textContent = msg;
    document.getElementById('toasts').appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

// Initial load
loadLogs(1);
</script>
<?php include 'includes/sidebar_footer.php'; ?>
</body>
</html>
