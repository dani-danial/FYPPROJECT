<?php
// Set the path to the storage folder
$storagePath = __DIR__ . '/storage';

echo "<h2>Starting Storage Repair...</h2>";

// 1. Forcefully remove any existing 'storage' (file, folder, or broken link)
if (file_exists($storagePath) || is_link($storagePath)) {
    echo "Found existing storage entry. Attempting to delete...<br>";
    // Use shell command for a deeper clean
    shell_exec("rm -rf " . escapeshellarg($storagePath));
}

// 2. Recreate the structure with the correct permissions
$directories = [
    $storagePath,
    $storagePath . '/logs',
    $storagePath . '/app',
    $storagePath . '/app/public',
    $storagePath . '/framework',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/views',
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "✅ Created: $dir<br>";
        } else {
            echo "❌ FAILED to create: $dir (Check parent folder permissions)<br>";
        }
    } else {
        echo "ℹ️ Already exists: $dir<br>";
    }
    // Set 775 permissions so the server can write to it
    chmod($dir, 0775);
}

// 3. Create a dummy log file to satisfy Monolog
$logFile = $storagePath . '/logs/laravel.log';
file_put_contents($logFile, "Log created at " . date('Y-m-d H:i:s'));
echo "✅ Created dummy log file.<br>";

echo "<h3>Done! Now try to refresh your main site.</h3>";
?>