<?php

namespace App\Http\Controllers;

use App\Services\RunnerTierEngine;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RunnerEngineController extends Controller
{
    public function __construct(private RunnerTierEngine $tierEngine)
    {
    }

    public function classify(Request $request)
    {
        try {
            Log::info('Onboarding Request received:', $request->all());

            $validated = $request->validate([
                'distance_score' => 'required|integer|min:1|max:3',
                'type_score' => 'required|integer|min:1|max:3',
                'frequency_score' => 'required|integer|min:1|max:3',
                'experience_score' => 'nullable|integer|min:1|max:4',
                'pace_score' => 'nullable|integer|min:1|max:3',
                'weekly_distance_score' => 'nullable|integer|min:1|max:4',
                'recovery_score' => 'nullable|integer|min:1|max:3',
                'event_score' => 'nullable|integer|min:1|max:4',
                'age' => 'nullable|integer|min:10|max:100',
                'gender' => 'nullable|string|max:30',
                'weekly_distance_km' => 'nullable|numeric|min:0|max:300',
                'weekly_frequency' => 'nullable|integer|min:0|max:14',
                'average_pace_min_km' => 'nullable|numeric|min:2|max:20',
                'injury_history' => 'nullable|string|in:none,minor,occasional,recovering,frequent,chronic,high',
                'running_goal' => 'nullable|string|max:80',
                'goal_type' => 'nullable|string|max:80',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User session not found. Please re-login.',
                ], 401);
            }

            $result = $this->tierEngine->classifyFromProfile($user, $validated);

            $user->runner_tier = $result['tier'];
            if ($request->filled('age')) $user->age = $request->age;
            if ($request->filled('gender')) $user->gender = $request->gender;
            if ($request->filled('goal_type')) $user->running_goal = $request->goal_type;
            elseif ($request->filled('running_goal')) $user->running_goal = $request->running_goal;
            if ($request->filled('average_pace_min_km')) $user->base_pace_min_km = $request->average_pace_min_km;
            $user->save();

            return response()->json([
                'status' => 'success',
                'category' => $result['tier'],
                'label' => $result['label'],
                'total_score' => $result['score'],
                'source' => $result['source'],
            ], 200);
        } catch (Exception $e) {
            Log::error('Classification Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
