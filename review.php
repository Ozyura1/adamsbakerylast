<?php 
include 'includes/header.php';


$transaction_id = isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : 0;

if (!$transaction_id) {
    $error = "Tidak ada transaksi yang dipilih. Silakan pilih transaksi untuk memberi ulasan.";
} else {
    // Verify transaction exists and is confirmed
    $transaction_result = $conn->query("SELECT * FROM transactions WHERE id = $transaction_id AND status = 'confirmed'");
    
    if ($transaction_result->num_rows == 0) {
        $error = "Transaksi tidak ditemukan atau belum dikonfirmasi.";
    } else {
        $transaction = $transaction_result->fetch_assoc();
        
        // Get transaction items that haven't been reviewed yet
        $items_query = "
            SELECT ti.*, 
                   CASE 
                       WHEN ti.item_type = 'product' THEN p.nama
                       WHEN ti.item_type = 'package' THEN pkg.nama
                   END as item_name,
                   CASE 
                       WHEN ti.item_type = 'product' THEN p.id
                       WHEN ti.item_type = 'package' THEN pkg.id
                   END as item_id,
                   (SELECT COUNT(*) FROM reviews r WHERE r.transaction_id = ti.transaction_id 
                    AND ((r.product_id = ti.product_id AND ti.item_type = 'product') 
                         OR (r.package_id = ti.package_id AND ti.item_type = 'package'))) as reviewed
            FROM transaction_items ti
            LEFT JOIN products p ON ti.product_id = p.id
            LEFT JOIN packages pkg ON ti.package_id = pkg.id
            WHERE ti.transaction_id = $transaction_id
        ";
        $items = $conn->query($items_query);
    }
}
?>

<main>
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
        <a href="index.php" class="btn">Kembali ke Beranda</a>
    <?php else: ?>
        <h2>Berikan Ulasan Anda</h2>
        <p>Transaksi ID: <strong><?php echo $transaction['id']; ?></strong></p>
        <p>Nama: <strong><?php echo $transaction['nama_pembeli']; ?></strong></p>
        
        <?php while ($item = $items->fetch_assoc()): ?>
            <div class="product-card" style="margin-bottom: 2rem;">
                <h4><?php echo $item['item_name']; ?> (<?php echo ucfirst($item['item_type']); ?>)</h4>
                <p>Jumlah: <?php echo $item['quantity']; ?>x</p>
                
                <?php if ($item['reviewed'] > 0): ?>
                    <div class="alert alert-info">Anda sudah memberikan ulasan untuk item ini.</div>
                <?php else: ?>
                    <form method="post" action="backend/process_review.php">
                        <input type="hidden" name="transaction_id" value="<?php echo $transaction_id; ?>">
                        <input type="hidden" name="item_type" value="<?php echo $item['item_type']; ?>">
                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                        <input type="hidden" name="nama_reviewer" value="<?php echo $transaction['nama_pembeli']; ?>">
                        
                        <div class="rating-row">
                        <label>Rating:</label>
                        <div class="rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating_<?php echo $item['item_id']; ?>_<?php echo $i; ?>" required>
                                <label for="rating_<?php echo $item['item_id']; ?>_<?php echo $i; ?>">★</label>
                            <?php endfor; ?>
                        </div>
                        </div>



                        
                        <label>Ulasan:</label>
                        <textarea name="review_text" rows="3" placeholder="Bagikan pengalaman Anda dengan produk ini..."></textarea>
                        
                        <button type="submit">Kirim Ulasan</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
        
        <div class="text-center mt-2">
            <a href="index.php" class="btn-secondary">Kembali ke Beranda</a>
        </div>
    <?php endif; ?>
</main>

<style>
.rating-row {
    display: flex;
    align-items: center;   /* sejajarkan secara vertikal */
    gap: 10px;             /* jarak antara teks dan bintang */
    justify-content: flex-start; /* semuanya rata kiri */
}

/* posisi bintang masih kanan ke kiri (untuk efek hover dari kiri ke kanan) */
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-start;
}

.rating-input input {
    display: none;
}

.rating-input label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

/* efek hover dan klik */
.rating-input label:hover,
.rating-input label:hover ~ label {
    color: #ffc107;
}

.rating-input input:checked ~ label {
    color: #ffc107;
}


</style>

<?php include 'includes/footer.php'; ?>
