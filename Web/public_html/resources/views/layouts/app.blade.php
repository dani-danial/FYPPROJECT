<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'RunTracker') }}</title>

        <script>
            window.authUserId = {{ auth()->id() }};
        </script>

        <link rel="stylesheet" href="https://runtracker.fun/public/build/assets/app-YR1x179d.css">
        <script type="module" src="https://runtracker.fun/public/build/assets/app-OANExTJh.js"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style>
            .page-transition {
                opacity: 0;
                transform: translateY(12px);
                transition: opacity 0.4s ease-out, transform 0.4s ease-out;
            }
            .page-transition-active {
                opacity: 1;
                transform: translateY(0);
            }
            #page-loader {
                position: fixed;
                inset: 0;
                background: #0a0a0a;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: opacity 0.3s ease-in-out;
            }
            /* EXCELLENT UI: Custom Scrollbar */
            ::-webkit-scrollbar { width: 8px; }
            ::-webkit-scrollbar-track { background: #0a0a0a; }
            ::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 10px; }
            ::-webkit-scrollbar-thumb:hover { background: #6b6b4b; }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#0a0a0a] text-white">
        
        {{-- System Loader --}}
        <div id="page-loader">
            <div class="w-10 h-10 border-4 border-[#6b6b4b]/20 border-t-[#6b6b4b] rounded-full animate-spin"></div>
        </div>

        <div class="min-h-screen flex">
            {{-- SIDEBAR INCLUSION --}}
            @include('layouts.sidebar')

            <div class="flex-1 flex flex-col">
                {{-- HEADER NAVIGATION --}}
                <header class="bg-[#1a1a1a] border-b border-[#2a2a2a] px-8 py-4 flex items-center justify-between sticky top-0 z-10">
                    
                    <div class="flex-1 flex flex-col max-w-2xl">
                        {{-- Breadcrumbs --}}
                        <nav class="flex text-[10px] font-bold uppercase tracking-[0.2em] text-[#4a4a4a] mb-2">
                            <span class="hover:text-[#6b6b4b] cursor-default">RunTracker</span>
                            <span class="mx-2">/</span>
                            <span class="text-[#8b8b6b] uppercase">{{ Request::segment(1) ? str_replace('-', ' ', Request::segment(1)) : 'Dashboard' }}</span>
                        </nav>
                        
                        {{-- Global Search --}}
                        <div class="relative">
                            <form action="{{ route('users.search') }}" method="GET">
                                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#4a4a4a]"></i>
                                <input type="text" name="query" placeholder="Search users by name or goal..." 
                                       style="background-color: black !important; color: white !important;"
                                       class="w-full border border-[#2a2a2a] rounded-xl py-2 pl-12 pr-4 text-sm focus:outline-none focus:border-[#6b6b4b] transition-all">
                            </form>
                        </div>
                    </div>
                    
                    {{-- HEADER ACTIONS --}}
                    <div class="flex items-center gap-6 ml-6">
                        @if(Auth::user()->role !== 'admin')
                        <div class="flex items-center gap-3">
                            
                            {{-- Search Nearby Shortcut --}}
                            <a href="{{ route('user.nearby') }}" 
                               class="relative p-2 bg-[#2a2a2a] rounded-full text-[#8b8b6b] hover:text-white hover:bg-[#6b6b4b] transition-colors"
                               title="Find Nearby Runners">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                            </a>

                            @php
                                $customNotificationCount = \App\Models\Notification::where('user_id', Auth::id())
                                                                            ->where('status', 'unread') 
                                                                            ->count();
                                $notifications = \App\Models\Notification::where('user_id', Auth::id())
                                                                            ->latest()
                                                                            ->take(5)
                                                                            ->get();
                            @endphp

                            {{-- Bell Notifications --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" 
                                        class="relative p-2 bg-[#2a2a2a] rounded-full text-[#8b8b6b] hover:text-white transition-colors focus:outline-none">
                                    <i data-lucide="bell" class="w-5 h-5"></i>
                                    <span id="notification-badge" 
                                          class="absolute top-0 right-0 w-4 h-4 bg-[#6b6b4b] text-[10px] text-white flex items-center justify-center rounded-full font-bold border-2 border-[#1a1a1a] {{ $customNotificationCount > 0 ? '' : 'hidden' }}">
                                        {{ $customNotificationCount }}
                                    </span>
                                </button>
                                
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     @click.outside="open = false" 
                                     class="absolute right-0 mt-2 w-80 bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl shadow-xl py-2 z-50"
                                     style="display: none;">
                                    
                                    <div class="px-4 py-2 border-b border-[#2a2a2a] flex justify-between items-center">
                                        <span class="text-xs font-black text-white uppercase tracking-wider">Notifications</span>
                                        <a href="{{ route('user.notifications') }}" class="text-[9px] font-black text-[#8b8b6b] hover:text-[#6b6b4b] uppercase tracking-widest">View All</a>
                                    </div>
                                    
                                    <div class="max-h-60 overflow-y-auto">
                                        @forelse($notifications as $notif)
                                            <div class="px-4 py-3 hover:bg-[#2a2a2a] border-b border-[#2a2a2a]/30 transition-colors text-left">
                                                <p class="text-[11px] font-bold text-white mb-0.5">{{ $notif->title }}</p>
                                                <p class="text-[10px] text-[#b0b0a0] leading-relaxed">{{ $notif->message }}</p>
                                                <span class="text-[9px] text-[#8b8b6b] font-bold mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                                            </div>
                                        @empty
                                            <div class="px-4 py-6 text-center text-xs text-[#4a4a4a] font-bold uppercase tracking-widest">
                                                No new alerts
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            @php
                                $unreadMessages = \App\Models\Message::whereHas('conversation', function($q) {
                                    $q->where('receiver_id', Auth::id());
                                })->where('is_read', false)->where('user_id', '!=', Auth::id())->count();
                            @endphp

                            {{-- Chat Messages --}}
                            <a href="{{ route('chat.index') }}" 
                               onclick="document.getElementById('message-badge').classList.add('hidden')"
                               class="relative p-2 bg-[#2a2a2a] rounded-full text-[#8b8b6b] hover:text-white transition-colors">
                                <i data-lucide="message-circle" class="w-5 h-5"></i>
                                <span id="message-badge" 
                                      class="absolute top-0 right-0 w-4 h-4 bg-[#6b6b4b] text-[10px] text-white flex items-center justify-center rounded-full font-bold border-2 border-[#1a1a1a] {{ $unreadMessages > 0 ? '' : 'hidden' }}">
                                    {{ $unreadMessages }}
                                </span>
                            </a>

                            {{-- Settings --}}
                            <a href="{{ route('profile.edit') }}" class="p-2 bg-[#2a2a2a] rounded-full text-[#8b8b6b] hover:text-white transition-colors">
                                <i data-lucide="settings" class="w-5 h-5"></i>
                            </a>
                        </div>
                        @endif

                        {{-- USER PROFILE DROPDOWN --}}
                        <div class="flex items-center gap-3 border-l border-[#2a2a2a] pl-6 relative" x-data="{ open: false }">
                            <div class="text-right hidden sm:block">
                                <p class="text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest">Welcome back,</p>
                                <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                            </div>

                            <button @click="open = !open" class="focus:outline-none">
                                <div class="w-10 h-10 bg-[#6b6b4b] rounded-full flex items-center justify-center text-white font-bold border-2 border-transparent hover:border-[#6b6b4b] transition-all overflow-hidden">
                                    @if(Auth::user()->profile_photo_path)
                                        {{-- 🛠️ FIXED: Using the app_data bypass URL for Hostinger --}}
                                        <img src="{{ url('app_data/app/public/' . Auth::user()->profile_photo_path) }}?t={{ time() }}" 
                                             class="w-full h-full object-cover"
                                             onerror="this.style.display='none'; this.parentElement.innerText='{{ substr(Auth::user()->name, 0, 1) }}'">
                                    @else
                                        <span class="text-lg uppercase">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    @endif
                                </div>
                            </button>

                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 @click.outside="open = false" 
                                 class="absolute right-0 top-full mt-2 w-48 bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl shadow-xl py-2 z-50"
                                 style="display: none;">
                                
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-[#8b8b6b] hover:text-white hover:bg-[#2a2a2a] transition-colors">
                                    <i data-lucide="user" class="w-4 h-4"></i> Profile Settings
                                </a>
                                
                                <div class="h-px bg-[#2a2a2a] my-1 mx-2"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 transition-colors">
                                        <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <main id="main-content" class="flex-1 page-transition">
                    @yield('content')
                </main>
            </div>
        </div>

        <script>
            lucide.createIcons();
            
            document.addEventListener('alpine:init', () => {
                Alpine.effect(() => {
                    lucide.createIcons();
                });
            });

            document.addEventListener("DOMContentLoaded", function() {
                const loader = document.getElementById('page-loader');
                const content = document.getElementById('main-content');

                setTimeout(() => {
                    if(loader) {
                        loader.style.opacity = '0';
                        setTimeout(() => loader.style.display = 'none', 300);
                    }
                    if(content) {
                        content.classList.add('page-transition-active');
                    }
                }, 150);

                document.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        const destination = this.href;
                        if (this.hostname === window.location.hostname && !this.hash && this.target !== '_blank' && !this.closest('form')) {
                            e.preventDefault();
                            content.classList.remove('page-transition-active');
                            setTimeout(() => { window.location.href = destination; }, 350);
                        }
                    });
                });
            });
        </script>
    </body>
</html>