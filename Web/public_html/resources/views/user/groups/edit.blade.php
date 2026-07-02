@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-4 mb-10">
            <a href="{{ route('user.groups.show', $group->id) }}" class="p-2 bg-[#1a1a1a] rounded-lg text-[#4a4a4a] hover:text-white transition-all">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="text-3xl font-black text-white tracking-tight uppercase">Edit Group</h2>
                <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">Update group profile and settings</p>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-10 shadow-2xl">
            <form action="{{ route('user.groups.update', $group->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PATCH')

                {{-- Uploads Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Group Icon Upload --}}
                    <div class="flex flex-col items-center gap-4">
                        <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">Group Icon</label>
                        <div class="w-32 h-32 rounded-3xl bg-[#0a0a0a] border-2 border-dashed border-[#2a2a2a] overflow-hidden flex items-center justify-center relative group">
                            @if($group->icon_url)
                                <img src="{{ str_contains($group->icon_url, 'http') ? $group->icon_url : url('/serve-image?path=' . $group->icon_url) }}" class="w-full h-full object-cover opacity-60">
                            @else
                                <i data-lucide="image" class="w-8 h-8 text-[#2a2a2a]"></i>
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/60">
                                <p class="text-[10px] font-bold text-white uppercase tracking-widest">Change Icon</p>
                            </div>
                            <input type="file" name="icon" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                    </div>

                    {{-- Group Banner Upload --}}
                    <div class="flex flex-col items-center gap-4">
                        <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">Group Banner</label>
                        <div class="w-full h-32 rounded-3xl bg-[#0a0a0a] border-2 border-dashed border-[#2a2a2a] overflow-hidden flex items-center justify-center relative group">
                            @if($group->banner_url)
                                <img src="{{ str_contains($group->banner_url, 'http') ? $group->banner_url : url('/serve-image?path=' . $group->banner_url) }}" class="w-full h-full object-cover opacity-60">
                            @else
                                <i data-lucide="layout" class="w-8 h-8 text-[#2a2a2a]"></i>
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/60">
                                <p class="text-[10px] font-bold text-white uppercase tracking-widest">Change Banner</p>
                            </div>
                            <input type="file" name="banner" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                    </div>
                </div>

                {{-- Group Name --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest ml-4">Group Name</label>
                    <input type="text" name="name" value="{{ $group->name }}" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-2xl px-6 py-4 text-white text-sm focus:outline-none focus:border-[#6b6b4b] transition-all">
                </div>

                {{-- Location --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest ml-4">City / Location</label>
                    <input type="text" name="location" value="{{ $group->location }}"
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-2xl px-6 py-4 text-white text-sm focus:outline-none focus:border-[#6b6b4b] transition-all">
                </div>

                {{-- Description --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest ml-4">Description</label>
                    <textarea name="description" rows="4" required
                              class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-2xl px-6 py-4 text-white text-sm focus:outline-none focus:border-[#6b6b4b] transition-all resize-none">{{ $group->description }}</textarea>
                </div>

                {{-- Monthly Running Target --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest ml-4">Monthly Running Target (KM)</label>
                    <input type="number" name="target_km" step="1" min="1" value="{{ old('target_km', $group->target_km) }}"
                           placeholder="e.g. 1000"
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-2xl px-6 py-4 text-white text-sm focus:outline-none focus:border-[#6b6b4b] transition-all placeholder-[#4a4a4a]">
                    <p class="text-[#6b6b4b] text-xs mt-2 ml-4">Set the collective kilometer goal for your community this month.</p>
                </div>

                {{-- Submit Button --}}
                <div class="pt-4">
                    <button type="submit" class="w-full py-5 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-2xl font-black uppercase tracking-[0.2em] text-xs transition-all shadow-xl shadow-[#6b6b4b]/20">
                        Update Group Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection