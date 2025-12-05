<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];

    $sql = "INSERT INTO products (nama, harga) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $nama, $harga);
    if ($stmt->execute()) {
        echo "Produk berhasil ditambahkan!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<h2>Tambah Produk</h2>
<form method="post">
    <label>Nama Produk:</label><br>
    <input type="text" name="nama" required><br><br>
    
    <label>Harga:</label><br>
    <input type="number" name="harga" required><br><br>
    
    <button type="submit">Tambah</button>
</form>
