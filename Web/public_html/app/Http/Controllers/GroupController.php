<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class GroupController extends Controller
{
    /**
     * 🛠️ Helper to format absolute URLs consistently.
     * FIXED: Now forces the URL through the serve-image proxy and handles spaces.
     */
    private function formatUrls($group) {
        if ($group->icon_url && !str_contains($group->icon_url, 'http')) {
            // Encode spaces to %20 to fix older broken uploads for Android
            $safePath = str_replace(' ', '%20', $group->icon_url);
            $group->icon_url = url('/serve-image?path=' . $safePath);
        }
        if ($group->banner_url && !str_contains($group->banner_url, 'http')) {
            $safePath = str_replace(' ', '%20', $group->banner_url);
            $group->banner_url = url('/serve-image?path=' . $safePath);
        }
        return $group;
    }

    // --- 1. ADMIN INDEX ---
    public function index(Request $request)
    {
        $query = Group::query();
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        $groups = $query->latest()->get();

        $userId = Auth::id() ?? auth('sanctum')->id();
        $groups->transform(function ($group) use ($userId) {
            $isMember = $userId ? $group->users()->where('users.id', $userId)->exists() : false;
            $group->setAttribute('is_member', $isMember);
            
            // Compute monthly current_km for members
            $memberIds = $group->users()->pluck('users.id');
            $currentKm = 0;
            if ($memberIds->isNotEmpty()) {
                $currentKm = \App\Models\RunSummary::whereIn('user_id', $memberIds)
                    ->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month)
                    ->sum('distance_km');
            }
            $group->setAttribute('current_km', round($currentKm, 2));

            return $this->formatUrls($group);
        });

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($groups, 200);
        }

        $totalGroups = Group::count();
        $activeGroups = Group::where('status', 'active')->count();
        $totalMembers = Group::sum('members_count');

        return view('groups.index', compact('groups', 'totalGroups', 'activeGroups', 'totalMembers'));
    }

    // --- 2. WEBSITE USER GROUPS ---
    public function userIndex()
    {
        $groups = Group::with('users')->where('status', 'active')->latest()->get();
        
        $groups->transform(function ($group) {
            return $this->formatUrls($group);
        });

        return view('user.groups', compact('groups'));
    }

    // --- 3. STORE (FIXED FOR SPACES & PROXY) ---
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'target_km' => 'required|numeric|min:0',
            'icon' => 'nullable|image|max:2048', 
            'banner' => 'nullable|image|max:3072', 
        ]);

        $iconUrl = null;
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            // 🛠️ Remove spaces from filename
            $safeName = str_replace(' ', '_', $file->getClientOriginalName());
            $filename = time() . '_icon_' . $safeName;
            $file->move(base_path('app_data/app/public/groups/icons'), $filename);
            $iconUrl = 'groups/icons/' . $filename;
        }

        $bannerUrl = null;
        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            // 🛠️ Remove spaces from filename
            $safeName = str_replace(' ', '_', $file->getClientOriginalName());
            $filename = time() . '_banner_' . $safeName;
            $file->move(base_path('app_data/app/public/groups/banners'), $filename);
            $bannerUrl = 'groups/banners/' . $filename;
        }

        $creatorId = $request->creator_id ?? Auth::id();

        $group = Group::create([
            'name' => $request->name,
            'location' => $request->location,
            'description' => $request->description,
            'target_km' => $request->target_km,
            'icon_url' => $iconUrl,
            'banner_url' => $bannerUrl, 
            'status' => 'active',
            'creator_id' => $creatorId,
            'members_count' => 1,
            'created_date' => now()
        ]);

        $user = User::find($creatorId);
        if ($user) {
            $user->joinedGroups()->syncWithoutDetaching([$group->id]);
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($this->formatUrls($group), 201); 
        }

        return redirect()->route('user.groups')->with('success', "Group created successfully!");
    }

    // --- 4. SHOW DETAILS ---
    public function show($id)
    {
        $group = Group::withCount('users')->findOrFail($id);
        $group = $this->formatUrls($group);

        $userId = Auth::id() ?? auth('sanctum')->id();
        $isMember = $userId ? $group->users()->where('users.id', $userId)->exists() : false;
        $group->setAttribute('is_member', $isMember);

        // Compute monthly current_km for members
        $memberIds = $group->users()->pluck('users.id');
        $currentKm = 0;
        if ($memberIds->isNotEmpty()) {
            $currentKm = \App\Models\RunSummary::whereIn('user_id', $memberIds)
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->sum('distance_km');
        }
        $group->setAttribute('current_km', round($currentKm, 2));

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json($group, 200);
        }

        return view('groups.show', compact('group'));
    }

    /**
     * 🏆 API: Retrieve leaderboard stats for the current month
     */
    public function leaderboard($id)
    {
        $group = Group::findOrFail($id);
        $members = $group->users; // Get all members of the group
        
        $leaderboard = [];
        
        foreach ($members as $member) {
            // Get runs in the current calendar month for this member
            $runs = \App\Models\RunSummary::where('user_id', $member->id)
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->get();
                
            $totalDistance = (double) $runs->sum('distance_km');
            $totalRuns = (int) $runs->count();
            
            $leaderboard[] = [
                'user_id' => (string) $member->id,
                'user_name' => $member->name,
                'user_image_base64' => $member->profile_photo_url, // Return URL, client loads via Glide
                'total_distance' => round($totalDistance, 2),
                'total_runs' => $totalRuns
            ];
        }
        
        // Sort descending by total_distance
        usort($leaderboard, function($a, $b) {
            return $b['total_distance'] <=> $a['total_distance'];
        });
        
        return response()->json($leaderboard, 200);
    }

    public function userShow($id)
    {
        $group = Group::with('users')->findOrFail($id);
        $group = $this->formatUrls($group);

        return view('user.groups.show', compact('group'));
    }

    // --- 5. MEMBERSHIP LOGIC ---
    public function members($id)
    {
        $group = Group::with(['users' => function($query) {
            $query->withPivot('created_at');
        }])->findOrFail($id);
        
        if (Auth::id() !== $group->creator_id && Auth::user()->role !== 'admin') {
            return redirect()->route('user.groups')->with('error', 'Unauthorized.');
        }

        return view('user.groups.members', compact('group'));
    }

    public function kick($groupId, $userId)
    {
        $group = Group::findOrFail($groupId);
        if (Auth::id() !== $group->creator_id) {
            return back()->with('error', 'Only the group creator can kick members.');
        }
        if ($userId == Auth::id()) {
            return back()->with('error', 'You cannot kick yourself.');
        }

        $group->users()->detach($userId);
        $group->update(['members_count' => $group->users()->count()]);
        return back()->with('success', 'Member removed successfully.');
    }

    public function join($id)
    {
        $group = Group::findOrFail($id);
        Auth::user()->joinedGroups()->syncWithoutDetaching([$id]);
        $group->update(['members_count' => $group->users()->count()]);

        if (request()->is('api/*')) {
            return response()->json(['message' => 'Joined successfully', 'members_count' => $group->members_count]);
        }
        return back()->with('success', "Joined {$group->name}!");
    }

    public function leave($id)
    {
        $group = Group::findOrFail($id);
        Auth::user()->joinedGroups()->detach($id);
        $group->update(['members_count' => $group->users()->count()]);
        return back()->with('success', "Left {$group->name}.");
    }

    // --- 6. UPDATE & DELETE ---
    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        if (Auth::id() !== $group->creator_id && Auth::user()->role !== 'admin') {
            if ($request->is('api/*')) return response()->json(['error' => 'Unauthorized'], 403);
            return redirect()->route('user.groups')->with('error', 'Unauthorized.');
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'target_km' => 'nullable|numeric',
            'icon' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:3072',
        ]);

        $data = $request->only(['name', 'description', 'location', 'target_km']);

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            // 🛠️ Remove spaces from filename
            $safeName = str_replace(' ', '_', $file->getClientOriginalName());
            $filename = time() . '_icon_' . $safeName;
            $file->move(base_path('app_data/app/public/groups/icons'), $filename);
            $data['icon_url'] = 'groups/icons/' . $filename;
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            // 🛠️ Remove spaces from filename
            $safeName = str_replace(' ', '_', $file->getClientOriginalName());
            $filename = time() . '_banner_' . $safeName;
            $file->move(base_path('app_data/app/public/groups/banners'), $filename);
            $data['banner_url'] = 'groups/banners/' . $filename;
        }

        $group->update($data);

        if ($request->is('api/*') || $request->wantsJson()) {
            return response()->json($this->formatUrls($group), 200);
        }
        
        return redirect()->route('user.groups.show', $id)->with('success', 'Group updated!');
    }

    public function destroy(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        // Security: Check if User is Creator or Admin
        if (Auth::id() !== $group->creator_id && Auth::user()->role !== 'admin') {
            if ($request->is('api/*')) return response()->json(['error' => 'Unauthorized'], 403);
            return redirect()->route('user.groups')->with('error', 'Unauthorized.');
        }

        // Cleanup Files
        if ($group->icon_url && File::exists(base_path('app_data/app/public/' . $group->icon_url))) {
            File::delete(base_path('app_data/app/public/' . $group->icon_url));
        }
        if ($group->banner_url && File::exists(base_path('app_data/app/public/' . $group->banner_url))) {
            File::delete(base_path('app_data/app/public/' . $group->banner_url));
        }

        $group->delete();

        if ($request->is('api/*') || $request->wantsJson()) {
            return response()->json(['message' => 'Group deleted successfully'], 200);
        }

        return redirect()->route('user.groups')->with('success', 'Group deleted successfully.');
    }

    // --- 7. VIEW HELPERS ---
    public function create() { return view('groups.create'); }
    public function userCreate() { return view('user.groups.create'); }
    public function userStore(Request $request) { return $this->store($request); }
    public function edit($id) { 
        $group = Group::findOrFail($id);
        $group = $this->formatUrls($group);
        return view('user.groups.edit', compact('group')); 
    }
    
    /**
     * 🏃 MOBILE API: Handle joining a group
     */
    public function apiJoin(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $group = Group::findOrFail($request->group_id);
        $request->user()->joinedGroups()->syncWithoutDetaching([$group->id]);
        $group->update(['members_count' => $group->users()->count()]);
        $group->loadCount('users');

        return response()->json([
            'message' => 'Successfully joined ' . $group->name,
            'members_count' => $group->members_count,
            'data' => $this->formatUrls($group),
        ], 200);
    }

    /**
     * 🏃 MOBILE API: Handle leaving a group
     */
    public function apiLeave(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $group = Group::findOrFail($request->group_id);
        $request->user()->joinedGroups()->detach($group->id);
        $group->update(['members_count' => $group->users()->count()]);
        $group->loadCount('users');

        return response()->json([
            'message' => 'Successfully left ' . $group->name,
            'members_count' => $group->members_count,
            'data' => $this->formatUrls($group),
        ], 200);
    }

    /**
     * Fetch all messages for a specific group.
     */
    public function getMessages(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        $userId = Auth::id() ?? auth('sanctum')->id();

        if (!$group->users()->where('users.id', $userId)->exists()) {
            return response()->json(['error' => 'You must be a member of the group to access chat.'], 403);
        }

        $messages = \App\Models\GroupMessage::where('group_id', $id)
            ->with(['user'])
            ->oldest()
            ->get();

        $messages->each(function ($message) {
            if ($message->user && $message->user->profile_photo_path && !str_contains($message->user->profile_photo_path, 'http')) {
                $message->user->profile_photo_path = $message->user->profile_photo_url;
            }
        });

        return response()->json($messages);
    }

    /**
     * Send a message to the group.
     */
    public function sendMessage(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        $userId = Auth::id() ?? auth('sanctum')->id();

        if (!$group->users()->where('users.id', $userId)->exists()) {
            return response()->json(['error' => 'You must be a member of the group to send messages.'], 403);
        }

        $request->validate([
            'body' => 'required|string',
        ]);

        $message = \App\Models\GroupMessage::create([
            'group_id' => $id,
            'user_id' => $userId,
            'body' => $request->body,
        ]);

        return response()->json($message->load('user'));
    }
}
