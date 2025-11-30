<?php
/**
 * Header template with navigation
 * Refactored to use consistent styling and security best practices
 */

require_once __DIR__ . '/init.php';

$isAuthenticated = isAuthenticated();
$currentPage = basename($_SERVER['PHP_SELF']);
$cartSummary = getCartSummary();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Roti Segar & Kue Berkualitas</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <div class="logo-section">
                <img src="assets/logoadambakery.png" alt="<?php echo APP_NAME; ?> Logo" class="logo">
                <div class="brand-info">
                    <h1 class="brand-name"><?php echo APP_NAME; ?></h1>
                    <p class="brand-tagline">Kelezatan Tradisional dengan Sentuhan Modern</p>
                </div>
            </div>
            
            <nav class="main-navigation">
                <a href="index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Beranda</a>
                <a href="products.php" class="nav-link <?php echo $currentPage === 'products.php' ? 'active' : ''; ?>">Produk</a>
                <a href="packages.php" class="nav-link <?php echo $currentPage === 'packages.php' ? 'active' : ''; ?>">Paket Spesial</a>
                <a href="view_reviews.php" class="nav-link <?php echo $currentPage === 'view_reviews.php' ? 'active' : ''; ?>">Ulasan</a>
                <a href="contact.php" class="nav-link <?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">Kontak</a>
                <a href="check_review_status.php" class="nav-link <?php echo $currentPage === 'check_review_status.php' ? 'active' : ''; ?>">Beri Ulasan</a>
            </nav>

            <div class="header-actions">
                <?php if ($isAuthenticated): ?>
                    <span class="user-welcome">
                        Halo, <?php echo InputSanitizer::escapeHtml($_SESSION['customer_name']); ?>
                    </span>
                    <a href="customer_logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="customer_auth.php" class="btn-login">Login/Daftar</a>
                <?php endif; ?>
                
                <a href="checkout.php" class="cart-link">
                    <span class="cart-icon">🛒</span>
                    <span class="cart-count"><?php echo $cartSummary['count']; ?></span>
                </a>
            </div>
        </div>
    </header>
    <hr class="header-divider">
    <main class="page-content">
