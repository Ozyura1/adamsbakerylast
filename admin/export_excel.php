<?php
session_start();
include '../backend/db.php';

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Set zona waktu
date_default_timezone_set('Asia/Jakarta');

// Tentukan tipe export (default: today)
$export_type = $_POST['export_type'] ?? 'today';
$tanggal_hari_ini = date('Y-m-d');

// Build query berdasarkan tipe export
if ($export_type === 'today') {
    // Ekspor transaksi hari ini yang confirmed
    $stmt = $conn->prepare("
        SELECT * FROM transactions
        WHERE DATE(created_at) = ?
        AND status = 'confirmed'
        ORDER BY created_at DESC
    ");
    $stmt->bind_param('s', $tanggal_hari_ini);
    $filename_prefix = "laporan_hari_ini_";
} else {
    // Ekspor semua transaksi yang confirmed
    $stmt = $conn->prepare("
        SELECT * FROM transactions
        WHERE status = 'confirmed'
        ORDER BY created_at DESC
    ");
    $filename_prefix = "laporan_semua_";
}

$stmt->execute();
$result = $stmt->get_result();

// Jika tidak ada transaksi
if ($result->num_rows == 0) {
    $_SESSION['info'] = 'Tidak ada transaksi yang bisa diekspor.';
    header("Location: view_transactions.php");
    exit();
}

// Header untuk file CSV
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=" . $filename_prefix . date('Ymd_His') . ".csv");
header("Pragma: no-cache");
header("Expires: 0");

// Output BOM untuk UTF-8
echo "\xEF\xBB\xBF";

// CSV Header
echo "ID,Nama Pembeli,Email,Total,Bank,Status,Tanggal\n";

// Data transaksi
while ($row = $result->fetch_assoc()) {
    $nama = str_replace('"', '""', $row['nama_pembeli']);
    $email = $row['email'];
    $total = $row['total_amount'];
    $bank = $row['bank_name'];
    $status = $row['status'];
    $tanggal = date('d/m/Y H:i', strtotime($row['created_at']));
    
    echo "\"{$row['id']}\",\"$nama\",\"$email\",\"Rp " . number_format($total, 0, ',', '.') . "\",\"$bank\",\"$status\",\"$tanggal\"\n";
}

$stmt->close();
$conn->close();
?>
