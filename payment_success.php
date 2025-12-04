<?php 
include 'includes/header.php';


require_once __DIR__ . '/backend/fonnte_config.php';
require_once __DIR__ . '/backend/FonnteGateway.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$transaction = null;
$transaction_id = isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : null;
$whatsapp_status = null;

if ($transaction_id) {
    $result = $conn->query("SELECT * FROM transactions WHERE id = $transaction_id");
    $transaction = $result->fetch_assoc();
    
    $items_query = "
        SELECT ti.*, 
               CASE 
                   WHEN ti.item_type = 'product' THEN p.nama
                   WHEN ti.item_type = 'package' THEN pkg.nama
               END as item_name,
               CASE 
                   WHEN ti.item_type = 'product' THEN p.harga
                   WHEN ti.item_type = 'package' THEN pkg.harga
               END as item_price
        FROM transaction_items ti
        LEFT JOIN products p ON ti.product_id = p.id
        LEFT JOIN packages pkg ON ti.package_id = pkg.id
        WHERE ti.transaction_id = $transaction_id
    ";
    $items = $conn->query($items_query);

    $_SESSION['has_order'] = true;
    $_SESSION['last_transaction_id'] = $transaction_id;

    if (defined('FONNTE_ENABLE_NOTIFICATIONS') && FONNTE_ENABLE_NOTIFICATIONS && !empty($transaction)) {
        try {
            $gateway = new FonnteGateway();
            
            // Get phone number with fallback
            $phone_raw = $transaction['phone'] ?? null;
            
            if (empty($phone_raw)) {
                error_log("Warning: Nomor telepon kosong untuk transaksi #" . $transaction['id']);
                $recipient = defined('FONNTE_FALLBACK_RECIPIENT') ? FONNTE_FALLBACK_RECIPIENT : null;
            } else {
                $recipient = $gateway->normalizePhoneNumber($phone_raw);
            }

            if ($recipient) {
                $order_id = $transaction['id'];
                $nama = $transaction['nama_pembeli'] ?? 'Pelanggan';
                $total = isset($transaction['total_amount']) ? 'Rp ' . number_format($transaction['total_amount'], 0, ',', '.') : 'N/A';
                $status = ucfirst($transaction['status'] ?? 'pending');

                // Build item list for message
                $items_text = "";
                if ($items && $items->num_rows > 0) {
                    $items->data_seek(0);
                    while ($it = $items->fetch_assoc()) {
                        $item_name = $it['item_name'] ?? 'Item';
                        $quantity = $it['quantity'] ?? 1;
                        $items_text .= "• {$item_name} x{$quantity}\n";
                    }
                }

                $message = "Hai {$nama},\n\n";
                $message .= "Terima kasih telah memesan di *Adam's Bakery* 🍞\n\n";
                $message .= "Pesananmu telah kami terima dan sedang kami proses.\n\n";
                $message .= "📦 *Nomor Pesanan:* #{$order_id}\n";
                
                if ($items_text) {
                    $message .= "\n*Item Pesanan:*\n{$items_text}";
                }
                
                $message .= "\n💰 *Total:* {$total}\n";
                $message .= "📊 *Status:* {$status}\n\n";
                $message .= "Jika sudah melakukan pembayaran, silakan tunggu konfirmasi dari kami (1-2 jam kerja).\n\n";
                $message .= "Pertanyaan? Hubungi kami kapan saja.\n\n";
                $message .= "Salam hangat,\n*Adam's Bakery* 🥐";

                // Send WhatsApp message
                $wa_result = $gateway->sendMessage($recipient, $message);
                
                if ($wa_result['status']) {
                    $whatsapp_status = [
                        'success' => true,
                        'message_id' => $wa_result['message_id'] ?? null,
                        'phone' => $wa_result['phone'] ?? $recipient
                    ];
                    error_log("WhatsApp notifikasi berhasil dikirim ke {$recipient} untuk order #{$order_id}");
                } else {
                    $whatsapp_status = [
                        'success' => false,
                        'error' => $wa_result['error'] ?? 'Unknown error',
                        'code' => $wa_result['code'] ?? null
                    ];
                    error_log("WhatsApp notifikasi gagal untuk order #{$order_id}: " . json_encode($whatsapp_status));
                }
            } else {
                error_log("Error: Tidak bisa menentukan nomor penerima untuk transaksi #{$transaction_id}");
                $whatsapp_status = [
                    'success' => false,
                    'error' => 'Nomor telepon tidak valid'
                ];
            }
        } catch (Exception $e) {
            error_log("Exception saat mengirim WhatsApp: " . $e->getMessage());
            $whatsapp_status = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Clear cart after successful order
    if (isset($_SESSION['cart'])) {
        unset($_SESSION['cart']);
    }
}
?>

<style>
    main {
        background-color: #f6e5c8;
        border-radius: 20px;
        padding: 2.5rem;
        position: relative;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid;
    }

    .alert-success {
        background: #e8f5e9;
        color: #2e7d32;
        border-left-color: #2e7d32;
    }

    .alert-warning {
        background: #fff3e0;
        color: #e65100;
        border-left-color: #e65100;
    }

    .alert-error {
        background: #ffebee;
        color: #c62828;
        border-left-color: #c62828;
    }

    .alert h2 {
        margin: 0 0 0.5rem 0;
    }

    .details-box {
        background: #fff;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(139, 90, 60, 0.1);
        margin: 2rem 0;
    }

    .details-box h3 {
        color: #8b5a3c;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #f4e4c1;
        padding-bottom: 0.5rem;
    }

    .details-box p {
        margin: 0.75rem 0;
        font-size: 1rem;
    }

    .details-box ul {
        margin: 1rem 0;
        padding-left: 1.5rem;
    }

    .details-box li {
        margin: 0.5rem 0;
    }

    .next-steps {
        background: #f4e4c1;
        padding: 1.5rem;
        border-radius: 10px;
        margin: 2rem 0;
    }

    .next-steps h3 {
        margin-top: 0;
    }

    .next-steps ol {
        margin: 1rem 0;
        padding-left: 2rem;
    }

    .next-steps li {
        margin: 0.75rem 0;
    }

    .button-group {
        text-align: center;
        margin: 2rem 0;
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn {
        background-color: #8b5a3c;
        color: #fff;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 25px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
        display: inline-block;
    }

    .btn:hover {
        background-color: #70492f;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #f4e4c1, #e8d5b7);
        color: #8b5a3c;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #f1dab2, #e0cba5);
    }

    .btn-invoice {
        background-color: #d4a373;
    }

    .btn-invoice:hover {
        background-color: #c4934f;
    }

    @media (max-width: 768px) {
        main {
            padding: 1.5rem;
        }

        .details-box {
            padding: 1.5rem;
        }

        .button-group {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<main>
    <!-- Added WhatsApp notification status display -->
    <?php if ($whatsapp_status && !$whatsapp_status['success']): ?>
        <div class="alert alert-warning">
            <h2>Catatan</h2>
            <p>Notifikasi WhatsApp mungkin tertunda atau gagal dikirim. Silakan hubungi kami jika Anda tidak menerima pesan.</p>
        </div>
    <?php endif; ?>

    <?php if ($transaction): ?>
        <div class="alert alert-success">
            <h2>Pembayaran Berhasil Diterima!</h2>
            <p>Terima kasih atas pesanan Anda. Kami telah mengirimkan konfirmasi ke WhatsApp Anda.</p>
            <p><strong>Nomor Transaksi:</strong> <span style="font-size: 1.2rem;">#<?php echo $transaction['id']; ?></span></p>
        </div>
        
        <div class="details-box">
            <h3>Detail Pesanan</h3>
            <p><strong>Nama Pemesan:</strong> <?php echo htmlspecialchars($transaction['nama_pembeli']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($transaction['email']); ?></p>
            <p><strong>Nomor Telepon:</strong> <?php echo htmlspecialchars($transaction['phone'] ?? '-'); ?></p>
            <p><strong>Alamat Pengiriman:</strong> <?php echo htmlspecialchars($transaction['alamat']); ?></p>
            <p><strong>Total Pembayaran:</strong> <span style="font-weight: bold; font-size: 1.1rem; color: #8b5a3c;">Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></span></p>
            <p><strong>Status Pesanan:</strong> <span style="background: #f4e4c1; padding: 0.25rem 0.75rem; border-radius: 20px;"><?php echo ucfirst($transaction['status'] ?? 'pending'); ?></span></p>
            
            <h3 style="margin-top: 2rem;">Item yang Dipesan</h3>
            <ul>
                <?php 
                $items->data_seek(0);
                while ($item = $items->fetch_assoc()): 
                ?>
                <li>
                    <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                    <br/>Qty: <?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?> 
                    = Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                </li>
                <?php endwhile; ?>
            </ul>

            <!-- Invoice button (only if confirmed) -->
            <?php if (strtolower($transaction['status'] ?? '') === 'confirmed'): ?>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="invoice.php?transaction_id=<?php echo $transaction['id']; ?>" 
                       class="btn btn-invoice">
                       Lihat Invoice
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="next-steps">
            <h3>Langkah Selanjutnya</h3>
            <ol>
                <li><strong>Verifikasi Pembayaran:</strong> Transfer sesuai jumlah yang tertera ke rekening yang Anda pilih</li>
                <li><strong>Tunggu Konfirmasi:</strong> Admin kami akan mengkonfirmasi pembayaran dalam 1-2 jam kerja</li>
                <li><strong>Siap Diproses:</strong> Setelah dikonfirmasi, pesanan akan diproses dan siap diambil/dikirim</li>
                <li><strong>Berikan Ulasan:</strong> Setelah menerima pesanan, Anda dapat memberikan ulasan dan rating</li>
            </ol>
        </div>
        
        <div class="button-group">
            <a href="index.php" class="btn">Kembali ke Beranda</a>
            <a href="products.php" class="btn btn-secondary">Belanja Lagi</a>
        </div>
        
    <?php else: ?>
        <div class="alert alert-error">
            <h2>Pesanan Tidak Ditemukan</h2>
            <p>Maaf, kami tidak dapat menemukan pesanan Anda. Silakan hubungi kami jika ada pertanyaan.</p>
        </div>
        <div class="button-group">
            <a href="index.php" class="btn">Kembali ke Beranda</a>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
