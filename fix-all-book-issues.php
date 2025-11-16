<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/helpers/DatabaseHelper.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix All Book Issues - ROBBOEB Libra</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #faa405; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #faa405; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #e89400; }
        pre { background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; background: white; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #faa405; color: white; }
        .step { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>";

echo "<h1>🔧 Fix All Book Issues - ROBBOEB Libra</h1>";

$issues_fixed = 0;
$issues_found = 0;

try {
    $pdo = DatabaseHelper::getConnection();
    
    // Step 1: Check and add missing columns
    echo "<div class='step'>";
    echo "<h2>Step 1: Database Structure</h2>";
    
    $columns = $pdo->query("SHOW COLUMNS FROM books")->fetchAll(PDO::FETCH_COLUMN);
    
    // Check pdf_file column
    if (!in_array('pdf_file', $columns)) {
        echo "<div class='warning'>⚠️ Missing pdf_file column. Adding...</div>";
        $pdo->exec("ALTER TABLE books ADD COLUMN pdf_file VARCHAR(500) NULL AFTER cover_image");
        echo "<div class='success'>✅ Added pdf_file column</div>";
        $issues_fixed++;
        $issues_found++;
    } else {
        echo "<div class='info'>✓ pdf_file column exists</div>";
    }
    
    // Check publisher column
    if (!in_array('publisher', $columns)) {
        echo "<div class='warning'>⚠️ Missing publisher column. Adding...</div>";
        $pdo->exec("ALTER TABLE books ADD COLUMN publisher VARCHAR(255) NULL AFTER description");
        echo "<div class='success'>✅ Added publisher column</div>";
        $issues_fixed++;
        $issues_found++;
    } else {
        echo "<div class='info'>✓ publisher column exists</div>";
    }
    
    echo "</div>";
    
    // Step 2: Check upload directories
    echo "<div class='step'>";
    echo "<h2>Step 2: Upload Directories</h2>";
    
    $cover_dir = __DIR__ . '/public/uploads/covers/';
    $pdf_dir = __DIR__ . '/public/uploads/pdfs/';
    
    if (!is_dir($cover_dir)) {
        mkdir($cover_dir, 0777, true);
        echo "<div class='success'>✅ Created covers directory</div>";
        $issues_fixed++;
        $issues_found++;
    } else {
        echo "<div class='info'>✓ Covers directory exists</div>";
    }
    
    if (!is_dir($pdf_dir)) {
        mkdir($pdf_dir, 0777, true);
        echo "<div class='success'>✅ Created PDFs directory</div>";
        $issues_fixed++;
        $issues_found++;
    } else {
        echo "<div class='info'>✓ PDFs directory exists</div>";
    }
    
    // Check permissions
    if (!is_writable($cover_dir)) {
        chmod($cover_dir, 0777);
        echo "<div class='success'>✅ Fixed covers directory permissions</div>";
        $issues_fixed++;
        $issues_found++;
    }
    
    if (!is_writable($pdf_dir)) {
        chmod($pdf_dir, 0777);
        echo "<div class='success'>✅ Fixed PDFs directory permissions</div>";
        $issues_fixed++;
        $issues_found++;
    }
    
    echo "</div>";
    
    // Step 3: Test book data
    echo "<div class='step'>";
    echo "<h2>Step 3: Test Book Data</h2>";
    
    $test_book = $pdo->query("SELECT * FROM books LIMIT 1")->fetch();
    
    if ($test_book) {
        echo "<table>";
        echo "<tr><th>Column</th><th>Value</th><th>Status</th></tr>";
        
        $important_columns = ['book_id', 'title', 'isbn', 'cover_image', 'pdf_file', 'publisher'];
        foreach ($important_columns as $col) {
            $value = $test_book[$col] ?? 'N/A';
            $status = !empty($value) ? '✅' : '⚠️';
            echo "<tr>";
            echo "<td><strong>$col</strong></td>";
            echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . "</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'>No books in database yet</div>";
    }
    
    echo "</div>";
    
    // Step 4: Check API files
    echo "<div class='step'>";
    echo "<h2>Step 4: API Files</h2>";
    
    $api_files = [
        'Create' => __DIR__ . '/api/books/create.php',
        'Update' => __DIR__ . '/api/books/update.php',
        'Delete' => __DIR__ . '/api/books/delete.php',
        'Get' => __DIR__ . '/api/books/get.php'
    ];
    
    echo "<table>";
    echo "<tr><th>API</th><th>Status</th><th>Size</th></tr>";
    foreach ($api_files as $name => $file) {
        $exists = file_exists($file);
        $size = $exists ? filesize($file) : 0;
        echo "<tr>";
        echo "<td>$name</td>";
        echo "<td>" . ($exists ? "✅ Exists" : "❌ Missing") . "</td>";
        echo "<td>" . ($exists ? number_format($size) . " bytes" : "N/A") . "</td>";
        echo "</tr>";
        
        if (!$exists) {
            $issues_found++;
        }
    }
    echo "</table>";
    
    echo "</div>";
    
    // Step 5: Test database query
    echo "<div class='step'>";
    echo "<h2>Step 5: Test Database Query</h2>";
    
    try {
        $books = DatabaseHelper::getAllBooks(['limit' => 3]);
        echo "<div class='success'>✅ getAllBooks() works correctly</div>";
        echo "<div class='info'>Found " . count($books) . " books</div>";
        
        if (!empty($books)) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Title</th><th>Cover</th><th>PDF</th><th>Publisher</th></tr>";
            foreach ($books as $book) {
                echo "<tr>";
                echo "<td>" . $book['book_id'] . "</td>";
                echo "<td>" . htmlspecialchars($book['title']) . "</td>";
                echo "<td>" . (!empty($book['cover_image']) ? "✅" : "❌") . "</td>";
                echo "<td>" . (!empty($book['pdf_file']) ? "✅" : "❌") . "</td>";
                echo "<td>" . (!empty($book['publisher']) ? "✅" : "❌") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
        $issues_found++;
    }
    
    echo "</div>";
    
    // Step 6: Check JavaScript
    echo "<div class='step'>";
    echo "<h2>Step 6: JavaScript Files</h2>";
    
    $js_file = __DIR__ . '/public/assets/js/admin/books.js';
    if (file_exists($js_file)) {
        $js_content = file_get_contents($js_file);
        $has_base_url = strpos($js_content, 'BASE_URL') !== false;
        $has_fallback = strpos($js_content, "window.BASE_URL || '/library-pro'") !== false;
        
        echo "<table>";
        echo "<tr><th>Check</th><th>Status</th></tr>";
        echo "<tr><td>File exists</td><td>✅</td></tr>";
        echo "<tr><td>Uses BASE_URL</td><td>" . ($has_base_url ? "✅" : "❌") . "</td></tr>";
        echo "<tr><td>Has fallback</td><td>" . ($has_fallback ? "✅" : "⚠️") . "</td></tr>";
        echo "</table>";
    } else {
        echo "<div class='error'>❌ JavaScript file missing</div>";
        $issues_found++;
    }
    
    echo "</div>";
    
    // Summary
    echo "<div class='step'>";
    echo "<h2>📊 Summary</h2>";
    
    if ($issues_found === 0) {
        echo "<div class='success'>";
        echo "<h3>✅ All Systems Operational!</h3>";
        echo "<p>No issues found. Your book management system is ready to use.</p>";
        echo "</div>";
    } else {
        echo "<div class='info'>";
        echo "<h3>Issues Found: $issues_found</h3>";
        echo "<h3>Issues Fixed: $issues_fixed</h3>";
        if ($issues_fixed === $issues_found) {
            echo "<p>✅ All issues have been fixed automatically!</p>";
        } else {
            echo "<p>⚠️ Some issues may require manual attention.</p>";
        }
        echo "</div>";
    }
    
    echo "</div>";
    
    // Next steps
    echo "<div class='step'>";
    echo "<h2>🎯 Next Steps</h2>";
    echo "<ol>";
    echo "<li>Clear your browser cache (Ctrl + Shift + Delete)</li>";
    echo "<li>Go to Books Management</li>";
    echo "<li>Try adding a new book</li>";
    echo "<li>Try editing an existing book</li>";
    echo "<li>Check if PDF files are displayed</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'><strong>Fatal Error:</strong> " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<div style='text-align: center; margin: 40px 0;'>";
echo "<a href='" . BASE_URL . "/public/admin/books.php' class='btn'>📚 Go to Books Management</a>";
echo "<a href='" . BASE_URL . "/test-book-api.php' class='btn'>🔍 Test API</a>";
echo "<a href='" . BASE_URL . "/test-add-book.html' class='btn'>🧪 Test Add Book</a>";
echo "</div>";

echo "</body></html>";
?>
