<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RunTracker Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#0a0a0a] text-white font-sans antialiased" x-data="{ sidebarOpen: true }">
    <div class="min-h-screen flex flex-col">
        
        {{-- Header Section: Matches React Component --}}
        <header class="bg-[#1a1a1a] border-b border-[#2a2a2a] px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-[#2a2a2a] rounded-lg transition-colors text-white">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!sidebarOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="sidebarOpen"></i>
                </button>
                <div class="flex items-center gap-2">
                    <span class="text-xl">🏃</span>
                    <h1 class="text-white font-bold text-lg">RunTracker <span class="text-[#8b8b6b] font-normal italic">Admin</span></h1>
                </div>
            </div>
            <div class="text-xs text-[#4a4a4a] font-bold uppercase tracking-widest hidden md:block">
                Laravel & MySQL Control Panel
            </div>
        </header>

        <div class="flex flex-1">
            {{-- Sidebar Section --}}
            <aside 
                :class="sidebarOpen ? 'w-64' : 'w-0'" 
                class="bg-[#1a1a1a] border-r border-[#2a2a2a] min-h-[calc(100vh-73px)] transition-all duration-300 overflow-hidden sticky top-[73px]"
            >
                <nav class="p-4 space-y-1">
                    @php
                        $adminTabs = [
                            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard'],
                            ['id' => 'events', 'label' => 'Events', 'icon' => 'calendar', 'route' => 'events.index'],
                            ['id' => 'users', 'label' => 'Users', 'icon' => 'users', 'route' => 'users.index'],
                            ['id' => 'groups', 'label' => 'Groups', 'icon' => 'users-round', 'route' => 'groups.index'],
                            ['id' => 'posts', 'label' => 'Content Posts', 'icon' => 'message-square', 'route' => 'posts.index'],
                            ['id' => 'notifications', 'label' => 'Notifications', 'icon' => 'bell', 'route' => 'notifications.index'],
                            ['id' => 'sos', 'label' => 'SOS Signals', 'icon' => 'alert-triangle', 'route' => 'sos.index'],
                        ];
                    @endphp

                    @foreach($adminTabs as $tab)
                        <a href="{{ route($tab['route']) }}" 
                           class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs($tab['route']) ? 'bg-[#6b6b4b] text-white' : 'text-[#b0b0a0] hover:bg-[#2a2a2a] hover:text-white' }}">
                            <i data-lucide="{{ $tab['icon'] }}" class="w-5 h-5"></i>
                            <span class="whitespace-nowrap font-medium">{{ $tab['label'] }}</span>
                        </a>
                    @endforeach

                    <form method="POST" action="{{ route('logout') }}" class="mt-8 pt-4 border-t border-[#2a2a2a]">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-400 hover:bg-red-500/10 transition-colors">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                            <span class="font-medium">Logout</span>
                        </button>
                    </form>
                </nav>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 p-8 bg-[#0a0a0a]">
                @yield('content')
            </main>
        </div>

    

    <script>
        lucide.createIcons();
    </script>
</body>
</html>