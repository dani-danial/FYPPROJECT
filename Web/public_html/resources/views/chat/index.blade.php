@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#0a0a0a] min-h-screen flex flex-col">
    <div class="max-w-6xl mx-auto w-full flex-1 flex flex-col">
        
        {{-- Page Header --}}
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-white tracking-tight">Messages</h2>
            <p class="text-[#8b8b6b] text-xs uppercase font-bold tracking-widest">Running Community Chat</p>
        </div>

        {{-- Main Chat Container --}}
        <div class="flex flex-1 bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl overflow-hidden shadow-2xl" style="height: calc(100vh - 200px);">
            
            {{-- Left Sidebar: Conversations List --}}
            <div class="w-full md:w-96 flex flex-col border-r border-[#2a2a2a] bg-[#1a1a1a]">
                <div class="p-4 border-b border-[#2a2a2a]">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8b8b6b]"></i>
                        <input type="text" placeholder="Search messages..." 
                               class="w-full bg-[#2a2a2a] text-white pl-10 pr-4 py-2 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#6b6b4b] border-none transition-all">
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    @forelse($conversations as $conv)
                        @php 
                            $otherUser = ($conv->sender_id == Auth::id()) ? $conv->receiver : $conv->sender; 
                            // 🛠️ PREPARE IMAGE URL FOR JS
                            $photoUrl = $otherUser->profile_photo_path ? $otherUser->profile_photo_url : null;
                        @endphp
                        
                        {{-- 🛠️ UPDATED: Passing photoUrl to the loadChat function --}}
                        <button onclick="loadChat({{ $conv->id }}, '{{ $otherUser->name }}', '{{ strtoupper(substr($otherUser->name, 0, 1)) }}', '{{ $photoUrl }}')" 
                             class="w-full p-4 flex items-start gap-3 hover:bg-[#2a2a2a] transition-colors border-b border-[#2a2a2a]/30 group text-left">
                            
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-[#6b6b4b] flex items-center justify-center text-white font-bold text-xl overflow-hidden border border-[#2a2a2a]">
                                    @if($otherUser->profile_photo_path)
                                        <img src="{{ $otherUser->profile_photo_url }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-[#1a1a1a]"></div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-1">
                                    <h3 class="font-medium text-white text-sm truncate">{{ $otherUser->name }}</h3>
                                    <span class="text-[10px] text-[#8b8b6b]">{{ $conv->updated_at->diffForHumans(null, true) }}</span>
                                </div>
                                <p class="text-xs text-[#8b8b6b] truncate">
                                    {{ $conv->messages->last()->body ?? 'Start a conversation...' }}
                                </p>
                            </div>
                        </button>
                    @empty
                        <div class="p-10 text-center">
                            <p class="text-xs text-[#4a4a4a] font-bold uppercase tracking-widest">No chats yet</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Right Section: Active Chat View --}}
            <div id="chat-view" class="flex-1 flex flex-col bg-[#0a0a0a]">
                
                {{-- Empty State --}}
                <div id="welcome-screen" class="flex-1 flex flex-col items-center justify-center text-center p-12">
                    <div class="w-20 h-20 bg-[#2a2a2a] rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="message-square" class="w-10 h-10 text-[#6b6b4b]"></i>
                    </div>
                    <h3 class="text-xl font-medium text-white mb-2">Select a chat to start messaging</h3>
                    <p class="text-[#8b8b6b] text-sm max-w-xs">Choose from your existing conversations or start a new one.</p>
                </div>

                {{-- Active Chat UI --}}
                <div id="active-chat-ui" class="hidden flex-1 flex flex-col h-full overflow-hidden">
                    <div class="p-4 bg-[#1a1a1a] border-b border-[#2a2a2a] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                {{-- 🛠️ UPDATED: Added overflow-hidden to ensure image stays circular --}}
                                <div id="active-user-avatar" class="w-10 h-10 rounded-full bg-[#6b6b4b] flex items-center justify-center text-white font-bold text-lg overflow-hidden">
                                    </div>
                                <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-[#1a1a1a]"></div>
                            </div>
                            <div>
                                <h3 id="active-user-name" class="font-medium text-white text-sm"></h3>
                                <p class="text-[10px] text-green-500 font-bold uppercase">Online</p>
                            </div>
                        </div>
                    </div>

                    {{-- Messages Window --}}
                    <div id="message-window" class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar flex flex-col bg-[#0a0a0a]">
                    </div>

                    {{-- Message Input --}}
                    <div class="p-4 bg-[#1a1a1a] border-t border-[#2a2a2a]">
                        <form id="chat-form" class="flex items-center gap-2">
                            <div class="flex-1 bg-black rounded-xl flex items-center px-4 py-2 border border-[#2a2a2a] focus-within:border-[#6b6b4b] transition-all">
                                <input type="text" id="message-input" placeholder="Type a message..." autocomplete="off"
                                       style="background-color: black !important; color: white !important;"
                                       class="flex-1 bg-transparent text-white text-sm focus:outline-none py-1">
                            </div>
                            <button type="submit" class="p-3 bg-[#6b6b4b] hover:bg-[#7b7b5b] rounded-xl text-white transition-all shadow-lg shadow-[#6b6b4b]/10">
                                <i data-lucide="send" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 10px; }
</style>

<script>
    let currentConversationId = null;

    // 🛠️ UPDATED: Added photoUrl parameter
    function loadChat(id, name, initial, photoUrl) {
        currentConversationId = id;
        document.getElementById('welcome-screen').classList.add('hidden');
        document.getElementById('active-chat-ui').classList.remove('hidden');
        document.getElementById('active-user-name').innerText = name;
        
        // 🛠️ UPDATED: Logic to show Image OR Text
        const avatarEl = document.getElementById('active-user-avatar');
        if (photoUrl && photoUrl !== 'null' && photoUrl !== '') {
            avatarEl.innerHTML = `<img src="${photoUrl}" class="w-full h-full object-cover">`;
        } else {
            avatarEl.innerText = initial;
        }
        
        const messageWindow = document.getElementById('message-window');
        messageWindow.innerHTML = '<div class="flex-1 flex items-center justify-center text-[#4a4a4a] text-xs font-bold uppercase tracking-widest animate-pulse">Synchronizing...</div>';

        fetch(`/chat/${id}`)
            .then(res => res.json())
            .then(data => {
                messageWindow.innerHTML = '';
                if(data.messages.length === 0) {
                    messageWindow.innerHTML = '<div class="text-center text-[#4a4a4a] py-10 text-xs italic">No messages yet.</div>';
                } else {
                    data.messages.forEach(msg => appendMessage(msg, data.user_id));
                }
                messageWindow.scrollTop = messageWindow.scrollHeight;
            });
    }

    function appendMessage(msg, currentUserId) {
        const isMe = msg.user_id == currentUserId;
        const html = `
            <div class="flex ${isMe ? 'justify-end' : 'justify-start'} animate-in fade-in slide-in-from-bottom-2 duration-300">
                <div class="${isMe ? 'bg-[#6b6b4b] rounded-tr-none' : 'bg-[#2a2a2a] rounded-tl-none'} px-4 py-2.5 rounded-2xl max-w-[70%] shadow-lg">
                    <p class="text-sm text-white leading-relaxed">${msg.body}</p>
                    <div class="flex items-center gap-1 justify-end mt-1 opacity-50">
                        <span class="text-[9px] text-white font-medium">
                            ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </span>
                        ${isMe ? '<i data-lucide="check-check" class="w-3 h-3 text-white"></i>' : ''}
                    </div>
                </div>
            </div>`;
        document.getElementById('message-window').insertAdjacentHTML('beforeend', html);
        if (window.lucide) lucide.createIcons();
    }

    document.getElementById('chat-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const body = input.value.trim();
        if(!body || !currentConversationId) return;

        fetch(`/chat/${currentConversationId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ body: body })
        })
        .then(res => res.json())
        .then(msg => {
            appendMessage(msg, {{ Auth::id() }});
            input.value = '';
            const window = document.getElementById('message-window');
            window.scrollTo({ top: window.scrollHeight, behavior: 'smooth' });
        });
    });
</script>
@endsection
