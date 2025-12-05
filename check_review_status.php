<?php
include 'backend/db.php';
include 'backend/csrf.php';
session_start();

// Generate CSRF token
generateCSRFToken();

if (isset($_GET['email']) && isset($_GET['transaction_id'])) {
    $email = $_GET['email'];
    $transaction_id = intval($_GET['transaction_id']);
    
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND email = ? AND status = 'confirmed'");
    $stmt->bind_param("is", $transaction_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: review.php?transaction_id=" . $transaction_id);
        exit();
    } else {
        $error = "Transaksi tidak ditemukan atau belum dikonfirmasi.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cek Status Ulasan - Adam Bakery</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/png" href="/assets/images/logoadambakery.png">
</head>
<body>
<?php include 'includes/header.php'; ?>

<main>
    <h2>Beri Status Ulasan</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="get">
        <!-- Tambahkan CSRF token ke form -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
        
        <p>Masukkan email dan ID transaksi untuk memberikan ulasan:</p>
        
        <label>Email:</label>
        <input type="email" name="email" required>
        
        <label>ID Transaksi:</label>
        <input type="text" name="transaction_id" required>
        
        <button type="submit">Cek Status</button>
    </form>
    
    <div class="text-center mt-2">
        <a href="index.php" class="btn">Kembali ke Beranda</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
