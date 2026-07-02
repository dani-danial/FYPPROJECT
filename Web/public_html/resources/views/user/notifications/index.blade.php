@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="mb-10">
            <h2 class="text-3xl font-black text-white tracking-tight uppercase">Activity Feed</h2>
            <p class="text-[#6b6b4b] text-[10px] font-bold uppercase tracking-[0.3em] mt-1">LATEST UPDATES & NOTIFICATIONS</p>
        </div>

        <div class="space-y-4">
            @forelse($notifications as $notif)
                @php
                    // 🛠️ COLOR LOGIC: Match the Admin Panel colors
                    $iconColor = 'text-blue-400';
                    $bgColor = 'bg-[#1a1a1a]'; 
                    $borderColor = 'border-[#2a2a2a]';
                    $icon = 'info'; // Default icon

                    switch($notif->type) {
                        case 'warning':
                            $iconColor = 'text-amber-500';
                            $bgColor = 'bg-amber-500/5'; // Very subtle yellow tint
                            $borderColor = 'border-amber-500/20';
                            $icon = 'alert-triangle';
                            break;
                        case 'error':
                            $iconColor = 'text-red-500';
                            $bgColor = 'bg-red-500/5'; // Very subtle red tint
                            $borderColor = 'border-red-500/20';
                            $icon = 'x-circle';
                            break;
                        case 'success':
                            $iconColor = 'text-emerald-500';
                            $bgColor = 'bg-emerald-500/5'; // Very subtle green tint
                            $borderColor = 'border-emerald-500/20';
                            $icon = 'check-circle';
                            break;
                        case 'info':
                        default:
                            $iconColor = 'text-blue-400';
                            $bgColor = 'bg-[#1a1a1a]'; // Keep standard dark for info
                            $borderColor = 'border-blue-500/20';
                            $icon = 'info';
                            break;
                    }

                    // Overwrite border if it's strictly unread to highlight it more
                    if($notif->status === 'unread') {
                        $borderColor = 'border-[#6b6b4b]/60';
                    }
                @endphp

                <div class="p-8 {{ $bgColor }} border {{ $borderColor }} rounded-[2rem] flex items-start gap-6 transition-all shadow-xl shadow-black/20 hover:scale-[1.01]">
                    {{-- Notification Icon --}}
                    <div class="p-4 bg-[#0a0a0a] border border-[#2a2a2a] {{ $iconColor }} rounded-2xl shrink-0">
                        <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="text-white font-black text-lg uppercase tracking-tight">{{ $notif->title }}</h4>
                            <div class="text-right">
                                <span class="block text-[9px] text-[#4a4a4a] font-black uppercase tracking-widest">{{ $notif->created_at->diffForHumans() }}</span>
                                {{-- Optional Type Badge --}}
                                <span class="text-[8px] font-bold uppercase {{ $iconColor }} opacity-80">{{ $notif->type }}</span>
                            </div>
                        </div>
                        <p class="text-[#8b8b6b] text-sm leading-relaxed italic">"{{ $notif->message }}"</p>
                    </div>
                </div>
            @empty
                {{-- Placeholder if no notifications exist --}}
                <div class="py-32 text-center bg-[#151515] border-2 border-dashed border-[#2a2a2a] rounded-[3rem]">
                    <div class="w-16 h-16 bg-[#0a0a0a] rounded-full flex items-center justify-center mx-auto mb-6 text-[#2a2a2a]">
                        <i data-lucide="bell-off" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-white font-black text-xl uppercase tracking-tighter">All caught up!</h3>
                    <p class="text-[#4a4a4a] text-[10px] font-bold uppercase tracking-widest mt-2">No new notifications at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection