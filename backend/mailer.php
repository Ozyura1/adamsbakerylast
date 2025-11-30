<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// load composer autoload
require __DIR__ . '/../vendor/autoload.php';  

function sendVerificationEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi server Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '2311102118@ittelkom-pwt.ac.id'; // email kamu
        $mail->Password   = 'icvutmgvxivfwnbn'; // sandi aplikasi Google (App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Pengirim
        $mail->setFrom('2311102118@ittelkom-pwt.ac.id', 'Adam Bakery');
        // Penerima
        $mail->addAddress($email);

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = 'Kode Verifikasi Akun Anda';

        // isi email OTP
        $mail->Body = "
            <h2>Verifikasi Akun Anda</h2>
            <p>Terima kasih sudah mendaftar di <b>Adam Bakery</b>.</p>
            <p>Kode verifikasi Anda adalah:</p>
            <h3 style='font-size:24px;letter-spacing:4px;'>$otp</h3>
            <p>Kode ini berlaku selama <b>5 menit</b>. Jangan bagikan kepada siapa pun.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Email tidak dapat dikirim. Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>
