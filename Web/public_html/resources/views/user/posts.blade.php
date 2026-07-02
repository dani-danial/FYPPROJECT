@extends('layouts.app')

@section('content')
<div class="p-8 space-y-8 bg-[#0a0a0a] min-h-screen max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tight">Community Feed</h2>
            <p class="text-[#8b8b6b] mt-1 text-sm uppercase font-bold tracking-widest">See what your runners are sharing</p>
        </div>
        <a href="{{ route('user.posts.create') }}" class="flex items-center gap-2 px-6 py-3 bg-[#6b6b4b] hover:bg-[#5a5a3f] rounded-xl text-white font-bold text-sm transition-all shadow-lg shadow-[#6b6b4b]/20 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:rotate-90 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Share Post
        </a>
    </div>

    {{-- Posts Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($posts as $post)
        <div class="group block h-full">
            <div class="bg-gradient-to-br from-[#1a1a1a] to-[#0d0d0d] border border-[#2a2a2a] rounded-2xl overflow-hidden flex flex-col h-full hover:border-[#6b6b4b]/50 transition-all duration-300 hover:shadow-lg hover:shadow-[#6b6b4b]/10 hover:-translate-y-1">
                
                {{-- User Header --}}
                <div class="p-6 border-b border-[#2a2a2a] bg-[#151515]">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-4 flex-grow">
                            <div class="w-12 h-12 bg-gradient-to-br from-[#6b6b4b] to-[#4a4a3a] rounded-full flex items-center justify-center border border-[#6b6b4b]/50 overflow-hidden flex-shrink-0 group-hover:scale-110 transition-transform">
                                @if($post->user && $post->user->profile_photo_path)
                                    {{-- 🛠️ FIXED: Removed asset('storage/...') --}}
                                    <img src="{{ $post->user->profile_photo_url }}" 
                                         class="w-full h-full object-cover"
                                         alt="{{ $post->user->name }}">
                                @else
                                    <span class="text-white font-black text-lg uppercase">{{ strtoupper(substr($post->user->name ?? 'U', 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-grow">
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-white font-bold text-sm truncate">{{ $post->user->name ?? 'Runner' }}</h4>
                                        @if($post->user && $post->user->role === 'admin')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#6b6b4b]/20 text-[#6b6b4b] text-[10px] font-bold border border-[#6b6b4b]/30">
                                                Admin
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-1">
                                        @if(Auth::id() === $post->user_id)
                                            <a href="{{ route('user.posts.edit', $post->id) }}" class="text-[#4a4a4a] hover:text-[#6b6b4b] transition-colors p-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Delete this post?');" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-[#4a4a4a] hover:text-red-500 transition-colors p-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-[10px] text-[#8b8b6b] font-bold uppercase tracking-widest">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Media Display Section --}}
                <div class="relative h-64 bg-[#0a0a0a] group-hover:bg-[#000] transition-colors border-b border-[#2a2a2a] group/slider">
                    @if($post->image_url)
                        @php
                            $mediaItems = is_array($post->image_url) ? $post->image_url : json_decode($post->image_url, true) ?? [$post->image_url];
                        @endphp
                        
                        <div id="slider-{{ $post->id }}" class="flex overflow-x-auto snap-x snap-mandatory h-full w-full scrollbar-hide scroll-smooth">
                            @foreach($mediaItems as $media)
                                <div class="flex-shrink-0 w-full h-full snap-center relative">
                                    {{-- 🛠️ FIXED: Removed asset('storage/...') --}}
                                    <img src="{{ $media }}" class="w-full h-full object-cover">
                                    
                                    @if(count($mediaItems) > 1)
                                        <div class="absolute top-2 right-2 bg-black/60 text-white text-[10px] font-bold px-2 py-1 rounded-full backdrop-blur-sm border border-white/10 z-10">
                                            {{ $loop->iteration }} / {{ count($mediaItems) }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if(count($mediaItems) > 1)
                            <button onclick="document.getElementById('slider-{{ $post->id }}').scrollBy({left: -this.parentElement.clientWidth, behavior: 'smooth'})" 
                                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/60 hover:bg-black/90 text-white p-2 rounded-full backdrop-blur-sm transition-all border border-white/20 z-20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                            </button>
                            <button onclick="document.getElementById('slider-{{ $post->id }}').scrollBy({left: this.parentElement.clientWidth, behavior: 'smooth'})" 
                                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/60 hover:bg-black/90 text-white p-2 rounded-full backdrop-blur-sm transition-all border border-white/20 z-20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </button>
                        @endif
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-center p-6 bg-gradient-to-br from-[#6b6b4b]/10 to-[#0a0a0a]">
                            <span class="text-6xl mb-4 group-hover:scale-125 transition-transform duration-500">🏃</span>
                            <p class="text-[#8b8b6b] text-xs font-bold">Running Moment</p>
                        </div>
                    @endif
                </div>

                {{-- Content Section --}}
                <div class="p-6 flex-grow flex flex-col">
                    <p class="text-[#b0b0a0] text-sm leading-relaxed mb-6 line-clamp-3">
                        {{ $post->content }}
                    </p>

                    <div class="space-y-3 mt-auto">
                        <a href="{{ route('user.posts.show', $post->id) }}" class="block w-full text-center bg-[#2a2a2a] hover:bg-[#3a3a3a] text-white py-2 rounded-lg font-bold text-sm transition-all">
                            View Post
                        </a>
                        <div class="flex gap-3">
                            <button onclick="toggleLike(this, {{ $post->id }})" class="flex-1 text-center py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg text-xs font-bold border border-red-500/20 transition-all">
                                <span class="like-count">{{ $post->likes_count ?? 0 }}</span> Likes
                            </button>
                            <a href="{{ route('user.posts.show', $post->id) }}" class="flex-1 text-center py-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg text-xs font-bold border border-blue-500/20 transition-all">
                                <span>{{ $post->comments_count ?? 0 }}</span> Comments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-20 bg-[#151515] rounded-3xl border border-dashed border-[#2a2a2a]">
            <p class="text-[#8b8b6b]">No posts found in the feed.</p>
        </div>
        @endforelse
    </div>
</div>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
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
            countSpan.textContent = data.count;
            if (data.liked) {
                btn.classList.add('bg-red-500/20');
            } else {
                btn.classList.remove('bg-red-500/20');
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
