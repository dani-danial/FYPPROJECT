<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // ==========================================
    // API METHODS (For Mobile App Integration)
    // ==========================================

    // --- 1. REGISTER (API) ---
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username', 
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'age' => 'nullable|integer|min:10|max:100',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'running_goal' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username, 
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'age' => $request->age,
            'gender' => $request->gender,
            'running_goal' => $request->running_goal,
            'phone' => $request->phone,
            'runner_tier' => null,
            'status' => 'active',
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // --- 2. LOGIN (API) ---
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found in database'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password mismatch',
                'received_pass' => $request->password,
            ], 401);
        }

        if ($user->status === 'banned' || $user->status === 'ban') {
            return response()->json(['message' => 'This account has been banned.'], 403);
        }

        $user->last_login_at = now();
        $user->status = 'active';
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    // --- 3. GET PROFILE (API) ---
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->loadCount('runs');
        $user->loadSum('runs as total_distance', 'distance_km');

        return response()->json($user);
    }

    // --- 4. UPDATE PROFILE (API) ---
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // 🛠️ UPDATED: Added weight, height, and base_pace to validation
        $request->validate([
            'name' => 'sometimes|string',
            'about' => 'sometimes|string',
            'username' => 'sometimes|string|unique:users,username,' . $user->id,
            'image' => 'nullable|image|max:2048',
            'weight_kg' => 'sometimes|numeric',
            'height_cm' => 'sometimes|numeric',
            'base_pace' => 'sometimes|string'
        ]);

        // Handle Profile Image Upload
        if ($request->hasFile('image')) {
            if ($user->profile_photo_path) {
                $oldPath = str_replace(asset('storage/'), '', $user->profile_photo_path);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('image')->store('profile-photos', 'public');
            $user->profile_photo_path = asset('storage/' . $path);
        }

        // Standard Fields
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('username')) $user->username = $request->username;
        if ($request->has('about')) $user->about_me = $request->about; 

        // 🛠️ NEW: Save Running Stats (Matches Android request keys)
        if ($request->has('weight_kg')) $user->weight_kg = $request->weight_kg;
        if ($request->has('height_cm')) $user->height_cm = $request->height_cm;
        if ($request->has('base_pace')) $user->base_pace_min_km = $request->base_pace;
        
        $user->save();

        // Reload fresh stats for the response
        $user->loadCount('runs');
        $user->loadSum('runs as total_distance', 'distance_km');

        return response()->json($user);
    }


    // ==========================================
    // WEB METHODS (For Admin Dashboard Login)
    // ==========================================

    public function webLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin/dashboard'); 
            }

            return redirect()->intended('/dashboard'); 
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function webLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
