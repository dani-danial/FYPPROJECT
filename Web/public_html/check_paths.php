<?php
echo "<h2>Server Path Diagnostic</h2>";

$paths = [
    'Base Path' => dirname(__DIR__),
    'Current Dir (public_html)' => __DIR__,
    'Uploads Folder' => __DIR__ . '/uploads',
    'Group Icons' => __DIR__ . '/uploads/groups/icons',
    'Storage Folder' => __DIR__ . '/storage',
];

foreach ($paths as $label => $path) {
    echo "<strong>$label:</strong> $path ";
    echo file_exists($path) ? "✅ (Exists)" : "❌ (Missing)";
    echo "<br>";
}

echo "<h3>Image Sample Check</h3>";
$sample = __DIR__ . '/uploads/groups/icons/XuiTMKlBSIGSqxN35v8bP3YeH0ucFlX2jA1YGCVs.jpg';
if (file_exists($sample)) {
    echo "✅ Sample image found! Your URL should be: " . urlencode(basename($sample));
} else {
    echo "❌ No images found in uploads/groups/icons/. Did you run the move script?";
}