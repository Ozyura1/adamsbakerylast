<?php
/**
 * Add to cart handler
 * Handles adding products/packages to session cart
 */

require_once 'includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('index.php', 'Invalid request', 'error');
}

$itemType = InputSanitizer::sanitizeString($_POST['item_type'] ?? '');
$itemId = (int)($_POST['item_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);
$redirect = InputSanitizer::sanitizeString($_POST['redirect'] ?? 'products.php');

// Validate item type
$allowedTypes = ['product', 'package'];
if (!in_array($itemType, $allowedTypes)) {
    redirectWithMessage('products.php', 'Tipe item tidak valid', 'error');
}

// Validate item ID
if ($itemId <= 0) {
    redirectWithMessage('products.php', 'ID item tidak valid', 'error');
}

// Validate quantity
$quantity = max(1, min(100, $quantity));

// Whitelist redirect URLs to prevent open redirect
$allowedRedirects = ['index.php', 'products.php', 'packages.php', 'checkout.php', 'view_reviews.php'];
$redirectAllowed = false;

foreach ($allowedRedirects as $allowed) {
    if (strpos($redirect, $allowed) === 0) {
        $redirectAllowed = true;
        break;
    }
}

if (!$redirectAllowed) {
    $redirect = 'products.php';
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cartKey = $itemType . '_' . $itemId;

// Add or update cart item
if (isset($_SESSION['cart'][$cartKey])) {
    $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$cartKey] = [
        'type' => $itemType,
        'id' => $itemId,
        'quantity' => $quantity
    ];
}

// Log activity
logActivity('ADD_TO_CART', "Added $quantity $itemType(s) with ID $itemId");

// Redirect with success indicator
if (strpos($redirect, '?') !== false) {
    $redirect .= '&added=1';
} else {
    $redirect .= '?added=1';
}

header('Location: ' . $redirect);
exit();
?>
