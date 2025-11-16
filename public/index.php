<?php
require_once __DIR__ . '/../src/services/AuthService.php';
require_once __DIR__ . '/../config/constants.php';

AuthService::initSession();

// Redirect authenticated users to their dashboard
if (AuthService::isAuthenticated()) {
    $userType = $_SESSION['user_type'];
    if ($userType === 'admin') {
        header('Location: ' . BASE_URL . '/public/admin/index.php');
    } else {
        header('Location: ' . BASE_URL . '/public/user/index.php');
    }
    exit;
}

// Show public home page for non-authenticated users
header('Location: ' . BASE_URL . '/public/home.php');
exit;
