# RunTracker Admin - UI Components Quick Reference

## Dashboard Components

### 1. Welcome Header
```
┌─────────────────────────────────────────┐
│ [👤]  Welcome back, [Name]              │
│        Keep pushing your limits         │
└─────────────────────────────────────────┘
```

### 2. Summary Stats Cards (4-column responsive)
```
┌──────────────────┐  ┌──────────────────┐
│ 🏃 123.5 km      │  │ ⏰ Next Run       │
│ Total KM Run     │  │ Mar 25, 2026     │
└──────────────────┘  └──────────────────┘

┌──────────────────┐  ┌──────────────────┐
│ 👥 5 Groups      │  │ 🌍 2,453 Runners │
│ Active Groups    │  │ Platform Stats   │
└──────────────────┘  └──────────────────┘
```

### 3. Quick Action Buttons
```
[+ Start New Run] [🔍 Browse Events] [👥 Join Groups] [💬 Social Feed]
```

### 4. Recent Activity Feed
```
[👤] Runner Name              2 hours ago
     Shared a moment from their run
     ❤️ 24  💬 5
```

---

## Event Page Components

### 1. Run Type Badges
```
Road Run (Sky Blue)      Trail Run (Emerald)    Ultramarathon (Purple)
[🏃 ROAD RUN] [●ACTIVE]  [🌲 TRAIL RUN] [●]   [🏔️ ULTRA] [●]
```

### 2. Event Card
```
┌────────────────────────────────────┐
│ [🏃 ROAD RUN] [●ACTIVE]           │
│ Event Title Here                   │
│ [📍 12 km] [👥 28 participants]   │
│                                    │
│ 📅 Mar 25, 2026                   │
│ 🕐 8:00 AM                        │
│ 📍 Kuala Lumpur                   │
│                                    │
│ RM 25.00                          │
│ [Details] [Join]                   │
└────────────────────────────────────┘
```

### 3. Empty State
```
        ⏰
   No Events Found
   Try adjusting filters
   [Reset Filters]
```

---

## Post/Social Feed Components

### 1. Post Card
```
┌──────────────────────────────────────────┐
│ [👤] Runner Name      [⭐ ADMIN BADGE]   │
│      2 hours ago                         │
├──────────────────────────────────────────┤
│          [Post Image or 🏃]             │
├──────────────────────────────────────────┤
│ Just finished an amazing 10km run today! │
│                                          │
│ [View Post]  [❤️ 24] [💬 5]            │
└──────────────────────────────────────────┘
```

### 2. Like/Comment Interactions
```
Before Click:                After Click:
[❤️ 24]                     [❤ 25]  (bounces & scales)
[💬 5]                      [💬 5]
```

---

## Group Page Components

### 1. Member Face-Pile
```
[👤] [👤] [👤] [👤] [👤] [+3]  ← 13 active members
```

### 2. Top Runners Leaderboard
```
┌──────────────────────────────────┐
│ ⭐ Top Runners                   │
├──────────────────────────────────┤
│ 1  [👤] Runner Name      123 km  │
│ 2  [👤] Runner Name       98 km  │
│ 3  [👤] Runner Name       87 km  │
│ 4  [👤] Runner Name       76 km  │
│ 5  [👤] Runner Name       65 km  │
└──────────────────────────────────┘
```

### 3. Group Challenge Progress
```
Monthly Goal: Run 1000 KM Together

████████░░░░░░░  75% Complete

750 km completed
250 km to go
```

### 4. Join/Leave Buttons
```
For Non-Members:           For Members:           For Creator:
[👤 Join Community]        [🚪 Leave Community]   ✓ You are the creator
(Green Button)             (Red Button)           (Read-only)
```

---

## Color Reference

| Element | Color | Usage |
|---------|-------|-------|
| Primary Button | #6b6b4b | Main actions |
| Background | #0a0a0a | Page background |
| Card Background | #1a1a1a | Card containers |
| Border | #2a2a2a | Outlines |
| Text Primary | #ffffff | Headings, labels |
| Text Secondary | #8b8b6b | Supporting text |
| Success | #10b981 | Status indicators |
| Info | #3b82f6 | Event info |
| Warning | #a855f7 | Ultramarathon |
| Error | #ef4444 | Delete/Leave actions |

---

## Animation Reference

| Animation | Trigger | Duration | Effect |
|-----------|---------|----------|--------|
| Lift | Hover on card | 300ms | `translate-y-2` ↑ |
| Scale | Hover on button | 300ms | `scale-110` 🔍 |
| Pulse | Status indicator | ∞ | Animated glow 💫 |
| Bounce | Like click | 1s | `animate-bounce` 🎈 |
| Glow | Border change | 300ms | Border color fade |
| Zoom | Hover on image | 500ms | `scale-110` 📸 |

---

## Responsive Breakpoints

| Breakpoint | Width | Layout |
|------------|-------|--------|
| Mobile | < 768px | 1 column |
| Tablet | 768px - 1024px | 2 columns |
| Desktop | 1024px+ | 3-4 columns |

---

## Accessibility Features

✓ Proper color contrast (WCAG AA compliant)
✓ Semantic HTML with proper heading hierarchy
✓ SVG icons with accessible attributes
✓ Button labels clearly describe actions
✓ Form inputs with associated labels
✓ Focus states for keyboard navigation
✓ Alt text for images

---

## Performance Notes

- CSS-only animations (no JavaScript overhead)
- Optimized for 60fps smooth scrolling
- Lazy loading for images
- Minimal DOM manipulation
- CSS transitions preferred over animations

