@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    #map { height: 350px; border-radius: 1.5rem; filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); border: 1px solid #2a2a2a; }
    .leaflet-container { background: #0a0a0a; }

    /* Custom styling for the file upload input to match your theme */
    input[type="file"]::file-selector-button {
        background-color: #2a2a2a;
        color: #8b8b6b;
        padding: 8px 16px;
        border-radius: 12px;
        border: 1px solid #3a3a3a;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 10px;
        margin-right: 15px;
        cursor: pointer;
        transition: 0.3s;
    }
    input[type="file"]::file-selector-button:hover {
        background-color: #6b6b4b;
        color: white;
    }
</style>

<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-[#8b8b6b] hover:text-white transition-colors mb-8 group">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
            <span class="text-xs font-bold uppercase tracking-widest">Cancel Editing</span>
        </a>

        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-10 shadow-2xl">
            <h2 class="text-3xl font-black text-white uppercase mb-2">Edit Event Parameters</h2>
            <p class="text-[#4a4a4a] text-sm mb-10 font-bold uppercase tracking-widest">Update details for {{ $event->title }}</p>

            @if ($errors->any())
                <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-xl text-[10px] font-black uppercase tracking-widest">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 🛠️ CRITICAL: enctype="multipart/form-data" added for logo upload --}}
            <form action="{{ route('events.update', $event->id) }}" method="POST" class="space-y-8" enctype="multipart/form-data">
                @csrf 
                @method('PUT')
                
                {{-- 🆕 EVENT LOGO UPDATE SECTION --}}
                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Event Icon / Logo</label>
                    <div class="flex items-center gap-6 p-5 bg-black border border-[#2a2a2a] rounded-2xl">
                        @if($event->logo_path)
                            <div class="relative group">
                                <img src="{{ $event->logo_path }}" class="w-16 h-16 rounded-xl object-cover border border-[#6b6b4b]/30 shadow-lg">
                                <div class="absolute -top-2 -right-2 bg-[#6b6b4b] text-white p-1 rounded-full border border-black">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                </div>
                            </div>
                        @else
                            <div class="w-16 h-16 rounded-xl bg-[#0a0a0a] flex items-center justify-center border border-dashed border-[#2a2a2a]">
                                <i data-lucide="image-plus" class="w-6 h-6 text-[#2a2a2a]"></i>
                            </div>
                        @endif
                        
                        <div class="flex-1">
                            <input type="file" name="logo" accept="image/*" 
                                class="w-full text-[#4a4a4a] text-[10px] font-bold uppercase outline-none">
                            <p class="text-[#4a4a4a] text-[9px] mt-2 font-bold uppercase tracking-widest">Select a new file to replace the current icon</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Run Category</label>
                        <select name="run_type" id="run_type" required onchange="toggleDistanceInput()" 
                            style="background-color: black !important; color: white !important;" 
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b] transition-all">
                            <option value="Road Run" {{ $event->run_type == 'Road Run' ? 'selected' : '' }}>Normal (Road Run)</option>
                            <option value="Trail Run" {{ $event->run_type == 'Trail Run' ? 'selected' : '' }}>Offroad (Trail Run)</option>
                            <option value="Ultramarathon" {{ $event->run_type == 'Ultramarathon' ? 'selected' : '' }}>Ultramarathon</option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Target Runner Tier</label>
                        <select name="runner_tier" required style="background-color: black !important; color: white !important;"
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b]">
                            <option value="LOW" {{ $event->runner_tier == 'LOW' ? 'selected' : '' }}>LOW (Beginner)</option>
                            <option value="MEDIUM" {{ $event->runner_tier == 'MEDIUM' ? 'selected' : '' }}>MEDIUM (Intermediate)</option>
                            <option value="HARD" {{ $event->runner_tier == 'HARD' ? 'selected' : '' }}>HARD (Advanced)</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Run Title</label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}" required 
                        style="background-color: black !important; color: white !important;" 
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b]">
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Date</label>
                        <input type="date" name="date" value="{{ old('date', $event->date) }}" required 
                            style="background-color: black !important; color: white !important;" 
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none [color-scheme:dark]">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Flag-off Time</label>
                        <input type="time" name="time" value="{{ old('time', \Carbon\Carbon::parse($event->time)->format('H:i')) }}" required 
                            style="background-color: black !important; color: white !important;" 
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none [color-scheme:dark]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Malaysia State</label>
                        <select name="state" id="state_select" required onchange="centerMapOnState()" 
                            style="background-color: black !important; color: white !important;" 
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none">
                            @foreach(['Perlis', 'Kedah', 'Penang', 'Kelantan', 'Terengganu', 'Perak', 'Selangor', 'Negeri Sembilan', 'Malacca (Melaka)', 'Johor', 'Pahang', 'Sabah', 'Sarawak'] as $state)
                                <option value="{{ $state }}" {{ $event->state == $state ? 'selected' : '' }}>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Event Status</label>
                        <select name="status" style="background-color: black !important; color: white !important;" 
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none">
                            <option value="upcoming" {{ $event->status == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="completed" {{ $event->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Entry Fee (RM)</label>
                    <input type="number" name="entry_fee" value="{{ old('entry_fee', $event->entry_fee) }}" required min="0" step="0.01"
                        style="background-color: black !important; color: white !important;" 
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b]">
                </div>

                <div class="space-y-4">
                    <div id="map"></div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ $event->latitude }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $event->longitude }}">
                    <input type="text" name="location" id="location" value="{{ old('location', $event->location) }}" required 
                        style="background-color: black !important; color: white !important;" 
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b]">
                </div>

                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Distance (KM)</label>
                    <div id="standard_distance_container" class="{{ $event->run_type == 'Ultramarathon' ? 'hidden' : '' }}">
                        <select id="distance_km_select" style="background-color: black !important; color: white !important;" class="w-full border border-[#2a2a2a] rounded-2xl p-5">
                            <option value="5.0" {{ $event->distance_km == 5.0 ? 'selected' : '' }}>5.0 KM</option>
                            <option value="10.0" {{ $event->distance_km == 10.0 ? 'selected' : '' }}>10.0 KM</option>
                            <option value="21.1" {{ $event->distance_km == 21.1 ? 'selected' : '' }}>21.1 KM</option>
                            <option value="42.2" {{ $event->distance_km == 42.2 ? 'selected' : '' }}>42.2 KM</option>
                        </select>
                    </div>
                    <div id="ultra_distance_container" class="{{ $event->run_type == 'Ultramarathon' ? '' : 'hidden' }}">
                        <input type="number" step="0.1" id="distance_km_input" value="{{ $event->distance_km }}" style="background-color: black !important; color: white !important;" class="w-full border border-[#2a2a2a] rounded-2xl p-5">
                    </div>
                    <input type="hidden" name="distance_km" id="distance_km" value="{{ $event->distance_km }}">
                </div>

                <button type="submit" class="w-full py-6 bg-[#6b6b4b] text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-[#6b6b4b]/20 hover:bg-[#7b7b5b] transition-all">
                    Update Official Event
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    lucide.createIcons();
    const map = L.map('map').setView([{{ $event->latitude ?? 2.1896 }}, {{ $event->longitude ?? 102.2501 }}], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    let marker = L.marker([{{ $event->latitude ?? 2.1896 }}, {{ $event->longitude ?? 102.2501 }}]).addTo(map);

    function centerMapOnState() {
        const stateCoords = { 'Johor': [1.9344, 103.3587], 'Kedah': [6.1184, 100.3685], 'Kelantan': [6.1254, 102.2381], 'Malacca (Melaka)': [2.1896, 102.2501], 'Negeri Sembilan': [2.7258, 101.9424], 'Pahang': [3.8126, 103.3256], 'Penang': [5.4141, 100.3288], 'Perak': [4.5921, 101.0901], 'Perlis': [6.4449, 100.2048], 'Sabah': [5.9788, 116.0753], 'Sarawak': [1.5533, 110.3592], 'Selangor': [3.0738, 101.5183], 'Terengganu': [5.3117, 103.1324] };
        const state = document.getElementById('state_select').value;
        if (stateCoords[state]) { map.setView(stateCoords[state], 11); marker.setLatLng(stateCoords[state]); document.getElementById('latitude').value = stateCoords[state][0]; document.getElementById('longitude').value = stateCoords[state][1]; }
    }

    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        marker.setLatLng(e.latlng);
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(res => res.json()).then(data => document.getElementById('location').value = data.display_name.split(',').slice(0,3).join(','));
    });

    function toggleDistanceInput() {
        const type = document.getElementById('run_type').value;
        document.getElementById('standard_distance_container').classList.toggle('hidden', type === 'Ultramarathon');
        document.getElementById('ultra_distance_container').classList.toggle('hidden', type !== 'Ultramarathon');
        syncDistanceValue();
    }

    function syncDistanceValue() {
        const type = document.getElementById('run_type').value;
        document.getElementById('distance_km').value = (type === 'Ultramarathon') ? document.getElementById('distance_km_input').value : document.getElementById('distance_km_select').value;
    }
    syncDistanceValue();
    document.getElementById('distance_km_select').addEventListener('change', syncDistanceValue);
    document.getElementById('distance_km_input').addEventListener('input', syncDistanceValue);
</script>
@endsection