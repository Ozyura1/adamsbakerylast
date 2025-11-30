<?php
/**
 * Helper functions for common operations
 * Reduces code duplication and improves maintainability
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/sanitizer.php';
require_once __DIR__ . '/db.php';

/**
 * Format currency (Rupiah)
 */
function formatCurrency($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format date
 */
function formatDate($date, $format = 'd M Y')
{
    try {
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Format datetime with relative time
 */
function formatDatetime($datetime)
{
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Render alert HTML
 */
function renderAlert($message, $type = 'success', $dismissible = true)
{
    $allowedTypes = ['success', 'error', 'warning', 'info'];
    $type = in_array($type, $allowedTypes) ? $type : 'info';
    
    $html = '<div class="alert alert-' . $type . '">';
    $html .= InputSanitizer::escapeHtml($message);
    
    if ($dismissible) {
        $html .= ' <button type="button" class="close" onclick="this.parentElement.style.display=\'none\';">&times;</button>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Get user cart summary
 */
function getCartSummary()
{
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [
            'count' => 0,
            'total' => 0,
            'items' => []
        ];
    }

    $conn = Database::getInstance()->getConnection();
    $cartItems = [];
    $totalAmount = 0;

    foreach ($_SESSION['cart'] as $key => $item) {
        $table = $item['type'] === 'product' ? 'products' : 'packages';
        $itemId = (int)$item['id'];
        
        $stmt = $conn->prepare("SELECT id, nama, harga FROM $table WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $subtotal = $row['harga'] * $item['quantity'];
            
            $cartItems[$key] = [
                'name' => $row['nama'],
                'price' => $row['harga'],
                'quantity' => $item['quantity'],
                'type' => $item['type'],
                'subtotal' => $subtotal
            ];
            
            $totalAmount += $subtotal;
        }
        
        $stmt->close();
    }

    return [
        'count' => count($cartItems),
        'total' => $totalAmount,
        'items' => $cartItems
    ];
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success')
{
    $_SESSION['alert_message'] = $message;
    $_SESSION['alert_type'] = $type;
    header('Location: ' . $url);
    exit();
}

/**
 * Display session alert if exists
 */
function displaySessionAlert()
{
    if (isset($_SESSION['alert_message'])) {
        $message = $_SESSION['alert_message'];
        $type = $_SESSION['alert_type'] ?? 'info';
        
        echo renderAlert($message, $type);
        
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_type']);
    }
}

/**
 * Generate star rating HTML
 */
function renderStarRating($rating, $maxStars = 5)
{
    $html = '<div class="rating">';
    
    for ($i = 1; $i <= $maxStars; $i++) {
        $class = $i <= $rating ? '' : 'empty';
        $html .= '<span class="' . $class . '">★</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Check if user is authenticated
 */
function isAuthenticated()
{
    return isset($_SESSION['customer_id']);
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth($redirectUrl = 'checkout.php')
{
    if (!isAuthenticated()) {
        $_SESSION['redirect_after_login'] = $redirectUrl;
        header('Location: customer_auth.php');
        exit();
    }
}

/**
 * Get current user data
 */
function getCurrentUser()
{
    if (!isAuthenticated()) {
        return null;
    }

    $conn = Database::getInstance()->getConnection();
    $customerId = (int)$_SESSION['customer_id'];
    
    $stmt = $conn->prepare("SELECT * FROM customer_users WHERE id = ?");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $user = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();
    
    return $user;
}

/**
 * Log activity for audit trail
 */
function logActivity($action, $details = '')
{
    try {
        $conn = Database::getInstance()->getConnection();
        $userId = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $userId, $action, $details, $ipAddress);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

/**
 * Handle pagination
 */
function getPaginationInfo($totalItems, $itemsPerPage = 10, $currentPage = 1)
{
    $currentPage = max(1, (int)$currentPage);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'limit' => $itemsPerPage,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Generate HTML pagination links
 */
function renderPaginationLinks($currentPage, $totalPages, $baseUrl)
{
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<div class="pagination">';
    
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=1">First</a> ';
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">Previous</a> ';
    }
    
    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="current-page">' . $i . '</span> ';
        } else {
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a> ';
        }
    }
    
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">Next</a> ';
        $html .= '<a href="' . $baseUrl . '?page=' . $totalPages . '">Last</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>
