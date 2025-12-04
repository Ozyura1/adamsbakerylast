<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get statistics
$stats = [];

// Total products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $result->fetch_assoc()['count'];

// Total packages
$result = $conn->query("SELECT COUNT(*) as count FROM packages");
$stats['packages'] = $result->fetch_assoc()['count'];

// Total transactions
$result = $conn->query("SELECT COUNT(*) as count FROM transactions");
$stats['transactions'] = $result->fetch_assoc()['count'];

// Total reviews
$result = $conn->query("SELECT COUNT(*) as count FROM reviews");
$stats['reviews'] = $result->fetch_assoc()['count'];

// Custom orders
$result = $conn->query("SELECT COUNT(*) as count FROM kontak WHERE jenis_kontak = 'custom_order'");
$stats['custom_orders'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM kontak WHERE jenis_kontak = 'custom_order' AND status = 'pending'");
$stats['pending_custom_orders'] = $result->fetch_assoc()['count'];

// ✅ Total promos
$result = $conn->query("SELECT COUNT(*) as count FROM promos");
$stats['promos'] = $result->fetch_assoc()['count'];

// Recent transactions
$recent_transactions = $conn->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Adam Bakery</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/logoadambakery.png">
</head>
<body>
<header class="admin-header">
    <h1>Dashboard Admin - Adam Bakery</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a> |
        <a href="manage_products.php">Kelola Produk</a> |
        <a href="manage_packages.php">Kelola Paket</a> |
        <a href="view_transactions.php">Transaksi</a> |
        <a href="wa_notifications.php">Notifikasi WA</a> |
        <a href="admin_promos.php">Promo</a> |
        <a href="view_reviews.php">Ulasan</a> |
        <a href="view_custom_orders.php">Pesanan & Pertanyaan</a> |
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h2>Selamat Datang, <?php echo $_SESSION['admin_username']; ?>!</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['products']; ?></div>
            <div class="stat-label">Total Produk</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['packages']; ?></div>
            <div class="stat-label">Total Paket</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['transactions']; ?></div>
            <div class="stat-label">Total Transaksi</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['reviews']; ?></div>
            <div class="stat-label">Total Ulasan</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['custom_orders']; ?></div>
            <div class="stat-label">Pesanan Kustom</div>
        </div>
        <div class="stat-card" style="background-color: #fff3cd;">
            <div class="stat-number" style="color: #856404;"><?php echo $stats['pending_custom_orders']; ?></div>
            <div class="stat-label" style="color: #856404;">Pesanan Pending</div>
        </div>

        <!-- ✅ Promo card with count -->
        <div class="stat-card" style="background-color: #e2f0d9; cursor: pointer;" onclick="window.location.href='admin_promos.php'">
            <div class="stat-number" style="color: #155724;"><?php echo $stats['promos']; ?></div>
            <div class="stat-label" style="color: #155724;">Promo Aktif</div>
        </div>
    </div>

    <div style="margin: 2rem 0;">
        <h3>Aksi Cepat</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="view_custom_orders.php" class="quick-action-btn">
                Kelola Pesanan Kustom
                <?php if ($stats['pending_custom_orders'] > 0): ?>
                    <span class="badge"><?php echo $stats['pending_custom_orders']; ?></span>
                <?php endif; ?>
            </a>
            <a href="view_transactions.php" class="quick-action-btn">Lihat Transaksi</a>
            <a href="manage_products.php" class="quick-action-btn">Tambah Produk</a>
            <a href="admin_promos.php" class="quick-action-btn">Kelola Promo</a>
        </div>
    </div>
    
    <h3>Transaksi Terbaru</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Pembeli</th>
                <th>Total</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $transaction['id']; ?></td>
                <td><?php echo $transaction['nama_pembeli']; ?></td>
                <td>Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></td>
                <td><?php echo ucfirst($transaction['status'] ?? 'pending'); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<style>
.quick-action-btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background-color: #8B4513;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s ease;
    position: relative;
}

.quick-action-btn:hover {
    background-color: #A0522D;
}

.badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    min-width: 20px;
    text-align: center;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
</style>

<?php include '../includes/footer.php'; ?>
