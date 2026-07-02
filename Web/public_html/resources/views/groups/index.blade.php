@extends('layouts.app')

@section('content')
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Groups Management</h2>
            <p class="text-[#8b8b6b] mt-1 text-sm">Manage running groups and communities.</p>
        </div>
        <a href="{{ route('groups.create') }}" 
           class="bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white px-4 py-2.5 rounded-lg flex items-center justify-center gap-2 transition-all shadow-lg shadow-[#6b6b4b]/20 text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create Group</span>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-[#6b6b4b] border border-[#6b6b4b] rounded-xl p-6">
            <h3 class="text-white/70 text-xs font-bold uppercase tracking-wider mb-2">Total Groups</h3>
            <div class="text-4xl font-bold text-white">{{ $totalGroups }}</div>
        </div>
        <div class="bg-[#6b6b4b] border border-[#6b6b4b] rounded-xl p-6">
            <h3 class="text-white/70 text-xs font-bold uppercase tracking-wider mb-2">Active Groups</h3>
            <div class="text-4xl font-bold text-white">{{ $activeGroups }}</div>
        </div>
        <div class="bg-[#6b6b4b] border border-[#6b6b4b] rounded-xl p-6">
            <h3 class="text-white/70 text-xs font-bold uppercase tracking-wider mb-2">Total Members</h3>
            <div class="text-4xl font-bold text-white">{{ $totalMembers }}</div>
        </div>
    </div>

    <div class="mb-6">
        <form action="{{ route('groups.index') }}" method="GET" class="relative">
            <input type="text" name="search" placeholder="Search groups by name, description, or location..." value="{{ request('search') }}"
                   class="w-full bg-[#1a1a1a] border border-[#2a2a2a] text-white rounded-lg pl-12 pr-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all placeholder-[#3a3a3a]">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i data-lucide="search" class="w-5 h-5 text-[#8b8b6b]"></i>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($groups as $group)
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-6 hover:border-[#6b6b4b] transition-colors group flex flex-col h-full">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-start gap-4">
                    {{-- FIXED: Logic to display existing group icon --}}
                    <div class="w-12 h-12 rounded-lg bg-[#2a2a2a] flex items-center justify-center text-[#8b8b6b] border border-[#333] overflow-hidden">
                        @if($group->icon_url)
                            <img src="{{ str_contains($group->icon_url, 'http') ? $group->icon_url : asset('storage/' . $group->icon_url) }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <i data-lucide="users" class="w-6 h-6"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg leading-tight">{{ $group->name }}</h3>
                        <p class="text-[#8b8b6b] text-sm mt-1 line-clamp-2">{{ $group->description }}</p>
                    </div>
                </div>
                <span class="px-2 py-1 rounded text-xs font-medium uppercase border 
                    {{ $group->status === 'active' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20' }}">
                    {{ $group->status }}
                </span>
            </div>

            <div class="space-y-3 mb-6 pl-16 flex-1">
                <div class="flex items-center text-[#b0b0a0] text-sm">
                    <i data-lucide="users" class="w-4 h-4 mr-2 text-[#6b6b4b]"></i>
                    {{ $group->members_count }} members
                </div>
                <div class="flex items-center text-[#b0b0a0] text-sm">
                    <i data-lucide="map-pin" class="w-4 h-4 mr-2 text-[#6b6b4b]"></i>
                    {{ $group->location }}
                </div>
                <div class="flex items-center text-[#b0b0a0] text-sm">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2 text-[#6b6b4b]"></i>
                    Created: {{ \Carbon\Carbon::parse($group->created_date)->format('Y-m-d') }}
                </div>
            </div>

            <div class="flex gap-3 border-t border-[#2a2a2a] pt-4 mt-auto">
                <a href="{{ route('groups.show', $group->id) }}" class="flex-1 bg-[#2a2a2a] hover:bg-[#333] text-[#b0b0a0] hover:text-white py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2" title="View Details">
                    <i data-lucide="eye" class="w-4 h-4"></i> View
                </a>

                <a href="{{ route('groups.edit', $group->id) }}" class="flex-1 bg-[#2a2a2a] hover:bg-[#333] text-white py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="pencil" class="w-4 h-4"></i> Edit
                </a>
                
                <form action="{{ route('groups.destroy', $group->id) }}" method="POST" onsubmit="return confirm('Delete this group?');" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-900/20 hover:bg-red-900/40 text-red-400 py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@endsection