@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('groups.index') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Groups
    </a>

    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Edit Group</h2>

        @if ($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('groups.update', $group->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Group Name</label>
                <input type="text" name="name" value="{{ old('name', $group->name) }}" required
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none transition-all">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Description</label>
                <textarea name="description" rows="3"
                          class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none transition-all">{{ old('description', $group->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Change Icon</label>
                    <input type="file" name="icon" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 text-sm focus:outline-none">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Change Banner</label>
                    <input type="file" name="banner" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 text-sm focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Location</label>
                    <input type="text" name="location" value="{{ old('location', $group->location) }}" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Created Date</label>
                    <input type="text" value="{{ \Carbon\Carbon::parse($group->created_date)->format('Y-m-d') }}" disabled
                           class="w-full bg-[#151515] border border-[#2a2a2a] text-[#6b6b6b] rounded-lg px-4 py-3 cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Target KM</label>
                    <input type="number" name="target_km" value="{{ old('target_km', $group->target_km) }}" step="0.1" required
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Status</label>
                    <select name="status" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none">
                        <option value="active" {{ old('status', $group->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $group->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('groups.index') }}" class="px-6 py-2.5 rounded-lg border border-[#2a2a2a] text-[#b0b0a0] hover:text-white transition-all text-sm">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white font-medium text-sm shadow-lg">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection