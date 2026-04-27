<?php
/**
 * BroMan Social Configuration
 * 
 * IMPORTANT: Update these settings for your hosting environment
 * Keep this file secure and never commit sensitive credentials to version control
 */

if (!function_exists('isHttpsRequest')) {
    function isHttpsRequest() {
        if (PHP_SAPI === 'cli') {
            return false;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
        }

        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        return (int)($_SERVER['SERVER_PORT'] ?? 80) === 443;
    }
}

if (!function_exists('getAppBaseUrl')) {
    function getAppBaseUrl() {
        if (PHP_SAPI === 'cli') {
            return 'http://social.local';
        }

        $scheme = isHttpsRequest() ? 'https' : 'http';
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'social.local';
        $host = trim(explode(',', (string) $host)[0]);

        return $scheme . '://' . $host;
    }
}

if (!function_exists('loadLocalConfigOverrides')) {
    function loadLocalConfigOverrides() {
        $configLocalPath = __DIR__ . '/config.local.php';

        if (!is_file($configLocalPath)) {
            return [];
        }

        $overrides = require $configLocalPath;
        return is_array($overrides) ? $overrides : [];
    }
}

if (!function_exists('getConfigOverride')) {
    function getConfigOverride(array $overrides, $key, $default = null) {
        if (array_key_exists($key, $overrides) && $overrides[$key] !== null && $overrides[$key] !== '') {
            return $overrides[$key];
        }

        $envValue = getenv(strtoupper($key));
        if ($envValue !== false && $envValue !== '') {
            return $envValue;
        }

        return $default;
    }
}

$configOverrides = loadLocalConfigOverrides();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'socialsync_db');
define('DB_USER', 'root');
define('DB_PASS', '1671');
define('DB_CHARSET', 'utf8mb4');

// Application URL (for CORS and redirects)
define('BASE_URL', getAppBaseUrl());

// Session Configuration
define('SESSION_NAME', 'SOCIALSYNC_SESSION');
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 days in seconds
define('SESSION_COOKIE_PATH', '/');
define('SESSION_COOKIE_SECURE', isHttpsRequest());
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Lax');

// Application Settings
define('APP_NAME', 'BroMan Social');
define('TIMEZONE', 'Africa/Cairo');
define('DATE_FORMAT', 'Y-m-d H:i:s');

// Security Settings
define('PASSWORD_MIN_LENGTH', 8);
define('HASH_ALGO', PASSWORD_DEFAULT);    // Uses bcrypt by default

// Pagination & Limits
define('POSTS_PER_PAGE', 50);
define('MAX_UPLOAD_SIZE', 5242880);       // 5MB in bytes

// Week Start Day (1 = Monday, 0 = Sunday)
define('WEEK_START_DAY', 1);

// Security: Rate Limiting
define('RATE_LIMIT_LOGIN_MAX', 5);         // Max login attempts per window
define('RATE_LIMIT_LOGIN_WINDOW', 60);     // Window in seconds (1 minute)
define('RATE_LIMIT_API_MAX', 100);         // Max API requests per window
define('RATE_LIMIT_API_WINDOW', 60);       // Window in seconds (1 minute)

// Security: CSRF Protection
define('CSRF_TOKEN_LENGTH', 32);           // Token length in bytes
define('CSRF_TOKEN_LIFETIME', 3600);       // Token lifetime in seconds (1 hour)

// Environment (development / production)
define('ENVIRONMENT', 'production');

// Error Reporting (disable in production)
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set(TIMEZONE);

function startAppSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', (string) SESSION_LIFETIME);
    ini_set('session.use_strict_mode', '1');

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => SESSION_COOKIE_PATH,
        'secure' => SESSION_COOKIE_SECURE,
        'httponly' => SESSION_COOKIE_HTTPONLY,
        'samesite' => SESSION_COOKIE_SAMESITE,
    ]);

    session_start();

    // Sliding expiration: extend the cookie lifetime on active use.
    if (!headers_sent() && session_id() !== '') {
        setcookie(session_name(), session_id(), [
            'expires' => time() + SESSION_LIFETIME,
            'path' => SESSION_COOKIE_PATH,
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE,
        ]);
    }
}

function destroyAppSession() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => SESSION_COOKIE_PATH,
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE,
        ]);
    }

    session_destroy();
}

define('ONESIGNAL_APP_ID', getConfigOverride($configOverrides, 'onesignal_app_id', '9748ea3b-8a42-4279-b664-e6ab00d9756e'));
define('ONESIGNAL_REST_KEY', getConfigOverride($configOverrides, 'onesignal_rest_key', ''));
