@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<div class="p-8 bg-[#0a0a0a] min-h-screen flex flex-col">
    <div class="max-w-4xl mx-auto w-full flex-1 flex flex-col">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-white tracking-tight uppercase">Coach AI</h2>
            <p class="text-[#8b8b6b] text-sm uppercase font-bold tracking-widest">Powered by Gemini</p>
        </div>

        {{-- Chat Window --}}
        <div id="chat-window" class="flex-1 bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2.5rem] p-8 overflow-y-auto mb-6 space-y-8 shadow-2xl custom-scrollbar" style="max-height: 65vh;">
            {{-- Welcome Message --}}
            <div class="flex gap-4">
                <div class="w-10 h-10 bg-[#6b6b4b] rounded-xl flex items-center justify-center text-white shrink-0 shadow-lg shadow-[#6b6b4b]/10">
                    <i data-lucide="bot" class="w-5 h-5"></i>
                </div>
                <div class="bg-[#2a2a2a] text-white p-6 rounded-3xl rounded-tl-none text-sm max-w-[85%] leading-relaxed">
                    Hello {{ Auth::user()->name }}! I am Coach Flash. Ready to optimize your run today? Ask me about training or recovery.
                </div>
            </div>
        </div>

        {{-- Suggestions --}}
        <div class="mb-4">
            <p class="text-xs text-[#8b8b6b] font-bold uppercase tracking-wider mb-2">Try asking Coach Flash:</p>
            <div class="flex flex-wrap gap-2">
                <button onclick="useSuggestion('How do I start training for a 5K run?')" 
                    class="bg-[#1a1a1a] hover:bg-[#2a2a2a] border border-[#2a2a2a] hover:border-[#6b6b4b] text-white text-xs px-4 py-2 rounded-full transition-all duration-300">
                    How do I start training for a 5K? 🏃
                </button>
                <button onclick="useSuggestion('What should I eat before a long run?')" 
                    class="bg-[#1a1a1a] hover:bg-[#2a2a2a] border border-[#2a2a2a] hover:border-[#6b6b4b] text-white text-xs px-4 py-2 rounded-full transition-all duration-300">
                    Pre-run diet tips 🍎
                </button>
                <button onclick="useSuggestion('How can I prevent runner\'s knee?')" 
                    class="bg-[#1a1a1a] hover:bg-[#2a2a2a] border border-[#2a2a2a] hover:border-[#6b6b4b] text-white text-xs px-4 py-2 rounded-full transition-all duration-300">
                    Preventing knee pain 🤕
                </button>
                <button onclick="useSuggestion('What is a good post-run recovery routine?')" 
                    class="bg-[#1a1a1a] hover:bg-[#2a2a2a] border border-[#2a2a2a] hover:border-[#6b6b4b] text-white text-xs px-4 py-2 rounded-full transition-all duration-300">
                    Post-run recovery 🧘
                </button>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="relative">
            <input type="text" id="user-input" placeholder="Ask about training, diet, or recovery..." 
                class="w-full bg-[#1a1a1a] border border-[#2a2a2a] rounded-[2rem] p-6 pr-24 text-white focus:ring-1 focus:ring-[#6b6b4b] focus:border-[#6b6b4b] outline-none transition-all placeholder-[#4a4a4a] text-sm">
            
            <button id="send-btn" class="absolute right-4 top-1/2 -translate-y-1/2 bg-[#6b6b4b] hover:bg-[#7b7b5b] p-4 rounded-2xl text-white transition-all shadow-lg shadow-[#6b6b4b]/20">
                <i data-lucide="send" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
</div>

<style>
    .ai-content h1, .ai-content h2 { font-weight: 800; color: white; margin-bottom: 0.5rem; }
    .ai-content strong { color: #6b6b4b; font-weight: 900; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 10px; }
</style>

<script>
    const sendBtn = document.getElementById('send-btn');
    const userInput = document.getElementById('user-input');
    const chatWindow = document.getElementById('chat-window');

    // URLs for profile images logic
    const userPhoto = "{{ Auth::user()->profile_photo_path ? Auth::user()->profile_photo_url : '' }}";
    const userInitial = "{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}";

    function appendMessage(role, text, isLoader = false) {
        const isAI = role === 'ai';
        const loaderId = isLoader ? 'id="ai-loader"' : '';
        const formattedText = isAI && !isLoader ? marked.parse(text) : text;

        // FIXED: Avatar logic to match your Post and Group pages
        const userAvatarHtml = userPhoto 
            ? `<img src="${userPhoto}" class="w-10 h-10 rounded-xl object-cover border border-[#6b6b4b]/30 shrink-0">`
            : `<div class="w-10 h-10 bg-[#6b6b4b] rounded-xl flex items-center justify-center text-white font-black shrink-0 border border-[#6b6b4b]/20">${userInitial}</div>`;

        const msgHtml = `
            <div ${loaderId} class="flex gap-4 ${isAI ? '' : 'justify-end'} animate-in slide-in-from-bottom-2 duration-300">
                ${isAI ? '<div class="w-10 h-10 bg-[#6b6b4b] rounded-xl flex items-center justify-center text-white shrink-0"><i data-lucide="bot" class="w-5 h-5"></i></div>' : ''}
                <div class="${isAI ? 'bg-[#2a2a2a] rounded-tl-none ai-content' : 'bg-[#6b6b4b]/20 border border-[#6b6b4b]/30 rounded-tr-none'} text-white p-6 rounded-3xl text-sm max-w-[85%] leading-relaxed shadow-lg">
                    ${formattedText}
                </div>
                ${!isAI ? userAvatarHtml : ''}
            </div>
        `;
        chatWindow.insertAdjacentHTML('beforeend', msgHtml);
        chatWindow.scrollTop = chatWindow.scrollHeight;
        lucide.createIcons();
    }

    function useSuggestion(text) {
        userInput.value = text;
        sendBtn.click();
    }

    sendBtn.addEventListener('click', () => {
        const message = userInput.value.trim();
        if (!message) return;

        appendMessage('user', message);
        userInput.value = '';
        
        sendBtn.disabled = true;
        appendMessage('ai', `<div class="flex items-center gap-2"><span class="text-[#8b8b6b] text-[10px] font-bold uppercase animate-pulse">Coach Flash is writing...</span></div>`, true);

        fetch("{{ route('coach.chat') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            if(document.getElementById('ai-loader')) document.getElementById('ai-loader').remove();
            appendMessage('ai', data.reply);
        })
        .catch(() => {
            if(document.getElementById('ai-loader')) document.getElementById('ai-loader').remove();
            appendMessage('ai', "Coach Flash lost connection. Verify your API key and Internet.");
        })
        .finally(() => { sendBtn.disabled = false; userInput.focus(); });
    });

    userInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') sendBtn.click(); });
</script>
@endsection