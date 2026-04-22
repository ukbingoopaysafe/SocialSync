<?php
/**
 * BroMan Social API Handler v3.0
 * Simplified 2-Role Workflow System
 */

require_once 'config.php';
require_once 'db.php';
require_once 'includes/security.php';
require_once 'includes/WorkflowManager.php';

session_name(SESSION_NAME);
session_start();

$isDirectApiRequest = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__;

if ($isDirectApiRequest) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: ' . BASE_URL);
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN');
    header('Access-Control-Allow-Credentials: true');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

$action = $isDirectApiRequest ? ($_GET['action'] ?? '') : '';

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
    return fetchOne("SELECT id, username, full_name, role, avatar_url FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function requireAuth() {
    if (!isAuthenticated()) sendResponse(false, null, 'Authentication required', 401);
}

function requireAdmin() {
    requireAuth();
    $user = getCurrentUser();
    if (!in_array($user['role'], ['admin', 'manager'])) sendResponse(false, null, 'Admin access required', 403);
}

function requireManager() {
    requireAuth();
    $user = getCurrentUser();
    if ($user['role'] !== 'manager') sendResponse(false, null, 'Manager access required', 403);
}

function logActivity($postId, $userId, $action, $oldValue = null, $newValue = null, $desc = null) {
    executeQuery("INSERT INTO activity_log (post_id, user_id, action, old_value, new_value, description) VALUES (?, ?, ?, ?, ?, ?)",
        [$postId, $userId, $action, $oldValue, $newValue, $desc]);
}

function getNotificationCompanyId($postId = null) {
    if (!empty($postId)) {
        $post = fetchOne("SELECT company_id FROM posts WHERE id = ?", [$postId]);
        if (!empty($post['company_id'])) {
            return (int)$post['company_id'];
        }
    }

    if (!empty($_SESSION['company_id'])) {
        return (int)$_SESSION['company_id'];
    }

    return null;
}

function getWorkspaceAliasValue($userId, $companyId) {
    return (string)$userId . ':' . (string)$companyId;
}

function notify($userId, $type, $title, $message, $postId = null, $triggeredBy = null) {
    // 1. Store notification in database (existing logic)
    executeQuery("INSERT INTO notifications (user_id, type, title, message, post_id, triggered_by) VALUES (?, ?, ?, ?, ?, ?)",
        [$userId, $type, $title, $message, $postId, $triggeredBy]);

    // 2. Send push notification via OneSignal
    if (defined('ONESIGNAL_APP_ID') && defined('ONESIGNAL_REST_KEY') && ONESIGNAL_APP_ID && ONESIGNAL_REST_KEY) {
        try {
            $companyId = getNotificationCompanyId($postId);
            $payload = [
                'app_id'          => ONESIGNAL_APP_ID,
                'target_channel'  => 'push',
                'headings'        => ['en' => $title],
                'contents'        => ['en' => $message],
            ];

            if ($companyId) {
                $payload['include_aliases'] = [
                    'workspace_user' => [getWorkspaceAliasValue($userId, $companyId)]
                ];
            } else {
                $payload['include_aliases'] = [
                    'external_id' => [strval($userId)]
                ];
            }

            $ch = curl_init('https://api.onesignal.com/notifications');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json; charset=utf-8',
                    'Authorization: Key ' . ONESIGNAL_REST_KEY,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 3,        // Hard timeout – never block API
                CURLOPT_CONNECTTIMEOUT => 2,
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            // Silently fail – push is best-effort, DB notification already saved
        }
    }
}

// Upload config
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm']);
define('MAX_FILE_SIZE', 100 * 1024 * 1024);

// ===== API ENDPOINTS =====

if ($isDirectApiRequest) {
try {
    switch ($action) {
        
        // ===== AUTH =====
        
        case 'login':
            // Rate limiting for login to prevent brute force
            enforceRateLimit('login');
            
            $input = json_decode(file_get_contents('php://input'), true);
            $username = sanitizeString(trim($input['username'] ?? ''), 50);
            $password = $input['password'] ?? '';
            $companyId = (int)($input['company_id'] ?? 1); // Default to BroMan
            
            if (empty($username) || empty($password)) sendResponse(false, null, 'Username and password required', 400);
            
            $user = fetchOne("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                sendResponse(false, null, 'Invalid credentials', 401);
            }
            
            // Validate company exists
            $company = fetchOne("SELECT * FROM companies WHERE id = ?", [$companyId]);
            if (!$company) {
                $companyId = 1; // Fallback to default
                $company = fetchOne("SELECT * FROM companies WHERE id = 1");
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['company_id'] = $companyId;
            $_SESSION['company_name'] = $company['name'] ?? 'BroMan';
            $_SESSION['company_logo'] = $company['logo_url'] ?? 'images/Final_Logo White.png';
            
            executeQuery("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
            
            unset($user['password_hash']);
            $user['company'] = $company;
            sendResponse(true, $user, 'Login successful');
            break;
        
        case 'logout':
            session_destroy();
            sendResponse(true, null, 'Logged out');
            break;
        
        case 'get_user':
            requireAuth();
            $user = getCurrentUser();
            // Add company info
            $user['company_id'] = $_SESSION['company_id'] ?? 1;
            $user['company_name'] = $_SESSION['company_name'] ?? 'BroMan';
            $user['company_logo'] = $_SESSION['company_logo'] ?? 'images/Final_Logo White.png';
            sendResponse(true, $user);
            break;
        
        case 'get_companies':
            // Public endpoint - no auth required for login page
            $companies = fetchAll("SELECT id, name, slug, logo_url, primary_color FROM companies ORDER BY id");
            if (empty($companies)) {
                // Fallback if table doesn't exist yet - use colored logos for visibility
                $companies = [
                    ['id' => 1, 'name' => 'BroMan', 'slug' => 'broman', 'logo_url' => 'images/Final_Logo.png', 'primary_color' => '#1e3a5f'],
                    ['id' => 2, 'name' => 'Cible', 'slug' => 'cible', 'logo_url' => 'images/Logo_Cible.png', 'primary_color' => '#2563eb'],
                    ['id' => 3, 'name' => 'Cible Finishing', 'slug' => 'cible-finishing', 'logo_url' => 'images/Logo Cible Fininshing6.png', 'primary_color' => '#10b981'],
                    ['id' => 4, 'name' => 'BFM', 'slug' => 'bfm', 'logo_url' => 'images/logo_BFM2.svg', 'primary_color' => '#f59e0b']
                ];
            }
            sendResponse(true, $companies);
            break;
        
        case 'get_current_company':
            requireAuth();
            $companyId = $_SESSION['company_id'] ?? 1;
            $company = fetchOne("SELECT * FROM companies WHERE id = ?", [$companyId]);
            sendResponse(true, $company);
            break;
            
        case 'switch_company':
            requireAuth();
            $input = json_decode(file_get_contents('php://input'), true);
            $companyId = (int)($input['company_id'] ?? 0);
            
            if (!$companyId) sendResponse(false, null, 'Company ID required', 400);
            
            $company = fetchOne("SELECT * FROM companies WHERE id = ?", [$companyId]);
            if (!$company) {
                // Fallback
                $fallbackCompanies = [
                    1 => ['id' => 1, 'name' => 'BroMan', 'slug' => 'broman', 'logo_url' => 'images/Final_Logo.png', 'primary_color' => '#1e3a5f'],
                    2 => ['id' => 2, 'name' => 'Cible', 'slug' => 'cible', 'logo_url' => 'images/Logo_Cible.png', 'primary_color' => '#2563eb'],
                    3 => ['id' => 3, 'name' => 'Cible Finishing', 'slug' => 'cible-finishing', 'logo_url' => 'images/Logo Cible Fininshing6.png', 'primary_color' => '#10b981'],
                    4 => ['id' => 4, 'name' => 'BFM', 'slug' => 'bfm', 'logo_url' => 'images/logo_BFM2.svg', 'primary_color' => '#f59e0b']
                ];
                if (isset($fallbackCompanies[$companyId])) {
                    $company = $fallbackCompanies[$companyId];
                } else {
                    sendResponse(false, null, 'Company not found', 404);
                }
            }
            
            $_SESSION['company_id'] = $company['id'];
            $_SESSION['company_name'] = $company['name'];
            $_SESSION['company_logo'] = $company['logo_url'] ?? 'images/Final_Logo White.png';
            
            session_write_close();
            
            sendResponse(true, ['company_id' => $company['id'], 'company_name' => $company['name']], 'Company switched successfully');
            break;
        
        // ===== DASHBOARD =====
        
        case 'get_dashboard_stats':
        case 'get_analytics':
            requireAuth();
            $user = getCurrentUser();
            $isAdmin = $user['role'] === 'admin';
            $isManager = $user['role'] === 'manager';
            $isAdminOrManager = $isAdmin || $isManager;
            $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
            $dateFrom = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            $datePrev = date('Y-m-d H:i:s', strtotime("-" . ($days * 2) . " days"));
            
            $analytics = [];
            
            // Get current company for data isolation
            $companyId = $_SESSION['company_id'] ?? 1;
            
            // === OVERVIEW KPIs WITH TRENDS (filtered by company) ===
            $currentPublished = (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ? AND status = 'PUBLISHED' AND published_date >= ?", [$companyId, $dateFrom])['c'];
            $prevPublished = (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ? AND status = 'PUBLISHED' AND published_date >= ? AND published_date < ?", [$companyId, $datePrev, $dateFrom])['c'];
            
            $currentCreated = (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ? AND created_at >= ?", [$companyId, $dateFrom])['c'];
            $prevCreated = (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ? AND created_at >= ? AND created_at < ?", [$companyId, $datePrev, $dateFrom])['c'];
            
            $submitted = (int)fetchOne("SELECT COUNT(DISTINCT a.post_id) as c FROM activity_log a JOIN posts p ON a.post_id = p.id WHERE p.company_id = ? AND a.action = 'status_changed' AND a.new_value = 'PENDING_REVIEW' AND a.created_at >= ?", [$companyId, $dateFrom])['c'];
            $approved = (int)fetchOne("SELECT COUNT(DISTINCT a.post_id) as c FROM activity_log a JOIN posts p ON a.post_id = p.id WHERE p.company_id = ? AND a.action = 'status_changed' AND a.new_value = 'APPROVED' AND a.created_at >= ?", [$companyId, $dateFrom])['c'];
            $approvalRate = $submitted > 0 ? round(($approved / $submitted) * 100) : 0;
            
            $prevSubmitted = (int)fetchOne("SELECT COUNT(DISTINCT a.post_id) as c FROM activity_log a JOIN posts p ON a.post_id = p.id WHERE p.company_id = ? AND a.action = 'status_changed' AND a.new_value = 'PENDING_REVIEW' AND a.created_at >= ? AND a.created_at < ?", [$companyId, $datePrev, $dateFrom])['c'];
            $prevApproved = (int)fetchOne("SELECT COUNT(DISTINCT a.post_id) as c FROM activity_log a JOIN posts p ON a.post_id = p.id WHERE p.company_id = ? AND a.action = 'status_changed' AND a.new_value = 'APPROVED' AND a.created_at >= ? AND a.created_at < ?", [$companyId, $datePrev, $dateFrom])['c'];
            $prevApprovalRate = $prevSubmitted > 0 ? round(($prevApproved / $prevSubmitted) * 100) : 0;
            
            $pending = (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ? AND status = 'PENDING_REVIEW'", [$companyId])['c'];
            $reviewed = (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ? AND status = 'REVIEWED'", [$companyId])['c'];
            
            $analytics['overview'] = [
                'total_posts' => (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ?", [$companyId])['c'],
                'published_period' => $currentPublished,
                'published_trend' => $prevPublished > 0 ? round((($currentPublished - $prevPublished) / $prevPublished) * 100) : ($currentPublished > 0 ? 100 : 0),
                'created_period' => $currentCreated,
                'created_trend' => $prevCreated > 0 ? round((($currentCreated - $prevCreated) / $prevCreated) * 100) : ($currentCreated > 0 ? 100 : 0),
                'scheduled_upcoming' => (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ? AND status = 'SCHEDULED' AND scheduled_date > NOW()", [$companyId])['c'],
                'pending_review' => $pending,
                'reviewed' => $reviewed,
                'approval_rate' => $approvalRate,
                'approval_trend' => $approvalRate - $prevApprovalRate,
            ];
            
            // === BOTTLENECK DETECTION (avg time per stage) ===
            $bottlenecks = [];
            $stageTransitions = [
                ['from' => 'DRAFT', 'to' => 'PENDING_REVIEW', 'label' => 'Drafting'],
                ['from' => 'PENDING_REVIEW', 'to' => 'REVIEWED', 'label' => 'Admin Review'],
                ['from' => 'REVIEWED', 'to' => 'APPROVED', 'label' => 'Manager Approval'],
                ['from' => 'APPROVED', 'to' => 'SCHEDULED', 'label' => 'Scheduling'],
                ['from' => 'SCHEDULED', 'to' => 'PUBLISHED', 'label' => 'Publishing'],
            ];
            
            foreach ($stageTransitions as $trans) {
                $avgHours = fetchOne(
                    "SELECT AVG(TIMESTAMPDIFF(HOUR, a1.created_at, a2.created_at)) as avg_hours
                     FROM activity_log a1
                     JOIN activity_log a2 ON a1.post_id = a2.post_id 
                        AND a2.action = 'status_changed' AND a2.new_value = ?
                        AND a2.created_at > a1.created_at
                     WHERE a1.action = 'status_changed' AND a1.new_value = ?
                     AND a1.created_at >= ?",
                    [$trans['to'], $trans['from'], $dateFrom]
                );
                $hours = round($avgHours['avg_hours'] ?? 0, 1);
                $bottlenecks[] = [
                    'stage' => $trans['label'],
                    'from' => $trans['from'],
                    'to' => $trans['to'],
                    'avg_hours' => $hours,
                    'avg_days' => round($hours / 24, 1),
                    'is_bottleneck' => $hours > 48 // More than 2 days = bottleneck
                ];
            }
            $analytics['bottlenecks'] = $bottlenecks;
            
            // === POSTS BY STATUS (Funnel) - filtered by company ===
            $analytics['by_status'] = [];
            $statuses = ['DRAFT', 'PENDING_REVIEW', 'REVIEWED', 'APPROVED', 'SCHEDULED', 'PUBLISHED'];
            foreach ($statuses as $status) {
                $analytics['by_status'][$status] = (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ? AND status = ?", [$companyId, $status])['c'];
            }
            $analytics['by_status']['CHANGES_REQUESTED'] = (int)fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = ? AND status = 'CHANGES_REQUESTED'", [$companyId])['c'];
            
            // === POSTS BY PLATFORM (counts each platform from JSON arrays) ===
            $allPosts = fetchAll("SELECT platforms FROM posts WHERE company_id = ? AND platforms IS NOT NULL AND platforms != ''", [$companyId]);
            $platformCounts = [];
            foreach ($allPosts as $post) {
                $platforms = json_decode($post['platforms'], true);
                if (is_array($platforms)) {
                    foreach ($platforms as $platform) {
                        $platform = trim($platform);
                        if ($platform) {
                            $platformCounts[$platform] = ($platformCounts[$platform] ?? 0) + 1;
                        }
                    }
                }
            }
            // Convert to array format for chart
            $analytics['by_platform'] = [];
            arsort($platformCounts); // Sort by count descending
            foreach ($platformCounts as $platform => $count) {
                $analytics['by_platform'][] = ['platform' => $platform, 'count' => $count];
            }
            
            // === TIME-BASED INSIGHTS - filtered by company ===
            $bestDay = fetchOne(
                "SELECT DAYNAME(created_at) as day, COUNT(*) as count 
                 FROM posts WHERE company_id = ? AND status = 'PUBLISHED' 
                 GROUP BY DAYOFWEEK(created_at) ORDER BY count DESC LIMIT 1",
                [$companyId]
            );
            $bestHour = fetchOne(
                "SELECT HOUR(created_at) as hour, COUNT(*) as count 
                 FROM posts WHERE company_id = ? AND status = 'PUBLISHED' 
                 GROUP BY HOUR(created_at) ORDER BY count DESC LIMIT 1",
                [$companyId]
            );
            $analytics['time_insights'] = [
                'best_day' => $bestDay['day'] ?? 'N/A',
                'best_day_count' => (int)($bestDay['count'] ?? 0),
                'best_hour' => isset($bestHour['hour']) ? sprintf('%02d:00', $bestHour['hour']) : 'N/A',
                'best_hour_count' => (int)($bestHour['count'] ?? 0),
            ];
            
            // === USER PERFORMANCE WITH DETAILED METRICS (filtered by company) ===
            $userQuery = $isAdminOrManager 
                ? "SELECT u.id, u.username, u.full_name, u.role,
                      -- Ideas from staff_ideas table
                      (SELECT COUNT(*) FROM staff_ideas si WHERE si.user_id = u.id AND si.company_id = ? AND si.created_at >= ?) as ideas_created,
                      -- Authoring stats (Staff focus)
                      (SELECT COUNT(*) FROM posts p2 WHERE p2.author_id = u.id AND p2.company_id = ? AND p2.status = 'DRAFT' AND p2.created_at >= ?) as drafts_created,
                      (SELECT COUNT(*) FROM posts p2 WHERE p2.author_id = u.id AND p2.company_id = ? AND p2.status IN ('PENDING_REVIEW', 'REVIEWED') AND p2.created_at >= ?) as pending_review_count,
                      (SELECT COUNT(*) FROM posts p2 WHERE p2.author_id = u.id AND p2.company_id = ? AND p2.status IN ('APPROVED', 'SCHEDULED', 'PUBLISHED') AND p2.created_at >= ?) as approved_count,
                      (SELECT COUNT(*) FROM posts p2 WHERE p2.author_id = u.id AND p2.company_id = ? AND p2.status = 'PUBLISHED' AND p2.created_at >= ?) as published_count,
                      
                      -- Admin/Manager stats (Reviewer focus)
                      (SELECT COUNT(DISTINCT al.post_id) FROM activity_log al JOIN posts p3 ON al.post_id = p3.id WHERE al.user_id = u.id AND p3.company_id = ? AND al.action = 'status_changed' AND al.new_value IN ('REVIEWED', 'APPROVED') AND al.created_at >= ?) as reviews_approved,
                      (SELECT COUNT(DISTINCT al.post_id) FROM activity_log al JOIN posts p3 ON al.post_id = p3.id WHERE al.user_id = u.id AND p3.company_id = ? AND al.action = 'status_changed' AND al.new_value = 'CHANGES_REQUESTED' AND al.created_at >= ?) as reviews_rejected,
                      (SELECT COUNT(*) FROM comments c JOIN posts p4 ON c.post_id = p4.id WHERE c.user_id = u.id AND p4.company_id = ? AND c.created_at >= ?) as comments_count,
                      
                      -- Meta
                      (SELECT MAX(al2.created_at) FROM activity_log al2 JOIN posts p5 ON al2.post_id = p5.id WHERE al2.user_id = u.id AND p5.company_id = ?) as last_activity
                   FROM users u WHERE u.is_active = 1 ORDER BY u.role DESC, u.full_name ASC"
                : "SELECT u.id, u.username, u.full_name, u.role,
                      (SELECT COUNT(*) FROM staff_ideas si WHERE si.user_id = u.id AND si.company_id = ? AND si.created_at >= ?) as ideas_created,
                      (SELECT COUNT(*) FROM posts p2 WHERE p2.author_id = u.id AND p2.company_id = ? AND p2.status = 'DRAFT' AND p2.created_at >= ?) as drafts_created,
                      (SELECT COUNT(*) FROM posts p2 WHERE p2.author_id = u.id AND p2.company_id = ? AND p2.status IN ('PENDING_REVIEW', 'REVIEWED') AND p2.created_at >= ?) as pending_review_count,
                      (SELECT COUNT(*) FROM posts p2 WHERE p2.author_id = u.id AND p2.company_id = ? AND p2.status IN ('APPROVED', 'SCHEDULED', 'PUBLISHED') AND p2.created_at >= ?) as approved_count,
                      (SELECT COUNT(*) FROM posts p2 WHERE p2.author_id = u.id AND p2.company_id = ? AND p2.status = 'PUBLISHED' AND p2.created_at >= ?) as published_count,
                      (SELECT COUNT(*) FROM comments c JOIN posts p4 ON c.post_id = p4.id WHERE c.user_id = u.id AND p4.company_id = ? AND c.created_at >= ?) as comments_count,
                      (SELECT MAX(al2.created_at) FROM activity_log al2 JOIN posts p5 ON al2.post_id = p5.id WHERE al2.user_id = u.id AND p5.company_id = ?) as last_activity
                   FROM users u WHERE u.id = ? AND u.is_active = 1";
            
            // Common params: company_id, date_from
            if ($isAdminOrManager) {
                $userParams = [
                    $companyId, $dateFrom, // ideas_created
                    $companyId, $dateFrom, // drafts
                    $companyId, $dateFrom, // pending
                    $companyId, $dateFrom, // approved
                    $companyId, $dateFrom, // published
                    $companyId, $dateFrom, // reviews_approved
                    $companyId, $dateFrom, // reviews_rejected
                    $companyId, $dateFrom, // comments
                    $companyId             // last_activity
                ];
            } else {
                $userParams = [
                    $companyId, $dateFrom, // ideas_created
                    $companyId, $dateFrom, // drafts
                    $companyId, $dateFrom, // pending
                    $companyId, $dateFrom, // approved
                    $companyId, $dateFrom, // published
                    $companyId, $dateFrom, // comments
                    $companyId,            // last_activity
                    $user['id']
                ];
            }
            
            $users = fetchAll($userQuery, $userParams);
            
            foreach ($users as &$u) {
                // Ensure all keys exist to prevent JS errors
                $u['ideas_created'] = (int)($u['ideas_created'] ?? 0);
                $u['drafts_created'] = (int)($u['drafts_created'] ?? 0);
                $u['pending_review_count'] = (int)($u['pending_review_count'] ?? 0);
                $u['approved_count'] = (int)($u['approved_count'] ?? 0);
                $u['published_count'] = (int)($u['published_count'] ?? 0);
                $u['reviews_approved'] = (int)($u['reviews_approved'] ?? 0);
                $u['reviews_rejected'] = (int)($u['reviews_rejected'] ?? 0);
                $u['comments_count'] = (int)($u['comments_count'] ?? 0);
                
                // Calculate approval rate based on drafts and approved
                $totalAuthoring = $u['drafts_created'] + $u['approved_count'];
                $u['author_approval_rate'] = $totalAuthoring > 0 ? round(($u['approved_count'] / $totalAuthoring) * 100) : 0;
            }
            $analytics['user_performance'] = $users;
            
            // === SMART RECOMMENDATIONS ===
            $recommendations = [];
            
            // Bottleneck recommendation
            $maxBottleneck = null;
            foreach ($bottlenecks as $b) {
                if ($b['is_bottleneck'] && (!$maxBottleneck || $b['avg_hours'] > $maxBottleneck['avg_hours'])) {
                    $maxBottleneck = $b;
                }
            }
            if ($maxBottleneck) {
                $recommendations[] = [
                    'type' => 'warning',
                    'icon' => '⏱️',
                    'title' => 'Bottleneck Detected',
                    'message' => "Posts spend avg {$maxBottleneck['avg_days']} days in {$maxBottleneck['stage']}. Consider faster review cycles.",
                ];
            }
            
            // High performer recommendation
            if ($isAdmin && count($users) > 1) {
                usort($users, fn($a, $b) => $b['productivity_score'] - $a['productivity_score']);
                $topUser = $users[0];
                if ($topUser['productivity_score'] >= 70) {
                    $recommendations[] = [
                        'type' => 'success',
                        'icon' => '⭐',
                        'title' => 'Top Performer',
                        'message' => "{$topUser['full_name']} has {$topUser['productivity_score']}% productivity score with {$topUser['published']} published posts.",
                    ];
                }
            }
            
            // Pending review alert
            $pending = $analytics['overview']['pending_review'];
            if ($pending >= 3) {
                $recommendations[] = [
                    'type' => 'alert',
                    'icon' => '📋',
                    'title' => 'Review Backlog',
                    'message' => "{$pending} posts waiting for review. Schedule time to clear the queue.",
                ];
            }
            
            // Best time insight
            if ($bestDay['day'] ?? false) {
                $recommendations[] = [
                    'type' => 'info',
                    'icon' => '💡',
                    'title' => 'Optimal Publishing',
                    'message' => "Most content gets published on {$bestDay['day']}s. Plan your workflow accordingly.",
                ];
            }
            
            $analytics['recommendations'] = $recommendations;
            
            // === WEEKLY TRENDS - filtered by company ===
            $analytics['weekly_trends'] = fetchAll(
                "SELECT YEARWEEK(created_at, 1) as week, DATE_FORMAT(MIN(created_at), '%b %d') as week_start,
                    COUNT(*) as created, SUM(CASE WHEN status = 'PUBLISHED' THEN 1 ELSE 0 END) as published
                 FROM posts WHERE company_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
                 GROUP BY YEARWEEK(created_at, 1) ORDER BY week",
                [$companyId]
            );
            
            // === REVISION ANALYTICS - filtered by company ===
            $analytics['revision_stats'] = [
                'total_revisions' => (int)fetchOne("SELECT COUNT(*) as c FROM activity_log a JOIN posts p ON a.post_id = p.id WHERE p.company_id = ? AND a.action = 'status_changed' AND a.new_value = 'CHANGES_REQUESTED' AND a.created_at >= ?", [$companyId, $dateFrom])['c'],
                'by_platform' => [], // Disabled after migration to multi-platform
            ];
            
            // === RECENT ACTIVITY - filtered by company, manager only ===
            if ($isManager) {
                $analytics['recent_activity'] = fetchAll(
                    "SELECT al.*, u.username, u.full_name, p.title as post_title, p.platforms, p.id as post_id
                     FROM activity_log al 
                     JOIN users u ON al.user_id = u.id 
                     LEFT JOIN posts p ON al.post_id = p.id 
                     WHERE (p.company_id = ? OR al.post_id IS NULL)
                     ORDER BY al.created_at DESC LIMIT 25",
                    [$companyId]
                );
            } else {
                $analytics['recent_activity'] = [];
            }
            
            // === UPCOMING SCHEDULED - filtered by company ===
            $analytics['upcoming_scheduled'] = fetchAll(
                "SELECT p.id, p.title, p.platforms, p.scheduled_date, u.username as author
                 FROM posts p JOIN users u ON p.author_id = u.id
                 WHERE p.company_id = ? AND p.status = 'SCHEDULED' AND p.scheduled_date > NOW()
                 ORDER BY p.scheduled_date ASC LIMIT 5",
                [$companyId]
            );
            
            // === CONTENT HEALTH SCORE ===
            $healthScore = 100;
            $totalBacklog = $pending + $reviewed;
            if ($totalBacklog >= 8) $healthScore -= 30;
            elseif ($totalBacklog >= 5) $healthScore -= 20;
            elseif ($totalBacklog >= 3) $healthScore -= 10;
            
            if ($maxBottleneck) $healthScore -= 15;
            if ($approvalRate < 50) $healthScore -= 20;
            elseif ($approvalRate < 70) $healthScore -= 10;
            
            $analytics['health'] = [
                'score' => max(0, $healthScore),
                'status' => $healthScore >= 80 ? 'healthy' : ($healthScore >= 50 ? 'warning' : 'critical'),
                'label' => $healthScore >= 80 ? 'Healthy Pipeline' : ($healthScore >= 50 ? 'Needs Attention' : 'Critical Issues'),
            ];
            
            sendResponse(true, $analytics);
            break;
        
        // ===== CALENDAR =====
        
        case 'fetch_calendar':
            requireAuth();
            $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
            $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
            $companyId = $_SESSION['company_id'] ?? 1;
            
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $posts = fetchAll(
                "SELECT p.id, p.title, p.status, p.platforms, p.scheduled_date, p.published_date,
                        (SELECT file_path FROM media_files WHERE post_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                 FROM posts p
                 WHERE p.company_id = ?
                 AND (p.status IN ('SCHEDULED', 'PUBLISHED'))
                 AND (
                     (p.scheduled_date BETWEEN ? AND ?)
                     OR (p.published_date BETWEEN ? AND ?)
                 )
                 ORDER BY COALESCE(p.scheduled_date, p.published_date) ASC",
                [$companyId, $startDate, $endDate . ' 23:59:59', $startDate, $endDate . ' 23:59:59']
            );
            
            sendResponse(true, $posts);
            break;
        
        // ===== USERS MANAGEMENT =====
        
        case 'get_users':
        case 'fetch_users':
            requireAdmin();
            $users = fetchAll(
                "SELECT id, username, full_name, role, is_active, created_at, 
                        (SELECT COUNT(*) FROM posts WHERE author_id = users.id) as post_count
                 FROM users 
                 ORDER BY created_at DESC"
            );
            sendResponse(true, $users);
            break;
        
        case 'update_user_status':
            requireManager();
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $input['id'] ?? 0;
            $isActive = $input['is_active'] ?? 0;
            
            if (!$userId) sendResponse(false, null, 'User ID required', 400);
            
            // Prevent deactivating yourself
            if ($userId == $_SESSION['user_id'] && !$isActive) {
                sendResponse(false, null, 'Cannot deactivate yourself', 400);
            }
            
            executeQuery("UPDATE users SET is_active = ? WHERE id = ?", [$isActive, $userId]);
            sendResponse(true, null, $isActive ? 'User activated' : 'User deactivated');
            break;
        
        case 'update_user':
            requireManager();
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $input['id'] ?? 0;
            $fullName = trim($input['full_name'] ?? '');
            $role = $input['role'] ?? '';
            $password = $input['password'] ?? '';
            
            if (!$userId) sendResponse(false, null, 'User ID required', 400);
            if (empty($fullName)) sendResponse(false, null, 'Full name is required', 400);
            if (!in_array($role, ['admin', 'staff', 'manager'])) sendResponse(false, null, 'Invalid role', 400);
            
            // Check if user exists and get current role
            $existingUser = fetchOne("SELECT id, role FROM users WHERE id = ?", [$userId]);
            if (!$existingUser) sendResponse(false, null, 'User not found', 404);
            
            // Prevent changing your own role (security measure)
            if ($userId == $_SESSION['user_id'] && $existingUser['role'] !== $role) {
                sendResponse(false, null, 'Cannot demote yourself', 400);
            }
            
            // Update user
            if (!empty($password)) {
                // Update with new password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                executeQuery(
                    "UPDATE users SET full_name = ?, role = ?, password_hash = ? WHERE id = ?",
                    [$fullName, $role, $passwordHash, $userId]
                );
            } else {
                // Update without changing password
                executeQuery(
                    "UPDATE users SET full_name = ?, role = ? WHERE id = ?",
                    [$fullName, $role, $userId]
                );
            }
            
            sendResponse(true, null, 'User updated successfully');
            break;
        
        case 'create_user':
            requireManager();
            $input = json_decode(file_get_contents('php://input'), true);
            $username = trim($input['username'] ?? '');
            $fullName = trim($input['full_name'] ?? '');
            $role = $input['role'] ?? 'staff';
            $password = $input['password'] ?? '';
            
            if (empty($username)) sendResponse(false, null, 'Username is required', 400);
            if (empty($fullName)) sendResponse(false, null, 'Full name is required', 400);
            if (empty($password)) sendResponse(false, null, 'Password is required', 400);
            if (!in_array($role, ['admin', 'staff', 'manager'])) sendResponse(false, null, 'Invalid role', 400);
            
            // Check if username already exists
            $existingUser = fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
            if ($existingUser) sendResponse(false, null, 'Username already exists', 400);
            
            // Create user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            executeQuery(
                "INSERT INTO users (username, full_name, role, password_hash, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())",
                [$username, $fullName, $role, $passwordHash]
            );
            
            sendResponse(true, ['id' => lastInsertId()], 'User created successfully');
            break;
        
        case 'get_user_by_id':
            requireManager();
            $id = $_GET['id'] ?? 0;
            if (!$id) sendResponse(false, null, 'User ID required', 400);
            
            $user = fetchOne(
                "SELECT id, username, full_name, role, is_active, created_at, last_login 
                 FROM users WHERE id = ?", [$id]
            );
            if (!$user) sendResponse(false, null, 'User not found', 404);
            sendResponse(true, $user);
            break;
        
        case 'save_user':
            requireManager();
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            $username = trim($input['username'] ?? '');
            $fullName = trim($input['full_name'] ?? '');
            $role = $input['role'] ?? 'staff';
            $password = $input['password'] ?? '';
            $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : true;
            
            if (empty($username)) sendResponse(false, null, 'Username is required', 400);
            if (!in_array($role, ['admin', 'staff', 'manager'])) $role = 'staff';
            
            if ($id) {
                // Update existing user
                $existingUser = fetchOne("SELECT id FROM users WHERE id = ?", [$id]);
                if (!$existingUser) sendResponse(false, null, 'User not found', 404);
                
                // Check for duplicate username
                $dup = fetchOne("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
                if ($dup) sendResponse(false, null, 'Username already exists', 400);
                
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    executeQuery("UPDATE users SET username=?, full_name=?, role=?, password_hash=?, is_active=? WHERE id=?",
                        [$username, $fullName, $role, $hash, $isActive, $id]);
                } else {
                    executeQuery("UPDATE users SET username=?, full_name=?, role=?, is_active=? WHERE id=?",
                        [$username, $fullName, $role, $isActive, $id]);
                }
                sendResponse(true, ['id' => $id], 'User updated successfully');
            } else {
                // Create new user
                if (empty($password)) sendResponse(false, null, 'Password is required for new users', 400);
                
                $dup = fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
                if ($dup) sendResponse(false, null, 'Username already exists', 400);
                
                $hash = password_hash($password, PASSWORD_DEFAULT);
                executeQuery("INSERT INTO users (username, full_name, role, password_hash, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())",
                    [$username, $fullName, $role, $hash, $isActive]);
                sendResponse(true, ['id' => lastInsertId()], 'User created successfully', 201);
            }
            break;
        
        // ===== POSTS =====
        
        case 'fetch_posts':
            requireAuth();
            $user = getCurrentUser();
            
            // Auto-publish check: Move SCHEDULED posts to PUBLISHED if due
            try {
                $duePosts = fetchAll("SELECT id, title, author_id FROM posts WHERE status = 'SCHEDULED' AND scheduled_date <= NOW()");
                foreach ($duePosts as $duePost) {
                    executeQuery("UPDATE posts SET status = 'PUBLISHED', published_date = NOW() WHERE id = ?", [$duePost['id']]);
                    // Use author_id for activity log since it's system-triggered
                    logActivity($duePost['id'], $duePost['author_id'], 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule');
                    notify($duePost['author_id'], 'published', 'Post Published', "Your post '{$duePost['title']}' has been automatically published!", $duePost['id'], null);
                }
            } catch (Exception $e) {
                // Don't fail fetch_posts if auto-publish has an issue
                error_log("Auto-publish error: " . $e->getMessage());
            }
            
            $where = [];
            $params = [];
            
            // CRITICAL: Filter by current company for data isolation
            $companyId = $_SESSION['company_id'] ?? 1;
            $where[] = "p.company_id = ?";
            $params[] = $companyId;
            
            if (!empty($_GET['status'])) {
                $where[] = "p.status = ?";
                $params[] = $_GET['status'];
            }
            if (!empty($_GET['platform'])) {
                $where[] = "p.platforms LIKE ?";
                $params[] = '%"' . $_GET['platform'] . '"%';
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
            
            $posts = fetchAll($sql, $params);
            
            // Populate media for all posts efficiently
            if (!empty($posts)) {
                $postIds = array_column($posts, 'id');
                // Fetch all media for these posts
                if (!empty($postIds)) {
                    $placeholders = str_repeat('?,', count($postIds) - 1) . '?';
                    $mediaFiles = fetchAll("SELECT id, post_id, file_path, file_type as type FROM media_files WHERE post_id IN ($placeholders)", $postIds);
                    
                    // Group media by post_id
                    $mediaMap = [];
                    foreach ($mediaFiles as $file) {
                        $mediaMap[$file['post_id']][] = $file;
                    }
                    
                    // Attach to posts
                    foreach ($posts as &$post) {
                        $post['media'] = $mediaMap[$post['id']] ?? [];
                    }
                }
            }
            
            sendResponse(true, $posts);
            break;
        
        case 'get_calendar_posts':
            requireAuth();
            $month = intval($_GET['month'] ?? date('m'));
            $year = intval($_GET['year'] ?? date('Y'));
            
            $startDate = "$year-$month-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $companyId = $_SESSION['company_id'] ?? 1;
            
            $posts = fetchAll("
                SELECT p.id, p.title, p.platforms, p.status, p.scheduled_date, p.published_date, 
                       u.username as author_name, u.full_name as author_full_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.company_id = ?
                  AND ((p.scheduled_date BETWEEN ? AND ?) 
                   OR (p.published_date BETWEEN ? AND ?))
                ORDER BY COALESCE(p.scheduled_date, p.published_date) ASC
            ", [$companyId, $startDate, $endDate, $startDate, $endDate]);
            
            sendResponse(true, [
                'posts' => $posts,
                'month' => $month,
                'year' => $year
            ]);
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
                $title = sanitizeString(trim($_POST['title'] ?? ''), 255);
                $content = sanitizeString(trim($_POST['content'] ?? ''));
                // Handle platforms as array or string
                $platforms = $_POST['platforms'] ?? $_POST['platform'] ?? [];
                if (is_string($platforms)) $platforms = json_decode($platforms, true) ?: [$platforms];
                $status = $_POST['status'] ?? 'DRAFT';
                $urgency = !empty($_POST['urgency']);
                $priority = $_POST['priority'] ?? 'normal';
                $scheduled_date = $_POST['scheduled_date'] ?? null;
            } else {
                // JSON request (backwards compatible)
                $input = json_decode(file_get_contents('php://input'), true);
                $id = $input['id'] ?? null;
                $title = sanitizeString(trim($input['title'] ?? ''), 255);
                $content = sanitizeString(trim($input['content'] ?? ''));
                // Handle platforms as array or string
                $platforms = $input['platforms'] ?? $input['platform'] ?? [];
                if (is_string($platforms)) $platforms = [$platforms];
                $status = $input['status'] ?? 'DRAFT';
                $urgency = !empty($input['urgency']);
                $priority = $input['priority'] ?? 'normal';
                $scheduled_date = $input['scheduled_date'] ?? null;
            }
            
            // Convert platforms array to JSON
            $platformsJson = json_encode(array_values(array_filter($platforms)));
            
            if (empty($title)) sendResponse(false, null, 'Title required', 400);
            if (empty($content)) sendResponse(false, null, 'Content required', 400);
            if (empty($platforms)) sendResponse(false, null, 'At least one platform required', 400);
            
            // Staff can only create DRAFT posts (Ideas are now separate)
            if ($user['role'] === 'staff' && $status !== 'DRAFT') {
                $status = 'DRAFT';
            }
            
            $postId = null;
            
            if ($id) {
                $existing = fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
                if (!$existing) sendResponse(false, null, 'Post not found', 404);
                
                // No one can edit posts after manager approval (APPROVED, SCHEDULED, PUBLISHED)
                if (in_array($existing['status'], ['APPROVED', 'SCHEDULED', 'PUBLISHED'])) {
                    sendResponse(false, null, 'Cannot edit post after manager approval', 403);
                }
                
                // Staff & Admin restriction: ONLY edit their own DRAFT or CHANGES_REQUESTED
                // Manager restriction: Managers can edit any post (passing above approval check)
                if (in_array($user['role'], ['staff', 'admin'])) {
                    if ($existing['author_id'] != $user['id']) {
                        sendResponse(false, null, 'Cannot edit others posts', 403);
                    }
                    if (!in_array($existing['status'], ['DRAFT', 'CHANGES_REQUESTED'])) {
                        sendResponse(false, null, 'Cannot edit post in current status', 403);
                    }
                }
                
                // Track what changed for detailed logging
                $changes = [];
                $oldData = [];
                $newData = [];
                
                if ($existing['title'] !== $title) {
                    $changes[] = 'العنوان';
                    $oldData['title'] = $existing['title'];
                    $newData['title'] = $title;
                }
                if ($existing['content'] !== $content) {
                    $changes[] = 'المحتوى';
                    $oldData['content'] = mb_substr($existing['content'], 0, 200);
                    $newData['content'] = mb_substr($content, 0, 200);
                }
                if ($existing['platforms'] !== $platformsJson) {
                    $changes[] = 'المنصات';
                    $oldData['platforms'] = $existing['platforms'];
                    $newData['platforms'] = $platformsJson;
                }
                if ((bool)$existing['urgency'] !== $urgency) {
                    $changes[] = 'الأولوية العاجلة';
                    $oldData['urgency'] = (bool)$existing['urgency'];
                    $newData['urgency'] = $urgency;
                }
                if ($existing['priority'] !== $priority) {
                    $changes[] = 'درجة الأهمية';
                    $oldData['priority'] = $existing['priority'];
                    $newData['priority'] = $priority;
                }
                
                executeQuery(
                    "UPDATE posts SET title=?, content=?, platforms=?, urgency=?, priority=?, scheduled_date=? WHERE id=?",
                    [$title, $content, $platformsJson, $urgency, $priority, $scheduled_date, $id]
                );
                
                $changeDesc = !empty($changes) ? 'تم تعديل: ' . implode('، ', $changes) : 'Content updated';
                logActivity($id, $user['id'], 'updated', 
                    !empty($oldData) ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null, 
                    !empty($newData) ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null, 
                    $changeDesc);
                $postId = $id;
            } else {
                // Get current company from session
                $companyId = $_SESSION['company_id'] ?? 1;
                
                executeQuery(
                    "INSERT INTO posts (company_id, title, content, platforms, status, urgency, priority, author_id, scheduled_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$companyId, $title, $content, $platformsJson, $status, $urgency, $priority, $user['id'], $scheduled_date]
                );
                $postId = lastInsertId();
                logActivity($postId, $user['id'], 'created', null, null, "Created as $status");
            }
            
            // Handle multiple file uploads (files[] array) or single file (legacy)
            $uploadedFiles = [];
            
            // Check for files array (new multi-upload)
            if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
                $fileCount = count($_FILES['files']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                        $uploadedFiles[] = [
                            'name' => $_FILES['files']['name'][$i],
                            'tmp_name' => $_FILES['files']['tmp_name'][$i],
                            'size' => $_FILES['files']['size'][$i],
                            'type' => $_FILES['files']['type'][$i]
                        ];
                    }
                }
            }
            // Legacy single file upload
            elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadedFiles[] = $_FILES['file'];
            }
            
            // Process all uploaded files
            foreach ($uploadedFiles as $fileIndex => $file) {
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
            $scheduledDate = $input['scheduled_date'] ?? null;
            
            if (!$id || !$newStatus) sendResponse(false, null, 'ID and status required', 400);
            
            $post = fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
            if (!$post) sendResponse(false, null, 'Post not found', 404);
            
            $oldStatus = $post['status'];
            
            // Validate transitions with Centralized WorkflowManager
            if (!WorkflowManager::canTransition($user['role'], $oldStatus, $newStatus)) {
                sendResponse(false, null, 'You do not have permission to perform this transition.', 403);
            }
            
            if ($newStatus === 'DRAFT' && $user['role'] !== 'manager') {
                if ($post['author_id'] != $user['id']) {
                    sendResponse(false, null, 'You can only recall your own posts to DRAFT.', 403);
                }
            }
            
            if ($newStatus === 'SCHEDULED' && empty($scheduledDate)) {
                sendResponse(false, null, 'Scheduled date is required', 400);
            }
            
            $updateSql = "UPDATE posts SET status = ?, reviewer_id = ?";
            $updateParams = [$newStatus, $user['id']];
            
            if ($newStatus === 'CHANGES_REQUESTED') {
                $updateSql .= ", change_request_reason = ?, change_requested_by = ?, change_requested_at = NOW()";
                $updateParams[] = $reason;
                $updateParams[] = $user['id'];
            }
            
            if ($newStatus === 'SCHEDULED' && $scheduledDate) {
                $updateSql .= ", scheduled_date = ?";
                $updateParams[] = $scheduledDate;
            }
            
            if ($newStatus === 'PUBLISHED') {
                $updateSql .= ", published_date = NOW()";
            }
            
            $updateSql .= " WHERE id = ?";
            $updateParams[] = $id;
            
            executeQuery($updateSql, $updateParams);
            
            // Enhanced activity logging
            $description = $reason;
            if ($newStatus === 'SCHEDULED' && $scheduledDate) {
                $description = "Scheduled for " . date('M j, Y g:i A', strtotime($scheduledDate));
            } elseif ($newStatus === 'PUBLISHED') {
                $description = "Published to archive";
            } elseif ($oldStatus === 'APPROVED' && $newStatus === 'REVIEWED') {
                $description = "Revoked approval - returned to manager review";
            } elseif ($oldStatus === 'REVIEWED' && $newStatus === 'PENDING_REVIEW') {
                $description = "Recalled from manager - returned to admin review";
            } elseif ($oldStatus === 'PENDING_REVIEW' && $newStatus === 'DRAFT') {
                $description = "Recalled from review - returned to draft";
            } elseif ($oldStatus === 'PENDING_REVIEW' && $newStatus === 'REVIEWED') {
                $description = "Admin approved - forwarded to manager";
            } elseif ($oldStatus === 'REVIEWED' && $newStatus === 'APPROVED') {
                $description = "Manager final approval";
            }
            
            logActivity($id, $user['id'], 'status_changed', $oldStatus, $newStatus, "{$user['full_name']} changed status: " . ($description ?: $newStatus));
            
            // Notifications
            if ($newStatus === 'PENDING_REVIEW') {
                $admins = fetchAll("SELECT id FROM users WHERE role = 'admin'");
                foreach ($admins as $admin) {
                    notify($admin['id'], 'review_needed', 'Review Needed', "Post '{$post['title']}' needs review", $id, $user['id']);
                }
            } elseif ($newStatus === 'REVIEWED') {
                // Notify all managers when a post is ready for final approval
                $managers = fetchAll("SELECT id FROM users WHERE role = 'manager'");
                foreach ($managers as $manager) {
                    notify($manager['id'], 'manager_approval_needed', 'Manager Approval Needed', "Post '{$post['title']}' needs your final approval", $id, $user['id']);
                }
                // Also notify the author that their post is under manager review
                notify($post['author_id'], 'reviewed', 'Post Under Manager Review', "Your post '{$post['title']}' is now under manager review", $id, $user['id']);
            } elseif ($newStatus === 'APPROVED') {
                notify($post['author_id'], 'approved', 'Post Approved', "Your post '{$post['title']}' was approved!", $id, $user['id']);
                // Notify admins that a post was approved by manager so they can schedule it
                $admins = fetchAll("SELECT id FROM users WHERE role = 'admin' AND id != ?", [$user['id']]);
                foreach ($admins as $admin) {
                    notify($admin['id'], 'post_approved', 'Post Ready for Scheduling', "Post '{$post['title']}' was approved and is ready for scheduling", $id, $user['id']);
                }
            } elseif ($newStatus === 'CHANGES_REQUESTED') {
                notify($post['author_id'], 'changes_requested', 'Changes Requested', "Changes requested on '{$post['title']}': $reason", $id, $user['id']);
            } elseif ($newStatus === 'SCHEDULED') {
                notify($post['author_id'], 'scheduled', 'Post Scheduled', "Your post '{$post['title']}' is scheduled for publishing!", $id, $user['id']);
            } elseif ($newStatus === 'PUBLISHED') {
                notify($post['author_id'], 'published', 'Post Published', "Your post '{$post['title']}' has been published!", $id, $user['id']);
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
            
            // No one can delete posts after manager approval
            if (in_array($post['status'], ['APPROVED', 'SCHEDULED', 'PUBLISHED'])) {
                sendResponse(false, null, 'Cannot delete post after manager approval', 403);
            }
            
            // Staff & Admin restriction: ONLY delete if author_id == user_id AND status == 'DRAFT'
            // Manager restriction: Managers can delete any post (passing above approval check)
            if (in_array($user['role'], ['staff', 'admin'])) {
                if ($post['author_id'] != $user['id']) sendResponse(false, null, 'Cannot delete others posts', 403);
                if ($post['status'] !== 'DRAFT') sendResponse(false, null, 'Cannot delete submitted post', 403);
            }
            
            // Log deletion BEFORE deleting (post_id will become NULL after deletion due to SET NULL FK)
            $deletedPostInfo = json_encode([
                'title' => $post['title'],
                'status' => $post['status'],
                'platforms' => $post['platforms'],
                'content' => mb_substr($post['content'], 0, 200)
            ], JSON_UNESCAPED_UNICODE);
            
            // Get author name for the description
            $postAuthor = fetchOne("SELECT full_name FROM users WHERE id = ?", [$post['author_id']]);
            $authorName = $postAuthor['full_name'] ?? 'Unknown';
            
            logActivity($id, $user['id'], 'post_deleted', $deletedPostInfo, null, 
                'تم حذف بوست: "' . mb_substr($post['title'], 0, 50) . '" (الكاتب: ' . $authorName . ')');
            
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
            if ($file['size'] > MAX_FILE_SIZE) sendResponse(false, null, 'File too large (max 100MB)', 400);
            
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
            $user = getCurrentUser();
            $id = $_GET['id'] ?? 0;
            if (!$id) sendResponse(false, null, 'ID required', 400);
            
            $media = fetchOne("SELECT * FROM media_files WHERE id = ?", [$id]);
            if (!$media) sendResponse(false, null, 'Not found', 404);
            
            // Log media deletion
            logActivity($media['post_id'], $user['id'], 'media_deleted', 
                json_encode(['file_name' => $media['original_name'], 'file_type' => $media['file_type']], JSON_UNESCAPED_UNICODE), 
                null, 
                'تم حذف ملف: ' . $media['original_name']);
            
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
            
            $postId = (int)($input['post_id'] ?? 0);
            $content = sanitizeString(trim($input['content'] ?? ''));
            
            if (!$postId || !$content) sendResponse(false, null, 'Post ID and content required', 400);
            if (mb_strlen($content) > 5000) sendResponse(false, null, 'Comment too long (max 5000 chars)', 400);
            
            // Get post information to find the author
            $post = fetchOne("SELECT id, title, author_id FROM posts WHERE id = ?", [$postId]);
            if (!$post) sendResponse(false, null, 'Post not found', 404);
            
            executeQuery("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)", [$postId, $user['id'], $content]);
            logActivity($postId, $user['id'], 'comment_added');
            
            // Send notification to post author (if they're not the one commenting)
            if ($post['author_id'] != $user['id']) {
                $commenterName = $user['full_name'] ?: $user['username'];
                $message = $commenterName . " commented on your post: " . mb_substr($content, 0, 100) . (mb_strlen($content) > 100 ? '...' : '');
                notify($post['author_id'], 'comment', 'New Comment on Your Post', $message, $postId, $user['id']);
            }
            
            // Send notification to all admins
            $admins = fetchAll("SELECT id FROM users WHERE role = 'admin' AND id != ?", [$user['id']]);
            $commenterName = $user['full_name'] ?: $user['username'];
            $postTitle = mb_substr($post['title'], 0, 50) . (mb_strlen($post['title']) > 50 ? '...' : '');
            $message = $commenterName . " commented on post '" . $postTitle . "': " . mb_substr($content, 0, 80) . (mb_strlen($content) > 80 ? '...' : '');
            foreach ($admins as $admin) {
                notify($admin['id'], 'comment', 'New Comment', $message, $postId, $user['id']);
            }
            
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
            $companyId = $_SESSION['company_id'] ?? 1;
            
            // Filter notifications to only show those for posts in current company
            $notifs = fetchAll(
                "SELECT n.*, p.title as post_title FROM notifications n 
                 LEFT JOIN posts p ON n.post_id = p.id 
                 WHERE n.user_id = ? AND (p.company_id = ? OR p.id IS NULL) 
                 ORDER BY n.created_at DESC LIMIT 30",
                [$user['id'], $companyId]
            );
            $unread = fetchOne("SELECT COUNT(*) as c FROM notifications n LEFT JOIN posts p ON n.post_id = p.id WHERE n.user_id = ? AND n.is_read = 0 AND (p.company_id = ? OR p.id IS NULL)", [$user['id'], $companyId])['c'];
            
            sendResponse(true, ['notifications' => $notifs, 'unread_count' => $unread]);
            break;
        
        case 'mark_notification_read':
            requireAuth();
            $user = getCurrentUser();
            $input = json_decode(file_get_contents('php://input'), true);
            $companyId = $_SESSION['company_id'] ?? 1;
            
            if (!empty($input['mark_all'])) {
                executeQuery(
                    "UPDATE notifications n
                     LEFT JOIN posts p ON n.post_id = p.id
                     SET n.is_read = 1
                     WHERE n.user_id = ? AND (p.company_id = ? OR p.id IS NULL)",
                    [$user['id'], $companyId]
                );
            } elseif (!empty($input['id'])) {
                executeQuery(
                    "UPDATE notifications n
                     LEFT JOIN posts p ON n.post_id = p.id
                     SET n.is_read = 1
                     WHERE n.id = ? AND n.user_id = ? AND (p.company_id = ? OR p.id IS NULL)",
                    [$input['id'], $user['id'], $companyId]
                );
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
            if (!in_array($role, ['admin', 'staff', 'manager'])) $role = 'staff';
            
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
        
        // ===== PERSONAL IDEAS WORKSPACE =====
        
        case 'get_user_ideas':
            requireAuth();
            $user = getCurrentUser();
            $companyId = $_SESSION['company_id'] ?? 1;
            
            // Only fetch ideas owned by the current user
            $ideas = fetchAll(
                "SELECT * FROM staff_ideas 
                 WHERE user_id = ? AND company_id = ? 
                 ORDER BY updated_at DESC",
                [$user['id'], $companyId]
            );
            
            // Attach media to each idea
            foreach ($ideas as &$idea) {
                $idea['media'] = fetchAll(
                    "SELECT * FROM idea_media WHERE idea_id = ? ORDER BY is_primary DESC, id ASC",
                    [$idea['id']]
                );
            }
            
            sendResponse(true, $ideas);
            break;
        
        case 'create_idea':
            requireAuth();
            $user = getCurrentUser();
            $companyId = $_SESSION['company_id'] ?? 1;
            
            $input = json_decode(file_get_contents('php://input'), true);
            $title = sanitizeString(trim($input['title'] ?? ''), 255);
            $content = sanitizeString(trim($input['content'] ?? ''));
            
            if (empty($content)) sendResponse(false, null, 'Content is required', 400);
            
            executeQuery(
                "INSERT INTO staff_ideas (user_id, company_id, title, content) VALUES (?, ?, ?, ?)",
                [$user['id'], $companyId, $title ?: null, $content]
            );
            
            $ideaId = lastInsertId();
            $idea = fetchOne("SELECT * FROM staff_ideas WHERE id = ?", [$ideaId]);
            
            sendResponse(true, $idea, 'Idea created successfully', 201);
            break;
        
        case 'update_idea':
            requireAuth();
            $user = getCurrentUser();
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            $title = sanitizeString(trim($input['title'] ?? ''), 255);
            $content = sanitizeString(trim($input['content'] ?? ''));
            
            if (!$id) sendResponse(false, null, 'Idea ID required', 400);
            if (empty($content)) sendResponse(false, null, 'Content is required', 400);
            
            // Verify ownership
            $idea = fetchOne("SELECT * FROM staff_ideas WHERE id = ? AND user_id = ?", [$id, $user['id']]);
            if (!$idea) sendResponse(false, null, 'Idea not found or access denied', 404);
            
            executeQuery(
                "UPDATE staff_ideas SET title = ?, content = ?, updated_at = NOW() WHERE id = ?",
                [$title ?: null, $content, $id]
            );
            
            $updatedIdea = fetchOne("SELECT * FROM staff_ideas WHERE id = ?", [$id]);
            sendResponse(true, $updatedIdea, 'Idea updated successfully');
            break;
        
        case 'delete_idea':
            requireAuth();
            $user = getCurrentUser();
            
            $id = $_GET['id'] ?? 0;
            if (!$id) sendResponse(false, null, 'Idea ID required', 400);
            
            // Verify ownership
            $idea = fetchOne("SELECT * FROM staff_ideas WHERE id = ? AND user_id = ?", [$id, $user['id']]);
            if (!$idea) sendResponse(false, null, 'Idea not found or access denied', 404);
            
            // Delete media files from disk
            $media = fetchAll("SELECT file_path FROM idea_media WHERE idea_id = ?", [$id]);
            foreach ($media as $m) {
                $path = __DIR__ . '/' . $m['file_path'];
                if (file_exists($path)) unlink($path);
            }
            
            executeQuery("DELETE FROM staff_ideas WHERE id = ?", [$id]);
            sendResponse(true, null, 'Idea deleted successfully');
            break;
        
        case 'convert_idea_to_draft':
            requireAuth();
            $user = getCurrentUser();
            $companyId = $_SESSION['company_id'] ?? 1;
            
            $input = json_decode(file_get_contents('php://input'), true);
            $ideaId = $input['idea_id'] ?? 0;
            
            if (!$ideaId) sendResponse(false, null, 'Idea ID required', 400);
            
            // Verify ownership
            $idea = fetchOne("SELECT * FROM staff_ideas WHERE id = ? AND user_id = ?", [$ideaId, $user['id']]);
            if (!$idea) sendResponse(false, null, 'Idea not found or access denied', 404);
            
            // Create a new DRAFT post from the idea (clean start, no history)
            $title = $idea['title'] ?: 'Untitled Post';
            $content = $idea['content'];
            
            executeQuery(
                "INSERT INTO posts (company_id, title, content, platforms, status, author_id, created_at, updated_at) 
                 VALUES (?, ?, ?, '[]', 'DRAFT', ?, NOW(), NOW())",
                [$companyId, $title, $content, $user['id']]
            );
            
            $postId = lastInsertId();
            
            // Copy media from idea to post
            $ideaMedia = fetchAll("SELECT * FROM idea_media WHERE idea_id = ?", [$ideaId]);
            $copiedMediaCount = 0;
            foreach ($ideaMedia as $m) {
                $fileType = str_starts_with($m['file_type'] ?? '', 'image/') ? 'image' : 'video';
                try {
                    executeQuery(
                        "INSERT INTO media_files (post_id, original_name, file_name, file_path, file_type, mime_type, file_size, is_primary, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$postId, $m['file_name'] ?? '', basename($m['file_path'] ?? ''), $m['file_path'] ?? '', $fileType, $m['file_type'] ?? '', $m['file_size'] ?? 0, $m['is_primary'] ?? 0, $user['id']]
                    );
                    $copiedMediaCount++;
                } catch (Exception $e) {
                    error_log("Failed to copy idea media: " . $e->getMessage());
                }
            }
            
            // Log the creation (fresh start)
            logActivity($postId, $user['id'], 'created', null, null, 'Created from personal idea');
            
            // Optionally delete the idea after converting (user can also keep it)
            $deleteAfterConvert = $input['delete_idea'] ?? false;
            if ($deleteAfterConvert) {
                // Note: We don't delete the files since they're now used by the post
                executeQuery("DELETE FROM idea_media WHERE idea_id = ?", [$ideaId]);
                executeQuery("DELETE FROM staff_ideas WHERE id = ?", [$ideaId]);
            }
            
            $post = fetchOne("SELECT * FROM posts WHERE id = ?", [$postId]);
            $post['media'] = fetchAll("SELECT * FROM media_files WHERE post_id = ?", [$postId]);
            sendResponse(true, $post, 'Idea converted to draft successfully', 201);
            break;
        
        case 'upload_idea_media':
            requireAuth();
            $user = getCurrentUser();
            
            $ideaId = $_POST['idea_id'] ?? 0;
            if (!$ideaId) sendResponse(false, null, 'Idea ID required', 400);
            
            // Verify ownership
            $idea = fetchOne("SELECT * FROM staff_ideas WHERE id = ? AND user_id = ?", [$ideaId, $user['id']]);
            if (!$idea) sendResponse(false, null, 'Idea not found or access denied', 404);
            
            if (empty($_FILES['file'])) sendResponse(false, null, 'No file uploaded', 400);
            
            $file = $_FILES['file'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
            
            if (!in_array($file['type'], $allowed)) {
                sendResponse(false, null, 'Invalid file type. Allowed: JPG, PNG, GIF, WebP, MP4, WebM', 400);
            }
            
            if ($file['size'] > 100 * 1024 * 1024) {
                sendResponse(false, null, 'File too large. Max 100MB', 400);
            }
            
            // Create uploads/ideas directory if it doesn't exist
            $uploadDir = __DIR__ . '/uploads/ideas/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'idea_' . $ideaId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $filePath = 'uploads/ideas/' . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                sendResponse(false, null, 'Failed to save file', 500);
            }
            
            // Check if this is first media (make it primary)
            $existingMedia = fetchOne("SELECT COUNT(*) as c FROM idea_media WHERE idea_id = ?", [$ideaId]);
            $isPrimary = ($existingMedia['c'] == 0) ? 1 : 0;
            
            executeQuery(
                "INSERT INTO idea_media (idea_id, file_path, file_type, file_name, file_size, is_primary) VALUES (?, ?, ?, ?, ?, ?)",
                [$ideaId, $filePath, $file['type'], $file['name'], $file['size'], $isPrimary]
            );
            
            $mediaId = lastInsertId();
            $media = fetchOne("SELECT * FROM idea_media WHERE id = ?", [$mediaId]);
            
            sendResponse(true, $media, 'File uploaded successfully', 201);
            break;
        
        case 'delete_idea_media':
            requireAuth();
            $user = getCurrentUser();
            
            $mediaId = $_GET['id'] ?? 0;
            if (!$mediaId) sendResponse(false, null, 'Media ID required', 400);
            
            // Get media and verify ownership through idea
            $media = fetchOne(
                "SELECT m.*, i.user_id FROM idea_media m 
                 JOIN staff_ideas i ON m.idea_id = i.id 
                 WHERE m.id = ?",
                [$mediaId]
            );
            
            if (!$media || $media['user_id'] != $user['id']) {
                sendResponse(false, null, 'Media not found or access denied', 404);
            }
            
            // Delete file from disk
            $path = __DIR__ . '/' . $media['file_path'];
            if (file_exists($path)) unlink($path);
            
            executeQuery("DELETE FROM idea_media WHERE id = ?", [$mediaId]);
            sendResponse(true, null, 'Media deleted successfully');
            break;
        
        // ===== ACTIVITY LOGS (Manager Only) =====
        
        case 'get_all_logs':
            requireAuth();
            $user = getCurrentUser();
            if ($user['role'] !== 'manager') sendResponse(false, null, 'Manager access required', 403);
            
            $companyId = $_SESSION['company_id'] ?? 1;
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 50)));
            $offset = ($page - 1) * $limit;
            
            // Build filter conditions
            $where = ["(p.company_id = ? OR al.post_id IS NULL)"];
            $params = [$companyId];
            
            // Filter by action type
            if (!empty($_GET['action_type'])) {
                $where[] = "al.action = ?";
                $params[] = $_GET['action_type'];
            }
            
            // Filter by user
            if (!empty($_GET['user_id'])) {
                $where[] = "al.user_id = ?";
                $params[] = intval($_GET['user_id']);
            }
            
            // Filter by date range
            if (!empty($_GET['date_from'])) {
                $where[] = "al.created_at >= ?";
                $params[] = $_GET['date_from'] . ' 00:00:00';
            }
            if (!empty($_GET['date_to'])) {
                $where[] = "al.created_at <= ?";
                $params[] = $_GET['date_to'] . ' 23:59:59';
            }
            
            // Search in description
            if (!empty($_GET['search'])) {
                $where[] = "(al.description LIKE ? OR p.title LIKE ? OR u.full_name LIKE ?)";
                $term = '%' . $_GET['search'] . '%';
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Get total count
            $total = (int)fetchOne(
                "SELECT COUNT(*) as c FROM activity_log al 
                 JOIN users u ON al.user_id = u.id 
                 LEFT JOIN posts p ON al.post_id = p.id 
                 WHERE $whereClause",
                $params
            )['c'];
            
            // Get logs
            $logParams = array_merge($params, [$limit, $offset]);
            $logs = fetchAll(
                "SELECT al.*, u.username, u.full_name as user_full_name, 
                        p.title as post_title, p.status as post_status
                 FROM activity_log al 
                 JOIN users u ON al.user_id = u.id 
                 LEFT JOIN posts p ON al.post_id = p.id 
                 WHERE $whereClause
                 ORDER BY al.created_at DESC 
                 LIMIT ? OFFSET ?",
                $logParams
            );
            
            // Get available action types for filter
            $actionTypes = fetchAll(
                "SELECT DISTINCT action FROM activity_log ORDER BY action"
            );
            
            // Get users for filter
            $logUsers = fetchAll(
                "SELECT DISTINCT u.id, u.full_name, u.username 
                 FROM activity_log al JOIN users u ON al.user_id = u.id 
                 ORDER BY u.full_name"
            );
            
            sendResponse(true, [
                'logs' => $logs,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
                'action_types' => array_column($actionTypes, 'action'),
                'users' => $logUsers
            ]);
            break;
        
        default:
            sendResponse(false, null, 'Invalid action', 400);
    }
    
    
} catch (Throwable $e) {
    error_log("API Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
}
