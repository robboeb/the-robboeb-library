<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Test</h1>";
echo "<p>PHP is working!</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

echo "<h2>Testing File Includes</h2>";

try {
    require_once __DIR__ . '/../config/constants.php';
    echo "<p>✓ Constants loaded</p>";
    echo "<p>BASE_URL: " . BASE_URL . "</p>";
} catch (Exception $e) {
    echo "<p>✗ Error loading constants: " . $e->getMessage() . "</p>";
}

try {
    require_once __DIR__ . '/../src/services/AuthService.php';
    echo "<p>✓ AuthService loaded</p>";
} catch (Exception $e) {
    echo "<p>✗ Error loading AuthService: " . $e->getMessage() . "</p>";
}

echo "<h2>Testing Database Connection</h2>";
try {
    require_once __DIR__ . '/../config/database.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p>✓ Database connection successful</p>";
} catch (PDOException $e) {
    echo "<p>✗ Database error: " . $e->getMessage() . "</p>";
}
?>
