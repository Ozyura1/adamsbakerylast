<?php
/**
 * Input sanitization and output escaping utilities
 * Protects against XSS, SQL Injection, and other common vulnerabilities
 */

class InputSanitizer
{
    /**
     * Sanitize string input - remove extra spaces and trim
     */
    public static function sanitizeString($input)
    {
        if (!is_string($input)) {
            return '';
        }
        return trim($input);
    }

    /**
     * Sanitize email input
     */
    public static function sanitizeEmail($input)
    {
        $input = self::sanitizeString($input);
        $input = filter_var($input, FILTER_SANITIZE_EMAIL);
        return strtolower($input);
    }

    /**
     * Sanitize integer input
     */
    public static function sanitizeInt($input)
    {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize phone number - keep only digits and +
     */
    public static function sanitizePhone($input)
    {
        return preg_replace('/[^0-9+\-\s]/', '', $input);
    }

    /**
     * Validate email format
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number format
     */
    public static function validatePhone($phone)
    {
        $phone = self::sanitizePhone($phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }

    /**
     * Escape output for HTML display
     */
    public static function escapeHtml($output)
    {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape output for HTML attributes
     */
    public static function escapeAttr($output)
    {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape output for JavaScript
     */
    public static function escapeJs($output)
    {
        return json_encode($output);
    }

    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedMimes = null, $maxSize = null)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'File upload error'];
        }

        // Check file size
        $maxSize = $maxSize ?? MAX_FILE_SIZE;
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File size exceeds limit'];
        }

        // Check MIME type
        $allowedMimes = $allowedMimes ?? ALLOWED_MIME_TYPES;
        $fileMime = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileMime, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Invalid file type'];
        }

        return ['valid' => true];
    }

    /**
     * Generate safe file name
     */
    public static function generateSafeFileName($originalName)
    {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
        
        return 'file_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    }
}
?>
