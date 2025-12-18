<?php
/**
 * SocialSync Configuration
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
define('BASE_URL', 'http://socialsync.local');

// Session Configuration
define('SESSION_NAME', 'SOCIALSYNC_SESSION');
define('SESSION_LIFETIME', 3600 * 24);    // 24 hours in seconds
define('SESSION_COOKIE_PATH', '/');
define('SESSION_COOKIE_SECURE', false);   // Set to TRUE if using HTTPS
define('SESSION_COOKIE_HTTPONLY', true);

// Application Settings
define('APP_NAME', 'SocialSync');
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
