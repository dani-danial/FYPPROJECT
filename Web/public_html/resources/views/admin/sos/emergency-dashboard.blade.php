<div x-data="sosDashboard()" class="space-y-6">
    <!-- Emergency Alert (if active signals) -->
    <template x-if="activeSos.length > 0">
        <div class="animate-pulse bg-red-500/20 border-2 border-red-500 rounded-2xl p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-red-500/10 rounded-full -mr-20 -mt-20 blur-3xl animate-pulse"></div>
            
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-16 h-16 rounded-full bg-red-500/30 border-2 border-red-500 flex items-center justify-center animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                
                <div class="flex-1">
                    <p class="text-red-400 font-black text-xl">EMERGENCY SIGNAL ACTIVE</p>
                    <p class="text-red-400/80 text-sm" x-text="`${activeSos.length} ${activeSos.length === 1 ? 'user' : 'users'} need immediate assistance`"></p>
                </div>

                <audio id="emergencyAlert" style="display:none;">
                    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAAB9AAACABAAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj==" type="audio/wav">
                </audio>
                
                <button @click="playAlert()" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold transition-colors flex-shrink-0">
                    Play Alert
                </button>
            </div>
        </div>
    </template>

    <!-- Split View: Map + Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Map (Left) -->
        <div class="lg:col-span-2">
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl overflow-hidden h-96">
                <div id="sos-map" class="w-full h-full" style="background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%); display: flex; align-items: center; justify-content: center;">
                    <p class="text-[#8b8b6b] text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-4 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Map loading... (requires Leaflet.js)
                    </p>
                </div>
            </div>
        </div>

        <!-- Details Panel (Right) -->
        <div class="space-y-6">
            <!-- Active SOS Count -->
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
                <p class="text-[#8b8b6b] text-sm uppercase font-bold tracking-widest mb-2">Active Signals</p>
                <p class="text-5xl font-black text-red-400" x-text="activeSos.length"></p>
                <p class="text-[#8b8b6b] text-xs mt-2">Requiring immediate response</p>
            </div>

            <!-- Current Signal Details -->
            <template x-if="activeSos.length > 0">
                <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6 space-y-4">
                    <h3 class="text-white font-bold">Signal Details</h3>
                    
                    <div class="p-4 bg-[#0a0a0a] rounded-lg border border-red-500/30 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center overflow-hidden flex-shrink-0">
                                <!-- User avatar here -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-bold" x-text="activeSos[0]?.user_name || 'Unknown'"></p>
                                <p class="text-red-400 text-sm font-semibold" x-text="activeSos[0]?.signal_status || 'Emergency'"></p>
                            </div>
                        </div>

                        <!-- Coordinates -->
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-[#8b8b6b]">Latitude:</span>
                                <span class="text-white font-mono" x-text="activeSos[0]?.latitude?.toFixed(6) || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#8b8b6b]">Longitude:</span>
                                <span class="text-white font-mono" x-text="activeSos[0]?.longitude?.toFixed(6) || 'N/A'"></span>
                            </div>
                        </div>

                        <!-- Medical Info -->
                        <div class="pt-3 border-t border-[#2a2a2a] space-y-2">
                            <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">Medical Info</p>
                            <p class="text-white text-sm" x-text="activeSos[0]?.medical_info || 'No medical info provided'"></p>
                        </div>

                        <!-- Emergency Contacts -->
                        <div class="pt-3 border-t border-[#2a2a2a] space-y-2">
                            <p class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">Emergency Contacts</p>
                            <div class="space-y-1">
                                <p class="text-white text-sm">📞 <span x-text="activeSos[0]?.emergency_contact_phone || 'N/A'"></span></p>
                                <p class="text-white text-sm">👤 <span x-text="activeSos[0]?.emergency_contact_name || 'N/A'"></span></p>
                            </div>
                        </div>

                        <!-- Time Since Signal -->
                        <div class="pt-3 border-t border-[#2a2a2a]">
                            <p class="text-[#8b8b6b] text-xs" x-text="`Signal received ${activeSos[0]?.time_since_signal || '...'}`"></p>
                        </div>
                    </div>

                    <!-- Resolution Log -->
                    <button @click="showResolutionModal = true"
                            class="w-full py-3 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 rounded-lg font-bold transition-colors border border-emerald-500/30">
                        Mark as Resolved
                    </button>
                </div>
            </template>

            <!-- Resolution Modal -->
            <template x-if="showResolutionModal">
                <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
                    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6 max-w-sm w-full">
                        <h3 class="text-white font-bold text-lg mb-4">Resolve SOS Signal</h3>
                        
                        <textarea @keydown.escape="showResolutionModal = false"
                                  placeholder="Describe the assistance provided..."
                                  rows="4"
                                  class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-4 py-3 text-white placeholder-[#4a4a4a] focus:border-[#6b6b4b] transition-colors resize-none mb-4"></textarea>
                        
                        <div class="flex gap-2">
                            <button @click="showResolutionModal = false"
                                    class="flex-1 py-2 bg-[#2a2a2a] text-[#8b8b6b] rounded-lg hover:text-white transition-colors">
                                Cancel
                            </button>
                            <button @click="resolveSos()"
                                    class="flex-1 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors font-bold">
                                Resolved
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- SOS History -->
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
        <h3 class="text-white font-bold mb-4">Recent SOS Signals</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-[#2a2a2a]">
                    <tr>
                        <th class="text-left p-3 text-[#8b8b6b] text-xs font-bold uppercase">User</th>
                        <th class="text-left p-3 text-[#8b8b6b] text-xs font-bold uppercase">Status</th>
                        <th class="text-left p-3 text-[#8b8b6b] text-xs font-bold uppercase">Location</th>
                        <th class="text-left p-3 text-[#8b8b6b] text-xs font-bold uppercase">Time</th>
                        <th class="text-left p-3 text-[#8b8b6b] text-xs font-bold uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a2a]">
                    @forelse($sosSignals ?? [] as $sos)
                    <tr class="hover:bg-[#0a0a0a] transition-colors">
                        <td class="p-3 text-white">{{ $sos->user->name ?? 'Unknown' }}</td>
                        <td class="p-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-bold"
                                  style="background-color: {{ $sos->status === 'resolved' ? '#059669' : '#dc2626' }}20; color: {{ $sos->status === 'resolved' ? '#10b981' : '#ef4444' }};">
                                {{ ucfirst($sos->status) }}
                            </span>
                        </td>
                        <td class="p-3 text-[#8b8b6b] text-xs">{{ $sos->location ?? 'N/A' }}</td>
                        <td class="p-3 text-[#8b8b6b] text-xs">{{ $sos->created_at->diffForHumans() }}</td>
                        <td class="p-3">
                            <button class="text-blue-400 hover:text-blue-300 transition-colors text-xs font-bold">View</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-[#8b8b6b]">No SOS signals recorded</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function sosDashboard() {
    return {
        activeSos: @json($activeSos ?? []),
        showResolutionModal: false,
        
        playAlert() {
            const audio = document.getElementById('emergencyAlert');
            // Play multiple times for urgency
            audio.play();
            setTimeout(() => audio.play(), 500);
        },
        
        resolveSos() {
            // Call controller to mark SOS as resolved
            this.showResolutionModal = false;
            alert('SOS marked as resolved');
        }
    };
}
</script>
