@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('notifications.index') }}" class="inline-flex items-center text-sm text-[#8b8b6b] hover:text-white mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Notifications
    </a>

    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Create New Notification</h2>

        <form action="{{ route('notifications.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Notification Title</label>
                <input type="text" name="title" placeholder="e.g. New Event Alert" required
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-[#b0b0a0]">Message Content</label>
                <textarea name="message" rows="3" required placeholder="Write your message here..."
                          class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Type</label>
                    <select name="type" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                        <option value="info">Info (Blue)</option>
                        <option value="warning">Warning (Yellow)</option>
                        <option value="success">Success (Green)</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Status</label>
                    <select name="status" class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                        <option value="sent">Send Immediately</option>
                        <option value="scheduled">Schedule for Later</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Schedule Date/Time</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ now()->format('Y-m-d\TH:i') }}"
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b] [color-scheme:dark]">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#b0b0a0]">Recipient Count (Simulated)</label>
                    <input type="number" name="recipients_count" value="0"
                           class="w-full bg-[#0a0a0a] border border-[#2a2a2a] text-white rounded-lg px-4 py-3 focus:outline-none focus:border-[#6b6b4b]">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white font-medium text-sm shadow-lg">Send Notification</button>
            </div>
        </form>
    </div>
</div>
@endsection