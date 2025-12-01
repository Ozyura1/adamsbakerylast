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
        if (!isset($file)) {
            return ['valid' => false, 'error' => 'File tidak ditemukan'];
        }

        // Check upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi batas server)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar',
                UPLOAD_ERR_PARTIAL => 'File tidak sepenuhnya terupload',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dipilih',
                UPLOAD_ERR_NO_TMP_DIR => 'Direktori sementara tidak tersedia',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file',
                UPLOAD_ERR_EXTENSION => 'Ekstensi file tidak diizinkan',
            ];
            return ['valid' => false, 'error' => $uploadErrors[$file['error']] ?? 'Upload error'];
        }

        // Check file size
        $maxSize = $maxSize ?? MAX_FILE_SIZE;
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Ukuran file melebihi limit (max 5MB)'];
        }

        // Check if file exists
        if (!file_exists($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'File upload tidak valid'];
        }

        $allowedMimes = $allowedMimes ?? ALLOWED_MIME_TYPES;
        
        // Try finfo first (most reliable)
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } else {
            // Fallback to mime_content_type if finfo not available
            $fileMime = mime_content_type($file['tmp_name']);
        }
        
        if (!in_array($fileMime, $allowedMimes)) {
            return ['valid' => false, 'error' => "Tipe file tidak diizinkan. Hanya JPG, PNG, GIF yang diperbolehkan. (Detected: $fileMime)"];
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
