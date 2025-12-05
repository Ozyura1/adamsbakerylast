<?php
include 'includes/header.php';


$transaction = null; // ✅ tambahkan inisialisasi awal biar tidak undefined
$items = null;       // ✅ inisialisasi juga untuk keamanan

$transaction_id = isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : 0;

if ($transaction_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $transaction = $result->fetch_assoc();

        $items_stmt = $conn->prepare("
            SELECT ti.*, 
                   CASE 
                       WHEN ti.item_type = 'product' THEN p.nama
                       WHEN ti.item_type = 'package' THEN pkg.nama
                   END as item_name
            FROM transaction_items ti
            LEFT JOIN products p ON ti.product_id = p.id
            LEFT JOIN packages pkg ON ti.package_id = pkg.id
            WHERE ti.transaction_id = ?
        ");
        $items_stmt->bind_param('i', $transaction_id);
        $items_stmt->execute();
        $items = $items_stmt->get_result();
    }
    $stmt->close();
}
?>

<main style="background: #fff; padding: 2rem; margin: 2rem auto; max-width: 700px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
    <?php if ($transaction): ?>
        <h2 style="text-align: center; color: #6b4226;">Invoice Pembelian</h2>
        <hr><br>

        <p><strong>No. Invoice:</strong> INV-<?php echo str_pad($transaction['id'], 5, '0', STR_PAD_LEFT); ?></p>
        <p><strong>Nama Pembeli:</strong> <?php echo htmlspecialchars($transaction['nama_pembeli'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($transaction['email'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Tanggal:</strong> <?php echo date('d-m-Y', strtotime($transaction['created_at'])); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($transaction['status']), ENT_QUOTES, 'UTF-8'); ?></p>
        <br>

        <?php if ($items && $items->num_rows > 0): ?>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f2d9b3;">
                    <th style="padding: 8px; border: 1px solid #ccc;">Item</th>
                    <th style="padding: 8px; border: 1px solid #ccc;">Qty</th>
                    <th style="padding: 8px; border: 1px solid #ccc;">Harga</th>
                    <th style="padding: 8px; border: 1px solid #ccc;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                while ($item = $items->fetch_assoc()):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ccc;"><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;"><?php echo $item['quantity']; ?></td>
                    <td style="padding: 8px; border: 1px solid #ccc;">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ccc;">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3 style="text-align: right; margin-top: 1rem;">Total: Rp <?php echo number_format($total, 0, ',', '.'); ?></h3>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem;">
            <button onclick="window.print()" 
                style="background: #d4a373; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin-right: 10px;">
                Cetak Invoice
            </button>

            <a href="invoice_download.php?transaction_id=<?php echo $transaction_id; ?>" 
                style="background: #6b4226; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px;">
                Download PDF
            </a>
        </div>

    <?php else: ?>
        <p>Data transaksi tidak ditemukan.</p>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
