<?php
/**
 * BroMan Social API Handler
 * 
 * Handles all AJAX requests from the frontend
 * Returns JSON responses for SPA functionality
 */

require_once 'config.php';
require_once 'db.php';

// Start session
session_name(SESSION_NAME);
session_start();

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . BASE_URL);
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Response helper
function sendResponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

// Check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Get current user from session
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    $sql = "SELECT id, username, email, role FROM users WHERE id = ?";
    return fetchOne($sql, [$_SESSION['user_id']]);
}

// Require authentication
function requireAuth() {
    if (!isAuthenticated()) {
        sendResponse(false, null, 'Authentication required', 401);
    }
}

// Hash password
function hashPassword($password) {
    return password_hash($password, HASH_ALGO);
}

// ===== API ENDPOINTS =====

try {
    switch ($action) {
        
        // --- Authentication ---
        
        case 'login':
            $input = json_decode(file_get_contents('php://input'), true);
            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                sendResponse(false, null, 'Username and password required', 400);
            }
            
            $sql = "SELECT id, username, email, role, password_hash FROM users WHERE username = ?";
            $user = fetchOne($sql, [$username]);
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                sendResponse(false, null, 'Invalid credentials', 401);
            }
            
            // Create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Generate session token for additional security
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
            
            $sql = "INSERT INTO sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)";
            executeQuery($sql, [$user['id'], $token, $expires]);
            
            $_SESSION['session_token'] = $token;
            
            // Return user data (without password)
            unset($user['password_hash']);
            sendResponse(true, $user, 'Login successful');
            break;
        
        case 'logout':
            requireAuth();
            
            // Delete session from database
            if (isset($_SESSION['session_token'])) {
                $sql = "DELETE FROM sessions WHERE session_token = ?";
                executeQuery($sql, [$_SESSION['session_token']]);
            }
            
            session_destroy();
            sendResponse(true, null, 'Logged out successfully');
            break;
        
        case 'get_user':
            requireAuth();
            $user = getCurrentUser();
            sendResponse(true, $user);
            break;
        
        // --- Posts Management ---
        
        case 'fetch_posts':
            requireAuth();
            $user = getCurrentUser();
            
            // Build query with filters
            $where = [];
            $params = [];
            
            // Filter by status
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $where[] = "p.status = ?";
                $params[] = $_GET['status'];
            }
            
            // Filter by platform
            if (isset($_GET['platform']) && !empty($_GET['platform'])) {
                $where[] = "p.platform = ?";
                $params[] = $_GET['platform'];
            }
            
            // Filter by author (show only my posts)
            if (isset($_GET['my_posts']) && $_GET['my_posts'] === 'true') {
                $where[] = "p.author_id = ?";
                $params[] = $user['id'];
            }
            
            // Filter by urgency
            if (isset($_GET['urgent']) && $_GET['urgent'] === 'true') {
                $where[] = "p.urgency = 1";
            }
            
            // Filter by week (for calendar view)
            if (isset($_GET['week_start']) && isset($_GET['week_end'])) {
                $where[] = "p.scheduled_date >= ? AND p.scheduled_date <= ?";
                $params[] = $_GET['week_start'];
                $params[] = $_GET['week_end'];
            }
            
            // Search by title or content
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $where[] = "(p.title LIKE ? OR p.content LIKE ?)";
                $searchTerm = '%' . $_GET['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT p.*, 
                           u.username as author_name,
                           r.username as reviewer_name
                    FROM posts p
                    LEFT JOIN users u ON p.author_id = u.id
                    LEFT JOIN users r ON p.reviewer_id = r.id
                    $whereClause
                    ORDER BY p.urgency DESC, p.scheduled_date ASC, p.created_at DESC";
            
            $posts = fetchAll($sql, $params);
            sendResponse(true, $posts);
            break;
        
        case 'get_post':
            requireAuth();
            $id = $_GET['id'] ?? 0;
            
            if (!$id) {
                sendResponse(false, null, 'Post ID required', 400);
            }
            
            $sql = "SELECT p.*, 
                           u.username as author_name,
                           r.username as reviewer_name
                    FROM posts p
                    LEFT JOIN users u ON p.author_id = u.id
                    LEFT JOIN users r ON p.reviewer_id = r.id
                    WHERE p.id = ?";
            
            $post = fetchOne($sql, [$id]);
            
            if (!$post) {
                sendResponse(false, null, 'Post not found', 404);
            }
            
            sendResponse(true, $post);
            break;
        
        case 'save_post':
            requireAuth();
            $user = getCurrentUser();
            $input = json_decode(file_get_contents('php://input'), true);
            
            $id = $input['id'] ?? null;
            $title = trim($input['title'] ?? '');
            $content = trim($input['content'] ?? '');
            $platform = $input['platform'] ?? '';
            $status = $input['status'] ?? 'DRAFT';
            $urgency = isset($input['urgency']) ? (bool)$input['urgency'] : false;
            $scheduled_date = $input['scheduled_date'] ?? null;
            $image_url = trim($input['image_url'] ?? '');
            
            // Validation
            if (empty($title)) {
                sendResponse(false, null, 'Title is required', 400);
            }
            if (empty($content)) {
                sendResponse(false, null, 'Content is required', 400);
            }
            if (empty($platform)) {
                sendResponse(false, null, 'Platform is required', 400);
            }
            
            // Update existing post
            if ($id) {
                // Check permissions - creators can only edit their own posts
                $existingPost = fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
                
                if (!$existingPost) {
                    sendResponse(false, null, 'Post not found', 404);
                }
                
                if ($user['role'] === 'creator' && $existingPost['author_id'] != $user['id']) {
                    sendResponse(false, null, 'You can only edit your own posts', 403);
                }
                
                $sql = "UPDATE posts SET 
                        title = ?, content = ?, platform = ?, status = ?,
                        urgency = ?, scheduled_date = ?, image_url = ?
                        WHERE id = ?";
                
                executeQuery($sql, [$title, $content, $platform, $status, $urgency, $scheduled_date, $image_url, $id]);
                
                sendResponse(true, ['id' => $id], 'Post updated successfully');
            }
            // Create new post
            else {
                $sql = "INSERT INTO posts (title, content, platform, status, urgency, author_id, scheduled_date, image_url)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                executeQuery($sql, [$title, $content, $platform, $status, $urgency, $user['id'], $scheduled_date, $image_url]);
                
                $newId = lastInsertId();
                sendResponse(true, ['id' => $newId], 'Post created successfully', 201);
            }
            break;
        
        case 'update_status':
            requireAuth();
            $user = getCurrentUser();
            $input = json_decode(file_get_contents('php://input'), true);
            
            $id = $input['id'] ?? 0;
            $status = $input['status'] ?? '';
            $rejected_reason = $input['rejected_reason'] ?? null;
            
            if (!$id) {
                sendResponse(false, null, 'Post ID required', 400);
            }
            
            $post = fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
            
            if (!$post) {
                sendResponse(false, null, 'Post not found', 404);
            }
            
            // Permission check for status changes
            if ($status === 'APPROVED' || $status === 'REJECTED') {
                if ($user['role'] !== 'approver') {
                    sendResponse(false, null, 'Only approvers can approve/reject posts', 403);
                }
                
                // Set reviewer_id when approving/rejecting
                $sql = "UPDATE posts SET status = ?, rejected_reason = ?, reviewer_id = ? WHERE id = ?";
                executeQuery($sql, [$status, $rejected_reason, $user['id'], $id]);
            } else {
                $sql = "UPDATE posts SET status = ?, rejected_reason = ? WHERE id = ?";
                executeQuery($sql, [$status, $rejected_reason, $id]);
            }
            
            sendResponse(true, ['id' => $id, 'status' => $status], 'Status updated successfully');
            break;
        
        case 'delete_post':
            requireAuth();
            $user = getCurrentUser();
            $id = $_GET['id'] ?? 0;
            
            if (!$id) {
                sendResponse(false, null, 'Post ID required', 400);
            }
            
            $post = fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
            
            if (!$post) {
                sendResponse(false, null, 'Post not found', 404);
            }
            
            // Only creators can delete their own posts, or approvers can delete any
            if ($user['role'] === 'creator' && $post['author_id'] != $user['id']) {
                sendResponse(false, null, 'You can only delete your own posts', 403);
            }
            
            $sql = "DELETE FROM posts WHERE id = ?";
            executeQuery($sql, [$id]);
            
            sendResponse(true, null, 'Post deleted successfully');
            break;
        
        // --- Helper Endpoints ---
        
        case 'get_users':
            requireAuth();
            // Get all users for filtering purposes
            $sql = "SELECT id, username, role FROM users ORDER BY username";
            $users = fetchAll($sql);
            sendResponse(true, $users);
            break;
        
        default:
            sendResponse(false, null, 'Invalid action', 400);
    }
    
} catch (Exception $e) {
    // Log error in production
    error_log("API Error: " . $e->getMessage());
    
    if (ENVIRONMENT === 'development') {
        sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
    } else {
        sendResponse(false, null, 'An error occurred. Please try again.', 500);
    }
}
