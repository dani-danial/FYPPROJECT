@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Users
    </a>

    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Add New User</h2>

        @if ($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg text-sm">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="john_run" required class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="john@example.com" required class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Password</label>
                <input type="password" name="password" required class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Status</label>
                <select name="status" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('users.index') }}" class="px-6 py-2.5 rounded-lg border border-[#2a2a2a] text-[#b0b0a0] hover:text-white transition-all text-sm">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white font-medium text-sm shadow-lg">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection