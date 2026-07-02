<div x-data="notificationBuilder()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Template Builder (Left) -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Template Selection -->
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
            <h3 class="text-white font-bold mb-4">Notification Template</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button @click="selectedTemplate = 'success'"
                        class="p-4 rounded-xl border-2 transition-all flex items-center gap-3"
                        :class="selectedTemplate === 'success' ? 'border-emerald-500 bg-emerald-500/10' : 'border-[#2a2a2a] hover:border-[#4a4a4a]'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div class="text-left">
                        <p class="text-white font-bold text-sm">Success</p>
                        <p class="text-[#8b8b6b] text-xs">Good news & updates</p>
                    </div>
                </button>

                <button @click="selectedTemplate = 'warning'"
                        class="p-4 rounded-xl border-2 transition-all flex items-center gap-3"
                        :class="selectedTemplate === 'warning' ? 'border-yellow-500 bg-yellow-500/10' : 'border-[#2a2a2a] hover:border-[#4a4a4a]'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div class="text-left">
                        <p class="text-white font-bold text-sm">Warning</p>
                        <p class="text-[#8b8b6b] text-xs">Important alerts</p>
                    </div>
                </button>

                <button @click="selectedTemplate = 'system'"
                        class="p-4 rounded-xl border-2 transition-all flex items-center gap-3"
                        :class="selectedTemplate === 'system' ? 'border-blue-500 bg-blue-500/10' : 'border-[#2a2a2a] hover:border-[#4a4a4a]'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="2" y1="17" x2="22" y2="17"></line><line x1="6" y1="21" x2="18" y2="21"></line>
                    </svg>
                    <div class="text-left">
                        <p class="text-white font-bold text-sm">System Alert</p>
                        <p class="text-[#8b8b6b] text-xs">Maintenance & system</p>
                    </div>
                </button>
            </div>
        </div>

        <!-- Message Builder -->
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6 space-y-4">
            <h3 class="text-white font-bold">Compose Message</h3>
            
            <div>
                <label class="block text-[#8b8b6b] text-sm mb-2">Title</label>
                <input type="text" x-model="title" placeholder="e.g. New Marathon Event Available"
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-4 py-3 text-white placeholder-[#4a4a4a] focus:border-[#6b6b4b] transition-colors">
            </div>

            <div>
                <label class="block text-[#8b8b6b] text-sm mb-2">Message</label>
                <textarea x-model="message" rows="4" placeholder="Your message here..."
                          class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-4 py-3 text-white placeholder-[#4a4a4a] focus:border-[#6b6b4b] transition-colors resize-none"></textarea>
            </div>

            <!-- Preview -->
            <div class="mt-6 p-4 rounded-xl" :class="selectedTemplate === 'success' ? 'bg-emerald-500/10 border border-emerald-500/30' : selectedTemplate === 'warning' ? 'bg-yellow-500/10 border border-yellow-500/30' : 'bg-blue-500/10 border border-blue-500/30'">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0"
                         :class="selectedTemplate === 'success' ? 'text-emerald-400' : selectedTemplate === 'warning' ? 'text-yellow-400' : 'text-blue-400'"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <template x-if="selectedTemplate === 'success'">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </template>
                        <template x-if="selectedTemplate === 'warning'">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </template>
                        <template x-if="selectedTemplate === 'system'">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="2" y1="17" x2="22" y2="17"></line>
                        </template>
                    </svg>
                    <div class="flex-1">
                        <p class="text-white font-bold" x-text="title || 'Notification Title'"></p>
                        <p class="text-[#b0b0a0] text-sm mt-1" x-text="message || 'Your message will appear here...'"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Target Selection (Right) -->
    <div class="space-y-6">
        <!-- Target Segment Selection -->
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
            <h3 class="text-white font-bold mb-4">Target Audience</h3>
            
            <div class="space-y-3">
                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#0a0a0a] cursor-pointer transition-colors">
                    <input type="checkbox" x-model="targets" value="all" class="w-4 h-4 rounded accent-[#6b6b4b]">
                    <div>
                        <p class="text-white font-semibold text-sm">All Users</p>
                        <p class="text-[#8b8b6b] text-xs">Send to entire platform</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#0a0a0a] cursor-pointer transition-colors">
                    <input type="checkbox" x-model="targets" value="runners" class="w-4 h-4 rounded accent-[#6b6b4b]">
                    <div>
                        <p class="text-white font-semibold text-sm">Active Runners</p>
                        <p class="text-[#8b8b6b] text-xs" x-text="`${activeRunners} users`"></p>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#0a0a0a] cursor-pointer transition-colors">
                    <input type="checkbox" x-model="targets" value="group_members" class="w-4 h-4 rounded accent-[#6b6b4b]">
                    <div>
                        <p class="text-white font-semibold text-sm">Group Members</p>
                        <p class="text-[#8b8b6b] text-xs">Select specific groups</p>
                    </div>
                </label>

                <!-- Conditional Group Selection -->
                <template x-if="targets.includes('group_members')">
                    <div class="pl-7 space-y-2 mt-4 pt-4 border-t border-[#2a2a2a]">
                        <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest mb-3">Select Groups</p>
                        @foreach($groups ?? [] as $group)
                        <label class="flex items-center gap-2 p-2 rounded hover:bg-[#0a0a0a] cursor-pointer transition-colors">
                            <input type="checkbox" x-model="selectedGroups" value="{{ $group->id }}" class="w-3 h-3 rounded accent-[#6b6b4b]">
                            <span class="text-white text-sm">{{ $group->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </template>

                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#0a0a0a] cursor-pointer transition-colors">
                    <input type="checkbox" x-model="targets" value="location" class="w-4 h-4 rounded accent-[#6b6b4b]">
                    <div>
                        <p class="text-white font-semibold text-sm">By Location</p>
                        <p class="text-[#8b8b6b] text-xs">e.g. Selangor, Melaka</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Delivery Status -->
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
            <h3 class="text-white font-bold mb-4">Delivery Status</h3>
            
            <template x-if="isSending">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <p class="text-[#8b8b6b]">Sending notifications...</p>
                            <p class="text-white font-bold" x-text="`${sentCount}/${totalCount}`"></p>
                        </div>
                        <div class="w-full bg-[#0a0a0a] rounded-full h-2 border border-[#2a2a2a] overflow-hidden">
                            <div class="bg-gradient-to-r from-[#6b6b4b] to-[#7b7b5b] h-full transition-all duration-300" :style="`width: ${(sentCount/totalCount)*100}%`"></div>
                        </div>
                    </div>
                    <p class="text-[#8b8b6b] text-xs">Do not close this window</p>
                </div>
            </template>

            <template x-if="!isSending">
                <button @click="sendNotification()"
                        class="w-full py-3 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-lg font-bold transition-colors">
                    Send Notification
                </button>
            </template>
        </div>

        <!-- Recent Notifications -->
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
            <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                Recent
            </h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($recentNotifications ?? [] as $notif)
                <div class="p-3 bg-[#0a0a0a] rounded-lg border border-[#2a2a2a]">
                    <p class="text-white text-sm font-semibold">{{ $notif->title }}</p>
                    <p class="text-[#8b8b6b] text-xs mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
                @empty
                <p class="text-[#8b8b6b] text-sm text-center py-4">No notifications sent yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
function notificationBuilder() {
    return {
        selectedTemplate: 'success',
        title: '',
        message: '',
        targets: [],
        selectedGroups: [],
        isSending: false,
        sentCount: 0,
        totalCount: 100,
        activeRunners: {{ $activeRunners ?? 0 }},
        
        sendNotification() {
            if (!this.title || !this.message) {
                alert('Please fill in title and message');
                return;
            }
            
            this.isSending = true;
            this.sentCount = 0;
            
            // Simulate sending
            const interval = setInterval(() => {
                this.sentCount = Math.min(this.sentCount + Math.random() * 30, this.totalCount);
                if (this.sentCount >= this.totalCount) {
                    clearInterval(interval);
                    this.isSending = false;
                    alert('Notifications sent successfully!');
                    this.title = '';
                    this.message = '';
                }
            }, 300);
        }
    };
}
</script>
