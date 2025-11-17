<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/services/AuthService.php';

AuthService::initSession();
$isLoggedIn = AuthService::isAuthenticated();
$currentUser = $isLoggedIn ? AuthService::getCurrentUser() : null;

echo "<h2>Authentication Test</h2>";
echo "<p>Is Logged In: " . ($isLoggedIn ? 'YES' : 'NO') . "</p>";
if ($currentUser) {
    echo "<pre>";
    print_r($currentUser);
    echo "</pre>";
}
echo "<p><a href='" . BASE_URL . "/public/login.php'>Login</a></p>";
echo "<p><a href='" . BASE_URL . "/public/home.php'>Home</a></p>";
