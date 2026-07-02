<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Display the chat dashboard with all active conversations.
     */
    public function index()
    {
        // Get all conversations involving the logged-in user
        $conversations = Conversation::where('sender_id', Auth::id())
            ->orWhere('receiver_id', Auth::id())
            ->with(['sender', 'receiver', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->get();

        return view('chat.index', compact('conversations'));
    }

    /**
     * Fetch all messages for a specific conversation via AJAX.
     */
    public function show(Conversation $conversation)
    {
        // Security check: ensure the user belongs to this conversation
        if ($conversation->sender_id !== Auth::id() && $conversation->receiver_id !== Auth::id()) {
            abort(403);
        }

        $messages = $conversation->messages()->with('user')->oldest()->get();

        $messages->each(function ($message) {
            if ($message->user && $message->user->profile_photo_path && !str_contains($message->user->profile_photo_path, 'http')) {
                $message->user->profile_photo_path = $message->user->profile_photo_url;
            }
        });

        if (request()->is('api/*') || request()->wantsJson() && str_starts_with(request()->path(), 'api/')) {
            return response()->json($messages);
        }

        return response()->json([
            'messages' => $messages,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Store and return a new message via AJAX.
     */
    public function store(Request $request, Conversation $conversation)
    {
        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->body
        ]);

        return response()->json($message->load('user'));
    }

    /**
     * Initialize or find a conversation from Android app
     */
    public function start($userId, Request $request)
    {
        $myId = Auth::id();

        // 1. Safety check to ensure the user is logged in
        if (!$myId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // 2. Find an existing conversation between these two users
        $conversation = Conversation::where(function($q) use ($myId, $userId) {
            $q->where('sender_id', $myId)->where('receiver_id', $userId);
        })->orWhere(function($q) use ($myId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $myId);
        })->first();

        // 3. If no conversation exists, create a new one
        if (!$conversation) {
            $conversation = Conversation::create([
                'sender_id' => $myId,
                'receiver_id' => $userId
            ]);
        }

        // 4. Return the Conversation object as JSON so Android Retrofit can read the ID
        return response()->json($conversation);
    }
}
