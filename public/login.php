<?php
require_once __DIR__ . '/../src/services/AuthService.php';
require_once __DIR__ . '/../config/constants.php';

// Check if user was logged out
$loggedOut = isset($_GET['logout']) && $_GET['logout'] == '1';

// Ensure session is destroyed if logout parameter is present
if ($loggedOut) {
    AuthService::destroySession();
}

AuthService::initSession();

// Redirect if already authenticated
if (AuthService::isAuthenticated()) {
    $userType = $_SESSION['user_type'];
    if ($userType === 'admin') {
        header('Location: ' . BASE_URL . '/public/admin/index.php');
    } else {
        header('Location: ' . BASE_URL . '/public/user/index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KH LIBRARY</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="<?php echo BASE_URL; ?>/public/assets/brand/symbol.svg" alt="THE ROBBOEB LIBRARY" class="brand-logo-large">
            </div>
            <h1>KH LIBRARY</h1>
            <h2>Library Management System</h2>
            
            <div id="message" class="message" style="display:none;"></div>
            
            <?php if ($loggedOut): ?>
            <div class="message success" style="display:block; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> You have been logged out successfully
            </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-bottom: 30px;">
                <h3 style="color: var(--gray-700); font-size: 18px; font-weight: 600;">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Your Account
                </h3>
                <p style="color: var(--gray-600); font-size: 14px; margin-top: 8px;">
                    Contact administrator to create an account
                </p>
            </div>
            
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="login-email" class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="login-email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="login-password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="login-password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div style="text-align: center; margin-top: var(--space-4);">
                <a href="<?php echo BASE_URL; ?>/public/home.php" class="btn btn-outline" style="width: 100%;">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/public/assets/js/api.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/auth.js"></script>
</body>
</html>
