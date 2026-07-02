# ADMIN DASHBOARD - QUICK START GUIDE

## 🚀 Getting Started in 5 Minutes

### Step 1: Create Admin Dashboard Controller
```php
php artisan make:controller AdminDashboardController
```

**app/Http/Controllers/AdminDashboardController.php**:
```php
<?php
namespace App\Http\Controllers;
use App\Models\{User, Group, Event, Post, SosSignal};

class AdminDashboardController extends Controller {
    public function index() {
        return view('admin.dashboard', [
            'users' => User::all(),
            'groups' => Group::with('users')->get(),
            'events' => Event::all(),
            'posts' => Post::with('user', 'likers')->get(),
            'reportedPosts' => [], // Implement with PostReport model
            'sosSignals' => SosSignal::all(),
            'activeSos' => SosSignal::where('status', 'active')->get(),
            'recentNotifications' => [],
            'activeRunners' => User::count(),
            'featuredGroupIds' => Group::where('is_featured', true)->pluck('id'),
        ]);
    }
}
```

### Step 2: Add Route
**routes/web.php**:
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
});
```

### Step 3: Add Admin Middleware (if not exists)
**app/Http/Middleware/AdminMiddleware.php**:
```php
<?php
namespace App\Http\Middleware;
use Closure;

class AdminMiddleware {
    public function handle($request, Closure $next) {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }
        return redirect('/');
    }
}
```

Register in **app/Http/Kernel.php**:
```php
protected $routeMiddleware = [
    // ... existing
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
];
```

### Step 4: Access Dashboard
Navigate to: `http://localhost:8000/admin/dashboard`

---

## 🔧 Essential Routes to Add

```php
// UserController methods
Route::post('/admin/users/{id}/ban', [UserController::class, 'ban']);
Route::post('/admin/users/{id}/verify', [UserController::class, 'verify']);

// EventController methods
Route::post('/admin/events/{id}/approve', [EventController::class, 'approve']);

// PostController methods
Route::delete('/admin/posts/{id}', [PostController::class, 'destroy']);
Route::post('/admin/reports/{id}/dismiss', [PostController::class, 'dismissReport']);
Route::post('/admin/posts/bulk-delete', [PostController::class, 'bulkDelete']);
Route::post('/admin/posts/bulk-feature', [PostController::class, 'bulkFeature']);

// SosController methods
Route::post('/admin/sos/{id}/resolve', [SosController::class, 'resolve']);

// GroupController methods
Route::post('/admin/groups/{id}/feature', [GroupController::class, 'feature']);
```

---

## 🎨 Quick Customization

### Change Brand Name
**admin/dashboard.blade.php**, Line 25:
```blade
<h1 class="text-white font-black text-lg">YOUR APP NAME</h1>
```

### Change Colors
Replace `#6b6b4b` (khaki) with your brand color throughout:
```bash
# In VS Code: Ctrl+H (Find & Replace)
# Find: #6b6b4b
# Replace: #your-color
```

### Add Menu Items
**admin/dashboard.blade.php**, Line 48-65:
```blade
@php
    $menuItems = [
        ['icon' => 'users', 'label' => 'Users', 'route' => 'admin.users.index', 'section' => 'users'],
        ['icon' => 'bell', 'label' => 'New Item', 'route' => 'route.name', 'section' => 'new'],
    ];
@endphp
```

---

## 📊 Data Binding Quick Reference

### Show Dynamic User Count
```blade
<p x-text="users.length"></p>
```

### Show Dynamic Group List
```blade
<template x-for="group in groups">
    <p x-text="group.name"></p>
</template>
```

### Conditional Visibility
```blade
<template x-if="currentSection === 'users'">
    @include('admin.users.table')
</template>
```

---

## 🧪 Test Data Script

Run this in tinker to populate test data:

```php
php artisan tinker

// Create test users
User::factory(50)->create(['is_banned' => false]);

// Create test groups
Group::factory(10)->create();

// Create test events
Event::factory(20)->create();

// Create test posts
Post::factory(30)->create();

// Create SOS signals
SosSignal::factory(5)->create(['status' => 'active']);
```

---

## 🐛 Troubleshooting

### Issue: "Route [admin.dashboard] not defined"
**Solution**: Add route in `routes/web.php`:
```php
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
```

### Issue: "Call to undefined method User::toArray()"
**Solution**: Views expect Eloquent collections. Ensure controller passes models:
```php
'users' => User::all(), // ✅ Correct
'users' => User::all()->toArray(), // ❌ Wrong
```

### Issue: Sidebar not collapsing
**Solution**: Ensure Alpine.js is loaded. Check in layout:
```blade
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
```

### Issue: Colors not showing
**Solution**: Ensure Tailwind CSS is compiled:
```bash
npm run build
```

---

## 📝 Mini-Tutorial: Adding a New Section

Want to add a "Settings" section? Follow these steps:

### 1. Create View
`resources/views/admin/settings/index.blade.php`:
```blade
<div>
    <h2 class="text-3xl font-black text-white">Settings</h2>
    <div class="bg-[#1a1a1a] border border-[#2a2a2a] rounded-2xl p-6">
        <p class="text-white">Settings content here</p>
    </div>
</div>
```

### 2. Add Menu Item
In `admin/dashboard.blade.php` (line 55):
```blade
['icon' => 'settings', 'label' => 'Settings', 'route' => 'admin.settings', 'section' => 'settings'],
```

### 3. Add Section to Dashboard
In `admin/dashboard.blade.php` (line 211):
```blade
<section x-show="currentSection === 'settings'">
    <div>
        <h2 class="text-3xl font-black text-white mb-2">Settings</h2>
        <p class="text-[#8b8b6b] text-sm">Configure system settings</p>
    </div>
    @include('admin.settings.index')
</section>
```

### 4. Update Breadcrumb
In `admin/dashboard.blade.php` (line 180):
```blade
'settings': 'Settings',
```

Done! 🎉

---

## 🎯 Performance Tips

1. **Use Pagination**
   ```blade
   @forelse($users->paginate(20) as $user)
   ```

2. **Eager Load Relations**
   ```php
   $users = User::with('groups', 'runs')->get();
   ```

3. **Cache Expensive Queries**
   ```php
   $groups = Cache::remember('all_groups', 3600, fn() => Group::all());
   ```

4. **Use Alpine.js for Client-Side Logic**
   - Don't refresh page for toggles
   - Use `fetch()` for quick updates

---

## 📚 File Reference

| File | Purpose |
|------|---------|
| `admin/dashboard.blade.php` | Main layout, sidebar, navigation |
| `admin/users/table.blade.php` | User management interface |
| `admin/notifications/template-builder.blade.php` | Notification sending |
| `admin/sos/emergency-dashboard.blade.php` | Emergency response |
| `admin/events/kanban.blade.php` | Event workflow |
| `admin/posts/moderation.blade.php` | Content moderation |
| `admin/groups/growth-dashboard.blade.php` | Group analytics |

---

## ✅ Verification Checklist

After setup, verify:
- [ ] Route `/admin/dashboard` loads without 404
- [ ] Sidebar appears on left
- [ ] Can click menu items to switch sections
- [ ] Can search in sidebar search
- [ ] Breadcrumb updates when switching sections
- [ ] User table loads and displays avatars
- [ ] Status badges show correct colors
- [ ] Ban button is clickable (even if not functional yet)
- [ ] Notification template selector works
- [ ] SOS section shows sample data
- [ ] Event kanban columns are visible
- [ ] Post moderation tabs switch
- [ ] Group growth chart displays

---

## 🚀 Next Steps

1. ✅ Set up controller & routes
2. ✅ Verify dashboard loads
3. ⏭️ Implement controller action methods
4. ⏭️ Add database migrations for flags (is_banned, is_featured, etc.)
5. ⏭️ Connect AJAX buttons to backend
6. ⏭️ Add real-time notifications (WebSocket optional)
7. ⏭️ Deploy to production

---

**Questions?** Check `ADMIN_DASHBOARD_GUIDE.md` for detailed documentation.

**Ready to build?** Start with Step 1! 💪
