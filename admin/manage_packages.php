<?php
session_start();
include '../backend/db.php';

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
                $nama = $conn->real_escape_string($_POST['nama']);
                $harga = $conn->real_escape_string($_POST['harga']);
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
                        move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath);
                    }
                }

                $sql = "INSERT INTO packages (nama, harga, deskripsi, image) 
                        VALUES ('$nama', '$harga', '$deskripsi', '$imageName')";
                $conn->query($sql);
                break;

            case 'delete':
                $id = $conn->real_escape_string($_POST['id']);
                $conn->query("DELETE FROM packages WHERE id = '$id'");
                break;

            case 'update':
                $id = $conn->real_escape_string($_POST['id']);
                $nama = $conn->real_escape_string($_POST['nama']);
                $harga = $conn->real_escape_string($_POST['harga']);
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
                        move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath);
                        $sqlImg = ", image = '$imageName'";
                    }
                }

                $sql = "UPDATE packages SET 
                        nama='$nama', harga='$harga', deskripsi='$deskripsi' $sqlImg
                        WHERE id='$id'";
                $conn->query($sql);
                break;
        }
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $id = $conn->real_escape_string($_GET['edit']);
    $editData = $conn->query("SELECT * FROM packages WHERE id='$id'")->fetch_assoc();
}

$packages = $conn->query("SELECT * FROM packages ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Paket - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/logoadambakery.png">
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
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
        <?php endif; ?>

        <label>Nama Paket:</label>
        <input type="text" name="nama" required value="<?php echo $editData['nama'] ?? ''; ?>">

        <label>Harga:</label>
        <input type="number" name="harga" required value="<?php echo $editData['harga'] ?? ''; ?>">

        <label>Deskripsi:</label>
        <textarea name="deskripsi" rows="3" required><?php echo $editData['deskripsi'] ?? ''; ?></textarea>

        <label>Foto Paket:</label>
        <input type="file" name="foto" accept="image/*">
        <?php if ($editData && $editData['image']): ?>
            <br><img src="../uploads/<?php echo $editData['image']; ?>" width="100">
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
            <td><?php echo $package['id']; ?></td>
            <td><?php echo $package['nama']; ?></td>
            <td>Rp <?php echo number_format($package['harga'],0,',','.'); ?></td>
            <td><?php echo substr($package['deskripsi'],0,30).'...'; ?></td>
            <td>
                <?php if ($package['image']): ?>
                    <img src="../uploads/<?php echo $package['image']; ?>" width="80">
                <?php endif; ?>
            </td>
            <td>
                <a href="?edit=<?php echo $package['id']; ?>">Edit</a>
                |
                <form method="post" style="display:inline" onsubmit="return confirm('Yakin hapus?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $package['id']; ?>">
                    <button type="submit">Hapus</button>    
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>
</body>
</html>
