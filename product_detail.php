<?php 
include 'includes/header.php';
include 'backend/db.php';

session_start();

$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$product_id) {
    header("Location: products.php");
    exit();
}

// Get product details
$product_result = $conn->query("SELECT * FROM products WHERE id = $product_id");
if ($product_result->num_rows == 0) {
    header("Location: products.php");
    exit();
}
$product = $product_result->fetch_assoc();

// Get reviews for this product
$reviews_query = "SELECT * FROM reviews WHERE product_id = $product_id ORDER BY created_at DESC";
$reviews = $conn->query($reviews_query);

// Calculate average rating
$avg_result = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE product_id = $product_id");
$avg_data = $avg_result->fetch_assoc();
$avg_rating = round($avg_data['avg_rating'], 1);
$total_reviews = $avg_data['total'];
?>

<main>
    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Produk berhasil ditambahkan ke keranjang!</div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem;">
        <!-- Product Image -->
        <div>
            <img src="/placeholder.svg?height=400&width=400" 
                 alt="<?php echo $product['nama']; ?>" 
                 style="width: 100%; height: 400px; object-fit: cover; border-radius: 15px;">
        </div>
        
        <!-- Product Info -->
        <div>
            <div class="category"><?php echo $product['kategori']; ?></div>
            <h2 style="margin: 1rem 0;"><?php echo $product['nama']; ?></h2>
            
            <?php if ($product['deskripsi']): ?>
                <p style="color: #6b5b47; font-size: 1.1rem; margin-bottom: 2rem;">
                    <?php echo $product['deskripsi']; ?>
                </p>
            <?php endif; ?>
            
            <div class="price" style="font-size: 1.5rem; margin-bottom: 2rem;">
                Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?>
            </div>
            
            <!-- Rating Summary -->
            <?php if ($total_reviews > 0): ?>
                <div style="background: #f4e4c1; padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
                    <div class="rating" style="font-size: 1.5rem; margin-bottom: 0.5rem;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?php echo $i <= $avg_rating ? '' : 'empty'; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <p><strong><?php echo $avg_rating; ?>/5</strong> dari <?php echo $total_reviews; ?> ulasan</p>
                </div>
            <?php endif; ?>
            
            <!-- Add to Cart Form -->
            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="item_type" value="product">
                <input type="hidden" name="item_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="redirect" value="product_detail.php?id=<?php echo $product['id']; ?>&added=1">
                
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                    <label>Jumlah:</label>
                    <input type="number" name="quantity" value="1" min="1" max="10" 
                           style="width: 80px; padding: 0.5rem;">
                    <button type="submit" style="flex: 1;">Tambah ke Keranjang</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <h3>Ulasan Pelanggan</h3>
    
    <?php if ($reviews->num_rows == 0): ?>
        <p>Belum ada ulasan untuk produk ini.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php while ($review = $reviews->fetch_assoc()): ?>
                <div class="product-card">
                    <h4><?php echo $review['nama_reviewer']; ?></h4>
                    
                    <div class="rating" style="margin: 0.5rem 0;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?php echo $i <= $review['rating'] ? '' : 'empty'; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($review['review_text']): ?>
                        <p style="font-style: italic; color: #6b5b47;">"<?php echo $review['review_text']; ?>"</p>
                    <?php endif; ?>
                    
                    <small style="color: #8b5a3c;">
                        <?php echo date('d F Y', strtotime($review['created_at'])); ?>
                    </small>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
    
    <div class="text-center mt-2">
        <a href="products.php" class="btn-secondary">Kembali ke Produk</a>
        <a href="checkout.php" class="btn">Lihat Keranjang</a>
    </div>
</main>

<style>
@media (max-width: 768px) {
    main > div:first-child {
        grid-template-columns: 1fr !important;
        gap: 2rem !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
