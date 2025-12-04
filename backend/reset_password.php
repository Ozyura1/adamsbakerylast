<?php
include 'db.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (empty($password) || empty($confirm)) {
        echo "<script>alert('Semua kolom harus diisi!');</script>";
    } elseif (strlen($password) < 8) {
        echo "<script>alert('Password minimal 8 karakter!');</script>";
    } elseif ($password !== $confirm) {
        echo "<script>alert('Password dan konfirmasi tidak cocok!');</script>";
    } else {
        $new_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("SELECT email FROM customer_users WHERE reset_token=? AND reset_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email);
            $stmt->fetch();
            $stmt->close();

            $update = $conn->prepare("UPDATE customer_users SET password=?, reset_token=NULL, reset_expires=NULL WHERE email=?");
            $update->bind_param("ss", $new_password, $email);
            $update->execute();

             // Redirect user to the correct customer auth page on successful reset
             $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
             $host = $_SERVER['HTTP_HOST'] ?? 'adambakery.thebamfams.web.id';
             $redirectUrl = $scheme . '://' . $host . 'customer_auth.php';

             echo "<script>
              alert('Password berhasil direset! Silakan login kembali.');
              window.location.href = '" . htmlspecialchars($redirectUrl, ENT_QUOTES) . "';
            </script>";

            exit;
        } else {
            echo "<script>alert('Token tidak valid atau sudah kadaluarsa!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Adams Bakery</title>
    <style>
/* ====================== */
/* 🎨 Tampilan Utama */
/* ====================== */
body {
  font-family: "Poppins", sans-serif;
  background: linear-gradient(135deg, #fff3e0, #fbe1b6);
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0;
  padding: 0;
}

.container {
  background: #fff;
  padding: 40px 50px;
  border-radius: 15px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.1);
  text-align: center;
  width: 100%;
  max-width: 400px;
}

h2 {
  color: #333;
  margin-bottom: 25px;
  font-size: 24px;
}

.form-group {
  position: relative;
  margin-bottom: 20px;
  text-align: left;
}

label {
  font-size: 14px;
  color: #444;
  margin-bottom: 5px;
  display: block;
}

input[type="password"], input[type="text"] {
  width: 100%;
  padding: 12px 40px 12px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
  box-sizing: border-box;
  transition: border-color 0.3s ease;
}

input:focus {
  border-color: #ab6117ff;
  outline: none;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 35px;
  cursor: pointer;
  user-select: none;
  font-size: 16px;
  color: #888;
}

button {
  width: 100%;
  padding: 12px;
  background: #af7030ff;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  font-size: 15px;
  transition: background 0.3s ease;
}

button:hover {
  background: #965d26;
}

.note {
  font-size: 12px;
  color: #888;
  margin-top: 8px;
}

/* ============================ */
/* 📱 RESPONSIVE DESIGN SECTION */
/* ============================ */

/* Tablet (≤ 992px) */
@media (max-width: 992px) {
  .container {
    padding: 35px 40px;
    max-width: 380px;
  }
  h2 {
    font-size: 22px;
  }
  input[type="password"], input[type="text"] {
    font-size: 15px;
  }
}

/* Mobile Landscape (≤ 768px) */
@media (max-width: 768px) {
  body {
    background: linear-gradient(135deg, #fff8eb, #fbe8c7);
    height: auto;
    padding: 40px 0;
  }

  .container {
    width: 85%;
    padding: 30px 35px;
    max-width: 350px;
  }

  h2 {
    font-size: 21px;
  }

  input[type="password"], input[type="text"] {
    font-size: 14px;
    padding: 10px 35px 10px 10px;
  }

  button {
    font-size: 14px;
    padding: 10px;
  }

  .toggle-password {
    top: 32px;
    font-size: 15px;
  }
}

/* Mobile Portrait (≤ 480px) */
@media (max-width: 480px) {
  body {
    padding: 20px 0;
  }

  .container {
    width: 90%;
    padding: 25px 20px;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
  }

  h2 {
    font-size: 20px;
    margin-bottom: 20px;
  }

  label {
    font-size: 13px;
  }

  input[type="password"], input[type="text"] {
    font-size: 13px;
    padding: 9px 30px 9px 9px;
  }

  button {
    font-size: 13.5px;
    padding: 10px;
  }

  .note {
    font-size: 11px;
  }

  .toggle-password {
    top: 30px;
    right: 10px;
    font-size: 14px;
  }
}
</style>

</head>
<body>
    <div class="container">
        <h2>Ganti Password Baru</h2>

        <?php if ($token): ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="form-group">
                <label>Password Baru:</label>
                <input type="password" id="password" name="password" required placeholder="Minimal 8 karakter">
                <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password baru">
                <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
            </div>

            <button type="submit">Reset Password</button>
        </form>
        <?php else: ?>
            <p style="color:red;">Token tidak ditemukan!</p>
        <?php endif; ?>

        <p class="note">Pastikan password minimal 8 karakter.</p>
    </div>

    <script>
        function togglePassword(id, el) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                el.textContent = "👁️";
            } else {
                input.type = "password";
                el.textContent = "👁️";
            }
        }
    </script>
</body>
</html>
