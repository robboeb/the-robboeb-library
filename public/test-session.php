<?php
require_once __DIR__ . '/../src/services/AuthService.php';
require_once __DIR__ . '/../config/constants.php';

AuthService::initSession();

echo "<h2>Session Test</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . (AuthService::isAuthenticated() ? 'Authenticated' : 'Not Authenticated') . "</p>";
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Cookies:</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo '<p><a href="' . BASE_URL . '/api/auth/logout">Test Logout</a></p>';
echo '<p><a href="' . BASE_URL . '/public/login.php">Go to Login</a></p>';
