<?php
require_once __DIR__ . '/../src/services/AuthService.php';
require_once __DIR__ . '/../config/constants.php';

AuthService::initSession();
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
            
            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <button class="tab-btn" onclick="showTab('register')">
                    <i class="fas fa-user-plus"></i> Register
                </button>
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
            
            <form id="registerForm" class="auth-form" style="display:none;">
                <div class="form-group">
                    <label for="reg-email" class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="reg-email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="reg-password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="reg-password" name="password" class="form-input" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="reg-first-name" class="form-label">
                        <i class="fas fa-user"></i> First Name
                    </label>
                    <input type="text" id="reg-first-name" name="first_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="reg-last-name" class="form-label">
                        <i class="fas fa-user"></i> Last Name
                    </label>
                    <input type="text" id="reg-last-name" name="last_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="reg-phone" class="form-label">
                        <i class="fas fa-phone"></i> Phone
                    </label>
                    <input type="tel" id="reg-phone" name="phone" class="form-input">
                </div>
                <div class="form-group">
                    <label for="reg-address" class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Address
                    </label>
                    <textarea id="reg-address" name="address" class="form-textarea" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            
            <div class="demo-credentials">
                <p><strong><i class="fas fa-info-circle"></i> Demo Credentials:</strong></p>
                <p><i class="fas fa-user-shield"></i> Admin: admin@library.com / password</p>
                <p><i class="fas fa-user"></i> User: john.doe@email.com / password</p>
            </div>
            
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
