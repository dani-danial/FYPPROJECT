<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; 
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user()->fresh(), 200);
    }

    public function update(Request $request): mixed
    {
        Log::info("--- Profile Update Request Started ---");
        
        $input = $request->all();

        // Normalize
        $fields = ['weight_kg', 'height_cm', 'base_pace_min_km', 'distance_km', 'total_runs'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $val = $input[$field];
                
                if (is_string($val) && strpos($val, ':') !== false) {
                    $parts = explode(':', $val);
                    $input[$field] = count($parts) == 2 ? $parts[0] + ($parts[1] / 60) : $parts[0];
                }

                if (is_string($input[$field])) {
                    $input[$field] = preg_replace('/[^0-9.]/', '', $input[$field]);
                }

                if ($input[$field] === "" || $input[$field] === "0") {
                    $input[$field] = null;
                }
            }
        }

        // Validate
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . Auth::id()],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'about_me' => ['nullable', 'string', 'max:1000'], 
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_picture' => ['nullable', 'image', 'max:10240'], 
            'weight_kg' => ['nullable', 'numeric'], 
            'height_cm' => ['nullable', 'numeric'],
            'base_pace_min_km' => ['nullable', 'numeric'],
            'distance_km' => ['nullable', 'numeric'],
            'total_runs' => ['nullable', 'integer'],
            'remove_photo' => ['nullable'],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $user = $request->user();

        // 🛠️ SAVING TO THE CENTRALIZED FOLDER
        // Points directly to the app_data/app/public/profile-photos path you requested
        $destinationPath = base_path('app_data/app/public/profile-photos');

        if ($request->hasFile('profile_picture')) {
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }

            if ($user->profile_photo_path) {
                $oldFileName = basename($user->profile_photo_path);
                $oldFilePath = $destinationPath . '/' . $oldFileName;
                if (File::exists($oldFilePath)) File::delete($oldFilePath);
            }

            try {
                $file = $request->file('profile_picture');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Save it to the app_data vault
                $file->move($destinationPath, $filename);
                
                $user->profile_photo_path = 'profile-photos/' . $filename;
                Log::info("Photo successfully saved to app_data vault: " . $user->profile_photo_path);
            } catch (\Exception $e) {
                Log::error("File Move Error: " . $e->getMessage());
            }

        } elseif ($request->boolean('remove_photo')) {
            if ($user->profile_photo_path) {
                $oldFileName = basename($user->profile_photo_path);
                $oldFilePath = $destinationPath . '/' . $oldFileName;
                if (File::exists($oldFilePath)) File::delete($oldFilePath);
                $user->profile_photo_path = null;
            }
        }

        // Update Text and Stats
        $user->name = $input['name'] ?? $user->name;
        $user->username = $input['username'] ?? $user->username;
        $user->about_me = $input['about_me'] ?? $user->about_me; 
        $user->phone = $input['phone'] ?? $user->phone;
        
        if (array_key_exists('weight_kg', $input)) $user->weight_kg = $input['weight_kg'];
        if (array_key_exists('height_cm', $input)) $user->height_cm = $input['height_cm'];
        if (array_key_exists('base_pace_min_km', $input)) $user->base_pace_min_km = $input['base_pace_min_km'];
        if (array_key_exists('distance_km', $input)) $user->distance_km = $input['distance_km'];
        if (array_key_exists('total_runs', $input)) $user->total_runs = $input['total_runs'];

        if ($request->has('email') && $user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }

        $user->save();
        
        Log::info("Profile Synced. Current Photo URL: " . $user->fresh()->profile_photo_url);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($user->fresh(), 200);
        }

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully! 🍫');
    }

    public function show($username): View
    {
        $user = \App\Models\User::where('username', $username)->firstOrFail();
        return view('profile.show', compact('user'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // 🛠️ Delete from the centralized folder
        if ($user->profile_photo_path) {
            $path = base_path('app_data/app/public/' . ltrim($user->profile_photo_path, '/'));
            if (File::exists($path)) File::delete($path);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;
        $user->save();

        return response()->json(['message' => 'Location updated successfully!']);
    }
}