<?php
include '../backend/db.php';

// Set zona waktu
date_default_timezone_set('Asia/Jakarta');
$tanggal_hari_ini = date('Y-m-d');

// Ambil transaksi yang dikonfirmasi dan dibuat hari ini
$query = "
    SELECT * FROM transactions
    WHERE DATE(created_at) = '$tanggal_hari_ini'
    AND status = 'confirmed'
    ORDER BY created_at DESC
";
$result = $conn->query($query);

// Jika tidak ada transaksi hari ini
if ($result->num_rows == 0) {
    echo "<script>
        alert('Tidak ada transaksi hari ini yang bisa diekspor.');
        window.location.href = 'view_transaction.php';
    </script>";
    exit();
}

// Header untuk file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_penjualan_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Cetak data transaksi
echo "<table border='1'>";
echo "<tr>
<th>ID</th>
<th>Nama Pembeli</th>
<th>Email</th>
<th>Total</th>
<th>Bank</th>
<th>Status</th>
<th>Tanggal</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['nama_pembeli']}</td>";
    echo "<td>{$row['email']}</td>";
    echo "<td>Rp " . number_format($row['total_amount'], 0, ',', '.') . "</td>";
    echo "<td>{$row['bank_name']}</td>";
    echo "<td>{$row['status']}</td>";
    echo "<td>" . date('d/m/Y H:i', strtotime($row['created_at'])) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Hapus transaksi hari ini setelah ekspor
$conn->query("
    DELETE FROM transactions 
    WHERE DATE(created_at) = '$tanggal_hari_ini'
    AND status = 'confirmed'
");

$conn->close();
?>
