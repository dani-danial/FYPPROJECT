@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen space-y-8">
    
    {{-- Unified Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tight">Users Management</h2>
            <p class="text-[#8b8b6b] mt-1 text-sm uppercase font-bold tracking-widest">System Access Control</p>
        </div>
        <a href="{{ route('users.create') }}" 
           class="bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white px-6 py-3 rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-[#6b6b4b]/20 text-xs font-bold uppercase tracking-widest">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add User</span>
        </a>
    </div>

    {{-- Stats Cards: System-wide totals --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-[#6b6b4b]/10 border border-[#2a2a2a] rounded-[2rem] p-6 transition-all hover:border-[#6b6b4b]/40">
            <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest mb-2">Total Users</p>
            <h3 class="text-4xl font-black text-white">{{ $totalUsers }}</h3>
        </div>
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] p-6 transition-all hover:border-[#6b6b4b]/40">
            <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest mb-2">Active Users</p>
            <h3 class="text-4xl font-black text-white">{{ $activeUsers }}</h3>
        </div>
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] p-6 transition-all hover:border-[#6b6b4b]/40">
            <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest mb-2">Total Runs</p>
            <h3 class="text-4xl font-black text-white">{{ $totalRuns }}</h3>
        </div>
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] p-6 transition-all hover:border-[#6b6b4b]/40">
            <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest mb-2">Total Distance</p>
            <h3 class="text-4xl font-black text-white">{{ number_format($totalDistance, 1) }} <span class="text-lg text-[#8b8b6b]">km</span></h3>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-widest flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Search Bar --}}
    <div class="max-w-2xl">
        <form action="{{ route('users.index') }}" method="GET" class="relative">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#4a4a4a]"></i>
            <input type="text" name="search" placeholder="Search by name, email, or username..." value="{{ request('search') }}"
                   style="background-color: black !important; color: white !important;"
                   class="w-full border border-[#2a2a2a] rounded-xl py-3 pl-12 pr-4 text-sm focus:outline-none focus:border-[#6b6b4b] transition-all placeholder-[#3a3a3a]">
        </form>
    </div>

    {{-- User Table --}}
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#151515] text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest border-b border-[#2a2a2a]">
                    <tr>
                        <th class="px-8 py-4">Runner</th>
                        <th class="px-8 py-4">Email</th>
                        <th class="px-8 py-4 text-center">Runs</th>
                        <th class="px-8 py-4 text-center">Distance</th>
                        <th class="px-8 py-4">Status</th>
                        <th class="px-8 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a2a]">
                    @foreach($users as $user)
                    <tr class="hover:bg-[#202020] transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#6b6b4b] flex items-center justify-center text-white font-bold text-sm border border-[#2a2a2a] overflow-hidden">
                                    @if($user->profile_photo_path)
                                        <img src="{{ $user->profile_photo_url }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white">{{ $user->name }}</div>
                                    <div class="text-[10px] text-[#4a4a4a] uppercase tracking-widest">{{ '@' . ($user->username ?? 'user_' . $user->id) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-sm text-[#8b8b6b]">{{ $user->email }}</td>
                        
                        {{-- 🛠️ FIXED: Using correct DB columns total_runs and distance_km --}}
                        <td class="px-8 py-5 text-center text-sm font-bold text-white">
                            {{ $user->total_runs ?? 0 }}
                        </td>
                        <td class="px-8 py-5 text-center text-sm font-bold text-[#6b6b4b]">
                            {{ number_format($user->distance_km ?? 0, 1) }} km
                        </td>

                        <td class="px-8 py-5">
                            <span class="px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest border 
                                {{ $user->status === 'active' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 
                                   (in_array($user->status, ['banned', 'ban']) ? 'bg-red-950/40 text-red-500 border-red-800/30' : 'bg-red-500/10 text-red-400 border-red-500/20') }}">
                                {{ $user->status }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('users.show', $user->id) }}" class="p-2 bg-[#2a2a2a] rounded-lg text-[#8b8b6b] hover:text-white transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('users.edit', $user->id) }}" class="p-2 bg-[#2a2a2a] rounded-lg text-[#8b8b6b] hover:text-white transition-colors">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete this runner?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 bg-red-900/10 rounded-lg text-red-500 hover:bg-red-900/20 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection