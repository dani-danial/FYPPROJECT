@extends('layouts.app')

@section('content')
<div class="p-8 space-y-8 bg-[#0a0a0a] min-h-screen">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tight uppercase">Running Groups</h2>
            <p class="text-[#8b8b6b] mt-1 text-sm uppercase font-bold tracking-widest">Find a community that matches your pace.</p>
        </div>
        <a href="{{ route('user.groups.create') }}" class="p-4 bg-[#6b6b4b] rounded-2xl text-white hover:bg-[#7b7b5b] transition-all shadow-lg shadow-[#6b6b4b]/20">
            <i data-lucide="plus" class="w-6 h-6"></i>
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        <span class="text-sm font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($groups as $group)
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-8 flex flex-col items-center text-center hover:border-[#6b6b4b]/50 transition-all group">
            
            {{-- Icon --}}
            <div class="w-20 h-20 bg-[#0a0a0a] rounded-2xl border border-[#2a2a2a] flex items-center justify-center mb-6 overflow-hidden shadow-xl">
                @if($group->icon_url)
                    <img src="{{ str_contains($group->icon_url, 'http') ? $group->icon_url : asset('storage/' . $group->icon_url) }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-[#6b6b4b] bg-[#6b6b4b]/10 font-black text-3xl">
                        {{ strtoupper(substr($group->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            
            {{-- Group Name Link --}}
            <a href="{{ route('user.groups.show', $group->id) }}" class="hover:text-[#6b6b4b] transition-colors decoration-none">
                <h3 class="text-xl font-black text-white mb-2 uppercase tracking-tighter">{{ $group->name }}</h3>
            </a>

            <span class="px-3 py-1 bg-[#0a0a0a] text-[#6b6b4b] rounded-full text-[10px] font-bold uppercase tracking-widest border border-[#6b6b4b]/20 mb-6">
                {{ $group->status }}
            </span>
            
            <p class="text-sm text-[#8b8b6b] leading-relaxed mb-8 h-12 overflow-hidden flex-1 italic">
                "{{ $group->description ?? 'Join us for regular runs.' }}"
            </p>

            {{-- Stats Section --}}
            <div class="w-full flex justify-between px-4 mb-6 border-t border-[#2a2a2a] pt-6">
                <div class="text-center">
                    <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest mb-1">Members</p>
                    <p class="text-white font-black text-lg">{{ $group->users->count() }}</p>
                </div>
                <div class="w-px h-8 bg-[#2a2a2a]"></div>
                <div class="text-center">
                    <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest mb-1">City</p>
                    <p class="text-white font-black text-lg">{{ $group->location ?? 'Global' }}</p>
                </div>
            </div>

            {{-- 👇 NEW VIEW COMMUNITY BUTTON 👇 --}}
            <a href="{{ route('user.groups.show', $group->id) }}" 
               class="w-full py-4 mb-4 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-2xl font-bold text-[10px] uppercase tracking-[0.2em] transition-all shadow-lg shadow-[#6b6b4b]/20 block text-center">
                View Community
            </a>
            {{-- 👆 END NEW BUTTON 👆 --}}

            {{-- Management Action Bar (Creator & Admin) --}}
            @if(Auth::id() === $group->creator_id || Auth::user()->role === 'admin')
            <div class="flex gap-2 w-full mb-4">
                <a href="{{ route('user.groups.members', $group->id) }}" class="flex-1 py-3 bg-[#2a2a2a] rounded-xl text-white text-[10px] font-bold uppercase text-center hover:bg-[#333] tracking-widest transition-all">
                    Manage Members
                </a>
                @if(Auth::id() === $group->creator_id)
                <a href="{{ route('user.groups.edit', $group->id) }}" class="p-3 bg-[#6b6b4b]/20 rounded-xl text-[#6b6b4b] hover:bg-[#6b6b4b] hover:text-white transition-all">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                </a>
                @endif
                <form action="{{ route('user.groups.destroy', $group->id) }}" method="POST" onsubmit="return confirm('Delete this group?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-3 bg-red-900/10 rounded-xl text-red-500 hover:bg-red-500 hover:text-white transition-all">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
            @endif

            {{-- Join / Leave Toggle --}}
            @if($group->users->contains(auth()->id()))
                <form action="{{ route('user.groups.leave', $group->id) }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full py-4 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-2xl font-bold text-[10px] uppercase tracking-[0.2em] transition-all border border-red-500/30">
                        Leave Group
                    </button>
                </form>
            @else
                <form action="{{ route('user.groups.join', $group->id) }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full py-4 bg-[#6b6b4b]/20 hover:bg-[#6b6b4b] text-white rounded-2xl font-bold text-[10px] uppercase tracking-[0.2em] transition-all border border-[#6b6b4b]/30">
                        Join Group
                    </button>
                </form>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection