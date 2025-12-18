<?php
/**
 * BroMan Social API Handler v3.0
 * Simplified 2-Role Workflow System
 */

require_once 'config.php';
require_once 'db.php';

session_name(SESSION_NAME);
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . BASE_URL);
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? '';

// ===== HELPERS =====

function sendResponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function getCurrentUser() {
    if (!isAuthenticated()) return null;
    return fetchOne("SELECT id, username, email, full_name, role, avatar_url FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function requireAuth() {
    if (!isAuthenticated()) sendResponse(false, null, 'Authentication required', 401);
}

function requireAdmin() {
    requireAuth();
    $user = getCurrentUser();
    if ($user['role'] !== 'admin') sendResponse(false, null, 'Admin access required', 403);
}

function logActivity($postId, $userId, $action, $oldValue = null, $newValue = null, $desc = null) {
    executeQuery("INSERT INTO activity_log (post_id, user_id, action, old_value, new_value, description) VALUES (?, ?, ?, ?, ?, ?)",
        [$postId, $userId, $action, $oldValue, $newValue, $desc]);
}

function notify($userId, $type, $title, $message, $postId = null, $triggeredBy = null) {
    executeQuery("INSERT INTO notifications (user_id, type, title, message, post_id, triggered_by) VALUES (?, ?, ?, ?, ?, ?)",
        [$userId, $type, $title, $message, $postId, $triggeredBy]);
}

// Upload config
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm']);
define('MAX_FILE_SIZE', 10 * 1024 * 1024);

// ===== API ENDPOINTS =====

try {
    switch ($action) {
        
        // ===== AUTH =====
        
        case 'login':
            $input = json_decode(file_get_contents('php://input'), true);
            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';
            
            if (empty($username) || empty($password)) sendResponse(false, null, 'Username and password required', 400);
            
            $user = fetchOne("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                sendResponse(false, null, 'Invalid credentials', 401);
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            executeQuery("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
            
            unset($user['password_hash']);
            sendResponse(true, $user, 'Login successful');
            break;
        
        case 'logout':
            session_destroy();
            sendResponse(true, null, 'Logged out');
            break;
        
        case 'get_user':
            requireAuth();
            sendResponse(true, getCurrentUser());
            break;
        
        // ===== DASHBOARD =====
        
        case 'get_dashboard_stats':
            requireAuth();
            $user = getCurrentUser();
            $stats = [];
            
            $stats['total_posts'] = fetchOne("SELECT COUNT(*) as c FROM posts")['c'];
            $stats['ideas'] = fetchOne("SELECT COUNT(*) as c FROM posts WHERE status = 'IDEA'")['c'];
            $stats['drafts'] = fetchOne("SELECT COUNT(*) as c FROM posts WHERE status IN ('DRAFT', 'CHANGES_REQUESTED')")['c'];
            $stats['pending'] = fetchOne("SELECT COUNT(*) as c FROM posts WHERE status = 'PENDING_REVIEW'")['c'];
            $stats['approved'] = fetchOne("SELECT COUNT(*) as c FROM posts WHERE status = 'APPROVED'")['c'];
            
            if ($user['role'] === 'staff') {
                $stats['my_posts'] = fetchOne("SELECT COUNT(*) as c FROM posts WHERE author_id = ?", [$user['id']])['c'];
            }
            
            $stats['recent_activity'] = fetchAll(
                "SELECT al.*, u.username, p.title as post_title FROM activity_log al 
                 JOIN users u ON al.user_id = u.id JOIN posts p ON al.post_id = p.id 
                 ORDER BY al.created_at DESC LIMIT 10"
            );
            
            sendResponse(true, $stats);
            break;
        
        // ===== POSTS =====
        
        case 'fetch_posts':
            requireAuth();
            $user = getCurrentUser();
            
            $where = [];
            $params = [];
            
            if (!empty($_GET['status'])) {
                $where[] = "p.status = ?";
                $params[] = $_GET['status'];
            }
            if (!empty($_GET['platform'])) {
                $where[] = "p.platform = ?";
                $params[] = $_GET['platform'];
            }
            if (!empty($_GET['my_posts']) && $_GET['my_posts'] === 'true') {
                $where[] = "p.author_id = ?";
                $params[] = $user['id'];
            }
            if (!empty($_GET['search'])) {
                $where[] = "(p.title LIKE ? OR p.content LIKE ?)";
                $term = '%' . $_GET['search'] . '%';
                $params[] = $term;
                $params[] = $term;
            }
            
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT p.*, u.username as author_name, u.full_name as author_full_name,
                           (SELECT file_path FROM media_files WHERE post_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
                    FROM posts p
                    LEFT JOIN users u ON p.author_id = u.id
                    $whereClause
                    ORDER BY p.urgency DESC, p.updated_at DESC";
            
            sendResponse(true, fetchAll($sql, $params));
            break;
        
        case 'get_post':
            requireAuth();
            $id = $_GET['id'] ?? 0;
            if (!$id) sendResponse(false, null, 'Post ID required', 400);
            
            $post = fetchOne(
                "SELECT p.*, u.username as author_name, u.full_name as author_full_name,
                        cr.username as change_requested_by_name
                 FROM posts p
                 LEFT JOIN users u ON p.author_id = u.id
                 LEFT JOIN users cr ON p.change_requested_by = cr.id
                 WHERE p.id = ?", [$id]
            );
            
            if (!$post) sendResponse(false, null, 'Post not found', 404);
            
            $post['media'] = fetchAll("SELECT * FROM media_files WHERE post_id = ?", [$id]);
            $post['comments'] = fetchAll(
                "SELECT c.*, u.username, u.full_name FROM comments c 
                 JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at", [$id]
            );
            $post['activity'] = fetchAll(
                "SELECT al.*, u.username FROM activity_log al 
                 JOIN users u ON al.user_id = u.id WHERE al.post_id = ? ORDER BY al.created_at DESC LIMIT 20", [$id]
            );
            
            sendResponse(true, $post);
            break;
        
        case 'save_post':
            requireAuth();
            $user = getCurrentUser();
            
            // Handle both multipart/form-data (with files) and JSON
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                // Multipart form data (supports file upload)
                $id = $_POST['id'] ?? null;
                $title = trim($_POST['title'] ?? '');
                $content = trim($_POST['content'] ?? '');
                $platform = $_POST['platform'] ?? '';
                $status = $_POST['status'] ?? 'DRAFT';
                $urgency = !empty($_POST['urgency']);
                $priority = $_POST['priority'] ?? 'normal';
                $scheduled_date = $_POST['scheduled_date'] ?? null;
            } else {
                // JSON request (backwards compatible)
                $input = json_decode(file_get_contents('php://input'), true);
                $id = $input['id'] ?? null;
                $title = trim($input['title'] ?? '');
                $content = trim($input['content'] ?? '');
                $platform = $input['platform'] ?? '';
                $status = $input['status'] ?? 'DRAFT';
                $urgency = !empty($input['urgency']);
                $priority = $input['priority'] ?? 'normal';
                $scheduled_date = $input['scheduled_date'] ?? null;
            }
            
            if (empty($title)) sendResponse(false, null, 'Title required', 400);
            if (empty($content)) sendResponse(false, null, 'Content required', 400);
            if (empty($platform)) sendResponse(false, null, 'Platform required', 400);
            
            // Staff can only create IDEA or DRAFT
            if ($user['role'] === 'staff' && !in_array($status, ['IDEA', 'DRAFT'])) {
                $status = 'DRAFT';
            }
            
            $postId = null;
            
            if ($id) {
                $existing = fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
                if (!$existing) sendResponse(false, null, 'Post not found', 404);
                
                // Staff can only edit own posts in DRAFT/CHANGES_REQUESTED
                if ($user['role'] === 'staff') {
                    if ($existing['author_id'] != $user['id']) {
                        sendResponse(false, null, 'Cannot edit others posts', 403);
                    }
                    if (!in_array($existing['status'], ['DRAFT', 'CHANGES_REQUESTED', 'IDEA'])) {
                        sendResponse(false, null, 'Cannot edit post in current status', 403);
                    }
                }
                
                executeQuery(
                    "UPDATE posts SET title=?, content=?, platform=?, urgency=?, priority=?, scheduled_date=? WHERE id=?",
                    [$title, $content, $platform, $urgency, $priority, $scheduled_date, $id]
                );
                logActivity($id, $user['id'], 'updated', null, null, 'Content updated');
                $postId = $id;
            } else {
                executeQuery(
                    "INSERT INTO posts (title, content, platform, status, urgency, priority, author_id, scheduled_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$title, $content, $platform, $status, $urgency, $priority, $user['id'], $scheduled_date]
                );
                $postId = lastInsertId();
                logActivity($postId, $user['id'], 'created', null, null, "Created as $status");
            }
            
            // Handle file upload if present
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file'];
                $mime = mime_content_type($file['tmp_name']);
                
                if (in_array($mime, ALLOWED_TYPES) && $file['size'] <= MAX_FILE_SIZE) {
                    $year = date('Y');
                    $month = date('m');
                    $uploadPath = UPLOAD_DIR . "$year/$month/";
                    if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);
                    
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $uniqueName = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $fullPath = $uploadPath . $uniqueName;
                    $relativePath = "uploads/$year/$month/$uniqueName";
                    
                    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                        $isImage = str_starts_with($mime, 'image/');
                        $isPrimary = fetchOne("SELECT COUNT(*) as c FROM media_files WHERE post_id = ?", [$postId])['c'] == 0;
                        
                        executeQuery(
                            "INSERT INTO media_files (post_id, original_name, file_name, file_path, file_type, mime_type, file_size, is_primary, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [$postId, $file['name'], $uniqueName, $relativePath, $isImage ? 'image' : 'video', $mime, $file['size'], $isPrimary, $user['id']]
                        );
                        logActivity($postId, $user['id'], 'media_uploaded', null, null, 'Media uploaded');
                    }
                }
            }
            
            sendResponse(true, ['id' => $postId], $id ? 'Post updated' : 'Post created', $id ? 200 : 201);
            break;

        
        case 'update_status':
            requireAuth();
            $user = getCurrentUser();
            $input = json_decode(file_get_contents('php://input'), true);
            
            $id = $input['id'] ?? 0;
            $newStatus = $input['status'] ?? '';
            $reason = $input['reason'] ?? null;
            
            if (!$id || !$newStatus) sendResponse(false, null, 'ID and status required', 400);
            
            $post = fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
            if (!$post) sendResponse(false, null, 'Post not found', 404);
            
            $oldStatus = $post['status'];
            $isAdmin = $user['role'] === 'admin';
            $isOwner = $post['author_id'] == $user['id'];
            
            // Validate transitions
            $allowed = false;
            
            // IDEA -> DRAFT (Admin approves idea)
            if ($oldStatus === 'IDEA' && $newStatus === 'DRAFT' && $isAdmin) $allowed = true;
            
            // DRAFT -> PENDING_REVIEW (Owner submits)
            if ($oldStatus === 'DRAFT' && $newStatus === 'PENDING_REVIEW' && ($isOwner || $isAdmin)) $allowed = true;
            
            // CHANGES_REQUESTED -> PENDING_REVIEW (Owner resubmits)
            if ($oldStatus === 'CHANGES_REQUESTED' && $newStatus === 'PENDING_REVIEW' && ($isOwner || $isAdmin)) $allowed = true;
            
            // PENDING_REVIEW -> APPROVED (Admin approves)
            if ($oldStatus === 'PENDING_REVIEW' && $newStatus === 'APPROVED' && $isAdmin) $allowed = true;
            
            // PENDING_REVIEW -> CHANGES_REQUESTED (Admin requests changes)
            if ($oldStatus === 'PENDING_REVIEW' && $newStatus === 'CHANGES_REQUESTED' && $isAdmin) $allowed = true;
            
            // CHANGES_REQUESTED -> DRAFT (internal, same as just editing)
            if ($oldStatus === 'CHANGES_REQUESTED' && $newStatus === 'DRAFT' && ($isOwner || $isAdmin)) $allowed = true;
            
            if (!$allowed) {
                sendResponse(false, null, "Cannot change from $oldStatus to $newStatus", 403);
            }
            
            $updateSql = "UPDATE posts SET status = ?, reviewer_id = ?";
            $updateParams = [$newStatus, $user['id']];
            
            if ($newStatus === 'CHANGES_REQUESTED') {
                $updateSql .= ", change_request_reason = ?, change_requested_by = ?, change_requested_at = NOW()";
                $updateParams[] = $reason;
                $updateParams[] = $user['id'];
            }
            
            $updateSql .= " WHERE id = ?";
            $updateParams[] = $id;
            
            executeQuery($updateSql, $updateParams);
            logActivity($id, $user['id'], 'status_changed', $oldStatus, $newStatus, $reason);
            
            // Notifications
            if ($newStatus === 'PENDING_REVIEW') {
                $admins = fetchAll("SELECT id FROM users WHERE role = 'admin'");
                foreach ($admins as $admin) {
                    notify($admin['id'], 'review_needed', 'Review Needed', "Post '{$post['title']}' needs review", $id, $user['id']);
                }
            } elseif ($newStatus === 'APPROVED') {
                notify($post['author_id'], 'approved', 'Post Approved', "Your post '{$post['title']}' was approved!", $id, $user['id']);
            } elseif ($newStatus === 'CHANGES_REQUESTED') {
                notify($post['author_id'], 'changes_requested', 'Changes Requested', "Changes requested on '{$post['title']}': $reason", $id, $user['id']);
            }
            
            sendResponse(true, ['id' => $id, 'status' => $newStatus], 'Status updated');
            break;
        
        case 'delete_post':
            requireAuth();
            $user = getCurrentUser();
            $id = $_GET['id'] ?? 0;
            
            if (!$id) sendResponse(false, null, 'ID required', 400);
            
            $post = fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
            if (!$post) sendResponse(false, null, 'Not found', 404);
            
            // Staff can only delete own drafts/ideas
            if ($user['role'] === 'staff') {
                if ($post['author_id'] != $user['id']) sendResponse(false, null, 'Cannot delete', 403);
                if (!in_array($post['status'], ['IDEA', 'DRAFT'])) sendResponse(false, null, 'Cannot delete submitted post', 403);
            }
            
            // Delete media files
            $media = fetchAll("SELECT file_path FROM media_files WHERE post_id = ?", [$id]);
            foreach ($media as $m) {
                $path = __DIR__ . '/' . $m['file_path'];
                if (file_exists($path)) unlink($path);
            }
            
            executeQuery("DELETE FROM posts WHERE id = ?", [$id]);
            sendResponse(true, null, 'Post deleted');
            break;
        
        // ===== MEDIA =====
        
        case 'upload_media':
            requireAuth();
            $user = getCurrentUser();
            $postId = $_POST['post_id'] ?? 0;
            
            if (!$postId) sendResponse(false, null, 'Post ID required', 400);
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                sendResponse(false, null, 'Upload error', 400);
            }
            
            $file = $_FILES['file'];
            $mime = mime_content_type($file['tmp_name']);
            
            if (!in_array($mime, ALLOWED_TYPES)) sendResponse(false, null, 'Invalid file type', 400);
            if ($file['size'] > MAX_FILE_SIZE) sendResponse(false, null, 'File too large (max 10MB)', 400);
            
            $year = date('Y');
            $month = date('m');
            $uploadPath = UPLOAD_DIR . "$year/$month/";
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueName = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $fullPath = $uploadPath . $uniqueName;
            $relativePath = "uploads/$year/$month/$uniqueName";
            
            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                sendResponse(false, null, 'Failed to save', 500);
            }
            
            $isImage = str_starts_with($mime, 'image/');
            $isPrimary = fetchOne("SELECT COUNT(*) as c FROM media_files WHERE post_id = ?", [$postId])['c'] == 0;
            
            executeQuery(
                "INSERT INTO media_files (post_id, original_name, file_name, file_path, file_type, mime_type, file_size, is_primary, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$postId, $file['name'], $uniqueName, $relativePath, $isImage ? 'image' : 'video', $mime, $file['size'], $isPrimary, $user['id']]
            );
            
            sendResponse(true, ['id' => lastInsertId(), 'file_path' => $relativePath], 'Uploaded');
            break;
        
        case 'delete_media':
            requireAuth();
            $id = $_GET['id'] ?? 0;
            if (!$id) sendResponse(false, null, 'ID required', 400);
            
            $media = fetchOne("SELECT * FROM media_files WHERE id = ?", [$id]);
            if (!$media) sendResponse(false, null, 'Not found', 404);
            
            $path = __DIR__ . '/' . $media['file_path'];
            if (file_exists($path)) unlink($path);
            
            executeQuery("DELETE FROM media_files WHERE id = ?", [$id]);
            sendResponse(true, null, 'Deleted');
            break;
        
        // ===== COMMENTS =====
        
        case 'add_comment':
            requireAuth();
            $user = getCurrentUser();
            $input = json_decode(file_get_contents('php://input'), true);
            
            $postId = $input['post_id'] ?? 0;
            $content = trim($input['content'] ?? '');
            
            if (!$postId || !$content) sendResponse(false, null, 'Post ID and content required', 400);
            
            executeQuery("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)", [$postId, $user['id'], $content]);
            logActivity($postId, $user['id'], 'comment_added');
            
            $comment = fetchOne(
                "SELECT c.*, u.username, u.full_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?",
                [lastInsertId()]
            );
            
            sendResponse(true, $comment, 'Comment added');
            break;
        
        // ===== NOTIFICATIONS =====
        
        case 'get_notifications':
            requireAuth();
            $user = getCurrentUser();
            
            $notifs = fetchAll(
                "SELECT n.*, p.title as post_title FROM notifications n 
                 LEFT JOIN posts p ON n.post_id = p.id 
                 WHERE n.user_id = ? ORDER BY n.created_at DESC LIMIT 30",
                [$user['id']]
            );
            $unread = fetchOne("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0", [$user['id']])['c'];
            
            sendResponse(true, ['notifications' => $notifs, 'unread_count' => $unread]);
            break;
        
        case 'mark_notification_read':
            requireAuth();
            $user = getCurrentUser();
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!empty($input['mark_all'])) {
                executeQuery("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$user['id']]);
            } elseif (!empty($input['id'])) {
                executeQuery("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$input['id'], $user['id']]);
            }
            sendResponse(true, null, 'Marked as read');
            break;
        
        // ===== USER MANAGEMENT (Admin Only) =====
        
        case 'get_users':
            requireAdmin();
            $users = fetchAll("SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC");
            sendResponse(true, $users);
            break;
        
        case 'get_user_by_id':
            requireAdmin();
            $id = $_GET['id'] ?? 0;
            if (!$id) sendResponse(false, null, 'ID required', 400);
            
            $u = fetchOne("SELECT id, username, email, full_name, role, is_active, avatar_url FROM users WHERE id = ?", [$id]);
            if (!$u) sendResponse(false, null, 'User not found', 404);
            sendResponse(true, $u);
            break;
        
        case 'save_user':
            requireAdmin();
            $input = json_decode(file_get_contents('php://input'), true);
            
            $id = $input['id'] ?? null;
            $username = trim($input['username'] ?? '');
            $email = trim($input['email'] ?? '');
            $fullName = trim($input['full_name'] ?? '');
            $role = $input['role'] ?? 'staff';
            $password = $input['password'] ?? '';
            $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : true;
            
            if (empty($username)) sendResponse(false, null, 'Username required', 400);
            if (empty($email)) sendResponse(false, null, 'Email required', 400);
            if (!in_array($role, ['admin', 'staff'])) $role = 'staff';
            
            if ($id) {
                // Update
                $existing = fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
                if (!$existing) sendResponse(false, null, 'User not found', 404);
                
                // Check unique
                $dup = fetchOne("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?", [$username, $email, $id]);
                if ($dup) sendResponse(false, null, 'Username or email already exists', 400);
                
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    executeQuery("UPDATE users SET username=?, email=?, full_name=?, role=?, password_hash=?, is_active=? WHERE id=?",
                        [$username, $email, $fullName, $role, $hash, $isActive, $id]);
                } else {
                    executeQuery("UPDATE users SET username=?, email=?, full_name=?, role=?, is_active=? WHERE id=?",
                        [$username, $email, $fullName, $role, $isActive, $id]);
                }
                sendResponse(true, ['id' => $id], 'User updated');
            } else {
                // Create
                if (empty($password)) sendResponse(false, null, 'Password required for new user', 400);
                
                $dup = fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
                if ($dup) sendResponse(false, null, 'Username or email already exists', 400);
                
                $hash = password_hash($password, PASSWORD_DEFAULT);
                executeQuery("INSERT INTO users (username, email, full_name, role, password_hash, is_active) VALUES (?, ?, ?, ?, ?, ?)",
                    [$username, $email, $fullName, $role, $hash, $isActive]);
                sendResponse(true, ['id' => lastInsertId()], 'User created', 201);
            }
            break;
        
        case 'delete_user':
            requireAdmin();
            $id = $_GET['id'] ?? 0;
            if (!$id) sendResponse(false, null, 'ID required', 400);
            
            $user = getCurrentUser();
            if ($id == $user['id']) sendResponse(false, null, 'Cannot delete yourself', 400);
            
            executeQuery("DELETE FROM users WHERE id = ?", [$id]);
            sendResponse(true, null, 'User deleted');
            break;
        
        default:
            sendResponse(false, null, 'Invalid action', 400);
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendResponse(false, null, ENVIRONMENT === 'development' ? $e->getMessage() : 'Server error', 500);
}
