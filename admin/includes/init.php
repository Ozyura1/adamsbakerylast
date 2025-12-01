<?php
/**
 * Application initialization file
 * Included at the beginning of all PHP files for consistent setup
 */

// Load configuration
require_once __DIR__ . '/../backend/config.php';

// Set security headers
setSecurityHeaders();

// Initialize session
initializeSession();

// Load all backend utilities
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/sanitizer.php';
require_once __DIR__ . '/../backend/csrf.php';
require_once __DIR__ . '/../backend/validator.php';
require_once __DIR__ . '/../backend/helpers.php';

// Error handling for production
if (!APP_DEBUG) {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}
?>
