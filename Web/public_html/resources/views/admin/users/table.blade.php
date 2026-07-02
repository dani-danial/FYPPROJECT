<div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl overflow-hidden shadow-2xl">
    <!-- Filters -->
    <div class="p-6 border-b border-[#2a2a2a] flex gap-4 items-center flex-wrap">
        <div class="flex-1 min-w-[200px]">
            <input type="text" placeholder="Search users..." 
                   class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-4 py-2 text-white placeholder-[#4a4a4a] focus:border-[#6b6b4b] transition-colors"
                   @input="filterUsers = $el.value">
        </div>
        <select class="bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-4 py-2 text-white focus:border-[#6b6b4b] transition-colors">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="banned">Banned</option>
            <option value="verified">Verified</option>
        </select>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto" x-data="{users: @json($users ?? []), filterUsers: ''}">
        <table class="w-full">
            <thead class="bg-[#0a0a0a] border-b border-[#2a2a2a]">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">User</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">Email</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">Activity</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#8b8b6b] uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2a2a2a]">
                <template x-for="user in users" :key="user.id">
                    <tr class="hover:bg-[#0a0a0a] transition-colors group" 
                        x-show="user.name.toLowerCase().includes(filterUsers.toLowerCase()) || user.email.toLowerCase().includes(filterUsers.toLowerCase())">
                        <!-- User Info -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#6b6b4b] flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <template x-if="user.profile_photo_path">
                                        <img :src="'{{ asset('storage') }}/' + user.profile_photo_path" :alt="user.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!user.profile_photo_path">
                                        <span class="text-white font-bold" x-text="user.name.charAt(0).toUpperCase()"></span>
                                    </template>
                                </div>
                                <div>
                                    <p class="text-white font-bold text-sm" x-text="user.name"></p>
                                    <p class="text-[#8b8b6b] text-xs" x-text="'@' + (user.username || 'user')"></p>
                                </div>
                            </div>
                        </td>

                        <!-- Email -->
                        <td class="px-6 py-4">
                            <p class="text-white text-sm" x-text="user.email"></p>
                            <template x-if="user.email_verified_at">
                                <span class="inline-block text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-1 rounded mt-1">Verified</span>
                            </template>
                        </td>

                        <!-- Activity Sparkline -->
                        <td class="px-6 py-4">
                            <div class="w-20 h-8 bg-[#0a0a0a] rounded border border-[#2a2a2a] flex items-end gap-0.5 p-1">
                                <!-- Mini sparkline - shows 7 days of activity -->
                                <div class="flex-1 bg-[#6b6b4b]/60 rounded-sm" :style="`height: ${20 + Math.random() * 80}%`"></div>
                                <div class="flex-1 bg-[#6b6b4b]/60 rounded-sm" :style="`height: ${20 + Math.random() * 80}%`"></div>
                                <div class="flex-1 bg-[#6b6b4b]/60 rounded-sm" :style="`height: ${20 + Math.random() * 80}%`"></div>
                                <div class="flex-1 bg-[#6b6b4b]/60 rounded-sm" :style="`height: ${20 + Math.random() * 80}%`"></div>
                                <div class="flex-1 bg-[#6b6b4b]/60 rounded-sm" :style="`height: ${20 + Math.random() * 80}%`"></div>
                                <div class="flex-1 bg-[#6b6b4b]/60 rounded-sm" :style="`height: ${20 + Math.random() * 80}%`"></div>
                                <div class="flex-1 bg-[#6b6b4b] rounded-sm" :style="`height: ${20 + Math.random() * 80}%`"></div>
                            </div>
                        </td>

                        <!-- Status Badges -->
                        <td class="px-6 py-4">
                            <div class="flex gap-2 flex-wrap">
                                <template x-if="user.role === 'admin'">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-500/20 text-purple-400 rounded-full text-[10px] font-bold">
                                        <span class="w-2 h-2 bg-purple-400 rounded-full"></span>
                                        Admin
                                    </span>
                                </template>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold"
                                      :class="user.is_banned ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400'">
                                    <span class="w-2 h-2 rounded-full" :class="user.is_banned ? 'bg-red-400' : 'bg-emerald-400'"></span>
                                    <span x-text="user.is_banned ? 'Banned' : 'Active'"></span>
                                </span>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <!-- Ban/Unban Toggle -->
                                <button @click="toggleBan(user.id)" 
                                        class="p-2 rounded-lg transition-all"
                                        :class="user.is_banned ? 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30' : 'bg-red-500/20 text-red-400 hover:bg-red-500/30'"
                                        :title="user.is_banned ? 'Unban user' : 'Ban user'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                    </svg>
                                </button>

                                <!-- Verify Toggle -->
                                <button @click="toggleVerify(user.id)"
                                        class="p-2 rounded-lg transition-all"
                                        :class="user.email_verified_at ? 'bg-[#2a2a2a] text-[#8b8b6b]' : 'bg-blue-500/20 text-blue-400 hover:bg-blue-500/30'"
                                        :title="user.email_verified_at ? 'Already verified' : 'Verify user'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>

                                <!-- View Details -->
                                <a :href="`/user/${user.username}`" target="_blank"
                                   class="p-2 rounded-lg bg-[#2a2a2a] text-[#8b8b6b] hover:text-white transition-colors"
                                   title="View profile">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!-- Empty State -->
        <template x-if="users.length === 0">
            <div class="p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-[#4a4a4a] mx-auto mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <p class="text-[#8b8b6b] text-sm">No users found</p>
            </div>
        </template>
    </div>
</div>

<script>
function toggleBan(userId) {
    // Alpine.js magic here - call controller to ban/unban
    fetch(`/admin/users/${userId}/ban`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(() => location.reload());
}

function toggleVerify(userId) {
    // Call controller to verify user
    fetch(`/admin/users/${userId}/verify`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(() => location.reload());
}
</script>
