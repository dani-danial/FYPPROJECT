<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CoachAIController extends Controller
{
    /**
     * Display the Coach Chat UI.
     */
    public function index()
    {
        return view('user.coach.index');
    }

    /**
     * Handle the Standard AI Chat request (Detailed Coaching).
     */
    public function chat(Request $request)
    {
        // 1. Validate incoming message
        $request->validate(['message' => 'required|string']);

        $apiKey = env('GEMINI_API_KEY');
        
        // 🛠️ Using Gemini 3.1 Flash Lite for higher RPD (500)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key=" . $apiKey;

        // 2. Fetch User Data for Personalization
        $user = Auth::user();
        $weight = $user->weight_kg ?? '70'; 
        $height = $user->height_cm ?? '170';
        $pace = $user->base_pace_min_km ?? '6:00';
        
        $stats = "Weight: {$weight}kg, Height: {$height}cm, Base Pace: {$pace} min/km.";

        // 🛠️ 3. Payload for Detailed Chat
        $payload = [
            "system_instruction" => [
                "parts" => [
                    ["text" => "Role: Coach Flash, an elite and motivating trainer. Bio: You are training " . $user->name . ". Stats: " . $stats . " Rules: Use these stats to calculate finish times or suggest training intensity. Keep your advice professional, encouraging, and focused on running safety. If you provide a list or training plan, ensure it is fully completed."]
                ]
            ],
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        ["text" => $request->message]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.8,
                "maxOutputTokens" => 2000, 
            ]
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $payload);

            if ($response->failed()) {
                Log::error("Gemini 3 API Chat Error: " . $response->body());
                return response()->json(['reply' => "Coach Flash is checking the track. (Status: " . $response->status() . ")"]);
            }

            $data = $response->json();
            $aiMessage = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Coach Flash is catching his breath.";

            return response()->json(['reply' => $aiMessage]);

        } catch (\Exception $e) {
            Log::error("Coach AI Exception: " . $e->getMessage());
            return response()->json(['reply' => "Connection lost with Coach Flash."], 500);
        }
    }

    /**
     * 🛠️ NEW: Proactive Support Shout-out (Short & Fast)
     * Triggered by Android when the user is running slower than target pace.
     */
    public function getQuickSupport(Request $request)
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key=" . $apiKey;

        $user = Auth::user();
        
        $payload = [
            "system_instruction" => [
                "parts" => [
                    ["text" => "Role: Coach Flash. Situation: You are watching " . $user->name . " run via GPS. They are currently slowing down below their target pace. Goal: Give a sharp, high-energy motivational shout-out in 10 words or less. Be witty, elite, and encouraging. Use their name."]
                ]
            ],
            "contents" => [
                [
                    "role" => "user", 
                    "parts" => [["text" => "I'm losing my pace, Coach!"]]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.9, // Higher temperature for more varied shout-outs
                "maxOutputTokens" => 60
            ]
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $payload);
            
            $data = $response->json();
            $msg = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Keep those legs moving, " . $user->name . "!";

            return response()->json(['reply' => trim($msg)]);
        } catch (\Exception $e) {
            Log::error("Coach Quick Support Error: " . $e->getMessage());
            return response()->json(['reply' => "Don't stop now, " . $user->name . "!"], 200);
        }
    }
}