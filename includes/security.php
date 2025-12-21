<?php
/**
 * Security Module for BroMan Social
 * 
 * Provides Rate Limiting, CSRF Protection, and Input Validation
 * 
 * @version 1.0.0
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// ============================================================================
// RATE LIMITING
// ============================================================================

/**
 * Check if request should be rate limited
 * 
 * @param string $action Action type (login, api, etc.)
 * @param string|null $identifier Custom identifier (IP or user ID)
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_in' => int]
 */
function checkRateLimit($action = 'api', $identifier = null) {
    // Get rate limit config based on action
    $config = getRateLimitConfig($action);
    $maxAttempts = $config['max'];
    $windowSeconds = $config['window'];
    
    // Use IP as default identifier
    if ($identifier === null) {
        $identifier = getClientIP();
    }
    
    // Create unique key for this action + identifier
    $key = $action . ':' . $identifier;
    
    try {
        // Clean up old records first (run occasionally)
        if (rand(1, 100) <= 5) { // 5% chance to run cleanup
            cleanupRateLimits();
        }
        
        // Get current rate limit record
        $record = fetchOne(
            "SELECT * FROM rate_limits WHERE identifier = ? AND action = ?",
            [$key, $action]
        );
        
        $now = time();
        
        if (!$record) {
            // First request - create new record
            executeQuery(
                "INSERT INTO rate_limits (identifier, action, attempts, first_attempt, last_attempt) 
                 VALUES (?, ?, 1, NOW(), NOW())",
                [$key, $action]
            );
            return [
                'allowed' => true,
                'remaining' => $maxAttempts - 1,
                'reset_in' => $windowSeconds
            ];
        }
        
        $firstAttempt = strtotime($record['first_attempt']);
        $windowExpiry = $firstAttempt + $windowSeconds;
        
        if ($now > $windowExpiry) {
            // Window expired - reset counter
            executeQuery(
                "UPDATE rate_limits SET attempts = 1, first_attempt = NOW(), last_attempt = NOW() 
                 WHERE identifier = ? AND action = ?",
                [$key, $action]
            );
            return [
                'allowed' => true,
                'remaining' => $maxAttempts - 1,
                'reset_in' => $windowSeconds
            ];
        }
        
        // Within window - check attempts
        $attempts = (int)$record['attempts'];
        
        if ($attempts >= $maxAttempts) {
            // Rate limit exceeded
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_in' => $windowExpiry - $now
            ];
        }
        
        // Increment attempts
        executeQuery(
            "UPDATE rate_limits SET attempts = attempts + 1, last_attempt = NOW() 
             WHERE identifier = ? AND action = ?",
            [$key, $action]
        );
        
        return [
            'allowed' => true,
            'remaining' => $maxAttempts - $attempts - 1,
            'reset_in' => $windowExpiry - $now
        ];
        
    } catch (Exception $e) {
        // On error, allow request (fail open for availability)
        error_log("Rate limit error: " . $e->getMessage());
        return ['allowed' => true, 'remaining' => $maxAttempts, 'reset_in' => $windowSeconds];
    }
}

/**
 * Get rate limit configuration for action
 */
function getRateLimitConfig($action) {
    $configs = [
        'login' => [
            'max' => defined('RATE_LIMIT_LOGIN_MAX') ? RATE_LIMIT_LOGIN_MAX : 5,
            'window' => defined('RATE_LIMIT_LOGIN_WINDOW') ? RATE_LIMIT_LOGIN_WINDOW : 60
        ],
        'api' => [
            'max' => defined('RATE_LIMIT_API_MAX') ? RATE_LIMIT_API_MAX : 100,
            'window' => defined('RATE_LIMIT_API_WINDOW') ? RATE_LIMIT_API_WINDOW : 60
        ],
        'upload' => [
            'max' => 20,
            'window' => 60
        ]
    ];
    
    return $configs[$action] ?? $configs['api'];
}

/**
 * Clean up expired rate limit records
 */
function cleanupRateLimits() {
    try {
        // Delete records older than 1 hour
        executeQuery(
            "DELETE FROM rate_limits WHERE last_attempt < DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
    } catch (Exception $e) {
        error_log("Rate limit cleanup error: " . $e->getMessage());
    }
}

/**
 * Get client IP address
 */
function getClientIP() {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            // Handle comma-separated IPs (X-Forwarded-For)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Enforce rate limit - sends response and exits if exceeded
 */
function enforceRateLimit($action = 'api', $identifier = null) {
    $result = checkRateLimit($action, $identifier);
    
    if (!$result['allowed']) {
        http_response_code(429);
        header('Retry-After: ' . $result['reset_in']);
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . $result['reset_in']);
        
        echo json_encode([
            'success' => false,
            'message' => 'Too many requests. Please try again in ' . $result['reset_in'] . ' seconds.',
            'retry_after' => $result['reset_in']
        ]);
        exit;
    }
    
    // Add rate limit headers
    header('X-RateLimit-Remaining: ' . $result['remaining']);
    header('X-RateLimit-Reset: ' . $result['reset_in']);
}

// ============================================================================
// CSRF PROTECTION
// ============================================================================

/**
 * Generate CSRF token for current session
 */
function generateCSRFToken() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return null;
    }
    
    $tokenLength = defined('CSRF_TOKEN_LENGTH') ? CSRF_TOKEN_LENGTH : 32;
    
    // Generate new token if not exists or expired
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time']) > (defined('CSRF_TOKEN_LIFETIME') ? CSRF_TOKEN_LIFETIME : 3600)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes($tokenLength));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from request
 */
function validateCSRFToken($token = null) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }
    
    // Get token from parameter, header, or POST data
    if ($token === null) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                 $_POST['csrf_token'] ?? 
                 null;
        
        // Also check JSON body for API requests
        if ($token === null) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $input = json_decode(file_get_contents('php://input'), true);
                $token = $input['csrf_token'] ?? null;
            }
        }
    }
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Constant-time comparison to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Enforce CSRF protection - sends response and exits if invalid
 * 
 * @param array $exemptActions Actions that don't require CSRF
 */
function enforceCSRF($exemptActions = []) {
    // Skip for GET, HEAD, OPTIONS requests
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
        return;
    }
    
    // Check if current action is exempt
    $action = $_GET['action'] ?? '';
    $defaultExempt = ['login', 'get_companies', 'get_user', 'logout'];
    $allExempt = array_merge($defaultExempt, $exemptActions);
    
    if (in_array($action, $allExempt)) {
        return;
    }
    
    // Validate token
    if (!validateCSRFToken()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or missing CSRF token. Please refresh the page.',
            'code' => 'CSRF_INVALID'
        ]);
        exit;
    }
}

/**
 * Get CSRF token meta tag for HTML pages
 */
function getCSRFMetaTag() {
    $token = generateCSRFToken();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================

/**
 * Sanitize string input
 */
function sanitizeString($input, $maxLength = 0) {
    if (!is_string($input)) {
        return '';
    }
    
    // Remove null bytes
    $input = str_replace("\0", '', $input);
    
    // Trim whitespace
    $input = trim($input);
    
    // Remove control characters except newlines and tabs
    $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
    
    // Limit length if specified
    if ($maxLength > 0 && mb_strlen($input) > $maxLength) {
        $input = mb_substr($input, 0, $maxLength);
    }
    
    return $input;
}

/**
 * Sanitize HTML - escape for safe output
 */
function sanitizeHTML($input) {
    return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Sanitize for safe HTML output (strip tags)
 */
function stripHTML($input) {
    return strip_tags($input);
}

/**
 * Validate email address
 */
function validateEmail($email) {
    $email = sanitizeString($email, 255);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate integer
 */
function validateInt($value, $min = null, $max = null) {
    $value = filter_var($value, FILTER_VALIDATE_INT);
    
    if ($value === false) {
        return false;
    }
    
    if ($min !== null && $value < $min) {
        return false;
    }
    
    if ($max !== null && $value > $max) {
        return false;
    }
    
    return true;
}

/**
 * Validate and sanitize username
 */
function validateUsername($username) {
    $username = sanitizeString($username, 50);
    
    if (strlen($username) < 3) {
        return false;
    }
    
    // Allow alphanumeric, underscore, and dot
    if (!preg_match('/^[a-zA-Z0-9_.]+$/', $username)) {
        return false;
    }
    
    return $username;
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    $minLength = defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8;
    
    if (strlen($password) < $minLength) {
        return [
            'valid' => false,
            'message' => "Password must be at least {$minLength} characters"
        ];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Sanitize filename for safe storage
 */
function sanitizeFilename($filename) {
    // Get extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    
    // Remove any path components
    $name = basename($name);
    
    // Replace dangerous characters
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
    
    // Limit length
    $name = substr($name, 0, 100);
    
    return $name . ($ext ? '.' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ext)) : '');
}

/**
 * Validate JSON input
 */
function validateJSONInput() {
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        return [];
    }
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON input'
        ]);
        exit;
    }
    
    return $data;
}

/**
 * Validate required fields in input
 */
function validateRequired($data, $fields) {
    $missing = [];
    
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        return [
            'valid' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missing),
            'missing' => $missing
        ];
    }
    
    return ['valid' => true, 'message' => '', 'missing' => []];
}

/**
 * Sanitize array of inputs
 */
function sanitizeInputArray($data, $rules = []) {
    $sanitized = [];
    
    foreach ($data as $key => $value) {
        $rule = $rules[$key] ?? 'string';
        
        switch ($rule) {
            case 'int':
                $sanitized[$key] = filter_var($value, FILTER_VALIDATE_INT) ?: 0;
                break;
            case 'bool':
                $sanitized[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'email':
                $sanitized[$key] = filter_var($value, FILTER_VALIDATE_EMAIL) ?: '';
                break;
            case 'html':
                $sanitized[$key] = sanitizeHTML($value);
                break;
            case 'raw':
                $sanitized[$key] = $value; // No sanitization
                break;
            case 'string':
            default:
                $sanitized[$key] = sanitizeString($value);
                break;
        }
    }
    
    return $sanitized;
}
