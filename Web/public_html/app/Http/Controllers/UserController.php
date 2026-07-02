<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RunSummary; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the users for the Admin panel.
     */
    public function index(Request $request)
    {
        // Automatically mark users who haven't logged in for 7 days as inactive
        User::where('role', '!=', 'admin')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('last_login_at', '<', now()->subDays(7))
                  ->orWhere(function ($sub) {
                      $sub->whereNull('last_login_at')
                          ->where('created_at', '<', now()->subDays(7));
                  });
            })
            ->update(['status' => 'inactive']);

        $query = $request->input('search');

        // 1. Count ONLY regular users (exclude admins)
        $totalUsers = User::where('role', '!=', 'admin')->count();
        $activeUsers = User::where('role', '!=', 'admin')->where('status', 'active')->count();
        
        // 🛠️ FIXED: Pulling system-wide totals directly from the users table columns
        $totalRuns = User::where('role', '!=', 'admin')->sum('total_runs') ?? 0;
        $totalDistance = User::where('role', '!=', 'admin')->sum('distance_km') ?? 0;

        // 2. Main Table Query - Hides Admins
        $users = User::where('role', '!=', 'admin')
            ->when($query, function ($q) use ($query) {
                $q->where(function($subQuery) use ($query) {
                    $subQuery->where('name', 'LIKE', "%{$query}%")
                             ->orWhere('email', 'LIKE', "%{$query}%")
                             ->orWhere('username', 'LIKE', "%{$query}%");
                });
            })
            ->latest()
            ->get();

        return view('users.index', compact(
            'users', 'totalUsers', 'activeUsers', 'totalRuns', 'totalDistance'
        ));
    }

    /**
     * API: Search runners by username/name and suggest same tier.
     */
    public function apiSearch(Request $request)
    {
        $query = $request->input('query');
        $currentUser = Auth::user();
        
        $userQuery = User::where('role', '!=', 'admin')
                        ->where('id', '!=', $currentUser->id);

        if (!empty($query)) {
            $userQuery->where(function($q) use ($query) {
                $q->where('username', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%");
            });
        } else {
            // If query is empty, suggest users with the same tier
            if ($currentUser->runner_tier) {
                $userQuery->where('runner_tier', $currentUser->runner_tier);
            }
        }

        $users = $userQuery->limit(20)->get();

        return response()->json($users);
    }

    /**
     * API: Get detailed profile of a specific user including follow status.
     */
    public function apiShow($id)
    {
        $user = User::findOrFail($id);
        $me = Auth::user();

        // Append counts and status
        $user->followers_count = $user->followers()->count();
        $user->following_count = $user->following()->count();
        $user->is_following = $me->following()->where('following_id', $user->id)->exists();

        return response()->json($user);
    }

    /**
     * Search results page for user lookups.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        $users = User::where('role', '!=', 'admin')
                    ->where(function($q) use ($query) {
                        $q->where('username', 'LIKE', "%{$query}%")
                          ->orWhere('name', 'LIKE', "%{$query}%");
                    })
                    ->get();

        return view('users.search_results', compact('users', 'query'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'status' => 'required',
            'weight_kg' => 'nullable|numeric',
            'height_cm' => 'nullable|numeric',
            'base_pace_min_km' => 'nullable|numeric',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'role' => 'user',
            'weight_kg' => $request->weight_kg ?? 0,
            'height_cm' => $request->height_cm ?? 0,
            'base_pace_min_km' => $request->base_pace_min_km ?? 0,
            'total_runs' => 0,
            'distance_km' => 0,
            'last_login_at' => $request->status === 'active' ? now() : null,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    /**
     * Show user details.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show form for editing.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update user details.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'status' => 'required',
            'weight_kg' => 'nullable|numeric',
            'height_cm' => 'nullable|numeric',
            'base_pace_min_km' => 'nullable|numeric',
        ]);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        
        // Admin manual override logic
        if ($request->status === 'active' && $user->status !== 'active') {
            $user->last_login_at = now();
        } elseif ($request->status === 'inactive' && $user->status !== 'inactive') {
            $user->last_login_at = null;
        }

        $user->status = $request->status;
        $user->weight_kg = $request->weight_kg;
        $user->height_cm = $request->height_cm;
        $user->base_pace_min_km = $request->base_pace_min_km;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Delete user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot delete admin accounts from this panel.');
        }

        $user->delete();
        
        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
