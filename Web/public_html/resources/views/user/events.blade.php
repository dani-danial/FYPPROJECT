@extends('layouts.app')

@section('content')
<div class="bg-[#0a0a0a] min-h-screen" x-data="{ showDetailModal: false, selectedEvent: {}, activeTab: 'upcoming' }">
    
    {{-- 1. HERO HEADER --}}
    <div class="bg-gradient-to-b from-[#1a1a1a] to-[#0a0a0a] border-b border-[#2a2a2a] sticky top-0 z-40 backdrop-blur-sm">
        <div class="p-8 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-4xl md:text-5xl font-black text-white uppercase tracking-tight">Running Events</h1>
                    <p class="text-[#8b8b6b] text-sm font-bold uppercase tracking-widest mt-2">Discover & Join Global Running Community</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. ALERTS --}}
    @if(session('error'))
        <div class="bg-red-500/10 border-b border-red-500/30 text-red-500 p-4 flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span class="text-xs font-bold uppercase tracking-widest">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-500/10 border-b border-emerald-500/30 text-emerald-400 p-4 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span class="text-xs font-bold uppercase tracking-widest text-emerald-400">{{ session('success') }}</span>
        </div>
    @endif

    <div class="p-8 max-w-7xl mx-auto space-y-8">
        
        {{-- 🛠️ MOVED LOGIC TO TOP: Define filters here so we can use the counts in the stats banner --}}
        @php
            // Filter events for upcoming (future or today)
            $upcomingEvents = $events->filter(function($event) {
                return \Carbon\Carbon::parse($event->date)->isFuture() || \Carbon\Carbon::parse($event->date)->isToday();
            });
            
            // Filter events for completed (past)
            $completedEvents = $events->filter(function($event) {
                return \Carbon\Carbon::parse($event->date)->isPast() && !\Carbon\Carbon::parse($event->date)->isToday();
            });
        @endphp

        {{-- 3. MY REGISTERED RUNS SECTION (Premium Highlight) --}}
        @if($joinedEvents->count() > 0)
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 bg-[#6b6b4b] rounded-full">
                    <i data-lucide="check-circle" class="w-5 h-5 text-white"></i>
                </div>
                <h2 class="text-xl font-black text-white uppercase tracking-wider">My Upcoming Runs</h2>
                <span class="ml-auto text-xs font-bold bg-[#6b6b4b]/20 text-[#6b6b4b] px-3 py-1 rounded-full">{{ $joinedEvents->count() }} registered</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($joinedEvents as $registered)
                <div class="group bg-gradient-to-br from-[#1a1a1a] via-[#151515] to-[#0d0d0d] border border-[#6b6b4b]/30 rounded-[2.5rem] p-8 hover:border-[#6b6b4b] hover:shadow-2xl hover:shadow-[#6b6b4b]/20 transition-all duration-300 overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#6b6b4b] to-transparent"></div>
                    
                    <div class="flex justify-between items-start mb-6">
                        <span class="text-[10px] font-black bg-[#6b6b4b] text-[#0a0a0a] px-4 py-2 rounded-full uppercase tracking-widest">Registered</span>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                            <span class="text-[10px] font-bold text-emerald-400">Active</span>
                        </div>
                    </div>

                    <h3 class="text-lg font-black text-white uppercase truncate group-hover:text-[#6b6b4b] transition-colors mb-1">{{ $registered->title }}</h3>
                    <p class="text-[#6b6b4b] text-[10px] font-bold uppercase tracking-widest mb-6">{{ $registered->distance_km }} KM • {{ $registered->run_type }}</p>

                    <div class="space-y-3 mb-8 pb-6 border-b border-[#2a2a2a]">
                        <div class="flex items-center gap-3 text-[#b0b0a0] text-xs">
                            <i data-lucide="calendar" class="w-4 h-4 text-[#6b6b4b] flex-shrink-0"></i>
                            <span>{{ \Carbon\Carbon::parse($registered->date)->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-[#b0b0a0] text-xs">
                            <i data-lucide="clock" class="w-4 h-4 text-[#6b6b4b] flex-shrink-0"></i>
                            <span>{{ \Carbon\Carbon::parse($registered->time)->format('h:i A') }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="showDetailModal = true; selectedEvent = {{ $registered->toJson() }}" 
                                class="flex-1 py-3 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                            Details
                        </button>
                        <form action="{{ route('user.events.quit', $registered->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl text-[10px] font-black uppercase tracking-widest border border-red-500/20 transition-all">
                                Withdraw
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-[#2a2a2a]"></div>
        @endif

        {{-- 4. STATISTICS BANNER --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-[#6b6b4b] to-[#5a5a3f] rounded-[2.5rem] p-8 shadow-lg border border-[#7b7b5b]/20">
                <p class="text-[#d0d0c0] text-[10px] font-black uppercase tracking-widest mb-3">Available Events</p>
                {{-- 🛠️ FIXED: Use $upcomingEvents->count() instead of $events->count() --}}
                <p class="text-5xl font-black text-white tracking-tighter">{{ $upcomingEvents->count() }}</p>
                <p class="text-[#d0d0c0] text-[10px] mt-3">Ready to join</p>
            </div>
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-8">
                <p class="text-[#4a4a4a] text-[10px] font-black uppercase tracking-widest mb-3">Matching Filter</p>
                <p class="text-5xl font-black text-white tracking-tighter">{{ count($events) }}</p>
                <p class="text-[#4a4a4a] text-[10px] mt-3">Current results</p>
            </div>
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-8">
                <p class="text-[#4a4a4a] text-[10px] font-black uppercase tracking-widest mb-3">Total Runners</p>
                <p class="text-5xl font-black text-white tracking-tighter">{{ $events->sum('users_count') }}</p>
                <p class="text-[#4a4a4a] text-[10px] mt-3">Community size</p>
            </div>
        </div>

        {{-- 5. SEARCH & FILTERS --}}
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-8 shadow-2xl space-y-6">
            <form action="{{ route('user.events') }}" method="GET" class="space-y-6">
                <div class="relative bg-[#0a0a0a] border border-[#2a2a2a] rounded-2xl overflow-hidden focus-within:border-[#6b6b4b] transition-all">
                    <i data-lucide="search" class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-[#4a4a4a]"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search events by name, organizer, or location..." 
                           class="w-full border-none focus:ring-0 text-sm pl-16 pr-6 py-4 rounded-2xl text-white bg-[#0a0a0a] outline-none placeholder:text-[#4a4a4a]">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    {{-- Run Type --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">Run Type</label>
                        <select name="run_type" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-xl py-3 px-4 text-xs text-white outline-none focus:border-[#6b6b4b] transition-all">
                            <option value="">All Types</option>
                            <option value="Road Run" {{ request('run_type') == 'Road Run' ? 'selected' : '' }}>Road Run</option>
                            <option value="Trail Run" {{ request('run_type') == 'Trail Run' ? 'selected' : '' }}>Trail Run</option>
                            <option value="Ultramarathon" {{ request('run_type') == 'Ultramarathon' ? 'selected' : '' }}>Ultra</option>
                        </select>
                    </div>
                    {{-- Distance --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">Distance</label>
                        <select name="distance" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-xl py-3 px-4 text-xs text-white outline-none focus:border-[#6b6b4b] transition-all">
                            <option value="">All KM</option>
                            <option value="5.0" {{ request('distance') == '5.0' ? 'selected' : '' }}>5 km</option>
                            <option value="10.0" {{ request('distance') == '10.0' ? 'selected' : '' }}>10 km</option>
                            <option value="21.1" {{ request('distance') == '21.1' ? 'selected' : '' }}>21 km</option>
                            <option value="42.2" {{ request('distance') == '42.2' ? 'selected' : '' }}>42 km</option>
                        </select>
                    </div>
                    {{-- Month --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">Month</label>
                        <select name="month" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-xl py-3 px-4 text-xs text-white outline-none focus:border-[#6b6b4b] transition-all">
                            <option value="">All Months</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- State --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">State</label>
                        <select name="state" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-xl py-3 px-4 text-xs text-white outline-none focus:border-[#6b6b4b] transition-all">
                            <option value="">All States</option>
                            @foreach(['Perlis', 'Kedah', 'Penang', 'Kelantan', 'Terengganu', 'Perak', 'Selangor', 'Negeri Sembilan', 'Malacca (Melaka)', 'Johor', 'Pahang', 'Sabah', 'Sarawak'] as $state)
                                <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Submit Button --}}
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all shadow-lg shadow-[#6b6b4b]/20">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- 6. EVENTS GRID (Split into Upcoming and Completed) --}}
        
        {{-- UPCOMING EVENTS SECTION --}}
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-black text-white uppercase tracking-wider">Explore Events</h2>
                <span class="ml-auto text-xs font-bold bg-[#2a2a2a] text-[#8b8b6b] px-3 py-1 rounded-full">{{ $upcomingEvents->count() }} found</span>
            </div>

            @if($upcomingEvents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($upcomingEvents as $event)
                    <div class="group bg-gradient-to-br from-[#1a1a1a] via-[#151515] to-[#0d0d0d] border border-[#2a2a2a] rounded-[2.5rem] p-8 flex flex-col transition-all duration-300 hover:border-[#6b6b4b] hover:shadow-2xl hover:shadow-[#6b6b4b]/15 overflow-hidden relative">
                        
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#6b6b4b] via-[#6b6b4b]/50 to-transparent"></div>

                        <div class="flex items-center justify-between mb-6 mt-2">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest
                                @if($event->run_type === 'Road Run') bg-sky-500/20 text-sky-400 border border-sky-500/30
                                @elseif($event->run_type === 'Trail Run') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                @elseif($event->run_type === 'Ultramarathon') bg-purple-500/20 text-purple-400 border border-purple-500/30
                                @else bg-[#6b6b4b]/20 text-[#6b6b4b] border border-[#6b6b4b]/30
                                @endif
                            ">
                                {{ $event->run_type ?? 'Run' }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse"></span>
                                <span class="text-[10px] font-bold text-emerald-400">Live</span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-black text-white tracking-tight group-hover:text-[#6b6b4b] transition-colors leading-tight mb-2 line-clamp-2">
                                {{ $event->title }}
                            </h3>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold bg-[#6b6b4b]/20 text-[#6b6b4b] px-3 py-1 rounded-full">
                                    <i data-lucide="map-pin" class="inline w-3 h-3 mr-1"></i> {{ $event->distance_km }} km
                                </span>
                                <span class="text-[10px] text-[#8b8b6b] font-semibold">{{ $event->users_count ?? 0 }} joined</span>
                            </div>
                        </div>

                        <div class="space-y-3 mb-8 flex-1 pb-6 border-b border-[#2a2a2a] text-[#b0b0a0]">
                            <div class="flex items-center gap-3">
                                <i data-lucide="calendar" class="w-4 h-4 text-[#6b6b4b] flex-shrink-0"></i>
                                <span class="text-xs font-semibold">{{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i data-lucide="clock" class="w-4 h-4 text-[#6b6b4b] flex-shrink-0"></i>
                                <span class="text-xs font-semibold">{{ \Carbon\Carbon::parse($event->time)->format('h:i A') }}</span>
                            </div>
                            <div class="flex items-center gap-3 truncate">
                                <i data-lucide="map-pin" class="w-4 h-4 text-[#6b6b4b] flex-shrink-0"></i>
                                <span class="text-xs font-semibold truncate">{{ $event->location }}</span>
                            </div>
                        </div>

                        <div class="mb-8">
                            @if($event->entry_fee > 0)
                                <div class="flex items-center gap-3">
                                    <i data-lucide="credit-card" class="w-5 h-5 text-[#6b6b4b]"></i>
                                    <p class="text-lg font-black text-[#6b6b4b]">RM {{ number_format($event->entry_fee, 2) }}</p>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase tracking-widest">
                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> FREE EVENT
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-6 border-t border-[#2a2a2a]">
                            <button @click="showDetailModal = true; selectedEvent = {{ $event->toJson() }}" 
                                    class="flex items-center justify-center gap-2 py-3 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all group-hover:border-[#6b6b4b]/30 border border-transparent">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Details
                            </button>
                            @if($event->users->contains(auth()->id()))
                                <form action="{{ route('user.events.quit', $event->id) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl font-bold text-[10px] uppercase tracking-widest border border-red-500/20 transition-all">
                                        Withdraw
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('user.events.join', $event->id) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-3 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all shadow-lg shadow-[#6b6b4b]/20 group-hover:shadow-[#6b6b4b]/30">
                                        Join Now
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="py-32 bg-[#151515] border-2 border-dashed border-[#2a2a2a] rounded-[3rem] text-center">
                    <i data-lucide="search" class="w-24 h-24 text-[#4a4a4a] mx-auto mb-6"></i>
                    <h3 class="text-2xl font-black text-white mb-3">No Upcoming Events Found</h3>
                    <p class="text-[#8b8b6b] text-sm mb-8 max-w-md mx-auto">Try adjusting your filters or check back later for new events</p>
                    <button type="reset" onclick="location.href='{{ route('user.events') }}'" class="bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white px-8 py-3 rounded-xl font-black text-sm transition-all shadow-lg shadow-[#6b6b4b]/20">
                        Clear Filters
                    </button>
                </div>
            @endif
        </div>

        {{-- COMPLETED EVENTS SECTION --}}
        @if($completedEvents->isNotEmpty())
        <div class="space-y-6 pt-12 border-t border-[#2a2a2a]">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-black text-[#4a4a4a] uppercase tracking-wider">Completed Events</h2>
                <span class="ml-auto text-xs font-bold bg-[#1a1a1a] text-[#4a4a4a] px-3 py-1 rounded-full border border-[#2a2a2a]">{{ $completedEvents->count() }} archived</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 opacity-60 hover:opacity-100 transition-opacity duration-500">
                @foreach($completedEvents as $event)
                <div class="group bg-[#0f0f0f] border border-[#2a2a2a] rounded-[2.5rem] p-8 flex flex-col grayscale hover:grayscale-0 transition-all duration-500">
                    <div class="flex justify-between items-start mb-6">
                        <span class="text-[10px] font-black bg-[#2a2a2a] text-[#666] px-4 py-2 rounded-full uppercase tracking-widest border border-[#333]">Ended</span>
                    </div>

                    <h3 class="text-lg font-black text-[#666] uppercase truncate mb-1">{{ $event->title }}</h3>
                    <p class="text-[#4a4a4a] text-[10px] font-bold uppercase tracking-widest mb-6">{{ $event->distance_km }} KM • {{ $event->run_type }}</p>

                    <div class="space-y-2 mb-6 text-[#4a4a4a] text-xs">
                        <div class="flex items-center gap-3">
                            <i data-lucide="calendar" class="w-4 h-4 flex-shrink-0"></i>
                            <span>{{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i data-lucide="map-pin" class="w-4 h-4 flex-shrink-0"></i>
                            <span class="truncate">{{ $event->location }}</span>
                        </div>
                    </div>

                    <button @click="showDetailModal = true; selectedEvent = {{ $event->toJson() }}" 
                            class="mt-auto w-full py-3 bg-[#1a1a1a] hover:bg-[#2a2a2a] text-[#666] rounded-xl font-bold text-[10px] uppercase tracking-widest border border-[#2a2a2a] transition-all">
                        View History
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>


    {{-- DETAILED EVENT MODAL --}}
    <div x-show="showDetailModal" class="fixed inset-0 bg-black/95 flex items-center justify-center z-[100] p-4 backdrop-blur-md" style="display: none;">
        <div @click.outside="showDetailModal = false" class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[3rem] p-12 w-full max-w-3xl relative shadow-2xl max-h-[90vh] overflow-y-auto">
            
            <button @click="showDetailModal = false" class="absolute top-6 right-6 p-3 bg-[#2a2a2a] hover:bg-[#3a3a3a] rounded-full text-[#8b8b6b] hover:text-white transition-all">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>

            <div class="mb-10">
                <p class="text-[#6b6b4b] text-[10px] font-black uppercase tracking-widest mb-3" x-text="selectedEvent.run_type"></p>
                <h2 class="text-4xl font-black text-white uppercase tracking-tighter mb-2" x-text="selectedEvent.title"></h2>
                <p class="text-[#b0b0a0] text-sm italic max-w-xl" x-text="selectedEvent.description || 'No description provided.'"></p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10 pb-10 border-b border-[#2a2a2a]">
                <div class="bg-[#0a0a0a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <p class="text-[#4a4a4a] text-[10px] font-black uppercase tracking-widest mb-2">Distance</p>
                    <p class="text-2xl font-black text-white" x-text="selectedEvent.distance_km + ' km'"></p>
                </div>
                <div class="bg-[#0a0a0a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <p class="text-[#4a4a4a] text-[10px] font-black uppercase tracking-widest mb-2">State</p>
                    <p class="text-2xl font-black text-white" x-text="selectedEvent.state"></p>
                </div>
                <div class="bg-[#0a0a0a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <p class="text-[#4a4a4a] text-[10px] font-black uppercase tracking-widest mb-2">Date</p>
                    <p class="text-lg font-black text-white" x-text="new Date(selectedEvent.date).toLocaleDateString()"></p>
                </div>
                <div class="bg-[#0a0a0a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <p class="text-[#4a4a4a] text-[10px] font-black uppercase tracking-widest mb-2">Fee</p>
                    <p class="text-2xl font-black" :class="selectedEvent.entry_fee > 0 ? 'text-[#6b6b4b]' : 'text-emerald-400'" x-text="selectedEvent.entry_fee > 0 ? 'RM ' + selectedEvent.entry_fee : 'FREE'"></p>
                </div>
            </div>

            <div x-show="selectedEvent.latitude" class="bg-[#6b6b4b]/10 border border-[#6b6b4b]/20 rounded-[2rem] p-8 mb-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <i data-lucide="map-pin" class="w-8 h-8 text-[#6b6b4b] flex-shrink-0"></i>
                        <div>
                            <p class="text-white font-black text-lg" x-text="selectedEvent.location"></p>
                            <p class="text-[#4a4a4a] text-[10px] font-black uppercase tracking-widest mt-1">Click map to navigate</p>
                        </div>
                    </div>
                    <a :href="'https://www.google.com/maps/search/?api=1&query=' + selectedEvent.latitude + ',' + selectedEvent.longitude" target="_blank" class="bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-[#6b6b4b]/20 transition-all whitespace-nowrap">
                        Open Map
                    </a>
                </div>
            </div>

            <div class="mb-10">
                <p class="text-[#8b8b6b] text-[10px] font-black uppercase tracking-widest mb-3">Participants</p>
                <p class="text-3xl font-black text-white" x-text="(selectedEvent.users_count || 0) + ' runners'"></p>
            </div>

            <button @click="showDetailModal = false" class="w-full py-5 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                Return to Events
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>document.addEventListener('alpine:init', () => { lucide.createIcons(); });</script>
@endsection