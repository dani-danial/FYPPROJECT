@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Adjusted route to user groups --}}
    <a href="{{ route('user.groups') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Groups
    </a>

    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Create New Group</h2>

        @if ($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- CRITICAL: enctype added so images actually upload --}}
        <form action="{{ route('user.groups.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">GROUP NAME</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Morning Runners" required
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">CITY / LOCATION</label>
                <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Melaka City" required
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">ABOUT THE GROUP</label>
                <textarea name="description" rows="3" placeholder="What is this group about? (e.g. Weekend morning runs for beginners)"
                          class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all">{{ old('description') }}</textarea>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">MONTHLY RUNNING TARGET (KM) - OPTIONAL</label>
                <input type="number" name="target_km" value="{{ old('target_km') }}" min="0" step="0.1" placeholder="e.g. 100"
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none transition-all">
                <p class="text-xs text-[#6b6b6b]">Set a collective monthly goal for your group members to achieve together</p>
            </div>

            {{-- Image Upload Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">GROUP ICON (OPTIONAL)</label>
                    <input type="file" name="icon" 
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 text-sm focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#6b6b4b]/20 file:text-[#b0b0a0] hover:file:bg-[#6b6b4b]/30">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">GROUP BANNER (OPTIONAL)</label>
                    <input type="file" name="banner" 
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 text-sm focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#6b6b4b]/20 file:text-[#b0b0a0] hover:file:bg-[#6b6b4b]/30">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('user.groups') }}" class="px-6 py-2.5 rounded-lg border border-[#2a2a2a] text-[#b0b0a0] hover:text-white transition-all text-sm">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white font-medium text-sm shadow-lg">Create Group</button>
            </div>
        </form>
    </div>
</div>
@endsection