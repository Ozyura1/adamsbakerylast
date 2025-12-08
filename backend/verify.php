<?php
require 'db.php';
session_start();

// ====== CEGAH AKSES TANPA EMAIL ======
if (!isset($_SESSION['pending_email'])) {
    header("Location: ../customer_auth.php");
    exit;
}

$email = $_SESSION['pending_email']; // email sedang menunggu OTP
$message = '';
$redirect = false;
$styleColor = '#4CAF50'; // Default: hijau sukses

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_code = $conn->real_escape_string($_POST['otp_code']);

    // Ambil user berdasarkan EMAIL di session
    $sql = "SELECT * FROM customer_users WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Cek expired
        if (strtotime($user['otp_expires_at']) < time()) {
            $styleColor = '#FF9800';
            $message = "<h2 style='color:$styleColor;'>⚠️ OTP Kadaluarsa</h2>
                        <p>Silahkan minta OTP baru.</p>";

        // Cek attempts
        } elseif ($user['otp_attempts'] >= 3) {
            $styleColor = '#FF9800';
            $message = "<h2 style='color:$styleColor;'>⚠️ Percobaan Terlalu Banyak</h2>
                        <p>Silahkan kirim ulang OTP.</p>";

        // Cek OTP benar
        } elseif ($user['otp_code'] === $otp_code) {
            $update = "UPDATE customer_users 
                       SET is_verified=1, otp_code=NULL, otp_expires_at=NULL, otp_attempts=0 
                       WHERE id=" . $user['id'];
            $conn->query($update);

            // HAPUS SESSION
            unset($_SESSION['pending_email']);

            $message = "
                <h2 style='color:$styleColor;'>✅ Verifikasi Berhasil!</h2>
                <p>Akun Anda sudah aktif. Anda akan diarahkan ke halaman login dalam <b>3 detik</b>.</p>
                <a href='../customer_auth.php' style='
                    display:inline-block;
                    margin-top:10px;
                    padding:10px 20px;
                    background:$styleColor;
                    color:white;
                    text-decoration:none;
                    border-radius:8px;
                    font-weight:bold;
                '>Login Sekarang</a>
            ";

            $redirect = true;

        } else {
            $conn->query("UPDATE customer_users 
                          SET otp_attempts = otp_attempts + 1 
                          WHERE id=" . $user['id']);

            $styleColor = '#f44336';
            $message = "<h2 style='color:$styleColor;'>❌ OTP Salah</h2>
                        <p>Masukkan kode OTP yang benar.</p>";
        }

    } else {
        $styleColor = '#FF9800';
        $message = "<h2 style='color:$styleColor;'>⚠️ Email tidak ditemukan</h2>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi OTP</title>
    <?php if ($redirect): ?>
        <meta http-equiv="refresh" content="3;url=../customer_auth.php?verified=1">
    <?php endif; ?>
</head>

<body style="font-family: Arial, sans-serif; background: #f5f5f5;">
    <div style="
        max-width: 400px;
        margin: 100px auto;
        padding: 20px;
        border: 2px solid <?= $styleColor ?>;
        background: #fff;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    ">
        <h2>Verifikasi Akun</h2>
        <p>Email: <b><?= htmlspecialchars($email) ?></b></p>

        <form method="POST" style="margin-top: 15px;">

            <!-- Email otomatis dari session (HIDDEN) -->
            <input type="hidden" name="email" value="<?= $email ?>">

            <input type="text" name="otp_code" placeholder="Masukkan OTP" required style="
                width: 90%;
                padding: 10px;
                margin-bottom: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                letter-spacing: 2px;
                text-align: center;
            "><br>

            <button type="submit" style="
                background: #4CAF50;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 8px;
                font-weight: bold;
                cursor: pointer;
            ">Verifikasi</button>
        </form>

        <div style="margin-top: 15px;"><?= $message ?></div>
    </div>
</body>
</html>
