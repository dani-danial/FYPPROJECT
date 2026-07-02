@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 350px; border-radius: 1.5rem; filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); border: 1px solid #2a2a2a; }
    .leaflet-container { background: #0a0a0a; }
    
    /* Custom styling for the file upload input to match your theme */
    input[type="file"]::file-selector-button {
        background-color: #6b6b4b;
        color: white;
        padding: 8px 16px;
        border-radius: 12px;
        border: none;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 10px;
        margin-right: 15px;
        cursor: pointer;
        transition: 0.3s;
    }
    input[type="file"]::file-selector-button:hover {
        background-color: #7b7b5b;
    }
</style>

<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-[#8b8b6b] hover:text-white transition-colors mb-8 group">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
            <span class="text-xs font-bold uppercase tracking-widest">Back to Management</span>
        </a>

        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-10 shadow-2xl">
            <h2 class="text-3xl font-black text-white uppercase mb-2">Create Official Event</h2>
            <p class="text-[#4a4a4a] text-sm mb-10 font-bold uppercase tracking-widest">Setup a new global running event</p>

            @if ($errors->any())
                <div class="mb-8 rounded-2xl border border-red-500/40 bg-red-500/10 p-5 text-red-200">
                    <p class="mb-3 text-xs font-black uppercase tracking-widest text-red-300">Event was not created</p>
                    <ul class="space-y-2 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 🛠️ CRITICAL: enctype="multipart/form-data" allows image uploading --}}
            <form action="{{ route('events.store') }}" method="POST" class="space-y-8" enctype="multipart/form-data" onsubmit="syncDistanceValue()">
                @csrf
                
                {{-- RUN CATEGORY & RUNNER TIER --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Run Category</label>
                        <select name="run_type" id="run_type" required onchange="toggleDistanceInput()" style="background-color: black !important; color: white !important;"
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b] transition-all">
                            <option value="Road Run" @selected(old('run_type', 'Road Run') === 'Road Run')>Normal (Road Run)</option>
                            <option value="Trail Run" @selected(old('run_type') === 'Trail Run')>Offroad (Trail Run)</option>
                            <option value="Ultramarathon" @selected(old('run_type') === 'Ultramarathon')>Ultramarathon</option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Target Runner Tier</label>
                        <select name="runner_tier" required style="background-color: black !important; color: white !important;"
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b] transition-all">
                            <option value="LOW" @selected(old('runner_tier', 'LOW') === 'LOW')>LOW (Beginner)</option>
                            <option value="MEDIUM" @selected(old('runner_tier') === 'MEDIUM')>MEDIUM (Intermediate)</option>
                            <option value="HARD" @selected(old('runner_tier') === 'HARD')>HARD (Advanced)</option>
                        </select>
                    </div>
                </div>

                {{-- 🆕 EVENT LOGO / ICON UPLOAD --}}
                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Event Icon / Logo</label>
                    <div class="w-full border border-[#2a2a2a] rounded-2xl p-5 bg-black">
                        <input type="file" name="logo" accept="image/*" required
                            class="w-full text-[#8b8b6b] text-xs font-bold uppercase outline-none">
                    </div>
                    <p class="text-[#4a4a4a] text-[9px] font-bold uppercase tracking-widest mt-1">Recommended: Square PNG/JPG (Max 2MB)</p>
                </div>

                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Run Title</label>
                    <input type="text" name="title" required value="{{ old('title') }}" placeholder="Official Event Name" style="background-color: black !important; color: white !important;"
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b]">
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Date</label>
                        <input type="date" name="date" required value="{{ old('date') }}" style="background-color: black !important; color: white !important;"
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none [color-scheme:dark]">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Time</label>
                        <input type="time" name="time" required value="{{ old('time') }}" style="background-color: black !important; color: white !important;"
                            class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none [color-scheme:dark]">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Malaysia State</label>
                    <select name="state" id="state_select" required onchange="centerMapOnState()" style="background-color: black !important; color: white !important;"
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none">
                        <option value="">-- Select State --</option>
                        @foreach(['Perlis', 'Kedah', 'Penang', 'Kelantan', 'Terengganu', 'Perak', 'Selangor', 'Negeri Sembilan', 'Malacca (Melaka)', 'Johor', 'Pahang', 'Sabah', 'Sarawak'] as $state)
                            <option value="{{ $state }}" @selected(old('state') === $state)>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Entry Fee (RM)</label>
                    <input type="number" name="entry_fee" required min="0" step="0.01" value="{{ old('entry_fee', '0') }}" placeholder="0.00" style="background-color: black !important; color: white !important;"
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b] transition-all">
                    <p class="text-[#4a4a4a] text-[10px] font-bold uppercase tracking-widest">Set to 0 for free events</p>
                </div>

                <div class="space-y-4">
                    <div id="map"></div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                    <input type="text" name="location" id="location" required value="{{ old('location') }}" placeholder="Pin on map..." style="background-color: black !important; color: white !important;"
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 outline-none focus:border-[#6b6b4b]">
                </div>

                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Distance (KM)</label>
                    <div id="standard_distance_container">
                        <select id="distance_km_select" style="background-color: black !important; color: white !important;" class="w-full border border-[#2a2a2a] rounded-2xl p-5">
                            <option value="5.0" @selected(old('distance_km', '5.0') == '5.0')>5.0 KM</option>
                            <option value="10.0" @selected(old('distance_km') == '10.0')>10.0 KM</option>
                            <option value="21.1" @selected(old('distance_km') == '21.1')>21.1 KM</option>
                            <option value="42.2" @selected(old('distance_km') == '42.2')>42.2 KM</option>
                        </select>
                    </div>
                    <div id="ultra_distance_container" class="hidden">
                        <input type="number" step="0.1" id="distance_km_input" value="{{ old('run_type') === 'Ultramarathon' ? old('distance_km') : '' }}" placeholder="Custom distance" style="background-color: black !important; color: white !important;" class="w-full border border-[#2a2a2a] rounded-2xl p-5">
                    </div>
                    <input type="hidden" name="distance_km" id="distance_km" value="{{ old('distance_km', '5.0') }}">
                </div>

                <div class="pt-6 flex gap-4">
                    <a href="{{ route('events.index') }}" class="flex-1 py-5 bg-[#2a2a2a] text-[#8b8b6b] text-center rounded-2xl font-black text-xs uppercase tracking-widest">Cancel</a>
                    <button type="submit" class="flex-1 py-5 bg-[#6b6b4b] text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-[#6b6b4b]/20">Confirm Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    lucide.createIcons();
    const map = L.map('map').setView([2.1896, 102.2501], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    let marker;
    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        if (marker) marker.setLatLng(e.latlng); else marker = L.marker(e.latlng).addTo(map);
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(res => res.json()).then(data => document.getElementById('location').value = data.display_name.split(',').slice(0,3).join(','));
    });
    
    function centerMapOnState() {
        const state = document.getElementById('state_select').value;
        const coords = { 'Perlis': [6.4449, 100.2048], 'Kedah': [6.1184, 100.3686], 'Penang': [5.4141, 100.3288], 'Kelantan': [6.1254, 102.2386], 'Terengganu': [5.3117, 103.1324], 'Perak': [4.5921, 101.0901], 'Selangor': [3.0738, 101.5183], 'Negeri Sembilan': [2.7258, 101.9424], 'Malacca (Melaka)': [2.1896, 102.2501], 'Johor': [1.4854, 103.7618], 'Pahang': [3.8126, 103.3256], 'Sabah': [5.9788, 116.0753], 'Sarawak': [1.5533, 110.3592] };
        if (coords[state]) map.flyTo(coords[state], 11);
    }
    
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
    
    document.getElementById('distance_km_select').addEventListener('change', syncDistanceValue);
    document.getElementById('distance_km_input').addEventListener('input', syncDistanceValue);
    toggleDistanceInput();
</script>
@endsection
