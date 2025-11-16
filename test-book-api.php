<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/helpers/DatabaseHelper.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Book API - ROBBOEB Libra</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #faa405; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; background: white; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #faa405; color: white; }
        .btn { display: inline-block; padding: 10px 20px; background: #faa405; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #e89400; }
        pre { background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #ddd; }
    </style>
</head>
<body>";

echo "<h1>🔍 Book API Test - ROBBOEB Libra</h1>";

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Test 1: Check database connection
    echo "<h2>1. Database Connection</h2>";
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Test 2: Check books table structure
    echo "<h2>2. Books Table Structure</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM books");
    $columns = $stmt->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $required_columns = ['pdf_file', 'publisher'];
    $missing_columns = [];
    $existing_columns = array_column($columns, 'Field');
    
    foreach ($columns as $column) {
        $is_new = in_array($column['Field'], $required_columns);
        $style = $is_new ? 'background: #d4edda;' : '';
        echo "<tr style='$style'>";
        echo "<td><strong>" . $column['Field'] . "</strong>" . ($is_new ? " <span style='color: green;'>NEW</span>" : "") . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for missing columns
    foreach ($required_columns as $col) {
        if (!in_array($col, $existing_columns)) {
            $missing_columns[] = $col;
        }
    }
    
    if (!empty($missing_columns)) {
        echo "<div class='warning'>";
        echo "<strong>⚠️ Missing Columns:</strong> " . implode(', ', $missing_columns);
        echo "<br><br>Please run: <a href='" . BASE_URL . "/update-database.php'>update-database.php</a>";
        echo "</div>";
    } else {
        echo "<div class='success'>✅ All required columns exist</div>";
    }
    
    // Test 3: Check upload directories
    echo "<h2>3. Upload Directories</h2>";
    $cover_dir = __DIR__ . '/public/uploads/covers/';
    $pdf_dir = __DIR__ . '/public/uploads/pdfs/';
    
    echo "<table>";
    echo "<tr><th>Directory</th><th>Status</th><th>Writable</th></tr>";
    
    echo "<tr>";
    echo "<td>Covers: <code>$cover_dir</code></td>";
    echo "<td>" . (is_dir($cover_dir) ? "✅ Exists" : "❌ Missing") . "</td>";
    echo "<td>" . (is_writable($cover_dir) ? "✅ Yes" : "❌ No") . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td>PDFs: <code>$pdf_dir</code></td>";
    echo "<td>" . (is_dir($pdf_dir) ? "✅ Exists" : "❌ Missing") . "</td>";
    echo "<td>" . (is_writable($pdf_dir) ? "✅ Yes" : "❌ No") . "</td>";
    echo "</tr>";
    
    echo "</table>";
    
    if (!is_dir($cover_dir) || !is_dir($pdf_dir)) {
        echo "<div class='warning'>";
        echo "<strong>⚠️ Upload directories missing</strong><br>";
        echo "Please run: <a href='" . BASE_URL . "/update-database.php'>update-database.php</a>";
        echo "</div>";
    }
    
    // Test 4: Check categories and authors
    echo "<h2>4. Categories and Authors</h2>";
    $categories = DatabaseHelper::getAllCategories();
    $authors = DatabaseHelper::getAllAuthors();
    
    echo "<div class='info'>";
    echo "<strong>Categories:</strong> " . count($categories) . " found<br>";
    echo "<strong>Authors:</strong> " . count($authors) . " found";
    echo "</div>";
    
    if (empty($categories)) {
        echo "<div class='warning'>⚠️ No categories found. Add categories first.</div>";
    }
    
    if (empty($authors)) {
        echo "<div class='warning'>⚠️ No authors found. Add authors first.</div>";
    }
    
    // Test 5: Sample book data
    echo "<h2>5. Sample Books</h2>";
    $books = DatabaseHelper::getAllBooks(['limit' => 5]);
    
    if (!empty($books)) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>ISBN</th><th>Cover</th><th>PDF</th></tr>";
        foreach ($books as $book) {
            echo "<tr>";
            echo "<td>" . $book['book_id'] . "</td>";
            echo "<td>" . htmlspecialchars($book['title']) . "</td>";
            echo "<td>" . htmlspecialchars($book['isbn']) . "</td>";
            echo "<td>" . (!empty($book['cover_image']) ? "✅" : "❌") . "</td>";
            echo "<td>" . (!empty($book['pdf_file']) ? "✅" : "❌") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'>No books found in database</div>";
    }
    
    // Test 6: API Endpoints
    echo "<h2>6. API Endpoints</h2>";
    $api_files = [
        'Create' => __DIR__ . '/api/books/create.php',
        'Update' => __DIR__ . '/api/books/update.php',
        'Delete' => __DIR__ . '/api/books/delete.php',
        'Get' => __DIR__ . '/api/books/get.php'
    ];
    
    echo "<table>";
    echo "<tr><th>Endpoint</th><th>Status</th></tr>";
    foreach ($api_files as $name => $file) {
        echo "<tr>";
        echo "<td>$name</td>";
        echo "<td>" . (file_exists($file) ? "✅ Exists" : "❌ Missing") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Summary
    echo "<h2>📊 Summary</h2>";
    
    $issues = [];
    if (!empty($missing_columns)) $issues[] = "Missing database columns";
    if (!is_dir($cover_dir)) $issues[] = "Missing covers directory";
    if (!is_dir($pdf_dir)) $issues[] = "Missing PDFs directory";
    if (empty($categories)) $issues[] = "No categories";
    if (empty($authors)) $issues[] = "No authors";
    
    if (empty($issues)) {
        echo "<div class='success'>";
        echo "<h3>✅ All Tests Passed!</h3>";
        echo "<p>Your system is ready to add books.</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Issues Found:</h3>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li>$issue</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><strong>Error:</strong> " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<div style='text-align: center; margin: 40px 0;'>";
echo "<a href='" . BASE_URL . "/update-database.php' class='btn'>🔧 Update Database</a>";
echo "<a href='" . BASE_URL . "/public/admin/books.php' class='btn'>📚 Books Management</a>";
echo "<a href='" . BASE_URL . "/public/admin/index.php' class='btn'>🏠 Admin Dashboard</a>";
echo "</div>";

echo "</body></html>";
?>
