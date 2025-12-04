<?php
include 'db.php';

// Pastikan timezone sesuai lokasi (biar waktu expire-nya akurat)
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Cek apakah email ada di database
    $stmt = $conn->prepare("SELECT id FROM customer_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Buat token unik dan waktu kadaluarsa (1 jam dari sekarang)
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Simpan token & waktu kadaluarsa ke database
        $update = $conn->prepare("UPDATE customer_users SET reset_token=?, reset_expires=? WHERE email=?");
        $update->bind_param("sss", $token, $expires, $email);
        $update->execute();

        // Buat link reset password (gunakan skema dan host saat ini jika tersedia)
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'adambakery.thebamfams.web.id';
        $resetLink = $scheme . '://' . $host . '/adamsbakery/backend/reset_password.php?token=' . $token;


        echo "<p>Link reset password:</p>";
        echo "<a href='$resetLink' style='color:#af7030; font-weight:bold;'>Reset Password</a>";
        echo "<br><br><small>(Token berlaku selama 1 jam)</small>";


    } else {
        echo "<p style='color:red;'>Email tidak ditemukan di database.</p>";
    }
}
?>

 <style>
/* ====== Gaya Umum ====== */
body {
  font-family: 'Poppins', sans-serif;
  background-color: #faf6f1;
  color: #5b3e1e;
  text-align: center;
  margin: 0;
  padding: 0;
}

/* ====== Container Form ====== */
h2 {
  margin-top: 60px;
  font-size: 28px;
  font-weight: 700;
  color: #5b3e1e;
}

form {
  background-color: #fffaf4;
  display: inline-block;
  padding: 30px 40px;
  margin-top: 20px;
  border: 1px solid #f0e0c9;
  border-radius: 12px;
  box-shadow: 0 2px 6px rgba(200, 170, 120, 0.3);
  text-align: left;
  width: 400px; /* 🔹 Ukuran standar untuk desktop */
}

/* ====== Label dan Input ====== */
label {
  font-weight: 600;
  display: block;
  margin-bottom: 6px;
  color: #5b3e1e;
}

input[type="email"] {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e6d4b7;
  border-radius: 8px;
  outline: none;
  font-size: 15px;
  background-color: #fffdf9;
  transition: all 0.2s ease;
}

input[type="email"]:focus {
  border-color: #cba86e;
  box-shadow: 0 0 4px rgba(203, 168, 110, 0.4);
}

/* ====== Tombol ====== */
button[type="submit"] {
  background-color: #cba86e;
  color: white;
  border: none;
  padding: 10px 28px;
  border-radius: 20px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s;
  width: 100%;
}

button[type="submit"]:hover {
  background-color: #b7925c;
}

/* ====== Pesan Output ====== */
p {
  margin-top: 20px;
}

p strong {
  color: #5b3e1e;
}

a {
  color: #a47135;
  text-decoration: none;
  font-weight: 500;
}

a:hover {
  text-decoration: underline;
}

/* =========================== */
/* 📱 Responsive Design Area   */
/* =========================== */

/* Tablet (≤ 992px) */
@media (max-width: 992px) {
  form {
    width: 80%;
    padding: 25px 30px;
  }

  h2 {
    font-size: 26px;
  }

  button[type="submit"] {
    padding: 12px;
    font-size: 16px;
  }
}

/* Mobile Landscape (≤ 768px) */
@media (max-width: 768px) {
  form {
    width: 90%;
    padding: 20px 25px;
  }

  h2 {
    font-size: 24px;
    margin-top: 40px;
  }

  label {
    font-size: 15px;
  }

  input[type="email"] {
    font-size: 14px;
    padding: 9px 10px;
  }

  button[type="submit"] {
    font-size: 15px;
  }
}

/* Mobile Portrait (≤ 480px) */
@media (max-width: 480px) {
  form {
    width: 92%;
    padding: 18px 20px;
    border-radius: 10px;
  }

  h2 {
    font-size: 22px;
  }

  input[type="email"] {
    font-size: 14px;
    padding: 8px;
  }

  button[type="submit"] {
    font-size: 14px;
    padding: 10px;
  }
}
</style>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - Adams Bakery</title>
</head>
<body>
    <h2>Lupa Password</h2>
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        <button type="submit">Kirim Link Reset</button>
    </form>
</body>
</html>
