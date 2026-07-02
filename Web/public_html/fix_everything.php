<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$base = __DIR__;
$storage = $base . '/storage';

echo "<h2>Laravel Storage Emergency Repair</h2>";

// 1. Check if public_html is writable
if (is_writable($base)) {
    echo "✅ public_html is writable.<br>";
} else {
    echo "❌ ERROR: public_html is NOT writable. Go to Hostinger hPanel -> File Manager, right-click 'public_html', and set Permissions to 755.<br>";
}

// 2. Clear any 'storage' ghost file/link
if (file_exists($storage) || is_link($storage)) {
    echo "Found existing 'storage' entry. Deleting...<br>";
    if (is_dir($storage) && !is_link($storage)) {
        // It's a real dir, leave it
    } else {
        unlink($storage); // Delete if it's a file or link
    }
}

// 3. Force Create the Structure
$paths = [
    $storage,
    $storage . '/logs',
    $storage . '/framework',
    $storage . '/framework/sessions',
    $storage . '/framework/views',
    $storage . '/framework/cache',
    $storage . '/app',
    $storage . '/app/public'
];

foreach ($paths as $path) {
    if (!file_exists($path)) {
        if (mkdir($path, 0775, true)) {
            echo "✅ Created: $path<br>";
        } else {
            $err = error_get_last();
            echo "❌ FAILED to create $path. Reason: " . $err['message'] . "<br>";
        }
    } else {
        echo "ℹ️ Path already exists: $path<br>";
    }
    chmod($path, 0775);
}

// 4. Create dummy log file
$log = $storage . '/logs/laravel.log';
if (!file_exists($log)) {
    file_put_contents($log, "Emergency log created " . date('Y-m-d H:i:s'));
    chmod($log, 0664);
    echo "✅ Created laravel.log<br>";
}

echo "<h3>Diagnostic Complete. Refresh your site now.</h3>";