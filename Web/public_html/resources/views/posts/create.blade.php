@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('posts.index') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Posts
    </a>

    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Simulate New Post</h2>

        @if ($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- TARGET GROUP --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Target Group <span class="text-red-400">*</span></label>
                <div class="relative">
                    <select name="group_id" required 
                            class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 appearance-none focus:outline-none focus:border-[#6b6b4b]">
                        <option value="" disabled selected>Select a group to post in...</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-[#6b6b6b]">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            {{-- SELECT USER SHORTCUT --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Simulate User (Optional)</label>
                <div class="relative">
                    <select id="userSelect" name="user_id" 
                            class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 appearance-none focus:outline-none focus:border-[#6b6b4b]">
                        <option value="" selected>Custom / Manual Entry</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" 
                                    data-name="{{ $user->name }}" 
                                    data-username="{{ $user->username }}">
                                {{ $user->name }} (@ {{ $user->username }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-[#6b6b6b]">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
                <p class="text-xs text-[#6b6b6b]">Select a real user to auto-fill details, or type manually below.</p>
            </div>

            {{-- AUTHOR DETAILS (Auto-filled or Manual) --}}
            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Author Name</label>
                    <input type="text" id="authorName" name="author_name" value="Admin User" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Author Username</label>
                    <input type="text" id="authorUsername" name="author_username" value="admin" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Post Content</label>
                <textarea name="content" rows="4" required placeholder="What's on your mind?"
                          class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">{{ old('content') }}</textarea>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Attach Image (Optional)</label>
                <input type="file" name="image" accept="image/*"
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-[#b0b0a0] rounded-lg px-4 py-3">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Category</label>
                    <select name="category" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                        <option value="general">General</option>
                        <option value="achievement">Achievement</option>
                        <option value="question">Question</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Flag Status</label>
                    <select name="is_flagged" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                        <option value="0">Clean</option>
                        <option value="1">Flagged</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white font-medium text-sm shadow-lg transition-all">
                    Post Content
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT TO AUTO-FILL DATA --}}
<script>
    document.getElementById('userSelect').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const name = selectedOption.getAttribute('data-name');
        const username = selectedOption.getAttribute('data-username');

        if (name && username) {
            document.getElementById('authorName').value = name;
            document.getElementById('authorUsername').value = username;
        } else {
            // Optional: Reset to Admin or leave as is if "Custom" is selected
            // document.getElementById('authorName').value = "Admin User";
            // document.getElementById('authorUsername').value = "admin";
        }
    });
</script>
@endsection