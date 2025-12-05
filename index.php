<?php 
include 'includes/header.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil promo terbaru (misalnya max 5)
$promos = $conn->query("SELECT * FROM promos ORDER BY created_at DESC LIMIT 5");

// Get featured packages (limit 3)
$featured_packages = $conn->query("SELECT * FROM packages ORDER BY id LIMIT 3");

// Get featured products by category (2 from each category)
$featured_products = [];
$categories = $conn->query("SELECT * FROM categories ORDER BY nama ASC");

while ($cat = $categories->fetch_assoc()) {
    $category_name = $cat['nama'];
    $category_id = $cat['id'];

    $result = $conn->query("
        SELECT p.*, c.nama AS kategori_nama
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.category_id = '$category_id'
        ORDER BY p.id LIMIT 3
    ");

    while ($product = $result->fetch_assoc()) {
        $featured_products[] = $product;
    }
}


// Get recent reviews
$recent_reviews = $conn->query("
    SELECT r.*, 
           CASE 
               WHEN r.item_type = 'product' THEN p.nama
               WHEN r.item_type = 'package' THEN pkg.nama
           END as item_name
    FROM reviews r
    LEFT JOIN products p ON r.product_id = p.id
    LEFT JOIN packages pkg ON r.package_id = pkg.id
    ORDER BY r.created_at DESC
    LIMIT 3 
");
?>



<!-- Wrapper 3 Kolom -->
<div style="
    display: flex; 
    gap: 2rem; 
    align-items: flex-start;
    justify-content: center;
">

    <!-- Sidebar Kiri: Custom Order + Promo -->
    <aside style="
        background: #fff7e6; 
        padding: 2rem 1.5rem; 
        border-radius: 12px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        width: 250px;
        flex-shrink: 0;
    ">
        <h3 style="color: #8b5a3c; margin-bottom: 1rem;">Pesanan Custom</h3>
        <p style="color: #6b5b47; font-size: 0.95rem; margin-bottom: 1.5rem;">
            Punya ide roti/kue spesial?  
            Yuk buat pesanan custom sesuai selera!
        </p>
        <a href="contact.php" class="btn" 
           style="display: inline-block; background: #8b5a3c; color: #fff; padding: 0.6rem 1.2rem; font-size: 0.9rem; border-radius: 30px; margin-bottom: 1.5rem;">
           Buat Pesanan
        </a>

        <!-- Promo Section -->
        <div style="
            background: #fef3c7; 
            padding: 1rem; 
            border-radius: 10px; 
            border: 1px dashed #d97706;
            margin-top: 1rem;
        ">
            <h4 style="color: #b45309; margin-bottom: 0.5rem;">🎉 Promo Spesial</h4>
            <ul style="font-size: 0.9rem; color: #6b5b47; padding-left: 1.2rem; margin: 0;">
                <?php if ($promos->num_rows > 0): ?>
                    <?php while ($promo = $promos->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($promo['title']); ?> - <?php echo htmlspecialchars($promo['description']); ?></li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>Tidak ada promo saat ini</li>
                <?php endif; ?>
            </ul>
        </div>
    </aside>

    <!-- Konten Tengah -->
    <div style="flex: 1; min-width: 0;">
        <main>
            <!-- Hero Section -->
            <section style="text-align: center; padding: 3rem 0; background: linear-gradient(135deg, #f4e4c1 0%, #e8d5b7 100%); border-radius: 15px; margin-bottom: 3rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Selamat Datang di Adam Bakery</h2>
                <p style="font-size: 1.2rem; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                    Kami menyediakan berbagai macam roti, kue, dan pastry berkualitas tinggi dengan resep tradisional dan sentuhan modern yang istimewa.
                </p>
            </section>

            <!-- Paket Spesial Section -->
            <section style="margin-bottom: 4rem;">
                <h2>Paket Spesial Kami</h2>
                <p style="text-align: center; margin-bottom: 2rem;">
                    Hemat lebih banyak dengan paket kombinasi produk terbaik kami!
                </p>
                
                <div class="product-grid">
                    <?php while ($package = $featured_packages->fetch_assoc()): ?>
                        <div class="package-card">
                            <?php if (!empty($package['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($package['image']); ?>" 
                                    alt="<?php echo htmlspecialchars($package['nama']); ?>" 
                                    style="width: 100%; height: 180px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem;">
                            <?php else: ?>
                                <img src="/assets/images/no-image.png" 
                                    alt="No Image" 
                                    style="width: 100%; height: 180px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem;">
                            <?php endif; ?>

                            
                            <h4><?php echo $package['nama']; ?></h4>
                            <p style="color: #6b5b47; font-size: 0.9rem; margin-bottom: 1rem;">
                                <?php echo substr($package['deskripsi'], 0, 80) . '...'; ?>
                            </p>
                            <div class="price">Rp <?php echo number_format($package['harga'], 0, ',', '.'); ?></div>
                            
                            <form method="post" action="add_to_cart.php" style="margin-top: 1rem;">
                                <input type="hidden" name="item_type" value="package">
                                <input type="hidden" name="item_id" value="<?php echo $package['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="redirect" value="index.php">
                                <button type="submit" style="width: 100%;">Pesan Sekarang</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="text-center mt-2">
                    <a href="packages.php" class="btn">Lihat Semua Paket</a>
                </div>
            </section>

            <!-- À La Carte Section -->
            <section style="margin-bottom: 4rem;">
                <h2>Pilihan À La Carte</h2>
                <p style="text-align: center; margin-bottom: 2rem;">
                    Pilih produk individual sesuai selera Anda
                </p>
                
                <div class="product-grid">
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <div class="category"><?php echo htmlspecialchars($product['kategori_nama']); ?></div>
                            
                            <?php if (!empty($product['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                alt="<?php echo htmlspecialchars($product['nama']); ?>" 
                                style="width: 100%; height: 180px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem;">
                        <?php else: ?>
                            <img src="assets/no-image.png" 
                                alt="No Image" 
                                style="width: 100%; height: 180px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem;">
                        <?php endif; ?>

                            
                            <h4><?php echo $product['nama']; ?></h4>
                            
                            <?php if ($product['deskripsi']): ?>
                                <p style="color: #6b5b47; font-size: 0.9rem; margin-bottom: 1rem;">
                                    <?php echo substr($product['deskripsi'], 0, 60) . '...'; ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></div>
                            
                            <form method="post" action="add_to_cart.php" style="margin-top: 1rem;">
                                <input type="hidden" name="item_type" value="product">
                                <input type="hidden" name="item_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="redirect" value="index.php">
                                <button type="submit" style="width: 100%;">Tambah ke Keranjang</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-2">
                    <a href="products.php" class="btn">Lihat Semua Produk</a>
                </div>
            </section>

            <!-- Testimonials Section -->
            <?php 
            $recent_reviews = $conn->query("
                SELECT r.*, 
                       CASE 
                           WHEN r.item_type = 'product' THEN p.nama
                           WHEN r.item_type = 'package' THEN pkg.nama
                       END as item_name
                FROM reviews r
                LEFT JOIN products p ON r.product_id = p.id
                LEFT JOIN packages pkg ON r.package_id = pkg.id
                ORDER BY r.created_at DESC
                LIMIT 3 
            ");
            if ($recent_reviews->num_rows > 0): 
            ?>
            <section style="background: #f4e4c1; padding: 3rem 2rem; border-radius: 15px; margin-bottom: 3rem;">
                <h2>Apa Kata Pelanggan Kami</h2>
                
                <div class="product-grid">
                    <?php while ($review = $recent_reviews->fetch_assoc()): ?>
                        <div style="background: #fff; padding: 1.5rem; border-radius: 10px; text-align: center;">
                            <div class="rating" style="margin-bottom: 1rem;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="<?php echo $i <= $review['rating'] ? '' : 'empty'; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            
                            <?php if ($review['review_text']): ?>
                                <p style="font-style: italic; color: #6b5b47; margin-bottom: 1rem;">
                                    "<?php echo htmlspecialchars(substr($review['review_text'], 0, 100)) . '...'; ?>"
                                </p>
                            <?php endif; ?>
                            
                            <h4 style="color: #8b5a3c; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($review['nama_reviewer']); ?></h4>
                            <small style="color: #8b5a3c;"><?php echo htmlspecialchars((string)($review['item_name'] ?? '')); ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="text-center mt-2">
                    <a href="view_reviews.php" class="btn-secondary">Lihat Semua Ulasan</a>
                </div>
            </section>
            <?php endif; ?>

            <!-- Call to Action Section -->
            <section style="text-align: center; padding: 3rem 0; background: linear-gradient(135deg, #8b5a3c 0%, #6b4423 100%); color: #f4e4c1; border-radius: 15px;">
                
                <img src="/assets/images/footer-banner.jpg" alt="Adam's Bakery" style="max-width: 80%; border-radius: 10px; margin-bottom: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                
                <h2 style="color: #f4e4c1;">Siap Memesan?</h2>
                <p style="color: #f4e4c1; margin-bottom: 2rem;">
                    Nikmati kelezatan produk Adam Bakery dengan mudah dan praktis
                </p>
                
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="products.php" class="btn" style="background: #f4e4c1; color: #8b5a3c;">Mulai Belanja</a>
                    <a href="packages.php" class="btn" style="background: #d4af8c; color: #fff;">Lihat Paket</a>
                    <a href="contact.php" class="btn" style="background: #9c7b5bff; color: #fff;">Hubungi Kami</a>
                </div>
            </section>

             <!-- Cart Summary -->
            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <div style="position: fixed; bottom: 20px; right: 20px; background: #d4af8c; color: white; padding: 1rem; border-radius: 50px; box-shadow: 0 4px 12px rgba(139, 90, 60, 0.3);">
                    <a href="checkout.php" style="color: white; text-decoration: none; font-weight: bold;">
                        🛒 Keranjang (<?php echo count($_SESSION['cart']); ?> item)
                    </a>
                </div>
            <?php endif; ?>

            <!-- Order Check Summary -->
            <?php if (isset($_SESSION['has_order']) && $_SESSION['has_order'] === true && isset($_SESSION['last_transaction_id'])): ?>
                <div style="position: fixed; bottom: 90px; right: 20px; background: #c79b77; color: white; padding: 1rem; border-radius: 50px; box-shadow: 0 4px 12px rgba(139, 90, 60, 0.3); z-index: 1000;">
                    <a href="payment_success.php?transaction_id=<?= $_SESSION['last_transaction_id']; ?>" 
                    style="color: white; text-decoration: none; font-weight: bold;">
                        📦 Cek Status Pesanan
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Sidebar Kanan: Tentang Bakery -->
    <aside style="
        background: #f4e4c1; 
        padding: 2rem 1.5rem; 
        border-radius: 12px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        width: 250px;
        flex-shrink: 0;
    ">
        <h3 style="color: #6b4423; margin-bottom: 1rem;">Tentang Adam Bakery</h3>
        <p style="font-size: 0.9rem; color: #6b5b47; margin-bottom: 1rem;">
            Adam Bakery berdiri sejak 1995 dan telah menjadi pilihan utama untuk roti, kue, dan pastry berkualitas tinggi.  
            Kami memadukan resep tradisional dengan sentuhan modern.
        </p>
        <hr style="margin: 1rem 0; border: none; border-top: 1px solid #d4af8c;">
        <h4 style="color: #6b4423; margin-bottom: 0.5rem;">📍 Alamat Toko</h4>
        <p style="font-size: 0.9rem; color: #6b5b47; margin: 0;">
           Jl. Raya Pacul, Kademangan, Mejasem Bar., Kec. Dukuhturi, Kabupaten Tegal, Jawa Tengah 52472<br>
        </p>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.011055791453!2d109.14774407475659!3d-6.889278393109725!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6fb95c3bb7b86b%3A0x59230f80f6e33e4f!2sAdams%20Bakery%20Tegal!5e0!3m2!1sid!2sid!4v1759113611767!5m2!1sid!2sid" width="200" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </aside>

</div>

<style>
@media (max-width: 768px) {
    /* Flex ke kolom tunggal di mobile */
    div[style*="display: flex"] {
        flex-direction: column;
    }
    aside {
        width: 100% !important;
        margin-bottom: 1rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
