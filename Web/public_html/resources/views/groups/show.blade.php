@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Back Button --}}
    <a href="{{ route('groups.index') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Groups
    </a>

    {{-- Group Header Card --}}
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl overflow-hidden mb-6">
        <div class="p-8">
            <div class="flex flex-col md:flex-row items-start justify-between gap-6">
                <div class="flex items-start gap-6">
                    {{-- FIXED: Logic to display existing group icon --}}
                    <div class="w-24 h-24 rounded-2xl bg-[#2a2a2a] flex items-center justify-center text-[#8b8b6b] border-2 border-[#333] overflow-hidden shadow-lg">
                        @if($group->icon_url)
                            <img src="{{ str_contains($group->icon_url, 'http') ? $group->icon_url : asset('storage/' . $group->icon_url) }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <i data-lucide="users" class="w-10 h-10"></i>
                        @endif
                    </div>
                    
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-3xl font-bold text-white">{{ $group->name }}</h2>
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase tracking-wider border 
                                {{ $group->status === 'active' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20' }}">
                                {{ $group->status }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-[#8b8b6b] text-sm">
                            <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-4 h-4"></i> {{ $group->location }}</span>
                            <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-4 h-4"></i> Since {{ \Carbon\Carbon::parse($group->created_date)->format('M Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3">
                    <a href="{{ route('groups.edit', $group->id) }}" class="px-4 py-2 bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white text-sm font-medium rounded-lg shadow-lg transition-colors flex items-center gap-2">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Edit Group
                    </a>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-[#2a2a2a]">
                <h3 class="text-xs font-bold text-[#6b6b6b] uppercase tracking-wider mb-2">Description</h3>
                <p class="text-[#b0b0a0] leading-relaxed">
                    {{ $group->description ?? 'No description provided for this group.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-6 flex items-center gap-4">
            <div class="p-3 bg-[#2a2a2a] rounded-lg text-[#6b6b4b]">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-[#8b8b6b] text-xs font-bold uppercase">Members</div>
                <div class="text-2xl font-bold text-white">{{ $group->members_count }}</div>
            </div>
        </div>

        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-6 flex items-center gap-4">
            <div class="p-3 bg-[#2a2a2a] rounded-lg text-[#6b6b4b]">
                <i data-lucide="target" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-[#8b8b6b] text-xs font-bold uppercase">Target Distance</div>
                <div class="text-2xl font-bold text-white">{{ $group->target_km ?? 0 }} km</div>
            </div>
        </div>

        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-6 flex items-center gap-4">
            <div class="p-3 bg-[#2a2a2a] rounded-lg text-[#6b6b4b]">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-[#8b8b6b] text-xs font-bold uppercase">Last Updated</div>
                <div class="text-xl font-bold text-white">{{ $group->updated_at ? \Carbon\Carbon::parse($group->updated_at)->diffForHumans() : ($group->created_date ? $group->created_date->diffForHumans() : 'N/A') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection