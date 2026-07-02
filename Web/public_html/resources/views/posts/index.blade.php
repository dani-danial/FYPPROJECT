@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-black text-white tracking-tight">Content Posts Management</h2>
    <p class="text-[#8b8b6b] mt-1 text-sm uppercase font-bold tracking-widest">Monitor and manage community activity.</p>
</div>

{{-- Global Stats Grid --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-[#6b6b4b] rounded-2xl p-6 shadow-lg shadow-[#6b6b4b]/10">
        <h3 class="text-white/70 text-[10px] font-bold uppercase tracking-widest mb-2">Total Posts</h3>
        <div class="text-4xl font-black text-white">{{ $totalPosts }}</div>
    </div>
   
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
        <h3 class="text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest mb-2">Total Likes</h3>
        <div class="text-4xl font-black text-white">{{ $totalLikes }}</div>
    </div>
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
        <h3 class="text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest mb-2">Total Comments</h3>
        <div class="text-4xl font-black text-white">{{ $totalComments }}</div>
    </div>
</div>

{{-- Management Table --}}
<div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] overflow-hidden shadow-2xl">
    <table class="w-full text-left">
        <thead class="bg-[#0a0a0a] border-b border-[#2a2a2a]">
            <tr class="text-[#8b8b6b] text-[10px] font-black uppercase tracking-[0.2em]">
                <th class="px-8 py-6">Author</th>
                <th class="px-8 py-6">Content Preview</th>
                <th class="px-8 py-6 text-center">Interactions</th>
                <th class="px-8 py-6 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#2a2a2a]">
            @forelse($posts as $post)
            <tr class="hover:bg-[#222] transition-colors group">
                <td class="px-8 py-6">
                    <div class="flex items-center gap-3">
                        
                        {{-- 👇 UPDATED SMART IMAGE BLOCK 👇 --}}
                        <div class="w-10 h-10 rounded-full bg-[#0a0a0a] border border-[#2a2a2a] overflow-hidden flex-shrink-0 relative">
                            @if($post->user_image)
                                {{-- 1. Try to show the image --}}
                                <img src="{{ str_contains($post->user_image, 'http') ? $post->user_image : asset('storage/' . $post->user_image) }}" 
                                     class="w-full h-full object-cover"
                                     alt="{{ $post->author_name }}"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                
                                {{-- 2. Hidden Fallback (Shows if image breaks) --}}
                                <div class="absolute inset-0 w-full h-full bg-[#1a1a1a] flex items-center justify-center text-[#6b6b4b] text-xs font-bold hidden">
                                    {{ substr($post->author_name, 0, 1) }}
                                </div>
                            @else
                                {{-- 3. Default Fallback (If no image set) --}}
                                <div class="w-full h-full flex items-center justify-center text-[#6b6b4b] text-xs font-bold">
                                    {{ substr($post->author_name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        {{-- 👆 END SMART IMAGE BLOCK 👆 --}}

                        <div>
                            <div class="text-white font-bold text-sm">{{ $post->author_name }}</div>
                            <div class="text-[#4a4a4a] text-[10px] uppercase font-bold tracking-widest">{{ '@'.$post->author_username }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-8 py-6">
                    <p class="text-[#b0b0a0] text-sm line-clamp-1 italic">{{ $post->content }}</p>
                </td>
                <td class="px-8 py-6">
                    <div class="flex items-center justify-center gap-4 text-[#4a4a4a]">
                        <span class="flex items-center gap-1 text-[10px] font-bold"><i data-lucide="heart" class="w-3 h-3 text-red-500"></i> {{ $post->likes_count }}</span>
                        <span class="flex items-center gap-1 text-[10px] font-bold"><i data-lucide="message-square" class="w-3 h-3 text-blue-500"></i> {{ $post->comments_count }}</span>
                    </div>
                </td>
                <td class="px-8 py-6 text-right">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('posts.show', $post->id) }}" class="p-2 bg-[#2a2a2a] text-[#8b8b6b] rounded-lg hover:text-white transition-colors">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </a>
                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Delete permanently?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 bg-red-900/10 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-all">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-8 py-20 text-center">
                    <div class="flex flex-col items-center gap-4">
                        <i data-lucide="file-x-2" class="w-12 h-12 text-[#2a2a2a]"></i>
                        <p class="text-[#4a4a4a] text-xs font-bold uppercase tracking-[0.2em]">No community posts detected yet.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection