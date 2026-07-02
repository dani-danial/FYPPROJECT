@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Dashboard
    </a>

    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Edit Run Details</h2>
            <div class="text-xs font-mono text-[#6b6b4b] bg-[#6b6b4b]/10 px-2 py-1 rounded">ID: {{ $run->id }}</div>
        </div>

        <form action="{{ route('runs.update', $run->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Date</label>
                <div class="relative">
                    <input type="date" name="date" value="{{ $run->date }}" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all [color-scheme:dark]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Distance (km)</label>
                    <input type="number" step="0.01" name="distance_km" id="distance_km" value="{{ round($run->distance_km, 2) }}" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Duration (HH:MM:SS)</label>
                    <input type="text" name="time" id="duration_time" value="{{ $run->time }}" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Pace (Auto-calculated)</label>
                <input type="text" name="pace" id="pace_result" value="{{ $run->pace }}" readonly
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-[#8b8b6b] rounded-lg px-4 py-3 focus:outline-none transition-all cursor-not-allowed">
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ url('/') }}" 
                   class="px-6 py-2.5 rounded-lg border border-[#2a2a2a] text-[#b0b0a0] hover:text-white hover:bg-[#2a2a2a] transition-all font-medium text-sm">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-lg bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-medium text-sm shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const distInput = document.getElementById('distance_km');
    const timeInput = document.getElementById('duration_time');
    const paceInput = document.getElementById('pace_result');

    function calculate() {
        const dist = parseFloat(distInput.value);
        const time = timeInput.value;
        if (dist > 0 && time.includes(':')) {
            const parts = time.split(':');
            let sec = 0;
            if (parts.length === 3) sec = (+parts[0]) * 3600 + (+parts[1]) * 60 + (+parts[2]);
            else if (parts.length === 2) sec = (+parts[0]) * 60 + (+parts[1]);

            if (sec > 0) {
                const paceSecs = sec / dist;
                const min = Math.floor(paceSecs / 60);
                const s = Math.round(paceSecs % 60);
                paceInput.value = min + ":" + (s < 10 ? '0' : '') + s + " /km";
            }
        }
    }
    distInput.addEventListener('input', calculate);
    timeInput.addEventListener('input', calculate);
});
</script>
@endsection