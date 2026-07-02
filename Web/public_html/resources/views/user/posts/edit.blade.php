@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-3xl font-black text-white tracking-tight mb-6">Edit Post</h2>
        
        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-8">
            <form action="{{ route('user.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    <label class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">Caption</label>
                    <textarea name="content" rows="4" required
                        class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-xl p-4 text-white focus:outline-none focus:border-[#6b6b4b] transition-all resize-none">{{ old('content', $post->content) }}</textarea>
                </div>

                {{-- Display Current Images --}}
                @if($post->image_url)
                    <div class="space-y-2">
                        <label class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">Current Media</label>
                        <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                            @php
                                $images = is_array($post->image_url) ? $post->image_url : [$post->image_url];
                            @endphp
                            @foreach($images as $img)
                                <div class="h-32 w-32 flex-shrink-0 rounded-xl overflow-hidden border border-[#2a2a2a]">
                                    <img src="{{ $img }}" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- New Image Upload --}}
                <div class="space-y-2">
                    <label class="text-[#8b8b6b] text-xs font-bold uppercase tracking-widest">Replace Media (Select Multiple)</label>
                    {{-- 🛠️ UPDATED: Added multiple and media[] --}}
                    <input type="file" name="media[]" multiple accept="image/*" class="block w-full text-sm text-[#8b8b6b]
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-xs file:font-bold file:uppercase file:tracking-widest
                        file:bg-[#2a2a2a] file:text-white
                        hover:file:bg-[#3a3a3a] cursor-pointer">
                    <p class="text-[10px] text-[#4a4a4a]">Uploading new media will replace current files.</p>
                </div>

                <div class="flex gap-4 pt-4">
                    <a href="{{ route('user.posts') }}" class="flex-1 py-3 border border-[#2a2a2a] text-[#8b8b6b] hover:text-white rounded-xl font-bold text-center transition-all">Cancel</a>
                    <button type="submit" class="flex-1 py-3 bg-[#6b6b4b] hover:bg-[#5a5a3f] text-white rounded-xl font-bold transition-all shadow-lg shadow-[#6b6b4b]/20">Update Post</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection