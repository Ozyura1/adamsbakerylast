<?php
/**
 * Payment Processing Handler
 * Handles transaction creation with comprehensive validation and security
 */

require_once __DIR__ . '/../includes/init.php';

// Require authentication
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../checkout.php', 'Invalid request method', 'error');
}

if (!CSRFToken::verify($_POST['csrf_token'] ?? '')) {
    redirectWithMessage('../checkout.php', 'Token keamanan tidak valid!', 'error');
}

$conn = Database::getInstance()->getConnection();

$validator = new Validator();
$validator->required($_POST['nama_pembeli'] ?? '', 'nama_pembeli');
$validator->email($_POST['email'] ?? '', 'email');
$validator->phone($_POST['phone'] ?? '', 'phone');
$validator->required($_POST['alamat'] ?? '', 'alamat');
$validator->required($_POST['account_name'] ?? '', 'account_name');
$validator->required($_POST['account_number'] ?? '', 'account_number');
$validator->inArray($_POST['bank_name'] ?? '', 'bank_name', ['Mandiri', 'Lainnya']);
$validator->required($_POST['transfer_amount'] ?? '', 'transfer_amount');

if (!$validator->passes()) {
    redirectWithMessage('../checkout.php', $validator->getFirstError(), 'error');
}

$namaPembeli = InputSanitizer::sanitizeString($_POST['nama_pembeli']);
$email = InputSanitizer::sanitizeEmail($_POST['email']);
$phone = InputSanitizer::sanitizePhone($_POST['phone']);
$alamat = InputSanitizer::sanitizeString($_POST['alamat']);
$totalAmount = (int)($_POST['total_amount'] ?? 0);
$accountName = InputSanitizer::sanitizeString($_POST['account_name']);
$accountNumber = InputSanitizer::sanitizeString($_POST['account_number']);
$bankName = InputSanitizer::sanitizeString($_POST['bank_name']);
$transferAmount = (int)($_POST['transfer_amount'] ?? 0);
$customerId = (int)($_SESSION['customer_id'] ?? 0);

// Validate amounts
if ($totalAmount <= 0 || $transferAmount <= 0) {
    redirectWithMessage('../checkout.php', 'Jumlah tidak valid', 'error');
}

$buktiPembayaran = null;

if (isset($_FILES['transfer_proof'])) {
    // Check if file was actually provided
    if ($_FILES['transfer_proof']['error'] === UPLOAD_ERR_NO_FILE) {
        redirectWithMessage('../checkout.php', 'Bukti pembayaran harus diunggah', 'error');
    }

    $fileValidation = InputSanitizer::validateFileUpload($_FILES['transfer_proof']);
    
    if (!$fileValidation['valid']) {
        redirectWithMessage('../checkout.php', $fileValidation['error'], 'error');
    }
    
    // Create upload directory with proper error handling
    $uploadDir = __DIR__ . '/../uploads/bukti_pembayaran/';
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            redirectWithMessage('../checkout.php', 'Gagal membuat direktori upload. Hubungi admin.', 'error');
        }
    }
    
    // Verify directory is writable
    if (!is_writable($uploadDir)) {
        redirectWithMessage('../checkout.php', 'Direktori upload tidak dapat ditulis. Hubungi admin.', 'error');
    }
    
    // Generate safe file name
    $fileName = InputSanitizer::generateSafeFileName($_FILES['transfer_proof']['name']);
    $filePath = $uploadDir . $fileName;
    
    // Move uploaded file with error handling
    if (!move_uploaded_file($_FILES['transfer_proof']['tmp_name'], $filePath)) {
        error_log("Upload failed for: " . $_FILES['transfer_proof']['name'] . " | Error: " . $_FILES['transfer_proof']['error']);
        redirectWithMessage('../checkout.php', 'Gagal mengunggah file bukti pembayaran. Coba lagi atau hubungi admin.', 'error');
    }
    
    // Verify file was actually written
    if (!file_exists($filePath)) {
        redirectWithMessage('../checkout.php', 'File tidak berhasil disimpan. Coba lagi.', 'error');
    }
    
    $buktiPembayaran = $fileName;
}

$status = 'pending';

$stmt = $conn->prepare('
    INSERT INTO transactions 
    (customer_id, nama_pembeli, email, phone, alamat, total_amount, bank_name, account_name, account_number, transfer_amount, bukti_pembayaran, status, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
');

$stmt->bind_param(
    'issssisissss',
    $customerId,
    $namaPembeli,
    $email,
    $phone,
    $alamat,
    $totalAmount,
    $bankName,
    $accountName,
    $accountNumber,
    $transferAmount,
    $buktiPembayaran,
    $status
);


if (!$stmt->execute()) {
    redirectWithMessage('../checkout.php', 'Gagal membuat transaksi', 'error');
}

$transactionId = $conn->insert_id;
$stmt->close();

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $itemType = $item['type'] === 'product' ? 'product' : 'package';
        $itemId = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        
        // Get item price
        $table = $itemType === 'product' ? 'products' : 'packages';
        $priceStmt = $conn->prepare("SELECT harga FROM $table WHERE id = ?");
        $priceStmt->bind_param('i', $itemId);
        $priceStmt->execute();
        $priceResult = $priceStmt->get_result();
        
        if ($priceResult->num_rows === 0) {
            continue;
        }
        
        $price = $priceResult->fetch_assoc()['harga'];
        $priceStmt->close();
        
        // Insert transaction item
        if ($itemType === 'product') {
            $itemStmt = $conn->prepare('
                INSERT INTO transaction_items (transaction_id, product_id, item_type, quantity, price) 
                VALUES (?, ?, ?, ?, ?)
            ');
            $itemStmt->bind_param('iisii', $transactionId, $itemId, $itemType, $quantity, $price);
        } else {
            $itemStmt = $conn->prepare('
                INSERT INTO transaction_items (transaction_id, package_id, item_type, quantity, price) 
                VALUES (?, ?, ?, ?, ?)
            ');
            $itemStmt->bind_param('iisii', $transactionId, $itemId, $itemType, $quantity, $price);
        }
        
        $itemStmt->execute();
        $itemStmt->close();
    }
}

// Clear cart
unset($_SESSION['cart']);
$_SESSION['last_transaction_id'] = $transactionId;
$_SESSION['has_order'] = true;

// Log activity
logActivity('TRANSACTION_CREATED', "Transaction ID: $transactionId, Amount: $totalAmount");

// Redirect to success page
header('Location: ../payment_success.php?transaction_id=' . $transactionId);
exit();
?>
