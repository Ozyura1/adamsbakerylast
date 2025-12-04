<?php
session_start();
include '../backend/db.php';

// Fonnte WhatsApp gateway for sending notifications when status changes
require_once __DIR__ . '/../backend/fonnte_config.php';
require_once __DIR__ . '/../backend/FonnteGateway.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $transaction_id = $conn->real_escape_string($_POST['transaction_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    
    $sql = "UPDATE transactions SET status = '$new_status' WHERE id = '$transaction_id'";
    if ($conn->query($sql)) {
        $success = "Status transaksi berhasil diupdate!";

        // Send WhatsApp notification according to the status selected by admin
        if (defined('FONNTE_ENABLE_NOTIFICATIONS') && FONNTE_ENABLE_NOTIFICATIONS) {
            try {
                $gateway = new FonnteGateway();

                // Fetch transaction details
                $tranRes = $conn->query("SELECT * FROM transactions WHERE id = '$transaction_id'");
                if ($tranRes && $tranRes->num_rows > 0) {
                    $tran = $tranRes->fetch_assoc();
                    $phone_raw = $tran['phone'] ?? null;

                    if (empty($phone_raw)) {
                        error_log("Warning: Nomor telepon kosong untuk transaksi #" . $transaction_id);
                        $recipient = defined('FONNTE_FALLBACK_RECIPIENT') ? FONNTE_FALLBACK_RECIPIENT : null;
                    } else {
                        $recipient = $gateway->normalizePhoneNumber($phone_raw);
                    }

                    if ($recipient) {
                        $order_id = $tran['id'];
                        $nama = $tran['nama_pembeli'] ?? 'Pelanggan';
                        $total = isset($tran['total_amount']) ? 'Rp ' . number_format($tran['total_amount'], 0, ',', '.') : 'N/A';

                        // Map status to display text
                        $display_status = ucfirst($new_status);

                        // Build message similar to payment_success.php but reflecting new status
                        $message = "Hai {$nama},\n\n";
                        $message .= "Ini adalah pemberitahuan mengenai pesanan Anda di *Adam's Bakery* 🍞\n\n";
                        $message .= "📦 *Nomor Pesanan:* #{$order_id}\n";
                        $message .= "\n💰 *Total:* {$total}\n";
                        $message .= "📊 *Status:* {$display_status}\n\n";

                        if ($new_status === 'confirmed') {
                            $message .= "Pembayaran Anda telah dikonfirmasi. Invoice dan detail pesanan bisa dilihat di: " . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : '') . "/invoice.php?transaction_id={$order_id}\n\n";
                        } elseif ($new_status === 'cancelled') {
                            $message .= "Sayangnya pesanan Anda dibatalkan. Jika ini kesalahan, silakan hubungi kami untuk klarifikasi.\n\n";
                        } else {
                            $message .= "Status pesanan Anda diperbarui menjadi *{$display_status}*.\n\n";
                        }

                        $message .= "Pertanyaan? Hubungi kami kapan saja.\n\n";
                        $message .= "Salam hangat,\n*Adam's Bakery* 🥐";

                        $wa_result = $gateway->sendMessage($recipient, $message);
                        if ($wa_result['status']) {
                            $success .= ' Notifikasi WhatsApp berhasil dikirim ke pelanggan.';
                            error_log("WhatsApp notifikasi berhasil dikirim ke {$recipient} untuk order #{$order_id}");
                        } else {
                            error_log("WhatsApp notifikasi gagal untuk order #{$order_id}: " . json_encode($wa_result));
                            $success .= ' Namun notifikasi WhatsApp gagal dikirim.';
                        }
                    } else {
                        error_log("Error: Tidak bisa menentukan nomor penerima untuk transaksi #{$transaction_id}");
                        $success .= ' Namun notifikasi WhatsApp tidak dikirim (nomor tidak valid).';
                    }
                }

            } catch (Exception $e) {
                error_log('Exception saat mengirim WhatsApp pada update status: ' . $e->getMessage());
                $success .= ' Namun terjadi kesalahan saat mengirim notifikasi WhatsApp.';
            }
        }
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Handle toggle admin notification per-order
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_admin_notif'])) {
    $transaction_id = $conn->real_escape_string($_POST['transaction_id']);
    $disabled = isset($_POST['disable']) && $_POST['disable'] == '1' ? 1 : 0;
    $sql = "UPDATE transactions SET admin_notifications_disabled = $disabled WHERE id = '$transaction_id'";
    if ($conn->query($sql)) {
        $success = 'Pengaturan notifikasi admin berhasil diperbarui.';
    } else {
        $error = 'Error: ' . $conn->error;
    }
}

// Handle resend admin notification for a transaction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resend_admin_notif'])) {
    $transaction_id = intval($_POST['transaction_id']);
    try {
        require_once __DIR__ . '/../backend/admin_notifier.php';
        $notifier = new AdminNotifier($conn);
        $res = $notifier->notifyNewOrder($transaction_id, true);
        if ($res['status']) {
            $success = 'Notifikasi ulang berhasil dikirim.';
        } else {
            $error = 'Gagal mengirim notifikasi ulang: ' . ($res['reason'] ?? $res['error'] ?? 'Unknown');
        }
    } catch (Exception $e) {
        $error = 'Exception: ' . $e->getMessage();
    }
}

// Get all transactions
$transactions = $conn->query("SELECT * FROM transactions ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Transaksi - Admin Adam Bakery</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/logoadambakery.png">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #d4af8c;
            text-align: center;
        }
        th {
            background-color: #d4af8c;
        }
        img.bukti-img {
            width: 70px;
            height: auto;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .no-proof {
            color: gray;
            font-style: italic;
        }
    </style>
</head>
<body>
<header class="admin-header">
    <h1>Daftar Transaksi - Adam Bakery</h1>
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
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <h2>Daftar Transaksi</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Pembeli</th>
                <th>Email</th>
                <th>Total</th>
                <th>Bank</th>
                <th>Bukti Pembayaran</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($transaction = $transactions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $transaction['id']; ?></td>
                <td><?php echo htmlspecialchars($transaction['nama_pembeli']); ?></td>
                <td><?php echo htmlspecialchars($transaction['email']); ?></td>
                <td>Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($transaction['bank_name']); ?></td>

                <!-- 🧾 Tampilan Bukti Pembayaran -->
                <td>
                    <?php if (!empty($transaction['bukti_pembayaran'])): ?>
                        <a href="../uploads/bukti_pembayaran/<?php echo htmlspecialchars($transaction['bukti_pembayaran']); ?>" target="_blank">
                            <img src="../uploads/bukti_pembayaran/<?php echo htmlspecialchars($transaction['bukti_pembayaran']); ?>" class="bukti-img" alt="Bukti">
                        </a>
                    <?php else: ?>
                        <span class="no-proof">Belum ada</span>
                    <?php endif; ?>
                </td>

                <td><?php echo ucfirst($transaction['status'] ?? 'pending');?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>

                <td>
                    <div style="display:flex; gap:8px; align-items:center; justify-content:center;">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?php echo $transaction['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $transaction['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $transaction['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>

                        <!-- Toggle admin notification for this order -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                            <input type="hidden" name="toggle_admin_notif" value="1">
                            <label style="font-size:12px; display:flex; align-items:center; gap:6px;">
                                <input type="checkbox" name="disable" value="1" onchange="this.form.submit()" <?php echo !empty($transaction['admin_notifications_disabled']) ? 'checked' : ''; ?>>
                                <span style="font-size:12px;">Disable WA</span>
                            </label>
                        </form>

                        <!-- Resend notification button -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                            <input type="hidden" name="resend_admin_notif" value="1">
                            <button type="submit" style="padding:6px 8px; background:#007bff; color:#fff; border:none; border-radius:4px;">Resend</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <form action="export_excel.php" method="post" style="text-align:center; margin-top:20px;">
        <button type="submit" class="btn-export">Ekspor Laporan Penjualan</button>
    </form>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
