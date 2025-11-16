<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing API directly...\n\n";

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/Libra Project/api/categories';

try {
    include 'api/index.php';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
