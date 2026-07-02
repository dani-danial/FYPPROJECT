<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RunSummary;
use App\Models\User;
use App\Services\RunnerTierEngine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RunController extends Controller
{
    public function __construct(private RunnerTierEngine $tierEngine)
    {
    }

    // ==========================================
    // WEB METHODS (For Admin Dashboard)
    // ==========================================

    /**
     * Display all activity logs.
     */
    public function index()
    {
        $runs = RunSummary::latest()->paginate(20);
        return view('runs.index', compact('runs'));
    }

    /**
     * Show the form for creating a new run manually.
     */
    public function create()
    {
        $users = User::select('id', 'username', 'name')
                     ->orderBy('username', 'asc')
                     ->get();
        
        return view('runs.create', compact('users'));
    }

    /**
     * Store a manually created run from the web interface.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_info' => 'required',
            'distance_km' => 'required|numeric',
            'time' => 'required',
            'date' => 'required|date',
            'manual_name' => 'required_if:user_info,Admin|nullable|string|max:255',
        ]);

        $run = new RunSummary();
        $selectedUser = $request->input('user_info');

        if ($selectedUser === 'Admin') {
            $run->user_id = 'Admin';
            $run->username = $request->input('manual_name');
        } else {
            list($userId, $username) = explode('|', $selectedUser);
            $run->user_id = $userId;
            $run->username = $username;
        }

        $run->distance_km = $request->input('distance_km');
        $run->time = $request->input('time');
        $run->pace = $this->calculatePace($request->input('distance_km'), $request->input('time'));
        $run->date = $request->input('date');
        
        $run->save();

        return redirect('/')->with('success', 'Run assigned successfully!');
    }

    // ==========================================
    // API METHODS (For Android App)
    // ==========================================

    /**
     * API: SAVE NEW RUN FROM ANDROID APP
     * Matches keys: distance_km, duration_seconds, average_pace, route_path, share_to_feed
     */
    public function apiStore(Request $request)
    {
        $request->validate([
            'distance_km' => 'required|numeric',
            'duration_seconds' => 'required|integer', 
            'average_pace' => 'required|string',
            'route_path' => 'nullable|string',
            'share_to_feed' => 'nullable|boolean',
            'image' => 'nullable|image|max:10240',
        ]);

        $run = new RunSummary();
        
        $user = Auth::user();
        $run->user_id = $user->id; 
        $run->username = $user->username;
        
        $run->distance_km = $request->distance_km;
        
        // Convert seconds to HH:mm:ss for MySQL "time" column
        $run->time = gmdate("H:i:s", $request->duration_seconds); 
        
        $run->pace = $request->average_pace;
        $run->date = now()->toDateString();
        
        if ($request->filled('route_path')) {
            $decoded = json_decode($request->route_path, true);
            $run->route_path = is_array($decoded) ? $decoded : $request->route_path;
        }
        
        $run->save();

        $user->distance_km = (float) ($user->distance_km ?? 0) + (float) $run->distance_km;
        $user->total_runs = (int) ($user->total_runs ?? 0) + 1;
        $user->save();

        // 1. Calculate Runner Tier engine update
        $tierUpdate = $this->tierEngine->updateFromWeeklyTraining($user);
        
        // 2. Synthesize modifiers log
        $weeklyDistance = $tierUpdate['weekly_distance_km'] ?? 0;
        $weeklyFrequency = $tierUpdate['weekly_frequency'] ?? 0;
        
        $targetDistance = 12;
        if (($tierUpdate['tier'] ?? '') === 'MEDIUM') $targetDistance = 25;
        if (($tierUpdate['tier'] ?? '') === 'HARD') $targetDistance = 45;
        
        $modifiers = [];
        if ($weeklyFrequency < 3) {
            $modifiers[] = "Low frequency penalty risk.";
        } else {
            $modifiers[] = "Run frequency maintained. No Inconsistency Penalty applied.";
        }
        $modifiers[] = "Weekly total updated to " . round($weeklyDistance, 2) . "km/{$targetDistance}km.";
        $modifiersLog = implode(" ", $modifiers);

        // 3. Call Gemini API for prescriptive coaching evaluation
        $aiEvaluation = "";
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key=" . $apiKey;
        
        $prompt = "Role: Professional Running Coach. Situation: A runner named " . $user->name . " just completed a run.
        Run Stats:
        - Distance: " . round($run->distance_km, 2) . " km
        - Duration: " . $run->time . "
        - Avg Pace: " . $run->pace . "
        
        User Profile:
        - Weight: " . ($user->weight_kg ?? '70') . " kg
        - Height: " . ($user->height_cm ?? '170') . " cm
        - Base Pace: " . ($user->base_pace_min_km ?? '6:00') . " min/km
        
        Goal: Analyze their performance and provide concise, elite feedback divided into exactly two sections:
        1. **Performance Note**: Analyzing their pace, distance, and duration relative to their baseline.
        2. **Recovery & Nutrition Advice**: Short, practical recovery window recommendations and nutrition tips.
        
        Format your response clearly. Be encouraging, elite, and keep it under 150 words total.";

        $payload = [
            "system_instruction" => [
                "parts" => [
                    ["text" => "Role: Professional Elite Running Coach. Be concise, actionable, and structured."]
                ]
            ],
            "contents" => [
                [
                    "role" => "user", 
                    "parts" => [["text" => $prompt]]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 300
            ]
        ];
        
        try {
            if ($apiKey) {
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                                ->post($url, $payload);
                if ($response->successful()) {
                    $resData = $response->json();
                    $aiMessage = $resData['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($aiMessage) {
                        $aiEvaluation = trim($aiMessage);
                    }
                } else {
                    Log::error("Gemini API Error in RunController: " . $response->body());
                }
            }
        } catch (\Exception $e) {
            Log::error("Gemini Run Evaluation Exception: " . $e->getMessage());
        }

        // Save AI evaluation to database
        if (!empty($aiEvaluation)) {
            $run->ai_evaluation = $aiEvaluation;
            $run->save();
        }

        // 4. Social Feed Sharing
        if ($request->input('share_to_feed')) {
            $joinedGroups = $user->joinedGroups;
            
            $content = "🏃 completed a " . round($run->distance_km, 2) . " km run in " . $run->time . " (Pace: " . $run->pace . ")!";
            
            $imagePaths = [];
            if ($request->hasFile('image')) {
                $destinationPath = base_path('app_data/app/public/post');
                if (!\Illuminate\Support\Facades\File::isDirectory($destinationPath)) {
                    \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true, true);
                }
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $imagePaths[] = 'post/' . $filename;
            }

            if ($joinedGroups->isNotEmpty()) {
                foreach ($joinedGroups as $joinedGroup) {
                    \App\Models\Post::create([
                        'group_id' => $joinedGroup->id,
                        'content' => $content,
                        'image_url' => !empty($imagePaths) ? $imagePaths : null,
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'author_name' => $user->name,
                        'author_username' => $user->username,
                        'user_image' => $user->profile_photo_path,
                        'posted_at' => now(),
                        'category' => 'run_summary',
                    ]);
                }
            } else {
                // Post to global feed only
                \App\Models\Post::create([
                    'group_id' => null,
                    'content' => $content,
                    'image_url' => !empty($imagePaths) ? $imagePaths : null,
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'author_name' => $user->name,
                    'author_username' => $user->username,
                    'user_image' => $user->profile_photo_path,
                    'posted_at' => now(),
                    'category' => 'run_summary',
                ]);
            }
        }

        // Convert route_path back to string for GSON compatibility
        if ($run->route_path !== null) {
            $run->route_path = json_encode($run->route_path);
        }

        return response()->json([
            'message' => 'Run synced to Dashboard! 🏃',
            'data' => $run,
            'tier_update' => $tierUpdate,
            'modifiers_log' => $modifiersLog,
            'ai_evaluation' => $aiEvaluation
        ], 201);
    }

    /**
     * API: GET RUN HISTORY FOR MOBILE USER
     */
    public function apiIndex()
    {
        $history = RunSummary::where('user_id', Auth::id())
                             ->latest()
                             ->get();

        // Convert route_path back to string for GSON compatibility
        $history->each(function($run) {
            if ($run->route_path !== null) {
                $run->route_path = json_encode($run->route_path);
            }
        });

        return response()->json($history);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Calculates pace string (mm:ss) based on distance and time.
     */
    private function calculatePace($distance, $time)
    {
        try {
            if (!$distance || $distance <= 0) return "--:--";
            
            $parts = explode(':', $time);
            $seconds = 0;
            if (count($parts) == 3) {
                $seconds = ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
            } else if (count($parts) == 2) {
                $seconds = ($parts[0] * 60) + $parts[1];
            }

            if ($seconds <= 0) return "--:--";

            $paceSecondsPerKm = $seconds / $distance;
            $min = floor($paceSecondsPerKm / 60);
            $sec = round($paceSecondsPerKm % 60);
            
            return $min . ":" . str_pad($sec, 2, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            return "--:--";
        }
    }
}
