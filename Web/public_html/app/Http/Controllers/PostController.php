<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Group;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    /**
     * 🛠️ HELPER: The Query String Formatter
     * Generates URLs like: https://runtracker.fun/serve-image?path=post/123.jpg
     */
    private function formatPostUrls($post)
    {
        // 1. Fix Post Images
        if ($post->image_url && is_array($post->image_url)) {
            $post->image_url = array_map(function($path) {
                return str_contains($path, 'http') ? $path : url('/serve-image?path=' . ltrim($path, '/'));
            }, $post->image_url);
        }
        
        // 2. Fix User Profile Photo attached to the Post
        if ($post->user && $post->user->profile_photo_path && !str_contains($post->user->profile_photo_path, 'http')) {
            $post->user->profile_photo_path = url('/serve-image?path=' . ltrim($post->user->profile_photo_path, '/'));
        }

        // 3. Fix the static user_image field on the post itself
        if ($post->user_image && !str_contains($post->user_image, 'http')) {
            $post->user_image = url('/serve-image?path=' . ltrim($post->user_image, '/'));
        }

        if ($post->relationLoaded('comments')) {
            $post->comments->each(function ($comment) {
                if ($comment->user && $comment->user->profile_photo_path && !str_contains($comment->user->profile_photo_path, 'http')) {
                    $comment->user->profile_photo_path = url('/serve-image?path=' . ltrim($comment->user->profile_photo_path, '/'));
                }
            });
        }

        $userId = Auth::id();
        if ($userId) {
            $post->liked_by_me = $post->likes()->where('user_id', $userId)->exists();
        }

        return $post;
    }

    // ==========================================
    // 1. API METHODS (FOR ANDROID APP)
    // ==========================================


    // ==========================================
    // 1. API METHODS (FOR ANDROID APP)
    // ==========================================

    public function homeFeed(Request $request)
    {
        $posts = Post::with('user')
                     ->withCount(['likes', 'comments'])
                     ->latest()
                     ->get();

        // Deduplicate posts that have the same content, user_id, and were posted around the same time.
        $posts = $posts->unique(function ($post) {
            $timeKey = $post->posted_at ? $post->posted_at->format('Y-m-d H:i') : '';
            return $post->user_id . '_' . md5($post->content) . '_' . $timeKey;
        })->values();

        $posts->transform(function ($post) {
            return $this->formatPostUrls($post);
        });

        return response()->json($posts, 200);
    }

    public function indexForApi($groupId)
    {
        $posts = Post::where('group_id', $groupId)
                     ->with('user')
                     ->withCount(['likes', 'comments'])
                     ->latest()
                     ->get();

        $posts->transform(function ($post) {
            return $this->formatPostUrls($post);
        });

        return response()->json($posts, 200);
    }

    // ==========================================
    // 2. WEBSITE USER METHODS (FOR /user/posts)
    // ==========================================

    public function userIndex()
    {
        $posts = Post::with('user', 'likes')->withCount(['likes', 'comments'])->latest()->get();
        
        // Deduplicate posts that have the same content, user_id, and were posted around the same time.
        $posts = $posts->unique(function ($post) {
            $timeKey = $post->posted_at ? $post->posted_at->format('Y-m-d H:i') : '';
            return $post->user_id . '_' . md5($post->content) . '_' . $timeKey;
        })->values();

        $posts->transform(function ($post) {
            return $this->formatPostUrls($post);
        });

        return view('user.posts', compact('posts'));
    }

    public function userCreate()
    {
        return view('user.posts.create');
    }

    public function userStore(Request $request)
    {
        return $this->store($request);
    }

    public function userEdit($id)
    {
        $post = Post::findOrFail($id);
        if (Auth::id() !== $post->user_id) {
            return redirect()->route('user.posts')->with('error', 'Unauthorized access.');
        }
        return view('user.posts.edit', compact('post'));
    }

    // ==========================================
    // 3. CORE LOGIC (HANDLES BOTH WEB & API)
    // ==========================================

    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'nullable|exists:groups,id', 
            'content' => 'required|string',
            'media.*' => 'nullable|image|max:10240', 
        ]);

        $user = Auth::user();
        $groupIds = [];

        if ($request->filled('group_id')) {
            $groupIds[] = $request->group_id;
        } else {
            // Post from home feed: publish to only the first joined group to avoid duplication
            $joinedGroups = $user->joinedGroups;
            if ($joinedGroups->isNotEmpty()) {
                $groupIds[] = $joinedGroups->first()->id;
            } else {
                // Fallback to first group if they haven't joined any groups (since column is not nullable)
                $firstGroup = Group::first();
                if ($firstGroup) {
                    $groupIds[] = $firstGroup->id;
                }
            }
        }

        $imagePaths = [];
        $destinationPath = base_path('app_data/app/public/post');

        if ($request->hasFile('media')) {
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }

            foreach ($request->file('media') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $imagePaths[] = 'post/' . $filename;
            }
        }

        $lastCreatedPost = null;

        foreach ($groupIds as $gId) {
            $lastCreatedPost = Post::create([
                'group_id' => $gId,
                'content' => $request->content,
                'image_url' => !empty($imagePaths) ? $imagePaths : null, 
                'user_id' => $user->id, 
                'username' => $user->username, 
                'author_name' => $user->name,
                'author_username' => $user->username,
                'user_image' => $user->profile_photo_path,
                'posted_at' => now(), 
                'category' => $request->category ?? 'general',
            ]);
        }

        // If no groups exist and groupIds is empty, we still try to create a global post
        if (empty($groupIds)) {
            $lastCreatedPost = Post::create([
                'group_id' => null,
                'content' => $request->content,
                'image_url' => !empty($imagePaths) ? $imagePaths : null, 
                'user_id' => $user->id, 
                'username' => $user->username, 
                'author_name' => $user->name,
                'author_username' => $user->username,
                'user_image' => $user->profile_photo_path,
                'posted_at' => now(), 
                'category' => $request->category ?? 'general',
            ]);
        }

        if ($request->is('api/*') || $request->wantsJson()) {
            if ($lastCreatedPost) {
                $lastCreatedPost->load('user');
                $lastCreatedPost->loadCount(['likes', 'comments']);
                return response()->json($this->formatPostUrls($lastCreatedPost), 201);
            }
            return response()->json(['message' => 'No group available to publish post.'], 400);
        }

        return redirect()->route('user.posts')->with('success', 'Post shared successfully!');
    }

    public function show($id) 
    {
        $post = Post::with(['user', 'comments.user'])->withCount(['likes', 'comments'])->findOrFail($id);
        $post = $this->formatPostUrls($post);
        
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json($post);
        }

        $view = request()->is('admin/*') ? 'posts.show' : 'user.posts.show';
        return view($view, compact('post'));
    }

    // ==========================================
    // 4. ADMIN & INTERACTION METHODS
    // ==========================================

    public function index(Request $request)
    {
        $query = Post::query();
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('content', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
        }
        
        $posts = $query->withCount(['likes', 'comments'])->latest()->get();
        
        $posts->transform(function ($post) {
            return $this->formatPostUrls($post);
        });
        
        $totalPosts = Post::count();
        $flaggedPosts = Post::where('is_flagged', 1)->count();
        $totalLikes = Like::count();
        $totalComments = Comment::count();
        
        return view('posts.index', compact('posts', 'totalPosts', 'flaggedPosts', 'totalLikes', 'totalComments'));
    }

    public function like($id) 
    {
        $userId = Auth::id();
        $post = Post::findOrFail($id);
        $existingLike = Like::where('post_id', $id)->where('user_id', $userId)->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            Like::create(['post_id' => $id, 'user_id' => $userId]);
            $liked = true;
        }

        return response()->json([
            'status' => 'success',
            'liked' => $liked,
            'count' => $post->likes()->count()
        ]);
    }

    public function comment(Request $request, $id) 
    {
        $request->validate(['comment' => 'required|string|max:1000']);
        $comment = Comment::create([
            'body' => $request->comment,
            'post_id' => $id,
            'user_id' => Auth::id(),
        ]);

        if ($request->is('api/*') || $request->wantsJson()) {
            $comment->load('user');
            if ($comment->user && $comment->user->profile_photo_path && !str_contains($comment->user->profile_photo_path, 'http')) {
                $comment->user->profile_photo_path = url('/serve-image?path=' . ltrim($comment->user->profile_photo_path, '/'));
            }
            return response()->json($comment, 201);
        }

        return back()->with('success', 'Comment added successfully!');
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if (Auth::id() !== $post->user_id && Auth::user()->role !== 'admin') {
            if (request()->is('api/*') || request()->wantsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            return back()->with('error', 'Unauthorized action.');
        }

        if ($post->image_url && is_array($post->image_url)) {
            foreach($post->image_url as $imagePath) {
                $absolutePath = base_path('app_data/app/public/' . ltrim($imagePath, '/'));
                if (File::exists($absolutePath)) {
                    File::delete($absolutePath);
                }
            }
        }

        $post->delete();
        if (request()->is('api/*') || request()->wantsJson()) {
            return response()->json(['message' => 'Post deleted successfully.']);
        }
        return back()->with('success', 'Post deleted successfully!');
    }
}
