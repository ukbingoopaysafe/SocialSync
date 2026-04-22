<?php
/**
 * BroMan Social Configuration
 * 
 * IMPORTANT: Update these settings for your hosting environment
 * Keep this file secure and never commit sensitive credentials to version control
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'socialsync_db');
define('DB_USER', 'root');
define('DB_PASS', '1671');
define('DB_CHARSET', 'utf8mb4');

// Application URL (for CORS and redirects)
define('BASE_URL', 'http://social.local');

// Session Configuration
define('SESSION_NAME', 'SOCIALSYNC_SESSION');
define('SESSION_LIFETIME', 3600 * 24);    // 24 hours in seconds
define('SESSION_COOKIE_PATH', '/');
define('SESSION_COOKIE_SECURE', false);   // Set to TRUE if using HTTPS
define('SESSION_COOKIE_HTTPONLY', true);

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

define('ONESIGNAL_APP_ID', '9748ea3b-8a42-4279-b664-e6ab00d9756e');
define('ONESIGNAL_REST_KEY', 'os_v2_app_s5eouo4kijbhtnte42vqbwlvn3uyaujabtru5544wqovvkgyjfs7h5lufbu3jhmic4ozgrfpoh7aofou7p2vief6wglz7xyypeymecq');