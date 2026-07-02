<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage; 
use App\Services\RunnerTierEngine;
use App\Services\TelegramEventNotifier;

class EventController extends Controller
{
    public function __construct(
        private RunnerTierEngine $tierEngine,
        private TelegramEventNotifier $telegramEventNotifier
    )
    {
    }

    /**
     * 🛠️ Helper to fix any old URLs with spaces specifically for Android Glide
     */
    private function formatUrls($event) {
        if ($event->logo_path) {
            // Encode spaces to %20 to prevent Android Glide from crashing on older uploads
            $event->logo_path = str_replace(' ', '%20', $event->logo_path);
        }
        return $event;
    }

    // --- ADMIN / PUBLIC INDEX ---
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('user.events');
        }

        // Auto-update past events to completed status
        Event::where('status', 'upcoming')
            ->whereDate('date', '<', now()->toDateString())
            ->update(['status' => 'completed']);

        $events = Event::orderBy('date', 'asc')->get();
        $totalEvents = Event::count();
        $upcomingEvents = Event::where('status', 'upcoming')->count(); 
        $totalParticipants = Event::withCount('users')->get()->sum('users_count') ?? 0;
        
        return view('events.index', compact('events', 'totalEvents', 'upcomingEvents', 'totalParticipants'));
    }

    // --- SHOW EVENT DETAILS ---
    public function show($id)
    {
        $event = Event::with('users')->withCount('users')->findOrFail($id);

        if (Auth::user()->role !== 'admin' && request()->routeIs('events.show')) {
            return redirect()->route('user.events.show', $event->id);
        }

        if (Auth::user()->role !== 'admin') {
            return view('user.events.show', compact('event'));
        }

        if (request()->routeIs('user.events.show')) {
            return redirect()->route('events.show', $event->id);
        }

        return view('events.show', compact('event'));
    }

    // --- USER EVENT DASHBOARD (WEB) ---
    public function userIndex(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('events.index');
        }

        // Auto-update past events to completed status
        Event::where('status', 'upcoming')
            ->whereDate('date', '<', now()->toDateString())
            ->update(['status' => 'completed']);

        $query = Event::with('users')->withCount('users');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('organizer', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('distance')) { $query->where('distance_km', $request->distance); }
        if ($request->filled('month')) { $query->whereMonth('date', $request->month); }
        if ($request->filled('state')) { $query->where('state', $request->state); }
        if ($request->filled('run_type')) { $query->where('run_type', $request->run_type); }
        if ($request->filled('runner_tier')) { $query->where('runner_tier', $request->runner_tier); } 

        $events = $query->latest('date')->get();
        $joinedEvents = Auth::user()->joinedEvents()
            ->where('events.status', 'upcoming')
            ->whereDate('events.date', '>=', now()->toDateString())
            ->latest('date')
            ->get();
        
        return view('user.events', compact('events', 'joinedEvents'));
    }

    // --- JOIN/QUIT LOGIC ---
    public function join($id)
    {
        $event = Event::findOrFail($id);
        $user = Auth::user();
        if ($event->entry_fee <= 0) {
            $user->joinedEvents()->syncWithoutDetaching([$id]);
            return back()->with('success', "Joined: {$event->title}!");
        }
        if (!$user->phone) return redirect()->route('profile.edit')->with('error', 'Phone number required.');
        return view('events.payment-confirm', compact('event', 'user'));
    }

    public function quit($id)
    {
        $event = Event::findOrFail($id);
        Auth::user()->joinedEvents()->detach($id);
        return back()->with('success', "Withdrawn from {$event->title}.");
    }

    // --- TOYYIBPAY PAYMENT LOGIC ---
    public function confirmPayment(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = Auth::user();
        if (!$user->phone) return back()->with('error', 'Phone number required');
        return $this->processToyyibpayPayment($event, $user);
    }

    private function processToyyibpayPayment($event, $user)
    {
        $secretKey = env('TOYYIBPAY_SECRET_KEY') ?? config('services.toyyibpay.key');
        $categoryCode = env('TOYYIBPAY_CATEGORY_CODE') ?? config('services.toyyibpay.category');
        $apiUrl = env('TOYYIBPAY_API_URL') ?? config('services.toyyibpay.api_url');
        
        $billAmount = (int)($event->entry_fee * 100);
        $phoneNumber = preg_replace('/[^0-9]/', '', $user->phone ?? '0');
        
        $paymentData = [
            'userSecretKey' => $secretKey,
            'categoryCode' => $categoryCode,
            'billName' => substr($event->title, 0, 30),
            'billDescription' => substr('Entry fee for ' . $event->title, 0, 100),
            'billPriceSetting' => 1,
            'billPayorInfo' => 0,
            'billAmount' => $billAmount, 
            'billReturnUrl' => route('user.events.paymentStatus'), 
            'billCallbackUrl' => route('user.events.paymentStatus'),
            'billExternalReferenceNo' => 'EVT_' . $event->id . '_' . $user->id,
            'billTo' => $user->name,
            'billEmail' => $user->email,
            'billPhone' => $phoneNumber,
            'noForm' => 1,
            'autoPayment' => 1,
        ];
        
        try {
            $response = Http::withoutVerifying()->timeout(30)->asForm()->post($apiUrl, $paymentData);
            if ($response->successful()) {
                $responseData = $response->json();
                $billCode = $responseData[0]['BillCode'] ?? $responseData['BillCode'] ?? null;
                if ($billCode) return redirect('https://dev.toyyibpay.com/' . $billCode);
                return back()->with('error', 'Payment Error.');
            }
            return back()->with('error', 'Gateway error.');
        } catch (\Exception $e) { return back()->with('error', 'Connection error.'); }
    }

    public function paymentStatus(Request $request)
    {
        if ($request->status_id == 1) { 
            $parts = explode('_', $request->order_id);
            // order_id is like: EVT_{event_id}_{user_id}
            if (count($parts) >= 3) {
                $eventId = $parts[1];
                $userId = $parts[2];
                $user = User::find($userId);
                if ($user) {
                    $user->joinedEvents()->syncWithoutDetaching([$eventId]);
                    
                    return view('events.payment-success');
                }
            }
        }
        
        if (Auth::check()) {
            return redirect()->route('user.events')->with('error', 'Payment failed.');
        }
        return view('events.payment-failed');
    }

    // --- ADMIN CRUD ---
    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('user.events');
        }

        return view('events.create');
    }
    
    // --- 🛠️ ADMIN STORE (APPLIED GROUP LOGIC) ---
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'state' => 'required|string',
            'run_type' => 'required|string',
            'runner_tier' => 'required|in:Beginner,Intermediate,Professional,LOW,MEDIUM,HARD', 
            'entry_fee' => 'required|numeric|min:0',
            'location' => 'required|string',
            'distance_km' => 'required|numeric|min:0.1',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $safeName = str_replace(' ', '_', $file->getClientOriginalName());
            $filename = 'admin_evt_' . time() . '_' . $safeName;
            
            // Use the same base_path logic
            $destinationPath = base_path('app_data/app/public/event-icons');
            
            if (!File::exists($destinationPath)) { File::makeDirectory($destinationPath, 0755, true); }
            $file->move($destinationPath, $filename);
            
            $data['logo_path'] = url('/serve-image?path=event-icons/' . $filename);
        }
        unset($data['logo']);

        $event = Event::create($data + ['organizer' => 'Admin', 'status' => 'upcoming']);
        $this->telegramEventNotifier->notifyCreated($event);

        return redirect()->route('events.index')->with('success', 'Event created!');
    }
    
    public function edit(Event $event)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        return view('events.edit', compact('event'));
    }
    
    // --- 🛠️ ADMIN UPDATE (APPLIED GROUP LOGIC) ---
    public function update(Request $request, Event $event)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'status' => 'required|string',
            'runner_tier' => 'nullable|in:Beginner,Intermediate,Professional,LOW,MEDIUM,HARD', 
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('logo')) {
            // Delete old file
            if ($event->logo_path && str_contains($event->logo_path, 'path=')) {
                $oldPath = last(explode('path=', $event->logo_path));
                // Decode in case of %20 spaces in old URLs
                $oldFile = base_path('app_data/app/public/' . urldecode($oldPath));
                if (File::exists($oldFile)) File::delete($oldFile);
            }

            $file = $request->file('logo');
            $safeName = str_replace(' ', '_', $file->getClientOriginalName());
            $filename = 'upd_evt_' . time() . '_' . $safeName;
            
            $destinationPath = base_path('app_data/app/public/event-icons');
            
            if (!File::exists($destinationPath)) { File::makeDirectory($destinationPath, 0755, true); }
            $file->move($destinationPath, $filename);
            
            $data['logo_path'] = url('/serve-image?path=event-icons/' . $filename);
        }

        $event->update($data);
        return redirect()->route('events.index')->with('success', 'Event updated!');
    }
    
    // --- 🛠️ ADMIN DESTROY ---
    public function destroy(Event $event) 
    { 
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($event->logo_path && str_contains($event->logo_path, 'path=')) {
            $oldPath = last(explode('path=', $event->logo_path));
            $oldFile = base_path('app_data/app/public/' . urldecode($oldPath));
            if (File::exists($oldFile)) File::delete($oldFile);
        }
        $event->delete(); 
        return redirect()->route('events.index'); 
    }

    // --- 🛠️ MOBILE API METHODS (ADDED formatUrls FOR ANDROID FIX) ---
    public function apiIndex(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        // Auto-update past events to completed status
        Event::where('status', 'upcoming')
            ->whereDate('date', '<', now()->toDateString())
            ->update(['status' => 'completed']);

        $userTier = $user->runner_tier ?? 'BEGINNER';

        $query = Event::withCount('users')
            ->where('status', 'upcoming')
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc');

        $events = $query->get();

        $ranks = [
            'BEGINNER' => 1, 'NOVICE' => 1, 'EASY' => 1,
            'INTERMEDIATE' => 2, 'MEDIUM' => 2,
            'PROFESSIONAL' => 3, 'ADVANCED' => 3, 'HARD' => 3, 'ELITE' => 3
        ];
        $userTierKey = strtoupper($userTier);
        $userRank = $ranks[$userTierKey] ?? 1;

        // Fix any old URLs that have spaces so Glide doesn't crash
        $events->transform(function ($event) use ($user, $ranks, $userRank) {
            $event->is_joined = $user->joinedEvents()->where('event_id', $event->id)->exists();
            
            $eventTierKey = strtoupper($event->runner_tier ?? 'BEGINNER');
            $eventRank = $ranks[$eventTierKey] ?? 1;
            $event->recommendation_status = ($eventRank > $userRank) ? 'challenge' : 'recommended';

            return $this->formatUrls($event);
        });

        return response()->json($events, 200);
    }

    public function apiJoin(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id'  => 'required|exists:users,id',
        ]);

        $event = Event::findOrFail($request->event_id);
        $user = User::findOrFail($request->user_id);

        if ($user->joinedEvents()->where('event_id', $event->id)->exists()) {
            return response()->json(['message' => 'Already joined'], 400);
        }

        // Check if there is an entry fee
        if ($event->entry_fee > 0) {
            if (!$user->phone) {
                return response()->json(['message' => 'Phone number is required in your profile to register for paid events.'], 400);
            }

            $secretKey = env('TOYYIBPAY_SECRET_KEY') ?? config('services.toyyibpay.key');
            $categoryCode = env('TOYYIBPAY_CATEGORY_CODE') ?? config('services.toyyibpay.category');
            $apiUrl = env('TOYYIBPAY_API_URL') ?? config('services.toyyibpay.api_url');
            
            $billAmount = (int)($event->entry_fee * 100);
            $phoneNumber = preg_replace('/[^0-9]/', '', $user->phone ?? '0');
            
            $paymentData = [
                'userSecretKey' => $secretKey,
                'categoryCode' => $categoryCode,
                'billName' => substr($event->title, 0, 30),
                'billDescription' => substr('Entry fee for ' . $event->title, 0, 100),
                'billPriceSetting' => 1,
                'billPayorInfo' => 0,
                'billAmount' => $billAmount, 
                'billReturnUrl' => route('user.events.paymentStatus'), 
                'billCallbackUrl' => route('user.events.paymentStatus'),
                'billExternalReferenceNo' => 'EVT_' . $event->id . '_' . $user->id,
                'billTo' => $user->name,
                'billEmail' => $user->email,
                'billPhone' => $phoneNumber,
                'noForm' => 1,
                'autoPayment' => 1,
            ];
            
            try {
                $response = Http::withoutVerifying()->timeout(30)->asForm()->post($apiUrl, $paymentData);
                if ($response->successful()) {
                    $responseData = $response->json();
                    $billCode = $responseData[0]['BillCode'] ?? $responseData['BillCode'] ?? null;
                    if ($billCode) {
                        return response()->json([
                            'status' => 'payment_required',
                            'payment_url' => 'https://dev.toyyibpay.com/' . $billCode,
                            'message' => 'Payment required to join this event.'
                        ], 200);
                    }
                    return response()->json(['message' => 'Payment gateway did not return a bill code.'], 500);
                }
                return response()->json(['message' => 'Payment gateway communication error.'], 500);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Connection error: ' . $e->getMessage()], 500);
            }
        }

        // Free event: join immediately
        $user->joinedEvents()->attach($event->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully joined ' . $event->title,
            'participant_count' => $event->users()->count() + 1
        ], 200);
    }
}
