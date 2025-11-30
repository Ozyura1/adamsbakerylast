<?php
session_start();
include '../backend/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$editData = null;

// === Handle form submissions ===
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nama = $conn->real_escape_string($_POST['nama']);
                $harga = $conn->real_escape_string($_POST['harga']);
                $category_id = $conn->real_escape_string($_POST['category_id']);
                $deskripsi = $conn->real_escape_string($_POST['deskripsi']);

                $imageName = null;
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                    $targetDir = "../uploads/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                    $imageName = time() . "_" . basename($_FILES["foto"]["name"]);
                    $targetFilePath = $targetDir . $imageName;

                    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($fileType, $allowedTypes)) {
                        if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath)) {
                            die("Upload gagal");
                        }
                    }
                }

                // Ambil nama kategori dari tabel categories
                $catRes = $conn->query("SELECT nama FROM categories WHERE id='$category_id'");
                $catRow = $catRes->fetch_assoc();
                $kategori_nama = $conn->real_escape_string($catRow['nama']);

                // Simpan ke tabel products (category_id dan kategori ikut diisi)
                $sql = "INSERT INTO products (nama, harga, category_id, kategori, deskripsi, image) 
                        VALUES ('$nama', '$harga', '$category_id', '$kategori_nama', '$deskripsi', '$imageName')";
                if (!$conn->query($sql)) {
                    die("Error INSERT: " . $conn->error);
                }
                break;

            case 'delete':
                $id = $conn->real_escape_string($_POST['id']);
                $conn->query("DELETE FROM products WHERE id = '$id'");
                break;

            case 'update':
                $id = $conn->real_escape_string($_POST['id']);
                $nama = $conn->real_escape_string($_POST['nama']);
                $harga = $conn->real_escape_string($_POST['harga']);
                $category_id = $conn->real_escape_string($_POST['category_id']);
                $deskripsi = $conn->real_escape_string($_POST['deskripsi']);

                $sqlImg = "";
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                    $targetDir = "../uploads/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                    $imageName = time() . "_" . basename($_FILES["foto"]["name"]);
                    $targetFilePath = $targetDir . $imageName;

                    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($fileType, $allowedTypes)) {
                        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath)) {
                            $sqlImg = ", image = '$imageName'";
                        }
                    }
                }

               // Ambil nama kategori dari tabel categories
                $catRes = $conn->query("SELECT nama FROM categories WHERE id='$category_id'");
                $catRow = $catRes->fetch_assoc();
                $kategori_nama = $conn->real_escape_string($catRow['nama']);

                $sql = "UPDATE products SET 
                        nama='$nama', 
                        harga='$harga', 
                        category_id='$category_id', 
                        kategori='$kategori_nama',
                        deskripsi='$deskripsi' 
                        $sqlImg
                        WHERE id='$id'";


                if (!$conn->query($sql)) {
                    die('Error UPDATE: ' . $conn->error);
                }
                break;

        }
    }
}

// === Handle edit request ===
if (isset($_GET['edit'])) {
    $id = $conn->real_escape_string($_GET['edit']);
    $editData = $conn->query("SELECT * FROM products WHERE id='$id'")->fetch_assoc();
}

// Ambil semua produk + kategori
$products = $conn->query("
    SELECT p.*, c.nama AS kategori_nama 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY c.nama, p.nama
");

// Ambil daftar kategori untuk dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/logoadambakery.png">
</head>
<body>
<header class="admin-header">
    <h1>Kelola Produk - Adam Bakery</h1>
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
    <h2><?php echo $editData ? "Edit Produk" : "Tambah Produk Baru"; ?></h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $editData ? "update" : "add"; ?>">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
        <?php endif; ?>

        <label>Nama Produk:</label>
        <input type="text" name="nama" required value="<?php echo $editData['nama'] ?? ''; ?>">

        <label>Harga:</label>
        <input type="number" name="harga" required value="<?php echo $editData['harga'] ?? ''; ?>">

        <label>Kategori:</label>
        <select name="category_id" required>
            <option value="">Pilih Kategori</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>" 
                    <?php echo ($editData && $editData['category_id']==$cat['id']) ? "selected" : ""; ?>>
                    <?php echo $cat['nama']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Deskripsi:</label>
        <textarea name="deskripsi" rows="3"><?php echo $editData['deskripsi'] ?? ''; ?></textarea>

        <label>Foto Produk:</label>
        <input type="file" name="foto" accept="image/*">
        <?php if ($editData && $editData['image']): ?>
            <br><img src="../uploads/<?php echo $editData['image']; ?>" width="100">
        <?php endif; ?>

        <button type="submit"><?php echo $editData ? "Update Produk" : "Tambah Produk"; ?></button>
    </form>

    <h2>Daftar Produk</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Foto</th><th>Aksi</th>
        </tr>
        <?php while ($product = $products->fetch_assoc()): ?>
        <tr>
            <td><?php echo $product['id']; ?></td>
            <td><?php echo $product['nama']; ?></td>
            <td><?php echo $product['kategori_nama']; ?></td>
            <td>Rp <?php echo number_format($product['harga'],0,',','.'); ?></td>
            <td>
                <?php if ($product['image']): ?>
                    <img src="../uploads/<?php echo $product['image']; ?>" width="80">
                <?php endif; ?>
            </td>
            <td>
                <a href="?edit=<?php echo $product['id']; ?>">Edit</a>
                |
                <form method="post" style="display:inline" onsubmit="return confirm('Yakin hapus produk ini?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>
</body>
</html>
