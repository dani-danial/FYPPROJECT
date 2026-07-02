@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-2xl mx-auto">
        {{-- Back Button --}}
        <a href="{{ route('user.groups') }}" class="inline-flex items-center gap-2 text-[#8b8b6b] hover:text-white transition-colors mb-8 group">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
            <span class="text-xs font-bold uppercase tracking-widest">Back to Communities</span>
        </a>

        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-10 shadow-2xl">
            <h2 class="text-3xl font-black text-white tracking-tight mb-2">Start a Group</h2>
            <p class="text-[#4a4a4a] text-sm mb-10 font-bold uppercase tracking-widest">Build your own running community</p>

            <form action="{{ route('user.groups.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                
                {{-- Group Name --}}
                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Group Name</label>
                    <input type="text" name="name" required placeholder="e.g. Midnight Striders" 
                        style="background-color: black !important; color: white !important;"
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 focus:ring-1 focus:ring-[#6b6b4b] focus:border-[#6b6b4b] outline-none transition-all placeholder-[#4a4a4a]">
                </div>

                {{-- Location --}}
                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">City / Location</label>
                    <input type="text" name="location" placeholder="e.g. Melaka City" 
                        style="background-color: black !important; color: white !important;"
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 focus:ring-1 focus:ring-[#6b6b4b] focus:border-[#6b6b4b] outline-none transition-all placeholder-[#4a4a4a]">
                </div>

                {{-- Description --}}
                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">About the Group</label>
                    <textarea name="description" rows="4" 
                        placeholder="What is this group about? (e.g. Weekend morning runs for beginners)" 
                        style="background-color: black !important; color: white !important;"
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 focus:ring-1 focus:ring-[#6b6b4b] focus:border-[#6b6b4b] outline-none transition-all resize-none placeholder-[#4a4a4a]"></textarea>
                </div>

                {{-- Running Target (Monthly) --}}
                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Monthly Running Target (KM) - Optional</label>
                    <input type="number" name="target_km" step="0.1" min="0" placeholder="e.g. 100" 
                        style="background-color: black !important; color: white !important;"
                        class="w-full border border-[#2a2a2a] rounded-2xl p-5 focus:ring-1 focus:ring-[#6b6b4b] focus:border-[#6b6b4b] outline-none transition-all placeholder-[#4a4a4a]">
                    <p class="text-[#6b6b4b] text-xs">Set a collective monthly goal for your group members to achieve together</p>
                </div>

                {{-- Image Upload Section (Icon & Banner) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Group Icon --}}
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Group Icon (Optional)</label>
                        <input type="file" name="icon" accept="image/*" 
                            class="block w-full text-sm text-[#8b8b6b] file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:bg-[#6b6b4b]/20 file:text-[#6b6b4b] file:font-bold hover:file:bg-[#6b6b4b]/30 cursor-pointer">
                    </div>

                    {{-- Group Banner --}}
                    <div class="space-y-3">
                        <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Group Banner (Optional)</label>
                        <input type="file" name="banner" accept="image/*" 
                            class="block w-full text-sm text-[#8b8b6b] file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:bg-[#6b6b4b]/20 file:text-[#6b6b4b] file:font-bold hover:file:bg-[#6b6b4b]/30 cursor-pointer">
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-6 flex gap-4">
                    <a href="{{ route('user.groups') }}" 
                        class="flex-1 py-5 bg-[#2a2a2a] text-[#8b8b6b] text-center rounded-2xl font-black text-xs uppercase tracking-widest hover:text-white transition-all border border-white/5">
                        Cancel
                    </a>
                    <button type="submit" 
                        class="flex-1 py-5 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-[#6b6b4b]/20">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection