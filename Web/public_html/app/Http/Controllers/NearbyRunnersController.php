<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NearbyRunnersController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 1. Safety Check: If user has no location set, show empty state
        if (!$user->latitude || !$user->longitude) {
            return view('user.nearby', [
                'nearbyUsers' => collect(),
                'radius' => 10,
                'error' => 'Please set your location in your profile settings to find runners nearby.'
            ]);
        }

        // 2. Get Radius from URL or default to 10km
        $radius = $request->get('radius', 10); 

        // 3. Haversine Formula to find users within specific KM radius
        $nearbyUsers = User::select('users.*')
            ->selectRaw('( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance', 
                [$user->latitude, $user->longitude, $user->latitude])
            ->where('id', '!=', $user->id)        // Exclude self
            ->where('role', '!=', 'admin')        // Exclude admins
            ->having('distance', '<=', $radius)   // Filter by radius
            ->orderBy('distance', 'asc')          // Closest first
            ->get();

        return view('user.nearby', compact('nearbyUsers', 'radius'));
    }
}