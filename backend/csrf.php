<?php
/**
 * CSRF Token generation and validation
 * Protects against Cross-Site Request Forgery attacks
 */

class CSRFToken
{
    const TOKEN_LENGTH = CSRF_TOKEN_LENGTH;
    const SESSION_KEY = 'csrf_token';

    /**
     * Generate a new CSRF token
     */
    public static function generate()
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Get current CSRF token
     */
    public static function get()
    {
        return isset($_SESSION[self::SESSION_KEY]) ? $_SESSION[self::SESSION_KEY] : '';
    }

    /**
     * Verify CSRF token from POST/REQUEST
     */
    public static function verify($token)
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        
        return hash_equals($_SESSION[self::SESSION_KEY], $token ?? '');
    }

    /**
     * Generate HTML hidden input for forms
     */
    public static function getField()
    {
        return '<input type="hidden" name="csrf_token" value="' . self::escapeAttr(self::generate()) . '">';
    }

    private static function escapeAttr($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

// Backward compatibility wrappers
function generateCSRFToken()
{
    return CSRFToken::generate();
}

function getCSRFToken()
{
    return CSRFToken::get();
}

function verifyCSRFToken($token)
{
    return CSRFToken::verify($token);
}
?>
