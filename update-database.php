<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/src/helpers/DatabaseHelper.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Update - ROBBOEB Libra</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #faa405; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #faa405; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #e89400; }
        pre { background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>";

echo "<h1>📚 Database Update - ROBBOEB Libra</h1>";

try {
    $pdo = DatabaseHelper::getConnection();
    
    echo "<div class='info'><strong>Starting database update...</strong></div>";
    
    // Check if pdf_file column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM books LIKE 'pdf_file'");
    $pdfColumnExists = $stmt->rowCount() > 0;
    
    if (!$pdfColumnExists) {
        echo "<div class='info'>Adding pdf_file column...</div>";
        $pdo->exec("ALTER TABLE books ADD COLUMN pdf_file VARCHAR(500) NULL AFTER cover_image");
        echo "<div class='success'>✅ Added pdf_file column</div>";
    } else {
        echo "<div class='info'>✓ pdf_file column already exists</div>";
    }
    
    // Check if publisher column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM books LIKE 'publisher'");
    $publisherColumnExists = $stmt->rowCount() > 0;
    
    if (!$publisherColumnExists) {
        echo "<div class='info'>Adding publisher column...</div>";
        $pdo->exec("ALTER TABLE books ADD COLUMN publisher VARCHAR(255) NULL AFTER description");
        echo "<div class='success'>✅ Added publisher column</div>";
    } else {
        echo "<div class='info'>✓ publisher column already exists</div>";
    }
    
    // Create uploads directories
    $coverDir = __DIR__ . '/public/uploads/covers/';
    $pdfDir = __DIR__ . '/public/uploads/pdfs/';
    
    if (!is_dir($coverDir)) {
        mkdir($coverDir, 0777, true);
        echo "<div class='success'>✅ Created covers upload directory</div>";
    } else {
        echo "<div class='info'>✓ Covers directory exists</div>";
    }
    
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0777, true);
        echo "<div class='success'>✅ Created PDFs upload directory</div>";
    } else {
        echo "<div class='info'>✓ PDFs directory exists</div>";
    }
    
    // Show current table structure
    echo "<h2>📋 Current Books Table Structure</h2>";
    $stmt = $pdo->query("DESCRIBE books");
    $columns = $stmt->fetchAll();
    
    echo "<pre>";
    echo str_pad("Field", 25) . str_pad("Type", 30) . str_pad("Null", 10) . "Key\n";
    echo str_repeat("-", 75) . "\n";
    foreach ($columns as $column) {
        echo str_pad($column['Field'], 25) . 
             str_pad($column['Type'], 30) . 
             str_pad($column['Null'], 10) . 
             $column['Key'] . "\n";
    }
    echo "</pre>";
    
    echo "<div class='success'><strong>✅ Database update completed successfully!</strong></div>";
    
    echo "<div class='info'>";
    echo "<h3>✨ New Features Available:</h3>";
    echo "<ul>";
    echo "<li>📸 Upload book cover images</li>";
    echo "<li>📄 Upload PDF files for books</li>";
    echo "<li>📝 Add publisher information</li>";
    echo "<li>✏️ Full CRUD operations in admin panel</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'><strong>Error:</strong> " . $e->getMessage() . "</div>";
}

echo "<div style='text-align: center; margin: 40px 0;'>";
echo "<a href='" . BASE_URL . "/public/admin/books.php' class='btn'>📚 Go to Books Management</a>";
echo "<a href='" . BASE_URL . "/public/admin/index.php' class='btn'>🏠 Admin Dashboard</a>";
echo "</div>";

echo "</body></html>";
?>
