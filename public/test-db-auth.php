<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database & Authentication Test</h1>";

// Test 1: Database Connection
echo "<h2>1. Testing Database Connection</h2>";
try {
    require_once __DIR__ . '/../config/database.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    echo "<p>Database: " . DB_NAME . "</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check if users table exists
echo "<h2>2. Checking Users Table</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Users table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Users table does not exist</p>";
        exit;
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    exit;
}

// Test 3: Check users in database
echo "<h2>3. Users in Database</h2>";
try {
    $stmt = $pdo->query("SELECT user_id, email, first_name, last_name, user_type, status FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<p style='color: green;'>✓ Found " . count($users) . " users</p>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Type</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['user_id'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['first_name'] . " " . $user['last_name'] . "</td>";
            echo "<td>" . $user['user_type'] . "</td>";
            echo "<td>" . $user['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠ No users found in database</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 4: Check password column
echo "<h2>4. Checking Password Column</h2>";
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasPassword = false;
    $hasPasswordHash = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'password') {
            $hasPassword = true;
            echo "<p style='color: green;'>✓ Found 'password' column</p>";
        }
        if ($column['Field'] === 'password_hash') {
            $hasPasswordHash = true;
            echo "<p style='color: green;'>✓ Found 'password_hash' column</p>";
        }
    }
    
    if (!$hasPassword && !$hasPasswordHash) {
        echo "<p style='color: red;'>✗ No password column found!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 5: Test User Model Authentication
echo "<h2>5. Testing User Model Authentication</h2>";
try {
    require_once __DIR__ . '/../src/models/User.php';
    $userModel = new User();
    
    $testEmail = 'admin@test.com';
    $testPassword = 'admin123';
    
    echo "<p>Testing login with:</p>";
    echo "<p>Email: <strong>$testEmail</strong></p>";
    echo "<p>Password: <strong>$testPassword</strong></p>";
    
    $user = $userModel->authenticate($testEmail, $testPassword);
    
    if ($user) {
        echo "<p style='color: green;'>✓ Authentication successful!</p>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Authentication failed</p>";
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$testEmail]);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dbUser) {
            echo "<p style='color: orange;'>⚠ User exists but password doesn't match</p>";
            echo "<p>Password hash in DB: " . substr($dbUser['password'], 0, 30) . "...</p>";
            
            // Test password verification
            if (password_verify($testPassword, $dbUser['password'])) {
                echo "<p style='color: green;'>✓ Password verification works!</p>";
            } else {
                echo "<p style='color: red;'>✗ Password verification failed</p>";
                echo "<p>Creating new hash for '$testPassword':</p>";
                $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
                echo "<p style='font-size: 12px;'>$newHash</p>";
                echo "<p><strong>Run this SQL to fix:</strong></p>";
                echo "<pre>UPDATE users SET password = '$newHash' WHERE email = '$testEmail';</pre>";
            }
        } else {
            echo "<p style='color: red;'>✗ User does not exist in database</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>
