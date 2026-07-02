<div x-data="eventKanban()" class="space-y-6">
    <!-- Global Map Overview -->
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl overflow-hidden h-96">
        <div id="event-map" class="w-full h-full" style="background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%); display: flex; align-items: center; justify-content: center;">
            <p class="text-[#8b8b6b] text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-4 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>
                </svg>
                Malaysia Event Map (requires Leaflet.js)
            </p>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $columns = [
                ['title' => 'Pending Approval', 'status' => 'pending', 'color' => 'yellow', 'icon' => 'clock'],
                ['title' => 'Active', 'status' => 'active', 'color' => 'emerald', 'icon' => 'check-circle'],
                ['title' => 'Completed', 'status' => 'completed', 'color' => 'blue', 'icon' => 'flag'],
                ['title' => 'Cancelled', 'status' => 'cancelled', 'color' => 'red', 'icon' => 'x-circle'],
            ];
        @endphp

        @foreach($columns as $column)
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-4 flex flex-col h-96">
            <!-- Column Header -->
            <div class="pb-4 border-b border-[#2a2a2a]">
                <div class="flex items-center gap-2 mb-2">
                    @if($column['icon'] === 'clock')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-{{ $column['color'] }}-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    @elseif($column['icon'] === 'check-circle')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-{{ $column['color'] }}-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    @elseif($column['icon'] === 'flag')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-{{ $column['color'] }}-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="4 15 4 3 20 3 20 15"></polyline><line x1="4" y1="15" x2="8" y2="21"></line>
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-{{ $column['color'] }}-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    @endif
                    <h3 class="text-white font-bold">{{ $column['title'] }}</h3>
                </div>
                <p class="text-[#8b8b6b] text-xs" x-text="`${eventsByStatus['{{ $column['status'] }}'] || 0} events`"></p>
            </div>

            <!-- Cards Container (Scrollable) -->
            <div class="flex-1 overflow-y-auto space-y-3 mt-4">
                @foreach($events ?? [] as $event)
                    @if($event->status === $column['status'])
                    <div class="bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg p-4 hover:border-[#4a4a4a] transition-all cursor-grab group">
                        <!-- Run Type Badge -->
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase"
                                  style="background-color: 
                                    @if($event->run_type === 'Road Run') #0369a1
                                    @elseif($event->run_type === 'Trail Run') #059669
                                    @else #7c3aed
                                    @endif 20;">
                                {{ $event->run_type }}
                            </span>
                        </div>

                        <h4 class="text-white font-bold text-sm mb-2">{{ $event->title }}</h4>
                        
                        <!-- Participant Analytics -->
                        <div class="bg-[#1a1a1a] rounded p-2 mb-3 text-xs">
                            <div class="flex justify-between items-center text-[#8b8b6b] mb-1">
                                <span>Participants</span>
                                <span class="text-white font-bold" x-text="`${Math.floor(Math.random()*50)}/${event.max_participants || 100}`"></span>
                            </div>
                            <div class="w-full bg-[#0a0a0a] rounded h-1.5">
                                <div class="bg-[#6b6b4b] h-full rounded" :style="`width: ${Math.random()*100}%`"></div>
                            </div>
                        </div>

                        <div class="space-y-1 text-[#8b8b6b] text-xs">
                            <p>📅 {{ \Carbon\Carbon::parse($event->date)->format('M d') }}</p>
                            <p>📍 {{ $event->location }}</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-3 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('events.show', $event->id) }}" 
                               class="flex-1 text-center px-2 py-1 bg-[#6b6b4b]/20 text-[#6b6b4b] rounded text-xs font-bold hover:bg-[#6b6b4b]/40 transition-colors">
                                View
                            </a>
                            @if($column['status'] === 'pending')
                            <button @click="approveEvent({{ $event->id }})"
                                    class="flex-1 px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded text-xs font-bold hover:bg-emerald-500/30 transition-colors">
                                Approve
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach

                <!-- Empty State -->
                @if(!$events->where('status', $column['status'])->count())
                <div class="flex items-center justify-center h-32 text-[#4a4a4a]">
                    <p class="text-sm text-center">No events in this stage</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Event Analytics Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
            <p class="text-[#8b8b6b] text-xs uppercase font-bold mb-2">Total Events</p>
            <p class="text-3xl font-black text-white">{{ $events->count() ?? 0 }}</p>
        </div>
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
            <p class="text-[#8b8b6b] text-xs uppercase font-bold mb-2">Pending Approval</p>
            <p class="text-3xl font-black text-yellow-400">{{ $events->where('status', 'pending')->count() ?? 0 }}</p>
        </div>
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
            <p class="text-[#8b8b6b] text-xs uppercase font-bold mb-2">Active Now</p>
            <p class="text-3xl font-black text-emerald-400">{{ $events->where('status', 'active')->count() ?? 0 }}</p>
        </div>
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
            <p class="text-[#8b8b6b] text-xs uppercase font-bold mb-2">Completed</p>
            <p class="text-3xl font-black text-blue-400">{{ $events->where('status', 'completed')->count() ?? 0 }}</p>
        </div>
    </div>
</div>

<script>
function eventKanban() {
    return {
        eventsByStatus: {
            'pending': {{ $events->where('status', 'pending')->count() ?? 0 }},
            'active': {{ $events->where('status', 'active')->count() ?? 0 }},
            'completed': {{ $events->where('status', 'completed')->count() ?? 0 }},
            'cancelled': {{ $events->where('status', 'cancelled')->count() ?? 0 }},
        },
        
        approveEvent(eventId) {
            fetch(`/admin/events/${eventId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            }).then(() => location.reload());
        }
    };
}
</script>
