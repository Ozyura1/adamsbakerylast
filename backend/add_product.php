<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $conn->real_escape_string($_POST['nama']);
    $harga = $conn->real_escape_string($_POST['harga']);

    $sql = "INSERT INTO products (nama, harga) VALUES ('$nama', '$harga')";
    if ($conn->query($sql) === TRUE) {
        echo "Produk berhasil ditambahkan!";
    } else {
        echo "Error: " . $conn->error;
    }
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
