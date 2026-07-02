<div x-data="postModeration()" class="space-y-6">
    <!-- Tabs for Different Views -->
    <div class="flex gap-2 border-b border-[#2a2a2a]">
        <button @click="currentTab = 'reported'"
                class="px-6 py-3 border-b-2 font-bold transition-colors"
                :class="currentTab === 'reported' ? 'border-[#6b6b4b] text-white' : 'border-transparent text-[#8b8b6b] hover:text-white'">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline-block mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            Reported Content
        </button>
        <button @click="currentTab = 'all'"
                class="px-6 py-3 border-b-2 font-bold transition-colors"
                :class="currentTab === 'all' ? 'border-[#6b6b4b] text-white' : 'border-transparent text-[#8b8b6b] hover:text-white'">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline-block mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            All Posts
        </button>
    </div>

    <!-- Reported Posts (Side-by-Side View) -->
    <div v-show="currentTab === 'reported'" class="space-y-6">
        <div class="space-y-6">
            @forelse($reportedPosts ?? [] as $report)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6 bg-[#1a1a1a] border border-red-500/30 rounded-2xl">
                <!-- Original Post -->
                <div class="bg-[#0a0a0a] rounded-xl p-4 border border-[#2a2a2a]">
                    <p class="text-[#8b8b6b] text-xs uppercase font-bold mb-3">ORIGINAL POST</p>
                    
                    <div class="space-y-3">
                        <!-- User Info -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#6b6b4b] overflow-hidden">
                                @if($report->post->user->profile_photo_path)
                                <img src="{{ $report->post->user->profile_photo_url }}" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center text-white font-bold">
                                    {{ substr($report->post->user->name, 0, 1) }}
                                </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-white font-bold text-sm">{{ $report->post->user->name }}</p>
                                <p class="text-[#8b8b6b] text-xs">{{ $report->post->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <!-- Content -->
                        <p class="text-white text-sm">{{ $report->post->content }}</p>

                        <!-- Engagement Stats -->
                        <div class="flex gap-4 text-xs text-[#8b8b6b] pt-3 border-t border-[#2a2a2a]">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                                {{ $report->post->likers()->count() }} likes
                            </span>
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                {{ $report->post->comments()->count() }} comments
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Report Details -->
                <div class="bg-red-500/10 rounded-xl p-4 border border-red-500/30 space-y-4">
                    <div>
                        <p class="text-[#8b8b6b] text-xs uppercase font-bold mb-2">REPORT REASON</p>
                        <p class="text-white font-bold text-lg">{{ $report->reason }}</p>
                    </div>

                    <div>
                        <p class="text-[#8b8b6b] text-xs uppercase font-bold mb-2">REPORT DETAILS</p>
                        <p class="text-red-300 text-sm">{{ $report->description }}</p>
                    </div>

                    <div>
                        <p class="text-[#8b8b6b] text-xs uppercase font-bold mb-2">REPORTED BY</p>
                        <p class="text-white font-semibold text-sm">{{ $report->reporter?->name ?? 'Anonymous' }}</p>
                        <p class="text-[#8b8b6b] text-xs">{{ $report->created_at->diffForHumans() }}</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2 pt-4 border-t border-red-500/20">
                        <button @click="deletePost({{ $report->post->id }})"
                                class="w-full py-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg font-bold transition-colors text-sm">
                            Delete Post
                        </button>
                        <button @click="dismissReport({{ $report->id }})"
                                class="w-full py-2 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-[#8b8b6b] rounded-lg font-bold transition-colors text-sm">
                            Dismiss Report
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-[#4a4a4a] mx-auto mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <p class="text-[#8b8b6b]">No reported posts at the moment. Great job!</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- All Posts (Engagement Grid) -->
    <div v-show="currentTab === 'all'" class="space-y-6">
        <!-- Bulk Actions -->
        <div class="flex items-center gap-4 p-4 bg-[#1a1a1a] border border-[#2a2a2a] rounded-lg"
             x-show="selectedPosts.length > 0">
            <p class="text-[#8b8b6b] text-sm" x-text="`${selectedPosts.length} posts selected`"></p>
            <button @click="bulkDelete()"
                    class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg font-bold transition-colors text-sm">
                Delete Selected
            </button>
            <button @click="bulkFeature()"
                    class="px-4 py-2 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 rounded-lg font-bold transition-colors text-sm">
                Feature Selected
            </button>
        </div>

        <!-- Posts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($posts ?? [] as $post)
            <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl overflow-hidden hover:border-[#4a4a4a] transition-all group relative">
                <!-- Selection Checkbox -->
                <label class="absolute top-3 left-3 z-10 flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" :value="{{ $post->id }}" x-model="selectedPosts" class="w-4 h-4 rounded accent-[#6b6b4b]">
                </label>

                <!-- Post Thumbnail -->
                <div class="h-48 bg-gradient-to-br from-[#6b6b4b]/20 to-[#1a1a1a] flex items-center justify-center text-4xl overflow-hidden relative">
                    🏃
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button class="p-2 bg-[#6b6b4b] rounded-lg text-white hover:bg-[#7b7b5b] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <button class="p-2 bg-red-500 rounded-lg text-white hover:bg-red-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Post Info -->
                <div class="p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-[#6b6b4b] overflow-hidden flex-shrink-0">
                            @if($post->user->profile_photo_path)
                            <img src="{{ $post->user->profile_photo_url }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-white font-bold text-xs">
                                {{ substr($post->user->name, 0, 1) }}
                            </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-white font-bold text-sm">{{ $post->user->name }}</p>
                            <p class="text-[#8b8b6b] text-xs">{{ $post->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <p class="text-white text-sm line-clamp-2">{{ $post->content }}</p>

                    <!-- Engagement Stats Overlay -->
                    <div class="flex gap-4 text-sm font-bold">
                        <span class="flex items-center gap-1 text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                            {{ $post->likers()->count() }}
                        </span>
                        <span class="flex items-center gap-1 text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            {{ $post->comments()->count() }}
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full p-12 text-center">
                <p class="text-[#8b8b6b]">No posts found</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function postModeration() {
    return {
        currentTab: 'reported',
        selectedPosts: [],
        
        deletePost(postId) {
            if (confirm('Are you sure you want to delete this post?')) {
                fetch(`/admin/posts/${postId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }).then(() => location.reload());
            }
        },
        
        dismissReport(reportId) {
            fetch(`/admin/reports/${reportId}/dismiss`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            }).then(() => location.reload());
        },
        
        bulkDelete() {
            if (confirm(`Delete ${this.selectedPosts.length} posts?`)) {
                // Call controller
                location.reload();
            }
        },
        
        bulkFeature() {
            if (confirm(`Feature ${this.selectedPosts.length} posts?`)) {
                // Call controller
                location.reload();
            }
        }
    };
}
</script>
