<?php
require __DIR__.'/../bootstrap/app.php';

use App\Models\Post;

header('Content-Type: application/json');

$post = Post::latest()->first();

if ($post) {
    echo json_encode([
        'status' => 'success',
        'raw_post' => $post->toArray(),
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No posts found in database.'
    ]);
}
