<?php 
include 'includes/header.php';


$item_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$item_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$sort_rating = isset($_GET['sort_rating']) ? $_GET['sort_rating'] : '';

// Whitelist validation untuk item_type
$allowed_types = ['all', 'product', 'package'];
if (!in_array($item_type, $allowed_types)) {
    $item_type = 'all';
}

// Whitelist validation untuk sort_rating
$allowed_sorts = ['', 'asc', 'desc'];
if (!in_array($sort_rating, $allowed_sorts)) {
    $sort_rating = '';
}

// Build query based on filters dengan prepared statements
$where_clause = "WHERE 1=1";
$params = [];
$types = '';

if ($item_type != 'all' && $item_id) {
    if ($item_type == 'product') {
        $where_clause .= " AND r.item_type = ? AND r.product_id = ?";
        $params = ['product', $item_id];
        $types = 'si';
    } elseif ($item_type == 'package') {
        $where_clause .= " AND r.item_type = ? AND r.package_id = ?";
        $params = ['package', $item_id];
        $types = 'si';
    }
}

// Urutkan berdasarkan rating jika dipilih
$order_by = "r.created_at DESC";
if ($sort_rating == "asc") {
    $order_by = "r.rating ASC";
} elseif ($sort_rating == "desc") {
    $order_by = "r.rating DESC";
}

$reviews_query = "
    SELECT r.*, 
           CASE 
               WHEN r.item_type = 'product' THEN p.nama
               WHEN r.item_type = 'package' THEN pkg.nama
           END as item_name
    FROM reviews r
    LEFT JOIN products p ON r.product_id = p.id
    LEFT JOIN packages pkg ON r.package_id = pkg.id
    $where_clause
    ORDER BY $order_by
";

if (count($params) > 0) {
    $stmt = $conn->prepare($reviews_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $reviews = $stmt->get_result();
} else {
    $reviews = $conn->query($reviews_query);
}

// Get average rating if viewing specific item dengan prepared statement
$avg_rating = 0;
$total_reviews = 0;
if ($item_type != 'all' && $item_id) {
    $avg_query = "SELECT AVG(r.rating) as avg_rating, COUNT(*) as total 
                  FROM reviews r 
                  $where_clause";
    
    if (count($params) > 0) {
        $stmt_avg = $conn->prepare($avg_query);
        $stmt_avg->bind_param($types, ...$params);
        $stmt_avg->execute();
        $avg_result = $stmt_avg->get_result();
    } else {
        $avg_result = $conn->query($avg_query);
    }
    
    if ($avg_result->num_rows > 0) {
        $avg_data = $avg_result->fetch_assoc();
        $avg_rating = round($avg_data['avg_rating'], 1);
        $total_reviews = $avg_data['total'];
    }
}
?>

<main>
    <h2>Ulasan Pelanggan</h2>

    <?php if ($item_type != 'all' && $item_id && $total_reviews > 0): ?>
        <div style="background: #f4e4c1; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; text-align: center;">
            <h3>Rating Rata-rata</h3>
            <div style="font-size: 2rem; color: #ffc107; margin: 0.5rem 0;">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?php echo $i <= $avg_rating ? '' : 'empty'; ?>">★</span>
                <?php endfor; ?>
            </div>
            <p><strong><?php echo htmlspecialchars($avg_rating); ?>/5</strong> dari <?php echo htmlspecialchars($total_reviews); ?> ulasan</p>
        </div>
    <?php endif; ?>

    <?php if ($reviews->num_rows > 0): ?>
        <!-- 🔽 Dropdown Filter Rating -->
        <form method="GET" style="text-align: right; margin-bottom: 1rem;">
            <!-- Encode output untuk mencegah XSS -->
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($item_type); ?>">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($item_id); ?>">
            
            <label for="sort_rating"><strong>Urutkan rating:</strong></label>
            <select name="sort_rating" id="sort_rating" onchange="this.form.submit()" style="padding: 0.4rem; border-radius: 6px; border: 1px solid #d8b98b;">
                <option value="">Terbaru</option>
                <option value="asc" <?php if($sort_rating=='asc') echo 'selected'; ?>>Terendah → Tertinggi</option>
                <option value="desc" <?php if($sort_rating=='desc') echo 'selected'; ?>>Tertinggi → Terendah</option>
            </select>
        </form>
    <?php endif; ?>

    <?php if ($reviews->num_rows == 0): ?>
        <p>Belum ada ulasan untuk item ini.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php while ($review = $reviews->fetch_assoc()): ?>
                <div class="product-card">
                    <!-- Encode semua output user-controlled untuk mencegah XSS -->
                   <h4><?php echo htmlspecialchars($review['item_name'] ?? ''); ?></h4>
                    <p><strong><?php echo htmlspecialchars($review['nama_reviewer']); ?></strong></p>
                    
                    <div class="rating" style="margin: 0.5rem 0;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?php echo $i <= $review['rating'] ? '' : 'empty'; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($review['review_text']): ?>
                        <p style="font-style: italic; color: #6b5b47;">"<?php echo htmlspecialchars($review['review_text']); ?>"</p>
                    <?php endif; ?>
                    
                    <small style="color: #8b5a3c;">
                        <?php echo date('d F Y', strtotime($review['created_at'])); ?>
                    </small>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <div class="text-center mt-2">
        <a href="products.php" class="btn">Lihat Produk</a>
        <a href="index.php" class="btn">Kembali ke Beranda</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
