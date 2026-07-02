@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    #show-map { 
        height: 400px; 
        border-radius: 2rem; 
        border: 1px solid #2a2a2a;
        filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        margin-bottom: 2rem;
    }
    .leaflet-container { background: #0a0a0a; }
</style>

<div class="p-8 space-y-8 bg-[#0a0a0a] min-h-screen">
    <a href="{{ route('user.events') }}" class="inline-flex items-center gap-2 text-[#8b8b6b] hover:text-white transition-colors mb-4 group">
        <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
        <span class="text-xs font-bold uppercase tracking-widest">Back to All Events</span>
    </a>

    <div class="max-w-5xl mx-auto bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] overflow-hidden shadow-2xl">
        <div class="h-64 bg-[#6b6b4b]/20 flex items-center justify-center border-b border-[#2a2a2a] relative">
            <span class="text-9xl opacity-20 absolute select-none">🏃</span>
            <div class="relative z-10 text-center">
                <span class="bg-[#0a0a0a] text-[#6b6b4b] text-[10px] font-bold px-4 py-2 rounded-full border border-[#6b6b4b]/30 uppercase tracking-[0.2em]">
                    {{ $event->state }} Run Details
                </span>
            </div>
        </div>

        <div class="p-10 md:p-16 space-y-12">
            <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <h1 class="text-5xl font-black text-white tracking-tighter">{{ $event->title }}</h1>
                        {{-- STATE BADGE --}}
                        <span class="bg-[#6b6b4b] text-white text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-widest">
                            {{ $event->state }}
                        </span>
                    </div>
                    <p class="text-[#8b8b6b] font-bold uppercase tracking-widest text-sm flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4"></i> Organized by <span class="text-white">{{ $event->organizer }}</span>
                    </p>
                </div>
                <div class="bg-[#0a0a0a] border border-[#2a2a2a] px-8 py-4 rounded-3xl text-center">
                    <p class="text-[10px] font-bold text-[#4a4a4a] uppercase mb-1">Target Distance</p>
                    <p class="text-3xl font-black text-[#6b6b4b]">{{ $event->distance_km }} <span class="text-sm uppercase">km</span></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-[#202020] p-6 rounded-3xl border border-white/5 space-y-3">
                    <div class="w-10 h-10 bg-[#6b6b4b]/20 rounded-xl flex items-center justify-center text-[#6b6b4b]">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest">Event Date</p>
                        <p class="text-xl font-bold text-white">{{ \Carbon\Carbon::parse($event->date)->format('F d, Y') }}</p>
                    </div>
                </div>

                <div class="bg-[#202020] p-6 rounded-3xl border border-white/5 space-y-3">
                    <div class="w-10 h-10 bg-[#6b6b4b]/20 rounded-xl flex items-center justify-center text-[#6b6b4b]">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest">Flag Off Time</p>
                        <p class="text-xl font-bold text-white">{{ \Carbon\Carbon::parse($event->time)->format('h:i A') }}</p>
                    </div>
                </div>

                <div class="bg-[#202020] p-6 rounded-3xl border border-white/5 space-y-3">
                    <div class="w-10 h-10 bg-[#6b6b4b]/20 rounded-xl flex items-center justify-center text-[#6b6b4b]">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest">Location</p>
                        <p class="text-xl font-bold text-white leading-tight truncate">{{ $event->location }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center border-b border-[#2a2a2a] pb-4">
                    <h3 class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest">Start Location Map</h3>
                    @if($event->latitude && $event->longitude)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $event->latitude }},{{ $event->longitude }}" 
                           target="_blank" class="flex items-center gap-2 text-[#6b6b4b] hover:text-white text-[10px] font-bold uppercase">
                            <i data-lucide="navigation" class="w-3 h-3"></i> Open in Google Maps
                        </a>
                    @endif
                </div>
                
                @if($event->latitude && $event->longitude)
                    <div id="show-map"></div>
                @else
                    <div class="h-48 bg-[#0a0a0a] rounded-3xl flex flex-col items-center justify-center border border-dashed border-[#2a2a2a]">
                        <i data-lucide="map-off" class="w-8 h-8 text-[#2a2a2a] mb-2"></i>
                        <p class="text-[10px] text-[#4a4a4a] font-bold uppercase">No map coordinates provided</p>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <h3 class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest border-b border-[#2a2a2a] pb-4">About this Run</h3>
                <p class="text-lg text-[#b0b0a0] leading-relaxed">{{ $event->description ?? 'No detailed description provided.' }}</p>
            </div>

            <div class="pt-8 flex flex-col items-center gap-6">
                @if($event->users->contains(auth()->id()))
                    <form action="{{ route('user.events.quit', $event->id) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full py-6 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-[2rem] font-black text-xl border border-red-500/30 uppercase tracking-widest flex items-center justify-center gap-3">
                            <i data-lucide="x-circle" class="w-6 h-6"></i> Quit This Run
                        </button>
                    </form>
                @else
                    <form action="{{ route('user.events.join', $event->id) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full py-6 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-[2rem] font-black text-xl uppercase tracking-widest flex items-center justify-center gap-3 shadow-2xl shadow-[#6b6b4b]/30">
                            <i data-lucide="zap" class="w-6 h-6"></i> Confirm & Join Run
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    lucide.createIcons();
    @if($event->latitude && $event->longitude)
        const map = L.map('show-map').setView([{{ $event->latitude }}, {{ $event->longitude }}], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([{{ $event->latitude }}, {{ $event->longitude }}]).addTo(map);
    @endif
</script>
@endsection