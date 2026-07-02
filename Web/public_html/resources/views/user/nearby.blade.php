@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #nearbyMap { 
        height: 500px; 
        border-radius: 2.5rem; 
        border: 1px border-[#2a2a2a];
        /* Dark Mode Map Filter */
        filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
    }
    .leaflet-container { background: #0a0a0a; }
</style>

<div class="p-8 space-y-8 bg-[#0a0a0a] min-h-screen">
    {{-- Header with Breadcrumbs --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <nav class="flex text-[10px] font-bold uppercase tracking-[0.2em] text-[#4a4a4a] mb-2">
                <span>RunTracker</span>
                <span class="mx-2">/</span>
                <span class="text-[#6b6b4b]">Nearby Runners</span>
            </nav>
            <h1 class="text-3xl font-black text-white uppercase tracking-tight">Connect Nearby</h1>
        </div>
        
        {{-- FILTER FORM --}}
        <form action="{{ route('user.nearby') }}" method="GET" class="flex items-center gap-3 bg-[#1a1a1a] p-2 rounded-2xl border border-[#2a2a2a]">
            <label class="text-[10px] font-black text-[#4a4a4a] uppercase tracking-widest ml-4">Search Radius:</label>
            
            {{-- FIXED: Uses request() to keep your selection after reload --}}
            <select name="radius" 
                    class="bg-[#0a0a0a] text-white text-xs border border-[#2a2a2a] focus:ring-0 rounded-xl pl-4 pr-10 py-2 font-bold cursor-pointer hover:border-[#6b6b4b] transition-colors appearance-none">
                
                {{-- Logic: If URL has ?radius=5, keep 5 selected. Otherwise default to 10 --}}
                <option value="5" {{ request('radius') == '5' ? 'selected' : '' }} class="bg-[#1a1a1a] text-white">5 KM</option>
                <option value="10" {{ request('radius', 10) == '10' ? 'selected' : '' }} class="bg-[#1a1a1a] text-white">10 KM</option>
                <option value="25" {{ request('radius') == '25' ? 'selected' : '' }} class="bg-[#1a1a1a] text-white">25 KM</option>
            
            </select>
            
            <button type="submit" class="bg-[#6b6b4b] text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-[#5a5a3f] transition-all">
                Filter
            </button>
        </form>
    </div>

    @if(isset($error))
        {{-- ERROR STATE --}}
        <div class="bg-red-500/10 border border-red-500 text-red-500 p-8 rounded-[2.5rem] text-center flex flex-col items-center justify-center space-y-4">
            <i data-lucide="map-pin-off" class="w-12 h-12 opacity-50"></i>
            
            <div>
                <p class="text-sm font-black uppercase tracking-widest">{{ $error }}</p>
                <p class="text-xs text-red-400 mt-2 mb-2">We need your coordinates to calculate distance.</p>
            </div>

            <a href="{{ route('profile.edit') }}" class="px-8 py-4 bg-red-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-red-600 transition-all shadow-lg shadow-red-500/20">
                Update Location in Profile
            </a>
        </div>
    @else
        {{-- RESULTS GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-[#1a1a1a] border border-[#2a2a2a] rounded-[3rem] p-2 shadow-2xl relative group">
                <div id="nearbyMap" class="z-0"></div>
                
                {{-- Map Overlay Legend --}}
                <div class="absolute bottom-6 left-6 z-[400] bg-[#0a0a0a]/80 backdrop-blur-md border border-[#2a2a2a] p-4 rounded-2xl">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-3 h-3 rounded-full bg-[#6b6b4b] border border-white"></span>
                        <span class="text-[10px] font-bold text-white uppercase">You</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500 border border-white"></span>
                        <span class="text-[10px] font-bold text-white uppercase">Runners</span>
                    </div>
                </div>
            </div>

            <div class="space-y-4 overflow-y-auto pr-2 custom-scrollbar" style="max-height: 510px;">
                <div class="flex justify-between items-end mb-4 ml-2 mr-2">
                    <h2 class="text-[10px] font-black text-[#4a4a4a] uppercase tracking-[0.3em]">Runners in Range</h2>
                    <span class="text-[10px] font-bold text-[#6b6b4b]">{{ count($nearbyUsers) }} found</span>
                </div>

                @forelse($nearbyUsers as $runner)
                    <div class="bg-[#1a1a1a] border border-[#2a2a2a] p-6 rounded-[2rem] hover:border-[#6b6b4b]/50 transition-all group shadow-xl hover:-translate-y-1 duration-300">
                        <div class="flex items-center gap-5">
                            {{-- Avatar --}}
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-[#6b6b4b] to-[#1a1a1a] flex items-center justify-center text-white font-black overflow-hidden border-2 border-[#6b6b4b]/30 shadow-lg shadow-[#6b6b4b]/10">
                                @if($runner->profile_photo_path)
                                    <img src="{{ $runner->profile_photo_url }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xl uppercase">{{ substr($runner->name, 0, 1) }}</span>
                                @endif
                            </div>
                            
                            {{-- Info --}}
                            <div class="flex-grow">
                                <h3 class="text-white font-black uppercase text-sm group-hover:text-[#6b6b4b] transition-colors">{{ $runner->name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.6)]"></div>
                                    <p class="text-[#6b6b4b] text-[10px] font-black uppercase tracking-widest">{{ number_format($runner->distance, 1) }} KM Away</p>
                                </div>
                            </div>

                            {{-- Chat Action --}}
                            <a href="{{ route('chat.show', $runner->id) }}" class="bg-[#2a2a2a] p-4 rounded-full text-[#8b8b6b] hover:text-white hover:bg-[#6b6b4b] transition-all shadow-inner group-hover:scale-110">
                                <i data-lucide="message-circle" class="w-5 h-5"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20 bg-[#1a1a1a] rounded-[2.5rem] border-2 border-dashed border-[#2a2a2a]">
                        <div class="bg-[#2a2a2a] w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="users" class="w-10 h-10 text-[#4a4a4a]"></i>
                        </div>
                        <p class="text-white text-xs font-black uppercase tracking-widest mb-2">No runners nearby</p>
                        <p class="text-[#4a4a4a] text-[10px] uppercase tracking-widest">Try increasing the search radius</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
        
        @if(!isset($error))
            const userLat = {{ Auth::user()->latitude }};
            const userLng = {{ Auth::user()->longitude }};
            
            const map = L.map('nearbyMap', { zoomControl: false }).setView([userLat, userLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // 1. User Range Circle (Radar Effect)
            L.circle([userLat, userLng], { 
                color: '#6b6b4b', 
                fillColor: '#6b6b4b', 
                fillOpacity: 0.1, 
                weight: 1,
                radius: {{ $radius * 1000 }} 
            }).addTo(map);

            // 2. Custom Icons
            const userIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color:#6b6b4b; height:16px; width:16px; border-radius:50%; border:3px solid white; box-shadow: 0 0 15px #6b6b4b;'></div>",
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            const runnerIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color:#3b82f6; height:12px; width:12px; border-radius:50%; border:2px solid white; box-shadow: 0 0 10px #3b82f6;'></div>",
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });

            // 3. Add Markers
            L.marker([userLat, userLng], { icon: userIcon }).addTo(map)
                .bindPopup("<div class='text-center'><b class='uppercase text-[10px] text-black'>You are here</b></div>");

            @foreach($nearbyUsers as $runner)
                L.marker([{{ $runner->latitude }}, {{ $runner->longitude }}], { icon: runnerIcon })
                    .addTo(map)
                    .bindPopup("<div class='text-center'><b class='uppercase text-[10px] font-black text-black'>{{ $runner->name }}</b><br><span class='text-[9px] uppercase font-bold text-[#6b6b4b]'>{{ number_format($runner->distance, 1) }} KM AWAY</span><br><a href='{{ route('chat.show', $runner->id) }}' class='inline-block mt-2 bg-black text-white text-[9px] px-2 py-1 rounded uppercase font-bold'>Message</a></div>");
            @endforeach
        @endif
    });
</script>
@endsection