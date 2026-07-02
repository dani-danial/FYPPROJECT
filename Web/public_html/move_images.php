<?php
// 1. Create a guaranteed public folder
$publicPath = __DIR__ . '/uploads';
$dirs = [$publicPath, $publicPath.'/groups/icons', $publicPath.'/groups/banners', $publicPath.'/profile-photos'];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) mkdir($dir, 0775, true);
    chmod($dir, 0775);
}

echo "<h2>Moving Images to Public 'uploads' Folder...</h2>";

// 2. Move files from old storage to new uploads
$source = __DIR__ . '/storage/app/public'; // Check old location
if (!is_dir($source)) $source = __DIR__ . '/app_data/app/public'; // Check new location

if (is_dir($source)) {
    shell_exec("cp -rn $source/* $publicPath/ 2>/dev/null");
    echo "✅ Files copied from $source to /public_html/uploads<br>";
} else {
    echo "❌ Source folder not found. Please upload images manually to /public_html/uploads/<br>";
}

echo "<h3>Done! Now update your code below.</h3>";