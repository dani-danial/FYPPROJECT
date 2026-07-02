@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-white tracking-tight">Search Results</h2>
            <p class="text-[#8b8b6b] text-sm uppercase font-bold tracking-widest">Showing results for: "{{ $query }}"</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($users as $user)
                <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] p-6 flex items-center gap-6 hover:border-[#6b6b4b] transition-all group">
                    {{-- Profile Photo --}}
                    <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-[#6b6b4b]/30 group-hover:border-[#6b6b4b] transition-all">
                        @if($user->profile_photo_path)
                            <img src="{{ $user->profile_photo_url }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-[#2a2a2a] flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    {{-- User Info --}}
                    <div>
                        <h4 class="text-lg font-bold text-white group-hover:text-[#6b6b4b] transition-colors">
                            {{ $user->name }}
                        </h4>
                        <p class="text-[#4a4a4a] text-xs font-bold uppercase tracking-widest">
                            @ {{ $user->username }}
                        </p>
                    </div>

                    {{-- View Profile Button --}}
                    <div class="ml-auto flex items-center">
                        {{-- 🛠️ FIXED LINE BELOW: Added ['username' => ...] to fix the error --}}
                        <a href="{{ route('profile.show', ['username' => $user->username]) }}" 
                           class="p-2 bg-[#181818] rounded-full text-[#8b8b6b] hover:text-white hover:bg-[#6b6b4b] transition-all flex items-center justify-center" 
                           title="View Profile">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-[#1a1a1a] border border-dashed border-[#2a2a2a] rounded-[2rem] p-20 text-center">
                    <p class="text-[#4a4a4a] font-bold uppercase tracking-[0.2em]">No runners found matching that username.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection