@extends('layouts.app')

@section('content')
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Notifications Management</h2>
            <p class="text-[#8b8b6b] mt-1 text-sm">Send and manage global notifications to all users.</p>
        </div>
        <a href="{{ route('notifications.create') }}" 
           class="bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white px-4 py-2.5 rounded-lg flex items-center justify-center gap-2 transition-all shadow-lg shadow-[#6b6b4b]/20 text-sm font-medium">
            <i data-lucide="send" class="w-4 h-4"></i>
            <span>Send Global Notification</span>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-[#6b6b4b] border border-[#6b6b4b] rounded-xl p-6">
            <h3 class="text-white/70 text-xs font-bold uppercase tracking-wider mb-2">Total Sent</h3>
            <div class="text-4xl font-bold text-white">{{ $totalSent }}</div>
        </div>
        <div class="bg-[#6b6b4b] border border-[#6b6b4b] rounded-xl p-6">
            <h3 class="text-white/70 text-xs font-bold uppercase tracking-wider mb-2">Scheduled</h3>
            <div class="text-4xl font-bold text-white">{{ $totalScheduled }}</div>
        </div>
        <div class="bg-[#6b6b4b] border border-[#6b6b4b] rounded-xl p-6">
            <h3 class="text-white/70 text-xs font-bold uppercase tracking-wider mb-2">Total Recipients</h3>
            <div class="text-4xl font-bold text-white">{{ $totalRecipients }}</div>
        </div>
        <div class="bg-[#6b6b4b] border border-[#6b6b4b] rounded-xl p-6">
            <h3 class="text-white/70 text-xs font-bold uppercase tracking-wider mb-2">This Month</h3>
            <div class="text-4xl font-bold text-white">{{ $thisMonth }}</div>
        </div>
    </div>

    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl p-6">
        <h3 class="text-lg font-bold text-white mb-6">Notification History</h3>

        <div class="space-y-4">
            @foreach($notifications as $notification)
            <div class="p-4 rounded-lg bg-[#0a0a0a] border border-[#2a2a2a] hover:border-[#333] transition-colors group relative">
                <div class="flex items-start gap-4">
                    @php
                        $iconColor = match($notification->type) {
                            'info' => 'text-blue-400 border-blue-500/20 bg-blue-500/10',
                            'warning' => 'text-yellow-400 border-yellow-500/20 bg-yellow-500/10',
                            'success' => 'text-green-400 border-green-500/20 bg-green-500/10',
                            default => 'text-gray-400 border-gray-500/20 bg-gray-500/10',
                        };
                    @endphp
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center border {{ $iconColor }}">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="text-white font-medium">{{ $notification->title }}</h4>
                            <div class="flex gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-medium border {{ $iconColor }}">
                                    {{ $notification->type }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-medium border 
                                    {{ $notification->status === 'sent' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20' }}">
                                    {{ $notification->status }}
                                </span>
                            </div>
                        </div>
                        <p class="text-[#8b8b6b] text-sm mt-1 mb-2">{{ $notification->message }}</p>
                        <div class="flex items-center gap-4 text-xs text-[#6b6b6b]">
                            <div class="flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                {{ $notification->scheduled_at->format('Y-m-d H:i') }}
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="users" class="w-3 h-3"></i>
                                {{ $notification->recipients_count }} recipients
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute right-4 bottom-4 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                    <a href="{{ route('notifications.edit', $notification->id) }}" class="p-1.5 hover:bg-[#2a2a2a] rounded text-[#8b8b6b] hover:text-white transition-colors">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </a>
                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Delete notification?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 hover:bg-red-900/20 rounded text-[#8b8b6b] hover:text-red-400 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection