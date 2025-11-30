<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get all reviews with product/package names
$reviews_query = "
    SELECT r.*, 
           CASE 
               WHEN r.item_type = 'product' THEN p.nama
               WHEN r.item_type = 'package' THEN pkg.nama
           END as item_name
    FROM reviews r
    LEFT JOIN products p ON r.product_id = p.id
    LEFT JOIN packages pkg ON r.package_id = pkg.id
    ORDER BY r.created_at DESC
";
$reviews = $conn->query($reviews_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Ulasan - Admin Adam Bakery</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/logoadambakery.png">
</head>
<body>
<header class="admin-header">
    <h1>Daftar Ulasan - Adam Bakery</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a> |
        <a href="manage_products.php">Kelola Produk</a> |
        <a href="manage_packages.php">Kelola Paket</a> |
        <a href="view_transactions.php">Transaksi</a> |
        <a href="admin_promos.php">Promo</a> |
        <a href="view_reviews.php">Ulasan</a> |
        <a href="view_custom_orders.php">Pesanan & Pertanyaan</a> |
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h2>Daftar Ulasan</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Reviewer</th>
                <th>Item</th>
                <th>Rating</th>
                <th>Ulasan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($review = $reviews->fetch_assoc()): ?>
            <tr>
                <td><?php echo $review['id']; ?></td>
                <td><?php echo $review['nama_reviewer']; ?></td>
                <td><?php echo $review['item_name'] . ' (' . ucfirst($review['item_type']) . ')'; ?></td>
                <td>
                    <div class="rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?php echo $i <= $review['rating'] ? '' : 'empty'; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                </td>
                <td><?php echo substr($review['review_text'], 0, 50) . '...'; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include '../includes/footer.php'; ?>
