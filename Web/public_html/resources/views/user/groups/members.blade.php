@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-black text-white tracking-tight">{{ $group->name }}</h2>
                <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">Member Management</p>
            </div>
            <a href="{{ route('user.groups') }}" class="text-[#4a4a4a] hover:text-white transition-colors text-xs font-bold uppercase tracking-widest bg-[#1a1a1a] px-4 py-2 rounded-lg border border-[#2a2a2a]">
                Back to Groups
            </a>
        </div>

        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] overflow-hidden shadow-2xl">
            <table class="w-full text-left">
                <thead class="bg-[#111] border-b border-[#2a2a2a] text-[10px] text-[#4a4a4a] font-bold uppercase tracking-widest">
                    <tr>
                        <th class="px-8 py-5">Runner</th>
                        <th class="px-8 py-5">Role</th>
                        <th class="px-8 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a2a]">
                    @foreach($group->users as $member)
                    <tr class="hover:bg-[#202020] transition-colors group">
                        <td class="px-8 py-5 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-[#6b6b4b] flex items-center justify-center text-white font-black text-sm border border-[#2a2a2a] overflow-hidden">
                                @if($member->profile_photo_path)
                                    <img src="{{ $member->profile_photo_url }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr($member->name, 0, 1) }}
                                @endif
                            </div>
                            
                            <div>
                                <div class="text-white font-bold text-sm">{{ $member->name }}</div>
                                <div class="text-[10px] text-[#4a4a4a]">{{ $member->email }}</div>
                            </div>
                        </td>
                        
                        <td class="px-8 py-5">
                            {{-- 
                                🛠️ DEBUGGING: This line creates a comparison logic that works 
                                even if one ID is a string ("1") and the other is a number (1).
                            --}}
                            @if($member->id == $group->creator_id)
                                <span class="px-3 py-1 rounded-full bg-[#6b6b4b]/10 border border-[#6b6b4b]/20 text-[#6b6b4b] text-[9px] font-bold uppercase tracking-widest">
                                    Owner
                                </span>
                            @else
                                <span class="text-[9px] text-[#4a4a4a] font-bold uppercase tracking-widest">
                                    Member
                                </span>
                                {{-- 🛠️ DEBUGGER: If it says Member but should be Owner, uncomment the line below to see why --}}
                                {{-- <span class="text-[8px] text-red-500 block">User: {{ $member->id }} | Creator: {{ $group->creator_id }}</span> --}}
                            @endif
                        </td>

                        <td class="px-8 py-5 text-right">
                            @if(Auth::id() == $group->creator_id && $member->id != $group->creator_id)
                                <form action="{{ route('user.groups.kick', [$group->id, $member->id]) }}" method="POST" onsubmit="return confirm('Remove this runner from the group?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400 text-[10px] font-bold uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">
                                        Remove Runner
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection