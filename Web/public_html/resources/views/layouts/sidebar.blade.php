<aside class="w-64 bg-[#1a1a1a] border-r border-[#2a2a2a] min-h-screen sticky top-0 flex flex-col">
    {{-- Logo & Brand: Displays current role from DB --}}
    <div class="p-6 mb-4 flex items-center gap-3">
        
        {{-- 🛠️ UPDATED: Removed padding, added object-cover and scale-110 for "zoom" effect --}}
        <div class="w-14 h-14 rounded-full border-2 border-[#6b6b4b] flex items-center justify-center overflow-hidden bg-[#0a0a0a] shadow-lg shadow-[#6b6b4b]/20">
            <img src="{{ asset('public/images/logo.png') }}" alt="RunTracker" class="w-full h-full object-cover scale-110">
        </div>
        
        <h1 class="text-white font-bold text-lg tracking-tight">
            RunTracker <span class="text-[#8b8b6b] font-normal italic text-sm">{{ ucfirst(Auth::user()->role) }}</span>
        </h1>
    </div>

    <nav class="p-4 space-y-2 flex-1">
        {{-- Unified Dashboard Link: Visible to all --}}
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-[#6b6b4b] text-white' : 'text-[#8b8b6b] hover:bg-[#2a2a2a] hover:text-white' }} transition-all">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        {{-- ADMIN MANAGEMENT SECTION: Visible only to Admin role --}}
        @if(Auth::user()->role === 'admin')
            <div class="pt-4 pb-2 px-4 text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest">Management</div>
            
            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('users.*') ? 'bg-[#6b6b4b] text-white' : 'text-[#8b8b6b] hover:bg-[#2a2a2a] hover:text-white' }} transition-all">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="font-medium">Users</span>
            </a>

            <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('notifications.*') ? 'bg-[#6b6b4b] text-white' : 'text-[#8b8b6b] hover:bg-[#2a2a2a] hover:text-white' }} transition-all">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="font-medium">Notifications</span>
            </a>
            
            <a href="{{ route('sos.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('sos.*') ? 'bg-red-900/20 text-red-400 border border-red-500/20' : 'text-[#8b8b6b] hover:bg-[#2a2a2a] hover:text-white' }} transition-all">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                <span class="font-medium">SOS Signals</span>
            </a>
        @endif

        {{-- COMMUNITY SECTION --}}
        <div class="pt-4 pb-2 px-4 text-[10px] font-bold text-[#4a4a4a] uppercase tracking-widest">Community</div>

        {{-- FIXED: Coach AI only visible to Runners (Non-Admins) --}}
        @if(Auth::user()->role !== 'admin')
            <a href="{{ route('coach.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('coach.*') ? 'bg-[#6b6b4b] text-white' : 'text-[#8b8b6b] hover:bg-[#2a2a2a] hover:text-white' }} transition-all">
                <i data-lucide="sparkles" class="w-5 h-5 text-yellow-500/80"></i>
                <span class="font-medium">Coach AI</span>
            </a>
        @endif

        {{-- Events: Logic handles role-based routes --}}
        @php $eventsRoute = Auth::user()->role === 'admin' ? 'events.index' : 'user.events'; @endphp
        <a href="{{ route($eventsRoute) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('*events*') ? 'bg-[#6b6b4b] text-white' : 'text-[#8b8b6b] hover:bg-[#2a2a2a] hover:text-white' }} transition-all">
            <i data-lucide="calendar" class="w-5 h-5"></i>
            <span class="font-medium">Events</span>
        </a>

        {{-- Posts: Logic handles role-based routes --}}
        @php $postsRoute = Auth::user()->role === 'admin' ? 'posts.index' : 'user.posts'; @endphp
        <a href="{{ route($postsRoute) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('*posts*') ? 'bg-[#6b6b4b] text-white' : 'text-[#8b8b6b] hover:bg-[#2a2a2a] hover:text-white' }} transition-all">
            <i data-lucide="message-square" class="w-5 h-5"></i>
            <span class="font-medium">Posts</span>
        </a>

        {{-- Groups: Logic handles role-based routes --}}
        @php $groupsRoute = Auth::user()->role === 'admin' ? 'groups.index' : 'user.groups'; @endphp
        <a href="{{ route($groupsRoute) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('*groups*') ? 'bg-[#6b6b4b] text-white' : 'text-[#8b8b6b] hover:bg-[#2a2a2a] hover:text-white' }} transition-all">
            <i data-lucide="users-round" class="w-5 h-5"></i>
            <span class="font-medium">Groups</span>
        </a>
    </nav>

    {{-- FIXED Logout Section: Wrapped in POST Form --}}
    <div class="p-6 border-t border-[#2a2a2a] mt-auto">
        <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-500/10 transition-all group">
                <i data-lucide="log-out" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                <span class="text-sm font-bold uppercase tracking-widest">Logout</span>
            </button>
        </form>
    </div>
</aside>