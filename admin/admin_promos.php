<?php
include '../backend/db.php';
session_start();

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Tambah promo baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_promo'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $desc  = $conn->real_escape_string($_POST['description']);
    $conn->query("INSERT INTO promos (title, description) VALUES ('$title', '$desc')");
    header("Location: admin_promos.php");
    exit();
}

// Hapus promo
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM promos WHERE id = $id");
    header("Location: admin_promos.php");
    exit();
}

// Ambil semua promo
$promos = $conn->query("SELECT * FROM promos ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Promo - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/logoadambakery.png">
</head>
<body>
<header class="admin-header">
    <h1>Kelola Promo - Adam Bakery</h1>
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
    <h2>Tambah Promo Baru</h2>
    <form method="POST">
        <input type="text" name="title" placeholder="Judul Promo" required>
        <textarea name="description" placeholder="Deskripsi Promo" rows="3" required></textarea>
        <button type="submit" name="add_promo">Tambah Promo</button>
    </form>

    <h2>Daftar Promo</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Deskripsi</th>
            <th>Tanggal Dibuat</th>
            <th>Aksi</th>
        </tr>
        <?php if ($promos->num_rows > 0): ?>
            <?php while ($promo = $promos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $promo['id']; ?></td>
                    <td><?php echo htmlspecialchars($promo['title']); ?></td>
                    <td><?php echo htmlspecialchars($promo['description']); ?></td>
                    <td><?php echo $promo['created_at']; ?></td>
                    <td>
                        <a href="?delete=<?php echo $promo['id']; ?>" onclick="return confirm('Hapus promo ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Belum ada promo.</td></tr>
        <?php endif; ?>
    </table>
</main>
</body>
</html>
