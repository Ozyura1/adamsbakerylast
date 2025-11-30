<?php
/**
 * Customer authentication page
 * Handles login and registration with security best practices
 */

require_once 'includes/init.php';

define('AUTH_ACTION_LOGIN', 'login');
define('AUTH_ACTION_REGISTER', 'register');

$action = isset($_GET['action']) && $_GET['action'] === AUTH_ACTION_REGISTER ? AUTH_ACTION_REGISTER : AUTH_ACTION_LOGIN;
$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!CSRFToken::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid!';
    } else {
        $email = InputSanitizer::sanitizeEmail($_POST['email'] ?? '');
        $password = InputSanitizer::sanitizeString($_POST['password'] ?? '');
        
        // Validate inputs
        if (empty($email) || empty($password)) {
            $error = 'Email dan password harus diisi!';
        } elseif (!InputSanitizer::validateEmail($email)) {
            $error = 'Format email tidak valid!';
        } else {
            $conn = Database::getInstance()->getConnection();
            
            $stmt = $conn->prepare('SELECT id, nama_lengkap, email, password FROM customer_users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    $_SESSION['customer_id'] = $user['id'];
                    $_SESSION['customer_name'] = $user['nama_lengkap'];
                    $_SESSION['customer_email'] = $user['email'];
                    
                    logActivity('LOGIN_SUCCESS', 'Customer logged in successfully');
                    
                    $redirect = 'index.php';
                    if (isset($_SESSION['redirect_after_login'])) {
                        $allowedRedirects = ['checkout.php', 'index.php', 'products.php', 'packages.php', 'view_reviews.php', 'contact.php'];
                        if (in_array($_SESSION['redirect_after_login'], $allowedRedirects)) {
                            $redirect = $_SESSION['redirect_after_login'];
                        }
                        unset($_SESSION['redirect_after_login']);
                    }
                    
                    redirectWithMessage($redirect, 'Login berhasil!', 'success');
                } else {
                    logActivity('LOGIN_FAILED', 'Invalid password attempt for email: ' . $email);
                    $error = 'Password salah!';
                }
            } else {
                logActivity('LOGIN_FAILED', 'Email not found: ' . $email);
                $error = 'Email tidak terdaftar!';
            }
            
            $stmt->close();
        }
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    if (!CSRFToken::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid!';
    } else {
        $namaLengkap = InputSanitizer::sanitizeString($_POST['nama_lengkap'] ?? '');
        $email = InputSanitizer::sanitizeEmail($_POST['email'] ?? '');
        $password = InputSanitizer::sanitizeString($_POST['password'] ?? '');
        $confirmPassword = InputSanitizer::sanitizeString($_POST['confirm_password'] ?? '');
        $phone = InputSanitizer::sanitizePhone($_POST['phone'] ?? '');
        $alamat = InputSanitizer::sanitizeString($_POST['alamat'] ?? '');
        
        $validator = new Validator();
        $validator->required($namaLengkap, 'nama_lengkap');
        $validator->email($email, 'email');
        $validator->password($password, 'password');
        $validator->phone($phone, 'phone');
        
        if (!$validator->passes()) {
            $error = $validator->getFirstError();
        } elseif ($password !== $confirmPassword) {
            $error = 'Password dan konfirmasi password tidak sama!';
        } else {
            $conn = Database::getInstance()->getConnection();
            
            // Check if email already exists
            $checkStmt = $conn->prepare('SELECT id FROM customer_users WHERE email = ?');
            $checkStmt->bind_param('s', $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $error = 'Email sudah terdaftar!';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $otpExpires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                
                $insertStmt = $conn->prepare('
                    INSERT INTO customer_users 
                    (nama_lengkap, email, password, phone, alamat, is_verified, otp_code, otp_expires_at)
                    VALUES (?, ?, ?, ?, ?, 0, ?, ?)
                ');
                $insertStmt->bind_param(
                    'sssssss',
                    $namaLengkap,
                    $email,
                    $hashedPassword,
                    $phone,
                    $alamat,
                    $otp,
                    $otpExpires
                );
                
                if ($insertStmt->execute()) {
                    logActivity('REGISTRATION_SUCCESS', 'New customer registered: ' . $email);
                    
                    // KIRIM OTP KE EMAIL
                    require_once 'backend/mailer.php';
                    sendVerificationEmail($email, $otp);

                    $_SESSION['pending_email'] = $email;
                    redirectWithMessage('backend/verify.php', 'Registrasi berhasil. Silakan verifikasi email Anda.', 'success');
                } else {
                    $error = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
                }
                
                $insertStmt->close();
            }
            
            $checkStmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Daftar - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main class="auth-container">
        <div class="auth-box">
            <?php displaySessionAlert(); ?>
            
            <?php if ($error): ?>
                <?php echo renderAlert($error, 'error'); ?>
            <?php endif; ?>
            
            <!-- Tab Navigation -->
            <div class="auth-tabs">
                <button 
                    type="button" 
                    class="auth-tab <?php echo $action === AUTH_ACTION_LOGIN ? 'active' : ''; ?>"
                    onclick="switchAuthMode('login')">
                    Login
                </button>
                <button 
                    type="button" 
                    class="auth-tab <?php echo $action === AUTH_ACTION_REGISTER ? 'active' : ''; ?>"
                    onclick="switchAuthMode('register')">
                    Daftar
                </button>
            </div>
            
            <!-- Login Form -->
            <div id="loginForm" class="auth-form <?php echo $action === AUTH_ACTION_LOGIN ? 'active' : ''; ?>">
                <h2>Login Pelanggan</h2>
                <form method="post">
                    <?php echo CSRFToken::getField(); ?>
                    
                    <div class="form-group">
                        <label for="login-email">Email:</label>
                        <input type="email" id="login-email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Password:</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="login-password" name="password" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('login-password')">👁️</button>
                        </div>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                    
                    <div class="form-footer">
                        <a href="backend/forgot_password.php" class="forgot-password-link">Lupa Password?</a>
                    </div>
                </form>
            </div>
            
            <!-- Registration Form -->
            <div id="registerForm" class="auth-form <?php echo $action === AUTH_ACTION_REGISTER ? 'active' : ''; ?>">
                <h2>Daftar Akun Baru</h2>
                <form method="post">
                    <?php echo CSRFToken::getField(); ?>
                    
                    <div class="form-group">
                        <label for="reg-nama">Nama Lengkap:</label>
                        <input type="text" id="reg-nama" name="nama_lengkap" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-email">Email:</label>
                        <input type="email" id="reg-email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-password">Password:</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="reg-password" name="password" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('reg-password')">👁️</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-confirm">Konfirmasi Password:</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="reg-confirm" name="confirm_password" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('reg-confirm')">👁️</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-phone">No. Telepon:</label>
                        <input type="tel" id="reg-phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-alamat">Alamat:</label>
                        <textarea id="reg-alamat" name="alamat" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" name="register" class="btn btn-primary">Daftar</button>
                </form>
            </div>
        </div>
    </main>
    
    <?php require_once 'includes/footer.php'; ?>
    
    <script>
        function switchAuthMode(mode) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const tabs = document.querySelectorAll('.auth-tab');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            
            if (mode === 'login') {
                loginForm.classList.add('active');
                registerForm.classList.remove('active');
                tabs[0].classList.add('active');
                window.history.pushState({}, '', '?action=login');
            } else {
                registerForm.classList.add('active');
                loginForm.classList.remove('active');
                tabs[1].classList.add('active');
                window.history.pushState({}, '', '?action=register');
            }
        }
        
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
