<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RunSummary;
use App\Models\Event;
use App\Models\Post;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // 1. Import DB Facade

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Monthly Goal Logic
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $monthlyGoal = \App\Models\MonthlyKmGoal::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => $currentYear,
                'month' => $currentMonth
            ],
            [
                'goal_km' => 50 
            ]
        );
        if ($user->role === 'admin') {
            $monthlyDistance = RunSummary::whereHas('user', function($query) {
                $query->where('role', '!=', 'admin');
            })
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->sum('distance_km') ?? 0;

            $monthlyRuns = RunSummary::whereHas('user', function($query) {
                $query->where('role', '!=', 'admin');
            })
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
        } else {
            $monthlyDistance = RunSummary::where('user_id', $user->id)
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->sum('distance_km') ?? 0;

            $monthlyRuns = RunSummary::where('user_id', $user->id)
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->count();
        }

        $monthlyProgress = $monthlyGoal->goal_km > 0 ? min(100, round(($monthlyDistance / $monthlyGoal->goal_km) * 100, 1)) : 0;

        // --- DASHBOARD LOGIC ---
        if ($user->role === 'admin') {
            // Admin Stats
            $activeUsers = User::where('role', '!=', 'admin')->count();
            $totalDistance = User::where('role', '!=', 'admin')->sum('distance_km') ?? 0;
            $totalRuns = User::where('role', '!=', 'admin')->sum('total_runs') ?? 0;
            
            // 🛠️ FIX: Use DB query to ignore Model scopes and handle case-sensitivity
            // This counts any group where status is 'active' or 'Active'
            $activeGroups = DB::table('groups')
                ->where(function($query) {
                    $query->where('status', 'active')
                          ->orWhere('status', 'Active');
                })
                ->count();

            $recentActivity = RunSummary::with('user')->latest()->take(10)->get();
            
            // Map admin variables
            $myRuns = $totalRuns;
            $myDistance = $totalDistance;
            
            $sevenDayActivity = $this->getSevenDayActivity();
        } else {
            // User Stats
            $activeUsers = User::where('role', '!=', 'admin')->count(); 
            $myDistance = $user->distance_km ?? 0;
            $myRuns = $user->total_runs ?? 0;
            
            // User's joined groups
            $activeGroups = $user->joinedGroups()->count();

            $totalDistance = $myDistance;
            $totalRuns = $myRuns;
            
            // Social Run activity feed (followed users + own runs)
            $followedUserIds = $user->following()->pluck('users.id')->concat([$user->id]);
            $recentActivity = RunSummary::whereIn('user_id', $followedUserIds)
                ->with('user')
                ->latest()
                ->take(10)
                ->get();
            
            $sevenDayActivity = $this->getSevenDayActivity($user->id);
        }

        // Fetch user's own runs history with AI coaching feedback (limit to 3 for dashboard)
        $myRunsHistory = ($user->role === 'admin') 
            ? collect() 
            : RunSummary::where('user_id', $user->id)->latest()->take(3)->get();

        // --- SOCIAL DATA ---
        // Auto-update past events to completed status
        Event::where('status', 'upcoming')
            ->whereDate('date', '<', now()->toDateString())
            ->update(['status' => 'completed']);

        $upcomingEvents = Event::where('status', 'upcoming')
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->take(3)
            ->get();
        $friendPosts = Post::with('user')->latest()->take(10)->get();
        $followingCount = $user->following()->count();
        $followersCount = $user->followers()->count();

        return view('dashboard', compact(
            'activeUsers', 
            'activeGroups', 
            'totalDistance', 
            'totalRuns', 
            'myDistance',
            'myRuns',
            'recentActivity',
            'upcomingEvents',
            'friendPosts',
            'followingCount',
            'followersCount',
            'sevenDayActivity',
            'monthlyGoal',
            'monthlyDistance',
            'monthlyProgress',
            'myRunsHistory',
            'monthlyRuns'
        ));
    }

    /**
     * Get 7-day activity.
     */
    private function getSevenDayActivity($userId = null)
    {
        $days = [];
        $distances = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[] = now()->subDays($i)->format('D');
            $query = RunSummary::whereDate('created_at', $date);
            if ($userId) {
                $query->where('user_id', $userId);
            }
            $distances[] = round($query->sum('distance_km'), 2);
        }
        return [
            'days' => $days,
            'distances' => $distances
        ];
    }

    /**
     * Display all past runs with AI suggestions for the user.
     */
    public function myRuns()
    {
        $user = Auth::user();
        if (!$user || $user->role === 'admin') {
            return redirect()->route('dashboard');
        }

        $myRunsHistory = RunSummary::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('user.runs', compact('myRunsHistory'));
    }
}
