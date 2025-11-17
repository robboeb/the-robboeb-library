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
    <style>
        .modern-login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Library Bookshelf Background */
        .modern-login-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                repeating-linear-gradient(
                    90deg,
                    transparent,
                    transparent 150px,
                    rgba(255, 87, 34, 0.03) 150px,
                    rgba(255, 87, 34, 0.03) 152px
                ),
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 200px,
                    rgba(255, 87, 34, 0.05) 200px,
                    rgba(255, 87, 34, 0.05) 205px
                );
            opacity: 0.3;
        }
        
        /* Floating Books Animation */
        .book-float {
            position: absolute;
            font-size: 40px;
            color: rgba(255, 87, 34, 0.15);
            animation: floatBook 20s infinite ease-in-out;
        }
        
        .book-float:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
            animation-duration: 25s;
        }
        
        .book-float:nth-child(2) {
            top: 20%;
            right: 15%;
            animation-delay: 3s;
            animation-duration: 30s;
        }
        
        .book-float:nth-child(3) {
            bottom: 15%;
            left: 20%;
            animation-delay: 6s;
            animation-duration: 28s;
        }
        
        .book-float:nth-child(4) {
            bottom: 25%;
            right: 10%;
            animation-delay: 9s;
            animation-duration: 32s;
        }
        
        .book-float:nth-child(5) {
            top: 50%;
            left: 5%;
            animation-delay: 12s;
            animation-duration: 27s;
        }
        
        .book-float:nth-child(6) {
            top: 60%;
            right: 8%;
            animation-delay: 15s;
            animation-duration: 29s;
        }
        
        @keyframes floatBook {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.1;
            }
            25% {
                transform: translateY(-30px) rotate(5deg);
                opacity: 0.15;
            }
            50% {
                transform: translateY(-60px) rotate(-5deg);
                opacity: 0.2;
            }
            75% {
                transform: translateY(-30px) rotate(3deg);
                opacity: 0.15;
            }
        }
        
        /* Spotlight Effect */
        .spotlight {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 87, 34, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            animation: spotlight 8s infinite ease-in-out;
        }
        
        .spotlight:nth-child(7) {
            top: -200px;
            left: -200px;
            animation-delay: 0s;
        }
        
        .spotlight:nth-child(8) {
            bottom: -200px;
            right: -200px;
            animation-delay: 4s;
        }
        
        @keyframes spotlight {
            0%, 100% {
                transform: scale(1);
                opacity: 0.3;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.5;
            }
        }
        
        .modern-login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .modern-login-box {
            background: white;
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
        }
        
        .modern-logo-section {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .modern-logo-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(255, 87, 34, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .modern-logo-wrapper img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }
        
        .modern-title {
            font-size: 32px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .modern-subtitle {
            font-size: 15px;
            color: #616161;
            font-weight: 400;
        }
        
        .modern-welcome {
            text-align: center;
            margin-bottom: 32px;
            padding: 20px;
            background: linear-gradient(135deg, #fff3f0 0%, #ffe5de 100%);
            border-radius: 16px;
            border: 1px solid #ffccc2;
        }
        
        .modern-welcome h3 {
            font-size: 18px;
            font-weight: 600;
            color: #111111;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .modern-welcome h3 i {
            color: #ff5722;
        }
        
        .modern-welcome p {
            font-size: 13px;
            color: #616161;
            margin: 0;
        }
        
        .modern-form-group {
            margin-bottom: 24px;
        }
        
        .modern-form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #111111;
            margin-bottom: 8px;
        }
        
        .modern-form-label i {
            color: #ff5722;
            margin-right: 6px;
        }
        
        .modern-form-input {
            width: 100%;
            padding: 14px 16px;
            font-size: 15px;
            border: 2px solid #eeeeee;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        .modern-form-input:focus {
            outline: none;
            border-color: #ff5722;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 87, 34, 0.1);
        }
        
        .modern-btn-login {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, #ff5722 0%, #ee3900 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
            margin-bottom: 16px;
        }
        
        .modern-btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 87, 34, 0.4);
        }
        
        .modern-btn-login:active {
            transform: translateY(0);
        }
        
        .modern-btn-home {
            width: 100%;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            color: #ff5722;
            background: white;
            border: 2px solid #ff5722;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .modern-btn-home:hover {
            background: #ff5722;
            color: white;
            transform: translateY(-2px);
        }
        
        .modern-message {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .modern-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .modern-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .modern-message.show {
            display: flex;
        }
        
        @media (max-width: 480px) {
            .modern-login-box {
                padding: 32px 24px;
            }
            
            .modern-title {
                font-size: 26px;
            }
        }
    </style>
</head>
<body class="modern-login-page">
    <!-- Floating Books -->
    <i class="fas fa-book book-float"></i>
    <i class="fas fa-book-open book-float"></i>
    <i class="fas fa-bookmark book-float"></i>
    <i class="fas fa-book-reader book-float"></i>
    <i class="fas fa-graduation-cap book-float"></i>
    <i class="fas fa-feather-alt book-float"></i>
    
    <!-- Spotlight Effects -->
    <div class="spotlight"></div>
    <div class="spotlight"></div>
    
    <div class="modern-login-container">
        <div class="modern-login-box">
            <div class="modern-logo-section">
                <div class="modern-logo-wrapper">
                    <img src="https://s3.ca-central-1.amazonaws.com/logojoy/logos/231703335/symbol.svg?1537014.9000000358" alt="KH LIBRARY">
                </div>
                <h1 class="modern-title">KH LIBRARY</h1>
                <p class="modern-subtitle">Library Management System</p>
            </div>
            
            <div id="message" class="modern-message"></div>
            
            <?php if ($loggedOut): ?>
            <div class="modern-message success show">
                <i class="fas fa-check-circle"></i>
                <span>You have been logged out successfully</span>
            </div>
            <?php endif; ?>
            
            <div class="modern-welcome">
                <h3>
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In to Your Account
                </h3>
                <p>Contact administrator to create an account</p>
            </div>
            
            <form id="loginForm">
                <div class="modern-form-group">
                    <label for="login-email" class="modern-form-label">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="login-email" name="email" class="modern-form-input" placeholder="Enter your email" required>
                </div>
                
                <div class="modern-form-group">
                    <label for="login-password" class="modern-form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="login-password" name="password" class="modern-form-input" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="modern-btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <a href="<?php echo BASE_URL; ?>/public/home.php" class="modern-btn-home">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </form>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/public/assets/js/api.js"></script>
    <script>
        function showMessage(message, type = 'error') {
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i><span>${message}</span>`;
            messageDiv.className = `modern-message ${type} show`;
        }

        function hideMessage() {
            const messageDiv = document.getElementById('message');
            messageDiv.className = 'modern-message';
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            hideMessage();
            
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            
            try {
                const response = await API.auth.login(email, password);
                if (response.success) {
                    showMessage('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        const userType = response.data.user.user_type;
                        if (userType === 'admin') {
                            window.location.href = '<?php echo BASE_URL; ?>/public/admin/index.php';
                        } else {
                            window.location.href = '<?php echo BASE_URL; ?>/public/user/index.php';
                        }
                    }, 1000);
                }
            } catch (error) {
                showMessage(error.error?.message || 'Invalid email or password', 'error');
            }
        });
    </script>
</body>
</html>
