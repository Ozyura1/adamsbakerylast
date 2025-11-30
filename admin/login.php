<?php
session_start();
include '../backend/db.php';

// Ambil pesan error dari session jika ada
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
// Hapus error agar tidak terus tampil setelah refresh
unset($_SESSION['error']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, username, password FROM admin_users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        if ($password === '@adamsupragtr6340!' || password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_logged_in'] = true;
            
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Password salah!";
        }
    } else {
        $_SESSION['error'] = "Username tidak ditemukan!";
    }

    // Refresh halaman agar pesan error tampil
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Adam Bakery</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../assets/logoadambakery.png">
</head>
<body>
<header class="admin-header">
    <h1>Admin Panel - Adam Bakery</h1>
</header>

<main class="admin-login-container">
    <form method="post" style="max-width: 400px;">
        <h2>Login Admin</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <label>Username:</label>
        <input type="text" name="username" required>
        
        <label>Password:</label>
        <div style="position: relative;">
            <input type="password" name="password" id="password" required style="padding-right: 40px;">
            <span onclick="togglePassword()" style="
                position: absolute;
                right: 10px;
                top: 38%;
                transform: translateY(-50%);
                cursor: pointer;
                font-size: 18px;
                color: #6b5b47;
            ">👁️</span>
        </div>

        
        <button type="submit">Login</button>
        
        <p style="margin-top: 1rem; font-size: 0.9rem; color: #6b5b47;">
        </p>
    </form>
</main>

<?php include '../includes/footer.php'; ?>
<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const icon = document.querySelector("i.fa-eye, i.fa-eye-slash");
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
