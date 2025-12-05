<?php
session_start();
include '../backend/db.php';

// Helper function for image upload validation
function validateAndUploadImage($file, $targetDir = "../uploads/") {
    if (!isset($file) || $file['error'] != 0) {
        return null;
    }
    
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    // Validate file size (max 5MB)
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxFileSize) {
        die("File terlalu besar. Maksimal ukuran: 5MB");
    }
    
    // Validate file extension
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png'];
    if (!in_array($fileExt, $allowedExts)) {
        die("Tipe file tidak diizinkan. Hanya JPG, JPEG, PNG yang diperbolehkan.");
    }
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = ['image/jpeg', 'image/png'];
    if (!in_array($mimeType, $allowedMimes)) {
        die("MIME type tidak valid. Hanya JPEG dan PNG yang diperbolehkan.");
    }
    
    // Generate secure filename
    $fileName = time() . "_" . uniqid() . "." . $fileExt;
    $filePath = $targetDir . $fileName;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        die("Gagal upload file");
    }
    
    return $fileName;
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$editData = null;

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nama = $_POST['nama'];
                $harga = $_POST['harga'];
                $deskripsi = $_POST['deskripsi'];

                $imageName = null;
                if (isset($_FILES['foto'])) {
                    $imageName = validateAndUploadImage($_FILES['foto']);
                }

                $sql = "INSERT INTO packages (nama, harga, deskripsi, image) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssss', $nama, $harga, $deskripsi, $imageName);
                $stmt->execute();
                $stmt->close();
                break;

            case 'delete':
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                break;

            case 'update':
                $id = intval($_POST['id']);
                $nama = $_POST['nama'];
                $harga = $_POST['harga'];
                $deskripsi = $_POST['deskripsi'];

                $imageName = null;
                if (isset($_FILES['foto'])) {
                    $imageName = validateAndUploadImage($_FILES['foto']);
                }

                if ($imageName) {
                    $stmt = $conn->prepare("UPDATE packages SET nama=?, harga=?, deskripsi=?, image=? WHERE id=?");
                    $stmt->bind_param('ssssi', $nama, $harga, $deskripsi, $imageName, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE packages SET nama=?, harga=?, deskripsi=? WHERE id=?");
                    $stmt->bind_param('sssi', $nama, $harga, $deskripsi, $id);
                }
                $stmt->execute();
                $stmt->close();
                break;
        }
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editData = $res->fetch_assoc();
    $stmt->close();
}

$packages = $conn->query("SELECT * FROM packages ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Paket - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/png" href="/assets/images/logoadambakery.png">
</head>
<body>
<header class="admin-header">
    <h1>Kelola Paket - Adam Bakery</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a> |
        <a href="manage_products.php">Kelola Produk</a> |
        <a href="manage_packages.php">Kelola Paket</a> |
        <a href="view_transactions.php">Transaksi</a> |
        <a href="admin_promos.php">Promo</a> |
        <a href="view_reviews.php">Ulasan</a> |
        <a href="view_custom_orders.php">Pesanan Kustom</a> |
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h2><?php echo $editData ? "Edit Paket" : "Tambah Paket Baru"; ?></h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $editData ? "update" : "add"; ?>">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo (int)$editData['id']; ?>">
        <?php endif; ?>

        <label>Nama Paket:</label>
        <input type="text" name="nama" required value="<?php echo isset($editData['nama']) ? htmlspecialchars($editData['nama'], ENT_QUOTES, 'UTF-8') : ''; ?>">

        <label>Harga:</label>
        <input type="number" name="harga" required value="<?php echo isset($editData['harga']) ? htmlspecialchars($editData['harga'], ENT_QUOTES, 'UTF-8') : ''; ?>">

        <label>Deskripsi:</label>
        <textarea name="deskripsi" rows="3" required><?php echo isset($editData['deskripsi']) ? htmlspecialchars($editData['deskripsi'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>

        <label>Foto Paket:</label>
        <input type="file" name="foto" accept="image/*">
        <?php if ($editData && $editData['image']): ?>
            <br><img src="../uploads/<?php echo htmlspecialchars($editData['image'], ENT_QUOTES, 'UTF-8'); ?>" width="100">
        <?php endif; ?>

        <button type="submit"><?php echo $editData ? "Update Paket" : "Tambah Paket"; ?></button>
    </form>

    <h2>Daftar Paket</h2>
    <table>
        <tr>
            <th>ID</th><th>Nama</th><th>Harga</th><th>Deskripsi</th><th>Foto</th><th>Aksi</th>
        </tr>
        <?php while ($package = $packages->fetch_assoc()): ?>
        <tr>
            <td><?php echo (int)$package['id']; ?></td>
            <td><?php echo htmlspecialchars($package['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>Rp <?php echo number_format($package['harga'],0,',','.'); ?></td>
            <td><?php echo htmlspecialchars(substr($package['deskripsi'],0,30), ENT_QUOTES, 'UTF-8') . '...'; ?></td>
            <td>
                <?php if ($package['image']): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($package['image'], ENT_QUOTES, 'UTF-8'); ?>" width="80">
                <?php endif; ?>
            </td>
            <td>
                <a href="?edit=<?php echo (int)$package['id']; ?>">Edit</a>
                |
                <form method="post" style="display:inline" onsubmit="return confirm('Yakin hapus?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$package['id']; ?>">
                    <button type="submit">Hapus</button>    
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>
</body>
</html>
