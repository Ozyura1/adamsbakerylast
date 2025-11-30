<?php
require('fpdf/fpdf.php');
include 'backend/db.php';

$transaction_id = isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : 0;

if ($transaction_id > 0) {
    $result = $conn->query("SELECT * FROM transactions WHERE id = $transaction_id");
    $transaction = $result->fetch_assoc();

    $items_query = "
        SELECT ti.*, 
               CASE 
                   WHEN ti.item_type = 'product' THEN p.nama
                   WHEN ti.item_type = 'package' THEN pkg.nama
               END as item_name
        FROM transaction_items ti
        LEFT JOIN products p ON ti.product_id = p.id
        LEFT JOIN packages pkg ON ti.package_id = pkg.id
        WHERE ti.transaction_id = $transaction_id
    ";
    $items = $conn->query($items_query);
} else {
    die("Transaksi tidak ditemukan");
}

class PDF extends FPDF
{
    function Header()
{
    // Header background color
    $this->SetFillColor(241, 221, 188);
    $this->Rect(0, 0, 210, 60, 'F');

    // Logo di atas nama toko, posisi tengah
    if (file_exists('assets/logoadambakery.png')) {
        // x, y, width — diatur supaya berada di tengah
        $this->Image('assets/logoadambakery.png', 92, 5, 25);
    }

    // Jarak antara logo dan teks
    $this->Ln(25);

    // Nama toko
    $this->SetFont('Arial', 'B', 20);
    $this->SetTextColor(107, 66, 38);
    $this->Cell(0, 10, 'Adams Bakery', 0, 1, 'C');

    // Alamat
    $this->SetFont('Arial', '', 11);
    $this->Cell(0, 8, 'Jl. Raya Pacul, Kademangan, Mejasem Bar., Kec. Dukuhturi, Kabupaten Tegal, Jawa Tengah 52472', 0, 1, 'C');

    // Spasi sedikit
    $this->Ln(5);

    // Garis bawah header
    $this->SetDrawColor(180, 150, 120);
    $this->Line(10, 60, 200, 60);
    $this->Ln(15);
}

}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(60, 40, 20);

// --- Detail Transaksi ---
$pdf->Cell(0, 8, 'No. Invoice: INV-' . str_pad($transaction['id'], 5, '0', STR_PAD_LEFT), 0, 1);
$pdf->Cell(0, 8, 'Nama Pembeli: ' . $transaction['nama_pembeli'], 0, 1);
$pdf->Cell(0, 8, 'Email: ' . $transaction['email'], 0, 1);
$pdf->Cell(0, 8, 'Tanggal: ' . date('d-m-Y', strtotime($transaction['created_at'])), 0, 1);
$pdf->Cell(0, 8, 'Status: ' . ucfirst($transaction['status']), 0, 1);
$pdf->Ln(6);

// --- Tabel ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(242, 217, 179);
$pdf->SetDrawColor(200, 170, 140);
$pdf->Cell(80, 10, 'Item', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Qty', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Harga', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Subtotal', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12);
$total = 0;

while ($item = $items->fetch_assoc()) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;

    $pdf->Cell(80, 10, $item['item_name'], 1);
    $pdf->Cell(30, 10, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(40, 10, 'Rp ' . number_format($item['price'], 0, ',', '.'), 1);
    $pdf->Cell(40, 10, 'Rp ' . number_format($subtotal, 0, ',', '.'), 1);
    $pdf->Ln();
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(150, 10, 'Total', 1);
$pdf->Cell(40, 10, 'Rp ' . number_format($total, 0, ',', '.'), 1, 1, 'R');

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 8, "Silakan simpan invoice ini sebagai bukti pembayaran. Pesanan Anda akan segera diproses setelah dikonfirmasi oleh admin.", 0, 'L');

$pdf->Output('D', 'Invoice_' . $transaction_id . '.pdf');
exit;
?>
