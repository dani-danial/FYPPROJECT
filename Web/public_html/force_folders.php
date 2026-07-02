<?php
$root = __DIR__;
$storagePath = $root . '/storage';

// 1. Force delete any invisible "ghost" files named storage
if (file_exists($storagePath) || is_link($storagePath)) {
    exec("rm -rf " . escapeshellarg($storagePath));
}

// 2. Create the real folder structure
$dirs = [
    $storagePath,
    $storagePath . '/logs',
    $storagePath . '/app/public/groups/icons',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/views',
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "✅ Created: $dir <br>";
        } else {
            echo "❌ Failed to create: $dir <br>";
        }
    } else {
        echo "ℹ️ Already exists: $dir <br>";
    }
    // Set permissions to 775
    chmod($dir, 0775);
}

echo "<h3>Refresh your File Manager now!</h3>";
?>