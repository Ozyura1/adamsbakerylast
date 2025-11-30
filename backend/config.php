<?php
/**
 * Configuration file
 * Centralized database and application configuration
 */

// Database Configuration
define('DB_HOST', '192.168.122.288');
define('DB_USER', 'adamsbakery');
define('DB_PASS', 'Adamsbakery123!');
define('DB_NAME', 'adamsbakery');

// Application Configuration
define('APP_NAME', 'Adam Bakery');
define('APP_DEBUG', true);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('CSRF_TOKEN_LENGTH', 32);

// Security Headers
function setSecurityHeaders()
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self'; frame-src https://www.google.com;");
}

// Initialize Session Securely
function initializeSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_samesite', 'Lax');
        session_start();
    }
}
?>