<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// 1. IMPORT CONTROLLERS
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController; 
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PostController; 
use App\Http\Controllers\RunController; 
use App\Http\Controllers\SosController;
use App\Http\Controllers\CoachAIController;
use App\Http\Controllers\EventController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RunnerEngineController; 

use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ===========================================
// 2. PUBLIC ROUTES (No Login Required)
// ===========================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/telegram/webhook', [SosController::class, 'handleWebhook']);

Route::get('/ping', function() {
    return response()->json(['message' => 'PONG! Server is working.']);
});

/**
 * 🛠️ EMERGENCY CACHE CLEAR
 */
Route::get('/clear-coach-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    return response()->json(['message' => 'Caches cleared successfully.']);
});


// ===========================================
// 3. PROTECTED ROUTES (Requires Bearer Token)
// ===========================================
Route::group(['middleware' => ['auth:sanctum']], function () {

    // --- USER PROFILE ---
    Route::get('/profile', [ProfileController::class, 'profile']); 
    Route::post('/profile', [ProfileController::class, 'update']); 
    Route::delete('/user/delete', [ProfileController::class, 'destroy']);

    // --- USERS / SEARCH / FOLLOW ---
    Route::get('/users/search', [UserController::class, 'apiSearch']);
    Route::get('/users/{id}', [UserController::class, 'apiShow']);
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle']);
    Route::get('/following', [FollowController::class, 'getFollowing']); // Moved here for better organization

    // --- CHAT ---
    Route::post('/chat/start/{userId}', [ChatController::class, 'start']);
    Route::get('/conversations', [ChatController::class, 'index']); // For app compatibility
    
    // FIX: Changed {id} to {conversation} so Laravel's Route Model Binding matches your Controller
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'show']); 
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'store']);

    // --- AI COACH ---
    Route::post('/ai-coach', [CoachAIController::class, 'chat']); 
    Route::post('/ai-support', [CoachAIController::class, 'getQuickSupport']); 

    // --- GROUPS ---
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/groups/{group}', [GroupController::class, 'show']);
    Route::post('/groups/{group}', [GroupController::class, 'update']);
    Route::delete('/groups/{group}', [GroupController::class, 'destroy']);
    Route::post('/groups/join', [GroupController::class, 'join']); 
    Route::post('/groups/join', [GroupController::class, 'apiJoin']); // Note: Duplicate route name, but it maps to the same URL

    // --- POSTS ---
    Route::get('/feed', [PostController::class, 'homeFeed']); 
    
    // Group-specific feed
    Route::get('/groups/{id}/posts', [PostController::class, 'indexForApi']); 
    Route::post('/posts', [PostController::class, 'store']);            
    
    // --- SOS ---
    Route::post('/sos/send', [SosController::class, 'apiStore']);

    // --- RUNS ---
    Route::get('/runs', [RunController::class, 'apiIndex']);
    Route::post('/runs', [RunController::class, 'apiStore']);

    // --- EVENTS ---
    Route::get('/events', [EventController::class, 'apiIndex']);
    Route::post('/events/join', [EventController::class, 'apiJoin']);
    
    // --- RUNNER CLASSIFICATION ---
    Route::post('/classify-runner', [RunnerEngineController::class, 'classify']);

});