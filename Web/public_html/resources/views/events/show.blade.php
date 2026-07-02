@extends('layouts.app')

@section('content')
{{-- 1. Leaflet CSS for Maps --}}
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
    
    /* Animation for the banner logo */
    .event-logo-glow {
        box-shadow: 0 0 40px rgba(107, 107, 75, 0.2);
    }
</style>

<div class="p-8 space-y-8 bg-[#0a0a0a] min-h-screen">
    {{-- Back to Management Hub --}}
    <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-[#8b8b6b] hover:text-white transition-colors mb-4 group">
        <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
        <span class="text-xs font-bold uppercase tracking-widest">Back to Events Management</span>
    </a>

    <div class="max-w-5xl mx-auto bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] overflow-hidden shadow-2xl">
        
        {{-- 🆕 UPDATED BANNER SECTION: Displays Event Logo --}}
        <div class="h-80 bg-[#151515] flex items-center justify-center border-b border-[#2a2a2a] relative overflow-hidden">
            {{-- Background Blur Decorative Effect --}}
            @if($event->logo_path)
                <img src="{{ $event->logo_path }}" class="absolute inset-0 w-full h-full object-cover opacity-10 blur-2xl">
            @endif

            <div class="relative z-10 flex flex-col items-center">
                <div class="w-32 h-32 md:w-40 md:h-40 bg-[#0a0a0a] rounded-[2.5rem] border-2 border-[#6b6b4b]/30 overflow-hidden event-logo-glow mb-6">
                    @if($event->logo_path)
                        <img src="{{ $event->logo_path }}" class="w-full h-full object-cover">
                    @else
                        {{-- Fallback: Show initials if no logo exists --}}
                        <div class="w-full h-full flex items-center justify-center bg-[#2a2a2a] text-[#6b6b4b] text-4xl font-black">
                            {{ strtoupper(substr($event->title, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <span class="bg-[#0a0a0a]/80 backdrop-blur-md text-[#6b6b4b] text-[10px] font-black px-6 py-3 rounded-full border border-[#6b6b4b]/30 uppercase tracking-[0.3em]">
                    Official Event Record
                </span>
            </div>
        </div>

        <div class="p-10 md:p-16 space-y-12">
            {{-- Header Details --}}
            <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <h1 class="text-5xl font-black text-white tracking-tighter uppercase">{{ $event->title }}</h1>
                        <span class="bg-[#6b6b4b] text-white text-[10px] font-black px-4 py-1 rounded-lg uppercase tracking-widest">
                            {{ $event->state }}
                        </span>
                    </div>
                    <p class="text-[#8b8b6b] font-bold uppercase tracking-widest text-sm flex items-center gap-3">
                        <i data-lucide="activity" class="w-5 h-5 text-[#6b6b4b]"></i> 
                        Run Category: <span class="text-white">{{ $event->run_type }}</span>
                    </p>
                </div>
                <div class="bg-[#0a0a0a] border border-[#2a2a2a] px-10 py-6 rounded-[2rem] text-center min-w-[180px]">
                    <p class="text-[10px] font-black text-[#4a4a4a] uppercase mb-1 tracking-widest">Distance</p>
                    <p class="text-4xl font-black text-[#6b6b4b]">{{ number_format($event->distance_km, 1) }} <span class="text-sm uppercase">km</span></p>
                </div>
            </div>

            {{-- Info Grid (5 Columns) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="bg-[#202020] p-6 rounded-[1.5rem] border border-white/5 space-y-3">
                    <i data-lucide="calendar" class="w-5 h-5 text-[#6b6b4b]"></i>
                    <p class="text-[9px] font-black text-[#4a4a4a] uppercase tracking-widest">Date</p>
                    <p class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}</p>
                </div>
                <div class="bg-[#202020] p-6 rounded-[1.5rem] border border-white/5 space-y-3">
                    <i data-lucide="clock" class="w-5 h-5 text-[#6b6b4b]"></i>
                    <p class="text-[9px] font-black text-[#4a4a4a] uppercase tracking-widest">Flag Off</p>
                    <p class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($event->time)->format('h:i A') }}</p>
                </div>
                <div class="bg-[#202020] p-6 rounded-[1.5rem] border border-white/5 space-y-3">
                    <i data-lucide="map-pin" class="w-5 h-5 text-[#6b6b4b]"></i>
                    <p class="text-[9px] font-black text-[#4a4a4a] uppercase tracking-widest">Venue</p>
                    <p class="text-sm font-bold text-white truncate">{{ $event->location }}</p>
                </div>
                <div class="bg-[#202020] p-6 rounded-[1.5rem] border border-white/5 space-y-3">
                    <i data-lucide="award" class="w-5 h-5 text-[#6b6b4b]"></i>
                    <p class="text-[9px] font-black text-[#4a4a4a] uppercase tracking-widest">Runner Tier</p>
                    <p class="text-sm font-bold text-white">{{ $event->runner_tier }}</p>
                </div>
                <div class="bg-[#202020] p-6 rounded-[1.5rem] border border-white/5 space-y-3">
                    <i data-lucide="users" class="w-5 h-5 text-[#6b6b4b]"></i>
                    <p class="text-[9px] font-black text-[#4a4a4a] uppercase tracking-widest">Attendance</p>
                    <p class="text-sm font-bold text-white">{{ $event->users_count ?? 0 }} Joined</p>
                </div>
            </div>

            {{-- Map Section --}}
            @if($event->latitude)
                <div class="space-y-6">
                    <h3 class="text-[10px] font-black text-[#4a4a4a] uppercase tracking-[0.3em] border-b border-[#2a2a2a] pb-6">Geographic Start Point</h3>
                    <div id="show-map"></div>
                </div>
            @endif

            {{-- Description Section --}}
            <div class="space-y-6">
                <h3 class="text-[10px] font-black text-[#4a4a4a] uppercase tracking-[0.3em] border-b border-[#2a2a2a] pb-6">Event Narrative</h3>
                <p class="text-xl text-[#b0b0a0] leading-relaxed font-medium italic">
                    {{ $event->description ?? 'No administrative description has been added for this event.' }}
                </p>
            </div>

            {{-- ADMIN MANAGEMENT ACTIONS --}}
            <div class="pt-12 flex flex-col sm:flex-row items-center gap-4">
                <a href="{{ route('events.edit', $event->id) }}" 
                   class="w-full py-6 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-[2rem] font-black text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3 shadow-xl shadow-[#6b6b4b]/20 transition-all">
                    <i data-lucide="pencil-line" class="w-5 h-5"></i> Modify Event Parameters
                </a>
                
                <form action="{{ route('events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Delete permanently?')" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                        class="w-full py-6 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-[2rem] font-black text-xs border border-red-500/30 uppercase tracking-[0.2em] flex items-center justify-center gap-3 transition-all">
                        <i data-lucide="trash-2" class="w-5 h-5"></i> Delete Official Record
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
        @if($event->latitude && $event->longitude)
            const map = L.map('show-map', { 
                zoomControl: false, 
                scrollWheelZoom: false 
            }).setView([{{ $event->latitude }}, {{ $event->longitude }}], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            
            const customIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color:#6b6b4b; width:12px; height:12px; border-radius:50%; border:2px solid white;'></div>",
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });

            L.marker([{{ $event->latitude }}, {{ $event->longitude }}], {icon: customIcon}).addTo(map);
        @endif
    });
</script>
@endsection