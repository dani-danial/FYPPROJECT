<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SosController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoachAIController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\NearbyRunnersController;
use Illuminate\Http\Request;             // 🛠️ Added for Query String Trick
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;     // 🛠️ Added for File Server
use Illuminate\Support\Facades\Response; // 🛠️ Added for File Server
use Illuminate\Support\Facades\Log;      // 🛠️ Added for Diagnostic Logging
use App\Models\Post;

/*
|--------------------------------------------------------------------------
| Public Utility Routes (REPAIR TOOLS)
|--------------------------------------------------------------------------
| Use these routes to fix server issues on Hostinger.
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/**
 * 🛠️ HOSTINGER BYPASS: The Smart Checker
 * Checks multiple vault locations to find where Hostinger actually saved the file.
 */
Route::get('/serve-image', function (Request $request) {
    $path = $request->query('path');
    
    // 1. Log the incoming request
    Log::info("--------------------------------------------------");
    Log::info("🖼️ IMAGE REQUESTED: " . $path);
    
    if (!$path) {
        Log::error("❌ No path was provided in the URL.");
        return response("No path provided", 400);
    }

    $cleanPath = ltrim($path, '/');
    
    // 🛠️ Check ALL known Hostinger vaults on your server
    $possiblePaths = [
        base_path('app_data/app/public/' . $cleanPath),          // Vault 1
        base_path('resources/storage/app/public/' . $cleanPath), // Vault 2 (The correct one!)
        storage_path('app/public/' . $cleanPath)                 // Default Laravel Vault
    ];

    $foundPath = null;
    foreach ($possiblePaths as $p) {
        Log::info("🔍 CHECKING: " . $p);
        if (File::exists($p)) {
            $foundPath = $p;
            Log::info("✅ FOUND IT AT: " . $p);
            break; // Stop looking once we find it!
        }
    }
    
    if (!$foundPath) {
        Log::error("❌ FILE MISSING IN ALL VAULTS!");
        return response("DIAGNOSTIC ERROR: I checked all vaults, but the image is missing!", 404);
    }

    try {
        $type = File::mimeType($foundPath);
        $file = File::get($foundPath);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    } catch (\Exception $e) {
        Log::error("❌ CANNOT OPEN FILE: " . $e->getMessage());
        return response("ERROR: I found the file, but can't open it -> " . $e->getMessage(), 500);
    }
});


/**
 * 🛠️ LIVE LOG VIEWER
 * Go to https://runtracker.fun/debug-logs to see what happened!
 */
Route::get('/debug-logs', function () {
    $logFile = storage_path('logs/laravel.log');
    
    if (!file_exists($logFile)) {
        return "<h2 style='color:red;'>No log file found yet!</h2>";
    }

    // Grab the last 50 lines of the log file
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    
    $output = implode("", $lastLines);
    
    return "<h2>Server Logs (Last 50 Actions)</h2>
            <button onclick='window.location.reload()'>Refresh Logs</button>
            <br><br>
            <pre style='background:#111; color:#0f0; padding:15px; border-radius:8px; overflow-x:auto;'>" . htmlspecialchars($output) . "</pre>";
});

/**
 * THE MASTER REPAIR FIX: 
 * Resolves "Invalid cache path" and validates the Storage shortcut.
 */
Route::get('/fix-storage', function () {
    $root = base_path(); 
    $customStorage = $root . '/resources/storage';
    $publicLink = $root . '/storage'; 
    
    // 1. FORCE paths into memory to prevent the 500 crash
    Config::set('view.compiled', $customStorage . '/framework/views');
    Config::set('session.files', $customStorage . '/framework/sessions');
    Config::set('cache.stores.file.path', $customStorage . '/framework/cache');

    try {
        $output = "";

        // 2. Physical deletion of stale bootstrap cache
        $cacheFiles = [
            $root . '/bootstrap/cache/config.php',
            $root . '/bootstrap/cache/services.php',
            $root . '/bootstrap/cache/packages.php'
        ];
        foreach ($cacheFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
                $output .= "Deleted stale cache: " . basename($file) . "<br>";
            }
        }
        
        // 3. Clear Laravel internal Artisan caches
        Artisan::call('optimize:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        $output .= "Laravel Artisan caches cleared! <br>";

        // 4. Ensure framework folders exist in resources/storage
        $folders = [
            $customStorage . '/framework/sessions',
            $customStorage . '/framework/views',
            $customStorage . '/framework/cache',
            $customStorage . '/app/public/groups/icons',
            $customStorage . '/app/public/posts',
            $customStorage . '/app/public/post', // Added for the new post uploads
        ];

        foreach ($folders as $path) {
            if (!file_exists($path)) {
                mkdir($path, 0775, true);
                $output .= "Created Folder: $path <br>";
            } else {
                chmod($path, 0775);
                $output .= "Permissions Reset: $path <br>";
            }
        }

        // 5. Link Status Verification
        if (is_link($publicLink)) {
            $output .= "✅ SUCCESS: The Storage shortcut is ACTIVE!<br>";
        } else {
            $output .= "❌ STILL BROKEN: Shortcut not found. Ensure you deleted the 'storage' folder in public_html first.<br>";
        }

        // 6. EMOJI CHECK (Deep Database Check for the LAST 3 POSTS)
        $recentPosts = Post::latest()->take(3)->get();
        if ($recentPosts->count() > 0) {
            $output .= "<h3>Recent Posts Diagnostic:</h3>";
            foreach($recentPosts as $post) {
                $rawUrl = $post->getRawOriginal('image_url');
                $output .= "<strong>Post ID {$post->id}:</strong><br>";
                $output .= "— Raw DB Value: <code>" . ($rawUrl ?: '[EMPTY]') . "</code><br>";
                
                if (empty($rawUrl)) {
                    $output .= "— ❌ <strong>STATUS:</strong> This post has NO image data. Your Controller likely didn't receive the file.<br>";
                } elseif (is_array($post->image_url)) {
                    $output .= "— ✅ <strong>STATUS:</strong> Correct Format (Array). " . count($post->image_url) . " images detected.<br>";
                } else {
                    $output .= "— ⚠️ <strong>STATUS:</strong> Format Error. Data is a string but Model expects an array.<br>";
                }
                $output .= "<hr style='border: 0; border-top: 1px dashed #444; margin: 10px 0;'>";
            }
        } else {
            $output .= "⚠️ No posts found in the database.<br>";
        }

        return "<h1>Master Repair Successful!</h1><p>$output</p><a href='/user/posts'>Go to Community Feed</a>";

    } catch (\Exception $e) {
        return "<h1>Repair Failed</h1><p>" . $e->getMessage() . "</p>";
    }
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
]);

/**
 * 🛠️ THE NUKE & LINK TOOL:
 * Force-recreates the symbolic link to fix 404 image errors.
 */
Route::get('/nuke-link', function () {
    $root = base_path();
    $publicLink = $root . '/storage'; 
    $targetPath = $root . '/resources/storage/app/public'; 

    // 1. Delete the old/broken link or folder
    if (file_exists($publicLink)) {
        if (is_link($publicLink)) {
            unlink($publicLink);
        } else {
            // If it's a real folder blocking the link, rename it
            rename($publicLink, $publicLink . '_old_' . time());
        }
    }

    // 2. Create the precise Symlink
    try {
        symlink($targetPath, $publicLink);
        return "<h1>✅ Symlink Recreated!</h1>
                <p>Target: $targetPath</p>
                <p>Link: $publicLink</p>
                <a href='/user/posts'>Check your feed</a>";
    } catch (\Exception $e) {
        return "<h1>❌ Failed</h1><p>" . $e->getMessage() . "</p>";
    }
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
]);

// Install app route
Route::get('/install-app', function () {
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('optimize:clear');
    return '<h1>Success! Database migrated & Cache cleared.</h1>';
});

// Public payment callback/redirect from ToyyibPay
Route::get('/payment-status', [EventController::class, 'paymentStatus'])->name('user.events.paymentStatus');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- ADMIN ---
    Route::prefix('admin')->group(function () {
        Route::get('/', function () { return redirect()->route('dashboard'); });
        Route::resource('users', UserController::class);
        Route::resource('notifications', NotificationController::class);
        Route::resource('sos', SosController::class);
        Route::patch('sos/{id}/update-status', [SosController::class, 'updateStatus'])->name('sos.updateStatus');
        Route::resource('events', EventController::class);
        Route::resource('posts', PostController::class);
        Route::resource('groups', GroupController::class);
    });

    // --- USER COMMUNITY ---
    Route::get('/user/notifications', [NotificationController::class, 'userIndex'])->name('user.notifications');

    // Events
    Route::get('/user/events', [EventController::class, 'userIndex'])->name('user.events');
    Route::get('/user/events/{id}', [EventController::class, 'show'])->name('user.events.show');
    Route::post('/user/events/{id}/join', [EventController::class, 'join'])->name('user.events.join');
    Route::post('/user/events/{id}/confirm-payment', [EventController::class, 'confirmPayment'])->name('user.events.confirmPayment');
    Route::post('/user/events/{id}/quit', [EventController::class, 'quit'])->name('user.events.quit');

    // Posts
    Route::get('/user/posts', [PostController::class, 'userIndex'])->name('user.posts');
    Route::get('/user/posts/create', [PostController::class, 'userCreate'])->name('user.posts.create');
    Route::post('/user/posts/store', [PostController::class, 'userStore'])->name('user.posts.store');
    Route::get('/user/posts/{id}', [PostController::class, 'show'])->name('user.posts.show');
    Route::post('/user/posts/{id}/like', [PostController::class, 'like'])->name('user.posts.like');
    Route::post('/user/posts/{id}/comment', [PostController::class, 'comment'])->name('user.posts.comment');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::get('/user/posts/{id}/edit', [PostController::class, 'userEdit'])->name('user.posts.edit');
    Route::put('/user/posts/{id}', [PostController::class, 'userUpdate'])->name('user.posts.update');

    // Groups
    Route::get('/user/groups', [GroupController::class, 'userIndex'])->name('user.groups');
    Route::get('/user/groups/create', [GroupController::class, 'userCreate'])->name('user.groups.create');
    Route::post('/user/groups/store', [GroupController::class, 'userStore'])->name('user.groups.store');
    Route::get('/user/groups/{id}', [GroupController::class, 'userShow'])->name('user.groups.show');
    Route::post('/user/groups/{id}/join', [GroupController::class, 'join'])->name('user.groups.join');
    Route::post('/user/groups/{id}/leave', [GroupController::class, 'leave'])->name('user.groups.leave');

    // Coaching & Profile
    Route::get('/user/coach', [CoachAIController::class, 'index'])->name('coach.index');
    Route::post('/user/coach/chat', [CoachAIController::class, 'chat'])->name('coach.chat');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/search', [UserController::class, 'search'])->name('users.search');
    Route::post('/user/{user}/follow', [FollowController::class, 'toggle'])->name('user.follow');
    Route::post('/update-location', [ProfileController::class, 'updateLocation'])->name('profile.update_location');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/start/{user}', [ChatController::class, 'start'])->name('chat.start');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}', [ChatController::class, 'store'])->name('chat.store');
    
    // Group Management
    Route::get('/user/groups/{id}/members', [GroupController::class, 'members'])->name('user.groups.members');
    Route::delete('/user/groups/{group}/members/{user}', [GroupController::class, 'kick'])->name('user.groups.kick');
    Route::get('/user/groups/{id}/edit', [GroupController::class, 'edit'])->name('user.groups.edit');
    Route::patch('/user/groups/{id}', [GroupController::class, 'update'])->name('user.groups.update');
    Route::delete('/user/groups/{id}', [GroupController::class, 'destroy'])->name('user.groups.destroy');

    Route::get('/nearby-runners', [NearbyRunnersController::class, 'index'])->name('user.nearby');
    
    Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');

    // Dynamic Profile (MUST BE LAST)
    Route::get('/user/{username}', [ProfileController::class, 'show'])->name('profile.show');
});

require __DIR__.'/auth.php';

// Toyyibpay Debug
Route::get('/debug/toyyibpay', function () {
    return response()->json([
        'secret_key' => env('TOYYIBPAY_SECRET_KEY'),
        'category_code' => env('TOYYIBPAY_CATEGORY_CODE'),
        'api_url' => env('TOYYIBPAY_API_URL'),
    ]);
});