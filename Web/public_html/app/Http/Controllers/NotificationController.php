<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Admin Dashboard: Monitors all global broadcasts.
     */
    public function index(Request $request)
    {
        $query = Notification::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
        }

        // Show unique broadcasts for the admin list (Grouped by Title)
        $notifications = $query->latest('created_at')->get()->unique('title');
        
        // 🛠️ FIXED: Count Logic
        // 1. Total Sent: Count all notifications where scheduled_at is NULL or in the past
        $totalSent = Notification::where(function($q) {
            $q->whereNull('scheduled_at')
              ->orWhere('scheduled_at', '<=', now());
        })->count();

        // 2. Scheduled: Count notifications with a future date
        $totalScheduled = Notification::where('scheduled_at', '>', now())->count();

        // 3. Recipients: Count unique users who have received messages
        $totalRecipients = Notification::distinct('user_id')->count();

        // 4. This Month: Count all created this month
        $thisMonth = Notification::whereMonth('created_at', now()->month)->count();

        return view('notifications.index', compact('notifications', 'totalSent', 'totalScheduled', 'totalRecipients', 'thisMonth'));
    }

    /**
     * User Feed: Marks unread as read and shows list.
     */
    public function userIndex()
    {
        // Mark all unread notifications as read when the user visits the feed
        Notification::where('user_id', Auth::id())
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('user.notifications.index', compact('notifications'));
    }

    /**
     * Store: Sends notifications to all registered users
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required',
            // 'status' validation removed or made optional since we hardcode 'unread' below
        ]);

        $users = User::all();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'status' => 'unread', // Default status for the user is 'unread'
                'scheduled_at' => $request->scheduled_at,
                'recipients_count' => $users->count(),
            ]);
        }

        return redirect()->route('notifications.index')->with('success', 'Global notification sent!');
    }

    // Standard CRUD
    public function create() { return view('notifications.create'); }
    
    public function edit($id) { 
        $notification = Notification::findOrFail($id); 
        return view('notifications.edit', compact('notification')); 
    }
    
    public function update(Request $request, $id) { 
        // Logic to update if needed
    }
    
    public function destroy($id) { 
        $notification = Notification::findOrFail($id);
        // Delete all notifications with the same title (Global Delete)
        Notification::where('title', $notification->title)->delete();
        return redirect()->route('notifications.index')->with('success', 'Notification broadcast deleted.');
    }
}