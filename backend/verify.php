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
    // Ambil input OTP dari form
    $otp_code = trim($_POST['otp_code']);

    // Ambil OTP dan expiry dari SESSION (bukan dari database)
    $otp_session = $_SESSION['pending_otp'] ?? null;
    $otp_expires = $_SESSION['pending_otp_expires'] ?? null;

    // Inisialisasi counter percobaan OTP di session jika belum ada
    if (!isset($_SESSION['pending_otp_attempts'])) {
        $_SESSION['pending_otp_attempts'] = 0;
    }

    // Validasi adanya data OTP di session
    if ($otp_session === null || $otp_expires === null) {
        $styleColor = '#FF9800';
        $message = "<h2 style='color:$styleColor;'>⚠️ Tidak ada proses verifikasi aktif</h2>
                    <p>Silakan daftar ulang atau minta kirim ulang OTP.</p>";
    } else {
        // Cek expired
        if (time() > $otp_expires) {
            $styleColor = '#FF9800';
            $message = "<h2 style='color:$styleColor;'>⚠️ OTP Kadaluarsa</h2>
                        <p>Silahkan minta OTP baru.</p>";
            // Optionally clear pending OTP to force resend
            unset($_SESSION['pending_otp']);
            unset($_SESSION['pending_otp_expires']);
            unset($_SESSION['pending_otp_attempts']);
        }
        // Cek attempts (maks 3)
        elseif ($_SESSION['pending_otp_attempts'] >= 3) {
            $styleColor = '#FF9800';
            $message = "<h2 style='color:$styleColor;'>⚠️ Percobaan Terlalu Banyak</h2>
                        <p>Silahkan kirim ulang OTP.</p>";
        }
        // Cek OTP benar
        elseif (hash_equals((string)$otp_session, (string)$otp_code)) {
            // Pastikan ada data pendaftaran di session
            if (!isset($_SESSION['pending_registration']) || !is_array($_SESSION['pending_registration'])) {
                $styleColor = '#FF9800';
                $message = "<h2 style='color:$styleColor;'>⚠️ Data pendaftaran tidak ditemukan</h2>
                            <p>Silakan daftar ulang.</p>";
            } else {
                // =========== OTP BENAR, BUAT AKUN BARU ===========
                $pending = $_SESSION['pending_registration'];

                $stmt = $conn->prepare("
                    INSERT INTO customer_users
                    (nama_lengkap, email, password, phone, alamat, is_verified)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");

                if ($stmt) {
                    $stmt->bind_param(
                        "sssss",
                        $pending['nama_lengkap'],
                        $pending['email'],
                        $pending['password'],
                        $pending['phone'],
                        $pending['alamat']
                    );
                    $stmt->execute();
                    $stmt->close();

                    // HAPUS SESSION pending semua
                    unset($_SESSION['pending_email']);
                    unset($_SESSION['pending_registration']);
                    unset($_SESSION['pending_otp']);
                    unset($_SESSION['pending_otp_expires']);
                    unset($_SESSION['pending_otp_attempts']);

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
                    // prepare gagal
                    $styleColor = '#FF9800';
                    $message = "<h2 style='color:$styleColor;'>⚠️ Terjadi kesalahan server</h2>
                                <p>Gagal membuat akun. Silakan coba lagi nanti.</p>";
                }
            }
        } else {
            // OTP salah -> increment attempt counter
            $_SESSION['pending_otp_attempts'] += 1;

            // Jika sudah mencapai limit, beri tanda
            if ($_SESSION['pending_otp_attempts'] >= 3) {
                $styleColor = '#FF9800';
                $message = "<h2 style='color:$styleColor;'>⚠️ Percobaan Terlalu Banyak</h2>
                            <p>Silahkan kirim ulang OTP.</p>";
            } else {
                $styleColor = '#f44336';
                $remaining = 3 - $_SESSION['pending_otp_attempts'];
                $message = "<h2 style='color:$styleColor;'>❌ OTP Salah</h2>
                            <p>Masukkan kode OTP yang benar. Sisa percobaan: <b>$remaining</b>.</p>";
            }
        }
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
