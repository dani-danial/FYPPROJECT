@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-4xl mx-auto mb-8">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-[#8b8b6b] hover:text-white transition-colors group">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
            <span class="text-[10px] font-bold uppercase tracking-widest">Back</span>
        </a>
    </div>

    <div class="max-w-4xl mx-auto bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] overflow-hidden shadow-2xl">
        <div class="h-40 bg-[#6b6b4b]/10 border-b border-[#2a2a2a]"></div>

        <div class="px-12 pb-12">
            <div class="relative -mt-20 mb-6">
                <div class="w-40 h-40 rounded-[2.5rem] overflow-hidden border-8 border-[#1a1a1a] bg-[#6b6b4b] shadow-2xl">
                    @if($user->profile_photo_path)
                        <img src="{{ $user->profile_photo_url }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-5xl text-white font-black">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="mb-10">
                <h1 class="text-5xl font-black text-white tracking-tight leading-none">{{ $user->name }}</h1>
                <p class="text-[#6b6b4b] font-bold uppercase tracking-widest text-sm mt-3">@ {{ $user->username }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 border-t border-[#2a2a2a] pt-10">
                <div class="md:col-span-2 space-y-10">
                    <div class="space-y-4">
                        <h3 class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest border-b border-[#2a2a2a] pb-4">About the Runner</h3>
                        <p class="text-[#b0b0a0] leading-relaxed italic text-lg">
                            {{ $user->about ?? 'This runner hasn\'t added a bio yet.' }}
                        </p>
                    </div>

                    {{-- Expanded Stats Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-[#2a2a2a]">
                            <p class="text-[10px] text-[#4a4a4a] font-bold uppercase mb-2 tracking-widest">Weight</p>
                            <p class="text-white font-bold text-xl">{{ $user->weight_kg ?? '--' }} kg</p>
                        </div>
                        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-[#2a2a2a]">
                            <p class="text-[10px] text-[#4a4a4a] font-bold uppercase mb-2 tracking-widest">Height</p>
                            <p class="text-white font-bold text-xl">{{ $user->height_cm ?? '--' }} cm</p>
                        </div>
                        <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-[#2a2a2a]">
                            <p class="text-[10px] text-[#4a4a4a] font-bold uppercase mb-2 tracking-widest">Base Pace</p>
                            <p class="text-[#6b6b4b] font-black text-xl">{{ $user->base_pace_min_km ?? '--' }} <span class="text-[10px]">/km</span></p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-[#0a0a0a] p-6 rounded-3xl border border-[#2a2a2a] mb-2 text-center">
                        <p class="text-[10px] text-[#4a4a4a] font-bold uppercase tracking-widest mb-1">Athlete Role</p>
                        <p class="text-white font-black uppercase italic">{{ $user->role ?? 'Member' }}</p>
                    </div>

                    @if(Auth::id() !== $user->id)
                        <form action="{{ route('user.follow', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                class="w-full py-5 {{ Auth::user()->following->contains($user->id) ? 'bg-[#2a2a2a] text-[#8b8b6b]' : 'bg-[#6b6b4b] text-white' }} rounded-3xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-[#6b6b4b]/20 flex items-center justify-center gap-3 border border-white/5">
                                @if(Auth::user()->following->contains($user->id))
                                    <i data-lucide="user-minus" class="w-5 h-5"></i> Unfollow Runner
                                @else
                                    <i data-lucide="user-plus" class="w-5 h-5"></i> Follow Runner
                                @endif
                            </button>
                        </form>
                    @else
                        <a href="{{ route('profile.edit') }}" class="w-full py-5 bg-[#2a2a2a] text-white rounded-3xl font-black text-xs uppercase tracking-widest transition-all flex items-center justify-center gap-3 border border-white/5">
                            <i data-lucide="settings" class="w-5 h-5"></i> Edit My Profile
                        </a>
                    @endif
                    
                    @if(Auth::id() !== $user->id)
                        <a href="{{ route('chat.start', $user->id) }}" class="w-full py-5 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-white rounded-3xl font-black text-xs uppercase tracking-widest transition-all flex items-center justify-center gap-3 border border-white/5">
                            <i data-lucide="message-circle" class="w-5 h-5"></i> Send Message
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
@endsection