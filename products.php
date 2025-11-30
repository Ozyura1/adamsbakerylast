<?php
/**
 * Products listing page
 * Displays products with filtering and add to cart functionality
 */

require_once 'includes/init.php';

define('CATEGORY_ALL', 'all');

$selectedCategory = InputSanitizer::sanitizeString($_GET['category'] ?? CATEGORY_ALL);

$conn = Database::getInstance()->getConnection();

// Define allowed categories
$categories = [
    'all' => 'Semua Produk',
    'Roti Manis' => 'Roti Manis',
    'Roti Gurih' => 'Roti Gurih',
    'Kue Kering' => 'Kue Kering',
    'Kue Ulang Tahun' => 'Kue Ulang Tahun'
];

// Validate selected category
if (!isset($categories[$selectedCategory])) {
    $selectedCategory = CATEGORY_ALL;
}

if ($selectedCategory === CATEGORY_ALL) {
    $stmt = $conn->prepare('SELECT id, nama, kategori, harga, deskripsi, image FROM products ORDER BY id DESC');
} else {
    $stmt = $conn->prepare('SELECT id, nama, kategori, harga, deskripsi, image FROM products WHERE kategori = ? ORDER BY id DESC');
    $stmt->bind_param('s', $selectedCategory);
}

$stmt->execute();
$productsResult = $stmt->get_result();

function getProductReviews($conn, $productId)
{
    $stmt = $conn->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE product_id = ? AND item_type = "product"');
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return ['avg_rating' => 0, 'total' => 0];
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main class="products-page">
        <?php displaySessionAlert(); ?>
        
        <?php if (isset($_GET['added'])): ?>
            <?php echo renderAlert('Produk berhasil ditambahkan ke keranjang!', 'success'); ?>
        <?php endif; ?>
        
        <section class="page-header">
            <h2>Produk Adam Bakery</h2>
            <p>Pilih produk individual sesuai selera Anda</p>
        </section>
        
        <!-- Category Filter -->
        <section class="category-filter">
            <div class="filter-buttons">
                <?php foreach ($categories as $key => $name): ?>
                    <a href="?category=<?php echo InputSanitizer::escapeAttr($key); ?>" 
                       class="btn <?php echo $selectedCategory === $key ? 'active' : 'secondary'; ?>">
                        <?php echo InputSanitizer::escapeHtml($name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Products Grid -->
        <section class="products-section">
            <?php if ($productsResult->num_rows > 0): ?>
                <div class="product-grid">
                    <?php while ($product = $productsResult->fetch_assoc()): ?>
                        <?php 
                            $reviews = getProductReviews($conn, $product['id']);
                            $imageFile = 'uploads/' . $product['image'];
                            if (empty($product['image']) || !file_exists($imageFile)) {
                                $imageFile = '/placeholder.svg?height=200&width=280';
                            }
                        ?>
                        <div class="product-card">
                            <div class="product-category">
                                <?php echo InputSanitizer::escapeHtml($product['kategori']); ?>
                            </div>
                            
                            <img src="<?php echo InputSanitizer::escapeAttr($imageFile); ?>" 
                                 alt="<?php echo InputSanitizer::escapeAttr($product['nama']); ?>" 
                                 class="product-image">
                            
                            <h3 class="product-name">
                                <?php echo InputSanitizer::escapeHtml($product['nama']); ?>
                            </h3>
                            
                            <?php if ($product['deskripsi']): ?>
                                <p class="product-description">
                                    <?php echo InputSanitizer::escapeHtml(substr($product['deskripsi'], 0, 60) . '...'); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="product-price">
                                <?php echo formatCurrency($product['harga']); ?>
                            </div>
                            
                            <!-- Reviews Summary -->
                            <div class="product-reviews">
                                <?php if ($reviews['total'] > 0): ?>
                                    <?php echo renderStarRating((int)round($reviews['avg_rating'])); ?>
                                    <small class="review-count">
                                        <?php echo number_format($reviews['avg_rating'], 1); ?>/5 (<?php echo $reviews['total']; ?> ulasan)
                                    </small>
                                <?php else: ?>
                                    <small class="no-reviews">Belum ada ulasan</small>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add to Cart Form -->
                            <form method="post" action="add_to_cart.php" class="add-to-cart-form">
                                <input type="hidden" name="item_type" value="product">
                                <input type="hidden" name="item_id" value="<?php echo (int)$product['id']; ?>">
                                <input type="hidden" name="redirect" value="products.php?category=<?php echo InputSanitizer::escapeAttr($selectedCategory); ?>">
                                
                                <div class="quantity-input">
                                    <label for="qty-<?php echo $product['id']; ?>">Jumlah:</label>
                                    <input type="number" 
                                           id="qty-<?php echo $product['id']; ?>" 
                                           name="quantity" 
                                           value="1" 
                                           min="1" 
                                           max="100">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Tambah ke Keranjang</button>
                            </form>
                            
                            <!-- View Reviews Link -->
                            <?php if ($reviews['total'] > 0): ?>
                                <a href="view_reviews.php?type=product&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-secondary">
                                    Lihat Ulasan
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Tidak ada produk dalam kategori ini.</p>
                    <a href="products.php" class="btn btn-primary">Lihat Semua Produk</a>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Cart Floating Badge -->
        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
            <div class="floating-cart-badge">
                <a href="checkout.php" title="Lihat keranjang">
                    <span class="cart-icon">🛒</span>
                    <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Footer Actions -->
        <div class="page-actions">
            <a href="index.php" class="btn btn-secondary">Kembali ke Beranda</a>
            <a href="checkout.php" class="btn btn-primary">Lihat Keranjang</a>
        </div>
    </main>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
