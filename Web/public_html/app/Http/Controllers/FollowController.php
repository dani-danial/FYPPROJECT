<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Toggles the follow status between the logged-in user and another runner.
     */
    public function toggle(User $user, Request $request)
    {
        $me = Auth::user();

        // Prevent users from following themselves
        if ($me->id === $user->id) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'You cannot follow yourself.'], 422);
            }
            return back()->with('error', "You cannot follow yourself.");
        }

        // The toggle method adds the record if missing, or deletes it if it exists
        $me->following()->toggle($user->id);
        
        $isFollowing = $me->following()->where('following_id', $user->id)->exists();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'is_following' => $isFollowing,
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count()
            ]);
        }

        return back()->with('success', 'Connection updated!');
    }

    /**
     * NEW: Fetch the list of users the current runner is following for the Chat App
     */
    public function getFollowing()
    {
        $user = Auth::user();
        
        // Fetch users that the current user follows
        $following = $user->following()->get(); 

        return response()->json($following);
    }
}