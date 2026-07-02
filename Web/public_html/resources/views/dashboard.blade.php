@extends('layouts.app')

@section('content')
<!-- Leaflet Map CSS & Markdown Parser -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<style>
    .leaflet-container { background: #0a0a0a !important; }
    .custom-pin {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #modalAiContent h1, #modalAiContent h2, .ai-feedback-content h1, .ai-feedback-content h2 { font-weight: 800; color: white; margin-bottom: 0.5rem; margin-top: 0.5rem; }
    #modalAiContent strong, .ai-feedback-content strong { color: #6b6b4b; font-weight: 900; }
    #modalAiContent ul, .ai-feedback-content ul { list-style-type: disc; margin-left: 1.25rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
    #modalAiContent li, .ai-feedback-content li { margin-bottom: 0.25rem; }
    #modalAiContent p, .ai-feedback-content p { margin-bottom: 0.5rem; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 10px; }
</style>

<div class="p-8 bg-[#0a0a0a] min-h-screen space-y-8">
    @php
        $isAdmin = Auth::user()->role === 'admin';
        $eventsRoute = $isAdmin ? 'events.index' : 'user.events';
    @endphp
    
    {{-- 1. WELCOME HEADER --}}
    <div class="flex justify-between items-start">
        <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#6b6b4b] to-[#4a4a3a] flex items-center justify-center overflow-hidden border-2 border-[#6b6b4b]/50 shadow-lg">
                @if(Auth::user()->profile_photo_path)
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                @endif
            </div>
            <div>
                <h1 class="text-4xl font-black text-white">Welcome back, <span class="text-[#6b6b4b]">{{ Auth::user()->name }}</span></h1>
                <p class="text-[#8b8b6b] text-sm mt-2 font-bold uppercase tracking-widest">{{ $isAdmin ? 'System Administrator' : 'Keep pushing your limits' }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[#8b8b6b] text-xs font-black uppercase tracking-widest">{{ now()->format('l, F j, Y') }}</p>
        </div>
    </div>

    {{-- 2. LOCATION STATUS WIDGET --}}
    @if(!$isAdmin)
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] p-6 rounded-2xl flex items-center justify-between shadow-lg">
        <div>
            <h3 class="text-white font-bold text-sm uppercase flex items-center gap-2">
                <i data-lucide="navigation" class="w-4 h-4 text-[#6b6b4b]"></i> Location Status
            </h3>
            <p class="text-[#8b8b6b] text-xs mt-1 font-mono" id="location-status">
                @if(Auth::user()->latitude)
                    <span class="text-emerald-500">● Active</span> ({{ number_format(Auth::user()->latitude, 4) }}, {{ number_format(Auth::user()->longitude, 4) }})
                @else
                    <span class="text-red-500">● Not Set</span> (Update required for Search Nearby)
                @endif
            </p>
        </div>
        <button onclick="getLocation()" class="bg-[#6b6b4b]/20 hover:bg-[#6b6b4b] text-[#6b6b4b] hover:text-white border border-[#6b6b4b] hover:border-transparent px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
            <i data-lucide="map-pin" class="w-4 h-4"></i> Update My Location
        </button>
    </div>
    @endif

    {{-- 3. SUMMARY STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-[#6b6b4b]/20 to-[#1a1a1a] border border-[#6b6b4b]/30 rounded-2xl p-6 hover:border-[#6b6b4b]/60 transition-all">
            <p class="text-[#8b8b6b] text-[10px] uppercase font-bold tracking-widest mb-2">Total KM Run</p>
            <h3 class="text-3xl font-black text-white">{{ number_format($myDistance, 1) }} <span class="text-lg text-[#8b8b6b]">km</span></h3>
            <p class="text-[#6b6b4b] text-[10px] font-black uppercase tracking-widest mt-2">{{ $myRuns }} total runs</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500/20 to-[#1a1a1a] border border-blue-500/30 rounded-2xl p-6 hover:border-blue-500/60 transition-all">
            <p class="text-[#8b8b6b] text-[10px] uppercase font-bold tracking-widest mb-2">Next Event</p>
            @if($upcomingEvents->first())
                <h3 class="text-sm font-bold text-white truncate">{{ $upcomingEvents->first()->title }}</h3>
                <p class="text-blue-400 text-[10px] font-black uppercase mt-2 tracking-widest">{{ \Carbon\Carbon::parse($upcomingEvents->first()->date)->format('M d, Y') }}</p>
            @else
                <h3 class="text-sm font-bold text-white">No upcoming events</h3>
            @endif
        </div>

        <div class="bg-gradient-to-br from-purple-500/20 to-[#1a1a1a] border border-purple-500/30 rounded-2xl p-6 hover:border-purple-500/60 transition-all">
            <p class="text-[#8b8b6b] text-[10px] uppercase font-bold tracking-widest mb-2">Active Groups</p>
            <h3 class="text-3xl font-black text-white">{{ $activeGroups }}</h3>
        </div>

        @if($isAdmin)
        <div class="bg-gradient-to-br from-emerald-500/20 to-[#1a1a1a] border border-emerald-500/30 rounded-2xl p-6 hover:border-emerald-500/60 transition-all">
            <p class="text-[#8b8b6b] text-[10px] uppercase font-bold tracking-widest mb-2">Active Runners</p>
            <h3 class="text-3xl font-black text-white">{{ $activeUsers }}</h3>
        </div>
        @endif
    </div>

    {{-- 4. TWO-COLUMN GRID LAYOUT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- LEFT COLUMN: Analytics & Actions & Events (2/3 width) --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- Analytics Goal & Chart Widget --}}
            @if(Auth::user()->role === 'admin')
                <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-8 shadow-2xl">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-[#2a2a2a]/60 pb-4 mb-6 gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-white uppercase tracking-tight flex items-center gap-2">
                                <i data-lucide="calendar" class="w-5 h-5 text-[#6b6b4b]"></i>
                                Monthly Tracker
                            </h2>
                            <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest mt-1">Activity from other runners for the current month</p>
                        </div>
                        <span class="bg-[#6b6b4b]/20 text-[#6b6b4b] border border-[#6b6b4b]/30 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest self-start sm:self-center">
                            {{ now()->format('F Y') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Monthly Runs --}}
                        <div class="bg-[#0d0d0d] border border-[#2a2a2a] p-6 rounded-xl flex items-center justify-between hover:border-[#6b6b4b]/40 transition-all">
                            <div>
                                <p class="text-[#8b8b6b] text-[10px] uppercase font-bold tracking-widest mb-1">Runs This Month</p>
                                <h3 class="text-4xl font-black text-white">{{ $monthlyRuns }} <span class="text-base font-bold text-[#8b8b6b] uppercase">runs</span></h3>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-[#6b6b4b]/10 flex items-center justify-center border border-[#6b6b4b]/20 text-[#6b6b4b]">
                                <i data-lucide="activity" class="w-6 h-6"></i>
                            </div>
                        </div>

                        {{-- Monthly Distance --}}
                        <div class="bg-[#0d0d0d] border border-[#2a2a2a] p-6 rounded-xl flex items-center justify-between hover:border-[#6b6b4b]/40 transition-all">
                            <div>
                                <p class="text-[#8b8b6b] text-[10px] uppercase font-bold tracking-widest mb-1">Distance This Month</p>
                                <h3 class="text-4xl font-black text-white">{{ number_format($monthlyDistance, 1) }} <span class="text-base font-bold text-[#8b8b6b] uppercase">km</span></h3>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-[#6b6b4b]/10 flex items-center justify-center border border-[#6b6b4b]/20 text-[#6b6b4b]">
                                <i data-lucide="navigation" class="w-6 h-6"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between text-[10px] font-black text-[#4a4a4a] uppercase tracking-widest border-t border-[#2a2a2a]/30 pt-4">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-[#6b6b4b]"></i>
                            Auto-Resets Monthly
                        </span>
                        <span>
                            Next Reset: {{ now()->addMonth()->startOfMonth()->format('F j, Y') }}
                        </span>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Monthly Goal --}}
                    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-8 flex flex-col items-center justify-center shadow-2xl">
                        <h2 class="text-xl font-bold text-white mb-4 uppercase tracking-tight">Monthly Goal</h2>
                        <div class="relative flex items-center justify-center mb-4">
                            <svg width="120" height="120" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="52" stroke="#232323" stroke-width="12" fill="none" />
                                <circle cx="60" cy="60" r="52" stroke="#6b6b4b" stroke-width="12" fill="none"
                                    stroke-dasharray="326.72" stroke-dashoffset="{{ 326.72 - (326.72 * $monthlyProgress / 100) }}"
                                    stroke-linecap="round" style="filter: drop-shadow(0 0 8px #6b6b4b88); transition: stroke-dashoffset 0.6s;" />
                                <text x="50%" y="54%" text-anchor="middle" fill="#fff" font-size="2.2rem" font-weight="bold" dy=".3em">{{ $monthlyProgress }}%</text>
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="text-[#8b8b6b] text-sm mb-1 font-bold">{{ number_format($monthlyDistance, 1) }} / {{ $monthlyGoal->goal_km }} km</p>
                            <p class="text-[10px] text-[#4a4a4a] uppercase tracking-widest">Progress this month</p>
                        </div>
                    </div>

                    {{-- Weekly Chart --}}
                    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-8 shadow-2xl flex flex-col justify-center">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-white uppercase tracking-tight">Weekly Distance</h2>
                            <span class="text-[10px] font-black text-[#8b8b6b] uppercase tracking-widest">Last 7 days</span>
                        </div>
                        @if(array_sum($sevenDayActivity['distances']) > 0)
                            <div class="relative h-[180px]">
                                <canvas id="sevenDayChart"></canvas>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i data-lucide="activity" class="w-16 h-16 text-[#4a4a4a] mx-auto mb-4"></i>
                                <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">No runs recorded this week</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Quick Action Buttons --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <a href="{{ route($eventsRoute) }}" class="bg-[#1a1a1a] hover:bg-[#252525] text-white border border-[#2a2a2a] py-4 px-6 rounded-xl font-bold transition-all text-center uppercase text-xs tracking-widest">Browse Events</a>
                <a href="{{ route('user.groups') }}" class="bg-[#1a1a1a] hover:bg-[#252525] text-white border border-[#2a2a2a] py-4 px-6 rounded-xl font-bold transition-all text-center uppercase text-xs tracking-widest">Join Groups</a>
                <a href="{{ route('user.posts') }}" class="bg-[#1a1a1a] hover:bg-[#252525] text-white border border-[#2a2a2a] py-4 px-6 rounded-xl font-bold transition-all text-center uppercase text-xs tracking-widest">Social Feed</a>
            </div>

            {{-- System Totals (Admin only) --}}
            @if($isAdmin)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] p-8">
                    <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest mb-2">Total System Distance</p>
                    <h3 class="text-5xl font-black text-white">{{ number_format($totalDistance, 2) }} <span class="text-lg text-[#8b8b6b]">km</span></h3>
                </div>
                <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] p-8">
                    <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest mb-2">Total System Runs</p>
                    <h3 class="text-5xl font-black text-white">{{ $totalRuns }}</h3>
                </div>
            </div>
            @endif

            {{-- Upcoming Events --}}
            @if(!$isAdmin)
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-8 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-white uppercase tracking-tight">Upcoming Events</h2>
                    <a href="{{ route('user.events') }}" class="text-[#8b8b6b] hover:text-[#6b6b4b] text-[10px] font-black uppercase tracking-widest">Explore More →</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($upcomingEvents as $event)
                        <div class="bg-[#0d0d0d] border border-[#2a2a2a] p-6 rounded-xl hover:border-[#6b6b4b]/60 transition-all">
                            <span class="text-[9px] font-black bg-[#6b6b4b]/20 text-[#6b6b4b] px-3 py-1 rounded-full uppercase tracking-widest mb-4 inline-block">{{ $event->run_type }}</span>
                            <h3 class="font-bold text-white mb-4 line-clamp-1">{{ $event->title }}</h3>
                            <div class="space-y-2 text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">
                                <p>{{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}</p>
                                <p class="truncate">{{ $event->location }}</p>
                            </div>
                            <a href="{{ route('user.events.show', $event->id) }}" class="block w-full mt-6 text-center bg-[#6b6b4b] text-white py-3 rounded-lg font-black text-[10px] uppercase">Details</a>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <p class="text-[#4a4a4a] font-black uppercase tracking-widest">No events found</p>
                        </div>
                    @endforelse
                </div>
            </div>
            @endif

            {{-- AI Coaching History --}}
            @if(!$isAdmin && $myRunsHistory->count() > 0)
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-8 shadow-2xl mt-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-white uppercase tracking-tight">AI Coaching History</h2>
                        <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest mt-1">Review your past performance evaluations</p>
                    </div>
                    <a href="{{ route('user.runs') }}" class="text-[#8b8b6b] hover:text-[#6b6b4b] text-[10px] font-black uppercase tracking-widest">View All Runs →</a>
                </div>
                
                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1 custom-scrollbar">
                    @foreach($myRunsHistory as $run)
                        <div class="p-6 bg-[#0d0d0d] rounded-xl border border-[#2a2a2a] hover:border-[#6b6b4b]/50 transition-all">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#2a2a2a]/30 pb-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#6b6b4b]/10 flex items-center justify-center border border-[#6b6b4b]/30">
                                        <i data-lucide="activity" class="w-5 h-5 text-[#6b6b4b]"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Run on {{ \Carbon\Carbon::parse($run->date)->format('M d, Y') }}</h4>
                                        <p class="text-[10px] text-[#8b8b6b] uppercase tracking-wider font-semibold">Stats: {{ number_format($run->distance_km, 2) }} km | {{ $run->time }} | {{ $run->pace }} /km</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    @if($run->route_path)
                                        <button data-route="{{ is_array($run->route_path) ? json_encode($run->route_path) : $run->route_path }}"
                                                data-distance="{{ $run->distance_km }}"
                                                data-pace="{{ $run->pace }}"
                                                data-time="{{ $run->time }}"
                                                data-date="{{ $run->created_at->format('M d, Y') }}"
                                                data-runner="{{ $run->user ? $run->user->name : $run->username }}"
                                                data-ai="{{ $run->ai_evaluation }}"
                                                onclick="viewRouteFromButton(this)"
                                                class="text-xs text-[#8b8b6b] hover:text-white bg-[#1a1a1a] hover:bg-[#6b6b4b] border border-[#2a2a2a] hover:border-transparent px-3 py-1.5 rounded-lg font-bold uppercase tracking-wider transition-all flex items-center gap-1">
                                            <i data-lucide="map" class="w-3.5 h-3.5"></i>
                                            View Path
                                        </button>
                                    @endif
                                    
                                    @if($run->ai_evaluation)
                                        <button onclick="toggleAiFeedback('ai-feedback-{{ $run->id }}')" 
                                                class="text-xs text-[#6b6b4b] hover:text-white bg-transparent hover:bg-[#6b6b4b] border border-[#6b6b4b] hover:border-transparent px-3 py-1.5 rounded-lg font-bold uppercase tracking-wider transition-all flex items-center gap-1">
                                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" id="icon-ai-feedback-{{ $run->id }}"></i>
                                            Coach Advice
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            @if($run->ai_evaluation)
                                <div id="ai-feedback-{{ $run->id }}" class="hidden pt-2 text-xs text-[#b0b0a0] leading-relaxed mt-2 animate-in fade-in duration-200">
                                    <div class="bg-[#151515] p-5 rounded-lg border border-[#2a2a2a] ai-feedback-content">
                                        {!! \Illuminate\Support\Str::markdown($run->ai_evaluation) !!}
                                    </div>
                                </div>
                            @else
                                <div class="text-[11px] text-[#4a4a4a] italic uppercase tracking-wider font-semibold">No AI Coaching evaluation was generated for this run.</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT COLUMN: Sidebar Social Run Feed (1/3 width) --}}
        <div class="space-y-8">
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-8 shadow-2xl flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-white uppercase tracking-tight">Recent Activity</h2>
                        <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest mt-1">Updates from followed runners</p>
                    </div>
                </div>
                
                <div class="space-y-4 max-h-[600px] overflow-y-auto pr-1">
                    @forelse($recentActivity as $run)
                        <div class="flex gap-4 p-4 bg-[#0d0d0d] rounded-xl border border-[#2a2a2a] hover:border-[#6b6b4b]/50 hover:bg-[#151515] transition-all">
                            <div class="w-10 h-10 rounded-full bg-[#6b6b4b]/20 flex-shrink-0 flex items-center justify-center overflow-hidden border border-[#6b6b4b]/50">
                                @if($run->user && $run->user->profile_photo_path)
                                    <img src="{{ $run->user->profile_photo_url }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs font-bold text-white uppercase">{{ substr($run->user ? $run->user->name : $run->username, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="flex-grow">
                                <div class="flex items-baseline justify-between mb-1">
                                    <h4 class="font-bold text-white text-sm">{{ $run->user ? $run->user->name : $run->username }}</h4>
                                    <span class="text-[9px] text-[#8b8b6b] font-bold uppercase tracking-widest">{{ $run->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-[#b0b0a0]">
                                    🏃‍♂️ Completed a <span class="text-white font-bold">{{ number_format($run->distance_km, 2) }} km</span> run 
                                    at a pace of <span class="text-white font-mono">{{ $run->pace }} /km</span>.
                                </p>
                                
                                @if($run->route_path)
                                    <button data-route="{{ is_array($run->route_path) ? json_encode($run->route_path) : $run->route_path }}"
                                            data-distance="{{ $run->distance_km }}"
                                            data-pace="{{ $run->pace }}"
                                            data-time="{{ $run->time }}"
                                            data-date="{{ $run->created_at->format('M d, Y') }}"
                                            data-runner="{{ $run->user ? $run->user->name : $run->username }}"
                                            data-ai="{{ $run->ai_evaluation }}"
                                            onclick="viewRouteFromButton(this)"
                                            class="mt-2 text-[#6b6b4b] hover:text-white hover:bg-[#6b6b4b] border border-[#6b6b4b] hover:border-transparent px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">
                                        View Route
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-[#4a4a4a] font-black uppercase tracking-widest">No runs recorded recently</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LEAFLET ROUTE MODAL --}}
<div id="routeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden">
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] w-full max-w-4xl rounded-2xl overflow-hidden shadow-2xl flex flex-col h-[80vh] m-4">
        {{-- Modal Header --}}
        <div class="px-6 py-4 bg-[#111111] border-b border-[#2a2a2a] flex items-center justify-between">
            <div>
                <h3 id="modalRunnerName" class="text-lg font-black text-white uppercase">RUN PATH</h3>
                <p id="modalRunDate" class="text-xs text-[#8b8b6b] font-bold uppercase tracking-wider mt-0.5">Date</p>
            </div>
            <button onclick="closeRouteModal()" class="text-[#8b8b6b] hover:text-white transition-colors focus:outline-none">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        {{-- Modal Content Grid --}}
        <div class="flex-grow flex flex-col md:flex-row min-h-0">
            {{-- Map Area --}}
            <div class="flex-grow h-64 md:h-full relative bg-[#0a0a0a]">
                <div id="route-map" class="w-full h-full"></div>
            </div>
            
            {{-- Metrics Sidebar --}}
            <div class="w-full md:w-96 bg-[#111111] border-t md:border-t-0 md:border-l border-[#2a2a2a] p-6 flex flex-col justify-between overflow-y-auto">
                <div class="space-y-6">
                    <h4 class="text-xs font-black text-[#8b8b6b] uppercase tracking-widest border-b border-[#2a2a2a] pb-2">Run Analysis</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-[#1a1a1a] border border-[#2a2a2a] p-4 rounded-xl">
                            <p class="text-[9px] text-[#8b8b6b] uppercase tracking-widest mb-1 font-bold">Distance</p>
                            <p class="text-xl font-black text-white"><span id="modalDistance">0.00</span> <span class="text-xs text-[#8b8b6b]">km</span></p>
                        </div>
                        <div class="bg-[#1a1a1a] border border-[#2a2a2a] p-4 rounded-xl">
                            <p class="text-[9px] text-[#8b8b6b] uppercase tracking-widest mb-1 font-bold">Avg Pace</p>
                            <p class="text-xl font-black text-[#6b6b4b]"><span id="modalPace">0:00</span> <span class="text-[10px] text-[#8b8b6b]">/km</span></p>
                        </div>
                        <div class="bg-[#1a1a1a] border border-[#2a2a2a] p-4 rounded-xl col-span-2">
                            <p class="text-[9px] text-[#8b8b6b] uppercase tracking-widest mb-1 font-bold">Duration</p>
                            <p class="text-xl font-black text-white" id="modalTime">00:00:00</p>
                        </div>
                    </div>

                    {{-- AI Suggestion Section inside Modal --}}
                    <div id="modalAiSection" class="border-t border-[#2a2a2a] pt-4 mt-4 hidden">
                        <h4 class="text-xs font-black text-[#8b8b6b] uppercase tracking-widest mb-2 flex items-center gap-1.5">
                            <i data-lucide="bot" class="w-4 h-4 text-[#6b6b4b]"></i>
                            AI Coaching Suggestion
                        </h4>
                        <div id="modalAiContent" class="bg-[#1a1a1a] border border-[#2a2a2a] p-4 rounded-xl text-xs text-[#b0b0a0] leading-relaxed space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                            {{-- Content will be dynamically inserted here --}}
                        </div>
                    </div>
                </div>
                
                <button onclick="closeRouteModal()" class="w-full mt-6 bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-colors shrink-0">
                    Close Map
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 9. SCRIPTS (Charts & Location) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // 1. Chart JS Logic
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('sevenDayChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($sevenDayActivity['days']),
                    datasets: [{
                        label: 'Distance (KM)',
                        data: @json($sevenDayActivity['distances']),
                        borderColor: '#6b6b4b',
                        backgroundColor: 'rgba(107, 107, 75, 0.15)',
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#6b6b4b',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: '#2a2a2a' }, ticks: { color: '#8b8b6b', font: { size: 10, weight: 'bold' } } },
                        y: { grid: { color: '#2a2a2a' }, ticks: { color: '#8b8b6b', font: { size: 10, weight: 'bold' } }, beginAtZero: true }
                    }
                }
            });
        }
    });

    // 2. Location Update Logic
    function getLocation() {
        const status = document.getElementById('location-status');
        
        if (!navigator.geolocation) {
            status.innerHTML = "<span class='text-red-500'>Geolocation is not supported by your browser</span>";
            return;
        }

        status.innerHTML = "<span class='text-[#6b6b4b] animate-pulse'>Locating...</span>";

        navigator.geolocation.getCurrentPosition(success, error);
    }

    function success(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.getElementById('location-status').innerHTML = `<span class='text-[#6b6b4b]'>Found you! Saving...</span>`;

        fetch('{{ route('profile.update_location') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ latitude: lat, longitude: lng })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('location-status').innerHTML = `<span class='text-emerald-500'>● Active</span> (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
            alert("Location updated! You can now use Search Nearby.");
        })
        .catch(err => {
            console.error(err);
            document.getElementById('location-status').innerHTML = "<span class='text-red-500'>Error saving location.</span>";
        });
    }

    function error() {
        document.getElementById('location-status').innerHTML = "<span class='text-red-500'>Unable to retrieve your location</span>";
        alert("Please allow location access in your browser settings.");
    }

    // 3. Leaflet Map Modal Logic
    let routeMap;
    let routeLayer;
    let routeMarkers = [];

    function viewRouteFromButton(btn) {
        const routeData = btn.getAttribute('data-route');
        const distance = btn.getAttribute('data-distance');
        const pace = btn.getAttribute('data-pace');
        const time = btn.getAttribute('data-time');
        const date = btn.getAttribute('data-date');
        const runnerName = btn.getAttribute('data-runner');
        const aiEvaluation = btn.getAttribute('data-ai');

        let coordinates = null;
        if (routeData) {
            try {
                let temp = routeData;
                while (typeof temp === 'string') {
                    temp = JSON.parse(temp);
                }
                coordinates = temp;
            } catch (e) {
                console.error("Error parsing route coordinates:", e);
            }
        }

        viewRoute(coordinates, distance, pace, time, date, runnerName, aiEvaluation);
    }

    function viewRoute(coordinates, distance, pace, time, date, runnerName, aiEvaluation = null) {
        // Show modal
        const modal = document.getElementById('routeModal');
        modal.classList.remove('hidden');
        
        // Set info
        document.getElementById('modalRunnerName').innerText = `${runnerName.toUpperCase()}'S RUN`;
        document.getElementById('modalRunDate').innerText = date;
        document.getElementById('modalDistance').innerText = parseFloat(distance).toFixed(2);
        document.getElementById('modalPace').innerText = pace;
        document.getElementById('modalTime').innerText = time;

        // Populate AI Coaching Section
        const aiSection = document.getElementById('modalAiSection');
        const aiContent = document.getElementById('modalAiContent');
        if (aiEvaluation) {
            aiSection.classList.remove('hidden');
            aiContent.innerHTML = marked.parse(aiEvaluation);
        } else {
            aiSection.classList.add('hidden');
            aiContent.innerHTML = '';
        }

        // Initialize map if not already done
        setTimeout(() => {
            if (!routeMap) {
                routeMap = L.map('route-map', {
                    zoomControl: true,
                    attributionControl: false
                });
                
                // Dark mode tiles
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20
                }).addTo(routeMap);
            } else {
                routeMap.invalidateSize();
                if (routeLayer) {
                    routeMap.removeLayer(routeLayer);
                }
            }

            // Clear previous markers
            routeMarkers.forEach(marker => routeMap.removeLayer(marker));
            routeMarkers = [];

            // Draw Polyline
            if (coordinates && coordinates.length > 0) {
                const latLngs = coordinates.map(pt => {
                    if (Array.isArray(pt)) {
                        return [pt[0], pt[1]];
                    } else if (pt && typeof pt === 'object') {
                        const lat = pt.latitude || pt.lat || pt.y;
                        const lng = pt.longitude || pt.lng || pt.x;
                        return [lat, lng];
                    }
                    return null;
                }).filter(pt => pt !== null);
                
                if (latLngs.length > 0) {
                    routeLayer = L.polyline(latLngs, {
                        color: '#6b6b4b',
                        weight: 5,
                        opacity: 0.9,
                        lineJoin: 'round'
                    }).addTo(routeMap);

                    // Fit bounds
                    routeMap.fitBounds(routeLayer.getBounds(), { padding: [30, 30] });

                    // Add start & end markers
                    const startPt = latLngs[0];
                    const endPt = latLngs[latLngs.length - 1];

                    const startIcon = L.divIcon({
                        html: '<div class="w-3 h-3 bg-emerald-500 rounded-full border border-white"></div>',
                        className: 'custom-pin',
                        iconSize: [12, 12]
                    });
                    const endIcon = L.divIcon({
                        html: '<div class="w-3 h-3 bg-red-500 rounded-full border border-white"></div>',
                        className: 'custom-pin',
                        iconSize: [12, 12]
                    });

                    const startMarker = L.marker(startPt, { icon: startIcon }).addTo(routeMap);
                    const endMarker = L.marker(endPt, { icon: endIcon }).addTo(routeMap);
                    routeMarkers.push(startMarker, endMarker);
                }
            }
        }, 150);
    }

    function closeRouteModal() {
        document.getElementById('routeModal').classList.add('hidden');
    }

    // Toggle AI Advice collapse/expand
    function toggleAiFeedback(id) {
        const element = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        
        if (element.classList.contains('hidden')) {
            element.classList.remove('hidden');
            if (icon) {
                icon.style.transform = 'rotate(180deg)';
            }
        } else {
            element.classList.add('hidden');
            if (icon) {
                icon.style.transform = 'rotate(0deg)';
            }
        }
    }
</script>
@endsection
