<?php
// Turn on all error reporting manually
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Step 1: PHP is working.</h2>";

// Check if Vendor folder exists
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<h2>Step 2: Vendor folder found.</h2>";
    require __DIR__ . '/vendor/autoload.php';
    echo "<h2>Step 3: Vendor loaded successfully.</h2>";
} else {
    die("<h1 style='color:red'>CRITICAL FAIL: Vendor folder is missing or incomplete!</h1>");
}

// Check if Storage is writable
$test_file = __DIR__ . '/storage/logs/test_permission.txt';
if (file_put_contents($test_file, 'testing write permission')) {
    echo "<h2>Step 4: Storage permissions are GOOD (Writable).</h2>";
    unlink($test_file); // Delete the test file
} else {
    die("<h1 style='color:red'>CRITICAL FAIL: Cannot write to Storage folder! Change permissions to 777.</h1>");
}

echo "<h1>✅ EVERYTHING IS FINE. The issue is in your .env or Config.</h1>";
?>