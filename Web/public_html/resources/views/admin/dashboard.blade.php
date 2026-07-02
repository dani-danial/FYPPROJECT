@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-[#0a0a0a]" x-data="adminDashboard()">
    <!-- Responsive Sidebar -->
    <aside class="bg-[#1a1a1a] border-r border-[#2a2a2a] w-64 overflow-y-auto transition-all duration-300"
           :class="{'w-20': sidebarCollapsed, 'w-64': !sidebarCollapsed}">
        
        <!-- Logo / Brand -->
        <div class="p-6 border-b border-[#2a2a2a] flex items-center justify-between">
            <h1 class="text-white font-black text-lg" :class="{'hidden': sidebarCollapsed}">ADMIN CONTROL</h1>
            <button @click="sidebarCollapsed = !sidebarCollapsed" class="text-[#8b8b6b] hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Global Search -->
        <div class="p-4 border-b border-[#2a2a2a]">
            <div class="relative" x-data="{searching: false}">
                <input type="text" 
                       @focus="searching = true"
                       @blur="searching = false"
                       placeholder="Search..." 
                       class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-3 py-2 text-sm text-white placeholder-[#4a4a4a] focus:border-[#6b6b4b] transition-colors"
                       @input="adminSearch = $el.value">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute right-3 top-2.5 text-[#4a4a4a]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="p-4 space-y-2">
            @php
                $menuItems = [
                    ['icon' => 'users', 'label' => 'User Management', 'route' => 'users.index', 'section' => 'users'],
                    ['icon' => 'bell', 'label' => 'Notifications', 'route' => 'notifications.index', 'section' => 'notifications'],
                    ['icon' => 'alert-circle', 'label' => 'SOS Signals', 'route' => 'sos.index', 'section' => 'sos'],
                    ['icon' => 'calendar', 'label' => 'Events', 'route' => 'events.index', 'section' => 'events'],
                    ['icon' => 'message-square', 'label' => 'Posts', 'route' => 'posts.index', 'section' => 'posts'],
                    ['icon' => 'users-plus', 'label' => 'Groups', 'route' => 'groups.index', 'section' => 'groups'],
                ];
            @endphp

            @foreach($menuItems as $item)
            <a href="{{ route($item['route']) }}"
               @click="currentSection = '{{ $item['section'] }}'"
               class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all group relative"
               :class="currentSection === '{{ $item['section'] }}' ? 'bg-[#6b6b4b] text-white' : 'text-[#8b8b6b] hover:text-white hover:bg-[#2a2a2a]'">
                
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    @if($item['icon'] === 'users')
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    @elseif($item['icon'] === 'bell')
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    @elseif($item['icon'] === 'alert-circle')
                        <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>
                    @elseif($item['icon'] === 'calendar')
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>
                    @elseif($item['icon'] === 'message-square')
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    @elseif($item['icon'] === 'users-plus')
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="16" y1="11" x2="22" y2="11"></line><line x1="19" y1="8" x2="19" y2="14"></line>
                    @endif
                </svg>

                <span :class="{'hidden': sidebarCollapsed}">{{ $item['label'] }}</span>

                <!-- Tooltip for collapsed state -->
                <div v-if="sidebarCollapsed" class="absolute left-20 bg-[#2a2a2a] text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity">
                    {{ $item['label'] }}
                </div>
            </a>
            @endforeach
        </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation Bar -->
        <header class="bg-[#1a1a1a] border-b border-[#2a2a2a] px-8 py-4 flex items-center justify-between">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('dashboard') }}" class="text-[#8b8b6b] hover:text-white transition-colors">Admin</a>
                <span class="text-[#4a4a4a]">/</span>
                <span class="text-white font-semibold" x-text="getBreadcrumb()"></span>
            </nav>

            <!-- User Profile & Settings -->
            <div class="flex items-center gap-6">
                <!-- Last Updated Info -->
                <div class="text-right text-xs">
                    <p class="text-[#8b8b6b]">Last updated</p>
                    <p class="text-white font-bold" x-text="new Date().toLocaleString()"></p>
                </div>

                <!-- Admin Profile -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#6b6b4b] flex items-center justify-center overflow-hidden">
                        @if(Auth::user()->profile_photo_path)
                            <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-white font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">{{ Auth::user()->name }}</p>
                        <p class="text-[#8b8b6b] text-xs">Admin</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Scrollable Content Area -->
        <main class="flex-1 overflow-auto">
            <div class="p-8 space-y-8">
                <!-- Users Section -->
                <section x-show="currentSection === 'users'" class="space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-white mb-2">User Management</h2>
                        <p class="text-[#8b8b6b] text-sm">Control Center - Manage user accounts and permissions</p>
                    </div>
                    @include('admin.users.table')
                </section>

                <!-- Notifications Section -->
                <section x-show="currentSection === 'notifications'" class="space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-white mb-2">Notification Management</h2>
                        <p class="text-[#8b8b6b] text-sm">Communication Hub - Send global alerts to users</p>
                    </div>
                    @include('admin.notifications.template-builder')
                </section>

                <!-- SOS Signals Section -->
                <section x-show="currentSection === 'sos'" class="space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-white mb-2">SOS Signals</h2>
                        <p class="text-[#8b8b6b] text-sm">Live Response - Active emergency signals</p>
                    </div>
                    @include('admin.sos.emergency-dashboard')
                </section>

                <!-- Events Section -->
                <section x-show="currentSection === 'events'" class="space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-white mb-2">Event Management</h2>
                        <p class="text-[#8b8b6b] text-sm">Approval Kanban - Manage event lifecycle</p>
                    </div>
                    @include('admin.events.kanban')
                </section>

                <!-- Posts Section -->
                <section x-show="currentSection === 'posts'" class="space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-white mb-2">Post Management</h2>
                        <p class="text-[#8b8b6b] text-sm">Social Moderation - Review and manage posts</p>
                    </div>
                    @include('admin.posts.moderation')
                </section>

                <!-- Groups Section -->
                <section x-show="currentSection === 'groups'" class="space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-white mb-2">Group Management</h2>
                        <p class="text-[#8b8b6b] text-sm">Community Growth - Monitor and promote groups</p>
                    </div>
                    @include('admin.groups.growth-dashboard')
                </section>
            </div>
        </main>
    </div>
</div>

<script>
function adminDashboard() {
    return {
        sidebarCollapsed: false,
        currentSection: 'users',
        adminSearch: '',
        
        getBreadcrumb() {
            const breadcrumbs = {
                'users': 'User Management',
                'notifications': 'Notification Management',
                'sos': 'SOS Signals',
                'events': 'Event Management',
                'posts': 'Post Management',
                'groups': 'Group Management'
            };
            return breadcrumbs[this.currentSection] || 'Dashboard';
        }
    };
}
</script>
@endsection
