<?php 
include 'includes/header.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get all packages
$packages = $conn->query("SELECT * FROM packages ORDER BY nama");

// Get reviews count and average rating for each package
function getPackageReviews($conn, $package_id) {
    $result = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE package_id = $package_id");
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return ['avg_rating' => 0, 'total' => 0];
}
?>

<style>
/* === GRID UNTUK DAFTAR PAKET === */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    justify-content: center;
    align-items: stretch;
    margin: 40px auto;
    max-width: 1100px;
}

/* === KARTU PAKET === */
.package-card {
    background: #fffaf5;
    border: 1px solid #f2e0cc;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    padding: 20px;
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.package-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
}

/* === GAMBAR PAKET === */
.package-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 15px;
}

/* === NAMA & HARGA === */
.package-card h4 {
    color: #6b3f19;
    font-size: 1.2rem;
    margin-bottom: 10px;
}

.package-card .price {
    color: #b3752a;
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 10px;
}

/* === TOMBOL === */
.package-card button,
.package-card .btn-secondary {
    background-color: #c9a675;
    border: none;
    color: white;
    padding: 10px;
    border-radius: 25px;
    width: 100%;
    cursor: pointer;
    transition: background-color 0.2s ease;
    text-decoration: none;
    display: inline-block;
    font-weight: 600;
}

.package-card button:hover,
.package-card .btn-secondary:hover {
    background-color: #b48c5d;
}

/* === RATING === */
.rating span {
    color: #ffc107;
    font-size: 1.2rem;
}

.rating .empty {
    color: #ddd;
}

/* ========================= */
/* === RESPONSIVE DESIGN === */
/* ========================= */

/* 🟡 Tablet Landscape (≤ 992px) */
@media (max-width: 992px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 25px;
        padding: 0 20px;
    }

    .package-card img {
        height: 180px;
    }

    .package-card h4 {
        font-size: 1.1rem;
    }

    .package-card .price {
        font-size: 1rem;
    }
}

/* 🟠 Tablet Potrait / HP Lebar (≤ 768px) */
@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 20px;
        margin: 30px auto;
    }

    .package-card {
        padding: 15px;
    }

    .package-card img {
        height: 160px;
    }

    .package-card button,
    .package-card .btn-secondary {
        padding: 8px 12px;
        font-size: 0.95rem;
    }
}

/* 🔵 HP Kecil (≤ 480px) */
@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: 1fr;
        gap: 15px;
        padding: 0 10px;
    }

    .package-card {
        padding: 12px;
        border-radius: 12px;
    }

    .package-card img {
        height: 140px;
    }

    .package-card h4 {
        font-size: 1rem;
    }

    .package-card .price {
        font-size: 0.95rem;
    }

    .package-card button,
    .package-card .btn-secondary {
        width: 100%;
        font-size: 0.9rem;
        padding: 10px;
    }
}

/* 🔴 HP Sangat Kecil (≤ 360px) */
@media (max-width: 360px) {
    .product-grid {
        grid-template-columns: 1fr;
        padding: 0 5px;
    }

    .package-card img {
        height: 120px;
    }

    .package-card h4 {
        font-size: 0.9rem;
    }

    .package-card .price {
        font-size: 0.9rem;
    }
}
</style>


<main>
    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Paket berhasil ditambahkan ke keranjang!</div>
    <?php endif; ?>
    
    <h2 style="text-align:center; color:#6b3f19;">Paket Spesial Adam Bakery</h2>
    <p style="text-align: center; margin-bottom: 3rem; color:#7a5a38;">
        Nikmati paket hemat dengan kombinasi produk terbaik kami!
    </p>
    
    <!-- Packages Grid -->
    <div class="product-grid">
        <?php while ($package = $packages->fetch_assoc()): ?>
            <?php $reviews = getPackageReviews($conn, $package['id']); ?>
            <div class="package-card">
                <?php if (!empty($package['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($package['image']); ?>" 
                        alt="<?php echo htmlspecialchars($package['nama']); ?>">
                <?php else: ?>
                    <img src="images/noimage.png" alt="Tidak ada gambar">
                <?php endif; ?>

                <h4><?php echo $package['nama']; ?></h4>
                
                <p style="color: #6b5b47; font-size: 0.9rem; margin-bottom: 1rem;">
                    <?php echo $package['deskripsi']; ?>
                </p>
                
                <div class="price">Rp <?php echo number_format($package['harga'], 0, ',', '.'); ?></div>
                
                <!-- Reviews Summary -->
                <?php if ($reviews['total'] > 0): ?>
                    <div style="margin: 0.5rem 0;">
                        <div class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?php echo $i <= round($reviews['avg_rating']) ? '' : 'empty'; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <small style="color: #8b5a3c;">
                            <?php echo round($reviews['avg_rating'], 1); ?>/5 (<?php echo $reviews['total']; ?> ulasan)
                        </small>
                    </div>
                <?php else: ?>
                    <div style="margin: 0.5rem 0;">
                        <small style="color: #8b5a3c;">Belum ada ulasan</small>
                    </div>
                <?php endif; ?>
                
                <!-- Add to Cart Form -->
                <form method="post" action="add_to_cart.php" style="margin-top: 1rem;">
                    <input type="hidden" name="item_type" value="package">
                    <input type="hidden" name="item_id" value="<?php echo $package['id']; ?>">
                    <input type="hidden" name="redirect" value="packages.php?added=1">
                    
                    <div style="display: flex; align-items: center; gap: 0.5rem; justify-content: center; margin-bottom: 1rem;">
                        <label style="margin: 0;">Jumlah:</label>
                        <input type="number" name="quantity" value="1" min="1" max="5" 
                               style="width: 60px; padding: 0.3rem; margin: 0;">
                    </div>
                    
                    <button type="submit">Tambah ke Keranjang</button>
                </form>
                
                <!-- View Reviews Link -->
                <?php if ($reviews['total'] > 0): ?>
                    <a href="view_reviews.php?type=package&id=<?php echo $package['id']; ?>" 
                       class="btn-secondary" style="margin-top: 0.5rem;">
                        Lihat Ulasan
                    </a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
    
    <?php if ($packages->num_rows == 0): ?>
        <div style="text-align: center; padding: 3rem;">
            <p>Belum ada paket tersedia.</p>
            <a href="products.php" class="btn">Lihat Produk Individual</a>
        </div>
    <?php endif; ?>
    
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
        <div style="position: fixed; bottom: 90px; right: 20px; background: #c79b77; color: white; 
                    padding: 1rem; border-radius: 50px; box-shadow: 0 4px 12px rgba(139, 90, 60, 0.3); 
                    z-index: 1000;">
            <a href="payment_success.php?transaction_id=<?= $_SESSION['last_transaction_id']; ?>" 
            style="color: white; text-decoration: none; font-weight: bold;">
                📦 Cek Status Pesanan
            </a>
        </div>
    <?php endif; ?>

    <div class="text-center mt-2" style="text-align:center; margin-top:2rem;">
        <a href="index.php" class="btn">Kembali ke Beranda</a>
        <a href="checkout.php" class="btn">Lihat Keranjang</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
