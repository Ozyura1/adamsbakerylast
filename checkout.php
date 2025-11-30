<?php 
require_once 'includes/init.php';

requireAuth('checkout.php');

$conn = Database::getInstance()->getConnection();

// Generate CSRF token
$csrfToken = CSRFToken::generate();

// Initialize variables
$cartItems = [];
$cartTotal = 0;
$transactionId = null;
$transaction = null;
$showCheckoutForm = false;

if (isset($_GET['transaction_id']) || isset($_GET['order_id'])) {
    $transactionId = (int)($_GET['transaction_id'] ?? $_GET['order_id']);
    
    $stmt = $conn->prepare('SELECT * FROM transactions WHERE id = ? AND customer_id = ?');
    $stmt->bind_param('ii', $transactionId, $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $transaction = $result->fetch_assoc();
    }
    $stmt->close();
}

if (isset($_GET['remove'])) {
    $removeKey = InputSanitizer::sanitizeString($_GET['remove']);
    unset($_SESSION['cart'][$removeKey]);
    redirectWithMessage('checkout.php', 'Item berhasil dihapus dari keranjang!', 'success');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (!CSRFToken::verify($_POST['csrf_token'] ?? '')) {
        displaySessionAlert();
        $_SESSION['alert_message'] = 'Token keamanan tidak valid!';
        $_SESSION['alert_type'] = 'error';
    } else {
        foreach ($_POST['quantities'] as $key => $qty) {
            $key = InputSanitizer::sanitizeString($key);
            $qty = (int)$qty;
            
            if ($qty > 0) {
                $_SESSION['cart'][$key]['quantity'] = $qty;
            } else {
                unset($_SESSION['cart'][$key]);
            }
        }
        
        $_SESSION['alert_message'] = 'Keranjang berhasil diperbarui!';
        $_SESSION['alert_type'] = 'success';
        $showCheckoutForm = true;
    }
}

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $item) {
        $table = $item['type'] === 'product' ? 'products' : 'packages';
        $itemId = (int)$item['id'];
        
        $stmt = $conn->prepare("SELECT id, nama, harga FROM $table WHERE id = ?");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $subtotal = $row['harga'] * $item['quantity'];
            
            $cartItems[$key] = [
                'name' => $row['nama'],
                'price' => $row['harga'],
                'quantity' => $item['quantity'],
                'type' => $item['type'],
                'subtotal' => $subtotal
            ];
            
            $cartTotal += $subtotal;
        }
        
        $stmt->close();
    }
}

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main class="checkout-page">
        <?php displaySessionAlert(); ?>
        
        <section class="page-header">
            <h2>Keranjang Belanja</h2>
        </section>
        
        <?php if (empty($cartItems) && !$transaction): ?>
            <!-- Empty Cart State -->
            <div class="empty-cart">
                <p>Keranjang belanja Anda kosong.</p>
                <a href="products.php" class="btn btn-primary">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <!-- Cart Contents -->
            <div class="cart-content">
                <form method="post" class="cart-form">
                    <input type="hidden" name="csrf_token" value="<?php echo InputSanitizer::escapeAttr($csrfToken); ?>">
                    
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $key => $item): ?>
                                <tr>
                                    <td class="item-name">
                                        <?php echo InputSanitizer::escapeHtml($item['name']); ?>
                                        <span class="item-type">(<?php echo ucfirst($item['type']); ?>)</span>
                                    </td>
                                    <td class="item-price"><?php echo formatCurrency($item['price']); ?></td>
                                    <td class="item-quantity">
                                        <input type="number" 
                                               name="quantities[<?php echo InputSanitizer::escapeAttr($key); ?>]" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" 
                                               max="100">
                                    </td>
                                    <td class="item-subtotal"><?php echo formatCurrency($item['subtotal']); ?></td>
                                    <td class="item-action">
                                        <a href="?remove=<?php echo InputSanitizer::escapeAttr($key); ?>" 
                                           class="btn btn-danger"
                                           onclick="return confirm('Hapus item ini?')">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="cart-total-row">
                                <td colspan="3"><strong>Total</strong></td>
                                <td><strong><?php echo formatCurrency($cartTotal); ?></strong></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <?php if (!$showCheckoutForm): ?>
                        <div class="cart-actions">
                            <button type="submit" name="update_cart" class="btn btn-primary">Update Keranjang</button>
                        </div>
                    <?php endif; ?>
                </form>
                
                <!-- Checkout Form -->
                <?php if ($showCheckoutForm && !empty($cartItems)): ?>
                    <form method="post" action="backend/process_payment.php" enctype="multipart/form-data" class="checkout-form">
                        <input type="hidden" name="csrf_token" value="<?php echo InputSanitizer::escapeAttr($csrfToken); ?>">
                        <input type="hidden" name="total_amount" value="<?php echo (int)$cartTotal; ?>">
                        <input type="hidden" name="customer_id" value="<?php echo (int)$_SESSION['customer_id']; ?>">
                        
                        <section class="checkout-section">
                            <h3>Informasi Pembeli</h3>
                            
                            <div class="form-group">
                                <label for="nama_pembeli">Nama Lengkap:</label>
                                <input type="text" 
                                       id="nama_pembeli" 
                                       name="nama_pembeli" 
                                       value="<?php echo InputSanitizer::escapeAttr($user['nama_lengkap']); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo InputSanitizer::escapeAttr($user['email']); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">No. Telepon:</label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo InputSanitizer::escapeAttr($user['phone'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="alamat">Alamat Lengkap:</label>
                                <textarea id="alamat" name="alamat" rows="3" required>
<?php echo InputSanitizer::escapeHtml($user['alamat'] ?? ''); ?></textarea>
                            </div>
                        </section>
                        
                        <section class="checkout-section">
                            <h3>Informasi Transfer Bank</h3>
                            <div class="bank-info">
                                <p><strong>Rekening Tujuan:</strong></p>
                                <p>Mandiri: 1390088899913 a.n. Adam Bakery</p>
                                <p><a href="assets/qris.jpg" target="_blank">Lihat QRIS</a></p>
                            </div>
                            
                            <div class="form-group">
                                <label for="account_name">Nama Pemilik Rekening (Pengirim):</label>
                                <input type="text" id="account_name" name="account_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="account_number">Nomor Rekening (Pengirim):</label>
                                <small>Jika menggunakan QRIS, ketik "-"</small>
                                <input type="text" id="account_number" name="account_number" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="bank_name">Bank Tujuan:</label>
                                <select id="bank_name" name="bank_name" required>
                                    <option value="">Pilih Bank</option>
                                    <option value="Mandiri">Mandiri</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="transfer_amount">Jumlah Transfer:</label>
                                <input type="number" 
                                       id="transfer_amount" 
                                       name="transfer_amount" 
                                       value="<?php echo (int)$cartTotal; ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="transfer_proof">Bukti Transfer (Wajib):</label>
                                <input type="file" 
                                       id="transfer_proof" 
                                       name="transfer_proof" 
                                       accept="image/*" 
                                       required>
                            </div>
                        </section>
                        
                        <div class="checkout-actions">
                            <button type="submit" class="btn btn-primary">Proses Pembayaran</button>
                            <a href="checkout.php" class="btn btn-secondary">Kembali ke Keranjang</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
