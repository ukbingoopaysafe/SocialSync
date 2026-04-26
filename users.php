<?php
/**
 * BroMan Social - User Management
 * Admin-only page for managing users
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

// Manager check
$currentUser = fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
if (!$currentUser || ($currentUser['role'] ?? '') !== 'manager') {
    header('Location: index.php');
    exit;
}
$pageTitle = 'User Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - BroMan Social</title>
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
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-700">
    
    <?php include 'includes/sidebar.php'; ?>
        <!-- Stats & Actions -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="flex gap-4">
                <div class="bg-white rounded-xl px-4 py-3 shadow-sm">
                    <div id="totalUsers" class="text-2xl font-bold text-navy-900">0</div>
                    <div class="text-sm text-slate-500">Total Users</div>
                </div>
                <div class="bg-white rounded-xl px-4 py-3 shadow-sm">
                    <div id="adminCount" class="text-2xl font-bold text-gold-600">0</div>
                    <div class="text-sm text-slate-500">Admins</div>
                </div>
                <div class="bg-white rounded-xl px-4 py-3 shadow-sm">
                    <div id="designerCount" class="text-2xl font-bold text-blue-600">0</div>
                    <div class="text-sm text-slate-500">Designers</div>
                </div>
            </div>
            <button onclick="openModal()" class="bg-gold-500 hover:bg-gold-600 text-navy-900 px-5 py-2.5 rounded-lg font-semibold text-sm shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add User
            </button>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <input type="text" id="searchInput" placeholder="Search users..." 
                   class="w-full max-w-md px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-gold-500 focus:border-gold-500"
                   onkeyup="filterUsers()">
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-navy-900 text-white">
                        <tr>
                            <th class="text-left px-6 py-4 font-semibold">User</th>

                            <th class="text-left px-6 py-4 font-semibold">Role</th>
                            <th class="text-left px-6 py-4 font-semibold">Status</th>
                            <th class="text-left px-6 py-4 font-semibold">Last Login</th>
                            <th class="text-center px-6 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody" class="divide-y divide-slate-100">
                        <!-- Users will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div id="emptyState" class="hidden text-center py-12 text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <p>No users found</p>
            </div>
        </div>
    </main>

    <!-- User Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="bg-navy-900 text-white px-6 py-4 flex justify-between rounded-t-2xl">
                <h2 id="modalTitle" class="text-xl font-bold">Add User</h2>
                <button onclick="closeModal()" class="text-2xl hover:text-gold-400">&times;</button>
            </div>
            <form id="userForm" class="p-6 space-y-4">
                <input type="hidden" id="userId">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" id="username" required 
                               class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-gold-500">
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-1">Full Name</label>
                        <input type="text" id="fullName" 
                               class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-gold-500">
                    </div>
                    

                    
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-1">
                            Password <span id="pwdRequired" class="text-red-500">*</span>
                            <span id="pwdOptional" class="hidden text-slate-400 text-xs">(leave blank to keep current)</span>
                        </label>
                        <input type="password" id="password" 
                               class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-gold-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1">Role <span class="text-red-500">*</span></label>
                        <select id="role" required 
                                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-gold-500">
                            <option value="designer">Designer</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="isActive" checked 
                                   class="w-5 h-5 text-green-500 rounded focus:ring-green-500">
                            <span class="text-sm font-medium">Active</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-gold-500 hover:bg-gold-600 text-navy-900 font-semibold py-3 rounded-lg">
                        Save User
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 bg-slate-200 hover:bg-slate-300 text-slate-700 py-3 rounded-lg">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Delete User?</h3>
                <p class="text-slate-500">This action cannot be undone. All posts by this user will also be deleted.</p>
                <p id="deleteUserName" class="font-semibold text-slate-700 mt-2"></p>
            </div>
            <div class="flex gap-3">
                <button onclick="confirmDelete()" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-lg">
                    Delete User
                </button>
                <button onclick="closeDeleteModal()" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 py-3 rounded-lg">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toasts" class="fixed bottom-4 right-4 z-[9999] space-y-2"></div>

<script>
let users = [];
let deleteUserId = null;
const currentUserId = <?= $_SESSION['user_id'] ?>;

function canonicalRole(role) {
    return role === 'staff' ? 'designer' : (role || '');
}

function getRoleLabel(role) {
    const normalized = canonicalRole(role);
    if (normalized === 'designer') return 'Designer';
    if (normalized === 'manager') return 'Manager';
    if (normalized === 'admin') return 'Admin';
    return normalized;
}

async function api(action, method = 'GET', body = null) {
    const opts = { method, headers: {} };
    if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    const res = await fetch(`api.php?action=${action}`, opts);
    return res.json();
}

async function loadUsers() {
    const data = await api('get_users');
    if (data.success) {
        users = data.data;
        renderUsers();
        updateStats();
    } else {
        toast(data.message || 'Failed to load users', 'error');
    }
}

function updateStats() {
    const admins = users.filter(u => canonicalRole(u.role) === 'admin').length;
    const designers = users.filter(u => canonicalRole(u.role) === 'designer').length;
    document.getElementById('totalUsers').textContent = users.length;
    document.getElementById('adminCount').textContent = admins;
    document.getElementById('designerCount').textContent = designers;
}

function renderUsers() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const filtered = users.filter(u => 
        u.username.toLowerCase().includes(search) ||
        (u.full_name && u.full_name.toLowerCase().includes(search))
    );
    
    const tbody = document.getElementById('usersTableBody');
    const emptyState = document.getElementById('emptyState');
    
    if (filtered.length === 0) {
        tbody.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    tbody.innerHTML = filtered.map(u => `
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gold-100 text-gold-600 rounded-full flex items-center justify-center font-bold">
                        ${u.username[0].toUpperCase()}
                    </div>
                    <div>
                        <div class="font-semibold text-slate-800">${escapeHtml(u.full_name || u.username)}</div>
                        <div class="text-sm text-slate-500">@${escapeHtml(u.username)}</div>
                    </div>
                </div>
            </td>

            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${canonicalRole(u.role) === 'admin' ? 'bg-gold-100 text-gold-700' : (canonicalRole(u.role) === 'manager' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700')}">
                    ${getRoleLabel(u.role)}
                </span>
            </td>
            <td class="px-6 py-4">
                <span class="flex items-center gap-1.5 ${u.is_active ? 'text-green-600' : 'text-slate-400'}">
                    <span class="w-2 h-2 rounded-full ${u.is_active ? 'bg-green-500' : 'bg-slate-300'}"></span>
                    ${u.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-slate-500">${u.last_login ? formatDate(u.last_login) : 'Never'}</td>
            <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editUser(${u.id})" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg" title="Edit">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    ${u.id !== currentUserId ? `
                        <button onclick="deleteUser(${u.id}, '${escapeHtml(u.username)}')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg" title="Delete">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    ` : `
                        <span class="p-2 text-slate-300 cursor-not-allowed" title="Cannot delete yourself">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </span>
                    `}
                </div>
            </td>
        </tr>
    `).join('');
}

function filterUsers() {
    renderUsers();
}

function openModal(userId = null) {
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('modalTitle').textContent = 'Add User';
    document.getElementById('password').required = true;
    document.getElementById('pwdRequired').classList.remove('hidden');
    document.getElementById('pwdOptional').classList.add('hidden');
    document.getElementById('userModal').classList.remove('hidden');
}

async function editUser(id) {
    const data = await api(`get_user_by_id&id=${id}`);
    if (!data.success) {
        toast(data.message || 'Failed to load user', 'error');
        return;
    }
    
    const u = data.data;
    document.getElementById('userId').value = u.id;
    document.getElementById('username').value = u.username;
    document.getElementById('fullName').value = u.full_name || '';

    document.getElementById('password').value = '';
    document.getElementById('role').value = u.role;
    document.getElementById('isActive').checked = u.is_active == 1;
    
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('password').required = false;
    document.getElementById('pwdRequired').classList.add('hidden');
    document.getElementById('pwdOptional').classList.remove('hidden');
    document.getElementById('userModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
}

document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const id = document.getElementById('userId').value;
    const formData = {
        id: id || null,
        username: document.getElementById('username').value.trim(),
        full_name: document.getElementById('fullName').value.trim(),

        role: document.getElementById('role').value,
        is_active: document.getElementById('isActive').checked
    };
    
    const password = document.getElementById('password').value;
    if (password) formData.password = password;
    
    if (!id && !password) {
        toast('Password is required for new users', 'error');
        return;
    }
    
    const data = await api('save_user', 'POST', formData);
    if (data.success) {
        toast(id ? 'User updated!' : 'User created!', 'success');
        closeModal();
        loadUsers();
    } else {
        toast(data.message || 'Error saving user', 'error');
    }
});

function deleteUser(id, username) {
    deleteUserId = id;
    document.getElementById('deleteUserName').textContent = `@${username}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteUserId = null;
}

async function confirmDelete() {
    if (!deleteUserId) return;
    
    const data = await api(`delete_user&id=${deleteUserId}`);
    if (data.success) {
        toast('User deleted', 'success');
        closeDeleteModal();
        loadUsers();
    } else {
        toast(data.message || 'Error deleting user', 'error');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function formatDate(str) {
    return str ? new Date(str).toLocaleString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit' 
    }) : '';
}

function toast(msg, type = 'info') {
    const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' };
    const t = document.createElement('div');
    t.className = `${colors[type]} text-white px-4 py-2 rounded-lg shadow-lg text-sm`;
    t.textContent = msg;
    document.getElementById('toasts').appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

loadUsers();
</script>
<?php include 'includes/sidebar_footer.php'; ?>
</body>
</html>
