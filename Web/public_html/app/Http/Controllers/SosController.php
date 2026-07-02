<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SosSignal;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Auth;

class SosController extends Controller
{
    /**
     * WEB VIEW: Admin Dashboard
     */
    public function index()
    {
        $signals = SosSignal::latest()->get();
        
        $totalSignals = SosSignal::count();
        $pendingSignals = SosSignal::where('status', 'pending')->count();
        $ongoingSignals = SosSignal::where('status', 'ongoing')->count();
        $resolvedSignals = SosSignal::where('status', 'resolved')->count();

        return view('sos.index', compact(
            'signals', 
            'totalSignals', 
            'pendingSignals', 
            'ongoingSignals', 
            'resolvedSignals'
        ));
    }

    /**
     * WEB VIEW: Manual Create Form
     */
    public function create()
    {
        return view('sos.create');
    }

    /**
     * WEB STORE: For manual triggers from Admin Dashboard
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_name' => 'required|string',
            'phone_number' => 'required|string',
            'message' => 'required|string',
            'location_name' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $data['user_identifier'] = 'MANUAL_' . rand(1000, 9999);
        $data['signal_time'] = now();
        $data['status'] = 'pending';

        $signal = SosSignal::create($data);

        // Dispatch to Telegram
        $this->sendTelegramNotification($signal);

        return redirect()->route('sos.index')->with('success', 'Manual SOS triggered and Telegram notified!');
    }

    /**
     * API STORE: Triggered by Android App SOS Button
     */
    public function apiStore(Request $request)
    {
        try {
            $request->validate([
                'user_name' => 'required|string',
                'phone_number' => 'required|string',
                'message' => 'required|string', 
                'location_name' => 'required|string',
                'user_identifier' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $signal = SosSignal::create([
                'user_name'       => $request->user_name,
                'user_identifier' => $request->user_identifier,
                'phone_number'    => $request->phone_number,
                'message'         => $request->message,
                'location_name'   => $request->location_name,
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
                'status'          => 'pending',
                'signal_time'     => now(),
            ]);

            // Attempt Telegram Dispatch
            $this->sendTelegramNotification($signal);

            return response()->json([
                'status'  => 'success',
                'message' => 'SOS dispatched to Community & Telegram!',
                'data'    => $signal
            ], 201);

        } catch (\Exception $e) {
            Log::error('SOS API Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🛠️ TELEGRAM WEBHOOK: Handles Both "Respond" and "Resolve" button clicks
     */
    public function handleWebhook(Request $request)
    {
        $callbackQuery = $request->input('callback_query');

        if (!$callbackQuery) {
            return response()->json(['status' => 'ignored']);
        }

        $callbackData = $callbackQuery['data']; 
        $messageId = $callbackQuery['message']['message_id'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $responderName = $callbackQuery['from']['first_name'] ?? 'A rescuer';

        // --- CASE 1: RESCUER CLICKS "I AM RESPONDING" ---
        if (str_starts_with($callbackData, 'respond_')) {
            $sosId = str_replace('respond_', '', $callbackData);
            $signal = SosSignal::find($sosId);

            if ($signal) {
                // 1. Update DB to Ongoing
                $signal->update(['status' => 'ongoing']);

                // 2. Edit Telegram Message: Add Responder Name & New "Resolve" Button
                $originalText = $callbackQuery['message']['text'];
                $updatedText = $originalText . "\n\n✅ <b>RESCUER ASSIGNED:</b> {$responderName} is on the way!";

                $buttons = [
                    'inline_keyboard' => [[
                        ['text' => '🏁 Mark as Resolved', 'callback_data' => 'resolve_' . $sosId]
                    ]]
                ];

                Http::withoutVerifying()->post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/editMessageText", [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => $updatedText,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode($buttons)
                ]);

                // 3. Confirm to the responder
                Http::withoutVerifying()->post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/answerCallbackQuery", [
                    'callback_query_id' => $callbackQuery['id'],
                    'text' => "You are now assigned. Safe travels!",
                ]);
            }
        }

        // --- CASE 2: RESCUER CLICKS "MARK AS RESOLVED" ---
        if (str_starts_with($callbackData, 'resolve_')) {
            $sosId = str_replace('resolve_', '', $callbackData);
            $signal = SosSignal::find($sosId);

            if ($signal) {
                // 1. Update DB to Resolved
                $signal->update(['status' => 'resolved']);

                // 2. Final Message Update: Remove all buttons
                $currentText = $callbackQuery['message']['text'];
                $finalText = $currentText . "\n\n🏁 <b>STATUS: RESOLVED</b>\nClosed by {$responderName}.";

                Http::withoutVerifying()->post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/editMessageText", [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => $finalText,
                    'parse_mode' => 'HTML'
                    // No reply_markup means buttons are deleted
                ]);

                // 3. Confirm alert
                Http::withoutVerifying()->post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/answerCallbackQuery", [
                    'callback_query_id' => $callbackQuery['id'],
                    'text' => "Case marked as Resolved. Thank you!",
                    'show_alert' => true
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * PRIVATE HELPER: Initial notification to Telegram
     */
    private function sendTelegramNotification($signal)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $destination = $this->resolveTelegramDestination($signal);
        $chatId = $destination['chat_id'];

        if (!$token || !$chatId) return;

        $text = "🚨 <b>EMERGENCY SOS ALERT</b> 🚨\n\n";
        $text .= "📡 <b>Dispatch Area:</b> " . htmlspecialchars($destination['name']) . "\n";
        $text .= "👤 <b>User:</b> " . htmlspecialchars($signal->user_name) . "\n";
        $text .= "⚠️ <b>Type:</b> " . htmlspecialchars($signal->message) . "\n";
        $text .= "📞 <b>Emergency Contact:</b> " . htmlspecialchars($signal->phone_number) . "\n";
        $text .= "📍 <b>Location:</b> " . htmlspecialchars($signal->location_name) . "\n";
        $text .= "🧭 <b>GPS:</b> " . $signal->latitude . ", " . $signal->longitude . "\n";
        $text .= "⏰ <b>Time:</b> " . $signal->signal_time->format('Y-m-d H:i:s') . "\n";
        $text .= "🆘 <b>EMERGENCY CALL:</b> 999\n\n";
        $text .= "🔗 <a href='https://www.google.com/maps?q={$signal->latitude},{$signal->longitude}'>View on Google Maps</a>";

        $buttons = [
            'inline_keyboard' => [[
                ['text' => '✅ I am Responding', 'callback_data' => 'respond_' . $signal->id]
            ]]
        ];

        try {
            Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($buttons)
            ]);
        } catch (\Exception $e) {
            Log::error('Telegram Transport Failed: ' . $e->getMessage());
        }
    }

    /**
     * Choose the Telegram group/channel from the SOS GPS coordinates.
     */
    private function resolveTelegramDestination($signal)
    {
        $latitude = (float) $signal->latitude;
        $longitude = (float) $signal->longitude;

        $nearestArea = null;
        $nearestDistance = null;

        foreach (config('telegram.areas', []) as $area) {
            $chatId = $area['chat_id'] ?? null;

            if (!$chatId || !isset($area['lat'], $area['lng'])) {
                continue;
            }

            $distance = $this->distanceKm($latitude, $longitude, (float) $area['lat'], (float) $area['lng']);

            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestArea = [
                    'name' => $area['name'],
                    'chat_id' => $chatId,
                    'distance_km' => round($distance, 2),
                ];
            }
        }

        if ($nearestArea) {
            return $nearestArea;
        }

        return [
            'name' => 'General Dispatch',
            'chat_id' => config('telegram.fallback_chat_id'),
        ];
    }

    /**
     * Haversine distance between two GPS coordinates.
     */
    private function distanceKm($fromLat, $fromLng, $toLat, $toLng)
    {
        $earthRadiusKm = 6371;

        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);

        $a = sin($latDelta / 2) * sin($latDelta / 2)
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat))
            * sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * WEB DASHBOARD: Update Status manually
     */
    public function updateStatus(Request $request, $id)
    {
        $signal = SosSignal::findOrFail($id);
        $request->validate(['status' => 'required|in:pending,ongoing,resolved']);
        $signal->update(['status' => $request->status]);
        return back()->with('success', 'Status updated!');
    }

    /**
     * WEB DASHBOARD: Delete Record
     */
    public function destroy($id)
    {
        $signal = SosSignal::findOrFail($id);
        $signal->delete();
        return redirect()->route('sos.index')->with('success', 'Deleted.');
    }
}
