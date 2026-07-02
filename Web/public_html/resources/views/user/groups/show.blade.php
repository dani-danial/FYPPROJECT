@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-6xl mx-auto space-y-8">
        
        {{-- Navigation Header --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('user.groups') }}" class="flex items-center gap-2 text-[#4a4a4a] hover:text-white transition-all text-xs font-bold uppercase tracking-widest group">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:-translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Groups
            </a>
            
            <div class="flex gap-3">
                @if(Auth::id() === $group->creator_id || Auth::user()->role === 'admin')
                    <a href="{{ route('user.groups.members', $group->id) }}" class="px-5 py-2 bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl text-white text-[10px] font-bold uppercase tracking-widest hover:bg-[#2a2a2a] transition-all">
                        Manage Members
                    </a>
                @endif
                
                @if(Auth::id() === $group->creator_id || Auth::user()->role === 'admin')
                    <a href="{{ route('user.groups.edit', $group->id) }}" class="px-5 py-2 bg-[#6b6b4b] rounded-xl text-white text-[10px] font-bold uppercase tracking-widest hover:bg-[#5a5a3f] transition-all shadow-lg shadow-[#6b6b4b]/20">
                        Edit Group
                    </a>
                @endif
            </div>
        </div>

        {{-- Group Profile Header with Banner Background --}}
        <div class="relative border border-[#2a2a2a] rounded-[2.5rem] p-8 md:p-12 overflow-hidden bg-cover bg-center shadow-2xl"
             style="background-image: @if($group->banner_url) url('{{ str_contains($group->banner_url, 'http') ? $group->banner_url : url('app_data/app/public/' . $group->banner_url) }}') @else linear-gradient(to bottom right, #1a1a1a, #0d0d0d) @endif;">
            
            {{-- Base Dark Overlay --}}
            <div class="absolute inset-0 bg-black/40"></div>
            
            <div class="absolute top-0 right-0 w-64 h-64 bg-[#6b6b4b]/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>

            <div class="flex flex-col md:flex-row items-center md:items-start gap-10 relative z-10">
                {{-- Group Icon --}}
                <div class="w-40 h-40 rounded-3xl bg-[#0a0a0a] border border-white/10 overflow-hidden shrink-0 shadow-2xl">
                    @if($group->icon_url)
                        <img src="{{ str_contains($group->icon_url, 'http') ? $group->icon_url : url('app_data/app/public/' . $group->icon_url) }}" 
                             class="w-full h-full object-cover"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($group->name) }}&background=6b6b4b&color=fff'">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-[#6b6b4b] bg-[#6b6b4b]/10 font-black text-5xl">
                            {{ strtoupper(substr($group->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                {{-- 🛠️ UPDATED: Text Container with Dark Opacity Background --}}
                <div class="flex-1 bg-black/50 backdrop-blur-md border border-white/5 p-8 rounded-[2rem]">
                    <div class="flex flex-wrap justify-center md:justify-start gap-3 mb-6">
                        <span class="px-4 py-1.5 bg-emerald-500/20 text-emerald-400 rounded-full text-[10px] font-bold uppercase tracking-widest border border-emerald-500/30 inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                            {{ $group->status }}
                        </span>
                        <span class="px-4 py-1.5 bg-white/5 text-[#8b8b6b] rounded-full text-[10px] font-bold uppercase tracking-widest border border-white/5">
                            Est. {{ \Carbon\Carbon::parse($group->created_date)->format('M Y') }}
                        </span>
                    </div>

                    <h1 class="text-4xl md:text-6xl font-black text-white mb-4 tracking-tight">{{ $group->name }}</h1>
                    <p class="text-[#b0b0a0] text-base leading-relaxed max-w-2xl mb-8 font-medium">
                        {{ $group->description ?? 'A community united by the passion for running and fitness.' }}
                    </p>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-8 pt-6 border-t border-white/5">
                        <div>
                            <p class="text-[10px] font-bold text-[#6b6b4b] uppercase tracking-widest mb-2">Location</p>
                            <p class="text-white font-black text-lg">{{ $group->location ?? 'Global' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-[10px] font-bold text-[#6b6b4b] uppercase tracking-widest mb-2">Members</p>
                            <p class="text-white font-black text-lg">{{ $group->users->count() }}</p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            @if($group->users->contains(auth()->id()))
                                <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-2">Your Status</p>
                                <div class="inline-flex items-center gap-2 text-white font-bold text-sm">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                                    {{ Auth::id() === $group->creator_id ? 'Owner' : 'Member' }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Community Members Section --}}
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-3xl p-8">
            <h3 class="text-lg font-bold text-white mb-6 uppercase tracking-wider text-xs">Community Members</h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center -space-x-4">
                    @forelse($group->users->take(5) as $member)
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#6b6b4b] to-[#4a4a3a] flex items-center justify-center border-2 border-[#0a0a0a] overflow-hidden shadow-lg hover:scale-110 hover:z-10 transition-all cursor-pointer group" title="{{ $member->name }}">
                            @if($member->profile_photo_path)
                                <img src="{{ url('app_data/app/public/' . $member->profile_photo_path) }}" class="w-full h-full object-cover" alt="{{ $member->name }}">
                            @else
                                <span class="text-white font-bold text-sm uppercase">{{ substr($member->name, 0, 1) }}</span>
                            @endif
                        </div>
                    @empty
                    @endforelse
                    
                    @if($group->users->count() > 5)
                        <div class="w-12 h-12 rounded-full bg-[#2a2a2a] flex items-center justify-center border-2 border-[#0a0a0a] text-[#8b8b6b] font-bold text-sm">
                            +{{ $group->users->count() - 5 }}
                        </div>
                    @endif
                </div>
                <div>
                    <p class="text-[#8b8b6b] text-sm">{{ $group->users->count() }} active members</p>
                </div>
            </div>
        </div>

        {{-- Stats and Goal Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Top Runners Section --}}
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-3xl p-8">
                <h3 class="text-sm font-bold text-white mb-6 flex items-center gap-2 uppercase tracking-widest">
                    <i data-lucide="trophy" class="w-4 h-4 text-[#6b6b4b]"></i>
                    Top Runners
                </h3>
                <div class="space-y-3">
                    @forelse($group->users()->orderBy('distance_km', 'desc')->take(5)->get() as $index => $member)
                        <div class="flex items-center gap-4 p-4 bg-[#0d0d0d] rounded-2xl border border-[#2a2a2a]">
                            <div class="w-8 h-8 rounded-full bg-[#6b6b4b]/20 flex items-center justify-center flex-shrink-0 font-bold text-[#6b6b4b]">
                                {{ $index + 1 }}
                            </div>
                            <div class="w-10 h-10 rounded-full bg-[#1a1a1a] flex items-center justify-center overflow-hidden border border-white/5">
                                @if($member->profile_photo_path)
                                    <img src="{{ url('app_data/app/public/' . $member->profile_photo_path) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-white font-bold text-xs uppercase">{{ substr($member->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="text-white font-bold text-sm truncate">{{ $member->name }}</p>
                                <p class="text-[10px] text-[#8b8b6b] uppercase font-bold tracking-tighter">Contributor</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-white font-black text-sm">{{ number_format($member->distance_km ?? 0, 1) }}</p>
                                <p class="text-[10px] text-[#8b8b6b] uppercase font-bold">KM</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-[#8b8b6b] text-sm text-center py-8">No members yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Monthly Goal Progress Section --}}
            <div class="bg-gradient-to-br from-[#1a1a1a] to-[#0d0d0d] border border-[#2a2a2a] rounded-3xl p-8">
                <h3 class="text-sm font-bold text-white mb-6 flex items-center gap-2 uppercase tracking-widest">
                    <i data-lucide="target" class="w-4 h-4 text-[#6b6b4b]"></i>
                    Monthly Goal
                </h3>

                <div class="bg-black/40 rounded-2xl p-6 border border-white/5">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            @if($group->target_km)
                                <h4 class="text-white font-bold text-lg mb-1">Run {{ number_format($group->target_km) }} KM Together</h4>
                            @else
                                <h4 class="text-white font-bold text-lg mb-1">Set a Monthly Goal</h4>
                            @endif
                            <p class="text-[#8b8b6b] text-xs uppercase tracking-tighter">Current Progress</p>
                        </div>
                        <span class="text-[#6b6b4b] font-bold text-[10px] uppercase tracking-widest bg-[#6b6b4b]/10 px-3 py-1 rounded-full border border-[#6b6b4b]/20">Active</span>
                    </div>

                    @php
                        $totalGroupDistance = $group->users->sum('distance_km') ?? 0;
                        $goalDistance = $group->target_km ?? 1000;
                        $progress = $goalDistance > 0 ? min(($totalGroupDistance / $goalDistance) * 100, 100) : 0;
                    @endphp
                    
                    <div class="space-y-4">
                        <div class="w-full bg-[#0a0a0a] rounded-full h-3 overflow-hidden border border-white/5">
                            <div class="bg-gradient-to-r from-[#6b6b4b] to-[#7b7b5b] h-full rounded-full transition-all duration-700" style="width: {{ $progress }}%">
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-white font-black text-2xl">{{ number_format($totalGroupDistance, 0) }}</p>
                                <p class="text-[#8b8b6b] text-[10px] uppercase font-bold tracking-widest">KM Completed</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[#6b6b4b] font-bold text-lg">{{ number_format(max(0, $goalDistance - $totalGroupDistance), 0) }}</p>
                                <p class="text-[#8b8b6b] text-[10px] uppercase font-bold tracking-widest">KM Remaining</p>
                            </div>
                        </div>

                        <div class="text-center pt-2">
                            <p class="text-[#6b6b4b] font-black text-sm">{{ round($progress, 1) }}% COMPLETE</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex justify-center pt-4">
            @if($group->users->contains(auth()->id()))
                @if(Auth::id() !== $group->creator_id)
                    <form action="{{ route('user.groups.leave', $group->id) }}" method="POST" class="w-full max-w-md">
                        @csrf
                        <button type="submit" class="w-full px-8 py-5 bg-red-500/10 border border-red-500/20 hover:bg-red-500/20 text-red-400 rounded-2xl font-bold uppercase tracking-[0.2em] text-xs transition-all duration-300">
                            Leave Community
                        </button>
                    </form>
                @else
                   <div class="text-center px-8 py-4 bg-white/5 rounded-2xl border border-white/5">
                       <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">✓ You are managing this community</p>
                   </div>
                @endif
            @else
                <form action="{{ route('user.groups.join', $group->id) }}" method="POST" class="w-full max-w-md">
                    @csrf
                    <button type="submit" class="w-full px-8 py-5 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-2xl font-black uppercase tracking-[0.2em] text-xs transition-all duration-300 shadow-xl shadow-[#6b6b4b]/20">
                        Join Community
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection