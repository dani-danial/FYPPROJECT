@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('user.posts') }}" class="inline-flex items-center gap-2 text-[#8b8b6b] hover:text-white transition-colors mb-8 group">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
            <span class="text-xs font-bold uppercase tracking-widest">Back to Feed</span>
        </a>

        <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-10 shadow-2xl">
            <h2 class="text-3xl font-black text-white tracking-tight mb-2">Create New Post</h2>
            <p class="text-[#4a4a4a] text-sm mb-10 font-bold uppercase tracking-widest">Share your latest run</p>

            <form action="{{ route('user.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                
                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Caption</label>
                    <textarea name="content" required 
                        placeholder="Tell everyone about your run..." 
                        class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-2xl p-6 text-white focus:ring-1 focus:ring-[#6b6b4b] focus:border-[#6b6b4b] h-48 outline-none transition-all resize-none placeholder-[#2a2a2a]"></textarea>
                </div>

                <div class="space-y-3">
                    <label class="block text-[#8b8b6b] text-[10px] font-bold uppercase tracking-widest">Add Media (Select Multiple)</label>
                    {{-- 🛠️ UPDATED: Added multiple and media[] --}}
                    <input type="file" name="media[]" multiple accept="image/*,video/*" 
                        class="block w-full text-sm text-[#8b8b6b] file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:bg-[#6b6b4b]/20 file:text-[#6b6b4b] file:font-bold hover:file:bg-[#6b6b4b]/30 cursor-pointer">
                </div>

                <div class="pt-6 flex gap-4">
                    <a href="{{ route('user.posts') }}" 
                        class="flex-1 py-5 bg-[#2a2a2a] text-[#8b8b6b] text-center rounded-2xl font-black text-xs uppercase tracking-widest hover:text-white transition-all border border-white/5">
                        Cancel
                    </a>
                    <button type="submit" 
                        class="flex-1 py-5 bg-[#6b6b4b] hover:bg-[#7b7b5b] text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-[#6b6b4b]/20">
                        Post Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection