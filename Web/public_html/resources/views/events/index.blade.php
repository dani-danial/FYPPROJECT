@extends('layouts.app')

@section('content')
<div class="p-8 space-y-8 bg-[#0a0a0a] min-h-screen">
    
    {{-- 1. ADMIN HEADER --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-black text-white tracking-tight uppercase leading-none">Events Management</h1>
            <p class="text-[#6b6b4b] mt-2 text-xs font-bold uppercase tracking-[0.3em]">Administrative Control Panel</p>
        </div>
        <a href="{{ route('events.create') }}" 
           class="bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white px-8 py-5 rounded-2xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-[#6b6b4b]/20 text-xs font-black uppercase tracking-widest group">
            <i data-lucide="plus" class="w-5 h-5 group-hover:rotate-90 transition-transform"></i>
            <span>Create Official Event</span>
        </a>
    </div>

    {{-- 2. MANAGEMENT STATS (Keeping existing logic) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-[#6b6b4b] rounded-[2rem] p-8 shadow-xl border border-white/5">
            <h3 class="text-[#d0d0c0] text-[10px] font-black uppercase tracking-widest mb-2">Total Events</h3>
            <div class="text-5xl font-black text-white tracking-tighter">{{ $events->count() }}</div>
        </div>
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] p-8">
            <h3 class="text-[#4a4a4a] text-[10px] font-black uppercase tracking-widest mb-2">Upcoming Runs</h3>
            <div class="text-5xl font-black text-white tracking-tighter">{{ $events->where('status', 'upcoming')->count() }}</div>
        </div>
    </div>

    {{-- 3. MANAGEMENT GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($events as $event)
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-8 flex flex-col group hover:border-[#6b6b4b]/40 transition-all shadow-xl shadow-black/40">
            
            {{-- EVENT HEADER WITH ICON --}}
            <div class="flex justify-between items-start mb-6">
                <div class="flex items-center gap-4">
                    {{-- 🆕 EVENT LOGO PREVIEW --}}
                    <div class="w-14 h-14 rounded-2xl bg-[#0a0a0a] border border-[#2a2a2a] overflow-hidden flex-shrink-0 shadow-inner flex items-center justify-center">
                        @if($event->logo_path)
                            <img src="{{ $event->logo_path }}" class="w-full h-full object-cover">
                        @else
                            <i data-lucide="image" class="w-5 h-5 text-[#2a2a2a]"></i>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-white text-xl font-black tracking-tight uppercase leading-none">{{ $event->title }}</h3>
                        <div class="flex gap-2 mt-2">
                            <span class="text-[9px] text-[#6b6b4b] font-black uppercase tracking-widest">{{ $event->run_type }}</span>
                            <span class="text-[9px] text-white bg-[#6b6b4b] px-2 py-0.5 rounded font-black uppercase tracking-widest">{{ $event->runner_tier }} TIER</span>
                        </div>
                    </div>
                </div>
                
                <span class="px-3 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border
                    {{ $event->status === 'upcoming' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-green-500/10 text-green-400 border-green-500/20' }}">
                    {{ $event->status }}
                </span>
            </div>

            <div class="space-y-4 mb-8 flex-1 text-[#b0b0a0]">
                <div class="flex items-center gap-3 text-xs font-bold">
                    <i data-lucide="calendar" class="w-4 h-4 text-[#6b6b4b]"></i>
                    {{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}
                </div>
                <div class="flex items-center gap-3 text-xs font-bold">
                    <i data-lucide="map-pin" class="w-4 h-4 text-[#6b6b4b]"></i>
                    <span class="truncate">{{ $event->location }} ({{ $event->state }})</span>
                </div>
                <div class="flex items-center gap-3 text-xs font-bold">
                    <i data-lucide="zap" class="w-4 h-4 text-[#6b6b4b]"></i>
                    Distance: {{ number_format($event->distance_km, 1) }} km
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-6 border-t border-[#2a2a2a]">
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('events.show', $event->id) }}" class="flex items-center justify-center gap-2 py-4 bg-[#2a2a2a] hover:bg-[#333] text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                        <i data-lucide="eye" class="w-4 h-4"></i> View
                    </a>
                    <a href="{{ route('events.edit', $event->id) }}" class="flex items-center justify-center gap-2 py-4 bg-[#2a2a2a] hover:bg-[#333] text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Edit
                    </a>
                </div>
                <form action="{{ route('events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Delete permanently?')" class="w-full">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full bg-red-900/10 hover:bg-red-500 text-red-500 hover:text-white py-4 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-red-900/20 flex items-center justify-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Delete Event
                    </button>
                </form>
            </div>
        </div>
        @empty
            <div class="col-span-full py-40 text-center bg-[#151515] border-2 border-dashed border-[#2a2a2a] rounded-[3rem]">
                <p class="text-[#4a4a4a] font-black uppercase tracking-[0.3em]">No official events found</p>
            </div>
        @endforelse
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });</script>
@endsection