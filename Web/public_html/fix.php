<?php
// This script creates a bridge from your hidden app data to the public web
$target = __DIR__ . '/app_data/app/storage/app/public';
$shortcut = __DIR__ . '/storage';

// Delete the old link if it exists
if (file_exists($shortcut)) {
    if (is_link($shortcut)) {
        unlink($shortcut);
    } else {
        rename($shortcut, $shortcut . '_old_' . time());
    }
}

// Create the new link
if (symlink($target, $shortcut)) {
    echo "<h1>Success!</h1><p>The bridge between storage and web is created.</p>";
} else {
    echo "<h1>Failed!</h1><p>Check if 'app_data/app/storage/app/public' exists.</p>";
}