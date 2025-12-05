<?php
// Flash helper: sets session message and redirects back to this page
function flashAndRedirect($type, $message) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if ($type === 'error') {
        $_SESSION['error'] = $message;
    } else {
        $_SESSION['info'] = $message;
    }
    header('Location: manage_products.php');
    exit();
}

// Helper function for image upload validation
function validateAndUploadImage($file, $targetDir = "../uploads/") {
    if (!isset($file) || $file['error'] != 0) {
        return null;
    }
    
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    // Validate file size (max 5MB)
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxFileSize) {
        flashAndRedirect('error', 'File terlalu besar. Maksimal ukuran: 5MB');
    }
    
    // Validate file extension
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png'];
    if (!in_array($fileExt, $allowedExts)) {
        flashAndRedirect('error', 'Tipe file tidak diizinkan. Hanya JPG, JPEG, PNG yang diperbolehkan.');
    }
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = ['image/jpeg', 'image/png'];
    if (!in_array($mimeType, $allowedMimes)) {
        flashAndRedirect('error', 'MIME type tidak valid. Hanya JPEG dan PNG yang diperbolehkan.');
    }
    
    // Generate secure filename
    $fileName = time() . "_" . uniqid() . "." . $fileExt;
    $filePath = $targetDir . $fileName;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        flashAndRedirect('error', 'Gagal upload file');
    }
    
    return $fileName;
}

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
                $nama = $_POST['nama'];
                $harga = $_POST['harga'];
                $category_id = intval($_POST['category_id']);
                $deskripsi = $_POST['deskripsi'];

                $imageName = null;
                if (isset($_FILES['foto'])) {
                    $imageName = validateAndUploadImage($_FILES['foto']);
                }

                // Ambil nama kategori dari tabel categories
                $catStmt = $conn->prepare("SELECT nama FROM categories WHERE id = ?");
                $catStmt->bind_param('i', $category_id);
                $catStmt->execute();
                $catRes = $catStmt->get_result();
                $catRow = $catRes->fetch_assoc();
                $kategori_nama = $catRow ? $catRow['nama'] : '';
                $catStmt->close();

                // Simpan ke tabel products (category_id dan kategori ikut diisi)
                $sql = "INSERT INTO products (nama, harga, category_id, kategori, deskripsi, image) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssisss', $nama, $harga, $category_id, $kategori_nama, $deskripsi, $imageName);
                if (!$stmt->execute()) {
                    error_log('manage_products.php INSERT error: ' . $stmt->error);
                    flashAndRedirect('error', 'Gagal menyimpan produk. Silakan coba lagi.');
                }
                $stmt->close();
                flashAndRedirect('info', 'Produk berhasil ditambahkan.');
                break;

            case 'delete':
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                flashAndRedirect('info', 'Produk berhasil dihapus.');
                break;

            case 'update':
                $id = intval($_POST['id']);
                $nama = $_POST['nama'];
                $harga = $_POST['harga'];
                $category_id = intval($_POST['category_id']);
                $deskripsi = $_POST['deskripsi'];

                $imageName = null;
                if (isset($_FILES['foto'])) {
                    $imageName = validateAndUploadImage($_FILES['foto']);
                }

               // Ambil nama kategori dari tabel categories
                $catStmt = $conn->prepare("SELECT nama FROM categories WHERE id = ?");
                $catStmt->bind_param('i', $category_id);
                $catStmt->execute();
                $catRes = $catStmt->get_result();
                $catRow = $catRes->fetch_assoc();
                $kategori_nama = $catRow ? $catRow['nama'] : '';
                $catStmt->close();

                if ($imageName) {
                    $sql = "UPDATE products SET 
                            nama=?, 
                            harga=?, 
                            category_id=?, 
                            kategori=?,
                            deskripsi=?, 
                            image=?
                            WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ssisssi', $nama, $harga, $category_id, $kategori_nama, $deskripsi, $imageName, $id);
                } else {
                    $sql = "UPDATE products SET 
                            nama=?, 
                            harga=?, 
                            category_id=?, 
                            kategori=?,
                            deskripsi=?
                            WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ssissi', $nama, $harga, $category_id, $kategori_nama, $deskripsi, $id);
                }

                if (!$stmt->execute()) {
                    error_log('manage_products.php UPDATE error: ' . $stmt->error);
                    flashAndRedirect('error', 'Gagal memperbarui produk. Silakan coba lagi.');
                }
                $stmt->close();
                flashAndRedirect('info', 'Produk berhasil diperbarui.');
                break;

        }
    }
}

// === Handle edit request ===
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editData = $res->fetch_assoc();
    $stmt->close();
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
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/png" href="/assets/images/logoadambakery.png">
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
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:6px;margin-bottom:1rem;">
            <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-info" style="background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:1rem;">
            <?php echo htmlspecialchars($_SESSION['info'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['info']); ?>
        </div>
    <?php endif; ?>
    <h2><?php echo $editData ? "Edit Produk" : "Tambah Produk Baru"; ?></h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $editData ? "update" : "add"; ?>">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo (int)$editData['id']; ?>">
        <?php endif; ?>

        <label>Nama Produk:</label>
        <input type="text" name="nama" required value="<?php echo isset($editData['nama']) ? htmlspecialchars($editData['nama'], ENT_QUOTES, 'UTF-8') : ''; ?>">

        <label>Harga:</label>
        <input type="number" name="harga" required value="<?php echo isset($editData['harga']) ? htmlspecialchars($editData['harga'], ENT_QUOTES, 'UTF-8') : ''; ?>">

        <label>Kategori:</label>
        <select name="category_id" required>
            <option value="">Pilih Kategori</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo (int)$cat['id']; ?>" 
                    <?php echo ($editData && $editData['category_id']==$cat['id']) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($cat['nama'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Deskripsi:</label>
        <textarea name="deskripsi" rows="3"><?php echo isset($editData['deskripsi']) ? htmlspecialchars($editData['deskripsi'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>

        <label>Foto Produk:</label>
        <input type="file" name="foto" accept="image/*">
        <?php if ($editData && $editData['image']): ?>
            <br><img src="/uploads/<?php echo htmlspecialchars($editData['image'], ENT_QUOTES, 'UTF-8'); ?>" width="100">
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
            <td><?php echo (int)$product['id']; ?></td>
            <td><?php echo htmlspecialchars($product['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($product['kategori_nama'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>Rp <?php echo number_format($product['harga'],0,',','.'); ?></td>
            <td>
                <?php if ($product['image']): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" width="80">
                <?php endif; ?>
            </td>
            <td>
                <a href="?edit=<?php echo (int)$product['id']; ?>">Edit</a>
                |
                <form method="post" style="display:inline" onsubmit="return confirm('Yakin hapus produk ini?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>
</body>
</html>
