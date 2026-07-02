@extends('layouts.app')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <a href="{{ route('posts.index') }}" class="text-[#8b8b6b] hover:text-white flex items-center gap-2 text-sm transition-colors uppercase font-black tracking-widest">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Management
    </a>
    <form action="{{ route('posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Delete this post permanently?');">
        @csrf @method('DELETE')
        <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition-all text-[10px] font-black uppercase tracking-widest border border-red-500/20">
            <i data-lucide="trash-2" class="w-4 h-4"></i> Delete Community Post
        </button>
    </form>
</div>

<div class="max-w-4xl mx-auto">
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[3rem] overflow-hidden shadow-2xl mb-12">
        
        {{-- Post Header --}}
        <div class="p-10 border-b border-[#2a2a2a] flex items-center justify-between bg-[#151515]">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#0a0a0a] border border-[#2a2a2a] overflow-hidden shadow-inner shrink-0">
                    @if($post->user && $post->user->profile_photo_path)
                        <img src="{{ $post->user->profile_photo_url }}" class="w-full h-full object-cover">
                    @elseif($post->user_image)
                        <img src="{{ str_contains($post->user_image, 'http') ? $post->user_image : url('/serve-image?path=' . ltrim($post->user_image, '/')) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-[#6b6b4b] font-black text-sm">
                            {{ strtoupper(substr($post->author_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h4 class="text-white font-black text-lg tracking-tighter">{{ $post->author_name }}</h4>
                    <p class="text-[9px] text-[#4a4a4a] font-black uppercase tracking-[0.2em]">{{ '@'.$post->author_username }} • {{ $post->created_at->diffForHumans() }}</p>
                </div>
            </div>
            
            <span class="px-5 py-2 bg-[#0a0a0a] border border-[#6b6b4b]/30 text-[#6b6b4b] rounded-full text-[10px] font-black uppercase tracking-widest">
                ID: #{{ $post->id }}
            </span>
        </div>

        {{-- Post Media Content (Updated for Multiple Images) --}}
        <div class="p-10">
            <p class="text-[#b0b0a0] text-xl leading-relaxed mb-10 italic">"{{ $post->content }}"</p>

            {{-- 🛠️ CAROUSEL CONTAINER --}}
            <div class="rounded-[2rem] overflow-hidden border border-[#2a2a2a] bg-[#0a0a0a] shadow-inner relative group/slider">
                @if($post->image_url)
                    @php
                        // Handle both array (new) and string (old) data safely
                        $mediaItems = is_array($post->image_url) ? $post->image_url : [$post->image_url];
                    @endphp

                    {{-- Slider --}}
                    <div id="slider-{{ $post->id }}" class="flex overflow-x-auto snap-x snap-mandatory w-full h-[500px] scrollbar-hide scroll-smooth">
                        @foreach($mediaItems as $media)
                            <div class="flex-shrink-0 w-full h-full snap-center flex items-center justify-center bg-black">
                                <img src="{{ $media }}" class="max-w-full max-h-full object-contain">
                            </div>
                        @endforeach
                    </div>

                    {{-- Navigation Arrows (Only if multiple) --}}
                    @if(count($mediaItems) > 1)
                        {{-- Counter --}}
                        <div class="absolute top-4 right-4 bg-black/60 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1 rounded-full border border-white/10 z-10">
                            {{ count($mediaItems) }} Media Items
                        </div>

                        {{-- Left Arrow --}}
                        <button onclick="document.getElementById('slider-{{ $post->id }}').scrollBy({left: -this.parentElement.clientWidth, behavior: 'smooth'})" 
                                class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/80 text-white p-3 rounded-full backdrop-blur-sm opacity-0 group-hover/slider:opacity-100 transition-opacity border border-white/10 shadow-xl z-20">
                            <i data-lucide="chevron-left" class="w-6 h-6"></i>
                        </button>
                        
                        {{-- Right Arrow --}}
                        <button onclick="document.getElementById('slider-{{ $post->id }}').scrollBy({left: this.parentElement.clientWidth, behavior: 'smooth'})" 
                                class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/80 text-white p-3 rounded-full backdrop-blur-sm opacity-0 group-hover/slider:opacity-100 transition-opacity border border-white/10 shadow-xl z-20">
                            <i data-lucide="chevron-right" class="w-6 h-6"></i>
                        </button>
                    @endif

                @else
                    <div class="py-24 text-center">
                        <i data-lucide="image-off" class="w-12 h-12 text-[#2a2a2a] mx-auto mb-4"></i>
                        <p class="text-[#4a4a4a] text-[10px] font-bold uppercase tracking-widest">No visual media attached</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Community Comments Section --}}
        <div class="p-10 border-t border-[#2a2a2a] bg-[#0f0f0f]">
            <h3 class="text-[#4a4a4a] text-xs font-black uppercase tracking-widest mb-6">Discussion Thread</h3>
            <div class="space-y-6">
                @forelse($post->comments as $comment)
                    <div class="flex gap-4 p-6 bg-[#151515] border border-[#222] rounded-2xl transition-all hover:border-[#6b6b4b]/20">
                        {{-- Commenter Icon --}}
                        <div class="w-9 h-9 rounded-xl bg-[#0a0a0a] border border-[#2a2a2a] flex items-center justify-center text-[#6b6b4b] text-[10px] font-black shrink-0 overflow-hidden">
                            @if($comment->user && $comment->user->profile_photo_path)
                                <img src="{{ $comment->user->profile_photo_url }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-black text-white uppercase tracking-tight">{{ $comment->user->name ?? 'Unknown User' }}</span>
                                <span class="text-[6px] text-[#4a4a4a] font-black uppercase tracking-[0.2em]">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-[#8b8b6b] text-sm leading-relaxed">{{ $comment->body }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center border-2 border-dashed border-[#222] rounded-[2rem]">
                        <p class="text-[#333] text-[10px] font-black uppercase tracking-widest">No discussion points found for this post</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Interaction Stats Footer --}}
        <div class="p-6 bg-[#0a0a0a] border-t border-[#2a2a2a] flex items-center justify-center gap-12">
            <div class="text-center">
                <p class="text-[#4a4a4a] text-[10px] font-black uppercase tracking-widest mb-2">Total Engagement</p>
                <div class="flex items-center gap-8">
                    <span class="flex items-center gap-2 text-white font-black"><i data-lucide="heart" class="w-4 h-4 text-red-500"></i> {{ $post->likes_count }}</span>
                    <span class="flex items-center gap-2 text-white font-black"><i data-lucide="message-circle" class="w-4 h-4 text-blue-500"></i> {{ $post->comments_count }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hide Scrollbar CSS --}}
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
