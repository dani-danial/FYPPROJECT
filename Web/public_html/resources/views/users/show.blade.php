@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- 
        🛠️ FIX: Calculate stats dynamically so they are always up-to-date.
        We query the RunSummary model directly for this specific user.
    --}}
    @php
        $realTotalRuns = \App\Models\RunSummary::where('user_id', $user->id)->count();
        $realTotalDistance = \App\Models\RunSummary::where('user_id', $user->id)->sum('distance_km');
        $lastRun = \App\Models\RunSummary::where('user_id', $user->id)->latest()->first();
        
        // Determine last active time: either the last run time OR the user's login update
        $lastActiveDate = $lastRun ? $lastRun->created_at : $user->updated_at;
    @endphp

    {{-- Back Button --}}
    <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Users
    </a>

    {{-- User Profile Header --}}
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-8 mb-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                {{-- Profile Image --}}
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-[#2a2a2a] flex items-center justify-center text-[#8b8b6b] border-2 border-[#333] overflow-hidden">
                    @if($user->profile_photo_path)
                        <img src="{{ $user->profile_photo_url }}" class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-full shadow" style="max-width:80px; max-height:80px;">
                    @else
                        <i data-lucide="user" class="w-8 h-8"></i>
                    @endif
                </div>
                
                <div>
                    <h2 class="text-3xl font-bold text-white">{{ $user->name }}</h2>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[#8b8b6b] text-sm">{{ '@' . $user->username }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border 
                            {{ $user->status === 'active' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20' }}">
                            {{ $user->status ?? 'Active' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('users.edit', $user->id) }}" class="px-4 py-2 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-white text-sm font-medium rounded-lg border border-[#3a3a3a] transition-colors">
                    Edit Profile
                </a>
            </div>
        </div>

        {{-- Details Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 pt-8 border-t border-[#2a2a2a]">
            <div>
                <label class="text-xs font-medium text-[#6b6b6b] uppercase tracking-wider block mb-1">Email Address</label>
                <div class="text-white text-lg">{{ $user->email }}</div>
            </div>
            <div>
                <label class="text-xs font-medium text-[#6b6b6b] uppercase tracking-wider block mb-1">Joined Date</label>
                <div class="text-white text-lg">{{ $user->created_at->format('M d, Y') }}</div>
            </div>
            <div class="col-span-1 md:col-span-2">
                <label class="text-xs font-medium text-[#6b6b6b] uppercase tracking-wider block mb-1">About Me</label>
                <p class="text-[#b0b0a0] leading-relaxed">
                    {{ $user->about_me ?? 'No bio description available.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Stats Overview (UPDATED WITH REAL DATA) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Total Runs --}}
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-6 text-center">
            <div class="text-[#8b8b6b] mb-1"><i data-lucide="person-standing" class="w-6 h-6 mx-auto mb-2"></i>Total Runs</div>
            <div class="text-3xl font-bold text-white">{{ $realTotalRuns }}</div>
        </div>

        {{-- Total Distance --}}
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-6 text-center">
            <div class="text-[#8b8b6b] mb-1"><i data-lucide="activity" class="w-6 h-6 mx-auto mb-2"></i>Total Distance</div>
            <div class="text-3xl font-bold text-[#6b6b4b]">{{ number_format($realTotalDistance, 1) }} km</div>
        </div>

        {{-- Last Active --}}
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-6 text-center">
            <div class="text-[#8b8b6b] mb-1"><i data-lucide="calendar" class="w-6 h-6 mx-auto mb-2"></i>Last Active</div>
            <div class="text-xl font-bold text-white">
                {{ \Carbon\Carbon::parse($lastActiveDate)->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
@endsection