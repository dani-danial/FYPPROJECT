@extends('layouts.app')

@section('content')
<div class="p-8 space-y-8 bg-[#0a0a0a] min-h-screen">
    
    {{-- 1. HEADER SECTION --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-[#1a1a1a] border border-[#2a2a2a] p-8 rounded-[2.5rem]">
        <div class="flex items-center gap-6">
            <div class="relative">
                <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-[#6b6b4b] shadow-xl shadow-[#6b6b4b]/10">
                    @if(Auth::user()->profile_photo_path)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-[#2a2a2a] flex items-center justify-center text-2xl">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 border-4 border-[#1a1a1a] rounded-full"></div>
            </div>
            <div>
                <h2 class="text-3xl font-black text-white tracking-tight">
                    Welcome back, <span class="text-[#6b6b4b]">{{ Auth::user()->name }}</span>
                </h2>
                <p class="text-[#4a4a4a] text-[10px] font-bold uppercase tracking-[0.2em] mt-1">
                    @ {{ Auth::user()->username ?? 'runner' }} • Premium Athlete
                </p>
            </div>
        </div>
        <a href="{{ route('profile.edit') }}" class="px-6 py-3 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-white text-xs font-bold uppercase tracking-widest rounded-xl transition-all border border-white/5">
            Edit Profile
        </a>
    </div>

    {{-- 2. Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-[#6b6b4b]/20 border border-[#6b6b4b]/30 rounded-2xl p-6 hover:bg-[#6b6b4b]/30 transition-all">
            <p class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest mb-1">My Total Runs</p>
            <h3 class="text-5xl font-black text-white">{{ $myRuns }}</h3>
        </div>
        <div class="bg-[#6b6b4b]/20 border border-[#6b6b4b]/30 rounded-2xl p-6 hover:bg-[#6b6b4b]/30 transition-all">
            <p class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest mb-1">Total Distance</p>
            <h3 class="text-5xl font-black text-white">{{ number_format($myDistance, 1) }} <span class="text-lg text-[#8b8b6b]">km</span></h3>
        </div>
        <div class="bg-[#6b6b4b]/20 border border-[#6b6b4b]/30 rounded-2xl p-6 hover:bg-[#6b6b4b]/30 transition-all">
            <p class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest mb-1">Following</p>
            <h3 class="text-5xl font-black text-white">{{ $followingCount }}</h3>
        </div>
        <div class="bg-[#6b6b4b]/20 border border-[#6b6b4b]/30 rounded-2xl p-6 hover:bg-[#6b6b4b]/30 transition-all">
            <p class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest mb-1">Followers</p>
            <h3 class="text-5xl font-black text-white">{{ $followersCount }}</h3>
        </div>
    </div>

    {{-- 3. Main Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-5 space-y-8">
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] p-6">
                <h3 class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-3 h-3"></i> About Me
                </h3>
                <p class="text-sm text-[#b0b0a0] leading-relaxed italic">{{ Auth::user()->about ?? 'No bio added yet.' }}</p>
            </div>
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold flex items-center gap-2 text-white">
                        <i data-lucide="calendar" class="w-5 h-5 text-[#6b6b4b]"></i> New Events
                    </h2>
                    <a href="{{ route('user.events') }}" class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest transition-colors">View All</a>
                </div>
                @foreach($upcomingEvents as $event)
                <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-5 hover:border-[#6b6b4b]/50 transition-all group">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="font-bold text-white group-hover:text-[#6b6b4b] transition-colors">{{ $event->title }}</h4>
                        </div>
                        <span class="bg-[#0a0a0a] text-[#8b8b6b] text-[10px] font-bold px-3 py-1 rounded-full border border-[#2a2a2a]">{{ $event->distance_km }} km</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="lg:col-span-7 space-y-6">
            <h2 class="text-lg font-bold text-white">Friends' Posts</h2>
            @foreach($friendPosts as $post)
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6 transition-all">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full overflow-hidden border border-[#6b6b4b]/20">
                        @if($post->user->profile_photo_path)
                            <img src="{{ asset('storage/' . $post->user->profile_photo_path) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-[#6b6b4b]/20 flex items-center justify-center text-[#6b6b4b] font-bold">
                                {{ strtoupper(substr($post->user->name ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">{{ $post->user->name }}</h4>
                        <p class="text-[10px] text-[#4a4a4a] font-bold uppercase tracking-widest">{{ $post->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <p class="text-sm text-[#b0b0a0] leading-relaxed mb-6">{{ $post->content }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
@endsection