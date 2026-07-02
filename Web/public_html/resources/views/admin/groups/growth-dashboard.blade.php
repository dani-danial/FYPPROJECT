<div x-data="groupGrowth()" class="space-y-6">
    <!-- Growth Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Member Growth Chart -->
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
            <h3 class="text-white font-bold mb-6">Member Growth (30 Days)</h3>
            <div id="growth-chart" class="w-full h-64" style="background: linear-gradient(180deg, rgba(107, 107, 75, 0.1) 0%, transparent 100%); border-radius: 1rem; display: flex; align-items: flex-end; gap: 0.5rem; padding: 1rem;">
                <!-- Chart bars will be rendered here by Chart.js -->
                <div v-for="i in 30" class="flex-1 rounded-t" :style="`height: ${20 + Math.random()*80}%; background: linear-gradient(to top, #6b6b4b, #7b7b5b);`"></div>
            </div>
            <p class="text-[#8b8b6b] text-xs mt-4 text-center">Total Growth: <span class="text-white font-bold">+342 members</span></p>
        </div>

        <!-- Most Active Groups -->
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6 space-y-4">
            <h3 class="text-white font-bold mb-6">Most Active Groups</h3>
            @forelse($groups ?? [] as $group)
            <div class="p-4 bg-[#0a0a0a] rounded-lg border border-[#2a2a2a] hover:border-[#4a4a4a] transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        @if($group->icon_url)
                        <img src="{{ str_contains($group->icon_url, 'http') ? $group->icon_url : asset('storage/' . $group->icon_url) }}" 
                             class="w-10 h-10 rounded-lg object-cover">
                        @else
                        <div class="w-10 h-10 rounded-lg bg-[#6b6b4b] flex items-center justify-center text-white font-bold">
                            {{ substr($group->name, 0, 1) }}
                        </div>
                        @endif
                        <div>
                            <p class="text-white font-bold text-sm">{{ $group->name }}</p>
                            <p class="text-[#8b8b6b] text-xs">{{ $group->users->count() }} members</p>
                        </div>
                    </div>
                    <span class="text-[#6b6b4b] font-bold text-sm">↑ 23%</span>
                </div>
            </div>
            @empty
            <p class="text-[#8b8b6b] text-center py-8">No groups yet</p>
            @endforelse
        </div>
    </div>

    <!-- Groups List with Challenges -->
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
        <h3 class="text-white font-bold mb-6">All Groups - Challenge Progress</h3>
        
        <div class="space-y-6">
            @forelse($groups ?? [] as $group)
            <div class="p-6 bg-[#0a0a0a] rounded-xl border border-[#2a2a2a] hover:border-[#4a4a4a] transition-all group/card">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-start">
                    <!-- Group Info -->
                    <div class="md:col-span-1">
                        <div class="flex items-center gap-3 mb-4">
                            @if($group->icon_url)
                            <img src="{{ str_contains($group->icon_url, 'http') ? $group->icon_url : asset('storage/' . $group->icon_url) }}" 
                                 class="w-12 h-12 rounded-lg object-cover">
                            @else
                            <div class="w-12 h-12 rounded-lg bg-[#6b6b4b] flex items-center justify-center text-white font-bold text-lg">
                                {{ substr($group->name, 0, 1) }}
                            </div>
                            @endif
                            <div>
                                <p class="text-white font-bold">{{ $group->name }}</p>
                                <p class="text-[#8b8b6b] text-xs">{{ $group->location }}</p>
                            </div>
                        </div>
                        <p class="text-[#8b8b6b] text-xs mb-3">{{ $group->users->count() }} members</p>
                        <button @click="toggleFeatured({{ $group->id }})"
                                class="text-xs px-3 py-1 rounded-lg font-bold transition-colors"
                                :class="featuredGroups.includes({{ $group->id }}) ? 'bg-yellow-500/20 text-yellow-400' : 'bg-[#2a2a2a] text-[#8b8b6b] hover:text-white'">
                            {{ $group->is_featured ? '⭐ Featured' : '☆ Feature' }}
                        </button>
                    </div>

                    <!-- Challenge Progress (Circular) -->
                    <div class="md:col-span-1 flex items-center justify-center">
                        @php
                            $totalDistance = $group->users->sum('distance_km') ?? 0;
                            $target = $group->target_km ?? 1000;
                            $progress = $target > 0 ? min(($totalDistance / $target) * 100, 100) : 0;
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-32 h-32 rounded-full relative flex items-center justify-center bg-[#1a1a1a]">
                                <!-- SVG Circular Progress -->
                                <svg class="w-full h-full" viewBox="0 0 120 120">
                                    <!-- Background circle -->
                                    <circle cx="60" cy="60" r="50" fill="none" stroke="#2a2a2a" stroke-width="8"/>
                                    <!-- Progress circle -->
                                    <circle cx="60" cy="60" r="50" fill="none" stroke="#6b6b4b" stroke-width="8"
                                            stroke-dasharray="314" :stroke-dashoffset="`${314 - (314 * {{ $progress }}/100)}`"
                                            style="transition: stroke-dashoffset 0.3s ease; transform: rotate(-90deg); transform-origin: 60px 60px;"/>
                                </svg>
                                <div class="absolute text-center">
                                    <p class="text-white font-black text-lg" x-text="`${{{ $progress }}.toFixed(0)}%`"></p>
                                    <p class="text-[#8b8b6b] text-xs">Complete</p>
                                </div>
                            </div>
                            <p class="text-[#8b8b6b] text-xs mt-2">Goal: {{ number_format($target) }} km</p>
                        </div>
                    </div>

                    <!-- Progress Stats -->
                    <div class="md:col-span-1">
                        <div class="space-y-3">
                            <div>
                                <p class="text-[#8b8b6b] text-xs uppercase tracking-widest mb-1">Current Progress</p>
                                <p class="text-white font-black text-2xl">{{ number_format($totalDistance, 0) }}</p>
                                <p class="text-[#8b8b6b] text-xs">of {{ number_format($target) }} km</p>
                            </div>
                            <div class="w-full bg-[#1a1a1a] rounded-full h-2 border border-[#2a2a2a] overflow-hidden">
                                <div class="bg-gradient-to-r from-[#6b6b4b] to-[#7b7b5b] h-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="text-[#8b8b6b] text-xs">{{ number_format($target - $totalDistance, 0) }} km remaining</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="md:col-span-1 space-y-2">
                        <a href="{{ route('user.groups.edit', $group->id) }}"
                           class="block text-center px-4 py-2 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-lg font-bold text-xs transition-colors">
                            Edit Group
                        </a>
                        <button class="w-full px-4 py-2 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-[#8b8b6b] rounded-lg font-bold text-xs transition-colors">
                            View Analytics
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-[#8b8b6b]">
                <p>No groups created yet</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Featured Groups Slider (Promotion) -->
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
        <h3 class="text-white font-bold mb-6 flex items-center gap-2">
            <span>⭐ Featured Groups (Dashboard Promotion)</span>
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($featuredGroups ?? [] as $group)
            <div class="p-4 bg-gradient-to-br from-[#6b6b4b]/20 to-[#1a1a1a] rounded-xl border-2 border-yellow-500/30 relative">
                <div class="absolute top-2 right-2">
                    <span class="inline-block px-2 py-1 bg-yellow-500/20 text-yellow-400 rounded text-[10px] font-bold">FEATURED</span>
                </div>
                
                @if($group->icon_url)
                <img src="{{ str_contains($group->icon_url, 'http') ? $group->icon_url : asset('storage/' . $group->icon_url) }}" 
                     class="w-full h-40 rounded-lg object-cover mb-3">
                @else
                <div class="w-full h-40 rounded-lg bg-[#6b6b4b] flex items-center justify-center text-5xl mb-3">
                    {{ substr($group->name, 0, 1) }}
                </div>
                @endif
                
                <p class="text-white font-bold">{{ $group->name }}</p>
                <p class="text-[#8b8b6b] text-xs">{{ $group->users->count() }} members</p>
                
                <button @click="toggleFeatured({{ $group->id }})"
                        class="w-full mt-3 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg font-bold text-xs transition-colors">
                    Unfeature
                </button>
            </div>
            @empty
            <div class="col-span-full p-8 text-center text-[#8b8b6b]">
                <p>No featured groups. Select groups to feature them on the user dashboard.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function groupGrowth() {
    return {
        featuredGroups: @json($featuredGroupIds ?? []),
        
        toggleFeatured(groupId) {
            fetch(`/admin/groups/${groupId}/feature`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            }).then(() => {
                this.featuredGroups = this.featuredGroups.includes(groupId)
                    ? this.featuredGroups.filter(id => id !== groupId)
                    : [...this.featuredGroups, groupId];
                // Optionally reload to reflect changes
                setTimeout(() => location.reload(), 500);
            });
        }
    };
}
</script>
