@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Dashboard
    </a>

    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Log New Run</h2>

        <form action="{{ route('runs.store') }}" method="POST" class="space-y-6">
            @csrf 

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Select Runner</label>
                <div class="relative">
                    <select name="user_info" id="user_info_select" required
                            class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all appearance-none cursor-pointer">
                        <option value="" disabled selected>-- Choose a User --</option>
                        <option value="Admin" class="text-[#6b6b4b] font-bold">Admin (Manual Entry)</option>
                        
                        @foreach($users as $user)
                            <option value="{{ $user->user_id }}|{{ $user->username }}">
                                {{ $user->username }} (@ {{ Str::limit($user->user_id, 6) }})
                            </option>
                        @endforeach
                    </select>
                    
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-[#8b8b6b]">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-2" id="manual_name_container" style="display: none;">
                <label class="text-sm font-medium text-[#b0b0a0]">Enter Admin/Runner Name</label>
                <input type="text" name="manual_name" id="manual_name_input" placeholder="e.g. Coach David"
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all placeholder-[#3a3a3a]">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Date</label>
                <input type="date" name="date" required
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all [color-scheme:dark]">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Distance (km)</label>
                    <input type="number" step="0.01" name="distance_km" id="distance_km" placeholder="0.00" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all placeholder-[#3a3a3a]">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Duration (HH:MM:SS)</label>
                    <input type="text" name="time" id="duration_time" placeholder="00:30:00" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] focus:ring-1 focus:ring-[#6b6b4b] transition-all placeholder-[#3a3a3a]">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Pace (Auto-calculated)</label>
                <input type="text" name="pace" id="pace_result" placeholder="6:00 /km" readonly
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-[#8b8b6b] rounded-lg px-4 py-3 focus:outline-none transition-all cursor-not-allowed">
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ url('/') }}" 
                   class="px-6 py-2.5 rounded-lg border border-[#2a2a2a] text-[#b0b0a0] hover:text-white hover:bg-[#2a2a2a] transition-all font-medium text-sm">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-lg bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white font-medium text-sm shadow-lg shadow-[#6b6b4b]/20 transition-all flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Assign Run
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('user_info_select');
    const manualNameContainer = document.getElementById('manual_name_container');
    const manualNameInput = document.getElementById('manual_name_input');
    const distInput = document.getElementById('distance_km');
    const timeInput = document.getElementById('duration_time');
    const paceInput = document.getElementById('pace_result');

    // Toggle Manual Name field
    userSelect.addEventListener('change', function() {
        if (this.value === 'Admin') {
            manualNameContainer.style.display = 'block';
            manualNameInput.setAttribute('required', 'required');
        } else {
            manualNameContainer.style.display = 'none';
            manualNameInput.removeAttribute('required');
            manualNameInput.value = '';
        }
    });

    // Pace Calculation Logic
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