<?php
// Ensure this script can run Laravel internally
require __DIR__.'/../bootstrap/app.php';

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

header('Content-Type: application/json');

$post = Post::latest()->first();
$user = User::first(); // Log in as the first user for auth context

if (!$post) {
    echo json_encode(['status' => 'error', 'message' => 'No posts in database.']);
    exit;
}

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'No users in database.']);
    exit;
}

Auth::login($user);

// Mimic api call internally
try {
    $controller = app()->make(\App\Http\Controllers\PostController::class);
    // Mimic Request
    $request = \Illuminate\Http\Request::create("api/posts/{$post->id}", 'GET');
    $request->headers->set('Accept', 'application/json');
    
    // Call the controller method
    $response = $controller->show($post->id);
    
    echo json_encode([
        'status' => 'success',
        'post_id_tested' => $post->id,
        'response_status_code' => $response->getStatusCode(),
        'response_content' => json_decode($response->getContent(), true)
    ], JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo json_encode([
        'status' => 'exception',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
