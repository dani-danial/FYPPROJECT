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
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('dashboard') }}" class="text-[#8b8b6b] hover:text-white text-[10px] font-black uppercase tracking-widest transition-colors flex items-center gap-1">
                    ← Dashboard
                </a>
            </div>
            <h1 class="text-3xl font-black text-white uppercase tracking-tight">Run History & Coach AI</h1>
            <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest mt-1">Full log of your training sessions and coach feedback</p>
        </div>
    </div>

    {{-- Runs List --}}
    <div class="max-w-6xl mx-auto">
        @if($myRunsHistory->count() > 0)
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-8 shadow-2xl space-y-6">
                <div class="space-y-4 pr-1">
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

                {{-- Pagination Links --}}
                <div class="mt-8 border-t border-[#2a2a2a]/30 pt-6">
                    {{ $myRunsHistory->links() }}
                </div>
            </div>
        @else
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-12 text-center shadow-2xl">
                <i data-lucide="activity" class="w-16 h-16 text-[#4a4a4a] mx-auto mb-4 animate-pulse"></i>
                <h3 class="text-lg font-bold text-white uppercase mb-2">No Runs Recorded Yet</h3>
                <p class="text-[#8b8b6b] text-xs max-w-md mx-auto leading-relaxed">Start recording your runs from the mobile app to see your performance metrics and get elite coaching advice from Coach Flash here!</p>
                <a href="{{ route('dashboard') }}" class="inline-block mt-6 bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white py-3 px-6 rounded-xl font-black text-xs uppercase tracking-widest transition-colors">
                    Back to Dashboard
                </a>
            </div>
        @endif
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Leaflet Map Modal Logic
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
