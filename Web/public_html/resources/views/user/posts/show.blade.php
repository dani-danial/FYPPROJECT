@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen flex justify-center">
    <div class="w-full max-w-5xl">
        
        {{-- Back Button --}}
        <a href="{{ route('user.posts') }}" class="inline-flex items-center gap-2 text-[#8b8b6b] hover:text-white transition-colors mb-8 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:-translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span class="text-xs font-bold uppercase tracking-widest">Back to Feed</span>
        </a>

        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] overflow-hidden shadow-2xl flex flex-col md:flex-row min-h-[500px]">
            
            {{-- LEFT: Media Slider --}}
            <div class="w-full md:w-3/5 bg-black relative group/slider">
                @if($post->image_url)
                    @php
                        $mediaItems = is_array($post->image_url) ? $post->image_url : json_decode($post->image_url, true) ?? [$post->image_url];
                    @endphp

                    <div id="slider-detail" class="flex overflow-x-auto snap-x snap-mandatory h-full w-full scrollbar-hide scroll-smooth">
                        @foreach($mediaItems as $media)
                            <div class="flex-shrink-0 w-full h-full snap-center flex items-center justify-center bg-[#050505] relative">
                                {{-- FIX: Correct storage path --}}
                                <img src="{{ $media }}" class="max-w-full max-h-full object-contain">
                            </div>
                        @endforeach
                    </div>

                    @if(count($mediaItems) > 1)
                        <div class="absolute top-4 right-4 bg-black/60 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1 rounded-full border border-white/10 z-10 shadow-lg">
                            {{ count($mediaItems) }} Items
                        </div>

                        <button onclick="document.getElementById('slider-detail').scrollBy({left: -this.parentElement.clientWidth, behavior: 'smooth'})" 
                                class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/90 text-white p-3 rounded-full backdrop-blur-sm border border-white/20 shadow-xl z-20 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        </button>
                        
                        <button onclick="document.getElementById('slider-detail').scrollBy({left: this.parentElement.clientWidth, behavior: 'smooth'})" 
                                class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/90 text-white p-3 rounded-full backdrop-blur-sm border border-white/20 shadow-xl z-20 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                    @endif
                @else
                    <div class="h-full w-full flex flex-col items-center justify-center p-12 text-[#4a4a4a]">
                        <span class="text-6xl mb-4">🏃</span>
                        <p class="font-bold text-sm uppercase tracking-widest">Running Update</p>
                    </div>
                @endif
            </div>

            {{-- RIGHT: Sidebar (Details & Comments) --}}
            <div class="w-full md:w-2/5 flex flex-col bg-[#1a1a1a] border-l border-[#2a2a2a]">
                <div class="p-6 border-b border-[#2a2a2a] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#6b6b4b] overflow-hidden border border-[#2a2a2a]">
                            @if($post->user && $post->user->profile_photo_path)
                                <img src="{{ $post->user->profile_photo_url }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white text-xs font-bold">{{ substr($post->user->name ?? 'U', 0, 1) }}</div>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-white font-bold text-sm">{{ $post->user->name ?? 'Runner' }}</h4>
                            <p class="text-[#8b8b6b] text-[10px] uppercase font-bold">{{ $post->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <button onclick="toggleLike(this, {{ $post->id }})" class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl text-xs font-bold border border-red-500/20 transition-all flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 {{ $post->liked_by_me ? 'fill-current' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="like-count">{{ $post->likes_count ?? 0 }}</span> Likes
                    </button>
                </div>

                <div class="p-6 border-b border-[#2a2a2a]">
                    <p class="text-white text-sm leading-relaxed">{{ $post->content }}</p>
                </div>

                {{-- Comments list --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-[#111]">
                    @forelse($post->comments as $comment)
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#2a2a2a] flex-shrink-0 overflow-hidden flex items-center justify-center">
                                @if($comment->user && $comment->user->profile_photo_path)
                                    <img src="{{ $comment->user->profile_photo_url }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[#8b8b6b] text-[10px] font-bold">{{ substr($comment->user->name ?? 'U', 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="bg-[#1a1a1a] p-3 rounded-2xl rounded-tl-none border border-[#2a2a2a]">
                                    <div class="flex justify-between items-baseline mb-1">
                                        <span class="text-xs font-bold text-white">{{ $comment->user->name ?? 'User' }}</span>
                                        <span class="text-[9px] text-[#4a4a4a]">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-[#b0b0a0]">{{ $comment->body }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <p class="text-[#4a4a4a] text-xs font-bold uppercase tracking-widest">No comments yet</p>
                        </div>
                    @endforelse
                </div>

                {{-- Input --}}
                <div class="p-4 bg-[#1a1a1a] border-t border-[#2a2a2a]">
                    <form action="{{ route('user.posts.comment', $post->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="comment" required placeholder="Write a comment..." 
                            class="flex-grow bg-[#0a0a0a] border border-[#2a2a2a] text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:border-[#6b6b4b] transition-all">
                        <button type="submit" class="px-4 bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white rounded-xl transition-all flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
</style>

<script>
function toggleLike(btn, postId) {
    btn.disabled = true;
    fetch(`/user/posts/${postId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        if (data.status === 'success') {
            const countSpan = btn.querySelector('.like-count');
            const svg = btn.querySelector('svg');
            countSpan.textContent = data.count;
            if (data.liked) {
                svg.classList.add('fill-current');
            } else {
                svg.classList.remove('fill-current');
            }
        }
    })
    .catch(error => {
        btn.disabled = false;
        console.error('Error toggling like:', error);
    });
}
</script>
@endsection
